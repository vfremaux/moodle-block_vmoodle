<?php
header('Content-Type: application/x-javascript');
require_once('../../../../../../config.php');
require_once('../../../../locallib.php');
echo 'var vmoodle_rolelib_nocapability = "'.get_string_vml('nocapability', 'rolelib').'"; ';
echo 'var vmoodle_rolelib_nosrcpltfrm = "'.get_string_vml('nosrcpltfrm', 'rolelib').'"; ';
echo 'var vmoodle_rolelib_nosyncpltfrm = "'.get_string_vml('nosyncpltfrm', 'rolelib').'"; ';
echo 'var vmoodle_rolelib_confirmrolecapabilitysync = "'.get_string_vml('confirmrolecapabilitysync', 'rolelib').'"; ';