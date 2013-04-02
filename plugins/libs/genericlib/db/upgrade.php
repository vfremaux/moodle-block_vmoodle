<?php
/**
 * Install and upgrade Exemple library for the block Vmoodle.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@club-internet.fr)
 */
function xmldb_vmoodle_lib_genericlib_upgrade($oldversion=0) {	// The function name must match with library name.
	// Initializing
	$result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

    if ($result && $oldversion < 2011300301) { //New version in version.php
        
	    // installing Data Exchange
	    if (!$service = get_record('mnet_service', 'name', 'dataexchange')){
	        $service->name = 'dataexchange';
	        $service->description = get_string('dataexchange_name', 'vmoodle');
	        $service->apiversion = 1;
	        $service->offer = 1;
	        if (!$service->id = insert_record('mnet_service', $service)){
	            notify('Error installing dataexchange service.');
	            $result = false;
	        }
	    }

		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'dataexchange_rpc_fetch_config')) {
			
			// Creating RPC call
			$rpc->function_name = 'dataexchange_rpc_fetch_config';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/dataexchange_rpc_fetch_config';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Get a configuration key.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing dataexchange RPC call "fetch_config".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $service->id;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "fetch_config" to the "dataexchange" service.');
					$result = false;
				}
			}
		}
	}
	
	// Returning result
    return $result;
}