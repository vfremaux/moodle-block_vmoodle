<?php

function xmldb_block_vmoodle_uninstall(){
    global $DB,$CFG;

	vmoodle_uninstall_plugins();

	// dismount all XML-RPC
	if ($service = $DB->get_record('mnet_service', array('name' => 'mnetadmin'))){
	    $DB->delete_records('mnet_service', array('id' => $service->id));
	    $DB->delete_records('mnet_rpc', array('plugintype' => 'vmoodleadminset'));
	    $DB->delete_records('mnet_remote_rpc', array('plugintype' => 'vmoodleadminset'));
	    $DB->delete_records('mnet_rpc', array('pluginname' => 'vmoodle'));
	    $DB->delete_records('mnet_remote_rpc', array('pluginname' => 'vmoodle'));
	    $DB->delete_records('mnet_service2rpc', array('serviceid' => $service->id));
	    $DB->delete_records('mnet_remote_service2rpc', array('serviceid' => $service->id));
	    $DB->delete_records('mnet_host2service', array('serviceid' => $service->id));
	}

    set_config('block_vmoodle_late_install', null);
	
	return true;
}
