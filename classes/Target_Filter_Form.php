<?php

require_once($CFG->libdir.'/formslib.php');

namespace block_vmoodle;

/**
 * Define forms to filter platforms..
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Target_Filter_Form extends moodleform {

    /**
     * Describes form.
     */
    public function definition() {
        // Setting variables
        $mform = &$this->_form;
        $filtertype = array(
                        'contains' => get_string('contains', 'block_vmoodle'),
                        'notcontains' => get_string('notcontains', 'block_vmoodle'),
                        'regexp' => get_string('regexp', 'block_vmoodle')
                    );
        
        // Adding fieldset
        $mform->addElement('header', 'pfilterform', get_string('filter', 'block_vmoodle'));
        // Adding group
        $filterarray = array();
        $filterarray[] = &$mform->createElement('select', 'filtertype', null, $filtertype);
        $filterarray[] = &$mform->createElement('text', 'filtervalue', null, 'size="25"');
        $filterarray[] = &$mform->createElement('submit', null, get_string('filter', 'block_vmoodle'), 'onclick="add_filter(); return false;"');
        $mform->addGroup($filterarray, 'filterparam', get_string('platformname', 'block_vmoodle'));
    }
}