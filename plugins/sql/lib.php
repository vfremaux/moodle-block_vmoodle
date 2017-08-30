<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This library provides SQL commands for the meta-administration.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

defined('VMOODLE_CLASSSES_DIR') || require_once($CFG->dirroot.'/blocks/vmoodle/lib.php');

use \vmoodleadminset_sql\Command_Sql_Exception;

/**
 * Get fields values of a virtual platform via MNET service.
 * @param string $host The virtual platform to aim.
 * @param string $table The table to read.
 * @param mixed $select The value of id or alternative field.
 * @param string $fields The fileds to retrieve (optional).
 * @throws Vmoodle_Command_Sql_Exception.
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

    // Checking select.
    if (empty($select) || (!is_array($select) && !is_int($select))) {
        throw new Command_Sql_Exception('invalidselect');
    }
    if (!is_array($select)) {
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

    // Returning result.
    return $result;
}