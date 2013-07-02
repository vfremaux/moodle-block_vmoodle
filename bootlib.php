<?php

/**
*
*
*/
function vmoodle_get_hostname(){
	global $CFG;
	
	if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
		$protocol = 'https';
	} else {
		$protocol = 'http';
	}
	
	$CFG->vmoodleroot = "{$protocol}://".@$_SERVER['HTTP_HOST'];
	$CFG->vmoodlename = @$_SERVER['HTTP_HOST'];
	if (empty($CFG->vmoodlename)){ // try again
	    $CFG->vmoodleroot = "{$protocol}://".$_SERVER['SERVER_NAME'];
	    if ($_SERVER['SERVER_PORT'] != 80){
	        $CFG->vmoodleroot .= ':'.$_SERVER['SERVER_PORT'];
	    }
	    $CFG->vmoodlename = $_SERVER['SERVER_NAME'];
	}
}

/**
* This is a boot lib that is required BEFORE we can have access
* to $CFG defines.
*/

function vmoodle_boot_configuration(){
	global $CFG;
	
	/**
	* vconfig provides an bypassed configuration using vmoodle host definition
	* from the vmoodle master instance
	*
	*/
	
	if ($CFG->vmoodleroot != $CFG->wwwroot){    
	
	    if ($CFG->vmasterdbtype == 'mysql'){
	    	$vmaster = new StdClass();
	        $vmaster->vdbtype = $CFG->vmasterdbtype;
	        $vmaster->vdbhost = $CFG->vmasterdbhost;
	        $vmaster->vdblogin = $CFG->vmasterdblogin;
	        $vmaster->vdbpass = $CFG->vmasterdbpass;
	        if (!$side_cnx = vmoodle_make_connection($vmaster)) return; // if vmoodle cnx not valid
	        if (!mysql_select_db($CFG->vmasterdbname, $side_cnx)); // if vmoodle cnx not valid
	        
	        $sql = "
	           SELECT 
	           *
	           FROM 
	              {$CFG->vmasterprefix}block_vmoodle
	           WHERE
	              vhostname = '$CFG->vmoodleroot'
	        ";

	        $res = mysql_query($sql, $side_cnx);
	        if ($res){
	            if (mysql_num_rows($res)){
	                $vmoodle = mysql_fetch_object($res);
	                $CFG->dbtype    = $vmoodle->vdbtype;
	                $CFG->dbhost    = $vmoodle->vdbhost;
	                $CFG->dbname    = $vmoodle->vdbname;
	                $CFG->dbuser    = $vmoodle->vdblogin;
	                $CFG->dbpass    = $vmoodle->vdbpass;
	                $CFG->dbpersist = $vmoodle->vdbpersist;
	                $CFG->prefix    = $vmoodle->vdbprefix;
	                
	                $CFG->wwwroot   = $CFG->vmoodleroot;
	                $CFG->dataroot  = $vmoodle->vdatapath;
	            
                
                } else {
	                die ("VMoodling : No configuration for this host : $CFG->vmoodleroot. May be faked.");
	            }
	        } else {
	            die ("VMoodling : Could not fetch virtual moodle configuration");
	        }
	    } elseif ($CFG->vmasterdbtype == 'mysqli'){
	    	
	    	$vmaster = new StdClass();
	        $vmaster->vdbtype = $CFG->vmasterdbtype;
	        $vmaster->vdbhost = $CFG->vmasterdbhost;
	        $vmaster->vdblogin = $CFG->vmasterdblogin;
	        $vmaster->vdbpass = $CFG->vmasterdbpass;
	        $vmaster->vdbname = $CFG->vmasterdbname;
	        if (!$side_cnx = vmoodle_make_connection($vmaster, true)) die ('VMoodle master server unreachable'); // if vmoodle cnx not valid
	        
	        $sql = "
	           SELECT 
	           *
	           FROM 
	              {$CFG->vmasterprefix}block_vmoodle
	           WHERE
	              vhostname = '$CFG->vmoodleroot'
	        ";
	    
	        $res = mysqli_query($side_cnx,$sql);
	        if ($res){
	            if (mysqli_num_rows($res)){

	                $vmoodle = mysqli_fetch_object($res);
	                $CFG->dbtype    = $vmoodle->vdbtype;
	                $CFG->dbhost    = $vmoodle->vdbhost;
	                $CFG->dbname    = $vmoodle->vdbname;
	                $CFG->dbuser    = $vmoodle->vdblogin;
	                $CFG->dbpass    = $vmoodle->vdbpass;
	                $CFG->dbpersist = $vmoodle->vdbpersist;
	                $CFG->prefix    = $vmoodle->vdbprefix;
	                
	                $CFG->wwwroot   = $CFG->vmoodleroot;
	                $CFG->dataroot  = $vmoodle->vdatapath;
	            } else {
	           // 	echo mysqli_error();
	                die ("VMoodling : No configuration for this host : $CFG->vmoodleroot. May be faked.");
	            }
	        } else {
	            die ("VMoodling : Could not fetch virtual moodle configuration");
	        }
	    } elseif ($CFG->vmasterdbtype == 'postgres' || $CFG->vmasterdbtype == 'postgres7'){
	    	$vmaster = new StdClass();
	        $vmaster->vdbtype = $CFG->vmasterdbtype;
	        $vmaster->vdbhost = $CFG->vmasterdbhost;
	        $vmaster->vdblogin = $CFG->vmasterdblogin;
	        $vmaster->vdbpass = $CFG->vmasterdbpass;
	        $side_cnx = vmoodle_make_connection($vmaster);
	
	        $sql = "
	           SELECT 
	           *
	           FROM 
	              {$CFG->vmasterprefix}block_vmoodle
	           WHERE
	              vhostname = '$CFG->vmoodleroot'
	        ";
	        $res = pg_query($side_cnx, $sql);
	        if ($res){
	            if (pg_num_rows($res)){
	                $vmoodle = pg_fetch_object($res);
	                $CFG->dbtype    = $vmoodle->vdbtype;
	                $CFG->dbhost    = $vmoodle->vdbhost;
	                $CFG->dbname    = $vmoodle->vdbname;
	                $CFG->dbuser    = $vmoodle->vdblogin;
	                $CFG->dbpass    = $vmoodle->vdbpass;
	                $CFG->dbpersist = $vmoodle->vdbpersist;
	                $CFG->prefix    = $vmoodle->vdbprefix;
	                
	                $CFG->wwwroot   = $CFG->vmoodleroot;
	                $CFG->dataroot  = $vmoodle->vdatapath;
	            } else {
	                die ("VMoodling : No configuration for this host. May be faked.");
	            }
	            pg_close($side_cnx);
	        } else {
	            die ("VMoodling : Could not fetch virtual moodle configuration");
	        }
	    } else {
	        die("VMoodling : Unsupported Database for VMoodleMaster");
	    }
	} elseif ($CFG->vmoodledefault) {
	    // echo "VDefault selected";
	    // do nothing, just bypass
	} else {
	    die ("real moodle instance cannot be used in this VMoodle implementation");
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
        if (!$mysql_side_cnx){
        	print_r($vmoodle);
            die ("VMoodle_make_connection : Server unreachable");
        }
        if ($binddb){
            if (!mysql_select_db($vmoodle->vdbname, $mysql_side_cnx)){
                die ("VMoodle_make_connection : Database not found");
            }
        }
        return $mysql_side_cnx;
    } elseif($vmoodle->vdbtype == 'mysqli'){
        // Important : force new link here
       
        $mysql_side_cnx = @mysqli_connect($vmoodle->vdbhost, $vmoodle->vdblogin, $vmoodle->vdbpass,$vmoodle->vdbname ,3306);
        if (!$mysql_side_cnx){
        	print_r($vmoodle);
            die ("VMoodle_make_connection : Server unreachable");
        }
        if ($binddb){
            if (!mysqli_select_db( $mysql_side_cnx,$vmoodle->vdbname)){
                die ("VMoodle_make_connection : Database not found");
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

