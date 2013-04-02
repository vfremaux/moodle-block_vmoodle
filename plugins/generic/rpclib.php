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
}

function dataexchange_rpc_fetch_config($user, $configkey, $module = '', $json_response = true){
	global $CFG, $USER;

	// Invoke local user and check his rights
	debug_trace("/$configkey/, $CFG->dataexchangesafekeys $CFG->wwwroot");
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