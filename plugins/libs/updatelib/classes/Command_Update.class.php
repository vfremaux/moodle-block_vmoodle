<?php
require_once VMOODLE_CLASSES_DIR.'Command.class.php';
require_once VMOODLE_CLASSES_DIR.'Command_Parameter.class.php';
require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

/**
 * Describes a platform update command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Vmoodle_Command_Update extends Vmoodle_Command {
	/** The cURL timeout */
	const curl_timeout = 30;
	
	/**
	 * Constructor.
	 * @throws				Vmoodle_Command_Exception.
	 */
	public function __construct() {
		// Getting command description
		$cmd_name = get_string_vml('cmdupdatename', 'updatelib');
		$cmd_desc = get_string_vml('cmdupdatedesc', 'updatelib');
								
		// Creating max request parameter
		$max_request_param = new Vmoodle_Command_Parameter('max_request', 'text', get_string_vml('maxrequestupdatedesc', 'updatelib'), 10);
		
		// Creating command
		parent::__construct($cmd_name, $cmd_desc, array($max_request_param));
	}
	
	/**
	 * Execute the command.
	 * @param	$hosts		mixed			The host where run the command (may be wwwroot or an array).
	 * @throws				Vmoodle_Command_Exception
	 */
	public function run($hosts) {
		global $CFG, $USER;
		
		// Checking capabilities
		if (!has_capability('block/vmoodle:execute', get_context_instance(CONTEXT_SYSTEM)))
			throw new Vmoodle_Command_Exception('insuffisantcapabilities');
		
		// Setting timeout
		set_time_limit(0);
			
		// Getting platforms
		$available_platforms = get_available_platforms();
		
		// Getting max request
		$max_request = intval($this->getParameter('max_request')->getValue());
		if (!is_int($max_request) || $max_request < 1)
			throw new Vmoodle_Command_Update_Exception('wrongmaxrequest');
			
		// Initializing
		$responses = array();
		
		// Sending requests
		foreach($hosts as $wwwroot => $host) {
			// Initializing
			$nbr_request = 0;
			// Initializing cURL session
			$ch = curl_init($wwwroot.'/admin/index.php');
			// Setting cURL session
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, self::curl_timeout);
			// Sending requests
			while ($nbr_request < $max_request) {
				// Sending request
				$return = curl_exec($ch);
				// Testing content
				if ($return === FALSE) {
					$responses[$wwwroot] = (object) array(
						'status' => RPC_FAILURE,
						'error' => get_string_vml('curlexecutionfailed', 'updatelib', curl_error($ch))
					);
					break;
				}
				// Checking http answer code
				$httpcode = $http_code = $this->_getHttpCode($return);
				if ($httpcode != 200 && $httpcode != 303) {
					$responses[$wwwroot] = (object) array(
						'status' => RPC_FAILURE,
						'error' => get_string_vml('httperror', 'updatelib', $http_code)
					);
					break;
				}
				// Checking if update is finished
				if ($this->_isUpdated($return)) {
					$responses[$wwwroot] = (object) array(
						'status' => RPC_SUCCESS
					);
					break;
				}
				
				// Counting request
				$nbr_request++;
			}
			if ($nbr_request == $max_request) {
				$response[$wwwroot] = (object) array(
					'status' => RPC_FAILURE,
					'error' => get_string_vml('maxcurlexecution', 'updatelib')
				);
			}
		}
		
		// Saving results
		$this->results = $responses + $this->results;
	}
	
	/**
	 * Get the result of command execution for one host.
	 * @param	$host		string			The host to retrieve result (optional, if null, returns general result).
	 * @param	$key		string			The information to retrieve (ie status, error / optional).
	 * @return				mixed			The result or null if result does not exist.
	 * @throws				Vmoodle_Command_Exception.
	 */
	public function getResult($host=null, $key=null) {
		// Checking if command has been runned
		if (!$this->isRunned())
			throw new Vmoodle_Command_Exception('commandnotrun');
		
		// Checking host (general result isn't provide in this kind of command)
		if (is_null($host) || !array_key_exists($host, $this->results))
			return null;
		$result = $this->results[$host];
		
		// Checking key
		if (is_null($key))
			return $result;
		else if (property_exists($result, $key))
			return $result->$key;
		else
			return null;
	}
	
	/**
	 * Analyse admin page contents to check if update is finished.
	 * @param	$content	string			The admin page content.
	 */
	private function _isUpdated($content) {
		return strpos($content, '<meta http-equiv="refresh" content="0; ') !== FALSE;
	}

	/**
	 * Get http code from page content.
	 * @param	$content	string			The page content.
	 * @return				mixed				The http code, null if is not found.
	 */
	private function _getHttpCode($content) {
		// Looking for end of first line
		if (($end_frst_line = strpos($content, "\n")) === FALSE)
			return null;
		// Getting first line
		$frst_line = substr($content, 0, $end_frst_line);
		// Looking for http code
		if (!preg_match('#^HTTP/1\.1 ([0-9]{3}) #', $frst_line, $matches))
			return null;
		// Returning http code
		return $matches[1];
	}
}