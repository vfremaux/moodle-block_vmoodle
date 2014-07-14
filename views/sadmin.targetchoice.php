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
 * The second step of wizard.
 * Displays available platforms.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading librairies.
Use \block_vmoodle\commands\Command_Form;
Use \block_vmoodle\Target_Filter_Form;
Use \block_vmoodle\Target_Form;

// Getting retrieve platform command.
if (!isset($rpcommand) && isset($SESSION->vmoodle_sa['command'])) {
    $command = unserialize($SESSION->vmoodle_sa['command']);
    $rpcommand = $command->getRPCommand();
} else {
    return -1;
}

// Checking if platforms are already selected.
if (isset($SESSION->vmoodle_sa['platforms'])) {
    $aplatforms = get_available_platforms();
    $splatforms = $SESSION->vmoodle_sa['platforms'];
    // Removing seletected platforms from available platforms.
    foreach ($splatforms as $key => $splatform) {
        unset($aplatforms[$key]);
    }
} else {
    // Leaving form filling selects.
    $aplatforms = null;
    $splatforms = null;
}

// Instantiating forms.
$command_form = new Command_Form($command, Command_Form::MODE_DISPLAY_COMMAND);
$target_filter_form = new Target_Filter_Form();
if (!isset($target_form)) {
    $target_form = new Target_Form(array('aplatforms' => $aplatforms, 'splatforms' => $splatforms));
}
if (!(is_null($rpcommand) || isset($rpcommand_form))) {
    $rpcommand_form  = new Command_Form($rpcommand, Command_Form::MODE_RETRIEVE_PLATFORM);
}

// Display forms.
$command_form->display();
$target_filter_form->display();
$target_form->display();
if (isset($rpcommand_form)) {
    $rpcommand_form->display();
}