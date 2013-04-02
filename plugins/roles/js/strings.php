<?php
header('Content-Type: application/x-javascript');
require_once('../../../../../config.php');
require_once('../../../locallib.php');
echo 'var vmoodle_rolelib_nocapability = "'.get_string('nocapability', 'vmoodleadminset_roles').'"; ';
echo 'var vmoodle_rolelib_nosrcpltfrm = "'.get_string('nosrcpltfrm', 'vmoodleadminset_roles').'"; ';
echo 'var vmoodle_rolelib_nosyncpltfrm = "'.get_string('nosyncpltfrm', 'vmoodleadminset_roles').'"; ';
echo 'var vmoodle_rolelib_confirmrolecapabilitysync = "'.get_string('confirmrolecapabilitysync', 'vmoodleadminset_roles').'"; ';