<?php

namespace block_vmoodle\commands;

require_once ($CFG->libdir.'/formslib.php');

/**
 * Define form to upload a SQL script.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class AdvancedCommand_Upload_Form extends \moodleform {
    /**
     * Constructor.
     */
    public function __construct() {
        // Calling parent's constructor
        parent::__construct('view.php?view=sadmin&what=uploadsqlscript');
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
        $mform->addElement('file', 'script', get_string('sqlfile', 'block_vmoodle'));
        $mform->setType('script', PARAM_FILE);

        // Adding submit button
        $mform->addElement('submit', 'uploadbutton', get_string('uploadscript', 'block_vmoodle'));
    }
}