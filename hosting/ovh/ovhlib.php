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
 * ovhlib.php
 *
 * A wrapping API to OVH hosting services.
 *
 * @package block_vmoodle
 * @subpackage hosting
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * checks if a dns record is registered at OVH side
 *
 */
function ovh_exists_dns_record($record) {

    $config = get_config('vmoodlehostingapi_ovh');

    // Open OVH API session.

    // Launch servide API.

    // Get results.
}

/**
 * registers a new OVH DNS record
 *
 */
function ovh_register_dns_record($record) {

    $config = get_config('vmoodlehostingapi_ovh');

    // Open OVH API session.

    // Launch servide API.

    // Get results.
}

/**
 * deletes a new OVH DNS record
 *
 */
function ovh_remove_dns_record($record) {

    $config = get_config('vmoodlehostingapi_ovh');

    // Open OVH API session.

    // Launch servide API.

    // Get results.
}

function ovh_get_authentication_token() {
    $url = 'https://eu.api.ovh.com/1.0/auth/credential';

    $curl = curl_init();

    $headers = array('Content-type: application/json', 'X-Ovh-Application: '.$config->serviceappkey);

    $rq = '{"accessrules":[{"method":"GET","path":"/domain/zone/*"},{"method":"POST","path":"/domain/zone/*", {"method":"DELETE","path":"/domain/zone/*"}}],"redirection":'.$CFG->wwwroot.'}';

    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_POST, true);
    curl_setopt($s, CURLOPT_POSTFIELDS, $rq);
    curl_setopt($s, CURLOPT_TIMEOUT, 30);
    curl_setopt($s, CURLOPT_MAXREDIRS, 10);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($s, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($s, CURLOPT_HTTPHEADER, $headers);

    $answer = json_decode(curl_exec($curl));

    if (!empty($answer)) {
        set_config('consumerkey', $answer->consumerKey, 'vmoodlehostingapi_ovh');
        set_config('state', $answer->state, 'vmoodlehostingapi_ovh');
        set_config('validationurl', $answer->validationUrl, 'vmoodlehostingapi_ovh');
        return true;
    }

    return false;
}

function ovh_hash_query_signature($method, $query, $body) {
    $config = get_config('vmoodlehostingapi_ovh');

    $now = time();
    if (!empty($config->timeoffset)) {
        $now += $config->timeoffset;
    }

    $signature = $config->servicesecret.'+'.$config->authtoken.'+'.$method.'+'.$query.'+'.$body.'+'.$now;

    $hashed = sha1($signature);

    return $hashed;
}

/**
 * Gets the OVH internal time so that a shift may be applied to all timestamps
 *
 */
function ovh_get_ovh_time() {
    $url = 'https://eu.api.ovh.com/1.0/auth/time';

    $curl = curl_init();

    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_TIMEOUT, 30);
    curl_setopt($s, CURLOPT_MAXREDIRS, 10);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);

    $now = time();
    $raw = curl_exec($curl);

    set_config('remotetime', $raw, 'vmoodlehostingapi_ovh');
    set_config('timeoffset', $raw - $now, 'vmoodlehostingapi_ovh');

    return $raw;
}

function ovh_get_query($path, $method, $params) {

    $config = get_config('vmoodlehostingapi_ovh');
    $s = curl_init();

    curl_setopt($s, CURLOPT_URL, $url);

    if ($method == 'POST') {
        curl_setopt($s, CURLOPT_POST, true);
        curl_setopt($s, CURLOPT_POSTFIELDS, $rq);
    } else if ($method == 'PUT') {
        curl_setopt($s, CURLOPT_PUT, true);
    } else if ($method == 'DELETE') {
        curl_setopt($s, CURLOPT_CUSTOMREQUEST, 'DELETE');
    } else {
        $pairs = array();
        foreach ($params as $key => $value) {
            $pairs[] = $key.'='.urlencode($value);
        }

        $querystring = implode('&', $pairs);
        // GET
        $query = $get.'?'.$querystring;
    }

    $headers['X-Ovh-Application'] = $config->serviceappkey;
    $headers['X-Ovh-Timestamp'] = time() + @$config->timeoffset;
    $headers['X-Ovh-Signature'] = ovh_hash_query_signature($method, $query, $body);
    $headers['X-Ovh-Consumer'] = '';

    curl_setopt($s, CURLOPT_TIMEOUT, 30);
    curl_setopt($s, CURLOPT_MAXREDIRS, 10);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($s, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($s, CURLOPT_HTTPHEADER, $headers);

    $answer = json_decode(curl_exec($curl));

}