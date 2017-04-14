<?php

// Moodle form's library.
require_once ($CFG->libdir.'/formslib.php');

// Add needed javascript here (because addonload() is needed before).
$js = array($CFG->wwwroot.'/blocks/vmoodle/js/host_form.js');
require_js($js);

/**
 * Define form for adding or editing a vmoodle host.
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Vmoodle_Host_Form extends moodleform {
	/** Action to call from controller. */
	private $mode;

	/** Data array for the form. */
	private $platform_form;

	/**
	 * Constructor.
	 * @param	$mode			string		The action to call from controler.
	 * @param	$platform_form	array		Data to input in fields.
	 */
	public function __construct($mode, $platform_form=null) {
		// Settings mode and data.
		$this->mode			    = $mode;
		$this->platform_form	= $platform_form;
		
		// Calling parent's constructor.
		parent::__construct('view.php?view=management&what=do'.$this->mode.'&page='.$this->mode);
	}

	/**
	 * Describes the form (each elements' name  corresponds to its name in database).
	 */
	public function definition() {
		// Global configuration.
		global $CFG;

		// Settings variables.
		$mform				    = &$this->_form;
		$size_input_text	    = 'size="30"';
		$size_input_text_big	= 'size="60"';

		/*
		 * Host's id.
		 */
		$mform->addElement('hidden', 'id');

		/*
		 * Features fieldset.
		 */
		$mform->addElement('header', 'featuresform', get_string('addformfeaturesgroup', 'block_vmoodle'));
		// Name.
		$mform->addElement('text', 'name', get_string('addformname', 'block_vmoodle'), $size_input_text);
		$mform->setHelpButton('name', array('name', get_string('addformname', 'block_vmoodle'), 'block_vmoodle'));
		if($this->isInAddMode()){
			// Shortname.
			$mform->addElement('text', 'shortname', get_string('addformshortname', 'block_vmoodle'), ($this->mode == 'edit' ? 'disabled="disabled" ' : ''));
			$mform->setHelpButton('shortname', array('shortname', get_string('addformshortname', 'block_vmoodle'), 'block_vmoodle'));
		}
		// Description.
		$mform->addElement('htmleditor', 'description', get_string('addformdescription', 'block_vmoodle'), 'rows="15" cols="40"');
		$mform->setHelpButton('description', array('description', get_string('addformdescription', 'block_vmoodle'), 'block_vmoodle'));
		if($this->isInAddMode()){
			// Host's name.
			$mform->addElement('text', 'vhostname', get_string('vhostname', 'block_vmoodle'), ($this->mode == 'edit' ? 'disabled="disabled" ' : '').$size_input_text);
			$mform->setHelpButton('vhostname', array('vhostname', get_string('vhostname', 'block_vmoodle'), 'block_vmoodle'));
			$mform->addElement('checkbox', 'forcedns', get_string('forcedns', 'block_vmoodle'));
		}
		$mform->closeHeaderBefore('dbform');

		/*
		 * Database fieldset.
		 */
		$mform->addElement('header', 'dbform', get_string('addformdbgroup', 'block_vmoodle'));
		// Database type.
		$dbtypearray = array('mysql' => 'MySQL', 'postgres' => 'PostgreSQL');
		$mform->addElement('select', 'vdbtype', get_string('vdbtype', 'block_vmoodle'), $dbtypearray);
		$mform->setHelpButton('vdbtype', array('vdbtype', get_string('vdbtype', 'block_vmoodle'), 'block_vmoodle'));
		// Database host.
		$mform->addElement('text', 'vdbhost', get_string('vdbhost', 'block_vmoodle'));
		$mform->setHelpButton('vdbhost', array('vdbhost', get_string('vdbhost', 'block_vmoodle'), 'block_vmoodle'));
		// Database login.
		$mform->addElement('text', 'vdblogin', get_string('vdblogin', 'block_vmoodle'));
		// Database password.
		$mform->addElement('password', 'vdbpass', get_string('vdbpass', 'block_vmoodle'));
		// Button for testing database connection.
		$mform->addElement('button', 'testconnection', get_string('testconnection', 'block_vmoodle'), 'onclick="opencnxpopup(\''.$CFG->wwwroot.'\'); return true;"');
		// Database name.
		$mform->addElement('text', 'vdbname', get_string('vdbname', 'block_vmoodle'));
		$mform->setHelpButton('vdbname', array('vdbname', get_string('vdbname', 'block_vmoodle'), 'block_vmoodle'));
		// Table's prefix.
		$mform->addElement('text', 'vdbprefix', get_string('vdbprefix', 'block_vmoodle'));

		// Connection persistance.
		$noyesarray = array('0' => get_string('no'), '1' => get_string('yes'));
		$mform->addElement('select', 'vdbpersist', get_string('vdbpersist', 'block_vmoodle'), $noyesarray);
		$mform->setHelpButton('vdbpersist', array('vdbpersist', get_string('vdbpersist', 'block_vmoodle'), 'block_vmoodle'));
		$mform->closeHeaderBefore('nfform');

		/*
		 * Network and data fieldset.
		 */
		$mform->addElement('header', 'nfform', get_string('addformnfgroup', 'block_vmoodle'));

		// Path for "moodledata".
		$mform->addElement('text', 'vdatapath', get_string('vdatapath', 'block_vmoodle'), $size_input_text_big);
		$mform->setHelpButton('vdatapath', array('vdatapath', get_string('vdatapath', 'block_vmoodle'), 'block_vmoodle'));

		// Button for testing datapath.
		$mform->addElement('button', 'testdatapath', get_string('testdatapath', 'block_vmoodle'), 'onclick="opendatapathpopup(\''.$CFG->wwwroot.'\'); return true;"');

		// MNET activation.
		/*
		$mform->addElement('select', 'mnetenabled', get_string('mnetenabled', 'block_vmoodle'), $noyesarray, 'onchange="switcherMNET();"');
		$mform->setHelpButton('mnetenabled', array('mnet', get_string('mnetenabled', 'block_vmoodle'), 'block_vmoodle'));
		$mform->setDefault('mnetenabled', '1');
		*/

		// MultiMNET.
		$subnetworks = array('-1' => get_string('nomnet', 'block_vmoodle'));
		$subnetworks['0'] = get_string('mnetfree', 'block_vmoodle');
		$subnetworksrecords = get_records_select('block_vmoodle', 'mnet > 0', 'mnet');
		$newsubnetwork = 1;
		if(!empty($subnetworksrecords)){
			foreach ($subnetworksrecords as $subnetworksrecord) {
				$subnetworks[$subnetworksrecord->mnet] = $subnetworksrecord->mnet;
			}
			$newsubnetwork = array_pop($subnetworksrecords)->mnet + $newsubnetwork;
		}
		$subnetworks[$newsubnetwork] = $newsubnetwork.' ('.get_string('mnetnew', 'block_vmoodle').')';
		$mform->addElement('select', 'mnet', get_string('multimnet', 'block_vmoodle'), $subnetworks, 'onchange="switcherServices(\''.$newsubnetwork.'\'); return true;"');
		$mform->setHelpButton('mnet', array('mnet', get_string('mnetenabled', 'block_vmoodle'), 'block_vmoodle'));

		// Services strategy.
		$services_strategies = array(
    		'default' => get_string('servicesstrategydefault', 'block_vmoodle'), 
    		'subnetwork' => get_string('servicesstrategysubnetwork', 'block_vmoodle')
		);
		$mform->addElement('select', 'services', get_string('servicesstrategy', 'block_vmoodle'), $services_strategies);
		$mform->setHelpButton('services', array('services', get_string('servicesstrategy', 'block_vmoodle'), 'block_vmoodle'));

		// CRON (linux).
		/**
		// Obsolete since vcron.php
		if ($CFG->ostype != 'WINDOWS') {
			$mform->addElement('text', 'crontab', get_string('crontab', 'block_vmoodle'), $size_input_text_big);
			$mform->setHelpButton('crontab', array('crontab', get_string('crontab', 'block_vmoodle'), 'block_vmoodle'));
		}
		**/

		if($this->isInAddMode()){
			// Template.
			$templatesarray	= vmoodle_get_available_templates();
			$mform->addElement('select', 'vtemplate', get_string('vtemplate', 'block_vmoodle'), $templatesarray);
			$mform->setHelpButton('vtemplate', array('vtemplate', get_string('vtemplate', 'block_vmoodle'), 'block_vmoodle'));
		}
		$mform->closeHeaderBefore('submitbutton');

		// Control buttons.
		$buttonarray = array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string(($this->mode == 'edit' ? 'edit' : 'create')));
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray, 'controlbuttons', '', array(' '), false);

		// Rules for the add mode.
		if($this->isInAddMode()) {
			$mform->addRule('name', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('shortname', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vhostname', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vdbhost', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vdblogin', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vdbpass', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vdbname', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vdbprefix', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
			$mform->addRule('vdatapath', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
		}

		// If adding and default configuration (automated schema).
		/*
		if($this->isInAddMode() && isset($this->platform_form)) {

			$mform->setDefault('vhostname',		$this->platform_form->vhostname);
			$mform->setDefault('vdbtype',		$this->platform_form->vdbtype);
			$mform->setDefault('vdbhost',		$this->platform_form->vdbhost);
			$mform->setDefault('vdblogin',		$this->platform_form->vdblogin);
			$mform->setDefault('vdbpass',		$this->platform_form->vdbpass);
			$mform->setDefault('vdbname',		$this->platform_form->vdbname);
			$mform->setDefault('vdbprefix',		$this->platform_form->vdbprefix);
			$mform->setDefault('vdbpersist',	$this->platform_form->vdbpersist);
			$mform->setDefault('vdatapath', 	$this->platform_form->vdatapath);

			// Try to get crontab (Linux).
			if ($CFG->ostype != 'WINDOWS'){
				$crontabcmd = escapeshellcmd('crontab -l');
				$mform->setDefault('crontab',	$crontabcmd);
			}
		}

		// If editing.
		if($this->isInEditMode() && isset($this->platform_form)) {

			 * Settings the data in the form.
			// Host's id.
			$mform->setDefault('id',			$this->platform_form->id);
			// Name.
			$mform->setDefault('name',			$this->platform_form->name);
			// Description.
			$mform->setDefault('description',	$this->platform_form->description);
			// Database type.
			$mform->setDefault('vdbtype',		$this->platform_form->vdbtype);
			// Database host.
			$mform->setDefault('vdbhost',		$this->platform_form->vdbhost);
			// Database login.
			$mform->setDefault('vdblogin',		$this->platform_form->vdblogin);
			// Database password.
			$mform->setDefault('vdbpass',		$this->platform_form->vdbpass);
			// Database name.
			$mform->setDefault('vdbname',		$this->platform_form->vdbname);
			// Table's prefix.
			$mform->setDefault('vdbprefix',		$this->platform_form->vdbprefix);
			// Connection persistance.
			$mform->setDefault('vdbpersist',	$this->platform_form->vdbpersist);
			// Connection persistance.
			$mform->setDefault('vdatapath',		$this->platform_form->vdatapath);

			// MNET activation.
			if($this->platform_form->mnet > -1) {
				$mform->setDefault('mnetenabled',	'yes');
			}
			else {
				$mform->setDefault('mnetenabled',	'no');
			}

			// MultiMNET.
			$mform->setDefault('multimnet',		$this->platform_form->mnet);
		}
		*/
	}

	/**
	 * Test connection validation.
	 * @see lib/moodleform#validation($data, $files)
	 */
	function validation($data) {
		global $CFG;

		// Empty array.
		$errors = parent::validation($data, null);

		// Checks database connection again, after Javascript test.
		$database	= new stdClass;
		$database->vdbtype		= $data['vdbtype'];
		$database->vdbhost		= $data['vdbhost'];
		$database->vdblogin		= $data['vdblogin'];
		$database->vdbpass		= $data['vdbpass'];
		if(!vmoodle_make_connection($database, false)){
			$errors['vdbhost']	= get_string('badconnection', 'block_vmoodle');
			$errors['vdblogin']	= get_string('badconnection', 'block_vmoodle');
			$errors['vdbpass']	= get_string('badconnection', 'block_vmoodle');
		}

		// Checks if database's name doesn't finish with '_'.
		if($data['vdbname'][strlen($data['vdbname']) -1] == '_'){
			$errors['vdbname']	= get_string('baddatabasenamecoherence', 'block_vmoodle');
		}
		
		// Checks if table's prefix doesn't begin with restricted values (which can evolve).
		$restrictedvalues = array(
			'vmoodle_'
		);
		foreach($restrictedvalues as $restrictedvalue){
			if($data['vdbprefix'] == $restrictedvalue){
				$errors['vdbprefix']	= get_string('baddatabaseprefixvalue', 'block_vmoodle');
			}
		}

		// ATTENTION Checks if user has entered a datapath with only one backslash between each folder
		// and/or file.
		if(isset($CFG->ostype) && ($CFG->ostype == 'WINDOWS')
		&& (preg_match('#\\\{3,}#', $data['vdatapath']) > 0)){
			$errors['vdatapath']	= get_string('badmoodledatapathbackslash', 'block_vmoodle');
			return $errors;
		}

		// Test of values which have to be well-formed and can not be modified after.
		if($this->isInAddMode()) {

			// Checks 'shortname', which must have no spaces.
			$shortname	=	$data['shortname'];
			if(strstr($shortname, ' ')){
				$errors['shortname']	= get_string('badshortname', 'block_vmoodle');
			}

			// Checks 'vhostname', if not already used.
			if($this->isEqualToAnotherVhostname($data['vhostname'])){
				
				// Check if the vhostname is deleted
				$sqlrequest = 'SELECT
									m.deleted
							   FROM
									'.$CFG->prefix.'block_vmoodle b,
									'.$CFG->prefix.'mnet_host m
							   WHERE
									b.vhostname = "'.$data['vhostname'].'"
							   AND
									b.vhostname = m.wwwroot' ; 
									
				$resultsqlrequest = get_record_sql($sqlrequest);
			
				if(!empty($resultsqlrequest)) {
					
					if($resultsqlrequest->deleted == 0) {
						$errors['vhostname']	= get_string('badhostnamealreadyused', 'block_vmoodle');
					} else {
						//Id the plateforme is deleted and the user want to reactivate the vhostname
						if($data['vtemplate'] == 0) {
							$sqlrequest = 'SELECT
												id,
												vdatapath,
												vdbname
										   FROM
												'.$CFG->prefix.'block_vmoodle
										   WHERE
												vhostname = "'.$data['vhostname'].'"';
												
							$resultsqlrequest = get_record_sql($sqlrequest);
														
							// Checks if datapath and vdbname of vhostname are the same on the form
							if($resultsqlrequest->vdatapath != stripslashes($data['vdatapath']) && 
								$resultsqlrequest->vdbname != $data['vdbname']) {
								$errors['vdatapath'] = get_string('errorreactivetemplate', 'block_vmoodle');
								$errors['vdbname'] = get_string('errorreactivetemplate', 'block_vmoodle');
							}					
						}
					}
				}
			}
			
			// Checks 'vhostname' consistency, with a regular expression.
			$vhostname	=	$data['vhostname'];
			if(!preg_match('/^http(s)?:\/\//', $vhostname)){
				$errors['vhostname']	= get_string('badvhostname', 'block_vmoodle');
			}

			// Checks 'vdatapath', if not already used.
			if($this->isEqualToAnotherDataRoot($data['vdatapath'])){
				if($data['vtemplate'] === 0) {
				} else {					
					$errors['vdatapath']	= get_string('badmoodledatapathalreadyused', 'block_vmoodle');
				}
			}

			// Checks 'vdbname', if not already used.
			if($this->isEqualToAnotherDatabaseName($data['vdbname']) && $data['vtemplate'] != 0){
				$errors['vdbname']	= get_string('baddatabasenamealreadyused', 'block_vmoodle');
			}
		}

		return $errors;
	}

	/**
	 * Test if form is in add mode.
	 * @return		bool		If TRUE, form is in add mode, else FALSE.
	 */
	protected function isInAddMode() {
		return ($this->mode == 'add');
	}

	/**
	 * Test if form is in edit mode.
	 * @return		bool		If TRUE, form is in edit mode, else FALSE.
	 */
	protected function isInEditMode() {
		return ($this->mode == 'edit');
	}

	/**
	 * Checks if the new virtual host's selected hostname is already used.
	 * @param		$vhostname	string	The hostname to check.
	 * @return		bool		If TRUE, the chosen hostname is already used, else FALSE.
	 */
	private function isEqualToAnotherVhostname($vhostname) {
		$block_vmoodles = get_records_select('block_vmoodle', 'vhostname LIKE \'%'.$vhostname.'\'');
		return (empty($block_vmoodles) ? false : true);
	}

	/**
	 * Checks if the new virtual host's datapath is already used.
	 * @param		$vdatapath		string	The datapath to check.
	 * @return		bool		If TRUE, the chosen datapath is already used, else FALSE.
	 */
	private function isEqualToAnotherDataRoot($vdatapath) {
		$vmoodles = get_records('block_vmoodle');
		if (!empty($vmoodles)){
			// Retrieves all the vmoodles datapaths.
			$vdatapaths	=	array();
			foreach($vmoodles as $vmoodle){
				$vdatapaths[]	=	$vmoodle->vdatapath;
			}
			
			return in_array(stripslashes($vdatapath), $vdatapaths);
		}
		return false;
	}

	/**
	 * Checks if the new virtual host's selected database name is already used.
	 * @param		$vdbname	string	The database name to check.
	 * @return		bool		If TRUE, the chosen database name is already used, else FALSE.
	 */
	private function isEqualToAnotherDatabaseName($vdbname) {
		$vdbs = get_records_sql('SHOW DATABASES');
		if (!empty($vdbs)){
			// Retrieves all the databases names.
			$vdbnames	=	array();
			foreach($vdbs as $vdb){
				$vdbnames[]	=	$vdb->Database;
			}
			return in_array($vdbname, $vdbnames);
		}
		return false;
	}
}