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
 * This file is a cron microclock script.
 * It will be used as replacement of setting individual
 * cron lines for all virtual instances.
 *
 * Setup this vcron to run at the smallest period possible, as
 * it will schedule all availables vmoodle to be run as required.
 * Note that one activaton of this cron may not always run real crons
 * or may be run more than one cron.
 *
 * If used on a big system with clustering, ensure hostnames are adressed
 * at the load balancer entry and not on physical hosts
 *
 * @package block_vmoodle
 * @category blocks
 * @author Valery fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

define('ROUND_ROBIN', 0);
define('LOWEST_POSSIBLE_GAP', 1);
define('RUN_PER_TURN', 1);

global $vcron;

$vcron = new StdClass;
$vcron->ACTIVATION = 'cli';                         // choose how individual cron are launched
$vcron->STRATEGY = ROUND_ROBIN ;                    // choose vcron rotation mode
$vcron->PERIOD = 15 * MINSECS ;                     // used if LOWEST_POSSIBLE_GAP to setup the max gap
$vcron->TIMEOUT = 300;                                 // time out for CURL call to effective cron
$vcron->TRACE = $CFG->dataroot.'/vcrontrace.log';   // Trace file where to collect cron outputs
$vcron->TRACE_ENABLE = false;                        // enables tracing

/**
 * fire a cron URL using CURL.
 *
 *
 */
function fire_vhost_cron($vhost) {
    global $vcron, $DB, $CFG;

    if ($vcron->TRACE_ENABLE) {
        $crontrace = fopen($vcron->TRACE, 'a');
    }
    $ch = curl_init($vhost->vhostname.'/admin/cron.php');

    curl_setopt($ch, CURLOPT_TIMEOUT, $vcron->TIMEOUT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_POSTFIELDS, '');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $timestamp_send = time();
    $rawresponse = curl_exec($ch);
    $timestamp_receive = time();

    if ($rawresponse === false) {
        $error = curl_errno($ch) .':'. curl_error($ch);
        if ($vcron->TRACE_ENABLE) {
            if ($crontrace){
                fputs($crontrace, "VCron start on $vhost->vhostname : $timestamp_send\n" );
                fputs($crontrace, "VCron Error : $error \n");
                fputs($crontrace, "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
                fclose($crontrace);
            }
        }
        echo "VCron started on $vhost->vhostname : $timestamp_send\n";
        echo "VCron Error : $error \n";
        echo "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n";
        return false;
    }

    if (!empty($CFG->vlogfilepattern)) {
        $logfile = str_replace('%%VHOSTNAME%%', $vhost->vhostname, $CFG->vlogfilepattern);
        $logfile = preg_replace('#https?://#', '', $logfile);
        if ($log = fopen($logfile, 'w')) {
            fputs($log, $rawresponse);
            fclose($log);
        }
    }

    if ($vcron->TRACE_ENABLE) {
        if ($crontrace) {
            fputs($crontrace, "VCron start on $vhost->vhostname : $timestamp_send\n" );
            fputs($crontrace, $rawresponse."\n");
            fputs($crontrace, "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
            fclose($crontrace);
        }
    }
    echo "VCron start on $vhost->vhostname : $timestamp_send\n";
    echo $rawresponse."\n";
    echo "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n";
    $vhost->lastcrongap = time() - $vhost->lastcron;
    $vhost->lastcron = $timestamp_send;
    $vhost->croncount++;

    $DB->update_record('block_vmoodle', $vhost);

}

/**
 * fire a cron URL using cli exec
 *
 *
 */
function exec_vhost_cron($vhost) {
    global $vcron, $DB, $CFG;

    if ($vcron->TRACE_ENABLE) {
        $crontrace = fopen($vcron->TRACE, 'a');
    }

    $cmd = 'php "'.$CFG->dirroot.'/blocks/vmoodle/cli/cron.php" --host='.$vhost->vhostname;

    $timestamp_send = time();
    exec($cmd, $rawresponse);
    $timestamp_receive = time();

    $output = implode("\n", $rawresponse);

    if ($vcron->TRACE_ENABLE) {
        if ($crontrace) {
            fputs($crontrace, "VCron start on $vhost->vhostname : $timestamp_send\n" );
            fputs($crontrace, implode("\n", $output)."\n");
            fputs($crontrace, "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
            fclose($crontrace);
        }
    }

    if (!empty($CFG->vlogfilepattern)) {
        $logfile = str_replace('%%VHOSTNAME%%', $vhost->vhostname, $CFG->vlogfilepattern);
        $logfile = preg_replace('#https?://#', '', $logfile);
        echo "Opening $logfile\n";
        if ($log = fopen($logfile, 'w')) {
            fputs($log, $output);
            fclose($log);
        }
    }

    echo "VCron start on $vhost->vhostname : $timestamp_send\n";
    echo $output."\n";
    echo "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n";
    $vhost->lastcrongap = time() - $vhost->lastcron;
    $vhost->lastcron = $timestamp_send;
    $vhost->croncount++;

    $DB->update_record('block_vmoodle', $vhost);
}

if (!$vmoodles = $DB->get_records('block_vmoodle', array('enabled' => 1))) {
    die("Nothing to do. No Vhosts");
}

$allvhosts = array_values($vmoodles);

echo "Moodle VCron... start\n";
echo "Last croned : ".@$CFG->vmoodle_cron_lasthost."\n";

if ($vcron->STRATEGY == ROUND_ROBIN) {
    $rr = 0;
    foreach($allvhosts as $vhost) {
        if ($rr == 1) {
            set_config('vmoodle_cron_lasthost', $vhost->id);
            echo "Round Robin : ".$vhost->vhostname."\n";
            if ($vcron->ACTIVATION == 'cli') {
                exec_vhost_cron($vhost);
            } else {
                fire_vhost_cron($vhost);
            }
            die('Done.');
        }
        if ($vhost->id == @$CFG->vmoodle_cron_lasthost) {
            $rr = 1; // take next one
        }
    }
    // we were at last. Loop back and take first
    set_config('vmoodle_cron_lasthost', $allvhosts[0]->id);
    echo "Round Robin : ".$vhost->vhostname."\n";
    if ($vcron->ACTIVATION == 'cli') {
        exec_vhost_cron($allvhosts[0]);
    } else {
        fire_vhost_cron($allvhosts[0]);
    }

} else if ($vcron->STRATEGY == LOWEST_POSSIBLE_GAP) {
    // first make measurement of cron period
    if (empty($CFG->vcrontickperiod)) {
        set_config('vcrontime', time());
        return;
    }
    set_config('vcrontickperiod', time() - $CFG->vcrontime);
    $hostsperturn = max(1, $vcron->PERIOD / $CFG->vcrontickperiod * count($allvhosts));
    $i = 0;
    foreach ($allvhosts as $vhost) {
        if ((time() - $vhost->lastcron) > $vcron->PERIOD) {
            if ($vcron->ACTIVATION == 'cli') {
                exec_vhost_cron($vhost);
            } else {
                fire_vhost_cron($vhost);
            }
            $i++;
            if ($i >= $hostsperturn) {
                return;
            }
        }
    }
}