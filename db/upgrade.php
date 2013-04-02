<?php  //$Id: upgrade.php,v 1.1.2.4 2011/02/01 10:20:31 vf Exp $

// This file keeps track of upgrades to 
// the vmoodle block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_vmoodle_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

    if ($result && $oldversion < 2010051400) { //New version in version.php
        
        if ($service = get_record('mnet_service', 'name', 'mnet_admin')){
    		/*
    		 * Installing RPC call 'keyswap'
    		 */
    		// Checking if it is already installed
    		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_keyswap')) {
    			
    			// Creating RPC call
    			$rpc->function_name = 'mnetadmin_keyswap';
    			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_keyswap';
    			$rpc->parent_type = 'block';  
    			$rpc->parent = 'vmoodle';
    			$rpc->enabled = 0; 
    			$rpc->help = 'Allow keyswap with key update forcing.';
    			$rpc->profile = '';
    			
    			// Adding RPC call
    			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
    				notify('Error installing mnet_admin RPC call "keyswap".');
    				$result = false;
    			} else {
    				// Mapping service and call
    				$rpcmap->serviceid = $service->id;
    				$rpcmap->rpcid = $rpcid;
    				if (!insert_record('mnet_service2rpc', $rpcmap)) {
    					notify('Error mapping RPC call "keyswap" to the "mnet_admin" service.');
    					$result = false;
    				}
    			}
    		}
    	} else {
    	    $result = false;
    	}
    }

    if ($result && $oldversion < 2010051600){
        // Adding mnet application type
        if (!get_record('mnet_application', 'name', 'vmoodle')){
            $application->name = 'vmoodle';
            $application->display_name = get_string('vmoodleappname', 'block_vmoodle');
            $application->xmlrpc_server_url = '/blocks/vmoodle/mnet/server.php';
            insert_record('mnet_application', $application);
        }
    }

	if ($result && $oldversion < 2011012200) {

    /// Define field lastcron to be added to block_vmoodle
        $table = new XMLDBTable('block_vmoodle');
        $field = new XMLDBField('lastcron');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'timecreated');

    /// Launch add field lastcron
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('lastcrongap');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'lastcron');

    /// Launch add field lastcrongap
        $result = $result && add_field($table, $field);

        $field = new XMLDBField('croncount');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'lastcrongap');

    /// Launch add field croncount
        $result = $result && add_field($table, $field);
    }

    return $result;
}

?>
