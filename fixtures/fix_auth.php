<?php

require("../../../config.php");

require_login();
$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

// This is a special fixture in cas we loose mnet auth settings

$vmoodles = $DB->get_records_select('block_vmoodle', " mnet > 0 ");

echo '<pre>';
mtrace("Fixing Auth Stacks in DBs");

foreach($vmoodles as $vm) {
    $sql = "
        UPDATE
            `{$vm->vdbname}`.`{$vm->vdbprefix}config`
        SET
            value = CONCAT(value, ',mnet')
        WHERE
            name = 'auth'
    ";
    $DB->execute($sql);

    $sql = "
        UPDATE
            `{$vm->vdbname}`.`{$vm->vdbprefix}config`
        SET
            value = REPLACE(value, ',mnet,mnet', ',mnet')
        WHERE
            name = 'auth'
    ";
    $DB->execute($sql);
    mtrace("Done with $vm->vhostname");

    // Remove MUC cache to clear config.
    unlink($vm->vdatapath.'/muc/config.php');
}
echo '</pre>';
