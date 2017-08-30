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
 * this script scans all virtual instances to perform a cronmonitor worker
 * action on each.
 *
 * @package block_vmoodle
 * @category blocks
 * @author Valery fremaux (valery.fremaux@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

global $VCRON;

$VCRON = new StdClass;
$VCRON->MODE = 'cli';                    // choose vcron rotation mode
$VCRON->TIMEOUT = 300;                                 // time out for CURL call to effective cron

/**
 * fire a cron URL using cli exec
 *
 *
 */
function exec_vhost_cron($vhost, $options = array()) {
    global $VCRON, $CFG;

    $cmd = 'php "'.$CFG->dirroot.'/blocks/vmoodle/cli/cronmonitor.php" --host='.$vhost->vhostname;

    if (array_key_exists('file', $options)) {
        $cmd .= ' --file='.$options['file'];
    }

    if (array_key_exists('mode', $options)) {
        $cmd .= ' --mode='.$options['mode'];
    } else {
        $cmd .= ' --mode='.$VCRON->MODE;
    }

    mtrace('');
    mtrace("VCronMonitor start on $vhost->vhostname");
    mtrace("Command: $cmd");

    exec($cmd, $rawresponse);

    mtrace(implode("\n", $rawresponse));

}

if (!$vmoodles = $DB->get_records('block_vmoodle', array('enabled' => 1))) {
    die("Nothing to do. No Vhosts");
}

$allvhosts = array_values($vmoodles);

mtrace("Moodle VCronMonitor... start");

$options = array();

foreach($allvhosts as $vhost) {

    if (!empty($CFG->vlogfilepattern)) {
        $logfile = str_replace('%%VHOSTNAME%%', $vhost->vhostname, $CFG->vlogfilepattern);
        $logfile = preg_replace('#https?://#', '', $logfile);
        $options['file'] = $logfile;
    }
    exec_vhost_cron($vhost, $options);
}

mtrace("\nMoodle VCronMonitor... done.");
