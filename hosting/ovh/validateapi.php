<?php

require('../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_capability('moodle/site:configure', $context_system::instance());

admin_externalpage_setup('validateovhapi');

$config = get_config('vmoodlehositngapi_ovh');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('ovhaccessvalidaton', 'vmoodlehostingapi_ovh'));

$validationlink = html_writer::link($config->validationurl, get_string('goandvalidateaccess', 'vmoodlehostingapi_ovh'));

echo $OUTPUT->box($validationlink);

echo $OUTPUT->footer();