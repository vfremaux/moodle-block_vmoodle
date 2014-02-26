<?php

/**
 * This file catches an action and do the corresponding usecase.
 * Called by 'view.php'.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * @usecase redefineservices
 */


// It must be included from 'view.php' in blocks/vmoodle.
if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');
}

// Confirmation message.
$message_object	=	new stdclass();
$message_object->message	=	'';
$message_object->style	=	'notifyproblem';

/**************************** Define or redefine default services strategy ************/
if ($action == 'redefineservices') {

	// Processing.
	$defaultservices = $DB->get_records('mnet_service', array('offer' => 1), 'name');
	if(!empty($defaultservices)){

		require_once(VMOODLE_CLASSES_DIR.'ServicesStrategy_Form.class.php');

		// Retrieve submitted data, from the services strategy form.
		$services_form	=	new Vmoodle_Services_Strategy_Form();
		$submitteddata	=	$services_form->get_data();

		// Saves default services strategy.
		set_config('block_vmoodle_services_strategy', serialize($submitteddata));
		// Every step was SUCCESS.
		$message_object->message = get_string('successstrategyservices', 'block_vmoodle');
		$message_object->style = 'notifysuccess';
	} else {
		$message_object->message = get_string('badservicesnumber', 'block_vmoodle');
	}

	// Save confirm message before redirection.
	$SESSION->vmoodle_ma['confirm_message'] = $message_object;
	header('Location: view.php?view=management');
	return -1;
}