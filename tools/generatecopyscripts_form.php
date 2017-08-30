<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class CopyScriptsParams_Form extends moodleform {

    function definition() {
        $mform = $this->_form;

        $mform->addelement('header', 'maindbhead', get_string('maindb', 'block_vmoodle'));
        $mform->setExpanded('maindbhead');
        $mform->addElement('text', 'fromversion', get_string('fromversion', 'block_vmoodle'), '');
        $mform->setType('fromversion', PARAM_TEXT);

        $mform->addElement('text', 'toversion', get_string('toversion', 'block_vmoodle'), '');
        $mform->setType('toversion', PARAM_TEXT);

        $mform->addelement('header', 'cronlineshead', get_string('cronlines', 'block_vmoodle'));
        $mform->setExpanded('cronlineshead');
        $cronoptions = array('cli' => get_string('clioperated', 'block_vmoodle'), 'web' => get_string('weboperated', 'block_vmoodle'));
        $mform->addElement('select', 'cronmode', get_string('cronmode', 'block_vmoodle'), $cronoptions);
        $mform->setType('cronmode', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('generate', 'block_vmoodle'));
    }
}