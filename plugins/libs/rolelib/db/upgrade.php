<?php
/**
 * Install and upgrade SyncRole library for the block Vmoodle.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */
function xmldb_vmoodle_lib_rolelib_upgrade($oldversion=0) {
	// Initializing
	$result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

    if ($result && $oldversion < 2010051100) { //New version in version.php
        
        $service = get_record('mnet_service', 'name', 'mnet_admin');
        if (!$service) return false;

		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_assign_role')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_assign_role';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_assign_role';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Remotely assign a role.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "assign_role".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $service->id;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "assign_role" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}

		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_create_user')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_create_user';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_create_user';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Remotely assign a role.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "create_user".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $service->id;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "create_user" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
	}

    if ($result && $oldversion < 2010072801) { //New version in version.php

        $service = get_record('mnet_service', 'name', 'mnet_admin');
        if (!$service) return false;

		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_remote_enrol')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_remote_enrol';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_remote_enrol';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Remotely enrols to a remote course.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "remote_enrol".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $service->id;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "remote_enrol" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
    }

    if ($result && $oldversion < 2010121800) { //New version in version.php

        $service = get_record('mnet_service', 'name', 'mnet_admin');
        if (!$service) return false;

		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_user_exists')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_user_exists';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_user_exists';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Checks for an existing user.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "user exists".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $service->id;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "user exists" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
    }

    if ($result && $oldversion < 2011090100) { //New version in version.php

        $service = get_record('mnet_service', 'name', 'mnet_admin');
        if (!$service) return false;

		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_has_role')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_has_role';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_has_role';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Checks for user role in some context.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "has role".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $service->id;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "has role" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
    }
	
	// Returning result
    return $result;
}