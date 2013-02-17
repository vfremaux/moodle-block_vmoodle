<?php

/**
 * Declare Vmoodle block.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery@club-internet.fr)
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 
// Adding requirements
require_once($CFG->dirroot.'/blocks/vmoodle/locallib.php');

/**
 * Vmoodle block displays virtual platforms and link to the settings.
 */
class block_vmoodle extends block_base {
	
	/**
	 * Initialize block.
	 */
	public function init() {		
		// Setting block parameters
		$this->title = get_string('blockname', 'block_vmoodle');
		$this->content_type = BLOCK_TYPE_TEXT;
		$this->version = 2011012200;
		$this->cron = 5;
	}
	
	/**
	 * Define the block preferred width.
	 * @return int The block prefered width.
	 */
	public function preferred_width() {
		return 200;
	}
	
	/**
	 * Check if the block have a configuration file.
	 * @return				boolean		True if the block have a configuration file, false otherwise.
	 */
	public function has_config() {
		return true;
	}
	
	/**
	 * Define the applicable formats to the block.
	 * @return array The aplicable formats to the block.
	 */
	public function applicable_formats() {
		return array('site' => true, 'learning' => false, 'admin' => true);
	}
	
	/**
	 * Return the block content.
	 * @uses $CFG
	 * @return string The block content.
	 */
	public function get_content() {		
		global $CFG;
		
		// Checking content cached
		if ($this->content !== NULL)
			return $this->content;
		
		// Creating new content
		$this->content = new stdClass;
		$this->content->footer = '';
		
		// Getting context
		$context = get_context_instance(CONTEXT_SYSTEM, 0);
		
		// Setting content depending on capabilities
		if (isloggedin()) {
			if (has_capability('block/vmoodle:managevmoodles', $context)) {
				$this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/vmoodle/view.php">'.get_string('administrate', 'block_vmoodle').'</a><br/>';
				$this->content->text = $this->_print_status();
			} else {
				$this->content->text = get_string('notallowed', 'block_vmoodle');
			}
		}
		
		// Returning content
		return $this->content;
	}
	
	/**
	 * Return status for all defined virtual moodles.
	 * @return string Status for all defined virtual moodles.
	 */
	private function _print_status(){
		// Initializing
		$str = '';
		// Getting virtual moodles
		$vmoodles = get_records('block_vmoodle');
		// Creating table
		if ($vmoodles) {
			$str = '<table>';
			foreach($vmoodles as $vmoodle)
				$str .= '<tr><td><a href="'.$vmoodle->vhostname.'" target="_blank">'.$vmoodle->shortname.' - '.$vmoodle->name.'</a></td><td>'.vmoodle_print_status($vmoodle, true).'</td></tr>';
			$str .= '</table>';
		}
		// Returning table
		return $str;
	}
	
	/**
	 * Setup the XMLRPC service, RPC calls and default block parameters.
	 * @return boolean TRUE if the installation is successfull, FALSE otherwise.
	 */
	function after_install() {
		global $CFG;
		
		// Initialising
		$result = true;
		$rpc = new stdclass;
		$rpcmap = new stdclass;
		
		/*
		 * Installing mnet_admin service
		 */
		if (!get_record('mnet_service', 'name', 'mnet_admin')) {
			// Installing service
			$service = new stdclass;
			$service->name = 'mnet_admin';
			$service->description = get_string('mnet_admin_name', 'block_vmoodle');
			$service->apiversion = 1;
			$service->offer = 1;
			if (!$serviceid = insert_record('mnet_service', $service)){
				notify('Error installing mnet_admin service.');
				$result = false;
			}
		}
		
		/*
		 * Installing RPC call 'bind_peer'
		 */
		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_bind_peer')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_bind_peer';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_bind_peer';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Adds a new peer to the known hosts with its public key.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "add_peer".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $serviceid;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "add_peer" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
		
		/*
		 * Installing RPC call 'unbind_peer'
		 */
		// Checking if it is already installed
		if (!get_record('mnet_rpc', 'function_name', 'mnetadmin_rpc_unbind_peer')) {
			
			// Creating RPC call
			$rpc->function_name = 'mnetadmin_rpc_unbind_peer';
			$rpc->xmlrpc_path = 'blocks/vmoodle/rpclib.php/mnetadmin_rpc_unbind_peer';
			$rpc->parent_type = 'block';  
			$rpc->parent = 'vmoodle';
			$rpc->enabled = 0; 
			$rpc->help = 'Deletes a peer by unmarking it.';
			$rpc->profile = '';
			
			// Adding RPC call
			if (!$rpcid = insert_record('mnet_rpc', $rpc)) {
				notify('Error installing mnet_admin RPC call "unbind_peer".');
				$result = false;
			} else {
				// Mapping service and call
				$rpcmap->serviceid = $serviceid;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "delete_peer" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}

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
				$rpcmap->serviceid = $serviceid;
				$rpcmap->rpcid = $rpcid;
				if (!insert_record('mnet_service2rpc', $rpcmap)) {
					notify('Error mapping RPC call "keyswap" to the "mnet_admin" service.');
					$result = false;
				}
			}
		}
		
		/*
		 * Setting default configuration
		 */
		set_config('block_vmoodle_automatedschema', 1);
		set_config('block_vmoodle_host_source', 'vmoodle');
		set_config('block_vmoodle_organization', get_string('organization', 'block_vmoodle'));
		set_config('block_vmoodle_organization_email', 'foo@organization');
		set_config('block_vmoodle_organization_unit', get_string('organizationunit', 'block_vmoodle'));
		set_config('block_vmoodle_vhostname', '<%%HOSTNAME%%>');
		set_config('block_vmoodle_vdatapathbase', '');
		set_config('block_vmoodle_vdbbasename', 'vmld_');
		set_config('block_vmoodle_vdbhost', 'localhost');
		set_config('block_vmoodle_vdblogin', 'root');
		set_config('block_vmoodle_vdbtype', 'mysql');
		set_config('block_vmoodle_vdbpass', '');
		set_config('block_vmoodle_vdbpersist', 0);
		set_config('block_vmoodle_vdbprefix', 'mdl_');
		set_config('block_vmoodle_vmoodleip', '127.0.0.1');
		// Setting default services strategy
		$records_services = get_records('mnet_service', '', '', '', 'id,name,description');
		foreach($records_services as &$record_service) {
			$record_service->publish = 0;
			$record_service->subscribe = 0;
		}
		set_config('block_vmoodle_services_strategy', serialize($records_services));
		unset($records_services, $record_service);
		
		/*
		 * Creating template directory
		 */
		if (!is_dir($CFG->dataroot.'/vmoodle')){
			if (!mkdir($CFG->dataroot.'/vmoodle')) {
				notify('Error creating template directory.');
				$result = false;
			}
		}

        // Adding mnet application type
        if (!get_record('mnet_application', 'name', 'vmoodle')){
            $application->name = 'vmoodle';
            $application->display_name = get_string('vmoodleappname', 'block_vmoodle');
            $application->xmlrpc_server_url = '/blocks/vmoodle/mnet/server.php';
            insert_record('mnet_application', $application);
        }
        
		
		// Returning result
		return $result;    
	}
	
	/**
	 * Remove the XMLRPC service.
	 * @return					boolean				TRUE if the deletion is successfull, FALSE otherwise.
	 */
	function before_delete() {
		global $CFG;
		
		// Adding requirements
		include_once ($CFG->libdir.'/accesslib.php');
		
		/**
		* remove application record
		*/
		delete_records('mnet_application', 'name', 'vmoodle');
		
		/*
		 * Uninstalling plugin libraries
		 */
		// Getting all plugins
		$plugins = get_list_of_plugins('blocks/vmoodle/plugins/libs');
		foreach($plugins as $plugin) {
			// Call custom uninstall plugin function
			if (file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/lib.php')){
				include_once($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/lib.php');
				$uninstall_function = $plugin.'_uninstall';
				if (function_exists($uninstall_function) && !$uninstall_function()) {
					notify('The plugin "'.$plugin.'" was not correctly uninstalled.');
				}
			}
			// Remove installed version from config
			unset_config('vmoodle_lib_'.$plugin.'_version');
		}
		
		// Removing module configuration
		unset_config('block_vmoodle_automatedschema');
		unset_config('block_vmoodle_host_source');
		unset_config('block_vmoodle_organization');
		unset_config('block_vmoodle_organization_email');
		unset_config('block_vmoodle_organization_unit');
		unset_config('block_vmoodle_services_strategy');
		unset_config('block_vmoodle_vhostname');
		unset_config('block_vmoodle_vdatapathbase');
		unset_config('block_vmoodle_vdbbasename');
		unset_config('block_vmoodle_vdbhost');
		unset_config('block_vmoodle_vdblogin');
		unset_config('block_vmoodle_vdbtype');
		unset_config('block_vmoodle_vdbpass');
		unset_config('block_vmoodle_vdbpersist');
		unset_config('block_vmoodle_vdbprefix');
		unset_config('block_vmoodle_vmoodleip');
		
		
		// Checking if mnet_admin service is installed
		if (!($service = get_record('mnet_service', 'name', 'mnet_admin')))
			return true;
		
		// Uninstalling mnet_admin service
		delete_records('mnet_host2service', 'serviceid', $service->id);
		delete_records('mnet_service2rpc', 'serviceid', $service->id);
		delete_records('mnet_rpc', 'parent', 'vmoodle');
		delete_records('mnet_service', 'name', 'mnet_admin');
		
		// Returning result
		return true;
	}
	
	/**
	 * Update subplugins.
	 * @param	$return			string			The URL to prompt to the user to continue. 
	 */
	public function update_subplugins($return) {
		upgrade_plugins('vmoodle_lib', 'blocks/vmoodle/plugins/libs', $return);
	}

    public function cron(){
        global $CFG;
        global $MNET;
        
        include "mnetcron.php";
    }
}