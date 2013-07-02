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

    //Loading jQuery 
     $PAGE->requires->js('/blocks/vmoodle/js/lib/jquery-1.7.2.min.js');  
     
    // Loading javascript files
    $PAGE->requires->js('/blocks/vmoodle/js/strings.php');
    $PAGE->requires->js ('/blocks/vmoodle/js/target_choice.js');
    
    $PAGE->requires->css ('/blocks/vmoodle/theme/styles.php');
 
    
	// Declaring parameters
	$view = optional_param('view', 'management', PARAM_TEXT);
	$action = optional_param('what', '', PARAM_TEXT);

	// Checking login
    $system_context = context_system::instance();
	require_login();

                                                       
	$plugins = get_list_of_plugins('/blocks/vmoodle/plugins');
	foreach($plugins as $plugin) {
		if(file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/js/strings.php'))
			$js_file = '/blocks/vmoodle/plugins/'.$plugin.'/js/strings.php';
            $PAGE->requires->js($js_file);
            
		foreach(glob($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/js/*.js') as $file)
			 $PAGE->requires->js( str_replace($CFG->dirroot,"",$file));
	}
   
	// Printing headers
	$strtitle = get_string('vmoodlemanager', 'block_vmoodle');

	$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/vmoodle/theme/styles.php';

	// Generating header
	
	ob_start();
	$PAGE->set_context($system_context);
	$PAGE->set_pagelayout('admin');
	$PAGE->set_title($strtitle);
	$PAGE->set_heading($SITE->fullname);
	/* SCANMSG: may be additional work required for $navigation variable */
	$PAGE->navbar->add($strtitle,'view.php?view='.$view,'misc');
    
    $PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(false);
	$PAGE->set_button('');
	$PAGE->set_headingmenu('');
	
    $url = new moodle_url('/blocks/vmoodle/adminview.php');
    $PAGE->set_url($url,array('view' => $view,'what' => $action));
    
    echo $OUTPUT->header(); 
	
    // Checking rights
	if (!has_capability('block/vmoodle:managevmoodles', context_system::instance()))
		print_error('onlyadministrators', 'block_vmoodle');

	// Adding heading
	echo $OUTPUT->heading(get_string('vmoodleadministration', 'block_vmoodle'));
	// Adding tabs
	$tabname = get_string('tabpoolmanage', 'block_vmoodle');
	$row[] = new tabobject('management', $CFG->wwwroot."/blocks/vmoodle/adminview.php?view=management", $tabname);
	$tabname = get_string('tabpoolsadmin', 'block_vmoodle');
	$row[] = new tabobject('sadmin', $CFG->wwwroot."/blocks/vmoodle/adminview.php?view=sadmin", $tabname);
	$tabname = get_string('tabpoolservices', 'block_vmoodle');
	$row[] = new tabobject('services', $CFG->wwwroot."/blocks/vmoodle/adminview.php?view=services", $tabname);
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
				echo $OUTPUT->footer();
				exit();
			}
		}
		catch(Exception $e) {
			echo $OUTPUT->notification($e->getMessage());
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
	echo $OUTPUT->footer();