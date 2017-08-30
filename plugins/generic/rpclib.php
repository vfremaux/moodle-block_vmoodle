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
 * Created on 18 nov. 2010
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/vmoodle/rpclib.php');

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

function dataexchange_rpc_fetch_config($user, $configkey, $module = '', $json_response = true) {
    global $CFG, $USER;

    // Invoke local user and check his rights.
    if (!preg_match("/$configkey/", @$CFG->dataexchangesafekeys)) {
        if ($auth_response = invoke_local_user((array)$user)) {
            if ($json_response) {
                return $auth_response;
            } else {
                return json_decode($auth_response);
            }
        }
    }

    // Creating response.
    $response = new StdClass();
    $response->status = RPC_SUCCESS;

    $response->value = get_config($module, $configkey);

    if ($json_response) {
        return json_encode($response);
    } else {
        return $response;
    }
}

/**
 * Set on or off maintenance mode.
 * @param object $user The calling user, containing mnethostroot reference and hostroot reference.
 * @param string $message If empty, asks for a maintenance switch off.
 */
function mnetadmin_rpc_set_maintenance($user, $message, $hardmaintenance = false, $json_response = true) {
    global $CFG, $USER;

    debug_trace('RPC '.json_encode($user));

    if ($auth_response = invoke_local_user((array)$user)) {
        if ($json_response) {
            return $auth_response;
        } else {
            return json_decode($auth_response);
        }
    }

    // Creating response.
    $response = new stdClass;
    $response->status = RPC_SUCCESS;

    /*
     * Keep old hard signalled maintenance mode of 1.9. Can be usefull in case database stops
     * but needs a patch in config to catch this real case.
     */
    $filename = $CFG->dataroot.'/maintenance.html';

    if ($message != 'OFF') {
        debug_trace('RPC : Setting maintenance on');
        $file = fopen($filename, 'w');
        fwrite($file, stripslashes($message));
        fclose($file);
        set_config('maintenance_enabled', 1);
        set_config('maintenance_message', $message);
    } else {
        debug_trace('RPC : Setting maintenance off');
        unlink($filename);
        set_config('maintenance_enabled', 0);
        set_config('maintenance_message', null);
    }

    debug_trace('RPC Bind : Sending response');

    // Returns response (success or failure).
    return json_encode($response);
}

/**
 * Set some config values.
 * @param object $user The calling user, containing mnethostroot reference and hostroot reference.
 * @param string $key the config key.
 * @param string $value the config value.
 * @param string $plugin the config plugin, core if empty.
 */
function mnetadmin_rpc_set_config($user, $key, $value, $plugin, $json_response = true) {
    global $CFG, $USER;

    if (!empty($user)) {
        if ($auth_response = invoke_local_user((array)$user)) {
            if ($json_response) {
                return $auth_response;
            } else {
                return json_decode($auth_response);
            }
        }
    }

    // Creating response.
    $response = new stdClass;
    $response->status = RPC_SUCCESS;

    set_config($key, $value, $plugin);

    debug_trace('RPC Bind : Sending response');

    // Returns response (success or failure).
    return json_encode($response);
}

/**
 * Set some config values.
 * @param object $user The calling user, containing mnethostroot reference and hostroot reference.
 * @param string $key the config key.
 * @param string $value the config value.
 * @param string $plugin the config plugin, core if empty.
 */
function mnetadmin_rpc_runpage($user, $pageurl, $params, $httpmode, $json_response = true) {
    global $CFG, $USER;

    if (!empty($user)) {
        if ($auth_response = invoke_local_user((array)$user)) {
            if ($json_response) {
                return $auth_response;
            } else {
                return json_decode($auth_response);
            }
        }
    }

    if ($httpmode == 'GET') {
        redirect($CFG->wwwroot.$pageurl.'?'.$params);
    }

    // Creating response.
    $response = new stdClass;
    $response->status = RPC_SUCCESS;

    // Returns response (success or failure).
    return json_encode($response);
}

/**
 * Purge internally all caches.
 * @param object $user The calling user, containing mnethostroot reference and hostroot reference.
 */
function mnetadmin_rpc_purge_caches($user, $json_response = true) {
    global $CFG, $USER;

    if ($auth_response = invoke_local_user((array)$user)) {
        if ($json_response) {
            return $auth_response;
        } else {
            return json_decode($auth_response);
        }
    }

    // Creating response.
    $response = new stdClass;
    $response->status = RPC_SUCCESS;

    purge_all_caches();

    // Returns response (success or failure).
    return json_encode($response);
}
