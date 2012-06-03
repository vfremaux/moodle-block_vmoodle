<?php
/**
 * Declare RPC functions for sqllib.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
include_once $CFG->libdir.'/pear/HTML/AJAX/JSON.php';

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
 * Get fields values of a virtual platform.
 * @param	$user					string				The calling user.
 * @param	$table					string				The table to read.
 * @param	$fields					string				The fileds to retrieve.
 * @param	$select					mixed				The value of id or alternative field.
 */
function mnetadmin_rpc_get_fields($user, $table, $fields, $select) {
	global $CFG, $USER;
	
	// Invoke local user and check his rights
	invoke_local_user($user, 'block/vmoodle:execute');
	
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	
	// Getting record
	ob_clean(); ob_start();	// Used to prevent HTML output from dmllib methods and capture errors
	$field = array_keys($select);
	$record = get_record($table,
						$field[0], $select[$field[0]],
						isset($select[1]) ? $field[1] : '', isset($select[1]) ? $select[1] : '', 						isset($select[2]) ? $field[2] : '', isset($select[2]) ? $select[2] : '', 						implode(',', $fields)
					);
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
 * @param	$return					boolean				True if the result of SQL should be returned, false otherwise.
 */
function mnetadmin_rpc_run_sql_command($user, $command, $return=false) {
	global $CFG, $USER, $vmcommands_constants;
	
	// Adding requierements
	require_once $CFG->dirroot.'/blocks/vmoodle/locallib.php';
	require_once $CFG->dirroot.'/blocks/vmoodle/classes/Command.class.php';
	
	// Invoke local user and check his rights
	invoke_local_user($user, 'block/vmoodle:execute');
	
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	
	// Parsing command constants
	function replaceParametersValues($matches) {
		return replace_parameters_values($matches, array(), false, true);
	}
	$command = preg_replace_callback(Vmoodle_Command::placeholder, 'replaceParametersValues', $command);
	
	// Runnning command
	ob_clean();	ob_start();	// Used to prevent HTML output from dmllib methods and capture errors
	if ((!$return && !execute_sql($command, false)) || ($return && !($record = get_record_sql($command)))) {
		$response->status = RPC_FAILURE_RUN;
		$error = parse_wlerror();
		if (empty($error))
			$error = 'Unable to run SQL command.';
		$response->errors[] = $error;
		ob_end_clean();
		return json_encode($response);
	}
	ob_end_clean();
	
	// Returning response
	$response->status = RPC_SUCCESS;
	if ($return)
		$response->value = $record;
	return json_encode($response);
}