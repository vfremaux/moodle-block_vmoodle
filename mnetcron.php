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

// check keys and renew with peers.

if (!defined('MOODLE_INTERNAL')) die("This script cannot be used this way");

/// requires : patching /mnet/xmlrpc/server.php for mnet_keyswap()
/// requires : patching /mnet/lib.php for mnet_keyswap()

global $DB;

require_once $CFG->dirroot.'/mnet/lib.php';
// require_once $CFG->dirroot.'/blocks/vmoodle/mnet/lib.php';
Use \block_vmoodle\Mnet_Peer;

$mnet = get_mnet_environment();

mtrace("Cron automatic rotation for MNET keys...\n");

// setting some defaults if the vmoodle config has not been setup
if (!isset($CFG->mnet_key_autorenew_gap)) set_config('mnet_key_autorenew_gap', 24 * 3); // three days
if (!isset($CFG->mnet_key_autorenew)) set_config('mnet_key_autorenew', 0); // not activated as a default
if (!isset($CFG->mnet_key_autorenew_hour)) set_config('mnet_key_autorenew_hour', 0); // midnight
if (!isset($CFG->mnet_key_autorenew_min)) set_config('mnet_key_autorenew_min', 0); // midnight

$CFG->mnet_key_autorenew_time = $CFG->mnet_key_autorenew_hour * HOURSECS + $CFG->mnet_key_autorenew_min * MINSECS;

// if autorenewal is enabled and we are mnetworking
if (!empty($CFG->mnet_key_autorenew) && $CFG->mnet_dispatcher_mode != 'none') {

    include_once $CFG->dirroot.'/mnet/peer.php';
    include_once $CFG->dirroot.'/mnet/lib.php';

    // check if key is getting obsolete
    $havetorenew = 0;
    $trace = '';

    // key is getting old : check if it is time to operate
    if ($mnet->public_key_expires - time() < $CFG->mnet_key_autorenew_gap * HOURSECS) {

        // this one is needed as temporary global toggle between distinct cron invocations,
        // but should not be changed through the GUI
        if (empty($CFG->mnet_autorenew_haveto)) {
            set_config('mnet_autorenew_haveto', 1);
            mtrace('Local key is expiring. Need renewing MNET keys...');
            $trace .= userdate(time()).' SET KEY RENEW ON on '.$CFG->wwwroot."\n";
        } else {

            if (!empty($CFG->mnet_key_autorenew_time)){
                $now = getdate(time());
                if ( ($now['hours'] * HOURSECS + $now['minutes'] * MINSECS) > $CFG->mnet_key_autorenew_time ){
                    $havetorenew = 1;
                }
            } else {
                $havetorenew = 1;
            }
        }
    }

    // renew if needed
    $force = optional_param('forcerenew', 0, PARAM_INT);
    if ($force) {
        mtrace("forced mode");
    }
    
    if ($havetorenew || $force) {
        mtrace("Local key will expire very soon. Renew MNET keys now !!...\n");
        // renew local key

        $mnet->replace_keys();

        // send new key using key exchange transportation

        // make a key and exchange it with all known and active peers
        $mnet_peers = $DB->get_records('mnet_host', array('deleted' => 0));
        if ($mnet_peers) {
            foreach ($mnet_peers as $peer) {

                if (($peer->id == $CFG->mnet_all_hosts_id) || ($peer->id == $CFG->mnet_localhost_id)) continue;

                $application = $DB->get_record('mnet_application', array('id' => $peer->applicationid));

                $mnet_peer = new mnet_peer();
                $mnet_peer->set_wwwroot($peer->wwwroot);
                // get the sessions for each vmoodle that have same ID Number
                // we use a force parameter to force fetching the key remotely anyway
                $currentkey = mnet_get_public_key($mnet_peer->wwwroot, $application, 1);
                if ($currentkey){
                    $mnet_peer->public_key = clean_param($currentkey, PARAM_PEM);
                    $mnet_peer->updateparams = new StdClass();
                    $mnet_peer->updateparams->public_key = clean_param($currentkey, PARAM_PEM);
                    $mnet_peer->public_key_expires = $mnet_peer->check_common_name($currentkey);
                    $mnet_peer->updateparams->public_key_expires = $mnet_peer->check_common_name($currentkey);
                    $mnet_peer->commit();
                    mtrace('My key renewed at '.$peer->wwwroot.' till '.userdate($mnet_peer->public_key_expires));
                    $trace .= userdate(time()).' KEY RENEW from '.$CFG->wwwroot.' to '.$peer->wwwroot." suceeded\n"; 
                } else {
                    mtrace('Failed renewing key with '.$peer->wwwroot."\n");
                    $trace .= userdate(time()).' KEY RENEW from '.$CFG->wwwroot.' to '.$peer->wwwroot." failed\n"; 
                }
            }
        }       
        set_config('mnet_autorenew_haveto', 0);
        $trace .= userdate(time()).' RESET KEY RENEW on '.$CFG->wwwroot."\n";

        /// record trace in trace file
        if ($CFG->tracevmoodlekeyrenew) {
            if ($TRACE = fopen($CFG->dataroot.'/vmoodle_renew.log', 'w+')) {
                fputs($TRACE, $trace);
                fclose($TRACE);
            }
        }
    }
}
