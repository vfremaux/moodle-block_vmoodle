<?php

/**
 * lib.php
 * 
 * General library for vmoodle.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
* Requires and includes
*/
include_once("{$CFG->dirroot}/blocks/vmoodle/filesystemlib.php");

/**
* get the list of available vmoodles
* @return an array of vmoodle objects
*/
function vmoodle_get_vmoodles(){
    global $DB;
    if ($vmoodles = $DB->get_records('block_vmoodle')){
        return $vmoodles;
    }
    return array();
}

/**
* prints an administrative status for a vmoodle
* @uses $CFG
* @param objet vmoodle
* @param boolean $return if true return the status html as a string, prints it elsewhere
*/
function vmoodle_print_status($vmoodle, $return = false){
    global $CFG;
    if (!vmoodle_check_installed($vmoodle)){
        $vmoodlestate = "<img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/broken.gif\"/>";
    } elseif($vmoodle->enabled) {
        $vmoodlestate = "<img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/enabled.gif\"/>";
    } else {
        $vmoodlestate = "<img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/disabled.gif\"/>";
    }
    if (!$return) echo $vmoodlestate;
    return $vmoodlestate;
}

/**
* checks physical availability of the vmoodle
* @uses $CFG
* @param object $vmoodle
* @return boolean
*/
function vmoodle_check_installed($vmoodle){
    global $CFG;

    return (filesystem_is_dir($vmoodle->vdatapath, ''));
}

/**
* adds an error css marker in case of matching error
* @param array $errors the current error set
* @param string $errorkey 
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

/**
* provides a side connection to a vmoodle database
* @param object $vmoodle
* @return a connection
*/
function vmoodle_make_connection(&$vmoodle, $binddb = false){
    if($vmoodle->vdbtype == 'mysql'){
        // Important : force new link here
        $mysql_side_cnx = @mysql_connect($vmoodle->vdbhost, $vmoodle->vdblogin, $vmoodle->vdbpass, true);
        if (!$mysql_side_cnx) return false;
        if ($binddb){
            if (!mysql_select_db($vmoodle->vdbname, $mysql_side_cnx)){
                echo "vmoodle_make_connection : Database not found<br/>";
                mysql_close($mysql_side_cnx);
                return false;
            }
        }
        return $mysql_side_cnx;
    } elseif($vmoodle->vdbtype == 'postgres') {
        if (ereg(":", $vmoodle->vdbhost)){
            list($host, $port) = explode(":", $vmoodle->vdbhost);
            $port = "port=$port";
        } else {
            $host = $vmoodle->vdbhost;
            $port = '';
        }
        $dbname = ($binddb) ? "dbname={$vmoodle->vdbname} " : '' ;
        $postgres_side_cnx = @pg_connect("host={$host} {$port} user={$vmoodle->vdblogin} password={$vmoodle->vdbpass} {$dbname}");
        return $postgres_side_cnx;
    } else {
        echo "vmoodle_make_connection : Database not supported<br/>";
    }
}

/**
* executes a query on a vmoodle database. Query must return no results
* so it may be an INSERT or an UPDATE or a DELETE.
* @param object $vmoodle
* @param string $sql
* @param handle $cnx
*/
function vmoodle_execute_query(&$vmoodle, $sql, $cnx){
    if($vmoodle->vdbtype == 'mysql'){
        if (!($res = mysql_query($sql, $cnx))){
            echo "vmoodle_execute_query : ".mysql_error($cnx)."<br/>";
            return false;
        }
        if ($newid = mysql_insert_id($cnx)){
            $res = $newid; // get the last insert id in case of an INSERT
        }
    }
    else if($vmoodle->vdbtype == 'mysqli'){
        if (!($res = mysqli_query($sql, $cnx))){
            echo "vmoodle_execute_query : ".mysql_error($cnx)."<br/>";
            return false;
        }
        if ($newid = mysqli_insert_id($cnx)){
            $res = $newid; // get the last insert id in case of an INSERT
        }
    }
     elseif ($vmoodle->vdbtype == 'postgres') {
        if (!($res = pg_query($cnx, $sql))){
            echo "vmoodle_execute_query : ".pg_last_error($cnx)."<br/>";
            return false;
        }
        if ($newid = pg_last_oid($res)){
            $res = $newid; // get the last insert id in case of an INSERT
        }
    } else {
        echo "vmoodle_execute_query : Database not supported<br/>" ;
        return false;
    }
     return $res;
}

/**
* closes a vmoodle database
* @param handle $cnx
*/
function vmoodle_close_connection($vmoodle, $cnx){
    if($vmoodle->vdbtype == 'mysql'){
        $res = mysql_close($cnx);
    } elseif($vmoodle->vdbtype == 'postgres') {
        $res = pg_close($cnx);
    } else {
        echo "vmoodle_close_connection : Database not supported<br/>";
        $res = false;
    }
    return $res;
}

/**
* setup and configure a mnet environment that describes this vmoodle 
* @uses $USER for generating keys
* @uses $CFG
* @param object $vmoodle
* @param handle $cnx a connection
*/
function vmoodle_setup_mnet_environment($vmoodle, $cnx){
    global $USER, $CFG;

    /*
    $dn = array(
       "countryName" => $USER->country,
       "stateOrProvinceName" => $USER->city,
       "localityName" => $USER->city,
       "organizationName" => $CFG->block_vmoodle_organization,
       "organizationalUnitName" => $CFG->block_vmoodle_organization_unit,
       "commonName" => $vmoodle->vhostname,
       "emailAddress" => $CFG->block_vmoodle_organization_email
    );
    */
    // make an empty mnet environment
    $mnet_env = new mnet_environment();    

    $mnet_env->wwwroot              = $vmoodle->vhostname;
    $mnet_env->ip_address           = $CFG->block_vmoodle_vmoodleip;
    $mnet_env->keypair              = array();
    $mnet_env->keypair              = mnet_generate_keypair(null);
    $mnet_env->public_key           = $mnet_env->keypair['certificate'];
    $details                        = openssl_x509_parse($mnet_env->public_key);
    $mnet_env->public_key_expires   = $details['validTo_time_t'];

    return $mnet_env;
}

/**
* setup services for a given mnet environment in a database
* @uses $CFG
* @param object $mnet_env an environment with valid id
* @param handle $cnx a connection to the target bdd
* @param object $services an object that holds service setup data
*/
function vmoodle_add_services(&$vmoodle, $mnet_env, $cnx, $services){
    if (!$mnet_env->id) return false;
    if ($services){
        foreach($services as $service => $keys){
            $sql = "
               INSERT INTO
                  {$vmoodle->vdbprefix}mnet_host2service(
                  hostid,
                  serviceid,
                  publish,
                  subscribe)
               VALUES (
                  {$mnet_env->id},
                  $service,
                  {$keys['publish']},
                  {$keys['subscribe']}
               )
            ";
            vmoodle_execute_query($vmoodle, $sql, $cnx);
        }
    }
}

/**
* get available services in the master
* @return array of service descriptors.
*/
function vmoodle_get_service_desc(){
    global $DB;
    $services = $DB->get_records('mnet_service', array('offer' => 1));

    $service_descriptor = array();

    if ($services){
        foreach($services as $service){
            $service_descriptor[$service->id]['publish'] = 1;
            $service_descriptor[$service->id]['subscribe'] = 1;
        }
    }
    return $service_descriptor;
}

/**
* given a complete mnet_environment record, and a connection
* record this mnet host in remote database. If the record is
* a new one, gives back a completed env with valid remote id.
* @param object $mnet_env
* @param handle $cnx
* @return the inserted mnet_env object
*/
function vmoodle_register_mnet_peer(&$vmoodle, $mnet_env, $cnx){
    $mnet_array = get_object_vars($mnet_env);
    if (empty($mnet_env->id)){
        foreach($mnet_array as $key => $value){
            if ($key == 'id') continue;
            $keylist[] = $key;
            $valuelist[] = "'$value'";
        }
        $keyset = implode(',', $keylist);
        $valueset = implode(',', $valuelist);
        $sql = "
            INSERT INTO
               {$vmoodle->vdbprefix}mnet_host(
                {$keyset}
                )
            VALUES(
                {$valueset}
            )
        ";
        $mnet_env->id = vmoodle_execute_query($vmoodle, $sql, $cnx);
    } else {
        foreach($mnet_array as $key => $value){
            $valuelist[] = "$key = '$value'";
        }
        unset($valuelist['id']);
        $valueset = implode(',', $valuelist);
        $sql = "
            UPDATE
               {$vmoodle->vdbprefix}mnet_host
            SET
                {$valueset}             
            WHERE
                id = {$mnet_array['id']}
        ";
        vmoodle_execute_query($vmoodle, $sql, $cnx);
    }
    return $mnet_env;
} 

/**
* get the mnet_env record for an host
* @param object $vmoodle
* @return object a mnet_host record
*/
function vmoodle_get_mnet_env(&$vmoodle){
    global $DB;
    $mnet_env = $DB->get_record('mnet_host', array('wwwroot' => $vmoodle->vhostname));
    return $mnet_env;
}

/**
* unregister a vmoodle from the whole remaining network
* @uses $CFG
* $param object $vmoodle
* @param handle $cnx
* @param object $fromvmoodle
*/
function vmoodle_unregister_mnet(&$vmoodle, $fromvmoodle ){
    global $CFG;

    if ($fromvmoodle){
        $vdbprefix = $fromvmoodle->vdbprefix;
    } else {
        $vdbprefix = $CFG->prefix;
    }
    $cnx = vmoodle_make_connection($fromvmoodle, true);
    // cleanup all services for the deleted host
    $sql = "
        DELETE FROM
            {$vmoodle->vdbprefix}mnet_host2service
        WHERE
            hostid = (SELECT 
                        id 
                     FROM 
                        {$vdbprefix}mnet_host
                     WHERE
                        wwwroot = '{$vmoodle->vhostname}')
    ";
    vmoodle_execute_query($vmoodle, $sql, $cnx);
    // delete the host
    $sql = "
        DELETE FROM
            {$vmoodle->vdbprefix}mnet_host
         WHERE
            wwwroot = '{$vmoodle->vhostname}'
     ";
    vmoodle_execute_query($vmoodle, $sql, $cnx);

}

/**
* drop a vmoodle database
* @param object $vmoodle
* @param handle $side_cnx
*/
function vmoodle_drop_database(&$vmoodle, $cnx=null){
    /// try to delete database
    $local_cnx = 0;
    if (!$cnx){
        $local_cnx = 1;
        $cnx = vmoodle_make_connection($vmoodle);
    }

    if (!$cnx){
        $erroritem->message = get_string('couldnotconnecttodb', 'block_vmoodle');
        $erroritem->on = 'db';
        return $erroritem;
    } else {
        if($vmoodle->vdbtype == 'mysql'){
            $sql = "
               DROP DATABASE `{$vmoodle->vdbname}`
            ";
        } elseif($vmoodle->vdbtype == 'postgres'){
            $sql = "
               DROP DATABASE {$vmoodle->vdbname}
            ";
        } else {
            echo "vmoodle_drop_database : Database not supported<br/>";
        }
        $res = vmoodle_execute_query($vmoodle, $sql, $cnx);
        if (!$res){
            $erroritem->message = get_string('couldnotdropdb', 'block_vmoodle');
            $erroritem->on = 'db';
            return $erroritem;
        }
        if ($local_cnx){
            vmoodle_close_connection($vmoodle, $cnx);
        }
    }
    return false;
}

/**
* load a bulk template in databse
* @param object $vmoodle
* @param string $bulfile a bulk file of queries to process on the database
* @param handle $cnx
* @param array $vars an array of vars to inject in the bulk file before processing
*/
function vmoodle_load_db_template(&$vmoodle, $bulkfile, $cnx = null, $vars=null, $filter=null){
    global $CFG;
    $local_cnx = 0;
    if (is_null($cnx) || $vmoodle->vdbtype == 'postgres'){
        // postgress MUST make a new connection to ensure db is bound to handle.
        $cnx = vmoodle_make_connection($vmoodle, true);
        $local_cnx = 1;
    }
    /// get dump file
    if (file_exists($bulkfile)){
        $sql = file($bulkfile);

        // converts into an array of text lines
        $dumpfile = implode("", $sql);
        if ($filter){
            foreach($filter as $from => $to){
                $dumpfile = mb_ereg_replace(preg_quote($from), $to, $dumpfile);
            }
        }
        // insert any external vars
        if (!empty($vars)){
            foreach($vars as $key => $value){
                // for debug : echo "$key => $value";
                $dumpfile = str_replace("<%%$key%%>", $value, $dumpfile);
            }
        }
        $sql = explode ("\n", $dumpfile);
        // cleanup unuseful things
        if ($vmoodle->vdbtype == 'mysql'){
            $sql = preg_replace("/^--.*/", "", $sql);
            $sql = preg_replace("/^\/\*.*/", "", $sql);
        }    
        $dumpfile = implode("\n", $sql);
    } else {
        echo "vmoodle_load_db_template : Bulk file not found";
        return false;
    }
    /// split into single queries
    $dumpfile = str_replace("\r\n", "\n", $dumpfile); // translates to Unix LF
    $queries = preg_split("/;\n/", $dumpfile);
    /// feed queries in database
    $i = 0;
    $j = 0;
    if (!empty($queries)){
        foreach($queries as $query){
            $query = trim($query); // get rid of trailing spaces and returns
            if ($query == '') continue; // avoid empty queries
            $query = mb_convert_encoding($query, 'iso-8859-1', 'auto');
            if (!$res = vmoodle_execute_query($vmoodle, $query, $cnx)){
                echo "<hr/>load error on <br/>" . $cnx . "<hr/>";
                $j++;
            } else {
                $i++;
            }
            // echo "<hr/><pre>$query</pre></hr>";
        }
    }
    echo "loaded : $i queries succeeded, $j queries failed<br/>";
    if ($local_cnx){
        vmoodle_close_connection($vmoodle, $cnx);    
    }
    return false;
}
/**
* read manifest values in vmoodle template.
* @uses $CFG
*/
function vmoodle_get_vmanifest($version){
    global $CFG;
    include($CFG->dirroot.'/blocks/vmoodle/'.$version.'_sql/manifest.php');
    $manifest->templatehost = $templatehost;
    return $manifest;
}

/**
* make a fake vmoodle that represents the current host
* @uses $CFG;
*/
function vmoodle_make_this(){
    global $CFG;
    $thismoodle->vdbhost = $CFG->dbhost;
    $thismoodle->vdblogin = $CFG->dbuser;
    $thismoodle->vdbpass = $CFG->dbpass;
    $thismoodle->vdbname = $CFG->dbname;
    $thismoodle->vdbpersist = $CFG->dbpersist;
    $thismoodle->vdbtype = $CFG->dbtype;
    $thismoodle->vdbprefix = $CFG->prefix;
    return $thismoodle;
}

/**
* provides a side connection to a vmoodle database
* @param object $vmoodle
* @return a connection
*/
function vmoodle_make_connection(&$vmoodle, $binddb = false){
    if($vmoodle->vdbtype == 'mysql'){
        // Important : force new link here
        $mysql_side_cnx = @mysql_connect($vmoodle->vdbhost, $vmoodle->vdblogin, $vmoodle->vdbpass, true);
        if (!$mysql_side_cnx) return false;
        if ($binddb){
            if (!mysql_select_db($vmoodle->vdbname, $mysql_side_cnx)){
                echo "vmoodle_make_connection : Database not found<br/>";
                mysql_close($mysql_side_cnx);
                return false;
            }
        }
        return $mysql_side_cnx;
    } elseif($vmoodle->vdbtype == 'postgres') {
        if (preg_match("/:/", $vmoodle->vdbhost)){
            list($host, $port) = explode(":", $vmoodle->vdbhost);
            $port = "port=$port";
        } else {
            $host = $vmoodle->vdbhost;
            $port = '';
        }
        $dbname = ($binddb) ? "dbname={$vmoodle->vdbname} " : '' ;
        $postgres_side_cnx = @pg_connect("host={$host} {$port} user={$vmoodle->vdblogin} password={$vmoodle->vdbpass} {$dbname}");
        return $postgres_side_cnx;
    } else {
        echo "vmoodle_make_connection : Database not supported<br/>";
    }
}

?>