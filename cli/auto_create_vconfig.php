<?php

// We need block Web use of theis script
define('CLI_SCRIPT', true);

// We need block evaluation of vconfig because possible not yet created !
global $CLI_VMOODLE_PRECHECK;
$CLI_VMOODLE_PRECHECK = true;

require('../../../config.php');

$configtpl = implode('', file($CFG->dirroot.'/blocks/vmoodle/vconfig-tpl.php'));

if (file_exists($CFG->dirroot.'/blocks/vmoodle/vconfig.php')) {
    copy($CFG->dirroot.'/blocks/vmoodle/vconfig.php', $CFG->dirroot.'/blocks/vmoodle/vconfig.php.back');
}

if (!$VCONFIG = fopen($CFG->dirroot.'/blocks/vmoodle/vconfig.php', 'w')) {
    die(-1);
}

$configtpl = str_replace('<%%DBHOST%%>', $CFG->dbhost, $configtpl);
$configtpl = str_replace('<%%DBTYPE%%>', $CFG->dbtype, $configtpl);
$configtpl = str_replace('<%%DBNAME%%>', $CFG->dbname, $configtpl);
$configtpl = str_replace('<%%DBLOGIN%%>', $CFG->dbuser, $configtpl);
$configtpl = str_replace('<%%DBPASS%%>', $CFG->dbpass, $configtpl);
$configtpl = str_replace('<%%DBPREFIX%%>', $CFG->prefix, $configtpl);

fputs($VCONFIG, $configtpl);
fclose($VCONFIG);

return 0;