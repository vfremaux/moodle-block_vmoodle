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
 * Describes a command that allows synchronising plugin state.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace vmoodleadminset_plugins;

use \block_vmoodle\commands\Command;
use \block_vmoodle\commands\Command_Parameter;
use \block_vmoodle\commands\Command_Exception;
use \StdClass;

class Command_Plugins_Sync extends Command {

    /**
     * Constructor.
     * @throws Command_Exception.
     */
    function __construct() {
        global $DB;

        // Getting command description.
        $cmd_name = vmoodle_get_string('cmdsyncname', 'vmoodleadminset_plugins');
        $cmd_desc = vmoodle_get_string('cmdsyncdesc', 'vmoodleadminset_plugins');

        // Creating platform parameter. This is the source platform.
        $platform_param = new Command_Parameter('platform',    'enum', vmoodle_get_string('platformparamsyncdesc', 'vmoodleadminset_plugins'), null, get_available_platforms());

        // Creating plugins type parameter. If this parameter has a value,
        // then all plugins in this type will be synchronized
        $plugintypes = get_plugin_types();
        $plugintype_param = new Command_Parameter('plugintype', 'enum', vmoodle_get_string('plugintypeparamsyncdesc', 'vmoodleadminset_plugins'), null, $plugintypes);

        // Creating command.
        parent::__construct($cmd_name, $cmd_desc, array($platform_param, $plugintype_param));
    }

    /**
     * Execute the command.
     * @param mixed $hosts The host where run the command (may be wwwroot or an array).
     * @throws Command_Exception
     */
    function run($hosts) {
        global $CFG, $USER;

        // Adding constants.
        require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

        // Checking capabilities.
        if (!has_capability('block/vmoodle:execute', context_system::instance())) {
            throw new Command_Exception('insuffisantcapabilities');
        }

        // Getting plugintype.
        $plugintype = $this->getParameter('plugintype')->getValue();

        // Checking hosts.
        $platform = $this->getParameter('platform')->getValue();
        if (array_key_exists($platform, $hosts)) {
            $platforms = get_available_platforms();
            throw new Command_Plugins_Exception('syncwithitself');
        }

        // Creating peer to read plugins configuration from the designated peer.
        $mnet_host = new mnet_peer();
        if (!$mnet_host->bootstrap($this->getParameter('platform')->getValue(), null, 'moodle')) {
            $response = (object) array(
                            'status' => MNET_FAILURE,
                            'error' => get_string('couldnotcreateclient', 'block_vmoodle', $platform)
                        );

            // if we fail, we fail for all.
            foreach($hosts as $host => $name) {
                $this->results[$host] = $response;
            }
            return;
        }

        // Creating XMLRPC client to read plugins configuration.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/plugins/rpclib.php/mnetadmin_rpc_get_plugins_info');
        $rpc_client->add_param($plugintype, 'string');

        // Checking result.
        if (!($rpc_client->send($mnet_host) && ($response = json_decode($rpc_client->response)) && $response->status == RPC_SUCCESS)) {
            // Creating response.
            if (!isset($response)) {
                $response = new Stdclass();
                $response->status = MNET_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                $response->error = implode('<br/>', $rpc_client->getErrors($mnet_host));
            }

            if (debugging()) {
                echo '<pre>';
                var_dump($rpc_client);
                ob_flush();
                echo '</pre>';
            }

            // result is a plugin info structure that needs be replicated remotely to all targets.
            $plugininfos = $response->value;

            return;
        }

        // Initializing responses.
        $responses = array();

        // Creating peers.
        $mnet_hosts = array();
        foreach ($hosts as $host => $name) {
            $mnet_host = new mnet_peer();
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
        $rpc_client->set_method('blocks/vmoodle/plugins/plugins/rpclib.php/mnetadmin_rpc_set_plugins_states');
        $rpc_client->add_param($plugininfos, 'object'); // Plugininfos structure.

        // Sending requests.
        foreach ($mnet_hosts as $mnet_host) {

            // Sending request.
            if (!$rpc_client->send($mnet_host)) {
                $response = new Stdclass();
                $response->status = MNET_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                $response->error = 'Set plugin state failed : Remote call error';
            } else {
                $response = json_decode($rpc_client->response);
            }

            // Recording response.
            $responses[$mnet_host->wwwroot] = $response;
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
    function getResult($host=null, $key=null) {

        // Checking if command has been runned.
        if (!$this->isRunned()) {
            throw new Command_Exception('commandnotrun');
        }

        // Checking host (general result isn't provide in this kind of command).
        if (is_null($host) || !array_key_exists($host, $this->results)) {
            return null;
        }
        $result = $this->results[$host];

        // Checking key.
        if (is_null($key)) {
            return $result;
        } else if (property_exists($result, $key)) {
            return $result->$key;
        } else {
            return null;
        }
    }
}