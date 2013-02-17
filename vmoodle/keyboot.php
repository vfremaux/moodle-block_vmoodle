<?php

/**
* this script is indented to provide a secured mechanisms to reboot the initial local MNET key
* when newly instanciatd. This results in executing a primary $MNET->replace_keys(), so the new
* instance has a valid own MNET setup. This script must be checked against security concerns as
* not being accessible from any unkown host. The way we know our trusted master is to checkback
* the incoming public key and search for a matching key in known hosts.
*
* This is a first secrity check that might not prevent for key steeling attacks.
*
* We cannot use usual MNET functions as impacting on behaviour of core mnet lib. this script can only be used once
* at platform instanciation.
*
*/

include "../../config.php";
include_once "debuglib.php"; // fakes existance of a debug lib

global $MNET;

require_once $CFG->dirroot.'/mnet/lib.php';

$test = 0;

if(!$test)
    $masterpk = required_param('pk', PARAM_RAW);

// avoid shooting in yourself
if (((!empty($masterpk) && $remotehost = get_record_select('mnet_host', " public_key = '$masterpk' AND id > 1"))) || $test){

	// $CFG->bootstrap_init is a key that has been added by master when postprocessing the deployment template
	// We check that the public key given matches the identity of the master who initiated the platform restoring.

    if ($test || (@$CFG->bootstrap_init == $remotehost->wwwroot)){
    
    	// at this time, the local platform may not have self key, or may inherit 
    	// an obsolete key from the template SQL backup.
    	// we must fix that forcing a local key replacement
        $MNET = new mnet_environment();
        $MNET->init();        
        $MNET->name = '';
        if ($test){
        // print_object($MNET);
	    }
        $oldkey = $MNET->public_key;
        $MNET->replace_keys();
        // debug_trace("REMOTE : Replaced keys from \n{$oldkey}\nto\n{$MNET->public_key}\n");

    } else {
        echo "ERROR : Master host don't match ".@$CFG->bootstrap_init;
    }
} else {
    echo "ERROR : Master host not found or master host key is empty";
}

// Finally we disable the keyboot script locking definitively the door.
set_config('bootstrap_init', '');