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
 * @param string $user The calling user.
 * @param string $table The table to read.
 * @param string $fields The fileds to retrieve.
 * @param mixed $select The value of id or alternative field.
 */
function mnetadmin_rpc_get_fields($user, $table, $fields, $select) {
    global $CFG, $USER, $DB;

    // Invoke local user and check his rights.
    invoke_local_user($user, 'block/vmoodle:execute');

    // Creating response.
    $response = new stdclass;
    $response->status = RPC_SUCCESS;

    // Getting record.
    ob_clean(); ob_start();    // Used to prevent HTML output from dmllib methods and capture errors.
    $field = array_keys($select);
    $record = $DB->get_record($table, array($field[0] => $select[$field[0]], isset($select[1]) ? $field[1] : '' => isset($select[1]) ? $select[1] : '', isset($select[2]) ? $field[2] : '' => isset($select[2]) ? $select[2] : ''), implode(',', $fields));
    if (!$record) {
        $error = parse_wlerror();
        if (empty($error)) {
            $error = 'Unable to retrieve record.';
        }
        $response->status = RPC_FAILURE_RECORD;
        $response->errors[] = $error;
        return json_encode($response);
    }
    ob_end_clean();

    // Setting value.
    $response->value = $record;

    // Returning response.
    return json_encode($response);
}

/**
 * Get fields values of a virtual platform.
 * @param string $user The calling user.
 * @param string $command The sql command to run.
 * @param boolean $return True if the result of SQL should be returned, false otherwise. In that case query CANNOT be multiple
 */
function mnetadmin_rpc_run_sql_command($user, $command, $params, $return=false, $multiple=false) {
    global $CFG, $USER, $vmcommands_constants, $DB;

    // Adding requirements.
    include_once($CFG->dirroot.'/blocks/vmoodle/locallib.php');

    // Invoke local user and check his rights.

//    invoke_local_user($user, 'block/vmoodle:execute');
    // Creating response.
    $response = new stdclass;
    $response->status = RPC_SUCCESS;

    // Split multiple, non return commands, or save unique as first of array.
    if ($multiple == true && !$return){
        $commands = explode(";\n", $command);
    } else {
        $commands[] = $command;
    }

    // Runnning commands.
    foreach ($commands as $command) {
        if (empty($command) || preg_match('/^\s+$/s', $command)) {
            continue;
        }
        if ($return) {
            try {
                $record = $DB->get_record_sql($command, $params);
                $response->value = $record;
            } catch(Exception $e) {
                $response->errors[] = $DB->get_last_error();
                $response->error = $DB->get_last_error();
            }
        } else {
            try {
                $DB->execute($command, $params);
            } catch(Exception $e) {
                $response->errors[] = $DB->get_last_error();
                $response->error = $DB->get_last_error();
            }
        }
    }

    // Returning response of last statement.
    if (!empty($response->errors)) {
        $response->status = RPC_FAILURE;
    } else {
        $response->status = RPC_SUCCESS;
    }
    return json_encode($response);
}