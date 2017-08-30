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
 * Define form to input an advanced SQL command.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require_once($CFG->libdir.'/formslib.php');

class AdvancedCommand_Form extends moodleform {

    /**
     * Constructor.
     */
    public function __construct() {
        // Calling parent's constructor.
        parent::__construct('view.php?view=sadmin&what=validateadvancedcommand');
    }

    /**
     * Describes form depending on command.
     */
    public function definition() {

        // Setting variables.
        $mform =& $this->_form;

        // Adding header.
        $mform->addElement('header', null, get_string('advancedmode', 'block_vmoodle'));

        // Adding field.
        $mform->addElement('textarea', 'sqlcommand', get_string('sqlcommand', 'block_vmoodle'), 'wrap="virtual" rows="20" cols="50"');
        $mform->setType('sqlcommand', PARAM_TEXT);
        $mform->addRule('sqlcommand', null, 'required', null, 'client');

        // Adding buttons.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('nextstep', 'block_vmoodle'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', null, array(' '), false);
    }
}