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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/vmoodle/hosting/ovh/ovhlib.php');

class vmoodlehostingapi_ovh extends hosting_api {

    public function register_hostname($vhost) {

        $config = get_config('vmoodlehostingapi_ovh');
        if (preg_match('/'.$config->masterdomain.'$/', $vhost->vhostname)) {
            $record = new StdClass();

            $record->hostname = $vhost->vhostname;
            $record->type = 'A';
            $record->ip = $config->localipaddress;

            if (!ovh_register_dns_record($record)) {
                throw new vmoodlehostingapi_ovh_exception();
            }
        }
    }

    public function unregister_hostname($vhost) {

        $config = get_config('vmoodlehostingapi_ovh');

        if (preg_match('/'.$config->masterdomain.'$/', $vhost->vhostname)) {
            $record = new StdClass();

            $record->hostname = $vhost->vhostname;
            $record->type = 'A';
            $record->ip = $config->localipaddress;

            if (ovh_dns_record_exists($record)) {
                if (!ovh_delete_dns_record($record)) {
                    throw new vmoodlehostingapi_ovh_exception();
                }
            }
        }
    }
}

class vmoodlehostingapi_ovh extends Exception {
}