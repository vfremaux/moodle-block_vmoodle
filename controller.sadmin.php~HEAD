<?php
/**
 * Manages the wizard of pool administration.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */
 
 	// Checking if is included from view.php in blocks/vmoodle
 	if (!defined('MOODLE_INTERNAL'))
	    die('Direct access to this script is forbidden.');
 	
 	// Checking action to do
 	switch ($action) {
 		// Validating the assisted command
 		case 'validateassistedcommand': {
 			// Loading librairy
 			require_once(VMOODLE_CLASSES_DIR.'Command_Category.class.php');
 			require_once(VMOODLE_CLASSES_DIR.'Command.class.php');
 			require_once(VMOODLE_CLASSES_DIR.'Command_Form.class.php');
 			
 			// Checking the neeed values
 			$category = optional_param('category_plugin_name', null);
 			$index = optional_param('command_index', -1, PARAM_INT);
 			if (is_null($category) || $index < 0)
 				return 0;
 			
 			// Loading command's category
 			if (is_dir(VMOODLE_PLUGINS_DIR.$category) && is_readable(VMOODLE_PLUGINS_DIR.$category.'/config.php'))
 				$command_category = load_vmplugin($_POST['category_plugin_name']);
 			else
 				return 0;

 			// Invoking a form
 			try {
 				$command = $command_category->getCommands($index);
 			} catch (Vmoodle_Command_Exception $vce) {
				return 0;
			}
 			$command_form = new Vmoodle_Command_Form($command, Vmoodle_Command_Form::MODE_COMMAND_CHOICE);
 			if (!($data = $command_form->get_data()))
 				return 0;
 				
 			// Setting parameters' values
 			try {
 				$command->populate($data);
 			} catch(Exception $exception) {
 				$message = $exception->getMessage();
 				if (empty($message))
 					$message = get_string('unablepopulatecommand', 'block_vmoodle'); 
 				notify($message);
 				unset($_POST);		// Done to remove form information. Otherwise, it crack all forms..
 				return 0;
 			}
 			
 			// Record the wizard status
 			$SESSION->vmoodle_sa['command'] = serialize($command);
 			$SESSION->vmoodle_sa['wizardnow'] = 'targetchoice';

			// Move to the next step
			header('Location: view.php?view=sadmin');
 		}
 		break;
 		
 		
 		// Switching to the advanced mode
 		case 'switchtoadvancedcommand': {
 		    $SESSION->vmoodle_sa['wizardnow'] = 'advancedcommand';
 		    header('Location: view.php?view=sadmin');
 		}
 		break;
 		
 		
 		// Validating the advanced command
 		case 'validateadvancedcommand': {
 			// Loading librairy
 			require_once(VMOODLE_CLASSES_DIR.'AdvancedCommand_Form.class.php');
 			
 			// Invoking form
 			$advancedcommand_form = new Vmoodle_AdvancedCommand_Form();
 			// Checking if the fom is cancelled
 			if ($advancedcommand_form->is_cancelled()) {
 			    $SESSION->vmoodle_sa['wizardnow'] = 'commandchoice';
 				header('Location: view.php?view=sadmin');
 				return -1;
 			}
 			// Checking sql command
 			if (!($data = $advancedcommand_form->get_data(false))){
 				return 0;
 			}
 			
 			// Creating a Vmoodle_Command_Sql
			$command = new Vmoodle_Command_Sql(
 							get_string('manualcommand', 'block_vmoodle'),
 							get_string('manualcommand', 'block_vmoodle'),
 							$data->sqlcommand
 						);
 			
 			// Record the wizard status
 			$SESSION->vmoodle_sa['command'] = serialize($command);
 			$SESSION->vmoodle_sa['wizardnow'] = 'targetchoice';

			// Move to the next step
			header('Location: view.php?view=sadmin');
 		}
 		break;
 		
 		
 		// Uploading a SQL script to fill Vmoodle_Command_Sql
 		case 'uploadsqlscript': {
 			// Loading librairy
 			require_once(VMOODLE_CLASSES_DIR.'AdvancedCommand_Form.class.php');
 			require_once(VMOODLE_CLASSES_DIR.'AdvancedCommand_Upload_Form.class.php');
 			
 			// Checking uploaded file
 			$advancedcommand_form = new Vmoodle_AdvancedCommand_Form();
 			$advancedcommand_upload_form = new Vmoodle_AdvancedCommand_Upload_Form();
 			if ($file_content = $advancedcommand_upload_form->get_file_content('script')){
 				$advancedcommand_form->set_data(array('sqlcommand' => $file_content));
 			}
 		}
	 	break;
	 	
	 	
	 	// Getting available platforms by their original value.
	 	case 'gettargetbyvalue': {
	 		// Including requierements
	 		require_once VMOODLE_CLASSES_DIR.'Command_Form.class.php';
	 		require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';
	 		
	 		// Checking command
 			if (!isset($SESSION->vmoodle_sa['command'])) {
 				$SESSION['vmoodle_sa']['wizardnow'] = 'commandchoice';
 				return 0;
 			}
 				
 			// Getting retrieve platforms command
 			$command = unserialize($SESSION->vmoodle_sa['command']);
 			$rpcommand = $command->getRPCommand();
 			if (is_null($rpcommand)){
 				return 0;
 			}
 			
 			// Invoking form
 			$rpcommand_form = new Vmoodle_Command_Form($rpcommand, Vmoodle_Command_Form::MODE_RETRIEVE_PLATFORM);
 			// Checking if form is submitted
 			if (!($data = $rpcommand_form->get_data())){
 				return 0;
 			}
 			// Setting parameters' values
 			$rpcommand->populate($data);
 			
 			// Sending command on available platforms
 			$platforms = get_available_platforms();
 			$rpcommand->setReturned(true);
 			$rpcommand->run($platforms);
 			
 			// Removing failed platforms
 			foreach($platforms as $host => $platform) {
 				if (!($rpcommand->getResult($host, 'status') == RPC_SUCCESS && $rpcommand->getResult($host, 'value'))){
 					unset($platforms[$host]);
 				}
 			}
 			
 			// Saving selected platforms in session
 			$SESSION->vmoodle_sa['platforms'] = $platforms;
	 		
	 		// Moving to current step
	 		@header('Location: view.php?view=sadmin');		// Adding @ due to debugging features
	 	}
	 	break;
	 	
	 	
	 	// Sending command on virtual platforms
	 	case 'sendcommand': {
	 		// Loading library
	 		require_once(VMOODLE_CLASSES_DIR.'Target_Form.class.php');
	 		
	 		// Inviking form
	 		$target_form = new Vmoodle_Target_Form();
	 		// Checking if form is canceled
	 		if ($target_form->is_cancelled()) {
	 			unset($SESSION->vmoodle_sa);
 				header('Location: view.php?view=sadmin');
 				return -1;
 			}
	 		// Checking data
	 		if (!($data = $target_form->get_data())){
 				return 0;
 			}
 			// Getting platforms // BUGFIX not found why splatforms dont' come into get_data()
 			$form_platforms = optional_param('splatforms', array(), PARAM_URL);
 			if (empty($form_platforms) || (count($form_platforms) == 1 && $form_platforms[0] == '0')){
 				throw new Vmoodle_Command_Exception('noplatformchosen');
 			}
 			$platforms = array();
 			$all_platforms = get_available_platforms();
 			foreach ($form_platforms as $platform_root){
 				$platforms[$platform_root] = $all_platforms[$platform_root];
 			}
 				
 			// Checking command
 			if (!isset($SESSION->vmoodle_sa['command'])) {
 				$SESSION['vmoodle_sa']['wizardnow'] = 'commandchoice';
 				return 0;
 			}
 			// Running command
 			$command = unserialize($SESSION->vmoodle_sa['command']);
 			$command->run($platforms);
 			$SESSION->vmoodle_sa['command'] = serialize($command);
 			
 			// Saving results to display
 			$SESSION->vmoodle_sa['platforms'] = $platforms;
 			$SESSION->vmoodle_sa['wizardnow'] = 'report';
 			
 			// Move to the next step
			@header('Location: view.php?view=sadmin');		// Adding @ due to debugging features
	 	}
	 	break;
	 	
	 	
	 	// Clean up wizard session to run a new command
	 	case 'newcommand': {
	 		unset($SESSION->vmoodle_sa);
	 		header('Location: view.php?view=sadmin');
	 	}
	 	break;
	 	
	 	
	 	// Run command again on other platforms
	 	case 'runotherpfm': {
	 		// Removing selected platforms from session
	 		if (isset($SESSION->vmoodle_sa['platforms'])) {
	 			unset($SESSION->vmoodle_sa['platforms']);
	 			$command = unserialize($SESSION->vmoodle_sa['command']);
	 			$command->clearResult();
	 			$SESSION->vmoodle_sa['command'] = serialize($command);
	 		}
	 		
	 		// Modifying wizard state
	 		$SESSION->vmoodle_sa['wizardnow'] = 'targetchoice';
	 		
	 		// Move to the step
	 		header('Location: view.php?view=sadmin');
	 	}
	 	break;
	 	
	 	
	 	// Run an other command on selected platforms
	 	case 'runothercmd': {
	 		// Removing selected command from session
	 		if (isset($SESSION->vmoodle_sa['command'])){
	 			unset($SESSION->vmoodle_sa['command']);
	 		}
	 			
	 		// Modifying wizard state
	 		$SESSION->vmoodle_sa['wizardnow'] = 'commandchoice';
	 		
	 		// Move to the step
	 		header('Location: view.php?view=sadmin');
	 	}
	 	break;
	 	
	 	
	 	// Run the command again on a platform
	 	case 'runcmdagain': {
	 		// Checking wizard session
	 		if (!isset($SESSION->vmoodle_sa['command'], $_GET['platform'])){
	 			return -1;
	 		}
	 		
	 		// Getting command
	 		$command = unserialize($SESSION->vmoodle_sa['command']);
	 		// Getting platform
	 		$platform = urldecode($_GET['platform']);
	 		if (!array_key_exists($platform, get_available_platforms())){
	 			return -1;
	 		}
	 			
	 		// Running command
	 		$command->run(array($platform));
	 		// Saving result
	 		$SESSION->vmoodle_sa['command'] = serialize($command);
	 		
	 		// Moving to report step
	 		@header('Location: view.php?view=sadmin');		// Adding @ due to debugging features
	 	}
	 	break;
	}