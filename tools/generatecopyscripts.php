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
 * @package blocks_vmoodle
 * @category
 * @author Valery Fremaux <valery.fremaux@gmail.com>, <valery@edunao.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/vmoodle/tools/generatecopyscripts_form.php');
require_once($CFG->dirroot.'/blocks/vmoodle/tools/lib.php');

$url = new moodle_url('/blocks/vmoodle/tools/generatecopyscripts.php');
$context = context_system::instance();
$PAGE->set_context($context);

require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_heading(get_string('scriptgenerator', 'block_vmoodle'));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$mform = new CopyScriptsParams_Form();

$datastr = '';
$dbstr = '';
$cronstr = '';
$configstr = '';
$preupgradestr = '';
$upgradestr = '';
$postupgradestr = '';
$backupdbstr = '';
$backupdbtransfer = '';
$restorebackupdbstr = '';
$restorebackupdbtransfer = '';
$dropbackupdbstr = '';
$sudostr = '';

if ($data = $mform->get_data()) {
    $vhosts = $DB->get_records('block_vmoodle', array('enabled' => 1));

    if ($CFG->branch < 28) {
        $vmoodlelocation = 'blocks';
    } else {
        $vmoodlelocation = 'local';
    }

    if ($data->toversion < 28) {
        $vmoodletolocation = 'blocks';
    } else {
        $vmoodletolocation = 'local';
    }

    if ($data->fromversion < 28) {
        $vmoodlefromlocation = 'blocks';
    } else {
        $vmoodlefromlocation = 'local';
    }

    $main = new StdClass;

    // Processing all roots
    $main->olddbname = $CFG->dbname;
    $main->newdbname = str_replace($data->fromversion, $data->toversion, $main->olddbname);

    $main->originwwwroot = $CFG->wwwroot;
    $main->currentwwwroot = $CFG->wwwroot;
    $main->archivewwwroot = change_version($data->fromversion, $data->toversion, $CFG->wwwroot, 'from');
    $main->currentwwwrootsed = remove_moodle_version($main->currentwwwroot);
    $main->currentwwwrootsed = str_replace("/", "\\/", $CFG->wwwroot);
    $main->originwwwrootsed = str_replace("/", "\\/", $main->originwwwroot);
    $main->archivewwwrootsed = str_replace("/", "\\/", $main->archivewwwroot);

    $main->olddataroot = $CFG->dataroot;
    $main->newdataroot = str_replace($data->fromversion, $data->toversion, $CFG->dataroot);
    $main->tomoodledatacontainer = dirname($main->newdataroot);

    $main->olddirroot = $CFG->dirroot;
    $main->newdirroot = str_replace($data->fromversion, $data->toversion, $CFG->dirroot);
    $main->olddirrootsed = str_replace("/", "\\/", $CFG->dirroot);
    $main->newdirrootsed = str_replace("/", "\\/", $main->newdirroot);
    $main->oldmoodledatased = str_replace("/", "\\/", $main->olddataroot);
    $main->newmoodledatased = str_replace("/", "\\/", $main->newdataroot);

    $hostreps = array();
    if ($vhosts) {
        foreach ($vhosts as $vhost) {
            $hostreps[$vhost->name] = new StdClass;

            $hostreps[$vhost->name]->olddbname = $vhost->vdbname;
            $hostreps[$vhost->name]->newdbname = str_replace($data->fromversion, $data->toversion, $hostreps[$vhost->name]->olddbname);

            // this is a bit tricky, but we need to manage soem special cases due to production apparent domains
            $hostreps[$vhost->name]->originwwwroot = $vhost->vhostname;
            $hostreps[$vhost->name]->currentwwwroot = $vhost->vhostname;
            // Explicits the next version
            $hostreps[$vhost->name]->currentwwwroot = change_version($data->fromversion, $data->toversion, $hostreps[$vhost->name]->currentwwwroot, 'to');
            // Revert to explicit archive
            $hostreps[$vhost->name]->archivewwwroot = change_version($data->toversion, $data->fromversion, $hostreps[$vhost->name]->currentwwwroot);
            // Finally locally remove the moodle version marker for production exposed domains.
            // Note that current version may NOT have changed from original in most cases.
            $hostreps[$vhost->name]->currentwwwroot = remove_moodle_version($hostreps[$vhost->name]->currentwwwroot);
            $hostreps[$vhost->name]->originwwwrootsed = str_replace("/", "\\/", $hostreps[$vhost->name]->originwwwroot);
            $hostreps[$vhost->name]->currentwwwrootsed = str_replace("/", "\\/", $hostreps[$vhost->name]->currentwwwroot);
            $hostreps[$vhost->name]->archivewwwrootsed = str_replace("/", "\\/", $hostreps[$vhost->name]->archivewwwroot);

            $hostreps[$vhost->name]->olddataroot = $vhost->vdatapath;
            $hostreps[$vhost->name]->newdataroot = str_replace($data->fromversion, $data->toversion, $hostreps[$vhost->name]->olddataroot);
            $hostreps[$vhost->name]->olddatarootsed = str_replace("/", "\\/", $hostreps[$vhost->name]->olddataroot);
            $hostreps[$vhost->name]->newdatarootsed = str_replace("/", "\\/", $hostreps[$vhost->name]->newdataroot);

            $hostreps[$vhost->name]->olddbname = $vhost->vdbname;
            $hostreps[$vhost->name]->newdbname = str_replace($data->fromversion, $data->toversion, $hostreps[$vhost->name]->olddbname);
            $hostreps[$vhost->name]->olddbnamesed = str_replace("/", "\\/", $hostreps[$vhost->name]->olddbname);
            $hostreps[$vhost->name]->newdbnamesed = str_replace("/", "\\/", $hostreps[$vhost->name]->newdbname);

        }
    }

    // Pre save databases for backup

    // Main host DB copy;
    $backupdbstr = '# Backup DB creation for '.$SITE->fullname."\n";
    $backupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$main->olddbname}_bak;' \n";
    $backupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$main->olddbname}_bak;' \n";

    $backupdbtransfer = '# Backup Data transfer for '.$SITE->fullname."\n";
    $backupdbtransfer .= 'mysqldump '.$main->olddbname.' -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' > temp.sql'."\n";
    $backupdbtransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$main->olddbname.'_bak < temp.sql'."\n";

    // Active Vhosts DB copy
    if ($vhosts) {
        foreach ($vhosts as $vhost) {

            $backupdbstr .= "\n";
            $backupdbstr .= '# Backup DB creation for '.$vhost->name."\n";
            $backupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$hostreps[$vhost->name]->olddbname}_bak ;' \n";
            $backupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$hostreps[$vhost->name]->olddbname}_bak;' \n";

            $backupdbtransfer .= "\n";
            $backupdbtransfer .= '# Backup Data transfer for '.$SITE->fullname."\n";
            $backupdbtransfer .= 'mysqldump '.$hostreps[$vhost->name]->olddbname.' -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' > temp.sql'."\n";
            $backupdbtransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$hostreps[$vhost->name]->olddbname.'_bak < temp.sql'."\n";
        }
    }

    // Drop backup set

    // Main host DB copy;
    $dropbackupdbstr = '# Drop Backup DB for '.$SITE->fullname."\n";
    $dropbackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$main->olddbname}_bak;' \n";

    // Active Vhosts DB copy
    if ($vhosts) {
        foreach ($vhosts as $vhost) {

            $dropbackupdbstr .= "\n";
            $dropbackupdbstr .= '# Drop Backup DB for '.$vhost->name."\n";
            $dropbackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$hostreps[$vhost->name]->olddbname}_bak ;' \n";

        }
    }

    // Backup Vhosts DB restore

    $restorebackupdbstr = '# Drop new DB for '.$SITE->fullname."\n";
    $restorebackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$main->newdbname};' \n";
    $restorebackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$main->olddbname};' \n";
    $restorebackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$main->olddbname};' \n";

    $restorebackupdbtransfer = '# Backup Data transfer for '.$SITE->fullname."\n";
    $restorebackupdbtransfer .= 'mysqldump '.$main->olddbname.'_bak -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' > temp.sql'."\n";
    $restorebackupdbtransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$main->olddbname.' < temp.sql'."\n";

    if ($vhosts) {
        foreach ($vhosts as $vhost) {

            $restorebackupdbstr .= "\n";
            $restorebackupdbstr .= '# Backup DB creation for '.$vhost->name."\n";
            $restorebackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$hostreps[$vhost->name]->newdbname};' \n";

            $restorebackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$hostreps[$vhost->name]->olddbname};' \n";
            $restorebackupdbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$hostreps[$vhost->name]->olddbname};' \n";

            $restorebackupdbtransfer .= "\n";
            $restorebackupdbtransfer .= '# Backup Data transfer for '.$SITE->fullname."\n";
            $restorebackupdbtransfer .= 'mysqldump '.$hostreps[$vhost->name]->olddbname.'_bak -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' > temp.sql'."\n";
            $restorebackupdbtransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$hostreps[$vhost->name]->olddbname.' < temp.sql'."\n";
        }
    }

    // Database generator

    // Main host DB copy;
    $dbstr = '# DB copy for '.$SITE->fullname."\n";
    $dbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$main->newdbname};' \n";
    $dbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$main->newdbname};' \n";


    $datatransfer = '# Data transfer for '.$SITE->fullname."\n";
    $datatransfer .= 'mysqldump '.$main->olddbname.' -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' > temp'.$data->fromversion.'.sql'."\n";
    $datatransfer .= '/bin/cp -f temp'.$data->fromversion.'.sql temp'.$data->toversion.'.sql'."\n";
    $datatransfer .= 'sed -i \'s/'.$main->currentwwwrootsed.'/'.$main->archivewwwrootsed.'/g\' temp'.$data->fromversion.'.sql'."\n";
    $datatransfer .= 'sed -i \'s/'.$main->originwwwrootsed.'/'.$main->currentwwwrootsed.'/g\' temp'.$data->toversion.'.sql'."\n";

    // process main database for all peer host names, paths and references
    if ($vhosts) {
        foreach ($vhosts as $vhost) {
            // this is a special copy that works the opposite way : newwwwroot is the older version to patch into the older database.
            $datatransfer .= 'sed -i \'s/'.$hostreps[$vhost->name]->currentwwwrootsed.'/'.$hostreps[$vhost->name]->archivewwwrootsed.'/g\' temp'.$data->fromversion.'.sql'."\n";
            $datatransfer .= 'sed -i \'s/'.$hostreps[$vhost->name]->originwwwrootsed.'/'.$hostreps[$vhost->name]->currentwwwrootsed.'/g\' temp'.$data->toversion.'.sql'."\n";

            $datatransfer .= 'sed -i \'s/'.$hostreps[$vhost->name]->olddatarootsed.'/'.$hostreps[$vhost->name]->newdatarootsed.'/g\' temp'.$data->toversion.'.sql'."\n";
            $datatransfer .= 'sed -i \'s/'.$hostreps[$vhost->name]->olddbnamesed.'/'.$hostreps[$vhost->name]->newdbnamesed.'/g\' temp'.$data->toversion.'.sql'."\n";
        }
    }

    $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$main->newdbname.' < temp'.$data->toversion.'.sql'."\n";

    // old DB replacement
    $datatransfer .= '# Old DB adjustements for '.$SITE->fullname."\n";
    $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$main->olddbname};' \n";
    $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$main->olddbname};' \n";
    $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$main->olddbname.' < temp'.$data->fromversion.'.sql'."\n";

    // Main host replacements

    // Active Vhosts DB copy
    if ($vhosts) {
        foreach ($vhosts as $vhost) {

            $dbstr .= "\n";
            $dbstr .= '# DB copy for '.$vhost->name."\n";
            $dbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$hostreps[$vhost->name]->newdbname};' \n";
            $dbstr .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$hostreps[$vhost->name]->newdbname};' \n";

            $datatransfer .= "\n";
            $datatransfer .= '# Data transfer for '.$vhost->name."\n";
            $datatransfer .= 'mysqldump '.$hostreps[$vhost->name]->olddbname.' -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' > temp'.$data->fromversion.'.sql'."\n";
            $datatransfer .= '/bin/cp -f temp'.$data->fromversion.'.sql temp'.$data->toversion.'.sql'."\n";
            $datatransfer .= 'sed -i \'s/'.$hostreps[$vhost->name]->originwwwrootsed.'/'.$hostreps[$vhost->name]->archivewwwrootsed.'/g\' temp'.$data->fromversion.'.sql'."\n";
            $datatransfer .= 'sed -i \'s/'.$main->currentwwwrootsed.'/'.$main->archivewwwrootsed.'/g\' temp'.$data->fromversion.'.sql'."\n";
            $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$hostreps[$vhost->name]->newdbname.' < temp'.$data->toversion.'.sql'."\n";

            // old DB replacement
            $datatransfer .= '# Old DB adjustements for '.$vhost->name."\n";
            $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'DROP DATABASE IF EXISTS {$hostreps[$vhost->name]->olddbname};' \n";
            $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass."' -e 'CREATE DATABASE {$hostreps[$vhost->name]->olddbname};' \n";
            $datatransfer .= 'mysql -h'.$CFG->dbhost.' -u'.$CFG->dbuser.' -p\''.$CFG->dbpass.'\' '.$hostreps[$vhost->name]->olddbname.' < temp'.$data->fromversion.'.sql'."\n";
        }
    }

    // Main host data copy
    $datastr = '# Data copy for '.$SITE->fullname."\n";
    $datastr .= "sudo -uwww-data rm -rf {$main->newdataroot}\n";
    $datastr .= "sudo -uwww-data rsync -r {$main->olddataroot} {$main->tomoodledatacontainer}\n";

    // Active Vhosts data copy
    if ($vhosts) {
        foreach ($vhosts as $vhost) {

            $datastr .= "\n";
            $datastr .= '# Data copy for '.$vhost->name."\n";
            $datastr .= "sudo -uwww-data rm -rf {$hostreps[$vhost->name]->newdataroot}\n";
            $datastr .= "sudo -uwww-data mkdir {$hostreps[$vhost->name]->newdataroot}\n";
            $datastr .= "sudo -uwww-data rsync -r {$hostreps[$vhost->name]->olddataroot} {$main->tomoodledatacontainer}\n";

            // Vhost replacements
        }
    }

    // Moodle cronlines generator
    // Moodle data generator

    // Main host DB copy;
    $cronstr = '# Cronlines '.$SITE->fullname."\n";
    if ($data->cronmode == 'cli') {
        $cronstr .= '*/10 * * * *  php '.$main->newdirroot.'/admin/cli/cron.php'."\n";
        $cronstr .= '*/1 * * * *  php '.$main->newdirroot.'/blocks/vmoodle/cli/vcron.php'."\n";
    } else {
        $cronstr .= '*/10 * * * *  wget -q -O /dev/null '.$main->newwwwroot.'/admin/cron.php'."\n";
        $cronstr .= '*/1 * * * *  wget -q -O /dev/null '.$main->newwwwroot.'/blocks/vmoodle/vcron.php'."\n";
    }

    // Moodle tools sudo generator
    // Moodle data generator
    if (is_dir($CFG->dirroot.'/admin/tool/delivery')) {
        $sudostr = '# Sudo file processing (must be root)'."\n";
        $sudostr .= '/bin/cp -f /etc/sudoers.d/moodle'.$data->fromversion.'_sudos /etc/sudoers.d/moodle'.$data->toversion."_sudos\n";
        $sudostr .= 'chmod u+w /etc/sudoers.d/moodle'.$data->toversion."_sudos\n";
        $sudostr .= 'sed -i \'s/'.$data->fromversion.'/'.$data->toversion.'/g\' /etc/sudoers.d/moodle'.$data->toversion."_sudos\n";
        $sudostr .= 'chmod u-w /etc/sudoers.d/moodle'.$data->toversion."_sudos\n";
    }

    // Main host config change
    $configstr = "/bin/cp -f {$main->newdirroot}/config.php {$main->newdirroot}/config.php.bak\n";
    $configstr .= "/bin/cp -f {$main->olddirroot}/config.php {$main->newdirroot}/config.php\n";
    $configstr .= "/bin/cp -f {$main->newdirroot}/{$vmoodletolocation}/vmoodle/vconfig.php {$main->newdirroot}/{$vmoodletolocation}/vmoodle/vconfig.php.bak\n";
    $configstr .= "/bin/cp -f {$main->olddirroot}/{$vmoodlefromlocation}/vmoodle/vconfig.php {$main->newdirroot}/{$vmoodletolocation}/vmoodle/vconfig.php\n";

    $configstr .= 'sed -i \'s/'.$main->currentwwwrootsed.'/'.$main->archivewwwrootsed.'/g\' '."{$main->olddirroot}/config.php\n";
    $configstr .= 'sed -i \'s/'.$main->olddirrootsed.'/'.$main->newdirrootsed.'/g\' '."{$main->newdirroot}/config.php\n";
    $configstr .= 'sed -i \'s/'.$main->oldmoodledatased.'/'.$main->newmoodledatased.'/g\' '."{$main->newdirroot}/config.php\n";
    $configstr .= 'sed -i \'s/'.$main->olddbname.'/'.$main->newdbname.'/g\' '."{$main->newdirroot}/config.php\n";

    $configstr .= 'sed -i \'s/'.$main->olddbname.'/'.$main->newdbname.'/g\' '."{$main->newdirroot}/{$vmoodletolocation}/vmoodle/vconfig.php\n";

    // Main host upgrade
    $preupgradestr = '# Pre upgrade for '.$SITE->fullname."\n";
    $preupgradestr .= "sudo -uwww-data php {$main->newdirroot}/admin/cli/mysql_compressed_rows.php --fix\n";

    $upgradestr = '# Full upgrade for '.$SITE->fullname."\n";
    $upgradestr .= "sudo -uwww-data php {$main->newdirroot}/admin/cli/upgrade.php  --non-interactive --allow-unstable\n";

    $postupgradestr = '# Post upgrade for '.$SITE->fullname."\n";
    $postupgradestr .= "sudo -uwww-data php {$main->newdirroot}/admin/cli/purge_caches.php\n";
    $postupgradestr .= "sudo -uwww-data php {$main->olddirroot}/blocks/user_mnet_hosts/cli/resync.php --host={$main->archivewwwroot}\n";
    $postupgradestr .= "wget {$main->archivewwwroot}/admin/cron.php?forcerenew=1\n";

    // Active Vhosts upgrades
    if ($vhosts) {
        foreach ($vhosts as $vhost) {

            $upgradestr .= "\n";
            $upgradestr .= '# Full upgrade for ['.$vhost->name.'] '.$vhost->vhostname."\n";
            $upgradestr .= "sudo -uwww-data php {$main->newdirroot}/{$vmoodletolocation}/vmoodle/cli/upgrade.php --host={$hostreps[$vhost->name]->currentwwwroot} --non-interactive --allow-unstable\n";

            $preupgradestr .= "\n";
            $preupgradestr .= '# Pre upgrade for ['.$vhost->name.'] '.$vhost->vhostname."\n";
            $preupgradestr .= "sudo -uwww-data php {$main->newdirroot}/{$vmoodletolocation}/vmoodle/cli/mysql_compressed_rows.php --fix --host={$hostreps[$vhost->name]->currentwwwroot}\n";

            $postupgradestr .= '# Post upgrade for ['.$vhost->name.'] '.$vhost->vhostname."\n";
            $postupgradestr .= "sudo -uwww-data php {$main->newdirroot}/{$vmoodletolocation}/vmoodle/cli/purge_caches.php --host={$hostreps[$vhost->name]->currentwwwroot}\n";
            if (is_dir($CFG->dirroot.'/blocks/user_mnet_hosts')) {
                $postupgradestr .= "sudo -uwww-data php {$main->olddirroot}/blocks/user_mnet_hosts/cli/resync.php --host={$hostreps[$vhost->name]->archivewwwroot}\n";
            }
            if (is_dir($CFG->dirroot.'/blocks/vmoodle')) {
                $postupgradestr .= "wget {$hostreps[$vhost->name]->archivewwwroot}/admin/cron.php?forcerenew=1\n";
            }

        }
    }
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('copyscripts', 'block_vmoodle'), 2);

$blockid = 1;

if ($backupdbstr) {
    echo $OUTPUT->heading(get_string('backupdbcopyscript', 'block_vmoodle'), 3);
    echo $OUTPUT->heading(get_string('makebackup', 'block_vmoodle'), 4);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$backupdbstr.'</pre>');
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$backupdbtransfer.'</pre>');
    echo $OUTPUT->heading(get_string('restorebackup', 'block_vmoodle'), 4);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$restorebackupdbstr.'</pre>');
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$restorebackupdbtransfer.'</pre>');
    echo $OUTPUT->heading(get_string('dropbackup', 'block_vmoodle'), 4);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$dropbackupdbstr.'</pre>');
}

if ($dbstr) {
    echo $OUTPUT->heading(get_string('dbcopyscript', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$dbstr.'</pre>');
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$datatransfer.'</pre>');
}

if ($datastr) {
    echo $OUTPUT->heading(get_string('datacopyscript', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$datastr.'</pre>');
}

if ($configstr) {
    echo $OUTPUT->heading(get_string('adjustconfig', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$configstr.'</pre>');
}

if ($preupgradestr) {
    echo $OUTPUT->heading(get_string('preupgrade', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$preupgradestr.'</pre>');
}

if ($upgradestr) {
    echo $OUTPUT->heading(get_string('upgrade', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$upgradestr.'</pre>');
}

if ($postupgradestr) {
    echo $OUTPUT->heading(get_string('postupgrade', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$postupgradestr.'</pre>');
}

if ($cronstr) {
    echo $OUTPUT->heading(get_string('cronlines', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$cronstr.'</pre>');
}

if ($sudostr) {
    echo $OUTPUT->heading(get_string('sudos', 'block_vmoodle'), 3);
    echo '<div>Block: '.$blockid.'</div>';
    $blockid++;
    echo $OUTPUT->box('<pre>'.$sudostr.'</pre>');
}

$mform->display();

echo $OUTPUT->footer();
