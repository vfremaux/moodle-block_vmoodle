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
 * This script is to be used from PHP command line and will snaphost a set
 * of Virtual VMoodle automatically from a CSV nodelist description.
 */

define('CLI_SCRIPT', true);

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php'); // various admin-only functions
require_once($CFG->libdir.'/upgradelib.php'); // general upgrade/install related functions
require_once($CFG->libdir.'/clilib.php'); // cli only functions
require_once($CFG->dirroot.'/blocks/vmoodle/lib.php');
require_once($CFG->dirroot.'/blocks/vmoodle/cli/clilib.php'); // vmoodle cli only functions

// Fakes an admin identity for all the process.
$USER = get_admin();

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'interactive' => false,
        'help'        => false,
        'config'      => false,
        'nodes'       => ''
    ),
    array(
        'h' => 'help',
        'c' => 'config',
        'n' => 'nodes',
        'i' => 'interactive'
    )
);

$interactive = !empty($options['interactive']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Command line VMoodle Generator.
Please note you must execute this script with the same uid as apache!

Options:
--interactive     No interactive questions or confirmations
-h, --help            Print out this help
-c, --config          Define an external config file
-n, --nodes           A node descriptor CSV file

Example:
\$sudo -u www-data /usr/bin/php blocks/vmoodle/cli/bulksnapshot.php -nodes=nodelist.csv
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if (empty($CFG->version)) {
    cli_error(get_string('missingconfigversion', 'debug'));
}

// Get all options from config file.

if (!empty($options['config'])) {
    echo "Loading config : ".$options['config'];
    if (!file_exists($options['config'])) {
        cli_error(get_string('confignotfound', 'local_sharedresources'));
    }
    $content = file($options['config']);
    foreach ($content as $l) {
        if (preg_match('/^\s+$/', $l)) {
            continue; // Empty lines.
        }
        if (preg_match('/^[#\/!;]/', $l)) {
            continue; // Comments (any form).
        }
        if (preg_match('/^(.*?)=(.*)$/', $l, $matches)) {
            if (in_array($matches[1], $expectedoptions)) {
                $options[trim($matches[1])] = trim($matches[2]);
            }
        }
    }
}

if (empty($options['nodes'])) {
    cli_error(get_string('climissingnodes', 'block_vmoodle'));
}

$nodes = vmoodle_parse_csv_snaplist($options['nodes']);

if (empty($nodes)) {
    cli_error(get_string('cliemptynodelist', 'block_vmoodle'));
}

mtrace(get_string('clistart', 'block_vmoodle'));

foreach ($nodes as $n) {

    mtrace(get_string('clisnapnode', 'block_vmoodle', $n->vhostname));

    $n->forcedns = 0;
    $wwwroot = $n->vhostname;

    if (empty($wwwroot)) {
        continue;
    }

    if (!$DB->get_record('block_vmoodle', array('vhostname' => $n->vhostname))) {
        mtrace(get_string('clinodemissingskip', 'block_vmoodle', $n->vhostname));
        continue;
    }

    /*
     * This launches automatically all steps of the controller.management.php script several times
     * with the "doadd" action and progressing in steps.
     */
    $action = 'snapshot';
    $SESSION->vmoodledata = $n;

    $automation = true;

    for ($vmoodlestep = 0 ; $vmoodlestep <= 2; $vmoodlestep++) {
        mtrace(get_string('clisnapstep', 'block_vmoodle', $vmoodlestep));
        $return = include $CFG->dirroot.'/blocks/vmoodle/controller.management.php';
        if ($return == -1) {
            cli_error(get_string('cliprocesserror', 'block_vmoodle'));
        }
        if ($interactive) {
            $input = readline("Continue (y/n|r) ?\n");
            if ($input == 'r' || $input == 'R') {
                $vmoodlestep--;
            } else if ($input == 'n' || $input == 'N') {
                echo "finishing\n";
                exit;
            }
        }
    }
}
