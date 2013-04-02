<?php
/**
 * The alternative first step of wizard.
 * Input a SQL command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

	// Loading the library
	require_once(VMOODLE_CLASSES_DIR.'AdvancedCommand_Form.class.php');
	
	// Display forms
	if (!isset($advancedcommand_form))
		$advancedcommand_form = new Vmoodle_AdvancedCommand_Form();
	$advancedcommand_form->display();
	if (!isset($advancedcommand_upload_form))
		$advancedcommand_upload_form = new Vmoodle_AdvancedCommand_Upload_Form();
	$advancedcommand_upload_form->display();