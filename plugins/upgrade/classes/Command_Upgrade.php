<?php

namespace vmoodleadminset_upgrade;
Use \block_vmoodle\commands\Command;
Use \block_vmoodle\commands\Command_Exception;
Use \block_vmoodle\commands\Command_Parameter;
Use \StdClass;

require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503); 
    define('RPC_FAILURE_CAPABILITY', 510);
    define('RPC_FAILURE_RECORD', 520);
    define('RPC_FAILURE_RUN', 521);
}

if (!defined('RPC_FAILURE_RUN')) {
    define('RPC_FAILURE_RUN', 521);
}
if (!defined('MNET_FAILURE')) {
    define('MNET_FAILURE', 511);
}

/**
 * Describes a platform update command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Command_Upgrade extends Command {

    /**
     * The cURL timeout
     */
    const curl_timeout = 30;

    /**
     * Constructor.
     * @throws                Command_Exception.
     */
    public function __construct() {

        // Getting command description.
        $cmd_name = vmoodle_get_string('cmdupgradename', 'vmoodleadminset_upgrade');
        $cmd_desc = vmoodle_get_string('cmdupgradedesc', 'vmoodleadminset_upgrade');

        // Creating command.
        parent::__construct($cmd_name, $cmd_desc);
    }

    public function run($hosts) {
        global $CFG, $USER, $DB;

        // Adding constants.
        require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

        // Checking host.
        if (!is_array($hosts)) {
            $hosts = array($hosts => 'Unnamed host');
        }

        // Checking capabilities.
        if (!has_capability('block/vmoodle:execute', \context_system::instance())) {
            throw new Command_Upgrade_Exception('insuffisantcapabilities');
        }

        // Initializing responses.
        $responses = array();

        // Creating peers.
        $mnet_hosts = array();
        foreach ($hosts as $host => $name) {
            $mnet_host = new \mnet_peer();
            if ($mnet_host->bootstrap($host, null, 'moodle')) {
                $mnet_hosts[] = $mnet_host;
            } else {
                $responses[$host] = (object) array('status' => RPC_FAILURE, 'error' => get_string('couldnotcreateclient', 'block_vmoodle', $host));
            }
        }

        // Creating XMLRPC client.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/upgrade/rpclib.php/mnetadmin_rpc_upgrade');

        // Sending requests
        foreach ($mnet_hosts as $mnet_host) {

            /**
            * just for testing
            if ($mnet_host->wwwroot == $CFG->wwwroot){
                require_once $CFG->dirroot.'/blocks/vmoodle/plugins/upgrade/rpclib.php';
                if (!($user_mnet_host = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))))
                    throw new Command_Exception('unknownuserhost');
                $user = array(
                            'username' => $USER->username,
                            'remoteuserhostroot' => $user_mnet_host->wwwroot,
                            'remotehostroot' => $CFG->wwwroot
                        );
                $response = mnetadmin_rpc_upgrade($user, true);
                $responses[$mnet_host->wwwroot] = $response;
                continue;
            }
            */
            
            // Sending request
            if (!$rpc_client->send($mnet_host)) {
                $response = new StdClass();
                $response->status = RPC_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                if (debugging()) {
                    // echo '<pre>';
                    // var_dump($rpc_client);
                    // echo '</pre>';
                }
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
    public function getResult($host=null, $key = null) {

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
        } elseif (property_exists($result, $key)) {
            return $result->$key;
        } else {
            return '';
        }
    }
}