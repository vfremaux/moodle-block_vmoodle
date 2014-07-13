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
 * @package block-vmoodle
 * @category blocks
 *
 * this script is indented to provide a secured mechanisms to reboot the initial local MNET key
 * when newly instanciated. This results in executing a primary $MNET->replace_keys(), so the new
 * instance has a valid own MNET setup. This script must be checked against security concerns as
 * not being accessible from any unkown host. The way we know our trusted master is to checkback
 * the incoming public key and search for a matching key in known hosts.
 *
 * This is a first security check that might not prevent for key steeling attacks.
 *
 * We cannot use usual MNET functions as impacting on behaviour of core mnet lib. this script can only be used once
 * at platform instanciation.
 *
*/

include "../../config.php";
include_once "debuglib.php"; // fakes existance of a debug lib

global $MNET;

require_once $CFG->dirroot.'/mnet/lib.php';

// This is a workaround to $_POST loosing long values.
// @see http://stackoverflow.com/questions/5077969/php-some-post-values-missing-but-are-present-in-php-input
$_POST = getRealPOST();

$test = 0;
$masterpk = required_param('pk', PARAM_RAW);

if(!$test){
    if (empty($masterpk)){
        echo "ERROR : Empty PK ";
    }
}

// avoid shooting in yourself (@see locallib.php§vmoodle_fix_database() )
// VMoodle Master identity has been forced in remote database with its current public key, so we should find it.
// whatever the case, the master record is always added as an "extra" ment_host record, after "self", and "all Hosts".

$remotehost = $DB->get_record_select('mnet_host', " TRIM(REPLACE(public_key, '\r', '')) = TRIM(REPLACE('$masterpk', '\r', '')) AND id > 1 ");

if ($remotehost || $test) {

    // $CFG->bootstrap_init is a key that has been added by master when postprocessing the deployment template
    // We check that the public key given matches the identity of the master who initiated the platform restoring.

    if ($test || (@$CFG->bootstrap_init == $remotehost->wwwroot)) {
    
        // at this time, the local platform may not have self key, or may inherit 
        // an obsolete key from the template SQL backup.
        // we must fix that forcing a local key replacement
        $MNET = new mnet_environment();
        $MNET->init();        
        $MNET->name = '';
        $oldkey = $MNET->public_key;
        $MNET->replace_keys();
        // debug_trace("REMOTE : Replaced keys from \n$oldkey\nto\n{$MNET->public_key}\n");

        // Finally we disable the keyboot script locking definitively the door.
        set_config('bootstrap_init', null);
        echo "SUCCESS";

    } else {
        echo "ERROR : Master host don't match ".@$CFG->bootstrap_init;
    }
} else {
    echo "ERROR : Master host not found or master host key is empty";
}

function getRealPOST() {
    $pairs = explode("&", file_get_contents("php://input"));
    $vars = array();
    if (!empty($pairs)){
        foreach ($pairs as $pair) {
            if(empty($pair)) continue;
            $nv = explode("=", $pair);
            $name = urldecode($nv[0]);
            $value = urldecode($nv[1]);
            $vars[$name] = $value;
        }
    }
    return $vars;
}