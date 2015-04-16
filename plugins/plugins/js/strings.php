<?php
header('Content-Type: application/x-javascript');
require_once('../../../../../config.php');
require_once($CFG->dirroot.'/blocks/vmoodle/lib.php');
echo 'var vmoodle_pluginlib_notinstalled = "'.vmoodle_get_string('notinstalled', 'vmoodleadminset_plugins').'"; ';
echo 'var vmoodle_pluginlib_nosrcpltfrm = "'.vmoodle_get_string('nosrcpltfrm', 'vmoodleadminset_plugins').'"; ';
echo 'var vmoodle_pluginlib_nosyncpltfrm = "'.vmoodle_get_string('nosyncpltfrm', 'vmoodleadminset_plugins').'"; ';
echo 'var vmoodle_pluginlib_confirmpluginvisibilitysync = "'.vmoodle_get_string('confirmpluginvisibilitysync', 'vmoodleadminset_plugins').'"; ';