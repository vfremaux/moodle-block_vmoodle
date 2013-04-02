<?php
/**
 * Chains commands of rolelib plugin library.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 	
 	
 	// Adding requierements
 	require_once('../../../../../config.php');
 	// Adding libraries
 	require_once('../../../locallib.php');
 	require_once('../../../classes/Command_Parameter.class.php');
 	require_once('classes/Command_Role_Compare.class.php');
 	require_once('classes/Command_Role_Capability_Sync.class.php');
 	
 	// Checking login
	require_login();
	
	// Checking rights
	if (!has_capability('block/vmoodle:managevmoodles', get_context_instance(CONTEXT_SYSTEM)))
		error("Only administrators can use this service");

	// Declaring parameters
	$action = optional_param('what', '', PARAM_TEXT);
	
	// Checking action to do
 	switch ($action) {
 		// Run sync role command.
 		case 'syncrole': {
 			// Getting parameters
 			$capability = optional_param('capability', '', PARAM_RAW);
 			$source_platform = optional_param('source_platform', '', PARAM_RAW);
 			$wwwroot_platforms = optional_param('platforms', array(), PARAM_RAW);
 			
 			// Checking platforms
 			$valid = true;
 			$available_plaforms = get_available_platforms();
 			if (!array_key_exists($source_platform, $available_plaforms))
 				$valid = false;
 			else {
 				$platforms = array();
 				foreach($wwwroot_platforms as $wwwroot_platform) {
 					if (!array_key_exists($wwwroot_platform, $available_plaforms)) {
 						$valid = false;
 						break;
 					}
 					$platforms[$wwwroot_platform] = $available_plaforms[$wwwroot_platform];
 				}
 			}
 			if (!$valid)
 				header('Location: '.$CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
 				
 			// Retrieving previous command
 			$command = unserialize($SESSION->vmoodle_sa['command']);
 			if ($SESSION->vmoodle_sa['wizardnow'] != 'report' || !($command instanceof Vmoodle_Command_Role_Compare))
 				header('Location: '.$CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
 			$role = $command->getParameter('role')->getValue();
 			
 			// Saving previous context
 			$SESSION->vmoodle_sa['rolelib']['command'] = $SESSION->vmoodle_sa['command'];
 			$SESSION->vmoodle_sa['rolelib']['platforms'] = $SESSION->vmoodle_sa['platforms'];
 			
 			// Creating RoleSyncCommand
 			$rolesync_command = new Vmoodle_Command_Role_Capability_Sync();
 			$rolesync_command->getParameter('platform')->setValue($source_platform);
 			$rolesync_command->getParameter('role')->setValue($role);
 			$rolesync_command->getParameter('capability')->setValue($capability);
 			
 			// Running command
 			$rolesync_command->run($platforms);
 			
 			// Saving new context
 			$SESSION->vmoodle_sa['command'] = serialize($rolesync_command);
 			$SESSION->vmoodle_sa['platforms'] = $platforms;
 			
 			// Moving to the report
 			header('Location: '.$CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
 		}
 		break;
 		
 		
 		// Going back to role comparison
 		case 'backtocomparison': {
 			// Getting old command
			if (!isset($SESSION->vmoodle_sa['rolelib']['command']) || !isset($SESSION->vmoodle_sa['rolelib']['platforms']) || !($SESSION->vmoodle_sa['rolelib']['command'] instanceof Vmoodle_Command_Role_Compare))
 				header('Location: '.$CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
 			$command = unserialize($SESSION->vmoodle_sa['rolelib']['command']);
 			$platforms = $SESSION->vmoodle_sa['rolelib']['platforms'];
 			
 			// Running command to actualize
 			$command->run($platforms);
 			
 			// Saving new context
 			$SESSION->vmoodle_sa['command'] = serialize($command);
 			$SESSION->vmoodle_sa['platforms'] = $platforms;
 			
 			// Moving to the report
 			header('Location: '.$CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
 		}
 		break;
 		
 		// Redirecting to super admin view
 		default: {
 			header('Location: '.$CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
 		}
 	}