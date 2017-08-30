<?php

$settings->add(new admin_setting_configtext('vmoodlehostingapi_ovh/serviceurl', vmoodle_get_string('serviceurl','vmoodlehostingapi_ovh'),
            vmoodle_get_string('serviceurl_desc','vmoodlehostingapi_ovh')));

$settings->add(new admin_setting_configtext('vmoodlehostingapi_ovh/serviceappkey', vmoodle_get_string('serviceappkey','vmoodlehostingapi_ovh'),
            vmoodle_get_string('serviceappkey_desc','vmoodlehostingapi_ovh')));

$settings->add(new admin_setting_configtext('vmoodlehostingapi_ovh/servicesecret', vmoodle_get_string('servicesecret','vmoodlehostingapi_ovh'),
            vmoodle_get_string('servicesecret_desc','vmoodlehostingapi_ovh')));

$settings->add(new admin_setting_configtext('vmoodlehostingapi_ovh/consumerkey', vmoodle_get_string('consumerkey','vmoodlehostingapi_ovh'),
            vmoodle_get_string('consumerkey_desc','vmoodlehostingapi_ovh')));

$settings->add(new admin_setting_configtext('vmoodlehostingapi_ovh/masterdomain', vmoodle_get_string('masterdomain','vmoodlehostingapi_ovh'),
            vmoodle_get_string('masterdomain_desc','vmoodlehostingapi_ovh')));

$settings->add(new admin_setting_configtext('vmoodlehostingapi_ovh/localipaddress', vmoodle_get_string('masterdomain','vmoodlehostingapi_ovh'),
            vmoodle_get_string('localipaddress_desc','vmoodlehostingapi_ovh')));
