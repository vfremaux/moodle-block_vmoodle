<?php
/**
 * This library provides functions and objects for the meta-administration.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
* Requires and includes
*/
include_once("{$CFG->dirroot}/blocks/vmoodle/bootlib.php");
include_once("{$CFG->dirroot}/blocks/vmoodle/filesystemlib.php");

/** Define constants */
define('VMOODLE_CLASSES_DIR', $CFG->dirroot.'/blocks/vmoodle/classes/');
define('VMOODLE_LIBS_DIR', $CFG->dirroot.'/blocks/vmoodle/plugins/libs/');
define('VMOODLE_PLUGINS_DIR', $CFG->dirroot.'/blocks/vmoodle/plugins/');

/**
 * Define MySQL and PostgreSQL paths for commands.
 */
// Windows.
if($CFG->ostype == 'WINDOWS'){
	$CFG->vmoodle_cmd_mysql			=	 '';
	$CFG->vmoodle_cmd_mysqldump		=	 '';
	$CFG->vmoodle_cmd_pgsql			=	 '';
	$CFG->vmoodle_cmd_pgsqldump		=	 '';
}
// Linux.
else {
	$CFG->vmoodle_cmd_mysql			=	 'mysql';
	$CFG->vmoodle_cmd_mysqldump		=	 'mysqldump';
	$CFG->vmoodle_cmd_pgsql			=	 'pgsql';
	$CFG->vmoodle_cmd_pgsqldump		=	 'pgsqldump';
}

/** Define commands' constants */
$vmcommands_constants = array(
	'prefix' => $CFG->prefix,
	'wwwroot' => $CFG->wwwroot,
);

// Loading plugin librairies
$plugin_libs = glob($CFG->dirroot.'/blocks/vmoodle/plugins/libs/*/lib.php');
foreach ($plugin_libs as $lib)
require_once $lib;

/**
 * Get available platforms to send Vmoodle_Command.
 * @return						array				The availables platforms based on MNET or Vmoodle table.
 */
function get_available_platforms() {
	global $CFG;

	// Getting description of master host
	$master_host = get_record('course', 'id', 1);

	// Setting available platforms
	$aplatforms = array();
	if ($CFG->block_vmoodle_host_source == 'vmoodle') {
		$id = 'vhostname';
		$records = get_records('block_vmoodle', null, null, 'name', $id.', name');
		if (!empty($CFG->vmoodledefault)){
			$records[] = (object) array($id => $CFG->wwwroot, 'name' => $master_host->fullname);
		}
	} else {
		$id = 'wwwroot';
		$records = get_records('mnet_host', 'deleted', 0, 'name', $id.', name');
		foreach ($records as $key => $record) {
			if ($record->name == '' || $record->name == 'All Hosts')
			unset($records[$key]);
		}
		$records[] = (object) array($id => $CFG->wwwroot, 'name' => $master_host->fullname);
	}
	if ($records){
		foreach($records as $record){
			$aplatforms[$record->$id] = $record->name;
		}
		asort($aplatforms);
	}

	return $aplatforms;
}

/**
 * Get string from lang file of a Vmoodle plugin's command.
 * @param	$identifier			string				The key identifier for the localized string.
 * @param	$category			string				The plugin's command category.
 * @param	$a					mixed				An object, string or number that can be used.
 * @return 						string 				The localized string.
 */
function get_string_vmp($identifier, $category, $a=null) {
	return get_string($identifier, $category, $a, array(VMOODLE_PLUGINS_DIR.$category.'/lang/', VMOODLE_PLUGINS_DIR.'_'.$category.'/lang/'));
}

/**
 * Get string from lang file of a Vmoodle library.
 * @param	$identifier			string				The key identifier for the localized string.
 * @param	$library			string				The library.
 * @param	$a					mixed				An object, string or number that can be used.
 * @return 						string 				The localized string.
 */
function get_string_vml($identifier, $library, $a=null) {
	return get_string($identifier, $library, $a, VMOODLE_LIBS_DIR.$library.'/lang/');
}

/**
 * Return html help icon from library help files.
 * @param	$library			string				The vmoodle library to display help file.
 * @param	$helpitem			string				The help item to display.
 * @param	$title				string				The title of help.
 * @return						string				Html span with help icon.
 */
function help_button_vml($library, $helpitem, $title) {
	return helpbutton('helprouter.html&amp;library='.$library.'&amp;helpitem='.$helpitem, get_string_vml($title, $library), 'block_vmoodle', true, false, '', true);
}

/**
 * Get the parameters' values from the placeholders.
 * @param	$matches			array				The placeholders found.
 * @param	$data				array				The parameters' values to insert.
 * @param	$parameters_replace	bool				True if variables should be replaced (optional).
 * @param	$contants_replace	bool				True if constants should be replaced (optional).
 * @return						string				The parameters' values.
 */
function replace_parameters_values($matches, $data, $parameters_replace=true, $constants_replace=true) {
	global $vmcommands_constants;

	// Parsing constants
	if ($constants_replace && empty($matches[1]) && array_key_exists($matches[2], $vmcommands_constants))
	$value = $vmcommands_constants[$matches[2]];
	// Parsing parameter
	else if ($parameters_replace && !empty($matches[1]) && array_key_exists($matches[2], $data))
	$value = $data[$matches[2]]->getValue();
	// Leave untouched
	else
	return $matches[0];

	// Checking if member is asked
	if (isset($matches[3]) && is_array($value))
	$value = $value[$matches[3]];

	return $value;
}

/**
 * Print the start of a collapsable block.
 * @param	$id					string				The id of the block.
 * @param	$caption			string				The caption of the block.
 * @param	$classes			string				The CSS classes of the block.
 * @param	$displayed			boolean				True if the block is displayed by default, false otherwise.
 */
function print_collapsable_bloc_start($id, $caption, $classes='', $displayed=true) {
	global $CFG;
	$caption = strip_tags($caption);

	echo '<div id="vmblock_'.$id.'" class="bvmc'.($displayed ? '' : ' hidden').'">' .
			'<div class="header">' .
				'<div class="title">' .
					'<input ' .
						'type="image" class="hide-show-image" ' .
						'onclick="elementToggleHide(this, true, function(el) {return findParentNode(el, \'DIV\', \'bvmc\'); }, \''.get_string('show').' '.$caption.'\', \''.get_string('hide').' '.$caption.'\'); return false;" ' .
						'src="'.$CFG->pixpath.'/t/switch_'.($displayed ? 'minus' : 'plus').'.gif" ' .
						'alt="'.get_string('show').' '.strip_tags($caption).'" ' .
						'title="'.get_string('show').' '.strip_tags($caption).'"/>' .
					'<h2>'.strip_tags($caption).'</h2>' .
				'</div>' .
			'</div>' .
			'<div class="content">';
}

/**
 * Print the end of a collapsable block.
 */
function print_collapsable_block_end() {
	echo '</div></div>';
}

/**
 * Load a vmoodle plugin and cache it.
 * @param	$plugin_name		string						The plugin name.
 * @return						Vmoodle_Command_Category	The category plugin.
 */
function load_vmplugin($plugin_name) {
	global $CFG;
	static $plugins = array();

	if (!array_key_exists($plugin_name, $plugins))
	$plugins[$plugin_name] = include_once($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin_name.'/config.php');
	return $plugins[$plugin_name];
}

/**
 * Get available templates for defining a new virtual host.
 * @return		array		The availables templates, or EMPTY array.
 */
function vmoodle_get_available_templates() {
	global $CFG;

	// Scans the templates.
	if(!filesystem_file_exists('vmoodle', $CFG->dataroot)){
		mkdir($CFG->dataroot.DIRECTORY_SEPARATOR.'vmoodle');
	}
	$dirs = filesystem_scan_dir('vmoodle', FS_IGNORE_HIDDEN, FS_ONLY_DIRS, $CFG->dataroot);
	$vtemplates = preg_grep("/^(.*)_vmoodledata$/", $dirs);

	// Retrieves template(s) name(s).
	$templatesarray = array();
	if ($vtemplates){
		foreach($vtemplates as $vtemplatedir){
			preg_match("/^(.*)_vmoodledata/", $vtemplatedir, $matches);
			$templatesarray[$matches[1]] = $matches[1];
			if (!isset($first)) $first = $matches[1];
		}
	}

	$templatesarray[] = get_string('reactivetemplate', 'block_vmoodle');

	return $templatesarray;
}

/**
 * Make a fake vmoodle that represents the current host database configuration.
 * @uses		$CFG		The global configuration.
 * @return		object		The current host's database configuration.
 */
function vmoodle_make_this(){
	global $CFG;

	$thismoodle = new stdclass;
	$thismoodle->vdbtype	= $CFG->dbtype;
	$thismoodle->vdbhost 	= $CFG->dbhost;
	$thismoodle->vdblogin	= $CFG->dbuser;
	$thismoodle->vdbpass	= $CFG->dbpass;
	$thismoodle->vdbname	= $CFG->dbname;
	$thismoodle->vdbpersist	= $CFG->dbpersist;
	$thismoodle->vdbprefix	= $CFG->prefix;

	return $thismoodle;
}

/**
 * Executes a query on a Vmoodle database. Query must return no results,
 * so it may be an INSERT or an UPDATE or a DELETE.
 * @param		$vmoodle	object		The Vmoodle object.
 * @param		$sql		string		The SQL request.
 * @param		$cnx		handle		The connection to the Vmoodle database.
 * @return		boolean		TRUE if the request is well-executed, FALSE otherwise.
 */
function vmoodle_execute_query(&$vmoodle, $sql, $cnx){

	// If database is MySQL typed.
	if($vmoodle->vdbtype == 'mysql'){
		if (!($res = mysql_query($sql, $cnx))){
			echo "vmoodle_execute_query() : ".mysql_error($cnx)."<br/>";
			return false;
		}
		if ($newid = mysql_insert_id($cnx)){
			$res = $newid; // get the last insert id in case of an INSERT
		}
	}

	// If database is PostgresSQL typed.
	elseif ($vmoodle->vdbtype == 'postgres') {
		if (!($res = pg_query($cnx, $sql))){
			echo "vmoodle_execute_query() : ".pg_last_error($cnx)."<br/>";
			return false;
		}
		if ($newid = pg_last_oid($res)){
			$res = $newid; // get the last insert id in case of an INSERT
		}
	}

	// If database not supported.
	else {
		echo "vmoodle_execute_query() : Database not supported<br/>" ;
		return false;
	}

	return $res;
}

/**
 * Closes a connection to a Vmoodle database.
 * @param		$vmoodle	object		The Vmoodle object.
 * @param		$cnx		handle		The connection to the database.
 * @return		boolean		If true, closing the connection is well-executed.
 */
function vmoodle_close_connection($vmoodle, $cnx){
	if($vmoodle->vdbtype == 'mysql'){
		$res = mysql_close($cnx);
	}
	elseif($vmoodle->vdbtype == 'postgres') {
		$res = pg_close($cnx);
	}
	else {
		echo "vmoodle_close_connection() : Database not supported<br/>";
		$res = false;
	}
	return $res;
}

/**
 * Dumps a SQL database for having a snapshot.
 * @param		$vmoodle	object		The Vmoodle object.
 * @param		$outputfile	string		The output SQL file.
 * @return		bool	If TRUE, dumping database was a success, otherwise FALSE.
 */
function vmoodle_dump_database($vmoodle, $outputfile){
	global $CFG;

	// Separating host and port, if sticked.
	if (strstr($vmoodle->vdbhost, ':') !== false){
		list($host, $port) = split(':', $vmoodle->vdbhost);
	} else {
		$host = $vmoodle->vdbhost;
	}

	// By default, empty password.
	$pass = '';
	if ($vmoodle->vdbtype == 'mysql'){ // MysQL.
		// Default port.
		if (empty($port)){
			$port = 3306;
		}

		// Password.
		if (!empty($vmoodle->vdbpass)){
			$pass = "-p".escapeshellarg($vmoodle->vdbpass);
		}

		// Making the command.
		if ($CFG->ostype == 'WINDOWS'){
    		$cmd = "-h{$host} -P{$port} -u{$vmoodle->vdblogin} {$pass} {$vmoodle->vdbname}";
    		$cmd .= " > " . $outputfile;
    	} else {
    		$cmd = "-h{$host} -P{$port} -u{$vmoodle->vdblogin} {$pass} {$vmoodle->vdbname}";
    		$cmd .= " > " . escapeshellarg($outputfile);
    	}

		// MySQL application (see 'vconfig.php').
		$pgm = (!empty($CFG->block_vmoodle_cmd_mysqldump)) ? stripslashes($CFG->block_vmoodle_cmd_mysqldump) : false;
	}
	else if ($vmoodle->vdbtype == 'postgres'){ // PostgreSQL.
		// Default port.
		if (empty($port)){
			$port = 5432;
		}

		// Password.
		if (!empty($vmoodle->vdbpass)){
			$pass = $vmoodle->vdbpass;
		}

		// Making the command, (if needed, a password prompt will be displayed).
		if ($CFG->ostype == 'WINDOWS'){
    		$cmd = " -d -b -Fc -h {$host} -p {$port} -U {$vmoodle->vdblogin} {$vmoodle->vdbname}";
    		$cmd .= " > " . $outputfile;
    	} else {
    		$cmd = " -d -b -Fc -h {$host} -p {$port} -U {$vmoodle->vdblogin} {$vmoodle->vdbname}";
    		$cmd .= " > " . escapeshellarg($outputfile);
    	}

		// PostgreSQL application (see 'vconfig.php').
		$pgm = (!empty($CFG->block_vmoodle_cmd_pgsqldump)) ? $CFG->vmoodle_cmd_pgsqldump : false ;
	}

	if(!$pgm){
	    error("Database dump command not available");
		return false;
	} else {
	    
        $phppgm = str_replace("\\", '/', $pgm);
        $phppgm = str_replace("\"", '', $phppgm);
        $pgm = str_replace('/', DIRECTORY_SEPARATOR, $pgm);

	    if (!is_executable($phppgm)){
    	    error("Database dump command $phppgm does not match any executable");
    		return false;
	    }
	    
		// Final command.
		$cmd = $pgm.' '.$cmd;

		// Prints log messages in the page and in 'cmd.log'.
		if ($LOG = fopen(dirname($outputfile).'/cmd.log', 'a')){
			fwrite($LOG, $cmd."\n");
		}

		// Executes the SQL command.
		exec($cmd, $execoutput, $returnvalue);
		if ($LOG){
			foreach($execoutput as $execline) fwrite($LOG, $execline."\n");
			fwrite($LOG, $returnvalue."\n");
			fclose($LOG);
		}
	}

	// End with success.
	return true;
}

/**
 * Loads a complete database dump from a template, and does some update.
 * @uses	$CFG		    The global configuration.
 * @param	$vmoodledata	object		All the Host_form data.
 * @param	$outputfile		array		The variables to inject in setup template SQL.
 * @return	bool	If TRUE, loading database from template was sucessful, otherwise FALSE.
 */
function vmoodle_load_database_from_template($vmoodledata) {
	global $CFG;

	// Gets the HTTP adress scheme (http, https, etc...) if not specified.
	if(is_null(parse_url($vmoodledata->vhostname, PHP_URL_SCHEME))){
		$vmoodledata->vhostname =	parse_url($CFG->wwwroot, PHP_URL_SCHEME).'://'.$vmoodledata->vhostname;
	}

	$manifest =	vmoodle_get_vmanifest($vmoodledata->vtemplate);
	$hostname = mnet_get_hostname_from_uri($CFG->wwwroot);
	$description = get_field('course', 'fullname', 'id', SITEID); 
	$cfgipaddress = gethostbyname($hostname);

    // availability of SQL commands

	// Checks if paths commands have been properly defined in 'vconfig.php'.
	if($vmoodledata->vdbtype == 'mysql') {
		$createstatement = 'CREATE DATABASE'; 
	}
	else if($vmoodledata->vdbtype == 'postgres') {
		$createstatement = 'CREATE SCHEMA'; 
	}

	// SQL files paths.
	$separator	=	DIRECTORY_SEPARATOR;
	$osDataroot = str_replace('/', DIRECTORY_SEPARATOR, $CFG->dataroot);
	$templatesqlfile_path	= $osDataroot.$separator.'vmoodle'.$separator.$vmoodledata->vtemplate.'_sql'.$separator.'vmoodle_master.sql';
	// Create temporaries files for replacing data.
	$temporarysqlfile_path	= $osDataroot.$separator.'vmoodle'.$separator.$vmoodledata->vtemplate.'_sql'.$separator.'vmoodle_master.temp.sql';

	// Retrieves files contents into strings.
    debug_trace("load_database_from_dump : getting sql content");
	if(!($dumptxt = file_get_contents($templatesqlfile_path))){
	    error("No sql");
	    return false;
    }

	// Change the tables prefix if requjired prefix does not match manifest's one (sql template).
	if ($manifest['templatevdbprefix'] != $vmoodledata->vdbprefix){
		$dumptxt = str_replace($manifest['templatevdbprefix'], $vmoodledata->vdbprefix, $dumptxt);
	}
	
	// fix special case on adodb_logsql table if prefix has a schema part (PostgreSQL)
	if (preg_match('/(.*)\./', $vmoodledata->vdbprefix, $matches)){
	    // we have schema, thus relocate adodb_logsql table within schema
		$dumptxt = str_replace('adodb_logsql', $matches[1].'.adodb_logsql', $dumptxt);		    
	}

	// Puts strings into the temporary files.
    debug_trace("load_database_from_dump : writing modified sql");
	if(!file_put_contents($temporarysqlfile_path, $dumptxt)){
	    error("No output for transformed sql");
	    return false;
	}

	// Creates the new database before importing the data.
	$sql = "$createstatement $vmoodledata->vdbname";
    debug_trace("load_database_from_dump : executing creation sql");
	if(!execute_sql($sql, false)){
	    error("No execution for ".$sql);
		return false;
	}

    $sqlcmd = vmoodle_get_database_dump_cmd($vmoodledata);

	// Make final commands to execute, depending on the database type.
	$import	= $sqlcmd.$temporarysqlfile_path;

	// Execute the command.
    debug_trace("load_database_from_dump : executing feeding sql");

	exec($import, $output, $return);

    debug_trace(implode("\n", $output)."\n");

	// Remove temporary files.
	//	if(!unlink($temporarysqlfile_path))){
	//		return false;
	//	}

	// End.
    debug_trace("load_database_from_dump : OUT");
	return true;
}

/**
 * Loads a complete database dump from a template, and does some update.
 * @uses	$CFG		The global configuration.
 * @param	$vmoodledata	object		All the Host_form data.
 * @param	$this_as_host	object		The mnet_host record that represents the master.
 * @return	bool	If TRUE, fixing database from template was sucessful, otherwise FALSE.
 */
function vmoodle_fix_database($vmoodledata, $this_as_host) {
	global $CFG, $SITE;

    debug_trace('fixing_database ; IN');
	$manifest = vmoodle_get_vmanifest($vmoodledata->vtemplate);
	$hostname	=	mnet_get_hostname_from_uri($CFG->wwwroot);
	$cfgipaddress = gethostbyname($hostname);

	// SQL files paths.
	$separator = DIRECTORY_SEPARATOR;
	$osDataroot = str_replace('/', DIRECTORY_SEPARATOR, $CFG->dataroot);
	$temporarysetup_path = $osDataroot.$separator.'vmoodle'.$separator.$vmoodledata->vtemplate.'_sql'.$separator.'vmoodle_setup_template.temp.sql';

	$separator = DIRECTORY_SEPARATOR;

    debug_trace('fixing_database ; opening setup script file');
	if (!$FILE = fopen($temporarysetup_path, 'w')){
	    error('Could not write the setup script');
	    return false;
	}
	
	
	$PREFIX = $vmoodledata->vdbprefix;
	
	// setup moodle name and description
	fwrite($FILE, "UPDATE {$PREFIX}course SET fullname='{$vmoodledata->name}', shortname='{$vmoodledata->shortname}', summary='{$vmoodledata->description}' WHERE category = 0 AND id = 1;\n");

	// setup a suitable cookie name
	$cookiename = clean_param($vmoodledata->shortname, PARAM_ALPHANUM);
	fwrite($FILE, "UPDATE {$PREFIX}config SET value='{$cookiename}' WHERE name = 'sessioncookie';\n\n");

    // delete all logs
	fwrite($FILE, "DELETE FROM {$PREFIX}log;\n\n");
	fwrite($FILE, "DELETE FROM {$PREFIX}mnet_log;\n\n");

	/** we need :
	* clean host to service
	* clean mnet_hosts unless All Hosts and self record
	* rebind self record to new wwwroot, ip and cleaning public key
	*/
	fwrite($FILE, "--\n-- Cleans all mnet tables but keeping service configuration in place \n--\n");
	fwrite($FILE, "DELETE FROM {$PREFIX}mnet_host2service;\n\n");
	fwrite($FILE, "DELETE FROM {$PREFIX}mnet_host WHERE wwwroot != '' AND wwwroot != '{$manifest['templatewwwroot']}';\n\n");
    fwrite($FILE, "UPDATE {$PREFIX}mnet_host SET id = 1, wwwroot = '{$vmoodledata->vhostname}', name = '{$vmoodledata->name}' , public_key = '', public_key_expires = 0, ip_address = '{$cfgipaddress}'  WHERE wwwroot = '{$manifest['templatewwwroot']}';\n\n");
    fwrite($FILE, "UPDATE {$PREFIX}config SET value = 1 WHERE name = 'mnet_localhost_id';\n\n"); // ensure consistance
    fwrite($FILE, "UPDATE {$PREFIX}user SET deleted = 1 WHERE auth = 'mnet' AND username != 'admin';\n\n"); // disable all mnet users
    fwrite($FILE, "DELETE FROM {$PREFIX}mnet_log;\n\n"); // purge mnet logs and sessions
    fwrite($FILE, "DELETE FROM {$PREFIX}mnet_session;\n\n"); // purge mnet logs and sessions

    if ($vmoodledata->mnet == -1){ // NO MNET AT ALL
        /** we need :
    	* disable mnet
    	*/
        fwrite($FILE, "UPDATE {$PREFIX}config SET value = 'off' WHERE name = 'mnet_dispatcher_mode';\n\n");
    } else { // ALL OTHER CASES
    	/** we need : 
    	* enable mnet
    	* push our master identity in mnet_host table
    	*/
        fwrite($FILE, "UPDATE {$PREFIX}config SET value = 'strict' WHERE name = 'mnet_dispatcher_mode';\n\n");
	    fwrite($FILE, "INSERT INTO {$PREFIX}mnet_host (wwwroot, ip_address, name, public_key, applicationid, public_key_expires) VALUES ('{$this_as_host->wwwroot}', '{$this_as_host->ip_address}', '{$SITE->fullname}', '{$this_as_host->public_key}', {$this_as_host->applicationid}, '{$this_as_host->public_key_expires}');\n\n");

    	fwrite($FILE, "--\n-- Enable the service 'mnet_admin, sso_sp and sso_ip' with host which creates this host.  \n--\n");
    	fwrite($FILE, "INSERT INTO {$PREFIX}mnet_host2service VALUES (null, (SELECT id FROM {$PREFIX}mnet_host WHERE wwwroot LIKE '{$this_as_host->wwwroot}'), (SELECT id FROM {$PREFIX}mnet_service WHERE name LIKE 'mnet_admin'), 1, 0);\n\n");
    	fwrite($FILE, "INSERT INTO {$PREFIX}mnet_host2service VALUES (null, (SELECT id FROM {$PREFIX}mnet_host WHERE wwwroot LIKE '{$this_as_host->wwwroot}'), (SELECT id FROM {$PREFIX}mnet_service WHERE name LIKE 'sso_sp'), 1, 0);\n\n");
    	fwrite($FILE, "INSERT INTO {$PREFIX}mnet_host2service VALUES (null, (SELECT id FROM {$PREFIX}mnet_host WHERE wwwroot LIKE '{$this_as_host->wwwroot}'), (SELECT id FROM {$PREFIX}mnet_service WHERE name LIKE 'sso_idp'), 0, 1);\n\n");

    	fwrite($FILE, "--\n-- Insert master host user admin.  \n--\n");
    	fwrite($FILE, "INSERT INTO {$PREFIX}user (auth, confirmed, policyagreed, deleted, mnethostid, username, password) VALUES ('mnet', 1, 0, 0, (SELECT id FROM {$PREFIX}mnet_host WHERE wwwroot LIKE '{$this_as_host->wwwroot}'), 'admin', '');\n\n");

    	fwrite($FILE, "--\n-- Links role and capabilites for master host admin.  \n--\n");
	    fwrite($FILE, "INSERT INTO {$PREFIX}role_assignments VALUES (0, (SELECT id FROM {$PREFIX}role WHERE shortname LIKE 'admin'), 1, (SELECT id FROM {$PREFIX}user WHERE auth LIKE 'mnet' AND username LIKE 'admin' AND mnethostid = (SELECT id FROM {$PREFIX}mnet_host WHERE wwwroot LIKE '{$this_as_host->wwwroot}')), 0, 0, 0, 0, 0, 'manual', 0);\n\n");

    	fwrite($FILE, "--\n-- Create a disposable key for renewing new host's keys.  \n--\n");
    	fwrite($FILE, "INSERT INTO {$PREFIX}config (name, value) VALUES ('bootstrap_init', '{$this_as_host->wwwroot}');\n");
    }
    fclose($FILE);
    debug_trace('fixing_database ; setup script written');

    $sqlcmd = vmoodle_get_database_dump_cmd($vmoodledata);

	// Make final commands to execute, depending on the database type.
	$import	= $sqlcmd.$temporarysetup_path;

	// Prints log messages in the page and in 'cmd.log'.
    debug_trace("fixing_database ; executing $import");

	// Execute the command.
	exec($import, $output, $return);

    debug_trace(implode("\n", $output)."\n");

	// Remove temporary files.
	//	if(!unlink($temporarysetup_path)){
	//		return false;
	//	}

	// End.
	return true;
}

/**
* get the service strategy and peer mirror strategy to apply to new host, depending on 
* settings. If no settings were made, use a simple peer to peer SSO binding so that users
* can just roam.
* @param object $vmoodledata the new host definition
* @param array reference $services the service scheme to apply to new host
* @param array reference $peerservices the service scheme to apply to new host peers
*/
function vmoodle_get_service_strategy($vmoodledata, &$services, &$peerservices){

	// We will mix in order to an single array of configurated service here.
	$servicesstrategy = unserialize(get_config(null, 'block_vmoodle_services_strategy'));
	
	$servicerecs = get_records('mnet_service', '', '');

	if(!empty($servicerecs)){
	    if ($vmoodledata->services == 'subnetwork'  && !empty($servicesstrategy)){
    		foreach($servicerecs as $key => $service){
    			if(array_key_exists($service->name, $servicesstrategy)){
    				$services[$service->name]->publish       = $servicesstrategy[$service->name]->publish;
    				$peerservices[$service->name]->subscribe = $services[$service->name]->publish;
    				$services[$service->name]->subscribe     = $servicesstrategy[$service->name]->subscribe;
    				$peerservices[$service->name]->publish   = $services[$service->name]->subscribe;
    			} else {
    				$services[$service->name]->publish       = 0;
    				$services[$service->name]->subscribe     = 0;
    				$peerservices[$service->name]->publish   = 0;
    				$peerservices[$service->name]->subscribe = 0;
    			}
    		}
    	} else { // if no strategy has been recorded, use default SSO binding
    		$services['sso_sp']->publish	= 1;
    		$services['sso_sp']->subscribe	= 1;
    		$services['sso_idp']->publish	= 1;
    		$services['sso_idp']->subscribe	= 1;
    		$peerservices['sso_sp']->publish	= 1;
    		$peerservices['sso_sp']->subscribe	= 1;
    		$peerservices['sso_idp']->publish	= 1;
    		$peerservices['sso_idp']->subscribe	= 1;
    	}
	}
}

/**
* get a proper SQLdump command
* @param object $vmoodledata the complete new host information
* @return string the shell command 
*/
function vmoodle_get_database_dump_cmd($vmoodledata){
    global $CFG;

	// Checks if paths commands have been properly defined in 'vconfig.php'.
	if($vmoodledata->vdbtype == 'mysql') {
		$pgm = (!empty($CFG->block_vmoodle_cmd_mysql)) ? stripslashes($CFG->block_vmoodle_cmd_mysql) : false;
	}
	else if($vmoodledata->vdbtype == 'postgres') {
	    // needs to point the pg_restore command
		$pgm = (!empty($CFG->block_vmoodle_cmd_pgsql)) ? stripslashes($CFG->block_vmoodle_cmd_pgsql) : false;
	}

	// Checks the needed program.
    debug_trace("load_database_from_dump : checking database command");
	if(!$pgm){
	    error("Database command not configured");
		return false;
	}

    $phppgm = str_replace("\\", '/', $pgm);
    $phppgm = str_replace("\"", '', $phppgm);
    $pgm = str_replace("/", DIRECTORY_SEPARATOR, $pgm);

    debug_trace("load_database_from_dump : checking command is available");
	if(!is_executable($phppgm)){
	    error("Database command $phppgm does not match an executable file");
		return false;
	}

	// Retrieves the host configuration (more secure).
	$thisvmoodle = vmoodle_make_this();
	if (strstr($thisvmoodle->vdbhost, ':') !== false){
		list($thisvmoodle->vdbhost, $thisvmoodle->vdbport) = split(':', $thisvmoodle->vdbhost);
	}

	// Password.
	if (!empty($thisvmoodle->vdbpass)){
		$thisvmoodle->vdbpass = '-p'.escapeshellarg($thisvmoodle->vdbpass).' ';
	}

	// Making the command line (see 'vconfig.php' file for defining the right paths).
	if($vmoodledata->vdbtype == 'mysql') {
		$sqlcmd	= $pgm.' -h'.$thisvmoodle->vdbhost.(isset($thisvmoodle->vdbport) ? ' -P'.$thisvmoodle->vdbport.' ' : ' ' );
		$sqlcmd .= '-u'.$thisvmoodle->vdblogin.' '.$thisvmoodle->vdbpass;
		$sqlcmd .= $vmoodledata->vdbname.' < ';
	}
	else if($vmoodledata->vdbtype == 'postgres') {
		$sqlcmd	= $pgm.' -Fc -h '.$thisvmoodle->vdbhost.(isset($thisvmoodle->vdbport) ? ' -p '.$thisvmoodle->vdbport.' ' : ' ' );
		$sqlcmd .= '-U '.$thisvmoodle->vdblogin.' ';
		$sqlcmd .= '-d '.$vmoodledata->vdbname.' -f ';
	}
	
	return $sqlcmd;
}

/**
 * Dump existing files of a template.
 * @uses		$CFG		The global configuration.
 * @param		$templatename		string		The template's name.
 * @param		$destpath			string		The destination path.
 */
function vmoodle_dump_files_from_template($templatename, $destpath) {
	global $CFG;

	// Copies files and protects against copy recursion.
	$templatefilespath	= $CFG->dataroot.'/vmoodle/'.$templatename.'_vmoodledata';
	$destpath			= str_replace('\\\\', '\\', $destpath);
	if (!is_dir($destpath)){
		mkdir($destpath);
	}
	filesystem_copy_tree($templatefilespath, $destpath, '');
}

/**
*
*
*/
function vmoodle_bind_to_network($submitteddata, &$newmnet_host){
    global $USER, $CFG;

    debug_trace("step 4.4 : binding to subnetwork");

	// Getting services schemes to apply
    debug_trace("step 4.4.1 : getting services");
	vmoodle_get_service_strategy($submitteddata, $services, $peerservices);

    debug_trace("step 4.4.2 : getting possible peers");
    $idnewblock = get_field('block_vmoodle', 'id', 'vhostname', $submitteddata->vhostname);

	// Retrieves the subnetwork member(s).
	$subnetwork_hosts	= array();
	$select = 'id != '.$idnewblock.' AND mnet = '.$submitteddata->mnet.' AND enabled = 1';
	$subnetwork_members = get_records_select('block_vmoodle', $select);

	if(!empty($subnetwork_members)){
        debug_trace("step 4.4.3 : preparing peers");
		foreach($subnetwork_members as $subnetwork_member){
			$temp_host	        = new stdClass();
			$temp_host->wwwroot	= $subnetwork_member->vhostname;
			$temp_host->name	= utf8_decode($subnetwork_member->name);
			$subnetwork_hosts[]	= $temp_host;
		}
	}

	// Member(s) of the subnetwork add the new host.
	if (!empty($subnetwork_hosts)){
        debug_trace("step 4.4.4 : bind peers");
		$rpc_client = new Vmoodle_XmlRpc_Client();
		$rpc_client->reset_method();
		$rpc_client->set_method('blocks/vmoodle/rpclib.php/mnetadmin_rpc_bind_peer');
        // authentication params
		$rpc_client->add_param($USER->username, 'string');
		$userhostroot = get_field('mnet_host', 'wwwroot', 'id', $USER->mnethostid);
		$rpc_client->add_param($userhostroot, 'string');
		$rpc_client->add_param($CFG->wwwroot, 'string');        			
		// peer to bind to
		$rpc_client->add_param((array)$newmnet_host, 'array');
		$rpc_client->add_param($peerservices, 'array');

		foreach($subnetwork_hosts as $subnetwork_host){
            debug_trace("step 4.4.4.1 : bind to -> $subnetwork_host->wwwroot");
			$temp_member = new vmoodle_mnet_peer();
			$temp_member->set_wwwroot($subnetwork_host->wwwroot);
			if (!$rpc_client->send($temp_member)){
                notify(implode('<br />', $rpc_client->getErrors($temp_member)));
                if (debugging()){
                    echo '<pre>';
                    var_dump($rpc_client);
                    echo '</pre>';
                }
            }

            debug_trace("step 4.4.4.1 : bind from <- $subnetwork_host->wwwroot");
			$rpc_client_2 = new Vmoodle_XmlRpc_Client();
			$rpc_client_2->reset_method();
			$rpc_client_2->set_method('blocks/vmoodle/rpclib.php/mnetadmin_rpc_bind_peer');
            // authentication params
			$rpc_client_2->add_param($USER->username, 'string');
			$userhostroot = get_field('mnet_host', 'wwwroot', 'id', $USER->mnethostid);
			$rpc_client_2->add_param($userhostroot, 'string');
			$rpc_client_2->add_param($CFG->wwwroot, 'string');
			// peer to bind to        			
			$rpc_client_2->add_param((array)$temp_member, 'array');
		    $rpc_client_2->add_param($services, 'array');

			if (!$rpc_client_2->send($newmnet_host)){
                notify(implode('<br />', $rpc_client_2->getErrors($newmnet_host)));
                if (debugging()){
                    echo '<pre>';
                    var_dump($rpc_client_2);
                    echo '</pre>';
                }
            }
            unset($rpc_client_2); // free some resource
		}
	}
}

/**
 * Checks existence and consistency of a full template.
 * @uses $CFG
 * @param string $templatename The template's name.
 * @return bool Returns TRUE if the full template is consistency, FALSE otherwise.
 */
function vmoodle_exist_template($templatename) {
	global $CFG;

	// Needed paths for checking.
	$templatedir_files	= $CFG->dataroot.'/vmoodle/'.$templatename.'_vmoodledata';
	$templatedir_sql	= $CFG->dataroot.'/vmoodle/'.$templatename.'_sql';

	return (in_array($templatename, vmoodle_get_available_templates())
	&& is_readable($templatedir_files)
	&& is_readable($templatedir_sql));
}

/**
 * Read manifest values in vmoodle template.
 */

/**
 * Gets value in manifest file (in SQL folder of a template).
 * @uses		$CFG		The global configuration.
 * @param		$templatename		string		The template's name.
 * @return		array		The manifest values.
 */
function vmoodle_get_vmanifest($templatename){
	global $CFG;

	// Reads php values.
	include($CFG->dataroot.'/vmoodle/'.$templatename.'_sql/manifest.php');
	$manifest	=	array();
	$manifest['templatewwwroot']	=	$templatewwwroot;
	$manifest['templatevdbprefix']	=	$templatevdbprefix;

	return $manifest;
}

/**
 * Searches and returns the last created subnetwork number.
 * @return		integer	The last created subnetwork number.
 */
function vmoodle_get_last_subnetwork_number(){
	$nbmaxsubnetwork	=	get_record_select('block_vmoodle', '', sql_max('mnet').' AS mnet');
	return $nbmaxsubnetwork->mnet;
}

// **************************************************************************************
// *                                    TO CHECK                                        *
// **************************************************************************************


/**
 * Be careful : this library might be include BEFORE any configuration
 * or other usual Moodle libs are loaded. It cannot rely on
 * most of the Moodle API functions.
 */

/**
 * Prints an administrative status (broken, enabled, disabled) for a Vmoodle.
 *
 * @uses		$CFG		The global configuration.
 * @param		$vmoodle	object			The Vmoodle object.
 * @param		$return		boolean			If false, prints the Vmoodle state, else not.
 * @return		string		The Vmoodle state.
 */
function vmoodle_print_status($vmoodle, $return = false) {
	global $CFG;

	if (!vmoodle_check_installed($vmoodle)) {
		$vmoodlestate = "<img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/broken.gif\"/>";
	} elseif($vmoodle->enabled) {
		$vmoodlestate = "<a href=\"{$CFG->wwwroot}/blocks/vmoodle/view.php?view=management&amp;what=disable&amp;id={$vmoodle->id}\"><img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/enabled.gif\" title=\"".get_string('disable').'" /></a>';
	} else {
		$vmoodlestate = "<a href=\"{$CFG->wwwroot}/blocks/vmoodle/view.php?view=management&amp;what=enable&amp;id={$vmoodle->id}\"><img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/disabled.gif\" title=\"".get_string('enable').'"/>';
	}

	// Prints the Vmoodle state.
	if (!$return) echo $vmoodlestate;

	return $vmoodlestate;
}

/**
 * Checks physical availability of the Vmoodle.
 * @param		$vmoodle	object		The Vmoodle object.
 * @return		boolean		If true, the Vmoodle is physically available.
 */
function vmoodle_check_installed($vmoodle) {
	return (filesystem_is_dir($vmoodle->vdatapath, ''));
}

/**
 * Adds an CSS marker error in case of matching error.
 * @param		$errors		array		The current error set.
 * @param		$errorkey	string		The error key.
 */
if (!function_exists('print_error_class')){
	function print_error_class($errors, $errorkeylist){
		if ($errors){
			foreach($errors as $anError){
				if ($anError->on == '') continue;
				if (preg_match("/\\b{$anError->on}\\b/" ,$errorkeylist)){
					echo " class=\"formerror\" ";
					return;
				}
			}
		}
	}
}

function vmoodle_load_plugins_strings(&$string){
	global $CFG;
	
	$lang = current_language();
	$plugin_langs = glob($CFG->dirroot."/blocks/vmoodle/plugins/libs/*/lang/{$lang}/*.php");
	foreach ($plugin_langs as $lang_complement){
		require_once $lang_complement;
	}
}