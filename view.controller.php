<?php

/**
 * This file catches an action and do the corresponding usecase.
 * Called by 'view.php'.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * @usecase doaddupdate
 * @usecase add
 * @usecase edit
 * @usecase delete
 */

include_once("{$CFG->dirroot}/blocks/vmoodle/filesystemlib.php");
include_once("{$CFG->dirroot}/mnet/lib.php");

/**************************** add the new vmoodle host and installs it ************/
if ($action == 'doaddupdate'){
    $form->id = optional_param('id', '', PARAM_INT);
    $form->name = required_param('name', PARAM_TEXT);
    $form->shortname = required_param('shortname', PARAM_TEXT);
    $form->description = required_param('description', PARAM_CLEANHTML);
    $form->vhostname = required_param('vhostname', PARAM_TEXT);
    $form->vdbtype = required_param('vdbtype', PARAM_TEXT);
    $form->vdbhost = required_param('vdbhost', PARAM_TEXT);
    $form->vdblogin = required_param('vdblogin', PARAM_TEXT);
    $form->vdbpass = required_param('vdbpass', PARAM_RAW);
    $form->vdbprefix = required_param('vdbprefix', PARAM_CLEANHTML);
    $form->vdbpersist = required_param('vdbpersist', PARAM_INT);
    $form->vdbname = required_param('vdbname', PARAM_RAW);
    $form->vdatapath = required_param('vdatapath', PARAM_RAW);
    $form->mnet = required_param('mnet', PARAM_INT);
    $v = 'v'.required_param('v', PARAM_INT);

    $vmoodle->name = $form->name;
    $vmoodle->shortname = $form->shortname;
    $vmoodle->description = $form->description;
    $vmoodle->vhostname = $form->vhostname;
    $vmoodle->vdbtype = $form->vdbtype;
    $vmoodle->vdbhost = $form->vdbhost;
    $vmoodle->vdblogin = $form->vdblogin;
    $vmoodle->vdbpass = $form->vdbpass;
    $vmoodle->vdbname = $form->vdbname;
    $vmoodle->vdbprefix = $form->vdbprefix;
    $vmoodle->vdbpersist = $form->vdbpersist;
    $vmoodle->vdatapath = $CFG->block_vmoodle_vdatapathbase.'/'.$form->vdatapath;
    $vmoodle->mnet = $form->mnet;

    if ($CFG->ostype != 'WINDOWS'){
        $form->crontab = required_param('crontab', PARAM_RAW);
    }
    unset($errors);
    echo $OUTPUT->box_start();
    if(empty($form->shortname) || empty($form->vhostname)){
        $erroritem->message = get_string('musthaveshortname', 'block_vmoodle');
        $erroritem->on = 'shortname,vhostname';
        $errors[] = $erroritem;
    } else if ($DB->get_record('block_vmoodle', array('name' => $form->name))) {
        $erroritem->message = get_string('hostexists', 'block_vmoodle');
        $erroritem->on = 'name';
        $errors[] = $erroritem;
    } else if ($DB->get_record('block_vmoodle', array('shortname' => $form->shortname))) {
        $erroritem->message = get_string('shortnameexists', 'block_vmoodle');
        $erroritem->on = 'shortname';
        $errors[] = $erroritem;
    } else if ($DB->get_record('block_vmoodle', array('vhostname' => $form->vhostname))) {
        $erroritem->message = get_string('hostnameexists', 'block_vmoodle');
        $erroritem->on = 'shortname';
        $errors[] = $erroritem;
    } else {
        /*
         * cannot install anything when editing data.
         * installing a vmoodle needs delete/add procedure
         * the update function is only provided for fixing
         * wring parameter values that have not influence upon
         * physical bindings.
         */
        if ($form->id == ''){

            if(!file_exists($form->vdatapath)){
                if (!filesystem_create_dir($form->vdatapath, FS_RECURSIVE, $CFG->block_vmoodle_vdatapathbase)){
                    $erroritem->message = get_string('couldnotcreatedataroot', 'block_vmoodle'). " ".$form->vdatapath;
                    $erroritem->on = 'vdatapath';
                    $errors[] = $erroritem;
                } else {
                    filesystem_copy_tree("{$CFG->dirroot}/blocks/vmoodle/{$v}_vmoodledata", $vmoodle->vdatapath, '');
                }
            } else {
                $done[] = 'datapath';
                print_string('datapathcreated', 'block_vmoodle');
                echo "<br/>";
            }
            // Try to create database.
            $side_cnx = vmoodle_make_connection($form, false);
            if (!$side_cnx){
                $erroritem->message = get_string('couldnotconnecttodb', 'block_vmoodle');
                $erroritem->on = 'db';
                $errors[] = $erroritem;
            } else {
                // Drop any previous database that could be in the way.
                @vmoodle_drop_database($form, $side_cnx);
                if($form->vdbtype == 'mysql'){
                    $sql = "
                       CREATE DATABASE `{$form->vdbname}` CHARACTER SET 'utf8'
                    ";
                } else if($form->vdbtype == 'posstgres'){
                    $sql = "
                       CREATE DATABASE {$form->vdbname} WITH OWNER={$form->vdblogin} ENCODING=UTF8
                    ";
                }
                $res = vmoodle_execute_query($form, $sql, $side_cnx);
                if (!$res){
                    $erroritem->message = get_string('couldnotcreatedb', 'block_vmoodle');
                    $erroritem->on = 'db';
                    $errors[] = $erroritem;
                } else {
                    $done[] = 'database';
                    print_string('databasecreated', 'block_vmoodle');
                    echo "<br/>";
                }
                // Make a new connection so we can bind to database.
                vmoodle_close_connection($form, $side_cnx);
                $side_cnx = vmoodle_make_connection($form, true);

                // Prepare a filter for absolute www roots.
                $manifest = vmoodle_get_vmanifest($v);
                $filter[$manifest->templatehost] = $form->vhostname;
                // Try to setup full datamodel loading database template.
                if ($res = vmoodle_load_db_template($form, "{$CFG->dirroot}/blocks/vmoodle/{$v}_sql/vmoodle_master.{$form->vdbtype}.sql", $side_cnx, $filter)){
                    $errors[] = $res;
                } else {
                    $done[] = 'databaseloaded';
                    print_string('databaseloaded', 'block_vmoodle');
                    echo "<br/>";
                }
                /// TODO run customisation SQL script
                $vars = get_object_vars($vmoodle);
                $vars['sessioncookie'] = strtoupper($vars->shortname);
                $vars['sessioncookie'] = preg_replace("/[-_]/", '', $vars['sessioncookie']); // strips out token damaging chars
                if ($res = vmoodle_load_db_template($form, "{$CFG->dirroot}/blocks/vmoodle/{$v}_sql/vmoodle_setup_template.{$form->vdbtype}.sql", $side_cnx, $vars)){
                    $erroritem->message = get_string('errorsetupdb', 'block_vmoodle');
                    $erroritem->on = 'db';
                    $errors[] = $erroritem;
                } else {
                    $done[] = 'databasesetup';
                    print_string('databasesetup', 'block_vmoodle');
                    echo "<br/>";
                }
                // MNET cross-registration : if mnet enabled cross register the new instance.
                if ($form->mnet){
                    // Check master host is mnet enabled.
                    if ($CFG->mnet_dispatcher_mode == 'strict'){

                        $services = vmoodle_get_service_desc();
                        // Make a moodle mnet env for the new vmoodle
                        $mnet_env = vmoodle_setup_mnet_environment($form, $side_cnx);
                        unset($mnet_env->keypair); // do not fit in mnet_host records.

                        // Record "this" environment in new peer.
                        echo "registering VMaster in peer<br/> ";
                        $remote_MNET = clone($MNET);
                        unset($remote_MNET->id); // Will force insertion in peer's database.
                        unset($remote_MNET->keypair);
                        $peer_master_env = vmoodle_register_mnet_peer($form, $remote_MNET, $side_cnx);
                        // Register services for this in peer.
                        vmoodle_add_services($form, $peer_master_env, $side_cnx, $services);

                        // Register the new vmoodle in "this" known hosts.
                        echo "registering in VMaster<br/>";
                        $thismoodle = vmoodle_make_this();
                        $this_cnx = vmoodle_make_connection($thismoodle, true);
                        if ($this_cnx){
                            $master_mnet_env = vmoodle_register_mnet_peer($thismoodle, $mnet_env, $this_cnx);
                            // Register services for peer in master (this).
                            vmoodle_add_services($thismoodle, $master_mnet_env, $this_cnx, $services);
                            vmoodle_close_connection($thismoodle, $this_cnx);
                        } else {
                            echo "Error with local connection";
                        }

                        // Register in vmoodle peers.
                        $mnet_peers = array();
                        echo "Examining other peers<br/>";
                        $mnet_moodles = $DB->get_records('block_vmoodle', array('mnet' => 1));
                        if (!empty($mnet_moodles)){
                            foreach($mnet_moodles as $peervmoodle){
                                /*
                                 * register new vmoodle in older vmoodles and get older vmoodle
                                 * definitions to make local's.
                                 */
                                if ($peer_cnx = vmoodle_make_connection($peervmoodle, true)){
                                    echo "Registering peer in {$peervmoodle->name}<br/> ";
                                    $mnet_peer_envs[] = vmoodle_get_mnet_env($peervmoodle);
                                    $peer_mnet_env = vmoodle_register_mnet_peer($peervmoodle, $mnet_env, $peer_cnx);
                                    // Register services for peer in other peers.
                                    vmoodle_add_services($peervmoodle, $peer_mnet_env, $peer_cnx, $services);
                                    vmoodle_close_connection($peervmoodle, $peer_cnx);
                                } else {
                                    $erroritem->message = get_string('errorbindingmnet', 'block_vmoodle', $peervmoodle->name);
                                    $erroritem->on = 'mnet';
                                    $errors[] = $erroritem;
                                }
                            }
                        }
                        // Register all peers in new vmoodle.
                        if (!empty($mnet_peer_envs)){
                            foreach($mnet_peer_envs as $peer_env){
                                echo "Registering {$peer_env->wwwroot} in peer<br/> ";
                                $peer_env = vmoodle_register_mnet_peer($form, $peer_env, $side_cnx);
                                // Register services for other peers in new peer.
                                vmoodle_add_services($form, $peer_env, $side_cnx, $services);
                            }
                        }
                        $done[] = 'mnet';
                        print_string('mnetbound', 'block_vmoodle');
                        echo "<br/>";
                    } else {
                        // Mnet required and master is not mnet.
                        echo get_string('mastermnetnotice', 'block_vmoodle');
                    }
                }
                vmoodle_close_connection($form, $side_cnx);
            }
            // Try to setup cron.
            if ($CFG->ostype != 'WINDOWS') {
                $crontab = escapeshellarg($form->crontab);
                $crontabsetup = "echo $crontab | crontab -";
                exec($crontabsetup);
            }
        }
    }
    if (empty($errors)) {
        if ($form->id) {
            $vmoodle->id = $form->id;
            $DB->update_record('block_vmoodle', $vmoodle);
        } else {
            $DB->insert_record('block_vmoodle', $vmoodle);
        }

    } else {
        // Errors when virtualizing.
        // Rollback.
        if (@array_key_exists('datapath', $done)) {
            filesystem_clear_dir($form->vdatapath, true, $CFG->block_vmoodle_vdatapathbase);
            print_string('datatpathunbound', 'block_vmoodle');
        }

        if (@array_key_exists('database', $done)) {
            vmoodle_drop_database($form);
            print_string('datatbasedroped', 'block_vmoodle');
        }

        if (@array_key_exists('mnet', $done)) {
            assert(true);
        }

        // Bounce to the form again.
        echo "bouncing";
        if ($form->id) {
            $action = 'edit';
        } else {
            $action = 'add';
        }
    }
    echo $OUTPUT->box_end();
}
// Make the add form ******************************************.
if ($action == 'add') {

    if (!empty($errors)) {
        $errorstr = '';
        foreach ($errors as $anError) {
            $errorstr .= $anError->message;
        }
        echo "<center>";
        print_simple_box($errorstr, 'center', '90%', '', 5, 'errorbox');
        echo "</center>";
    }

    echo $OUTPUT->heading(get_string('newvmoodle', 'block_vmoodle'));
    if ($CFG->block_vmoodle_automatedschema) {
        $form->vhostname = $CFG->block_vmoodle_vmoodlehost;
        $form->vdbtype = $CFG->block_vmoodle_vdbtype;
        $form->vdbhost = $CFG->block_vmoodle_vdbhost;
        $form->vdblogin = $CFG->block_vmoodle_vdblogin;
        $form->vdbpass = $CFG->block_vmoodle_vdbpass;
        $form->vdbprefix = $CFG->block_vmoodle_vdbprefix;
        $form->vdbpersist = $CFG->block_vmoodle_vdbpersist;
        $form->vdbname = $CFG->block_vmoodle_vdbbasename;
        $form->vdatapath = $CFG->block_vmoodle_vdatapathbase;
    }

    // Try to get crontab.
    if ($CFG->ostype != 'WINDOWS') {
        $crontabcmd = escapeshellcmd('crontab -l');
        $form->crontab = passthru($crontabcmd);
    }
    $usehtmleditor = can_use_html_editor();
    include "add.html";
    return -1;
}
// Make the edit form ********************************************.
if ($action == 'edit') {
    $id = required_param('id', PARAM_INT);

    if (!($form = $DB->get_record('block_vmoodle', array('id' => $id)))) {
        print_error('badvmoodleid');
        return (-1);
    }

    // Print errors.
    if (!empty($errors)) {
        $errorstr = '';
        foreach ($errors as $anError) {
            $errorstr .= $anError->message;
        }
        echo "<center>";
        print_simple_box($errorstr, 'center', '90%', '', 5, 'errorbox');
        echo "</center>";
    }

    echo $OUTPUT->heading(get_string('editvmoodle', 'block_vmoodle'));
    // Try to get crontab.
    if ($CFG->ostype != 'WINDOWS') {
        $crontabcmd = escapeshellcmd('crontab -l');
        $form->crontab = passthru($crontabcmd);
    }
    $usehtmleditor = can_use_html_editor();
    $form->editing = 1;
    include "add.html";
   return -1;
}

// Delete a vmoodle and uninstall it ******************************.
if ($action == 'delete') {
    $id = required_param('id', PARAM_INT);
    $vmoodle = $DB->get_record('block_vmoodle', array('id' => $id));

    if ($vmoodle) {
        // Drop record in vmoodle table.
        $DB->delete_records('block_vmoodle', array('id' => $id));
        // Destroy database. work silently.
        @vmoodle_drop_database($vmoodle);
        // Unlink datapath.
        filesystem_clear_dir($vmoodle->vdatapath, FS_FULL_DELETE, '');
        // Unbind mnet hosts.
        if ($vmoodle->mnet) {

            // Unregister from me (this).
            $thismoodle = vmoodle_make_this();
            $this_cnx = vmoodle_make_connection($thismoodle, true);
            if ($this_cnx) {
                vmoodle_unregister_mnet($vmoodle, $thismoodle);
                vmoodle_close_connection($vmoodle, $this_cnx);
            }
            // Unregister from all remaining peers (this).
            $mnetpeers = $DB->get_records('block_vmoodle', array('mnet' => 1));
            if (!empty($mnetpeers)) {
                foreach ($mnetpeers as $peervmoodle) {
                    $this_cnx = vmoodle_make_connection($peervmoodle, true);
                    if ($peer_cnx) {
                        vmoodle_unregister_mnet($vmoodle, $peervmoodle);
                        vmoodle_close_connection($vmoodle, $peer_cnx);
                    } else {
                    }
                }
            }
        }
    } else {
        error ("Bad VMoodle Id");
    }
}

