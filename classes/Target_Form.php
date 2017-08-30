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
 * Define form to choose targets.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace block_vmoodle;

require_once($CFG->libdir.'/formslib.php');

class Target_Form extends \moodleform {

    /**
     * Constructor.
     * @param array $customdata The data about the form such as available platforms (optional).
     */
    public function __construct($customdata=null) {
        parent::__construct('view.php', $customdata, 'post', '', array('onsubmit'=>'submit_target_form()'));
    }

    /**
     * Describes form.
     */
    public function definition() {
        global $CFG;

        // Setting variables.
        $mform =& $this->_form;

        // Define available targets.
        if (isset($this->_customdata['aplatforms'])) {
            $achoices = $this->_customdata['aplatforms'];
            if (empty($achoices)) {
                $achoices = array(get_string('none', 'block_vmoodle'));
            }
        } else {
            $achoices = get_available_platforms();
        }

        // Define selected targets.
        if (isset($this->_customdata['splatforms']) && !empty($this->_customdata['splatforms'])) {
            $schoices = $this->_customdata['splatforms'];
        } else {
            $schoices = array(get_string('none', 'block_vmoodle'));
        }

        // Adding header.
        $mform->addElement('header', 'platformschoice', get_string('virtualplatforms', 'block_vmoodle'));

        // Adding hidden field.
        $mform->addElement('hidden', 'view', 'sadmin');
        $mform->setType('view', PARAM_TEXT);

        $mform->addElement('hidden', 'what', 'sendcommand');
        $mform->setType('what', PARAM_TEXT);

        $mform->addElement('hidden', 'achoices', json_encode($achoices));
        $mform->setType('achoices', PARAM_TEXT);

        // Adding selects group.
        $selectarray = array();
        $selectarray[0] = &$mform->createElement('select', 'aplatforms', get_string('available', 'block_vmoodle'), $achoices, 'size="15"');
        $selectarray[1] = &$mform->createElement('select', 'splatforms', get_string('selected', 'block_vmoodle'), $schoices, 'size="15"');
        $selectarray[0]->setMultiple(true);
        $selectarray[1]->setMultiple(true);
        $mform->addGroup($selectarray, 'platformsgroup', null, ' ', false);

        // Adding platforms buttons group.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('button', null, get_string('addall', 'block_vmoodle'), 'onclick="select_all_platforms(); return false;"');
        $buttonarray[] = &$mform->createElement('button', null, get_string('addtoselection', 'block_vmoodle'), 'onclick="select_platforms(); return false;"');
        $buttonarray[] = &$mform->createElement('button', null, get_string('removefromselection', 'block_vmoodle'), 'onclick="unselect_platforms(); return false;"');
        $buttonarray[] = &$mform->createElement('button', null, get_string('removeall', 'block_vmoodle'), 'onclick="unselect_all_platforms(); return false;"');
        $mform->addGroup($buttonarray);

        // Adding submit buttons group.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('nextstep', 'block_vmoodle'));
        $buttonarray[] = $mform->createElement('cancel', 'cancelbutton', get_string('cancelcommand', 'block_vmoodle'));
        $mform->addGroup($buttonarray);

        // Changing renderer.
        $renderer =& $mform->defaultRenderer();
        $template = '<label class="qflabel" style="vertical-align:top">{label}</label> {element}';
        $renderer->setGroupElementTemplate($template, 'platformsgroup');
    }
}