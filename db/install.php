<?php

require_once $CFG->dirroot.'/blocks/vmoodle/upgradelib.php';

function xmldb_block_vmoodle_install_recovery(){
	xmldb_block_vmoodle_install();
}

function xmldb_block_vmoodle_install(){
	
	vmoodle_upgrade_subplugins_modules('print_upgrade_part_start', 'print_upgrade_part_end');
	
    set_config('block_vmoodle_late_install', 1);
}

function xmldb_block_vmoodle_late_install(){
    global $USER, $DB;

    //MDL-
    //we need to replace the word "block" with word "blocks"
    $rpcs = $DB->get_records('mnet_remote_rpc', array('pluginname' => 'vmoodle'));
    
    if(!empty($rpcs)){        
        foreach($rpcs as $rpc ){
            $rpc->xmlrpcpath = str_replace('block/', 'blocks/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_remote_rpc', $rpc);
        }        
    }

    //we need to replace the word "block" with word "blocks"
    $rpcs = $DB->get_records('mnet_rpc',array('pluginname' => 'vmoodle'));
    
    if(!empty($rpcs)){        
        foreach($rpcs as $rpc ){
            $rpc->xmlrpcpath = str_replace('block/', 'blocks/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_rpc',$rpc);
        }
    }

    //MDL-
    //we need to replace the word "vmoodleadminset/" with real subplugin path "blocks/vmoodle/plugins/"
    $rpcs = $DB->get_records('mnet_remote_rpc', array('plugintype' => 'vmoodleadminset'));
    
    if(!empty($rpcs)){        
        foreach($rpcs as $rpc ){
            $rpc->xmlrpcpath = str_replace('vmoodleadminset/', 'blocks/vmoodle/plugins/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_remote_rpc', $rpc);
        }        
    }

    //we need to replace the word "block" with word "blocks"
    $rpcs = $DB->get_records('mnet_rpc',array('plugintype' => 'vmoodleadminset'));
    
    if(!empty($rpcs)){        
        foreach($rpcs as $rpc ){
            $rpc->xmlrpcpath = str_replace('vmoodleadminset/', 'blocks/vmoodle/plugins/', $rpc->xmlrpcpath);
            $DB->update_record('mnet_rpc',$rpc);
        }
    }
}	 