<?php
/**
 * Manage the command wizard.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 	$PAGE->requires->js('/blocks/vmoodle/js/sadmin.js');
 
	// Declaring parameters
	if (isset($SESSION->vmoodle_sa['wizardnow']))
		$wizardnow = $SESSION->vmoodle_sa['wizardnow'];
	else
		$wizardnow = 'commandchoice';
	// Include the step wizard
	switch($wizardnow) {
		case 'commandchoice': {
			$result = include 'sadmin.commandchoice.php';
		}
		break;
		case 'advancedcommand' : {
			$result = include 'sadmin.advancedcommand.php';
		}
		break;
		case 'targetchoice': {
			$result = include 'sadmin.targetchoice.php';
		}
		break;
		case 'report': {
			$result = include 'sadmin.report.php';
		}
		break;
		default: {
			$result = -1;
		}
	}
	// If an error happens
	if ($result == -1) {
	    echo $OUTPUT->footer();
	    exit(0);
	}