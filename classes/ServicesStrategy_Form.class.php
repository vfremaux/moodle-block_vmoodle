<?php

// Moodle form's library.

require_once($CFG->libdir.'/formslib.php');

/**
 * Define form for editing default services strategy
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Vmoodle_Services_Strategy_Form extends moodleform {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Calling parent's constructor.
		parent::__construct('view.php?view=services&what=redefineservices');
	}

	/**
	 * Describes the form, with the triplet (service_name, publish, subscribe).
	 */
	public function definition() {
		// Global configuration.
		global $CFG, $SESSION, $DB;

		// Settings variables.
		$mform =& $this->_form;
		// Master services.
		$defaultservices	=	$DB->get_records('mnet_service', array('offer' => 1), 'name');
		if(!empty($defaultservices)){
			// Services fieldset.
			$mform->addElement('header', 'servicesform', get_string('servicesformselection', 'block_vmoodle'));

			$group = array();
			$group[]	=	$mform->createElement('static',	'publish', get_string('publish', 'block_vmoodle'), get_string('publish', 'block_vmoodle'));
			$group[]	=	$mform->createElement('static',	'subscribe', get_string('subscribe', 'block_vmoodle'), get_string('subscribe', 'block_vmoodle'));
			$mform->addGroup($group, null, get_string('startingstate', 'block_vmoodle'));

			foreach ($defaultservices as $defaultservice){
				$group = array();
				$group[]	=	$mform->createElement('advcheckbox', $defaultservice->name.'_publish');
				$group[]	=	$mform->createElement('advcheckbox', $defaultservice->name.'_subscribe');
				$group[]	=	$mform->createElement('static', $defaultservice->name.'_description');
				$group[]	=	$mform->createElement('hidden', $defaultservice->name.'_id');
				$mform->setDefault($defaultservice->name.'_description', $defaultservice->description);
				$mform->setDefault($defaultservice->name.'_id',	$defaultservice->id);
				$mform->addGroup($group, null, $defaultservice->name);
				$mform->setType($defaultservice->name.'_id', PARAM_INT);
			}

			// Submit button.
			$mform->addElement('submit', 'submitbutton', get_string('edit'));

			// Already saved values, if existence checks.
			if($services = unserialize(get_config(null, 'block_vmoodle_services_strategy'))){
				foreach ($defaultservices as $defaultservice){
					if(array_key_exists($defaultservice->name, $services)){
						$service	=	$services[$defaultservice->name];
						$mform->setDefault($defaultservice->name.'_publish',	$service->publish);
						$mform->setDefault($defaultservice->name.'_subscribe',	$service->subscribe);
					}
				}
			}
		}
		else {
			// Confirmation message.
			$message_object	=	new stdclass();
			$message_object->message = get_string('badservicesnumber', 'block_vmoodle');
			$message_object->style	=	'notifyproblem';
			// Save confirm message before redirection.
			$SESSION->vmoodle_ma['confirm_message'] = $message_object;
			header('Location: view.php?view=management');
		}
	}
}