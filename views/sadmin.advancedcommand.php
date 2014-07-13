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
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 
Use \block_vmoodle\commands\AdvancedCommand_Form;
Use \block_vmoodle\commands\AdvancedCommand_Upload_Form;

// Display forms
if (!isset($advancedcommand_form)) {
    $advancedcommand_form = new AdvancedCommand_Form();
}
$advancedcommand_form->display();

if (!isset($advancedcommand_upload_form)) {
    $advancedcommand_upload_form = new AdvancedCommand_Upload_Form();
}
$advancedcommand_upload_form->display();