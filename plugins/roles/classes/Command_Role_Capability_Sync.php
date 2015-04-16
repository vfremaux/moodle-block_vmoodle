<?php

namespace vmoodleadminset_roles;
Use \block_vmoodle\commands\Command;
Use \block_vmoodle\commands\Command_Exception;
Use \block_vmoodle\commands\Command_Parameter;
Use \StdClass;
Use \moodle_url;

require_once($CFG->libdir.'/accesslib.php');

/**
 * Describes a role syncrhonisation command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Command_Role_Capability_Sync extends Command {

    /**
     * Constructor.
     * @throws Command_Exception.
     */
    public function __construct() {
        global $DB;
        
        // Getting command description.
        $cmd_name = vmoodle_get_string('cmdsynccapabilityname', 'vmoodleadminset_roles');
        $cmd_desc = vmoodle_get_string('cmdsynccapabilitydesc', 'vmoodleadminset_roles');

        // Creating platform parameter.
        $platform_param = new Command_Parameter('platform',    'enum', vmoodle_get_string('platformparamsyncdesc', 'vmoodleadminset_roles'), null, get_available_platforms());

        // Getting role parameter.
        $roles = role_fix_names(get_all_roles(), \context_system::instance(), ROLENAME_ORIGINAL);
        $rolemenu = array();

        foreach ($roles as $r) {
            $rolemenu[$r->shortname] = $r->name;
        }
        $role_param = new Command_Parameter('role', 'enum', vmoodle_get_string('roleparamsyncdesc', 'vmoodleadminset_roles'), null, $rolemenu);

        // Creating capability parameter.
        $records = $DB->get_records('capabilities', null, 'name', 'name');
        $capabilities = array();

        foreach($records as $record) {
            $capabilities[$record->name] = get_capability_string($record->name);
        }

        asort($capabilities);
        $capability_param = new Command_Parameter('capability', 'enum', vmoodle_get_string('capabilityparamsyncdesc', 'vmoodleadminset_roles'), null, $capabilities);

        // Creating command.
        parent::__construct($cmd_name, $cmd_desc, array($platform_param, $role_param, $capability_param));
    }

    /**
     * Execute the command.
     * @param mixed $hosts The host where run the command (may be wwwroot or an array).
     * @throws Command_Exception.
     */
    public function run($hosts) {
        global $CFG, $USER;

        // Adding constants.
        require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

        // Checking capabilities.
        if (!has_capability('block/vmoodle:execute', \context_system::instance())) {
            throw new Command_Exception('insuffisantcapabilities');
        }

        // Getting role.
        $role = $this->getParameter('role')->getValue();

        // Getting platform.
        $platform = $this->getParameter('platform')->getValue();

        // Getting capability.
        $capability = $this->getParameter('capability')->getValue();

        // Checking hosts.
        if (array_key_exists($platform, $hosts)) {
            $platforms = get_available_platforms();
            throw new Command_Role_Exception('syncwithitself', (object)array('role' => $role, 'platform' => $platforms[$platform]));
        }

        // Creating peer to read role configuration.
        $mnet_host = new \mnet_peer();
        if (!$mnet_host->bootstrap($this->getParameter('platform')->getValue(), null, 'moodle')) {
            $response = (object) array(
                            'status' => MNET_FAILURE,
                            'error' => get_string('couldnotcreateclient', 'block_vmoodle', $platform)
                        );
            foreach ($hosts as $host => $name) {
                $this->results[$host] = $response;
            }
            return;
        }

        // Creating XMLRPC client to read role configuration.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_get_role_capabilities');
        $rpc_client->add_param($role, 'string');
        $rpc_client->add_param($capability, 'string');

        // Checking result.
        if (!($rpc_client->send($mnet_host) && ($response = json_decode($rpc_client->response)) && (
                $response->status == RPC_SUCCESS ||
                ($response->status == RPC_FAILURE_RECORD && (
                    in_array($response->errors, 'No capabilites for this role.') || 
                    in_array($response->error, 'No role capability found.'))
                )
            ))) {
            // Creating response.
            if (!isset($response)) {
                $response = new \StdClass();
                $response->status = MNET_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
            }
            if (debugging()) {
                echo '<pre>';
                var_dump($rpc_client);
                ob_flush();
                echo '</pre>';
            }

            // Saving results.
            foreach($hosts as $host => $name) {
                $this->results[$host] = $response;
            }
            return;
        }

        // Getting role configuration.
        if ($response->status == RPC_FAILURE_RECORD) {
            $role_capability = array($capability => null);
        } else {
            $role_capability = (array) $response->value;
        }
        unset($response);

        // Initializing responses.
        $responses = array();

        // Creating peers.
        $mnet_hosts = array();

        foreach ($hosts as $host => $name) {
            $mnet_host = new \mnet_peer();
            if ($mnet_host->bootstrap($host, null, 'moodle')) {
                $mnet_hosts[] = $mnet_host;
            } else {
                $responses[$host] = (object) array(
                    'status' => MNET_FAILURE,
                    'error' => get_string('couldnotcreateclient', 'block_vmoodle', $host)
                );
            }
        }

        // Creating XMLRPC client.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_set_role_capabilities');
        $rpc_client->add_param($role, 'string');
        $rpc_client->add_param($role_capability, 'string');
        $rpc_client->add_param(false, 'boolean');

        // Sending requests.
        foreach ($mnet_hosts as $mnet_host) {
            // Sending request.
            if (!$rpc_client->send($mnet_host)) {
                $response = new \StdClass();
                $response->status = MNET_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                $response->error = 'Remote Set role capability : Remote proc error';
                if (debugging()) {
                    echo '<pre>';
                    var_dump($rpc_client);
                    ob_flush();
                    echo '</pre>';
                }
            } else {
                $response = json_decode($rpc_client->response);
                $response->errors[] = implode('<br/>', $response->errors);
            }
            // Recording response
            $responses[$mnet_host->wwwroot] = $response;
        }
        // Saving results
        $this->results = $responses + $this->results;
    }

    /**
     * Get the result of command execution for one host.
     * @param    $host        string            The host to retrieve result (optional, if null, returns general result).
     * @param    $key        string            The information to retrieve (ie status, error / optional).
     * @return                mixed            The result or null if result does not exist.
     * @throws                Command_Exception.
     */
    public function getResult($host = null, $key = null) {
        global $CFG, $SESSION,$DB,$OUTPUT;

        // Checking if command has been runned.
        if (!$this->isRunned()) {
            throw new Command_Exception('commandnotrun');
        }

        // Checking host (general result isn't provide in this kind of command).
        if (is_null($host)) {
            if (isset($SESSION->vmoodle_sa['rolelib']['command']) && isset($SESSION->vmoodle_sa['rolelib']['platforms'])) {
                return '<center>'.$OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/blocks/vmoodle/plugins/roles/controller.rolelib.sadmin.php', array('what' => 'backtocomparison')), get_string('backtocomparison', 'vmoodleadminset_roles'), 'get').'</center><br/>';
            } else {
                return null;
            }
        } elseif (!array_key_exists($host, $this->results)) {
            return null;
        }
        $result = $this->results[$host];

        // Checking key.
        if (is_null($key)) {
            return $result;
        } elseif (property_exists($result, $key)) {
            return $result->$key;
        } else {
            return null;
        }
    }
}