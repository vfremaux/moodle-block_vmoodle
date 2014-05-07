<?php

	define('CLI_SCRIPT', true);
	
	require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
	require_once($CFG->libdir.'/adminlib.php');     // various admin-only functions
	require_once($CFG->libdir.'/upgradelib.php');   // general upgrade/install related functions
	require_once($CFG->libdir.'/clilib.php');       // cli only functions
	require_once($CFG->dirroot.'/blocks/vmoodle/locallib.php');
	require_once('clilib.php');         			// vmoodle cli only functions

    // fake an admin identity for all the process
	$USER = get_admin();

	// now get cli options
	list($options, $unrecognized) = cli_get_params(
	    array(
	        'interactive'   	=> false,
	        'help'              => false,
	        'config'            => false,
	        'nodes'             => '',
	        'lint'              => false
	    ),
	    array(
	        'h' => 'help',
	        'i' => 'interactive',
	        'c' => 'config',
	        'n' => 'nodes',
	        'l' => 'lint'
	    )
	);
	
	$interactive = !empty($options['interactive']);
	
	if ($unrecognized) {
	    $unrecognized = implode("\n  ", $unrecognized);
	    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
	}
	
	if ($options['help']) {
	    $help =
	"Command line VMoodle Generator.
	Please note you must execute this script with the same uid as apache!
	
	Options:
	--interactive     	  Blocks on each step and waits for input to continue
	-h, --help            Print out this help
	-c, --config          Define an external config file
	-n, --nodes           A node descriptor CSV file
	-l, --lint            Decodes node file and give a report on nodes to be created.
	
	Example:
	\$sudo -u www-data /usr/bin/php blocks/vmoodle/cli/bulkcreatenodes.php
	"; //TODO: localize - to be translated later when everything is finished
	
	    echo $help;
	    die;
	}
	
	if (empty($CFG->version)) {
	    cli_error(get_string('missingconfigversion', 'debug'));
	}

	// Get all options from config file
	
	if (!empty($options['config'])){
		echo "Loading config : ".$options['config'];
		if (!file_exists($options['config'])){
	    	cli_error(get_string('confignotfound', 'local_sharedresources'));
		}
		$content = file($options['config']);
		foreach($content as $l){
			if (preg_match('/^\s+$/', $l)) continue; // empty lines
			if (preg_match('/^[#\/!;]/', $l)) continue; // comments (any form)
			if (preg_match('/^(.*?)=(.*)$/', $l, $matches)) {
				if (in_array($matches[1], $expectedoptions)){
					$options[trim($matches[1])] = trim($matches[2]);
				}
			} 
		}
	}
	
	if (empty($options['nodes'])){		
	    cli_error(get_string('climissingnodes', 'block_vmoodle'));
	}

	$nodes = vmoodle_parse_csv_nodelist($options['nodes']);	
	
	if ($options['lint']){
		print_object($nodes);
		die;
	}
	
	if (empty($nodes)){
	    cli_error(get_string('cliemptynodelist', 'block_vmoodle'));
	}

	mtrace(get_string('clistart', 'block_vmoodle'));

	foreach($nodes as $n){

		mtrace(get_string('climakenode', 'block_vmoodle', $n->vhostname));
		
		$n->forcedns = 0;
		
		if (!empty($n->vtemplate)){
			mtrace(get_string('cliusingtemplate', 'block_vmoodle', $n->vtemplate));

			if (!vmoodle_exist_template($n->vtemplate)){
				mtrace(get_string('climissingtemplateskip', 'block_vmoodle', $n->vtemplate));
				continue;
			}
		}
		
		if ($DB->get_record('block_vmoodle', array('vhostname' => $n->vhostname))){
			mtrace(get_string('clinodeexistsskip', 'block_vmoodle'));
			continue;
		}
		
		// this launches automatically all steps of the controller.management.php script several times
		// with the "doadd" action and progressing in steps.
		$action = "doadd";
		$SESSION->vmoodledata = $n;
		
		$automation = true;
		
		for ($vmoodlestep = 0 ; $vmoodlestep <= 4; $vmoodlestep++){
			mtrace(get_string('climakestep', 'block_vmoodle', $vmoodlestep));
			$return = include $CFG->dirroot.'/blocks/vmoodle/controller.management.php';
			if ($return == -1){
				cli_error(get_string('cliprocesserror', 'block_vmoodle'));
			}
			if ($interactive){
				$input = readline("Continue (y/n|r) ?\n");
				if ($input == 'r' || $input == 'R'){
					$vmoodlestep--;
				} elseif ($input == 'n' || $input == 'N'){
					echo "finishing\n";
					exit;
				}
			}
		}
	}	
