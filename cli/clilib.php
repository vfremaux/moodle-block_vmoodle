<?php

/*
// TODO : integrate some of LDAP/CAS configuration field overrides in
(part of LDAP implementation)
auth/ldap	host_url
auth/ldap	contexts	 
auth/ldap	user_type
auth/ldap	user_attribute	 
auth/ldap	search_sub
auth/ldap	opt_deref
auth/ldap	preventpassindb
auth/ldap	bind_dn
auth/ldap	bind_pw
auth/ldap	objectclass 
auth/ldap	memberattribute	 
auth/ldap	memberattribute_isdn	 
auth/ldap	creators
auth/ldap	create_context
auth/ldap	auth_user_create
auth/ldap	changepasswordurl
auth/ldap	removeuser

(part of CAS implementation)
*/

/**
* Opens and parses/checks a VMoodle instance definition file
* @param string $location 
*
*/
function vmoodle_parse_csv_nodelist($nodelistlocation = ''){
	global $CFG;
	
	$vnodes = array();
	
	if (empty($nodelistlocation)){
		$nodelistlocation = $CFG->dataroot.'/vmoodle/nodelist.csv';
	}
	
	// decode file
	$csv_encode = '/\&\#44/';
	if (isset($CFG->block_vmoodle_csvseparator)) {
		$csv_delimiter = '\\' . $CFG->block_vmoodle_csvseparator;
		$csv_delimiter2 = $CFG->block_vmoodle_csvseparator;

		if (isset($CFG->CSV_ENCODE)) {
			$csv_encode = '/\&\#' . $CFG->CSV_ENCODE . '/';
		}
	} else {
		$csv_delimiter = "\;";
		$csv_delimiter2 = ";";
	}
		
	//*NT* File that is used is currently hardcoded here!
	// Large files are likely to take their time and memory. Let PHP know
	// that we'll take longer, and that the process should be recycled soon
	// to free up memory.
	@set_time_limit(0);
	@raise_memory_limit("256M");
	if (function_exists('apache_child_terminate')) {
		@apache_child_terminate();
	}

	// make arrays of valid fields for error checking
	$required = array('vhostname' => 1,
			'name' => 1,
			'shortname' => 1,
			'vdatapath' => 1,
			'vdbname' => 1,
			'vdblogin' => 1,
			'vdbpass' => 1,
			);

	$optional = array(
			'description' => 1,
			'vdbhost' => 1,
			'vdbpersist' => 1,
			'vtemplate' => 1,
			'services' => 1,
			'mnet' => 1);

	$optionalDefaults = array(
			'mnet' => 1, 
			'vdbtype' => 'mysqli', 
			'vdbhost' => $CFG->dbhost,
			'vdbpersist' => $CFG->dboptions['dbpersist'],
			'vdbprefix' => 'mdl_',
			'vtemplate' => '',
			'services' => 'default');

	$patterns = array();
	$metas = array();

	// --- get header (field names) ---

	$textlib = new textlib();

	if (!$fp = fopen($nodelistlocation, 'rb')){
		cli_error(get_string('badnodefile', 'block_vmoodle', $nodelistlocation));
	}

	// jump any empty or comment line
	$text = fgets($fp, 1024);
	$i = 0;
	while(vmoodle_is_empty_line_or_format($text, $i == 0)){
		$text = fgets($fp, 1024);
		$i++;
	}
	
	$headers = explode($csv_delimiter2, $text);

	// check for valid field names
	foreach ($headers as $h) {
		$header[] = trim($h); 
		$patternized = implode('|', $patterns) . "\\d+";
		$metapattern = implode('|', $metas);
		if (!(isset($required[$h]) or isset($optionalDefaults[$h]) or isset($optional[$h]) or preg_match("/$patternized/", $h) or preg_match("/$metapattern/", $h))) {
			cli_error(get_string('invalidfieldname', 'error', $h));
			return;
		}

		if (isset($required[trim($h)])) {
			$required[trim($h)] = 0;
		}
	}

	// check for required fields
	foreach ($required as $key => $value) {
		if ($value) { //required field missing
			cli_error(get_string('fieldrequired', 'error', $key));
			return;
		}
	}
	$linenum = 2; // since header is line 1

	// take some from admin profile, other fixed by hardcoded defaults
	while (!feof ($fp)) {

		// make a new base record
		$vnode = new StdClass;
		foreach ($optionalDefaults as $key => $value) {
			$vnode->$key = $value;
		}

		//Note: commas within a field should be encoded as &#44 (for comma separated csv files)
		//Note: semicolon within a field should be encoded as &#59 (for semicolon separated csv files)
		$text = fgets($fp, 1024);
		if (vmoodle_is_empty_line_or_format($text, false)) {
			$i++;
			continue;
		}

		$valueset = explode($csv_delimiter2, $text);
		$f = 0;
		foreach ($valueset as $value) {
			//decode encoded commas
			$key = $headers[$f];
			$vnode->$key = preg_replace($csv_encode, $csv_delimiter2, trim($value));
			$f++;
		}
		$vnodes[] = $vnode;
	}
	
	return $vnodes;
}

/**
* Check a CSV input line format for empty or commented lines
* Ensures compatbility to UTF-8 BOM or unBOM formats
*/
function vmoodle_is_empty_line_or_format(&$text, $resetfirst = false){
	global $CFG;
	
	static $textlib;
	static $first = true;
		
	// we may have a risk the BOM is present on first line
	if ($resetfirst) $first = true;	
	if (!isset($textlib)) $textlib = new textlib(); // singleton
	if ($first && $CFG->block_vmoodle_encoding == 'UTF-8'){
		$text = $textlib->trim_utf8_bom($text);
		$first = false;
	}
	
	$text = preg_replace("/\n?\r?/", '', $text);

	if ($CFG->block_vmoodle_encoding != 'UTF-8'){
		$text = utf8_encode($text);
	}

	// last chance
	if ('ASCII' == mb_detect_encoding($text)){
		$text = utf8_encode($text);
	}
	
	// check the text is empty or cmment line and answer true if it is
	return preg_match('/^$/', $text) || preg_match('/^(\(|\[|-|#|\/| )/', $text);
}
