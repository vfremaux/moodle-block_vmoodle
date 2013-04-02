<?php
/*
 * Created on 22 sept. 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';
include_once $CFG->libdir."/pear/HTML/AJAX/JSON.php";

function vmoodle_get_remote_config($mnethost, $configkey, $domain = ''){
	global $CFG, $USER;

	// we should NOT alter the $USER global as this causes anonymous login fake effect.
	$user = clone($USER);
	
	if (!isset($user->username)){
		$user = get_guest();
		$resetnulluser = true;
	}	
	$user->username = $user->username;
	$userhost = get_record('mnet_host', 'id', $user->mnethostid);
	$user->remoteuserhostroot = $userhost->wwwroot;
	$user->remotehostroot = $CFG->wwwroot;
	
    // get the sessions for each vmoodle that have same ID Number
    $rpcclient = new mnet_xmlrpc_client();
    $rpcclient->set_method('blocks/vmoodle/rpclib.php/dataexchange_rpc_fetch_config');
    $rpcclient->add_param($user, 'struct');
    $rpcclient->add_param($configkey, 'string');
    $rpcclient->add_param($domain, 'string');
    
    $mnet_host = new mnet_peer();
    $mnet_host->set_wwwroot($mnethost->wwwroot);
    if ($rpcclient->send($mnet_host)){
        $response = json_decode($rpcclient->response);
        if ($response->status == 200){
        	return $response->value;
        } else {
        	if (debugging()){
        		notify('Remote RPC error '.implode('<br/>', $response->errors));
        	}
        }
    } else {
    	if (debugging()){
    		notify('Remote RPC failure '.implode('<br/', $rpcclient->error));
    	}
    }		
}

/**
 * Install update plugin library.
 * @return					boolean				TRUE if the installation is successfull, FALSE otherwise.
 */
function genericlib_install() {
	// No install operation

	$result = true;
	
    // installing Data Exchange
    if (!$service = get_record('mnet_service', 'name', 'dataexchange')){
        $service->name = 'dataexchange';
        $service->description = get_string('dataexchange_name', 'block_vmoodle');
        $service->apiversion = 1;
        $service->offer = 1;
        if (!$service->id = insert_record('mnet_service', $service)){
            notify('Error installing prf_dataexchange service.');
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
	
	if ($previous = get_config('dataexchangesafekeys')){
		$genericconfigs[] = $previous;
	}
	$genericconfigs[] = 'globaladminmessage';
	$genericconfigs[] = 'globaladminmessagecolor';
	set_config('dataexchangesafekeys', implode(',', $genericconfigs));

	return $result;
}

/**
 * Uninstall rolelib plugin library.
 * @return					boolean				TRUE if the uninstallation is successfull, FALSE otherwise.
 */
function genericlib_uninstall() {

	if (!(($rpc_record = get_record('mnet_rpc', 'function_name', 'dataexchange_rpc_fetch_config')) && 
        delete_records('mnet_rpc', 'id', $rpc_record->id) &&
	    delete_records('mnet_service2rpc', 'rpcid', $rpc_record->id))) {
		notify('Error uninstalling dataexchange RPC call "dataexchange_rpc_fetch_config".');
		// $result = false; // let uninstall anyway
	}

	if($service = get_record('mnet_service', 'name', 'dataexchange')){
	    delete_records('mnet_host2service', 'serviceid', $service->id); // delete all host mapping
	    delete_records('mnet_service', 'id', $service->id); // delete service
	}

	return true;
}