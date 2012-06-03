<?php
/**
 * admin.php
 * 
 * This file is the administration page of super-administration.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

	// Adding autoloader
	require_once('autoloader.php');

	// Adding requirements
	require_once('../../config.php');
	require_once($CFG->dirroot.'/blocks/vmoodle/locallib.php');
	
	// Checking login
	require_login();
	
	// Printing headers
	$strtitle = get_string('vmoodlemanager', 'block_vmoodle');
	$navigation = build_navigation(array(
									array(
										'name' => $strtitle,
										'link' => 'view.php?view=sadmin',
										'type' =>'misc'
									),
									array(
										'name' => get_string('administration', 'block_vmoodle'),
										'link' => 'view.php',
										'type' => 'misc')
								));
	print_header($strtitle, $SITE->fullname, $navigation, '', '', false, '', '', false); 
	
	// Checking rights
	if (!has_capability('block/vmoodle:managevmoodles', get_context_instance(CONTEXT_SYSTEM)))
		error("Only administrators can use this service");
		
	// Getting action
	$action = optional_param('action');
	switch ($action) {
		case 'enablecommands':
			// Getting commands plugin path
			$commands = optional_param('commands');
			if (is_null($commands)) {
				notify(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			$plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/'.$commands;
			$new_plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/'.substr($commands, 1);
			
			// Checking if plugin exists
			if (!is_dir($plugin_path)) {
				notify(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			
			// Enabling plugin
			if (!rename($plugin_path, $new_plugin_path)) {
				notify(get_string('pluginnotenabled', 'block_vmoodle'));
				break;
			}
			notify(get_string('pluginenabled', 'block_vmoodle'), 'notifysuccess');
			echo '<center>';
			print_single_button('admin.php', array());
			echo '</center>';
			print_footer();
			exit();
			
		case 'disablecommands':
			// Getting commands plugin path
			$commands = optional_param('commands');
			if (is_null($commands)) {
				notify(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			$plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/'.$commands;
			$new_plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/_'.$commands;
			
			// Checking if plugin exists
			if (!is_dir($plugin_path)) {
				notify(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			
			// Disabling plugin
			if (!rename($plugin_path, $new_plugin_path)) {
				notify(get_string('pluginnotdisabled', 'block_vmoodle'));
				break;
			}
			notify(get_string('plugindisabled', 'block_vmoodle'), 'notifysuccess');
			echo '<center>';
			print_single_button('admin.php', array());
			echo '</center>';
			print_footer();
			exit();
		
		case 'uninstallplugin':
			// Getting plugin
			$plugin = optional_param('plugin');
			if (is_null($plugin) || !file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/lib.php')) {
				notify(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			
			// Loading plugin library
			include_once($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/lib.php');
			
			// Removing plugin library
			$uninstall_function = $plugin.'_uninstall';
			if ((function_exists($uninstall_function) && !$uninstall_function()) || 					!unset_config('vmoodle_lib_'.$plugin.'_version')) {
				notify(get_string('pluginnotuninstalled', 'block_vmoodle', $plugin));
				break;
			}
			notify(get_string('pluginuninstalled', 'block_vmoodle'), 'notify_success', $plugin);
			echo '<center>';
			print_single_button('admin.php', array());
			echo '</center>';
			print_footer();
			exit();
	}
	
	
	// Retrieving commands plugins
	$assistedcommands_conffiles = glob($CFG->dirroot.'/blocks/vmoodle/plugins/*/config.php');
	
	// Creating table
	$table = new stdclass;
	$table->head = array('<b>'.get_string('commands', 'block_vmoodle').'</b>', '<b>'.get_string('operation', 'block_vmoodle').'</b>');
	$table->align = array('LEFT', 'CENTER');
	$table->size = array('70%', '30%');
	$table->width = '80%';
	
	// Adding commands plugins
	foreach ($assistedcommands_conffiles as $conffile) {
		$path = explode('/', $conffile);
		$category = $path[count($path)-2];
		$vmoodle_category = load_vmplugin($category);
		$table->data[] = array(
							$vmoodle_category->getName().'<br/> > '.$vmoodle_category->count().' '.get_string('elements', 'block_vmoodle'),
							($category[0] == '_' ?
								print_single_button('admin.php', array('action' => 'enablecommands', 'commands' => $category), get_string('enable'), 'get', '_self', true) :
								print_single_button('admin.php', array('action' => 'disablecommands', 'commands' => $category), get_string('disable'), 'get', '_self', true)
							)
						);
	}
	
	// Displaying commands plugins
	print_heading(get_string('commandsadministration', 'block_vmoodle'));
	echo '<br/>';
	print_table($table);
	echo '<br/>';
	
	// Retrieving vmoodle plugins
	$plugins = get_list_of_plugins('/blocks/vmoodle/plugins/libs');
	foreach($plugins as $key => $plugin) {
		if (!get_record('config', 'name', 'vmoodle_lib_'.$plugin.'_version'))
			unset($plugins[$key]);
	}
	
	// Creating table
	$table = new stdclass;
	$table->head = array('<b>'.get_string('plugin', 'block_vmoodle').'</b>', '<b>'.get_string('operation', 'block_vmoodle').'</b>');
	$table->align = array('LEFT', 'CENTER');
	$table->size = array('70%', '30%');
	$table->width = '80%';
	
	// Adding plugins
	foreach ($plugins as $plugin) {
		$table->data[] = array($plugin, print_single_button('admin.php', array('action' => 'uninstallplugin', 'plugin' => $plugin), get_string('uninstall', 'block_vmoodle'), 'get', '_self', true));
	}
	
	// Displaying plugins
	print_heading(get_string('pluginsadministration', 'block_vmoodle'));
	echo '<br/>';
	print_table($table);
	
	// Adding go back menu
	echo '<br/><center>';
	print_single_button('view.php', array('view' => 'sadmin'), get_string('tabpoolsadmin', 'block_vmoodle'));
	echo '</center>';
	
	// Adding footer
	print_footer();