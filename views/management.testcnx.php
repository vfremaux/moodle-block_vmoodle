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
 * Tests database connection.
 *
 * @package block_vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading $CFG configuration.
require('../../../config.php');
require_once $CFG->dirroot.'/blocks/vmoodle/bootlib.php';

// Retrieve parameters for database connection test.
$database = new stdClass;
$database->vdbtype = required_param('vdbtype', PARAM_TEXT);
$database->vdbhost = required_param('vdbhost', PARAM_TEXT);
$database->vdblogin = required_param('vdblogin', PARAM_TEXT);
$database->vdbpass = required_param('vdbpass', PARAM_TEXT);

// Works, but need to improve the style...
if (vmoodle_make_connection($database, false)) {
    echo(get_string('connectionok', 'block_vmoodle'));
} else {
    echo(get_string('badconnection', 'block_vmoodle'));
}
