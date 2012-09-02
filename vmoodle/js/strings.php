<?php
header('Content-Type: application/x-javascript');
require_once('../../../config.php');
echo 'var vmoodle_badregexp = "'.get_string('badregexp', 'block_vmoodle').'"; ';
echo 'var vmoodle_contains = "'.get_string('contains', 'block_vmoodle').'"; ';
echo 'var vmoodle_delete = "'.get_string('delete', 'block_vmoodle').'"; ';
echo 'var vmoodle_none = "'.get_string('none', 'block_vmoodle').'"; ';
echo 'var vmoodle_notcontains = "'.get_string('notcontains', 'block_vmoodle').'"; ';
echo 'var vmoodle_regexp = "'.get_string('regexp', 'block_vmoodle').'"; ';

echo 'var vmoodle_testconnection = "'.get_string('testconnection', 'block_vmoodle').'"; ';
echo 'var vmoodle_testdatapath = "'.get_string('testdatapath', 'block_vmoodle').'"; ';
echo 'var mnetactivationrequired = "'.get_string('mnetactivationrequired', 'block_vmoodle').'"; ';