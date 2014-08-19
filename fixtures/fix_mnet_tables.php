<?php

/**
 * This script is a fixture that checks the whole rpc/service/host mnet tables to eliminate surnumerous rpc declares
 *
 */
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/vmoodle/fixtures/fix_mnet_tables_lib.php');

// Security.

$context = context_system::instance();
$url = new moodle_url('/block/vmoodle/fixtures/fix_mnet_tables.php');
$PAGE->set_url($url);
$PAGE->set_context($context);

require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add('MNET Fixes');

echo $OUTPUT->header();

echo $OUTPUT->heading('Mnet Tables Consistancy Cleaner');

fix_mnet_tables_fixture();

echo $OUTPUT->footer();
