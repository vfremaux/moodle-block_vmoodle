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

function xmldb_block_vmoodle_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $result = true;
    $dbman = $DB->get_manager();

    // Moodle 2.0 Upgrade break.
    if ($oldversion < 2014081300) {

        // Changing precision of field vdbpass on table block_vmoodle to (32).
        $table = new xmldb_table('block_vmoodle');
        $field = new xmldb_field('vdbpass', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'vdblogin');

        // Launch change of precision for field vdbpass.
        $dbman->change_field_precision($table, $field);

        // Vmoodle savepoint reached.
        upgrade_block_savepoint(true, 2014081300, 'vmoodle');
    }

    return $result;
}
