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
 * This script is to be used from PHP command line and will create a set
 * of Virtual VMoodle automatically from a CSV nodelist description.
 * Template names can be used to feed initial data of new VMoodles.
 * The standard structure of the nodelist is given by the nodelist-dest.csv file.
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/adminlib.php'); // various admin-only functions
require_once($CFG->libdir.'/upgradelib.php'); // general upgrade/install related functions
require_once($CFG->libdir.'/clilib.php'); // cli only functions
require_once($CFG->dirroot.'/blocks/vmoodle/locallib.php');
require_once('clilib.php'); // vmoodle cli only functions

// Fakes an admin identity for all the process.
$USER = get_admin();

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'interactive' => false,
        'help'        => false,
        'config'      => false,
        'nodes'       => '',
        'lint'        => false
    ),
    array(
        'h' => 'help',
        'c' => 'config',
        'n' => 'nodes',
        'i' => 'interactive',
        'l' => 'lint'
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
-l, --lint            Decodes node file and give a report on nodes to be created.

Example:
\$sudo -u www-data /usr/bin/php blocks/vmoodle/cli/bulkcreatenodes.php
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

$nodes = vmoodle_parse_csv_nodelist($options['nodes']);

if ($options['lint']) {
    print_object($nodes);
    die;
}

if (empty($nodes)) {
    cli_error(get_string('cliemptynodelist', 'block_vmoodle'));
}

mtrace(get_string('clistart', 'block_vmoodle'));

foreach ($nodes as $n) {

    mtrace(get_string('climakenode', 'block_vmoodle', $n->vhostname));

    $n->forcedns = 0;

    if (!empty($n->vtemplate)) {
        mtrace(get_string('cliusingtemplate', 'block_vmoodle', $n->vtemplate));

        if (!vmoodle_exist_template($n->vtemplate)) {
            mtrace(get_string('climissingtemplateskip', 'block_vmoodle', $n->vtemplate));
            continue;
        }
    }

    if ($DB->get_record('block_vmoodle', array('vhostname' => $n->vhostname))) {
        mtrace(get_string('clinodeexistsskip', 'block_vmoodle'));
        continue;
    }

    /*
     * This launches automatically all steps of the controller.management.php script several times
     * with the "doadd" action and progressing in steps.
     */
    $action = "doadd";
    $SESSION->vmoodledata = $n;

    $automation = true;

    for ($vmoodlestep = 0 ; $vmoodlestep <= 4; $vmoodlestep++) {
        mtrace(get_string('climakestep', 'block_vmoodle', $vmoodlestep));
        $return = include $CFG->dirroot.'/blocks/vmoodle/controller.management.php';
        if ($return == -1) {
            cli_error(get_string('cliprocesserror', 'block_vmoodle'));
        }
        if ($interactive) {
            $input = readline("Continue (y/n|r) ?\n");
            if ($input == 'r' || $input == 'R'){
                $vmoodlestep--;
            } elseif ($input == 'n' || $input == 'N') {
                echo "finishing\n";
                exit;
            }
        }
    }

    // Once all steps done on, this node, process extra settings from CSV file using a side connection

    $VDB = vmoodle_setup_DB($n);

    // special fix for deployed networks : 
    // Fix the master node name in mnet_host
    // We need overseed the issue of loosing the name of the master node in the deploied instance
    // TODO : this is a turnaround quick fix.
    if ($remote_vhost = $VDB->get_record('mnet_host', array('wwwroot' => $CFG->wwwroot))) {
        global $SITE;
        $remote_vhost->name = $SITE->fullname;
        $VDB->update_record('mnet_host', $remote_vhost, 'id');
    }

    if (!empty($n->config)) {
        $confiarr = (array) $n->config;
        foreach ($confiarr as $key => $value) {
            if ($oldrec = $VDB->get_record('config', array('name' => $key))) {
                $oldrec->value = $value;
                $VDB->update_record('config', $oldrec);
            } else {
                $rec = new StdClass;
                $rec->name = $key;
                $rec->value = $value;
                $VDB->insert_record('config', $rec);
            }
        }
    }

    if (!empty($n->local)) {
        foreach ($n->local as $pluginname => $plugin) {
            foreach ($plugin as $setting => $value) {
                if ($oldrec = $VDB->get_record('config_plugins', array('plugin' => 'local_'.$pluginname, 'name' => $setting))) {
                    $oldrec->value = $value;
                    $VDB->update_record('config_plugins', $oldrec);
                } else {
                    $rec = new StdClass;
                    $rec->plugin = 'local_'.$pluginname;
                    $rec->name = $setting;
                    $rec->value = $value;
                    $VDB->insert_record('config_plugins', $rec);
                }
            }
        }
    }

    if (!empty($n->block)) {
        foreach ($n->block as $plugin) {
            foreach ($plugin as $setting => $value) {
                if ($oldrec = $VDB->get_record('config_plugins', array('plugin' => 'block_'.$plugin, 'name' => $setting))) {
                    $oldrec->value = $value;
                    $VDB->update_record('config_plugins', $oldrec);
                } else {
                    $rec = new StdClass;
                    $rec->plugin = 'block_'.$plugin;
                    $rec->name = $setting;
                    $rec->value = $value;
                    $VDB->insert_record('config_plugins', $rec);
                }
            }
        }
    }

    if (!empty($n->mod)) {
        foreach ($n->mod as $plugin) {
            foreach ($plugin as $setting => $value) {
                if ($oldrec = $VDB->get_record('config_plugins', array('plugin' => 'mod_'.$plugin, 'name' => $setting))) {
                    $oldrec->value = $value;
                    $VDB->update_record('config_plugins', $oldrec);
                } else {
                    $rec = new StdClass;
                    $rec->plugin = 'mod_'.$plugin;
                    $rec->name = $setting;
                    $rec->value = $value;
                    $VDB->insert_record('config_plugins', $rec);
                }
            }
        }
    }

    if (!empty($n->format)){
        foreach ($n->format as $plugin) {
            foreach ($plugin as $setting => $value) {
                if ($oldrec = $VDB->get_record('config_plugins', array('plugin' => 'format_'.$plugin, 'name' => $setting))) {
                    $oldrec->value = $value;
                    $VDB->update_record('config_plugins', $oldrec);
                } else {
                    $rec = new StdClass;
                    $rec->plugin = 'format_'.$plugin;
                    $rec->name = $setting;
                    $rec->value = $value;
                    $VDB->insert_record('config_plugins', $rec);
                }
            }
        }
    }

    if (!empty($n->auth)) {
        foreach ($n->auth as $pluginkey => $plugin) {
            foreach ($plugin as $setting => $value) {
                if ($oldrec = $VDB->get_record('config_plugins', array('plugin' => 'auth/'.$pluginkey, 'name' => $setting))) {
                    $oldrec->value = $value;
                    $VDB->update_record('config_plugins', $oldrec);
                } else {
                    $rec = new StdClass;
                    $rec->plugin = 'auth/'.$pluginkey;
                    $rec->name = $setting;
                    $rec->value = $value;
                    $VDB->insert_record('config_plugins', $rec);
                }
            }
        }
    }
}
