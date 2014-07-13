<?php
/**
 * Declare RPC functions for syncrolelib.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';
require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';
require_once $CFG->dirroot.'/blocks/vmoodle/plugins/plugins/pluginscontrolslib.php';

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

if (!defined('MNET_FAILURE')) {
    define('MNET_FAILURE', 511);
}

define('VMOODLE_PLUGIN_ENABLE', 1);
define('VMOODLE_PLUGIN_DISABLE', 0);

/**
 * Get role capabilities of a virtual platform.
 * @param    $user                    array or object        The calling user.
 * @param    $role                    string                The role to read capabilities.
 * @param    $capabilitues            mixed                The capabilities to read (optional / may be string or array).
 */
function mnetadmin_rpc_get_plugins_info($user, $plugintype, $json_response = true) {
    global $CFG, $USER, $DB;

    // Invoke local user and check his rights
    if ($auth_response = invoke_local_user((array)$user, 'block/vmoodle:execute')){
        if ($json_response) {
            return $auth_response;
        } else {
            return json_decode($auth_response);
        }
    }

    $response = new StdClass();
    $response->errors = array();
    $response->error = '';

    // Creating response
    $response->status = RPC_SUCCESS;

    // Getting role
    $pm = plugin_manager::instance();
    
    $allplugins = $pm->get_plugins();

    if (!array_key_exists($plugintype, $allplugins))    {
        $response->status = RPC_FAILURE_RECORD;
        $response->errors[] = "Non existant plugin type $plugintype.";
        $response->error = "Non existant plugin type $plugintype.";
        if ($json_response){
            return json_encode($response);
        } else {
            return $response;
        }
    }

    // Setting result value
    $response->value = (array)$allplugins[$plugintype];

    $actionclass = $plugintype.'_remote_control';

    // get activation status    
    foreach($response->value as $pluginname => $foobar){

        // ignore non implemented
        if (!class_exists($actionclass)){
            debug_trace("failing running remote action on $actionclass. Class not found");
            continue;
        }

        $control = new $actionclass($pluginname);
        $response->value[$pluginname]->enabled = $control->is_enabled();
    }

    // Returning response
    if ($json_response){
        return json_encode($response);
    } else {
        return $response;
    }
}

/**
 * Enables or disables a plugin of a virtual platform.
 * @param    $user                    string                The calling user.
 * @param    $plugininfos            string                a structure with info for each plugin to setup.
 */

function mnetadmin_rpc_set_plugins_states($user, $plugininfos, $json_response = true) {
    global $CFG, $USER, $DB;

    debug_trace("Plugin Set States: Entry point");

    // Creating response
    $response = new Stdclass();
    $response->status = RPC_SUCCESS;
    $response->errors = array();
    $response->error = '';

    // Invoke local user and check his rights
    if ($auth_response = invoke_local_user((array)$user, 'block/vmoodle:execute')){
        if ($json_response){ // we could not have a credential
            return $auth_response;
        } else {
            return json_decode($auth_response);
        }
    }

    // Getting plugin enable/disable method
    
    if (!empty($plugininfos)){
        foreach($plugininfos as $plugin => $infos){
            $actionclass = $infos['type'].'_remote_control';

            // ignore non implemented
            if (!class_exists($actionclass)){
                debug_trace("failing running remote action on $actionclass. Class not found");
                continue;
            }

            $control = new $actionclass($infos['type'].'/'.$plugin);
            $action = $infos['action'];
            $return = $control->action($action);
            if ($return !== 0){
                $response->status = RPC_FAILURE;
                $response->errors[] = $return;
            }
            $response->value = 'done.';
        }
    }

    $response->error = implode(', ', $response->errors);

    // Returning response
    if ($json_response){
        return json_encode($response);
    } else {
        return $response;
    }
}

