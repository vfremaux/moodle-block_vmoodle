<?php

/// check keys and renew with peers.

/// requires : patching /mnet/xmlrpc/server.php for mnet_keyswap()
/// requires : patching /mnet/lib.php for mnet_keyswap()

global $DB;

require_once $CFG->dirroot.'/mnet/lib.php';
// require_once $CFG->dirroot.'/blocks/vmoodle/mnet/lib.php';
require_once $CFG->dirroot.'/blocks/vmoodle/classes/Mnet_Peer.class.php';

$mnet = get_mnet_environment();

mtrace("Cron automatic rotation for MNET keys...\n");

// setting some defaults if the vmoodle config has not been setup
if (!isset($CFG->mnet_key_autorenew_gap)) set_config('mnet_key_autorenew_gap', 24 * 3); // three days
if (!isset($CFG->mnet_key_autorenew)) set_config('mnet_key_autorenew', 0); // not activated as a default
if (!isset($CFG->mnet_key_autorenew_hour)) set_config('mnet_key_autorenew_hour', 0); // midnight
if (!isset($CFG->mnet_key_autorenew_min)) set_config('mnet_key_autorenew_min', 0); // midnight

$CFG->mnet_key_autorenew_time = $CFG->mnet_key_autorenew_hour * HOURSECS + $CFG->mnet_key_autorenew_min * MINSECS;

// if autorenewal is enabled and we are mnetworking
if (!empty($CFG->mnet_key_autorenew) && $CFG->mnet_dispatcher_mode != 'none'){

    include_once $CFG->dirroot.'/mnet/peer.php';
    include_once $CFG->dirroot.'/mnet/lib.php';

    // check if key is getting obsolete
    $havetorenew = 0;

    // key is getting old : check if it is time to operate
    if ($mnet->public_key_expires - time() < $CFG->mnet_key_autorenew_gap * HOURSECS){

        // this one is needed as temporary global toggle between distinct cron invocations, 
        // but should not be changed through the GUI
        if (empty($CFG->mnet_autorenew_haveto)){
            set_config('mnet_autorenew_haveto', 1);
            mtrace('Local key is expiring. Need renewing MNET keys...');
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
    if ($force){
        mtrace("forced mode");
    }
    
    if ($havetorenew || $force){
        mtrace("Local key will expire very soon. Renew MNET keys now !!...\n");
        // reniew local key
		// mtrace('Me : '.$CFG->wwwroot);
		// debug_trace("My Old Key :\n ".$MNET->public_key);

        $mnet->replace_keys();

        // send new key using key exchange transportation
		// debug_trace("My New Key :\n ".$mnet->public_key);

        // make a key and exchange it with all known and active peers
        $mnet_peers = $DB->get_records('mnet_host', array('deleted' => 0));
        if ($mnet_peers){
            foreach($mnet_peers as $peer){

                if (($peer->id == $CFG->mnet_all_hosts_id) || ($peer->id == $CFG->mnet_localhost_id)) continue;

                $application = get_record('mnet_application', 'id', $peer->applicationid);

                $mnet_peer = new mnet_peer();
                $mnet_peer->set_wwwroot($peer->wwwroot);
                // get the sessions for each vmoodle that have same ID Number
                // we use a force parameter to force fetching the key remotely anyway
                $currentkey = mnet_get_public_key($mnet_peer->wwwroot, $application, 1);
                if ($currentkey){
                    $mnet_peer->public_key = clean_param($currentkey, PARAM_PEM);
                    $mnet_peer->updateparams->public_key = clean_param($currentkey, PARAM_PEM);
                    $mnet_peer->public_key_expires = $mnet_peer->check_common_name($currentkey);
                    $mnet_peer->updateparams->public_key_expires = $mnet_peer->check_common_name($currentkey);
                    $mnet_peer->commit();
                    // mtrace('My key renewed at '.$peer->wwwroot.' till '.userdate($mnet_peer->public_key_expires));
                } else {
                    mtrace('Failed renewing key with '.$peer->wwwroot."\n");
                }
            }
        }       
        set_config('mnet_autorenew_haveto', 0);
    }
}

?>