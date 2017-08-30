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
 * @package block-vmoodle
 * @category blocks
 * @author Valery fremaux (valery.fremaux@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require('../../config.php');

define('ROUND_ROBIN', 0);
define('LOWEST_POSSIBLE_GAP', 1);

$password = optional_param('password', '', PARAM_RAW);

global $VCRON;

$VCRON = new StdClass;
$VCRON->ACTIVATION = 'cli';                         // Choose how individual cron are launched (cli or curl).
$VCRON->STRATEGY = ROUND_ROBIN ;                    // Choose vcron rotation mode.
$VCRON->PERIOD = 15 * MINSECS ;                     // Used if LOWEST_POSSIBLE_GAP to setup the max gap.
$VCRON->TIMEOUT = 300;                              // Time out for CURL call to effective cron.
$VCRON->TRACE = $CFG->dataroot.'/vcrontrace.log';   // Trace file where to collect cron outputs.
$VCRON->TRACE_ENABLE = false;                       // Enables tracing.

/**
* fire a cron URL using CURL.
*
*
*/
function fire_vhost_cron($vhost, $password = '') {
    global $VCRON, $DB;

    if ($VCRON->TRACE_ENABLE) {
        $CRONTRACE = fopen($VCRON->TRACE, 'a');
    }

    $url = $vhost->vhostname.'/admin/cron.php';
    if ($password) {
        $url .= '?password='.$password;
    }
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_TIMEOUT, $VCRON->TIMEOUT);
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
        if ($VCRON->TRACE_ENABLE) {
            if ($CRONTRACE) {
                fputs($CRONTRACE, "VCron start on $vhost->vhostname : $timestamp_send\n" );
                fputs($CRONTRACE, "VCron Error : $error \n");
                fputs($CRONTRACE, "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
                fclose($CRONTRACE);
            }
        }
        mtrace("VCron started on $vhost->vhostname : $timestamp_send\n" );
        mtrace("VCron Error : $error \n");
        mtrace("VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
        return false;
    }

    if ($VCRON->TRACE_ENABLE) {
        if ($CRONTRACE) {
            fputs($CRONTRACE, "VCron start on $vhost->vhostname : $timestamp_send\n" );
            fputs($CRONTRACE, $rawresponse."\n");
            fputs($CRONTRACE, "VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
            fclose($CRONTRACE);
        }
    }
    mtrace("VCron start on $vhost->vhostname : $timestamp_send\n" );
    mtrace($rawresponse."\n");
    mtrace("VCron stop on $vhost->vhostname : $timestamp_receive\n#################\n\n" );
    $vhost->lastcrongap = time() - $vhost->lastcron;
    $vhost->lastcron = $timestamp_send;
    $vhost->croncount++;

    $DB->update_record('block_vmoodle', $vhost);

}

if (!$vmoodles = $DB->get_records('block_vmoodle', array('enabled' => 1))) {
    die("Nothing to do. No Vhosts");
}

$allvhosts = array_values($vmoodles);

mtrace("<pre>");
mtrace("Moodle VCron... start");
mtrace("Last croned : ".@$CFG->vmoodle_cron_lasthost);

if ($VCRON->STRATEGY == ROUND_ROBIN){
    $rr = 0;
    foreach ($allvhosts as $vhost) {
        if ($rr == 1) {
            set_config('vmoodle_cron_lasthost', $vhost->id);
            mtrace("Round Robin : ".$vhost->vhostname);
            fire_vhost_cron($vhost, $password);
            die('Done.');
        }
        if ($vhost->id == @$CFG->vmoodle_cron_lasthost) {
            $rr = 1; // Take next one.
        }
    }
    // We were at last. Loop back and take first.
    set_config('vmoodle_cron_lasthost', $allvhosts[0]->id);
    mtrace("Round Robin : ".$vhost->vhostname);
    fire_vhost_cron($allvhosts[0], $password);

} else if ($VCRON->STRATEGY == LOWEST_POSSIBLE_GAP) {
    // First make measurement of cron period.
    if (empty($CFG->vcrontickperiod)) {
        set_config('vcrontime', time());
        return;
    }
    set_config('vcrontickperiod', time() - $CFG->vcrontime);
    $hostsperturn = max(1, $VCRON->PERIOD / $CFG->vcrontickperiod * count($allvhosts));
    $i = 0;
    foreach ($allvhosts as $vhost) {
        if ((time() - $vhost->lastcron) > $VCRON->PERIOD) {
            fire_vhost_cron($vhost, $password);
            $i++;
            if ($i >= $hostsperturn) {
                return;
            }
        }
    }
}