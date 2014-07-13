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
 * Manage the command wizard.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 $PAGE->requires->js('/blocks/vmoodle/js/sadmin.js');

// Declaring parameters.
if (isset($SESSION->vmoodle_sa['wizardnow'])) {
    $wizardnow = $SESSION->vmoodle_sa['wizardnow'];
} else {
    $wizardnow = 'commandchoice';
}

// Include the step wizard.
switch($wizardnow) {
    case 'commandchoice': {
        $result = include 'sadmin.commandchoice.php';
    }
    break;
    case 'advancedcommand' : {
        $result = include 'sadmin.advancedcommand.php';
    }
    break;
    case 'targetchoice': {
        $result = include 'sadmin.targetchoice.php';
    }
    break;
    case 'report': {
        $result = include 'sadmin.report.php';
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