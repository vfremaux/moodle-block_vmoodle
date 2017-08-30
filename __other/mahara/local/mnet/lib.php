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
 * this function hooks cases of keyswap being called with forced mode.
 * Forced mode can only be used from hosts we trust untill now.
 *
 * @see Readme.txt for patch information
 */
function local_xmlrpc_key_forced_keyswap($wwwroot, $pubkey, $application) {

    $now = time();
    // Reinforced security : only known host with still valid key can force us renewal.
    if ($exists = get_records_select_array('host', " wwwroot = '$wwwroot' AND deleted = 0 AND publickeyexpires >= $now ")){
        try {
            $peer = new Peer();

            if ($peer->findByWwwroot($wwwroot)){
                $pk = new PublicKey($pubkey, $wwwroot);
                $peer->publickey = $pk;
                $peer->commit();
            }
            // Mahara return his own key.
            $openssl = OpenSslRepo::singleton();
            return $openssl->certificate;
        } catch (Exception $e) {
            throw new SystemException($e->getMessage(), $e->getCode());
        }
    } else {
        throw new SystemException("Fails exists known $wwwroot as wwwroot", 6100);
    }
}