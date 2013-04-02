<?php

require_once($CFG->libdir.'/formslib.php');

/**
 * Define form to input an advanced SQL command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Vmoodle_AdvancedCommand_Form extends moodleform {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Calling parent's constructor
		parent::__construct('view.php?view=sadmin&what=validateadvancedcommand');
	}
	
	/**
	 * Describes form depending on command.
	 */
	public function definition() {
		// Setting variables
		$mform =& $this->_form;
		// Adding header
		$mform->addElement('header', null, get_string('advancedmode', 'block_vmoodle'));
		// Adding field
		$mform->addElement('textarea', 'sqlcommand', get_string('sqlcommand', 'block_vmoodle'), 'wrap="virtual" rows="20" cols="50"');
		// Adding buttons
		$mform->addRule('sqlcommand', null, 'required', null, 'client');
		$buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('nextstep', 'block_vmoodle'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', null, array(' '), false);
	}
}