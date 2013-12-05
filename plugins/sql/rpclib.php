<?php
/**
 * Declare RPC functions for sqllib.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

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

/**
 * Get fields values of a virtual platform.
 * @param	$user					string				The calling user.
 * @param	$table					string				The table to read.
 * @param	$fields					string				The fileds to retrieve.
 * @param	$select					mixed				The value of id or alternative field.
 */
function mnetadmin_rpc_get_fields($user, $table, $fields, $select) {
	global $CFG, $USER,$DB;
	// Invoke local user and check his rights
	invoke_local_user($user, 'block/vmoodle:execute');
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	// Getting record
	ob_clean(); ob_start();	// Used to prevent HTML output from dmllib methods and capture errors
	$field = array_keys($select);
	$record = $DB->get_record($table, array($field[0] => $select[$field[0]], isset($select[1]) ? $field[1] : '' => isset($select[1]) ? $select[1] : '', isset($select[2]) ? $field[2] : '' => isset($select[2]) ? $select[2] : ''), implode(',', $fields));
	if (!$record) {
		$error = parse_wlerror();
		if (empty($error))
			$error = 'Unable to retrieve record.';
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = $error;
		return json_encode($response);
	}
	ob_end_clean();
	// Setting value
	$response->value = $record;
	// Returning response
	return json_encode($response);
}

/**
 * Get fields values of a virtual platform.
 * @param	$user					string				The calling user.
 * @param	$command				string				The sql command to run.
 * @param	$return					boolean				True if the result of SQL should be returned, false otherwise. In that case query CANNOT be multiple
 */
function mnetadmin_rpc_run_sql_command($user, $command, $params, $return=false, $multiple=false) {
	global $CFG, $USER, $vmcommands_constants, $DB;
	// Adding requierements
    
	require_once $CFG->dirroot.'/blocks/vmoodle/locallib.php';
	require_once $CFG->dirroot.'/blocks/vmoodle/classes/Command.class.php';
	// Invoke local user and check his rights

//	invoke_local_user($user, 'block/vmoodle:execute');
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;

	// debug_trace("Original command : $command | $return | $multiple ");

	// Parsing command constants
	// this should be done at start location
	/*
	function replaceParametersValues($matches) {
		return replace_parameters_values($matches, array(), false, true);
	}
	$command = preg_replace_callback(Vmoodle_Command::placeholder, 'replaceParametersValues', $command);
	*/

// split multiple, non return commands, or save unique as first of array	
	if ($multiple == true && !$return){
		$commands = explode(";\n", $command);
	} else {
		$commands[] = $command;
	}

	// Runnning commands
	foreach($commands as $command){
		
		// debug_trace("Remote admin SQL Call : $command");
		// debug_trace("Params : ".serialize($params));
	
		// ob_clean();	ob_start();	// Used to prevent HTML output from dmllib methods and capture errors
		if ($return){
			try{
				$record = $DB->get_record_sql($command, $params);
				$response->value = $record;
			} catch(Exception $e) {
				$response->errors[] = $e->error;
				$response->error = $e->error;
			}
		} else {
			try{
				$DB->execute($command, $params);
			} catch(Exception $e) {
				$response->errors[] = $e->error;
				$response->error = $e->error;
			}
		}
		
		/*
		if ((!$return && !$DB->execute($command)) || ($return && !($record = $DB->get_record_sql($command)))) {
			$response->status = RPC_FAILURE_RUN;
			$error = parse_wlerror();

			if (empty($error)){
				$error = "Unable to run SQL command: ".$command;
			}

			$response->errors[] = $error;
			ob_end_clean();
			return json_encode($response);
		}
		*/
		// ob_end_clean();
	}

	// Returning response of last statement.
	if (!empty($response->errors)){
		$response->status = RPC_FAILURE;
	} else {
		$response->status = RPC_SUCCESS;
	}

	return json_encode($response);
}