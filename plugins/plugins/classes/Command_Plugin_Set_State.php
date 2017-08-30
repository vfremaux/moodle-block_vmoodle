<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Describes a role comparison command.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace vmoodleadminset_plugins;

use \block_vmoodle\commands\Command;
use \block_vmoodle\commands\Command_Exception;
use \block_vmoodle\commands\Command_Parameter;

require_once($CFG->libdir.'/accesslib.php');
require_once($CFG->dirroot.'/blocks/vmoodle/plugins/plugins/rpclib.php');
require_once($CFG->dirroot.'/blocks/vmoodle/plugins/plugins/lib.php');

class Command_Plugin_Set_State extends Command {

    /**
     * The plugintype
     */
    private $plugintype;

    /**
     * The plugin
     */
    private $plugin;

    /**
     * The html report
     */
    private $report;

    /**
     * Constructor.
     * @throws Command_Exception.
     */
    public function __construct() {
        global $DB, $STANDARD_PLUGIN_TYPES;

        // Getting command description.
        $cmd_name = vmoodle_get_string('cmdpluginsetupname', 'vmoodleadminset_plugins');
        $cmd_desc = vmoodle_get_string('cmdpluginsetupdesc', 'vmoodleadminset_plugins');

        $pm = \plugin_manager::instance();

        $allplugins = $pm->get_plugins();

        $pluginlist = array();
        foreach ($allplugins as $type => $plugins) {
            foreach ($plugins as $p) {
                if (array_key_exists($type, $STANDARD_PLUGIN_TYPES)) {
                    $pluginlist[$type.'/'.$p->name] = $STANDARD_PLUGIN_TYPES[$type].' : '.$p->displayname;
                }
            }
        }

        asort($pluginlist, SORT_STRING);

        $plugin_param = new Command_Parameter('plugin', 'enum', vmoodle_get_string('pluginparamdesc', 'vmoodleadminset_plugins'), null, $pluginlist);

        $states = array();
        $states['enable'] = vmoodle_get_string('enable', 'vmoodleadminset_plugins');
        $states['disable'] = vmoodle_get_string('disable', 'vmoodleadminset_plugins');
        $state_param = new Command_Parameter('state', 'enum', vmoodle_get_string('pluginstateparamdesc', 'vmoodleadminset_plugins'), null, $states);

        // Creating command.
        parent :: __construct($cmd_name, $cmd_desc, array($plugin_param, $state_param));
    }

    /**
     * Execute the command.
     * @param mixed $hosts The host where run the command (may be wwwroot or an array).
     * @throws Command_Exception.
     */
    public function run($hosts) {
        global $CFG, $USER;

        // Adding constants.
        require_once($CFG->dirroot.'/blocks/vmoodle/rpclib.php');

        // Checking capability to run.
        if (!has_capability('block/vmoodle:execute', \context_system::instance()))
            throw new Command_Exception('insuffisantcapabilities');

        // Getting plugin.
        list($type, $plugin) = explode('/', $this->getParameter('plugin')->getValue());

        // Getting the state.
        $state = $this->getParameter('state')->getValue();

        $pm = \plugin_manager::instance();

        $plugininfo = $pm->get_plugin_info($plugin);
        if (empty($plugininfo->type)) {
            if (empty($plugininfo)) {
                $plugininfo = new \StdClass();
            }
            $plugininfo->type = $type;
        }
        $plugininfo->action = $state;

        $plugininfos[$plugin] = (array)$plugininfo;

        // Creating XMLRPC client to change remote configuration.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/plugins/rpclib.php/mnetadmin_rpc_set_plugins_states');
        $rpc_client->add_param($plugininfos, 'array');

        // Initializing responses.
        $responses = array();

        // Creating peers.
        $mnet_hosts = array();
        if (!empty($hosts)) {
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
        }

        // Sending requests.
        foreach($mnet_hosts as $mnet_host) {
            // Sending request.
            if (!$rpc_client->send($mnet_host)) {
                $response = new \StdClass();
                $response->status = MNET_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                if (debugging()) {
                    echo '<pre>';
                    var_dump($rpc_client);
                    echo '</pre>';
                }
            } else {
                $response = json_decode($rpc_client->response);
            }

            // Recording response.
            $responses[$mnet_host->wwwroot] = $response;

            // Recording plugin descriptors.
            if ($response->status == RPC_SUCCESS) {
                $this->plugins[$mnet_host->wwwroot] = @$response->value;
            }
        }

        // Saving results.
        $this->results = $responses + $this->results;

    }

    /**
     * Get the result of command execution for one host.
     * @param string $host The host to retrieve result (optional, if null, returns general result).
     * @param string $key The information to retrieve (ie status, error / optional).
     * @return mixed The result or null if result does not exist.
     * @throws Command_Exception.
     */
    public function getResult($host = null, $key = null) {

        // Checking if command has been runned.
        if (!$this->isRunned()) {
            throw new Command_Exception('commandnotrun');
        }

        // Checking host (general result isn't provide in this kind of command).
        if (is_null($host)) {
            return $this->report;
        } else {
            if (!array_key_exists($host, $this->results)) {
                return null;
            }
        }
        $result = $this->results[$host];

        // Checking key.
        if (is_null($key)) {
            return $result;
        } else {
            if (property_exists($result, $key)) {
                return $result-> $key;
            } else {
                return null;
            }
        }
    }
}