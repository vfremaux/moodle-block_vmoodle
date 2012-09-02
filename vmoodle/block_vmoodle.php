<?php
/**
 * Declare Vmoodle block.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @version Moodle 2.2
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
        //$context = context_block::instance(0); #WAFA #1.9                                           
        $context = get_context_instance(CONTEXT_BLOCK,$this->instance->id);
           
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
		global $DB;
		// Initializing
		$str = '';
		// Getting virtual moodles
		$vmoodles = $DB->get_records('block_vmoodle');
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

	function after_install(){		
   
	}

	/**
	 * Remove the XMLRPC service.
	 * @return					boolean				TRUE if the deletion is successfull, FALSE otherwise.
	 */
	function before_delete() {
		global $CFG, $DB, $OUTPUT;
		// Adding requirements
		include_once ($CFG->libdir.'/accesslib.php');
		/**
		* remove application record
		*/
		$DB->delete_records('mnet_application', array('name' => 'vmoodle'));
		/*
		 * Uninstalling plugin libraries
		 */
		// Getting all plugins
		$plugins = get_list_of_plugins('blocks/vmoodle/plugins');
		foreach($plugins as $plugin) {
			// Call custom uninstall plugin function
			if (file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/lib.php')){
				include_once($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/lib.php');
				$uninstall_function = $plugin.'_uninstall';
				if (function_exists($uninstall_function) && !$uninstall_function()) {
					echo $OUTPUT->notification('The plugin "'.$plugin.'" was not correctly uninstalled.');
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
		// Returning result
		return true;
	}
	/**
	 * Update subplugins.
	 * @param	$return			string			The URL to prompt to the user to continue. 
	 */
	public function update_subplugins($verbose) {
		upgrade_plugins('vmoodlelib', '', '', $verbose);
	}
    public function cron(){
        global $CFG;
        global $MNET;
        include "mnetcron.php";
    }
}