<?php

define('CLI_SCRIPT', true);
require('../../../../../config.php');
require_once('../pluginscontrolslib.php');

$casauthcontrol = new auth_remote_control('auth/cas');
$mnetauthcontrol = new auth_remote_control('auth/mnet');

echo "Test 1 : enable disable mnet \n";
$mnetauthcontrol->action('enable');
echo $CFG->auth."\n";
$mnetauthcontrol->action('disable');
echo $CFG->auth."\n";

echo "Test 2 : enable disable cas \n";
$casauthcontrol->action('enable');
echo $CFG->auth."\n";
$casauthcontrol->action('disable');
echo $CFG->auth."\n";

echo "Test 3 : enable disable cas then mnet \n";
$casauthcontrol->action('enable');
echo $CFG->auth."\n";
$mnetauthcontrol->action('enable');
echo $CFG->auth."\n";
$mnetauthcontrol->action('disable');
echo $CFG->auth."\n";
$casauthcontrol->action('disable');
echo $CFG->auth."\n";

echo "Test 4 : enable disable cas then mnet interleaved\n";

$casauthcontrol->action('enable');
echo $CFG->auth."\n";
$mnetauthcontrol->action('enable');
echo $CFG->auth."\n";
$casauthcontrol->action('disable');
echo $CFG->auth."\n";
$mnetauthcontrol->action('disable');
echo $CFG->auth."\n";
