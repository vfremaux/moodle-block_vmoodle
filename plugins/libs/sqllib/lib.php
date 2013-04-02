<?php

defined('VMOODLE_CLASSSES_DIR') || require_once $CFG->dirroot.'/blocks/vmoodle/locallib.php';
require_once(VMOODLE_CLASSES_DIR.'XmlRpc_Client.class.php');

/**
 * This library provides SQL commands for the meta-administration.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 
/** Adding the SQL commands' constants */
/*
if (isset($vmcommands_constants))
	$vmcommands_constants = array_merge(
		$vmcommands_constants,
		array(
			'prefix' => $CFG->prefix
		)
	);
*/

/**
 * Get fields values of a virtual platform via MNET service.
 * @param	host			string				The virtual platform to aim.
 * @param	table			string				The table to read.
 * @param	select			mixed				The value of id or alternative field.
 * @param	fields			string				The fileds to retrieve (optional).
 * @throws					Vmoodle_Command_Sql_Exception.
 */
function vmoodle_get_field($host, $table, $select, $fields='*') {
	global $CFG, $USER;
	
	// Checking capabilities
	if (!has_capability('block/vmoodle:execute', get_context_instance(CONTEXT_SYSTEM)))
		throw new Vmoodle_Command_Sql_Exception('unsiffisantcapability');
	
	// Checking host
	if (!get_record('mnet_host', 'wwwroot', $host))
		throw new Vmoodle_Command_Sql_Exception('invalidhost');
	
	// Checking table
	if (empty($table) || !is_string($table))
		throw new Vmoodle_Command_Sql_Exception('invalidtable');
			
	// Checkig select
	if (empty($select) || (!is_array($select) && !is_int($select)))
		throw new Vmoodle_Command_Sql_Exception('invalidselect');
	if (!is_array($select)) 
		$select = array('id' => $select);
		
	// Checking field
	if (empty($fields))
		throw new Vmoodle_Command_Sql_Exception('invalidfields');
	if (!is_array($fields))
		$fields = array($fields);
		
	// Creating peer
	$mnet_host = new mnet_peer();
	if (!$mnet_host->bootstrap($host, null, 'moodle')) {
		return (object) array(
							'status' => MNET_FAILURE,
							'error' => get_string('couldnotcreateclient', 'block_vmoodle', $host)
						);
	}
	
	// Creating XMLRPC client
	$rpc_client = new Vmoodle_XmlRpc_Client();
	$rpc_client->add_param($table, 'string');
	$rpc_client->add_param($fields, 'array');
	$rpc_client->add_param($select, 'array');
	
	// Sending request
	if (!$rpc_client->send($mnet_host)) {
		if (debugging()) {
			echo '<pre>';
			var_dump($rpc_client);
			echo '</pre>';
		}
	}
	
	// Returning result
	return $rpc_client->response;
}

/**
 * Install sqllib plugin library.
 * @return					boolean				TRUE if the installation is successfull, FALSE otherwise.
 */
function sqllib_install() {
	// Initialising
	$result = true;
	$rpc = new stdclass;
	$rpcmap = new stdclass;
	
	// Retrieve service
	if (!$service = get_record('mnet_service', 'name', 'mnet_admin')) {
		notify('The mnet_service is not installed.');
		return false;
	} else {
		$serviceid = $service->id;
		unset($service);
	}
	
	/*
	 * Installing RPC call 'get_fields'
	 */
	// Checking if it is already installed
	if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_get_fields')) {
		
		// Creating RPC call
		$rpc->function_name = 'mnetadmin_rpc_get_fields';
		$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_get_fields';
		$rpc->parent_type = 'block';  
		$rpc->parent = 'vmoodle';
		$rpc->enabled = 0; 
		$rpc->help = 'Get fields from database.';
		$rpc->profile = '';
		
		// Adding RPC call
		if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
			notify('Error installing mnet_admin RPC call "get_fields".');
			$result = false;
		} else {	
			// Mapping service and call
			$rpcmap->serviceid = $serviceid;
			$rpcmap->rpcid = $rpcid;
			if (!insert_record('mnet_service2rpc', $rpcmap)) {
				notify('Error mapping RPC call "get_fields to the "mnet_admin" service.');
				$result = false;
			}
		}
	}
	
	/*
	 * Installing RPC call 'run_sql_command'
	 */
	// Checking if it is already installed
	if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_run_sql_command')) {
		
		// Creating RPC call
		$rpc->function_name = 'mnetadmin_rpc_run_sql_command';
		$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_run_sql_command';
		$rpc->parent_type = 'block';  
		$rpc->parent = 'vmoodle';
		$rpc->enabled = 0; 
		$rpc->help = 'Run SQL commands.';
		$rpc->profile = '';
		
		// Adding RPC call
		if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
			notify('Error installing mnet_admin RPC call "run_sql_command".');
			$result = false;
		} else {	
			// Mapping service and call
			$rpcmap->serviceid = $serviceid;
			$rpcmap->rpcid = $rpcid;
			if (!insert_record('mnet_service2rpc', $rpcmap)) {
				notify('Error mapping RPC call "run_sql_command" to the "mnet_admin" service.');
				$result = false;
			}
		}
	}
	
	// Returning result
	return $result;
}

/**
 * Uninstall sqlib plugin library.
 * @return					boolean				TRUE if the uninstallation is successfull, FALSE otherwise.
 */
function sqllib_uninstall() {
	// Initializing
	$result = true;
	
	// Uninstalling RPC call 'mnetadmin_rpc_get_fields'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_get_fields')) && 			delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_get_fields".');
		$result = false;
	}
	
	// Uninstalling RPC call 'mnetadmin_rpc_run_sql_command'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_run_sql_command')) && 			delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_run_sql_command".');
		$result = false;
	}
	
	// Returning result
	return $result;
}