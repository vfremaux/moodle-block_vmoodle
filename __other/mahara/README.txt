@Author : Valery Fremaux (valery.fremaux@gmail.com)

This is a patch given for Mahara to enable the automatic rotation of MNET keys
by a VMoodle Moodle array. This will ensure long time connectivity stability
of any member of the VMoodle set to a Mahara. 

The patch provides a local extension for Mahara. A Hook point to use this local addition
is explained below

this function hooks cases of keyswap being called with forced mode.    
Forced mode can only be used from hosts we trust untill now.

@see api/xmlrpc/dispatcher.php::keyswap()

Add : 
	// PATCH add force mode
       if (!empty($params[3])){ // requiring force mode
       	$mnetlocallib = get_config('docroot').'/local/mnet/lib.php';
       	if (file_exists($mnetlocallib)){
        	return local_xmlrpc_key_forced_keyswap($wwwroot, $pubkey, $application);
        }
        return false;
       }
       // /PATCH

after $params decoding for enabling forced mode.
