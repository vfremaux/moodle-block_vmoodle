<?php

defined('VMOODLE_CLASSSES_DIR') || require_once $CFG->dirroot.'/blocks/vmoodle/locallib.php';

Use \vmoodleadminset_sql\Command_Sql_Exception;

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
 * @param string host The virtual platform to aim.
 * @param string table The table to read.
 * @param string select The value of id or alternative field.
 * @param string fields The fileds to retrieve (optional).
 * @throws Command_Sql_Exception.
 */
function vmoodle_get_field($host, $table, $select, $fields='*') {
    global $CFG, $USER, $DB;

    // Checking capabilities.
    if (!has_capability('block/vmoodle:execute', context_system::instance())) {
        throw new Command_Sql_Exception('unsiffisantcapability');
    }

    // Checking host.
    if (!$DB->get_record('mnet_host', array('wwwroot' => $host))) {
        throw new Command_Sql_Exception('invalidhost');
    }

    // Checking table.
    if (empty($table) || !is_string($table)) {
        throw new Command_Sql_Exception('invalidtable');
    }

    // Checkig select.
    if (empty($select) || (!is_array($select) && !is_int($select))) {
        throw new Command_Sql_Exception('invalidselect');
    }
    if (!is_array($select))  {
        $select = array('id' => $select);
    }

    // Checking field.
    if (empty($fields)) {
        throw new Command_Sql_Exception('invalidfields');
    }

    if (!is_array($fields)) {
        $fields = array($fields);
    }

    // Creating peer.
    $mnet_host = new mnet_peer();
    if (!$mnet_host->bootstrap($host, null, 'moodle')) {
        return (object) array(
                            'status' => MNET_FAILURE,
                            'error' => get_string('couldnotcreateclient', 'vmoodleadminset_sql', $host)
                        );
    }

    // Creating XMLRPC client.
    $rpc_client = new \bock_vmoodle\XmlRpc_Client();
    $rpc_client->add_param($table, 'string');
    $rpc_client->add_param($fields, 'array');
    $rpc_client->add_param($select, 'array');

    // Sending request.
    if (!$rpc_client->send($mnet_host)) {
        if (debugging()) {
            echo '<pre>';
            var_dump($rpc_client);
            echo '</pre>';
        }
    }

    // Returning result.
    return $rpc_client->response;
}

/**
 * Install sqllib plugin library.
 * @return boolean true if the installation is successfull, false otherwise.
 */
function sqllib_install() {
    global $DB, $OUTPUT;

    $result = true;
    $rpc = new stdclass;
    $rpcmap = new stdclass;
    // Retrieve service.

    // Returning result.
    return $result;
}

/**
 * Uninstall sqlib plugin library.
 * @return boolean true if the uninstallation is successfull, false otherwise.
 */
function sqllib_uninstall() {
    // Initializing.
    global $DB, $OUTPUT;

    $result = true;

    // Returning result
    return $result;
}