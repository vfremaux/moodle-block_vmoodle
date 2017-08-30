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
 * Define form to upload a SQL script.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require_once ($CFG->libdir.'/formslib.php');

class AdvancedCommand_Upload_Form extends moodleform {
    /**
     * Constructor.
     */
    public function __construct() {
        // Calling parent's constructor
        parent::__construct(new moodle_url('/blocks/vmoodle/view.php', array('view' => 'sadmin', 'what' => 'uploadsqlscript')));
    }

    /**
     * Describes form depending on command.
     */
    public function definition() {
        // Setting variables.
        $mform =& $this->_form;

        // Adding header
        $mform->addElement('header', null, get_string('uploadscript', 'block_vmoodle'));

        // Adding field
        // $mform->addElement('file', 'script', get_string('sqlfile', 'block_vmoodle'));
        $mform->createElement('filepicker', 'script', get_string('sqlfile', 'block_vmoodle'), array('accepted_types' => '.sql'));

        // Adding submit button
        $mform->addElement('submit', 'uploadbutton', get_string('uploadscript', 'block_vmoodle'));
    }
}