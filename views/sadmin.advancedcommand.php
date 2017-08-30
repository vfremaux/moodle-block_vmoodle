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
 * The alternative first step of wizard.
 * Input a SQL command.
 *
 * @package block_vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

// Loading the libraries.
require_once($CFG->dirroot.'/blocks/vmoodle/classes/commands/AdvancedCommand_Form.php');
require_once($CFG->dirroot.'/blocks/vmoodle/classes/commands/AdvancedCommand_Upload_Form.php');

// Display forms.
if (!isset($advancedcommand_form)) {
    $advancedcommand_form = new AdvancedCommand_Form();
}
$advancedcommand_form->display();

if (!isset($advancedcommand_upload_form)) {
    $advancedcommand_upload_form = new AdvancedCommand_Upload_Form();
}
$advancedcommand_upload_form->display();