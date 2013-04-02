<?php
/**
 * Declare RPC functions for syncrolelib.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once $CFG->dirroot.'/blocks/vmoodle/classes/XmlRpc_Client.class.php';
require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';
require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}

/**
 * Get role capabilities of a virtual platform.
 * @param	$user					array or object		The calling user.
 * @param	$role					string				The role to read capabilities.
 * @param	$capabilitues			mixed				The capabilities to read (optional / may be string or array).
 */
function mnetadmin_rpc_get_role_capabilities($user, $role, $capabilities = null, $json_response = true) {
	global $CFG, $USER,$DB;

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$user, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
		    return json_decode($auth_response);
		}
	}
	$response->errors = array();
	$response->error = '';
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	// Getting role
	$record_role = $DB->get_record('role', array('shortname' => $role));
	if (!$record_role) {
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Unable to retrieve role on source host.';
		$response->error = 'Unable to retrieve role on source host.';
		if ($json_response){
			return json_encode($response);
		} else {
			return $response;
		}
	}
	// Creating SQL filter in WHERE clause
	if ($capabilities) {
		// Formatting capabilities
		if (!is_array($capabilities))
			$capabilities = array($capabilities);
		$in_capabilities = $capabilities;
		foreach($in_capabilities as &$in_capability)
               $in_capability = $in_capability;
        $in_capabilities = join(',', $in_capabilities);
	}
	// Getting capabilities
   
    
    $where = "";
    $params=array();
    if(!is_null($capabilities))
    {
    $where = 'where name IN (:cap)';
    $params = array('cap'=>$in_capabilities);
    
    }
   
	$records_capabilities = $DB->get_records_sql('select name,contextlevel from {capabilities} '.$where,$params);
	if (!$records_capabilities) {
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Unable to retrieve capabilities.';
		$response->error = 'Unable to retrieve capabilities.';
		if ($json_response){
			return json_encode($response);
		} else {
			return $response;
		}
	}
	// Getting role capabilities
	$records_role_capabilities = $DB->get_records_sql('select capability,contextid,permission from {role_capabilities} where roleid = '.$record_role->id.' AND contextid = 1'.($capabilities ? ' AND capability IN (?)' : ''),array($in_capabilities));
	@ob_clean(); 
	ob_start();	// Used to prevent HTML output from dmllib methods and capture errors
	if (!$records_role_capabilities) {
		$sql_error = parse_wlerror();
		// Checking if there was a sql error
		if (empty($sql_error)){
			// Defining empty record set
			$records_role_capabilities = array();
		} else {
			// Returning error
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = 'Unable to retrieve role capabilites: '.$sql_error;
			$response->error = 'Unable to retrieve role capabilites: '.$sql_error;
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}
	}
	ob_end_clean();
	// Creating result
	$result = array();
	foreach($records_capabilities as $capability_name => $record_capability) {
		// Checking if role_capability is set
		if (!array_key_exists($capability_name, $records_role_capabilities))
			$result[$capability_name] = null;
		else {
			// Getting role capatiliy
			$role_capability = $records_role_capabilities[$capability_name];
			// Adding capability contextlevel
			$role_capability->contextlevel = $record_capability->contextlevel;
			$result[$capability_name] = $role_capability;
		}
	}
	// Setting value
	$response->value = $result;
	// Returning response
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

/**
 * Set role capabilities of a virtual platform.
 * @param	$user					string				The calling user.
 * @param	$role					string				The role to set capabilities.
 * @param	$role_capabilities		mixed				The role capabilities (array or object due to xmlrpc failures).
 * @param	$clear					bool				True if the role capabilities should be cleared before, false otherwise.
 */
function mnetadmin_rpc_set_role_capabilities($user, $role, $role_capabilities, $clear = false, $json_response = true) {
	global $CFG, $USER,$DB;

	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	$response->errors = array();
	$response->error = '';

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$user, 'block/vmoodle:execute')){
		if ($json_response){ // we could not have a credential
		    return $auth_response;
		} else {
			return json_decode($auth_response);
		}
	}
	// Getting role
	$record_role = $DB->get_record('role', array('shortname' => $role));
	if (!$record_role) {
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Unable to retrieve role.';
		$response->error = 'Unable to retrieve role.';
		if ($json_response){
			return json_encode($response);
		} else {
			return $response;
		}
	}
	// Formatting role capabilities
	foreach($role_capabilities as $role_capability_name => $role_capability)
		$role_capabilities[$role_capability_name] = $role_capability ? (object) $role_capability : null;
	// Getting capabilities
	$records_capabilities = $DB->get_records('capabilities', null, '', 'name,id,captype,contextlevel,component,riskbitmask');
	if (!$records_capabilities) {
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Unable to retrieve capabilities.';
		$response->error = 'Unable to retrieve capabilities.';
		if ($json_response){
			return json_encode($response);
		} else {
			return $response;
		}
	}
	if ($clear) {
		// Removing current role capabilities
		$DB->delete_records('role_capabilities', array('roleid' => $record_role->id));
	} else {
		// Getting current role capabilities
		$records_current_role_capabilities = $DB->get_records('role_capabilities', array('roleid' => $record_role->id));
		$current_role_capabilities = array();
		// Removing other role capabilities
		foreach($records_current_role_capabilities as $id => $record_current_role_capability) {
			foreach($role_capabilities as $role_capability_name => $role_capability) {
				if ($record_current_role_capability->capability == $role_capability_name) {
					$current_role_capabilities[$record_current_role_capability->capability] = $record_current_role_capability;
					break;
				}
			}
		}
	}
	// Setting role capabilities
	@ob_clean(); ob_start();	// Used to prevent HTML output from dmllib methods and capture errors
	foreach($role_capabilities as $role_capability_name => $role_capability) {
		// Checking if capability exists
		if (!array_key_exists($role_capability_name, $records_capabilities)) {
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = 'Capability "'.$role_capability_name.'" does not exist.';
			$response->error = 'Capability "'.$role_capability_name.'" does not exist.';
			continue;
		}
		// Checking if role capability should be removed
		if (is_null($role_capability)) {
			@ob_clean();
			if (!$DB->delete_records('role_capabilities', array('roleid' => $record_role->id, 'capability' => $role_capability_name))) {
				$response->status = RPC_FAILURE_RECORD;
				$sql_error = parse_wlerror();
				$response->errors[] = 'Unable to remove role capability "'.$role_capability_name.'"'.(empty($sql_error) ? '.' : ': '.$sql_error.'.');
				$response->error = 'Unable to remove role capability "'.$role_capability_name.'"'.(empty($sql_error) ? '.' : ': '.$sql_error.'.');
			}
			continue;
		}
		// Checking capability contextlevel
		$capability = $records_capabilities[$role_capability_name];
		if ($capability->contextlevel != $role_capability->contextlevel) {
			@ob_clean();
			$capability->contextlevel = $role_capability->contextlevel;
			if (!$DB->update_record('capabilities', $capability)) {
				$response->status = RPC_FAILURE_RECORD;
				$sql_error = parse_wlerror();
				$response->errors[] = 'Unable to fix contextlevel of capability "'.$capability->name.'"'.(empty($sql_error) ? '.' : ': '.$sql_error);
				$response->error = 'Unable to fix contextlevel of capability "'.$capability->name.'"'.(empty($sql_error) ? '.' : ': '.$sql_error);
				continue;
			}
		}
		// Checking if role capability should be created
		if ($clear || !array_key_exists($role_capability->capability, $current_role_capabilities)) {
			// Creating record
			$record = $role_capability;
			$record->roleid = $record_role->id;
			$record->timemodified = time();
			$record->modifierid = $USER->id;
			unset($record->contextlevel);
			// Inserting role capability
			@ob_clean();
			if (!$DB->insert_record('role_capabilities', $record)) {
				$response->status = RPC_FAILURE_RECORD;
				$sql_error = parse_wlerror();
				$response->errors[] = 'Unable to insert role capability "'.$record->capability.'"'.(empty($sql_error) ? '.' : ': '.$sql_error);
				$response->error = 'Unable to insert role capability "'.$record->capability.'"'.(empty($sql_error) ? '.' : ': '.$sql_error);
			}
		}
		// Checking if role capability should be updated
		else if (!$clear && array_key_exists($role_capability_name, $current_role_capabilities) &&
		$current_role_capabilities[$role_capability_name]->permission != $role_capability->permission) {	
			// Modifying record
			$record = $current_role_capabilities[$role_capability->capability];
			$record->permission = $role_capability->permission;
			// Updating record
			@ob_clean();
			if (!$DB->update_record('role_capabilities', $record)) {
				$response->status = RPC_FAILURE_RECORD;
				$sql_error = parse_wlerror();
				$response->errors[] = 'Unable to update role capability "'.$record->capability.'"'.(empty($sql_error) ? '.' : ': '.$sql_error);
				$response->error = 'Unable to update role capability "'.$record->capability.'"'.(empty($sql_error) ? '.' : ': '.$sql_error);
			}
		}
	}
	// Returning response
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

/**
 * Get role allowances of a virtual platform.
 * As being a cross platform match, do only rely on shortnames and never on ids
 * @param	$user					array or object		The calling user.
 * @param	$table					string				'assign' or 'override'.
 * @param	$rolename					string				The role shortname to get allowance from.
 */
function mnetadmin_rpc_get_role_allow_table($user, $table, $rolename = '', $json_response = true) {
	global $CFG, $USER, $DB;

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$user, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
		    return json_decode($auth_response);
		}
	}
	$response->errors = array();
	$response->error = '';
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	// Getting allowance records
	if ($rolename){
		if (!$role = $DB->get_record('role', array('shortname' => $rolename))){
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = "Unknown role $rolename in remote.";
			$response->error = "Unknown role $rolename in remote.";
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}
		$allows = $DB->get_records('role_allow_'.$table, array('roleid', $role->id));
	} else {
		$allows = $DB->get_records('role_allow_'.$table, array());
	}
	$result = array();
	if ($allows){
		foreach($allows as $a){
			$rolename = $DB->get_field('role', 'shortname', array('id' => $a->roleid));
			$key = 'allow'.$table;
			$targetname = $DB->get_field('role', 'shortname', array('id' => $a->$key));
			$result[$rolename][] = $targetname;
		}
	}

	// Setting value
	$response->value = $result;
	// Returning response
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

/**
 * Get role allowances of a virtual platform.
 * @param	$user					array or object		The calling user.
 * @param	$table					string				'assign' or 'override'.
 * @param	$rolename				string				The role shortname to get allowance from.
 * @param	$targetrolenames		string				comma separated lists of role shortnames.
 */
function mnetadmin_rpc_set_role_allow($user, $table, $rolename, $targetrolenames, $json_response = true) {
	global $CFG, $USER, $DB;

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$user, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
		    return json_decode($auth_response);
		}
	}
	$response->errors = array();
	$response->error = '';
	// Creating response
	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	// Getting allowance records
	if ($rolename){
		if($role = $DB->get_record('role', array('shortname' => $rolename))){
			$DB->delete_records('role_allow_'.$table, array('roleid' => $role->id));
			$targets = explode(',', $targetrolenames);
			foreach($targets as $targetname){
				if ($targetrole = $DB->get_record('role', array('shortname' => $targetname))){
					$key = 'allow'.$table;
					$roleallow = new StdClass;
					$roleallow->roleid = $role->id;
					$roleallow->$key = $targetrole->id;
					$DB->insert_record('role_allow_'.$table, $roleallow);
				} else {
					$response->errors[] = "Bad target role shortname $targetname.";
					$response->error = "Some target role shortname error.";
				}
			}
		} else {
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = "Bad source role shortname $rolename.";
			$response->error = "Bad source role shortname $rolename.";
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}
	} else {
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Bad role id.';
		$response->error = 'Unable to retrieve assign allowance table on source host.';
		if ($json_response){
			return json_encode($response);
		} else {
			return $response;
		}
	}

	// Setting value
	$response->value = '';
	// Returning response
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

/**
 * asks for a role assignation on a context.
 * @param	$callinguser			object				The calling user.
 * @param	$targetuser			    string				The username of the user to assign a role remotely.
 * @param	$rolename		        mixed				The role shortname to assign
 * @param	$contextidentityfield	string				Tells the field to use to get context real object instance
 * @param	$contextlevel		    integer				The contextlevel concerned, defaults to SYSTEM 
 * @param	$contextidentity		string				Some identifying value allowing to remotely point the context instance
 *
 * Identifying context from remote aplications : 
 * CONTEXT_SYSTEM : unused
 * CONTEXT_COURSECAT : not implemented
 * CONTEXT_COURSE : the course shortname is used
 * CONTEXT_MODULE : the coursemodule IDnumber is used
 * CONTEXT_USER : the username is used
 */
function mnetadmin_rpc_has_role($callinguser, $targetuser, $userhostroot, $rolename, $contextidentityfield = '', $contextlevel = CONTEXT_SYSTEM, $contextidentity = '', $whereroot = '', $json_response = true) {
	global $CFG, $USER;

	debug_trace("mnetadmin_rpc_has_role($callinguser, $targetuser, $userhostroot, $rolename, $contextidentityfield, $contextlevel, $contextidentity, $whereroot, $json_response )");

	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	$response->errors = array();
	$response->error = '';

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$callinguser, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
			return json_decode($auth_response);
		}
	}
	if (empty($whereroot) || $whereroot == $CFG->wwwroot){
	// check it locally
		debug_trace("check locally for $targetuser as $rolename in context $contextidentity of level $contextlevel keyed by $contextidentityfield in ".$whereroot);

		// Getting role
		$record_role = $DB->get_record('role', array('shortname' => $rolename));
		if (!$record_role) {
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = 'Unkown role.';
			$response->error = 'Unkown role.';
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}

		$context = rpc_check_context_target($contextlevel, $contextidentityfield, $contextidentity, $response);
		if ($response->status != RPC_SUCCESS){
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}
		if ($userhostroot == '') $userhostroot = $CFG->wwwroot;
		if (!$usermnet = $DB->get_record('mnet_host', array('wwwroot' => $userhostroot))){
			$response->status = RPC_FAILURE;
			$response->errors[] = "Unknown user host reference";
			$response->error = "Unknown user host reference";
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}

		if (!$user = $DB->get_record('user', array('username' => $targetuser, 'mnethostid' => $usermnet->id))){
			$response->status = RPC_FAILURE_USER;
			$response->errors[] = "Unknown user";
			$response->error = "Unknown user";
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}

		debug_trace("Checking RA for 'userid', $user->id, 'roleid', $record_role->id, 'contextid', $context->id ");
		if (!$DB->record_exists('role_assignments', array('userid' => $user->id, 'roleid' => $record_role->id, 'contextid' => $context->id))){
			$response->status = RPC_FAILURE;
			$response->errors[] = "Has no role here";
			$response->error = "Has no role here";
			if ($json_response){
				return json_encode($response);
			} else {
				return $response;
			}
		}
		$response->message = "Has role";
	} else {
		// Make remote call
		// debug_trace('check remotely at '.$whereroot);
	    $userhostroot = $DB->get_field_select('mnet_host', 'wwwroot', " id = {$USER->mnethostid} AND deleted = 0 "); 
	    if (!$userhostroot){
            $extresponse->error = 'Unkown userroot (or deleted).';        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
	    }

		if ($remotedeleted = $DB->get_field('mnet_host', 'deleted', array('wwwroot' => $whereroot))){
            $extresponse->error = 'Unkown whereroot.';        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
		}
        $rpcclient = new mnet_xmlrpc_client();
		$rpcclient->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_has_role');
		$caller->username = $USER->username;
		$caller->remoteuserhostroot = $userhostroot;
		$caller->remotehostroot = $CFG->wwwroot;
		$rpcclient->add_param($caller, 'struct'); // caller user
		$rpcclient->add_param($targetuser, 'string');
		$rpcclient->add_param($userhostroot, 'string');
	    $rpcclient->add_param($rolename, 'string');
	    $rpcclient->add_param($contextidentityfield, 'string');
	    $rpcclient->add_param($contextlevel, 'string');
	    $rpcclient->add_param($contextidentity, 'string');
        $mnet_host = new mnet_peer();
        $mnet_host->set_wwwroot($whereroot);
        if (!$rpcclient->send($mnet_host)){
            $extresponse->status = RPC_FAILURE;
            $extresponse->errors[] = 'REMOTE : '.implode("<br/>\n", $rpcclient->errors);        
            $extresponse->errors[] = json_encode($rpcclient);
            $extresponse->error = 'REMOTE : '.implode("<br/>\n", $rpcclient->errors);        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        }
        $response = json_decode($rpcclient->response);
        if ($response->status != RPC_SUCCESS){
        	$extresponse->status = $response->status;
		    $extresponse->errors[] = 'Remote application error : ';
		    $extresponse->errors[] = $response->errors;
		    $extresponse->error = 'Remote application error : '. implode("\n", $response->errors);
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        }
	}

	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

function mnetadmin_rpc_has_role_wrapped($wrap) {
	debug_trace("WRAP mnetadmin_rpc_has_role : ".json_encode($wrap));	
	return mnetadmin_rpc_has_role(@$wrap['callinguser'], @$wrap['targetuser'], @$wrap['userhostroot'], @$wrap['rolename'], @$wrap['contextidentityfield'], @$wrap['contextlevel'], @$wrap['contextidentity'], @$wrap['whereroot'], @$wrap['json_response']);
}

/**
 * assign remotely a role based on role shortname and user username.
 * @param	$callinguser			object				The calling user.
 * @param	$targetuser			    string				The username of the user to assign a role remotely.
 * @param	$rolename		        mixed				The role shortname to assign
 * @param	$contextidentityfield	string				Tells the field to use to get context real object instance
 * @param	$contextlevel		    integer				The contextlevel concerned, defaults to SYSTEM 
 * @param	$contextidentity		string				Some identifying value allowing to remotely point the context instance
 *
 * Identifying context from remote aplications : 
 * CONTEXT_SYSTEM : unused
 * CONTEXT_COURSECAT : not implemented
 * CONTEXT_COURSE : the course shortname is used
 * CONTEXT_MODULE : the coursemodule IDnumber is used
 * CONTEXT_USER : the username is used
 */
function mnetadmin_rpc_assign_role($callinguser, $targetuser, $rolename, $contextidentityfield = '', $contextlevel = CONTEXT_SYSTEM, $contextidentity = '', $starttime = 0, $endtime = 0, $json_response = true) {
	global $CFG, $USER;

	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	$response->errors = array();
	$response->error = '';

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$callinguser, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
			return json_decode($auth_response);
		}
	}
	// Getting role
	$unassign = (strstr($rolename, '-') !== false);
	$rolename = str_replace('-', '', $rolename);

	$record_role = $DB->get_record('role', array('shortname' => $rolename));
	if (!$record_role) {
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Unkown role '.$rolename.'.';
		$response->error = 'Unkown role '.$rolename.'.';
		if ($json_response){
			return json_encode($response);
		} else {
			return $response;
		}
	}
	// Check context target
	switch($contextlevel){
	    case CONTEXT_SYSTEM :{
	        $context = context_system::instance();
	        break;
	    }
	    case CONTEXT_COURSE :{
	    	if (!preg_match('/id|shortname|idnumber/', $contextidentityfield)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'This fieldname does\'nt apply for this context level.';
        		$response->error = 'This fieldname does\'nt apply for this context level.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	    	}
	        if(!$course = $DB->get_record('course', array($contextidentityfield => $contextidentity))){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'Course Context not found.';
        		$response->error = 'Course Context not found.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        }
	        $context = context_course::instance($course->id);
	        break;
	    }
	    case CONTEXT_MODULE :{
	    	if (!preg_match('/id|idnumber/', $contextidentityfield)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'This fieldname does\'nt apply for this context level.';
        		$response->error = 'This fieldname does\'nt apply for this context level.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	    	}
	        if(!$cm = $DB->get_record('course_modules', array($contextidentityfield => $contextidentity))){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'Course Module not found.';
        		$response->error = 'Course Module not found.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        }
	        if (!$context = context_module::instance($cm->id)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'Course Module context not found.';
        		$response->error = 'Course Module context not found.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        }
	        break;
	    }
	    case CONTEXT_USER :{
	    	if (!preg_match('/id|username|email|idnumber', $contextidentityfield)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'This fieldname does\'nt apply for this context level.';
        		$response->error = 'This fieldname does\'nt apply for this context level.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	    	}
	        if(!$user = $DB->get_record('user', array($contextidentityfield => $contextidentity))){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'User not found.';
        		$response->error = 'User not found.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        }
	        if (!$context = context_user::instance($cm)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'User context not found.';
        		$response->error = 'User context not found.';
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        }
	        break;
	    }
	    default:{
    		$response->status = RPC_FAILURE_RECORD;
    		$response->errors[] = 'Context not implemented.';
    		$response->error = 'Context not implemented.';
    		if ($json_response){
        		return json_encode($response);
        	} else {
        		return $response;
        	}
	    }
	}
	debug_trace("Got context $contextlevel");

    if (!$targetuser = $DB->get_record('user', array('username' => $targetuser))){
		$response->status = RPC_FAILURE_RECORD;
		$response->errors[] = 'Not such target user.';
		$response->error = 'Not such target user.';
		if ($json_response){
    		return json_encode($response);
    	} else {
    		return $response;
    	}
    }

	if ($unassign){
		debug_trace("role_unassign($record_role->id, $targetuser->id, null, $context->id)");
		if (role_unassign($record_role->id, $targetuser->id, null, $context->id)){
			$response->status = RPC_SUCCESS;
			$response->message = "Role $record_role->name unassigned from ". fullname($targetuser);
			debug_trace("Role $record_role->name unassigned for ". fullname($targetuser));
		} else {
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = "Could not unassign $targetuser->username on context $context->id for role $rolename";
			$response->error = "Could not unassign $targetuser->username on context $context->id for role $rolename";
			debug_trace("Could not unassign role $rolename to $targetuser->username on context $context->id");
		}
	} else {
		debug_trace("role_assign($record_role->id, $targetuser->id, null, $context->id, $starttime, $endtime)");
		if ($starttime && $endtime && ($starttime > $endtime)){
			$response->status = RPC_FAILURE_RECORD;
			$response->errors[] = "Cannot assign when starttime is above endtime";
			$response->error = "Cannot assign when starttime is above endtime";
			debug_trace("Bad times for role assign");
		} else {		
			if (role_assign($record_role->id, $targetuser->id, null, $context->id, $starttime, $endtime)){
				$response->status = RPC_SUCCESS;
				$response->message = "Role $record_role->name assigned to ". fullname($targetuser);
				debug_trace("Role $record_role->name assigned to ". fullname($targetuser));
			} else {
				$response->status = RPC_FAILURE_RECORD;
				$response->errors[] = "Could not assign role $rolename to $targetuser->username on context $context->id";
				$response->error = "Could not assign role $rolename to $targetuser->username on context $context->id";
				debug_trace("Could not assign role $rolename to $targetuser->username on context $context->id");
			}
		}
	}
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

function mnetadmin_rpc_assign_role_wrapped($wrap) {
	debug_trace("WRAP mnetadmin_rpc_assign_role : ".json_encode($wrap));	
	return mnetadmin_rpc_assign_role(@$wrap['callinguser'], @$wrap['targetuser'], @$wrap['rolename'], @$wrap['contextidentityfield'], @$wrap['contextlevel'], @$wrap['contextidentity'], @$wrap['starttime'], @$wrap['endtime'], @$wrap['json_response']);
}

/**
 * allows checking if a user exists.
 * @param	$callinguser			object				The calling user.
 * @param	$targetuser			    string				The username of the user to be created.
 * @param	$userhostname			string				the user's supposed origin .
 *
 * if userhostname is empty, the user is checked locally and his known userhost is mentionned.
 *
 */
function mnetadmin_rpc_user_exists($callinguser, $targetuser, $whereroot = '', $json_response = true) {
	global $CFG, $USER,$DB;

	debug_trace("$CFG->wwwroot : mnetadmin_rpc_user_exists entry");

	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	$response->errors = array();
	$response->error = '';

	if ($auth_response = invoke_local_user((array)$callinguser, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
			return json_decode($auth_response);
		}
	}

	// local search
	debug_trace("$CFG->wwwroot : asked for $whereroot");
	if (empty($whereroot) || $whereroot == $CFG->wwwroot){
		debug_trace("mnetadmin_rpc_user_exists : local resolution");
		if(!$response->user = $DB->get_record('user', array('username' => $targetuser), 'id,username,firstname,lastname,email,firstaccess,lastaccess,auth,email,emailstop,confirmed,deleted,mnethostid')){
			debug_trace("User exists : $targetuser did not matched locally.");
			$response->location = 'local';
			$response->errors[] = "Unknown user.";
			$response->error = "Unknown user.";
			if ($json_response){
			    return json_encode($response);
			} else {
				return $response;
			}			
		}
		debug_trace("User exists : $targetuser matched locally.");
		$userhostid = $response->user->mnethostid;
		$response->user->userknownhost = $DB->get_field_select('mnet_host', 'wwwroot', " id = {$userhostid} AND deleted = 0 ");			
	} else {
		debug_trace("mnetadmin_rpc_user_exists : remote resolution in $whereroot");
		// Make remote call
	    $userhostroot = $DB->get_field_select('mnet_host', 'wwwroot', " id = $USER->mnethostid AND deleted = 0 "); 

	    if (!$userhostroot){
			$extresponse->status = RPC_FAILURE_DATA;
			$extresponse->location = 'remote';
            $extresponse->error = 'Unknown userroot (or deleted).';        
            $extresponse->errors[] = 'Unknown userroot (or deleted).';        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
	    }

	    if (!$DB->record_exists('mnet_host', array('wwwroot' => $whereroot, 'deleted' => 0))){
			$extresponse->status = RPC_FAILURE_DATA;
			$extresponse->location = 'remote';
            $extresponse->error = "Unknown host $whereroot (or deleted).";        
            $extresponse->errors[] = "Unknown host $whereroot (or deleted).";        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
	    }
        $rpcclient = new mnet_xmlrpc_client();
		$rpcclient->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_user_exists');
		$caller->username = $USER->username;
		$caller->remoteuserhostroot = $userhostroot;
		$caller->remotehostroot = $CFG->wwwroot;
		$rpcclient->add_param($caller, 'struct'); // caller user
		$rpcclient->add_param($targetuser, 'string');
		$rpcclient->add_param($whereroot, 'string');
        $mnet_host = new mnet_peer();
        $mnet_host->set_wwwroot($whereroot);
        if (!$response = $rpcclient->send($mnet_host)){
            $extresponse->status = RPC_FAILURE;
            $extresponse->errors[] = "REMOTE RPC ERRORS \n";
            $extresponse->error = 'Remote rpc error.';        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        }
        $response = json_decode($rpcclient->response);
        if ($response->status != RPC_SUCCESS){
        	$extresponse->status = $response->status;
		    $extresponse->errors[] = 'Remote application error : ';
		    $extresponse->errors[] = $response->errors;
		    $extresponse->error = 'Remote application error : '. implode("\n", $response->errors);
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        }
	}
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

function mnetadmin_rpc_user_exists_wrapped($wrap) {
	// debug_trace("WRAP mnetadmin_rpc_user_exists : ".json_encode($wrap));	
	return mnetadmin_rpc_user_exists(@$wrap['callinguser'], @$wrap['targetuser'], @$wrap['whereroot'], @$wrap['json_response']);
}

/**
 * force user account creation.
 * @param	$callinguser			object				The calling user.
 * @param	$targetuser			    string				The username of the user to be created.
 * @param	$userparams			    array				an array containing all data for user.
 * @param	$userhostname			string				the user's origin account.
 * @param	$bounceto			    array				an array of or a string containing hostnames to propagate users to.
 * @param	$onlybounce			    boolean				if true, do not try to create the user locally, just bounce.
 *
 * if userhostname is empty, the user is created with an account bound to the localhost mnethost id (local account) and
 * reset to manual auth if the auth is 'mnet' (note the auth will remain unchanged if other than mnet, so it is possible to preset
 * an SAML or LDAP bound account.
 * If userhostname is not empty, the call forces auth being mnet, whatever the auth field was set to, and the hostname is searched
 * for a local matching host in mnet_hosts.
 *
 * If bounceto is not empty, the account will be propagated to matching mnet_hosts in the MNET proximity.
 * The onlybounce feature is provided for using this rpc function using a local direct call to propagate a user programatically
 * a user to some bounce locations
 */
function mnetadmin_rpc_create_user($callinguser, $targetuser, $userparams, $userhostname = '', $bounceto = null, $onlybounce = false, $json_response = true, $overridecapability = false) {
	global $CFG, $USER;

	$response = new stdclass;
	$response->status = RPC_SUCCESS;
	$response->errors = array();
	$response->error = '';

    $userparamsarr = (array)$userparams;

	$capability = '';
	if (!$overridecapability){
		$capability = 'block/vmoodle:execute';
	}

	if ($auth_response = invoke_local_user((array)$callinguser, $capability)){
		if ($json_response){
		    return $auth_response;
		} else {
			return json_decode($auth_response);
		}
	}

    if (!$onlybounce){
		debug_trace("Up to create $targetuser ");	
        if (!$user = $DB->get_record('user', array('username' => $targetuser))){
            // collect eventual profilefields and cleanup user record from them
            foreach($userparamsarr as $key => $value){
                if (preg_match('/^profile_field_/', $key)){
                    $profilefields[$key] = $value;
                    unset($userparams[$key]);
                }
            }
            $newuser = addslashes_recursive((object)$userparams);
            $newuser->username = $targetuser;
            // remap local mnethostid and auth method if needed
            if (!empty($userhostname)){
                if (!$originuserhost = $DB->get_record('mnet_host', array('wwwroot' => $userhostname))){
                	$response->status = RPC_FAILURE_RECORD;
                	$response->errors[] = "Bad origin host ".json_encode($userhostname).", or origin host of the user is not known by this host.";
                	$response->error = "Bad origin host ".json_encode($userhostname).", or origin host of the user is not known by this host.";
	        		if ($json_response){
		        		return json_encode($response);
		        	} else {
		        		return $response;
		        	}
                } else {
                    $newuser->mnethostid = $originuserhost->id;
               		if (($originuserhost->id != $CFG->mnet_localhost_id) && (empty($newuser->auth) || ($newuser->auth == 'manual'))){
	                    $newuser->auth = 'mnet';
	                } else {
	                	if (empty($newuser->auth) || $newuser->auth == 'mnet'){
		                    $newuser->auth = 'manual';
		                }
	                }
                }
            } else {
                $newuser->mnethostid = $CFG->mnet_localhost_id;
                if (empty($newuser->auth) || $newuser->auth == 'mnet'){
                    $newuser->auth = 'manual';
                }
            }
            $newuser->confirmed = 1;
            $newuser->timemodified = time();
			debug_trace("REMOTE CALL : recording user");	
            if (!$userid = $DB->insert_record('user', $newuser)){
            	$response->status = RPC_FAILURE_RECORD;
            	$response->errors[] = "Could not create the user.";
            	$response->error = "Could not create the user.";
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
            }
            $response->userid = $userid;
            // add profilefields
			debug_trace("REMOTE CALL : Adding profile fields");	
            if (!empty($profilefields)){
                foreach($profilefields as $key => $value){
                    $key = str_replace('profile_field_', '', $key); // extract real shortname
                    if ($field = $DB->get_record('user_info_field', array('shortname' => $key))){ // do insert only if known field. Ignore others
                        $valuerec->userid = $userid;
                        $valuerec->fieldid = $field->id;
                        $valuerec->data = $value;
                        $DB->insert_record('user_info_data', $valuerec);
                    }
                }
            }
        } else {
			debug_trace("REMOTE CALL : Reviving user");	
            if ($user->deleted == 1){
                $user->deleted = 0;
                foreach($userparams as $key => $value){
                    $user->$key = $value;
                }
                $user->username = $targetuser;
                if (!$userid = $DB->update_record('user', $user)){
                	$response->status = RPC_FAILURE_RECORD;
                	$response->errors[] = "Could not revive the user.";
                	$response->error = "Could not revive the user.";
	        		if ($json_response){
		        		return json_encode($response);
		        	} else {
		        		return $response;
		        	}
                }
                $response->userid = $userid;
            } else {
				debug_trace("User exists");	
				/*
				// usually create user matching user should be happy with that
            	$response->status = RPC_SUCCESS;
            	$response->errors[] = "User already exists.";
            	$response->error = "User already exists.";
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        	*/
            }
        }
    } else {
    	if (!$userparams = $DB->get_record('user', array('username' => $targetuser))){
        	$response->status = RPC_FAILURE_RECORD;
        	$response->errors[] = "No such user to propagate.";
        	$response->error = "No such user to propagate.";
    		if ($json_response){
        		return json_encode($response);
        	} else {
        		return $response;
        	}
    	}
    	debug_trace('got user data as '.json_encode($userparams));
    }
    /// now proceed to bounces if any
    if (!empty($bounceto)){
        if (is_string($bounceto)){
            $bounceto = explode(';', $bounceto);
        }

	    foreach($bounceto as $bouncehost){
	        // check if known as mnet_hosts and possible to send admin requests
	        $sql = "
	            SELECT
	                COUNT(*)
	            FROM 
	                {mnet_host} as mh,
	                {mnet_service} as ms,
	                {mnet_host2service} as h2s
	            WHERE
	                mh.wwwroot = '$bouncehost' AND
	                mh.id = h2s.hostid AND
	                mh.deleted = 0 AND
	                h2s.serviceid = ms.id AND
	                ms.name = 'mnetadmin' AND
	                h2s.subscribe = 1
	        ";
	        $ok = $DB->count_records_sql($sql);
	        if ($ok){
	            // we can do it.
	            $userhostroot = $DB->get_field('mnet_host', 'wwwroot', array('id' => $USER->mnethostid)); 
	    		$rpc_client = new Vmoodle_XmlRpc_Client();
	    		$rpc_client->reset_method();
	    		$rpc_client->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_create_user');
	    		$caller->username = $USER->username;
	    		$caller->remoteuserhostroot = $userhostroot;
	    		$caller->remotehostroot = $CFG->wwwroot;
	    		$rpc_client->add_param($caller, 'struct'); // username
	    		$rpc_client->add_param($targetuser, 'string');
	    		$rpc_client->add_param($userparams, 'struct');
	    		if ($userhostname == ''){
	        		$rpc_client->add_param($CFG->wwwroot, 'string');            
	        	} else {
	        		$rpc_client->add_param($userhostname, 'string');            
	        	}
				debug_trace("REMOTE CALL : Bouncing to $bouncehost ");	
			    $mnet_host = new mnet_peer();
			    if ($mnet_host->set_wwwroot($bouncehost)){
	        	    $result = $rpc_client->send($mnet_host);
	        	    if (empty($result)){
	        	        // if (preg_match('/dev/', $CFG->wwwroot)) print_object($rpc_client);
	        	        $response->errors[] = 'bounce failed rpc transaction to '.$bouncehost;
	        	        $response->errors[] = $rpc_client->getErrors();
	        	        $response->error = 'bounce failed rpc transaction to '.$bouncehost;
	        	    } else {
	        	        // whatever we have, aggregate eventual remote errors to error stack.
	        	        $res = json_decode($rpc_client->response);
	        	        if (!empty($res->errors)){
	        	            foreach($res->errors as $remoteerror){
	        	                $response->errors[] = 'REMOTE: '.implode(' ', (array)$remoteerror);
	        	        		$response->error = 'bounce failed rpc some of transactions to '.$bouncehost;
	        	            }
	        	        }
	        	    }
	        	} else {
	        	    // silently ignore unless debugging
	        	    $response->errors[] = 'ignoring bounce to '.$bouncehost.' because host communication failed.';
	        	    $response->error = '(last error) ignoring bounce to '.$bouncehost.' because host communication failed.';
	        	}        	   
	        } else {
        	    $response->errors[] = 'ignoring bounce to '.$bouncehost.' because host unregistered.';
        	    $response->error = '(last error) ignoring bounce to '.$bouncehost.' because host unregistered.';
	        }       
	    }
	}
	if ($json_response){
		return json_encode($response);
	} else {
		return $response;
	}
}

function mnetadmin_rpc_create_user_wrapped($wrap) {
	debug_trace("WRAP mnetadmin_rpc_create_user : ".json_encode($wrap));	
	return mnetadmin_rpc_create_user(@$wrap['callinguser'], @$wrap['targetuser'], @$wrap['userparams'], @$wrap['userhostname'], @$wrap['bounceto'], @$wrap['onlybounce'], @$wrap['json_response']);
}

/**
 * require remote enrollement on a MNET satellite.
 * This XML-RPC call fetches for a remotely known course and enroll the user inside
 * This is essentially intended to use by foreign systems to slave the user management
 * in a MNET network.
 * @param	$callinguser		string			The calling user.
 * @param	$targetuser			string			The username or user identifier of the user to assign a role remotely.
 * @param	$useridfield	    string			The field used for identifying the user (id, idnumber or username).
 * @param	$courseidfield		string			The identifying value of the remote course 
 * @param	$courseidentifier	string			The identifying value of the remote course 
 * @param	$rolename		    string			The remote role name to be assigned as
 * @param	$starttime		    string			The starting date
 * @param	$endtime		    string			The enrollement ending date
 *
 */
function mnetadmin_rpc_remote_enrol($callinguser, $targetuser, $rolename, $whereroot, $courseidfield, $courseidentifier, $starttime = 0, $endtime = 0, $json_response = true) {
	global $CFG, $USER,$DB;
	debug_trace($CFG->wwwroot. ' >> mnetadmin_rpc_remote_enrol('.json_encode($callinguser).", $targetuser, $rolename, $whereroot, $courseidfield, $courseidentifier, $starttime = 0, $endtime = 0, $json_response = true) ");

	$extresponse = new stdclass;
	$extresponse->status = RPC_SUCCESS;
	$extresponse->errors = array();
	$extresponse->error = '';

	// Invoke local user and check his rights
	if ($auth_response = invoke_local_user((array)$callinguser, 'block/vmoodle:execute')){
		if ($json_response){
		    return $auth_response;
		} else {
			return json_decode($auth_response);
		}
	}

	if ($whereroot == $CFG->wwwroot){
		debug_trace("local enrol process for $targetuser as $rolename in $courseidentifier by $courseidfield from $starttime to $endtime");	
		// Getting remote_course definition
		switch($courseidfield){
			case 'id':
				$course = $DB->get_record('course', array('id' => $courseidentifier));
				break;
			case 'shortname':
				$course = $DB->get_record('course', array('shortname' => $courseidentifier));
				break;
			case 'idnumber':
				$course = $DB->get_record('course', array('idnumber' => $courseidentifier));
				break;		
		}
		if (!$course){
			$extresponse->status = RPC_FAILURE_RECORD;
			$extresponse->errors[] = "Unkown course $courseidentifier based on $courseidfield.";
			$extresponse->error = "Unkown course $courseidentifier based on $courseidfield.";
			debug_trace("Unkown course based on $courseidfield with $courseidentifier ");	
			if ($json_response){
	    		return json_encode($extresponse);
	    	} else {
	    		return $extresponse;
	    	}
		}

		// Getting role if default
		if (empty($rolename)){
		    $rolename = $course->defaultrolename;
		}
		debug_trace("Bounce to mnetadmin_rpc_assignrole");	
		$extresponse = mnetadmin_rpc_assign_role($callinguser, $targetuser, $rolename, 'id', CONTEXT_COURSE, $course->id, $starttime, $endtime, $json_response);
		if (!$json_response){
    		return json_decode($extresponse);
    	} else {
    		return $extresponse;
    	}
	} else {	
		debug_trace('remote source process');	
		// Make remote call
	    $userhostroot = $DB->get_field_select('mnet_host', 'wwwroot', " id = $USER->mnethostid AND deleted = 0 "); 
	    if (!$userhostroot){
            $extresponse->error = 'Unkown user host root (or deleted).';        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
	    }

		if (!$DB->record_exists('mnet_host', array('wwwroot' => $whereroot, 'deleted' => 0))){
            $extresponse->error = '$whereroot is unknown host or deleted.';        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
		}
        $rpcclient = new mnet_xmlrpc_client();
		$rpcclient->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_remote_enrol');
		$caller->username = $USER->username;
		$caller->remoteuserhostroot = $userhostroot;
		$caller->remotehostroot = $CFG->wwwroot;
		$rpcclient->add_param($caller, 'struct'); // caller user
		$rpcclient->add_param($targetuser, 'string');
	    $rpcclient->add_param($rolename, 'string');
	    $rpcclient->add_param($whereroot, 'string');
	    $rpcclient->add_param($courseidfield, 'string');
	    $rpcclient->add_param($courseidentifier, 'string');
	    $rpcclient->add_param($starttime, 'int');
	    $rpcclient->add_param($endtime, 'int');
        $mnet_host = new mnet_peer();
        $mnet_host->set_wwwroot($whereroot);
        if (!$rpcclient->send($mnet_host)){
            $extresponse->status = RPC_FAILURE;
            $extresponse->errors[] = 'REMOTE : '.implode("<br/>\n", @$rpcclient->errors);        
            $extresponse->error = 'REMOTE : '.implode("<br/>\n", @$rpcclient->errors);        
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        }
        $response = json_decode($rpcclient->response);
        if ($response->status == 200){
            $extresponse->message = 'remote enrol success';
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        } else {
        	$extresponse->status = RPC_FAILURE;
		    $extresponse->errors = array();
		    $extresponse->errors[] = 'Remote application errors : ';
		    $extresponse->errors = array_merge($extresponse->errors, $response->errors);
		    $extresponse->error = 'Remote application error.';
			if ($json_response){
				return json_encode($extresponse);
			} else {
				return $extresponse;
			}
        }
    }
}

function mnetadmin_rpc_remote_enrol_wrapped($wrap) {
	// debug_trace("WRAP mnetadmin_rpc_remote_enrol : ".json_encode($wrap));	
	return mnetadmin_rpc_remote_enrol(@$wrap['callinguser'], @$wrap['targetuser'], @$wrap['rolename'], @$wrap['whereroot'], @$wrap['courseidfield'], @$wrap['courseidentifier'], @$wrap['starttime'], @$wrap['endtime'], @$wrap['json_response']);
}

//*********** Utilities *****************//

function rpc_check_context_target($contextlevel, $contextidentityfield, $contextidentity, &$response, $json_response){
        global $DB;
	// Check context target
	switch($contextlevel){
	    case CONTEXT_SYSTEM :{
	        $context = context_system::instance();
	        break;
	    }
	    case CONTEXT_COURSE :{
	    	if (!preg_match('/id|shortname|idnumber/', $contextidentityfield)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = "This fieldname ($contextidentityfield) does\'nt apply for this course context level.";
        		$response->error = "This fieldname ($contextidentityfield) does\'nt apply for this course context level.";
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	    	}
	        if(!$course = $DB->get_record('course', array($contextidentityfield => $contextidentity))){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = " Course Context $contextidentity not found based on $contextidentityfield.";
        		$response->error = " Course Context $contextidentity not found based on $contextidentityfield.";
        		if ($json_response){
	        		return json_encode($response);
	        	} else {
	        		return $response;
	        	}
	        }
	        $context = context_course::instance($course->id);
	        break;
	    }
	    case CONTEXT_MODULE :{
	    	if (!preg_match('/id|idnumber/', $contextidentityfield)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = " This fieldname ($contextidentityfield) does\'nt apply for this module context level.";
        		$response->error = " This fieldname ($contextidentityfield) does\'nt apply for this module context level.";
	    	}
	        if(!$cm = $DB->get_record('course_modules', array($contextidentityfield => $contextidentity))){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = " Course Module $contextidentity not found based on $contextidentityfield.";
        		$response->error = " Course Module $contextidentity not found based on $contextidentityfield.";
	        }
	        if (!$context = context_module::instance($cm->id)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'Course Module context not found.';
        		$response->error = 'Course Module context not found.';
	        }
	        break;
	    }
	    case CONTEXT_USER :{
	    	if (!preg_match('/id|username|email|idnumber', $contextidentityfield)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = " This fieldname ($contextidentityfield) does\'nt apply for this user context level.";
        		$response->error = " This fieldname ($contextidentityfield) does\'nt apply for this user context level.";
	    	}
	        if(!$user = $DB->get_record('user', array($contextidentityfield => $contextidentity))){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = " User $contextidentity not found based on $contextidentityfield. ";
        		$response->error = " User $contextidentity not found based on $contextidentityfield. ";
	        }
	        if (!$context = context_user::instance($user->id)){
        		$response->status = RPC_FAILURE_RECORD;
        		$response->errors[] = 'User context not found with userid '.$user->id.'.';
        		$response->error = 'User context not found with userid '.$user->id.'.';
	        }
	        break;
	    }
	    default:{
    		$response->status = RPC_FAILURE_RECORD;
    		$response->errors[] = "Context level ($contextlevel) not implemented.";
    		$response->error = "Context level ($contextlevel) not implemented.";
	    }
	}
	debug_trace("Got context $contextlevel");
	return $context;
}
