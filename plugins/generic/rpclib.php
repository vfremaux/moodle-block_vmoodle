<?php
/*
 * Created on 18 nov. 2010
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

if (!defined('RPC_SUCCESS')) {
	define('RPC_TEST', 100);
	define('RPC_SUCCESS', 200);
	define('RPC_FAILURE', 500);
	define('RPC_FAILURE_USER', 501);
	define('RPC_FAILURE_CONFIG', 502);
	define('RPC_FAILURE_DATA', 503); 
	define('RPC_FAILURE_CAPABILITY', 510);
	define('MNET_FAILURE', 511);
	define('RPC_FAILURE_RECORD', 520);
	define('RPC_FAILURE_RUN', 521);
}

function dataexchange_rpc_fetch_config($user, $configkey, $module = '', $json_response = true){
	global $CFG, $USER;

	// Invoke local user and check his rights
	// debug_trace("/$configkey/, $CFG->dataexchangesafekeys $CFG->wwwroot");
	if (!preg_match("/$configkey/", @$CFG->dataexchangesafekeys)){
		if ($auth_response = invoke_local_user((array)$user)){
			if ($json_response){
			    return $auth_response;
			} else {
			    return json_decode($auth_response);
			}
		}
	}
	
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;

	$response->value = get_config('', $configkey);		
	
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

/**
 * Set on or off maintenance mode.
 * @param		$user		object		The calling user, containing mnethostroot reference and hostroot reference.
 * @param		$message	string		If empty, asks for a maintenance switch off.
 */
function mnetadmin_rpc_set_maintenance($user, $message, $hardmaintenance = false, $json_response = true) {
	global $CFG, $USER;

    debug_trace('RPC '.json_encode($user));

	if ($auth_response = invoke_local_user((array)$user)){
		if ($json_response){
		    return $auth_response;
		} else {
		    return json_decode($auth_response);
		}
	}

	// Creating response.
	$response = new stdClass;
	$response->status = RPC_SUCCESS;

	// keep old hard signalled maintenance mode of 1.9. Can be usefull in case database stops
	// but needs a patch in config to catch this real case.
	$filename = $CFG->dataroot.'/maintenance.html';

	if ($message != 'OFF'){
	    debug_trace('RPC : Setting maintenance on');
        $file = fopen($filename, 'w');
        fwrite($file, stripslashes($message));
        fclose($file);
        set_config('maintenance_enabled', 1);
        set_config('maintenance_message', $message);
	} else {
	    debug_trace('RPC : Setting maintenance off');
        unlink($filename);
        set_config('maintenance_enabled', 0);
        set_config('maintenance_message', null);
	}

    debug_trace('RPC Bind : Sending response');
	// Returns response (success or failure).
	return json_encode($response);
}

/**
 * Purge internally all caches.
 * @param		$user		object		The calling user, containing mnethostroot reference and hostroot reference.
 */
function mnetadmin_rpc_purge_caches($user, $json_response = true) {
	global $CFG, $USER;

    debug_trace('RPC '.json_encode($user));

	if ($auth_response = invoke_local_user((array)$user)){
		if ($json_response){
		    return $auth_response;
		} else {
		    return json_decode($auth_response);
		}
	}

	// Creating response.
	$response = new stdClass;
	$response->status = RPC_SUCCESS;

    purge_all_caches();

    debug_trace('RPC Bind : Sending response');
	// Returns response (success or failure).
	return json_encode($response);
}
