<?php

// Moodle form's library.

require_once($CFG->libdir.'/formslib.php');
if (file_exists($CFG->libdir.'/pear/HTML/QuickForm/elementgrid.php')){
	require_once($CFG->libdir.'/pear/HTML/QuickForm/elementgrid.php');
} else {
	require_once('__other/elementgrid.php');
}

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
		global $CFG, $SESSION, $DB;

		// Settings variables.
		$mform =& $this->_form;

		// Master services.
		$defaultservices	=	$DB->get_records('mnet_service', array('offer' => 1), 'name');

		// get version info to get real names
		$self_mnet_peer = new mnet_peer();
		$self_mnet_peer->set_id($CFG->mnet_localhost_id);
		$myservices = mnet_get_service_info($self_mnet_peer);

		if(!empty($defaultservices)){
			// Services fieldset.
			$mform->addElement('header', 'servicesform', get_string('servicesformselection', 'block_vmoodle'));

			$grid = &$mform->addElement('elementgrid', 'grid', get_string('mainservicesformselection', 'block_vmoodle'));
			
			$row = array();
			$row[]	=	get_string('publish', 'block_vmoodle');
			$row[]	=	get_string('subscribe', 'block_vmoodle');
			$row[]	=	'';
			$row[]	=	'';

			$grid->setColumnNames($row);

			foreach ($defaultservices as $defaultservice){
				$row = array();
				$row[]	=	$mform->createElement('advcheckbox', 'main_'.$defaultservice->name.'_publish');
				$row[]	=	$mform->createElement('advcheckbox', 'main_'.$defaultservice->name.'_subscribe');
				$row[]	=	$mform->createElement('static', 'main_'.$defaultservice->name.'_description');
				$row[]	=	$mform->createElement('hidden', 'main_'.$defaultservice->name.'_id');
				
				$description = $defaultservice->description;
				if (empty($description)){
		            $version = current($myservices[$defaultservice->name]);
		            $langmodule =
		                ($version['plugintype'] == 'mod'
		                    ? ''
		                    : ($version['plugintype'] . '_'))
		                . $version['pluginname']; // TODO there should be a moodle-wide way to do this
		            $description = get_string($defaultservice->name.'_name', $langmodule);
				}
				
				$mform->setDefault('main_'.$defaultservice->name.'_description', $description);
				$mform->setDefault('main_'.$defaultservice->name.'_id',	$defaultservice->id);
				$mform->setType('main_'.$defaultservice->name.'_id', PARAM_INT);
				$grid->addRow($row);
			}

			// Services fieldset.

			$grid = &$mform->addElement('elementgrid', 'grid', get_string('peerservicesformselection', 'block_vmoodle'));
			
			$row = array();
			$row[]	=	get_string('publish', 'block_vmoodle');
			$row[]	=	get_string('subscribe', 'block_vmoodle');
			$row[]	=	'';
			$row[]	=	'';

			$grid->setColumnNames($row);

			foreach ($defaultservices as $defaultservice){
				$row = array();
				$row[]	=	$mform->createElement('advcheckbox', 'peer_'.$defaultservice->name.'_publish');
				$row[]	=	$mform->createElement('advcheckbox', 'peer_'.$defaultservice->name.'_subscribe');
				$row[]	=	$mform->createElement('static', 'peer_'.$defaultservice->name.'_description');
				$row[]	=	$mform->createElement('hidden', 'peer_'.$defaultservice->name.'_id');

				$description = $defaultservice->description;
				if (empty($description)){
		            $version = current($myservices[$defaultservice->name]);
		            $langmodule =
		                ($version['plugintype'] == 'mod'
		                    ? ''
		                    : ($version['plugintype'] . '_'))
		                . $version['pluginname']; // TODO there should be a moodle-wide way to do this
		            $description = get_string($defaultservice->name.'_name', $langmodule);
				}
				
				$mform->setDefault('peer_'.$defaultservice->name.'_description', $description);
				$mform->setDefault('peer_'.$defaultservice->name.'_id',	$defaultservice->id);
				$mform->setType('peer_'.$defaultservice->name.'_id', PARAM_INT);
				$grid->addRow($row);
			}

			/*
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
			*/

			// Submit button.
			$mform->addElement('submit', 'submitbutton', get_string('edit'));

		} else {
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