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
	$PAGE->set_title($strtitle);
	$PAGE->set_heading($SITE->fullname);
	/* SCANMSG: may be additional work required for $navigation variable */
	$PAGE->set_focuscontrol('');
	$PAGE->set_cacheable(false);
	$PAGE->set_button('');
	$PAGE->set_headingmenu('');
	echo $OUTPUT->header(); 
	// Checking rights
	if (!has_capability('block/vmoodle:managevmoodles', context_system::instance()))
		print_error('onlyadministrators', 'block_vmoodle');
	// Getting action
	$action = optional_param('action');
	switch ($action) {
		case 'enablecommands':
			// Getting commands plugin path
			$commands = optional_param('commands');
			if (is_null($commands)) {
				echo $OUTPUT->notification(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			$plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/'.$commands;
			$new_plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/'.substr($commands, 1);
			// Checking if plugin exists
			if (!is_dir($plugin_path)) {
				echo $OUTPUT->notification(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			// Enabling plugin
			if (!rename($plugin_path, $new_plugin_path)) {
				echo $OUTPUT->notification(get_string('pluginnotenabled', 'block_vmoodle'));
				break;
			}
			echo $OUTPUT->notification(get_string('pluginenabled', 'block_vmoodle'), 'notifysuccess');
			echo '<center>';
			echo $OUTPUT->single_button('admin.php', 'OK', 'get');
			echo '</center>';
			echo $OUTPUT->footer();
			exit();
		case 'disablecommands':
			// Getting commands plugin path
			$commands = optional_param('commands');
			if (is_null($commands)) {
				echo $OUTPUT->notification(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			$plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/'.$commands;
			$new_plugin_path = $CFG->dirroot.'/blocks/vmoodle/plugins/_'.$commands;
			// Checking if plugin exists
			if (!is_dir($plugin_path)) {
				echo $OUTPUT->notification(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			// Disabling plugin
			if (!rename($plugin_path, $new_plugin_path)) {
				echo $OUTPUT->notification(get_string('pluginnotdisabled', 'block_vmoodle'));
				break;
			}
			echo $OUTPUT->notification(get_string('plugindisabled', 'block_vmoodle'), 'notifysuccess');
			echo '<center>';
			echo $OUTPUT->single_button('admin.php', 'OK', 'get');
			echo '</center>';
			echo $OUTPUT->footer();
			exit();
		case 'uninstallplugin':
			// Getting plugin
			$plugin = optional_param('plugin');
			if (is_null($plugin) || !file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/lib.php')) {
				echo $OUTPUT->notification(get_string('wrongplugin', 'block_vmoodle'));
				break;
			}
			// Loading plugin library
			include_once($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/lib.php');
			// Removing plugin library
			$uninstall_function = $plugin.'_uninstall';
			if ((function_exists($uninstall_function) && !$uninstall_function()) || 					!unset_config('vmoodle_lib_'.$plugin.'_version')) {
				echo $OUTPUT->notification(get_string('pluginnotuninstalled', 'block_vmoodle', $plugin));
				break;
			}
			echo $OUTPUT->notification(get_string('pluginuninstalled', 'block_vmoodle'), 'notify_success');
			echo '<center>';
			echo $OUTPUT->single_button('admin.php', 'OK', 'get');
			echo '</center>';
			echo $OUTPUT->footer();
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
								$OUTPUT->single_button(new moodle_url('admin.php', array('action' => 'enablecommands', 'commands' => $category)), get_string('enable'), 'get') :
								$OUTPUT->single_button(new moodle_url('admin.php', array('action' => 'disablecommands', 'commands' => $category)), get_string('disable'), 'get')
							)
						);
	}
	// Displaying commands plugins
	echo $OUTPUT->heading(get_string('commandsadministration', 'block_vmoodle'));
	echo '<br/>';
	echo html_writer::table($table);
	echo '<br/>';
	// Retrieving vmoodle plugins
	$plugins = get_list_of_plugins('/blocks/vmoodle/plugins');
	foreach($plugins as $key => $plugin) {
		if (!$DB->get_record('config', array('name' => 'vmoodle_lib_'.$plugin.'_version')))
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
		$table->data[] = array($plugin, $OUTPUT->single_button(new moodle_url('admin.php', array('action' => 'uninstallplugin', 'plugin' => $plugin)), get_string('uninstall', 'block_vmoodle'), 'get'));
	}
	// Displaying plugins
	echo $OUTPUT->heading(get_string('pluginsadministration', 'block_vmoodle'));
	echo '<br/>';
	echo html_writer::table($table);
	// Adding go back menu
	echo '<br/><center>';
	echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin')), get_string('tabpoolsadmin', 'block_vmoodle'), 'get');
	echo '</center>';
	// Adding footer
	echo $OUTPUT->footer();