<?php

// this is an emergency physical cache fix (cache purge)
// in case of severe cacheing inconsistancies

require('../../../config.php');

require_login();
$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

// This is a special fixture in cas we loose mnet auth settings

$vmoodles = $DB->get_records_select('block_vmoodle', " mnet > 0 ");

echo '<pre>';
mtrace("Removing all cache data");

foreach ($vmoodles as $vm) {
    // Remove caches.
    mtrace("Removing physical cache data for $vm->vhostname");

    $cmd = "rm -rf {$vm->vdatapath}/cache/*";
    exec($cmd);

    $cmd = "rm -rf {$vm->vdatapath}/muc/*";
    exec($cmd);
}
echo '</pre>';
