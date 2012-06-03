<?php
/**
 * This library provides command for synchronize role capabilites.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Install rolelib plugin library.
 * @return					boolean				TRUE if the installation is successfull, FALSE otherwise.
 */
function rolelib_install() {
	// Initialising
	$result = true;
	$rpc = new stdclass;
	$rpcmap = new stdclass;
	
	// Retrieve service
	if (!$service = get_record('mnet_service', 'name', 'mnet_admin')) {
		notify('The mnet_service is not installed.');
		return false;
	} else {
		$serviceid = $service->id;
		unset($service);
	}
	
	/*
	 * Installing RPC call 'get_role_capabilites'
	 */
	// Checking if it is already installed
	if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_get_role_capabilities')) {
		
		// Creating RPC call
		$rpc->function_name = 'mnetadmin_rpc_get_role_capabilities';
		$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_get_role_capabilities';
		$rpc->parent_type = 'block';  
		$rpc->parent = 'vmoodle';
		$rpc->enabled = 0; 
		$rpc->help = 'Get role capabilities.';
		$rpc->profile = '';
		
		// Adding RPC call
		if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
			notify('Error installing mnet_admin RPC call "get_role_capabilites".');
			$result = false;
		} else {
			// Mapping service and call
			$rpcmap->serviceid = $serviceid;
			$rpcmap->rpcid = $rpcid;
			if (!insert_record('mnet_service2rpc', $rpcmap)) {
				notify('Error mapping RPC call "get_role_capabilites to the "mnet_admin" service.');
				$result = false;
			}
		}
	}
	
	/*
	 * Installing RPC call 'set_role_capabilites'
	 */
	// Checking if it is already installed
	if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_set_role_capabilities')) {
		
		// Creating RPC call
		$rpc->function_name = 'mnetadmin_rpc_set_role_capabilities';
		$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_set_role_capabilities';
		$rpc->parent_type = 'block';  
		$rpc->parent = 'vmoodle';
		$rpc->enabled = 0; 
		$rpc->help = 'Set role capabilities.';
		$rpc->profile = '';
		
		// Adding RPC call
		if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
			notify('Error installing mnet_admin RPC call "set_role_capabilities".');
			$result = false;
		} else {
			// Mapping service and call
			$rpcmap->serviceid = $serviceid;
			$rpcmap->rpcid = $rpcid;
			if (!insert_record('mnet_service2rpc', $rpcmap)) {
				notify('Error mapping RPC call "set_role_capabilities" to the "mnet_admin" service.');
				$result = false;
			}
		}

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
				$rpcmap->serviceid = $serviceid;
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
				$rpcmap->serviceid = $serviceid;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "create_user" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
    }

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
			$rpcmap->serviceid = $serviceid;
			$rpcmap->rpcid = $rpcid;
			if (!insert_record('mnet_service2rpc', $rpcmap)) {
				notify('Error mapping RPC call "remote_enrol" to the "mnet_admin" service.');
				$result = false;
			}
		}
	}
	
	// Returning result
	return $result;
}

/**
 * Uninstall rolelib plugin library.
 * @return					boolean				TRUE if the uninstallation is successfull, FALSE otherwise.
 */
function rolelib_uninstall() {
	// Initializing
	$result = true;
	
	// Uninstalling RPC call 'get_role_capabilites'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_get_role_capabilities')) && 
	        delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			    delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_get_role_capabilities".');
		// $result = false; // let uninstall anyway
	}
	
	// Uninstalling RPC call 'set_role_capabilites'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_set_role_capabilities')) && 
	        delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			    delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_set_role_capabilities".');
		// $result = false; // let uninstall anyway
	}

	// Uninstalling RPC call 'assign_role'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_assign_role')) && 
	        delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			    delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_assign_role".');
		// $result = false; // let uninstall anyway
	}

	// Uninstalling RPC call 'create_user'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_create_user')) && 
	        delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			    delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_create_user".');
		// $result = false; // let uninstall anyway
	}

	// Uninstalling RPC call 'remote_enrol'
	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_remote_enrol')) && 
	        delete_records('mnet_rpc', 'id', $rpc_record->id) &&
			    delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling mnet_admin RPC call "mnetadmin_rpc_remote_enrol".');
		// $result = false; // let uninstall anyway
	}
	
	// Returning result
	return $result;
}
