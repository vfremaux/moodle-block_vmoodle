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

/*
 * Created on 20 sept. 2013
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/vmoodle/rpclib.php');
require_once($CFG->dirroot.'/blocks/vmoodle/lib.php');
require_once($CFG->libdir.'/adminlib.php');       // Various admin-only functions.
require_once($CFG->libdir.'/upgradelib.php');     // General upgrade/install related functions.
require_once($CFG->libdir.'/environmentlib.php');
require_once($CFG->libdir.'/pluginlib.php');

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
    define('MNET_FAILURE', 511);
    define('RPC_FAILURE_RECORD', 520);
    define('RPC_FAILURE_RUN', 521);
}

function mnetadmin_rpc_upgrade($user, $json_response = true) {
    global $CFG, $USER;

    // Invoke local user and check his rights.
    if ($auth_response = invoke_local_user((array)$user)) {
        if ($json_response) {
            return $auth_response;
        } else {
            return json_decode($auth_response);
        }
    }

    // Creating response
    $response = new stdclass();
    $response->status = RPC_SUCCESS;

    require("$CFG->dirroot/version.php");       // Defines $version, $release, $branch and $maturity.
    $CFG->target_release = $release;            // Used during installation and upgrades.

    if ($version < $CFG->version) {
        $response->status = RPC_FAILURE_RUN;
        $response->error = get_string('downgradedcore', 'error');
        $response->errors[] = get_string('downgradedcore', 'error');
        if ($json_response){
            return json_encode($response);
        } else {
            return $response;
        }
    }

    $oldversion = "$CFG->release ($CFG->version)";
    $newversion = "$release ($version)";

    if (!moodle_needs_upgrading()) {
        $response->message = get_string('cliupgradenoneed', 'core_admin', $newversion);
        if ($json_response){
            return json_encode($response);
        } else {
            return $response;
        }
    }

    list($envstatus, $environment_results) = check_moodle_environment(normalize_version($release), ENV_SELECT_NEWER);
    if (!$envstatus) {
        $response->status = RPC_FAILURE_RUN;
        $response->error = vmoodle_get_string('environmentissues', 'vmoodleadminset_upgrade');
        $response->errors[] = vmoodle_get_string('environmentissues', 'vmoodleadminset_upgrade');
        $response->detail = $environment_results;
        if ($json_response){
            return json_encode($response);
        } else {
            return $response;
        }
    }

    // Test plugin dependencies.
    $failed = array();
    if (!plugin_manager::instance()->all_plugins_ok($version, $failed)) {
        $response->status = RPC_FAILURE_RUN;
        $response->error = get_string('pluginschecktodo', 'admin');
        $response->errors[] = get_string('pluginschecktodo', 'admin');
        if ($json_response){
            return json_encode($response);
        } else {
            return $response;
        }
    }

    ob_start();

    if ($version > $CFG->version) {
        upgrade_core($version, false);
    }
    set_config('release', $release);
    set_config('branch', $branch);

    // Unconditionally upgrade.

    upgrade_noncore(false);

    // Log in as admin - we need doanything permission when applying defaults.

    session_set_user(get_admin());

    // Apply all default settings, just in case do it twice to fill all defaults.

    admin_apply_default_settings(null, false);
    admin_apply_default_settings(null, false);
    ob_end_clean();

    $response->message = vmoodle_get_string('upgradecomplete', 'vmoodleadminset_upgrade', $newversion);

    if ($json_response){
        return json_encode($response);
    } else {
        return $response;
    }
}