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

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/blocks/vmoodle/upgradelib.php';

function xmldb_block_vmoodle_install_recovery() {
    xmldb_block_vmoodle_install();
}

function xmldb_block_vmoodle_install() {

    vmoodle_upgrade_subplugins_modules('print_upgrade_part_start', 'print_upgrade_part_end');

    set_config('block_vmoodle_late_install', 1);
}

function xmldb_block_vmoodle_late_install() {
    global $USER, $DB;

    // We need to replace the word "block" with word "blocks".
    $rpcs = $DB->get_records('mnet_remote_rpc', array('pluginname' => 'vmoodle'));

    if (!empty($rpcs)) {
        foreach ($rpcs as $rpc) {
            $rpc->xmlrpcpath = str_replace('block/', 'blocks/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_remote_rpc', $rpc);
        }
    }

    // We need to replace the word "block" with word "blocks".
    $rpcs = $DB->get_records('mnet_rpc',array('pluginname' => 'vmoodle'));

    if (!empty($rpcs)) {
        foreach ($rpcs as $rpc) {
            $rpc->xmlrpcpath = str_replace('block/', 'blocks/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_rpc',$rpc);
        }
    }

    // We need to replace the word "vmoodleadminset/" with real subplugin path "blocks/vmoodle/plugins/".
    $rpcs = $DB->get_records('mnet_remote_rpc', array('plugintype' => 'vmoodleadminset'));

    if (!empty($rpcs)) {
        foreach ($rpcs as $rpc) {
            $rpc->xmlrpcpath = str_replace('vmoodleadminset/', 'blocks/vmoodle/plugins/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_remote_rpc', $rpc);
        }
    }

    // We need to replace the word "block" with word "blocks".
    $rpcs = $DB->get_records('mnet_rpc',array('plugintype' => 'vmoodleadminset'));

    if (!empty($rpcs)) {
        foreach ($rpcs as $rpc) {
            $rpc->xmlrpcpath = str_replace('vmoodleadminset/', 'blocks/vmoodle/plugins/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_rpc',$rpc);
        }
    }
}