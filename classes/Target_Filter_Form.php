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
 * Define forms to filter platforms..
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace block_vmoodle;

require_once($CFG->libdir.'/formslib.php');

class Target_Filter_Form extends \moodleform {

    /**
     * Describes form.
     */
    public function definition() {

        // Setting variables.
        $mform = &$this->_form;
        $filtertype = array(
                        'contains' => get_string('contains', 'block_vmoodle'),
                        'notcontains' => get_string('notcontains', 'block_vmoodle'),
                        'regexp' => get_string('regexp', 'block_vmoodle')
                    );

        // Adding fieldset.
        $mform->addElement('header', 'pfilterform', get_string('filter', 'block_vmoodle'));

        // Adding group.
        $filterarray = array();
        $filterarray[] = &$mform->createElement('select', 'filtertype', null, $filtertype);
        $filterarray[] = &$mform->createElement('text', 'filtervalue', null, 'size="25"');
        $filterarray[] = &$mform->createElement('submit', null, get_string('filter', 'block_vmoodle'), 'onclick="add_filter(); return false;"');
        $mform->addGroup($filterarray, 'filterparam', get_string('platformname', 'block_vmoodle'), '', false);
        $mform->setType('filtervalue', PARAM_TEXT);
    }
}