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
 * A fixture worker will play a script after
 */

define('CLI_SCRIPT', true);
define('ENT_INSTALLER_SYNC_INTERHOST', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
require_once($CFG->dirroot.'/lib/clilib.php'); // CLI only functions

// Now get cli options.

list($options, $unrecognized) = cli_get_params(
    array(
        'help'              => false,
        'fixture'           => false,
        'nodes'             => false,
        'logfile'           => false,
        'logmode'           => false,
        'verbose'           => false,
    ),
    array(
        'h' => 'help',
        'f' => 'fixture',
        'n' => 'nodes',
        'l' => 'logfile',
        'm' => 'logmode',
        'v' => 'verbose'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || empty($options['nodes'])) {
    $help =
        "Command Line Fixture Worker.

        Options:
        -h, --help          Print out this help
        -f, --fixture       The fixture to run.
        -n, --nodes         Node ids to work with.
        -l, --logfile       the log file to use. No log if not defined
        -m, --logmode       'append' or 'overwrite'
        -v, --verbose       Verbose output

        "; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if (empty($options['logmode'])) {
    $options['logmode'] = 'w';
}

if (!empty($options['logfile'])) {
    $LOG = fopen($options['logfile'], $options['logmode']);
}

// Fire sequential synchronisation.
mtrace("Starting worker");
if (isset($LOG)) {
    fputs($LOG, "Starting worker\n");
};

$nodes = explode(',', $options['nodes']);
foreach ($nodes as $nodeid) {
    $host = $DB->get_record('block_vmoodle', array('id' => $nodeid));
    $cmd = "/usr/bin/php {$CFG->dirroot}/blocks/vmoodle/cli/{$options['fixture']}.php --host={$host->vhostname} ";
    $return = 0;
    $output = array();
    mtrace($cmd);
    exec($cmd, $output, $return);
    if ($return) {
        die ("Worker failed\n");
    }
    if (!empty($options['verbose'])) {
        echo implode("\n", $output);
    }
    if (isset($LOG)) {
        fputs($LOG, "$cmd\n#-------------------\n");
        fputs($LOG, implode("\n", $output));
    };
    sleep(ENT_INSTALLER_SYNC_INTERHOST);
}

if (isset($LOG)) fclose($LOG);

return 0;