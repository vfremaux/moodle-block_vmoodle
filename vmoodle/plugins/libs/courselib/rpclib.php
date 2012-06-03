<?php
/**
 * Declare RPC functions for course MNET operations.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery@valeisti.fr)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

include_once $CFG->libdir.'/pear/HTML/AJAX/JSON.php';
require_once $CFG->dirroot.'/blocks/vmoodle/classes/XmlRpc_Client.class.php';

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}
 
/**
 * Ask for deploying a local template using publisflow 
 * This is a special access point for external ERP invocation
 *
 * @param	$callinguser			string				The calling user.
 * @param	$idnumber				string				The idnumber of the local course template that needs to be deployed.
 * @param	$remotehostroot			string				The hostname where to deploy.
 */
function mnetadmin_rpc_create_course($callinguser, $idnumber, $remotehostroot) {
	global $CFG, $USER;

	// Invoke local user and check his rights
	invoke_local_user((array)$user, 'block/vmoodle:execute');
	
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;

    return json_encode($response);	
}
