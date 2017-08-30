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
 * Redirection to a certain page of Vmoodle management.
 *
 * @package block_vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

// Declaring the page parameter.
$action = optional_param('what', 'list', PARAM_TEXT);

// Templates test, for ADD page.
$templates = vmoodle_get_available_templates();
if ($action == 'add' && empty($templates)) {
    $action = 'list';
}

// Selecting the page.
switch ($action) {
    case 'list': {
        $result = include($CFG->dirroot.'/blocks/vmoodle/views/management.list.php');
    }
    break;
    case 'add': {
        $result = include($CFG->dirroot.'/blocks/vmoodle/views/management.add.php');
    }
    break;
    case 'edit': {
        $result = include($CFG->dirroot.'/blocks/vmoodle/views/management.edit.php');
    }
    break;
    default: {
        $result = -1;
    }
}

// If an error happens.
if ($result == -1) {
    echo $OUTPUT->footer();
    exit(0);
}