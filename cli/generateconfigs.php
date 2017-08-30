<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * this script is to use in command line mode to generate physical config.php files
 * of virtualized Moodles. This may be usefull to devirtualize a moodle instance and
 * give it back to a standard installation.
 */
define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/adminlib.php');       // various admin-only functions
require_once($CFG->libdir.'/upgradelib.php');     // general upgrade/install related functions
require_once($CFG->libdir.'/clilib.php');         // cli only functions
require_once($CFG->libdir.'/environmentlib.php');
require_once($CFG->libdir.'/pluginlib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'non-interactive'   => false,
        'help'              => false
    ),
    array(
        'h' => 'help'
    )
);

$interactive = empty($options['non-interactive']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Command line VMoodle Configuration Files Extractor.

This script extracts physical config files for playing vmoodle as
main independant hosts. This is usefull for using CLI upgrades on
each VMoodle.

Please note you must execute this script with the same uid as apache!

Site defaults may be changed via local/defaults.php.

Options:
--non-interactive     No interactive questions or confirmations
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php blocks/vmoodle/cli/generateconfigs.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if (empty($CFG->version)) {
    cli_error(get_string('missingconfigversion', 'debug'));
}

$allvmoodles = $DB->get_records('block_vmoodle', array());

// Prepare generation dir.

$configpath = $CFG->dataroot.'/vmoodle_configs';

if (!is_dir($configpath)) {
    mkdir($configpath, 0777);
}

// Generate.

$configtemplate = implode('', file($CFG->dirroot.'/config.php'));

$generated = array();

$result = 'generating';

foreach ($allvmoodles as $vm) {

    $configvm = $configtemplate;

    assert(preg_match("#CFG->wwwroot\s+=\s+'.*?';#", $configvm));

    $configvm = preg_replace("#CFG->wwwroot\s+=\s+['\"].*?['\"];#s", 'CFG->wwwroot = \''.$vm->vhostname."';", $configvm);
    $configvm = preg_replace("#CFG->dataroot\s+=\s+['\"].*?['\"];#s", 'CFG->dataroot = \''.$vm->vdatapath."';", $configvm);
    $configvm = preg_replace("#CFG->dbhost\s+=\s+['\"].*?['\"];#s", 'CFG->dbhost = \''.$vm->vdbhost."';", $configvm);
    $configvm = preg_replace("#CFG->dbname\s+=\s+['\"].*?['\"];#s", 'CFG->dbname = \''.$vm->vdbname."';", $configvm);
    $configvm = preg_replace("#CFG->dbuser\s+=\s+['\"].*?['\"];#s", 'CFG->dbuser = \''.$vm->vdblogin."';", $configvm);
    $configvm = preg_replace("#CFG->dbpass\s+=\s+['\"].*?['\"];#s", 'CFG->dbpass = \''.$vm->vdbpass."';", $configvm);
    $configvm = preg_replace("#CFG->prefix\s+=\s+['\"].*?['\"];#s", 'CFG->prefix = \''.$vm->vdbprefix."';", $configvm);

    if ($vm->vdbpersist) {
        $configvm = preg_replace("#'dbpersist'\s+=\s+.*?,#", "'dbpersist' = true,", $configvm);
    }

    if ($CONFIG = fopen($configpath.'/config-'.$vm->shortname.'.php', 'w')) {
        $generated[] = 'config-'.$vm->shortname.'.php';
        fputs($CONFIG, $configvm);
        fclose($CONFIG);
    }
}

if (!empty($generated)) {
    $result = implode("\n", $generated);
    $controllerresult = get_string('generatedconfigs', 'block_vmoodle', $result);
}
