<?php

	include_once '../../../config.php';
	require_capability('moodle/site:doanything', context_system::instance());
	$view = optional_param('view', 'cnxs', PARAM_TEXT);
	$vhosts = $DB->get_records('block_vmoodle', array('enabled' => '1'));
	$navlinks = array(array('name' => get_string('overalmonitoring', 'block_vmoodle'), 'type' => 'title', 'link' => ''));
	$PAGE->set_title(get_string('overalmonitoring', 'block_vmoodle'));
	$PAGE->set_heading(get_string('overalmonitoring', 'block_vmoodle'));
	echo $OUTPUT->header();

	/// Print tabs with options for user
	if (!preg_match('/cnxs|roles|users/', $view)) $view = 'cnxs';
	$rows[0][] = new tabobject('cnxs', "view.php?view=cnxs", get_string('cnxs', 'block_vmoodle'));
	$rows[0][] = new tabobject('users', "view.php?view=users", get_string('users','block_vmoodle'));
	$rows[0][] = new tabobject('roles', "view.php?view=roles", get_string('roles','block_vmoodle'));

	print_tabs($rows, $view);

	if ($view == 'cnxs'){
		include 'mnet_general.php';
	}

	if ($view == 'users'){
		include 'mnet_users.php';
	}

	if ($view == 'roles'){
		include 'mnet_roles.php';
	}

echo $OUTPUT->footer();
?>