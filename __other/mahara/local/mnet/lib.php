<?php

/**
* this function hooks cases of keyswap being called with forced mode.    
* Forced mode can only be used from hosts we trust untill now.
* 
* @see api/xmlrpc/dispatcher.php::keyswap()
* 
* Add : 
*		// PATCH add force mode
*        if (!empty($params[3])){ // requiring force mode
*        	$mnetlocallib = get_config('docroot').'/local/mnet/lib.php';
*        	if (file_exists($mnetlocallib)){
*	        	return local_xmlrpc_key_forced_keyswap($wwwroot, $pubkey, $application);
*	        }
*	        return false;
*        }
*        // /PATCH
*
* after $params decoding for enabling forced mode.
*/
function local_xmlrpc_key_forced_keyswap($wwwroot, $pubkey, $application){
	
	$now = time();
	// reinforced security : only known host with still valid key can force us renewal
	if ($exists = get_records_select_array('host', " wwwroot = '$wwwroot' AND deleted = 0 AND publickeyexpires >= $now ")){
        try {
            $peer = new Peer();
            if ($peer->findByWwwroot($wwwroot)){
                $pk = new PublicKey($pubkey, $wwwroot);
                $peer->publickey = $pk;
                $peer->commit();
            }
            // Mahara return his own key
	        $openssl = OpenSslRepo::singleton();
	        return $openssl->certificate;
        } catch (Exception $e) {
            throw new SystemException($e->getMessage(), $e->getCode());
        }
    } else {
        throw new SystemException("Fails exists known $wwwroot as wwwroot", 6100);
    }
}