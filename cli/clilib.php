<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Opens and parses/checks a VMoodle instance definition file
 * @param string $location
 *
 */
function vmoodle_parse_csv_nodelist($nodelistlocation = '') {
    global $CFG;

    $vnodes = array();

    if (empty($nodelistlocation)) {
        $nodelistlocation = $CFG->dataroot.'/vmoodle/nodelist.csv';
    }

    // Decode file.
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

    /*
     * File that is used is currently hardcoded here !
     * Large files are likely to take their time and memory. Let PHP know
     * that we'll take longer, and that the process should be recycled soon
     * to free up memory.
     */
    @set_time_limit(0);
    @raise_memory_limit("256M");
    if (function_exists('apache_child_terminate')) {
        @apache_child_terminate();
    }

    // Make arrays of valid fields for error checking.
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
            'enabled' => 1,
            'mnet' => 1);

    $optionalDefaults = array(
            'mnet' => 1,
            'vdbtype' => 'mysqli',
            'vdbhost' => $CFG->dbhost,
            'vdbpersist' => $CFG->dboptions['dbpersist'],
            'vdbprefix' => 'mdl_',
            'vtemplate' => '',
            'enabled' => 1,
            'services' => 'default');

    $patterns = array();

    $metas = array('auth_.*',
        'block_.*',
        'mod_.*',
        'local_.*',
        'report_.*',
        'format_.*',
        'config_.*');

    // Get header (field names).

    $textlib = new textlib();

    if (!$fp = fopen($nodelistlocation, 'rb')) {
        cli_error(get_string('badnodefile', 'block_vmoodle', $nodelistlocation));
    }

    // Jump any empty or comment line.
    $text = fgets($fp, 1024);
    $i = 0;
    while(vmoodle_is_empty_line_or_format($text, $i == 0)){
        $text = fgets($fp, 1024);
        $i++;
    }

    $headers = explode($csv_delimiter2, $text);

    // Check for valid field names.
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

    // Check for required fields.
    foreach ($required as $key => $value) {
        if ($value) {
            // Required field missing.
            cli_error(get_string('fieldrequired', 'error', $key));
            return;
        }
    }
    $linenum = 2; // Since header is line 1.

    // Take some from admin profile, other fixed by hardcoded defaults.
    while (!feof ($fp)) {

        // Make a new base record.
        $vnode = new StdClass();
        foreach ($optionalDefaults as $key => $value) {
            $vnode->$key = $value;
        }

        // Commas within a field should be encoded as &#44 (for comma separated csv files).
        // Semicolon within a field should be encoded as &#59 (for semicolon separated csv files).
        $text = fgets($fp, 1024);
        if (vmoodle_is_empty_line_or_format($text, false)) {
            $i++;
            continue;
        }

        $valueset = explode($csv_delimiter2, $text);
        $f = 0;
        foreach ($valueset as $value) {
            // Decode encoded commas.
            $key = $headers[$f];

            // Do we have a global config ?
            if (preg_match('/^config_/', $key)) {
                $smartkey = str_replace('config_', '', $key);
                $vnode->config->$smartkey = trim($value);
                $f++;
                continue;
            }

            // Do we have a plugin config ?
            /*
             * plugin configs will come as f.e. "auth_cas|server" key
             *
             */
            if (strpos($key, '|') > 0) {
                list($component, $key) = explode('|', $key);
                list($type, $plugin) = normalize_component($component);
                if (!isset($vnode->$type)) {
                    $vnode->$type = new StdClass();
                }
                if (!isset($vnode->$type->$plugin)) {
                    $vnode->$type->$plugin = new StdClass();
                }
                $vnode->$type->$plugin->$key = preg_replace($csv_encode, $csv_delimiter2, trim($value));
                $f++;
                continue;
            }

            $vnode->$key = preg_replace($csv_encode, $csv_delimiter2, trim($value));
            $f++;
        }
        $vnodes[] = $vnode;
    }

    return $vnodes;
}

/**
 * Opens and parses/checks a VMoodle nodelist for snapshotting. Basically
 * compatible with nodelist format.
 * @param string $nodelistlocation
 */
function vmoodle_parse_csv_snaplist($nodelistlocation = '') {
    global $CFG;

    $vnodes = array();

    if (empty($nodelistlocation)) {
        $nodelistlocation = $CFG->dataroot.'/vmoodle/snaplist.csv';
    }

    // Decode file.
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

    /*
     * File that is used is currently hardcoded here !
     * Large files are likely to take their time and memory. Let PHP know
     * that we'll take longer, and that the process should be recycled soon
     * to free up memory.
     */
    @set_time_limit(0);
    @raise_memory_limit("1024M");
    if (function_exists('apache_child_terminate')) {
        @apache_child_terminate();
    }

    // Make arrays of valid fields for error checking.
    $required = array('vhostname' => 1,
            );

    // this will allow using a node creation list to operate
    $optional = array(
            'name' => 1,
            'shortname' => 1,
            'vdatapath' => 1,
            'vdbname' => 1,
            'vdblogin' => 1,
            'vdbpass' => 1,
            'description' => 1,
            'vdbhost' => 1,
            'vdbpersist' => 1,
            'vtemplate' => 1,
            'services' => 1,
            'enabled' => 1,
            'mnet' => 1);

    $optionalDefaults = array(
            'mnet' => 1,
            'vdbtype' => 'mysqli',
            'vdbhost' => $CFG->dbhost,
            'vdbpersist' => $CFG->dboptions['dbpersist'],
            'vdbprefix' => 'mdl_',
            'vtemplate' => '',
            'enabled' => 1,
            'services' => 'default');

    $patterns = array();

    $metas = array('auth_.*',
        'block_.*',
        'mod_.*',
        'local_.*',
        'report_.*',
        'format_.*',
        'config_.*');

    // Get header (field names).

    $textlib = new textlib();

    if (!$fp = fopen($nodelistlocation, 'rb')) {
        cli_error(get_string('badnodefile', 'block_vmoodle', $nodelistlocation));
    }

    // Jump any empty or comment line.
    $text = fgets($fp, 1024);
    $i = 0;
    while(vmoodle_is_empty_line_or_format($text, $i == 0)){
        $text = fgets($fp, 1024);
        $i++;
    }

    $headers = explode($csv_delimiter2, $text);

    // Check for valid field names.
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

    // Check for required fields.
    foreach ($required as $key => $value) {
        if ($value) {
            // Required field missing.
            cli_error(get_string('fieldrequired', 'error', $key));
            return;
        }
    }
    $linenum = 2; // Since header is line 1.

    // Take some from admin profile, other fixed by hardcoded defaults.
    while (!feof ($fp)) {

        // Make a new base record.
        $vnode = new StdClass();
        foreach ($optionalDefaults as $key => $value) {
            $vnode->$key = $value;
        }

        // Commas within a field should be encoded as &#44 (for comma separated csv files).
        // Semicolon within a field should be encoded as &#59 (for semicolon separated csv files).
        $text = fgets($fp, 1024);
        if (vmoodle_is_empty_line_or_format($text, false)) {
            $i++;
            continue;
        }

        $valueset = explode($csv_delimiter2, $text);
        $f = 0;
        foreach ($valueset as $value) {
            // Decode encoded commas.
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

    // We may have a risk the BOM is present on first line.
    if ($resetfirst) {
        $first = true;
    }
    if (!isset($textlib)) {
        $textlib = new textlib(); // Singleton.
    }
    if ($first && $CFG->block_vmoodle_encoding == 'UTF-8') {
        $text = $textlib->trim_utf8_bom($text);
        $first = false;
    }

    $text = preg_replace("/\n?\r?/", '', $text);

    if ($CFG->block_vmoodle_encoding != 'UTF-8') {
        $text = utf8_encode($text);
    }

    // last chance
    if ('ASCII' == mb_detect_encoding($text)) {
        $text = utf8_encode($text);
    }

    // Check the text is empty or comment line and answer true if it is
    return preg_match('/^$/', $text) || preg_match('/^(\(|\[|-|#|\/| )/', $text);
}
