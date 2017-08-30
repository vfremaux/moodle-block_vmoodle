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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This script speads as many fixture executions as required on any amount
 * of VMoodle that need complex fix to be processed.
 *
 * the launcher will launch several parallel workers that each take care of
 * a little amount of hosts. Distributed mode allow on Linux to have parallelisation
 * of the processing to increase speed.
 *
 * Each worker will launch one time the fixture script that must reside in the current blocks/vmoodle/cli
 * lib as a CLI, VMoodle enabled script. Vmoodle enabled scripts start <with a special piece of code
 * that allows host switching before processing the real CLI algorithm.
 */

define('CLI_SCRIPT', true);

define('ENT_INSTALLER_SYNC_MAX_WORKERS', 2);
define('JOB_INTERLEAVE', 2);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
require_once($CFG->dirroot.'/lib/clilib.php'); // CLI only functions

mtrace("VMoodle Automated Distributed Fixture Tool");
mtrace("##########################################\n");

// Ensure options are blanck;
unset($options);

// Now get cli options.

list($options, $unrecognized) = cli_get_params(
    array(
        'help'             => false,
        'workers'          => false,
        'distributed'      => false,
        'fixture'          => false,
        'logroot'          => false,
        'include'          => false,
        'exclude'          => false,
        'simulate'         => false,
        'verbose'         => false,
    ),
    array(
        'h' => 'help',
        'w' => 'workers',
        'd' => 'distributed',
        'f' => 'fixture',
        'l' => 'logroot',
        'e' => 'exclude',
        'i' => 'include',
        's' => 'simulate',
        'v' => 'verbose',
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Command line ENT Sync worker.

        Options:
        -h, --help          Print out this help
        -w, --workers       Number of workers.
        -f, --fixture       Script name to give to workers.
        -d, --distributed   Distributed operations.
        -l, --logroot       Root directory for logs.
        -e, --exclude       Exclude pattern filter (NOT LIKE).
        -i, --include       Include pattern filter (LIKE).
        -s, --simulate      Stops before launching and gives host list.
        -v, --verbose      Stops before launching and gives host list.
        "; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if ($options['workers'] === false) {
    $options['workers'] = ENT_INSTALLER_SYNC_MAX_WORKERS;
}

if (!empty($options['include'])) {
    $allhosts = $DB->get_records_select('block_vmoodle', ' vhostname LIKE "'.$options['include'].'" ', array());
} else if(!empty($options['exclude'])) {
    $allhosts = $DB->get_records_select('block_vmoodle', ' vhostname NOT LIKE "'.$options['include'].'" ', array());
} else {
    $allhosts = $DB->get_records('block_vmoodle', array('enabled' => 1));
}

// Make worker lists.

$joblists = array();
$i = 0;
foreach ($allhosts as $h) {
    $joblist[$i][] = $h->id;
    $i++;
    if ($i == $options['workers']) {
        $i = 0;
    }
}

if (!empty($options['simulate'])) {
    mtrace('Simulate mode. Target host list:');
    foreach($allhosts as $host) {
        mtrace($host->vhostname.' ('.$host->name.')');
    }
    print_object($joblist);
    die;
}

if (!file_exists($CFG->dirroot.'/blocks/vmoodle/cli/'.$options['fixture'].'.php')) {
    die ("This fixture has no CLI script file\n");
}

// Start spreading workers, and pass the list of vhost ids. Launch workers in background
// Linux only implementation.

if ($CFG->ostype == 'WINDOWS') {
    $phpcmd = 'php.exe';
} else {
    $phpcmd = '/usr/bin/php';
}

$verboseattr = (!empty($options['verbose'])) ? '--verbose' : '';

$i = 1;
foreach ($joblist as $jl) {
    $jobids = array();
    if (!empty($jl)) {

        $logattr = (!empty($options['logroot'])) ? "--logfile={$options['logroot']}/fixture_log_{$i}.log" : '' ;
        $hids = implode(',', $jl);
        $workercmd = "$phpcmd \"{$CFG->dirroot}/blocks/vmoodle/cli/fixture_worker.php\" --nodes=\"$hids\" $logattr --fixture={$options['fixture']} $verboseattr";
        if (!empty($options['verbose'])) {
            mtrace("Worker start : $workercmd");
        }
        if ($options['distributed']) {
            $workercmd .= ' &';
        }
        mtrace("Executing $workercmd\n######################################################\n");
        $output = array();
        exec($workercmd, $output, $return);
        if ($return) {
            die("Worker ended with error");
        }
        if (!$options['distributed']) {
            mtrace(implode("\n", $output));
        }
        $i++;
        sleep(JOB_INTERLEAVE);
    }
}