<?php
/**
 * view.php
 * 
 * This file is the main page of vmoodle module which deals with
 * management et super-administration controlers.
 *
 * @package block-vmoodle
 * @category blocks
 */
	// include("debugging.php");
	
	// Adding autoloader
	require_once('autoloader.php');
	
	// Adding requierements
	require_once('../../config.php');
	require_once($CFG->dirroot.'/blocks/vmoodle/locallib.php');
	require_once($CFG->dirroot.'/blocks/vmoodle/debuglib.php');
	require_once($CFG->dirroot.'/mnet/lib.php');
	
	// Declaring parameters
	$view = optional_param('view', 'management', PARAM_TEXT);
	$action = optional_param('what', '', PARAM_TEXT);
	
	// Checking login
	require_login();
	
	// Loading javascript files
	$js = array(
		$CFG->wwwroot.'/blocks/vmoodle/js/strings.php',
		$CFG->wwwroot.'/blocks/vmoodle/js/target_choice.js'
	);
	$plugins = get_list_of_plugins('/blocks/vmoodle/plugins/libs');
	foreach($plugins as $plugin) {
		if(file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/js/strings.php'))
			$js[] = $CFG->wwwroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/js/strings.php';
		foreach(glob($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/js/*.js') as $file)
			$js[] = str_replace($CFG->dirroot, $CFG->wwwroot, $file);
	}
	require_js($js);

	// Printing headers
	$strtitle = get_string('vmoodlemanager', 'block_vmoodle');
	$navigation = build_navigation(array(array(
									'name' => $strtitle,
									'link' => 'view.php?view='.$view,
									'type' =>'misc'
								)));
	$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/vmoodle/theme/styles.php';
	// Generating header
	ob_start();
	print_header($strtitle, $SITE->fullname, $navigation, '', '', false, '', '', false); 
	
	// Checking rights
	if (!has_capability('block/vmoodle:managevmoodles', get_context_instance(CONTEXT_SYSTEM)))
		error("Only administrators can use this service");
		
	// Adding heading
	print_heading(get_string('vmoodleadministration', 'block_vmoodle'));
	
	// Adding tabs
	$tabname = get_string('tabpoolmanage', 'block_vmoodle');
	$row[] = new tabobject('management', $CFG->wwwroot."/blocks/vmoodle/view.php?view=management", $tabname);
	$tabname = get_string('tabpoolsadmin', 'block_vmoodle');
	$row[] = new tabobject('sadmin', $CFG->wwwroot."/blocks/vmoodle/view.php?view=sadmin", $tabname);
	$tabname = get_string('tabpoolservices', 'block_vmoodle');
	$row[] = new tabobject('services', $CFG->wwwroot."/blocks/vmoodle/view.php?view=services", $tabname);
	$tabrows[] = $row;
	print_tabs($tabrows, $view);

	// Capturing action
	if ($action != '') {
		try {
			switch ($view) {
				case 'management': {
					$result = include 'controller.management.php';
				}
				break;
				case 'sadmin': {
					$result = include 'controller.sadmin.php';
				}
				break;
				case 'services': {
					$result = include 'controller.services.php';
				}
				break;
				default: {
					$result = -1;
				}
			}		
			if ($result == -1) {
				print_footer();
				exit();
			}
		}
		catch(Exception $e) {
			notify($e->getMessage());
		}
	}
	
	// Displaying headers
	ob_end_flush();
	
	// Including contents
	switch($view) {
		case 'management': {
			include $CFG->dirroot.'/blocks/vmoodle/views/management.main.php';
		}
		break;
		case 'sadmin': {
			include $CFG->dirroot.'/blocks/vmoodle/views/sadmin.main.php';
		}
		break;
		case 'services': {
			include $CFG->dirroot.'/blocks/vmoodle/views/services.main.php';
		}
		break;
	}
	
 	// Adding footer
	print_footer();