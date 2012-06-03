<?php // $Id: lib.php,v 1.2.2.5 2012-02-21 20:24:34 vf Exp $
/**
 * Library functions for mnet
 *
 * @author  Valery Fremaux valery@valeisti.fr
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mnet
 */
require_once $CFG->dirroot.'/mnet/xmlrpc/xmlparser.php';
require_once $CFG->dirroot.'/mnet/peer.php';

/**
 * Get the remote machine's SSL Cert
 *
 * @param  string  $uri     The URI of a file on the remote computer, including
 *                          its http:// or https:// prefix
 * @return string           A PEM formatted SSL Certificate.
 */
function vmoodle_mnet_get_public_key($mnet_peer, $oldprivatekey, $force = 0) {
    global $CFG, $MNET;

    // The key may be cached in the mnet_set_public_key function...
    // check this first

    // cache location of key must be bypassed when we need an automated renew.
    if (!$force){
        $key = mnet_set_public_key($uri);
        if ($key != false) {
            return $key;
        }
    }

    $application = get_record('mnet_application', 'name', 'vmoodle');

    $rq = xmlrpc_encode_request('blocks/vmoodle/rpclib.php/mnetadmin_keyswap', array($CFG->wwwroot, $MNET->public_key, 'moodle', $force), array("encoding" => "utf-8"));
    $rq = mnet_sign_message($rq, $oldprivatekey);
    $rq = mnet_encrypt_message($rq, $mnet_peer->public_key);
 
    $uri = $mnet_peer->wwwroot . $application->xmlrpc_server_url;
    $ch = curl_init($uri);

    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rq);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $res = xmlrpc_decode(curl_exec($ch));

    // check for curl errors
    $curlerrno = curl_errno($ch);
    if ($curlerrno!=0) {
        debug_trace("VMoodle_get_public key : Request for $uri failed with curl error $curlerrno");
        debugging("Request for $uri failed with curl error $curlerrno");
    } 

    // check HTTP error code
    $info =  curl_getinfo($ch);
    if (!empty($info['http_code']) and ($info['http_code'] != 200)) {
        debug_trace("VMoodle_get_public_key : Request for $uri failed with HTTP code ".$info['http_code']);
        debugging("Request for $uri failed with HTTP code ".$info['http_code']);
    }

    curl_close($ch);

    if (!is_array($res)) { // ! error
        $public_certificate = $res;
        $credentials = array();
        if (strlen(trim($public_certificate))) {
            $credentials = openssl_x509_parse($public_certificate);
            $host = $credentials['subject']['CN'];
            if (strpos($uri, $host) !== false) {
                mnet_set_public_key($uri, $public_certificate);
                return $public_certificate;
            } else {
                debug_trace("VMoodle_get_public_key : Request for $uri returned public key for different URI - $host");
                debugging("Request for $uri returned public key for different URI - $host");
            }
        } else {
            debug_trace("VMoodle_get_public_key : Request for $uri returned empty response");
            debugging("Request for $uri returned empty response");
        }
    } else {
        debug_trace( "VMoodle_get_public_key : Request for $uri returned unexpected result");
        debugging( "Request for $uri returned unexpected result");
    }
    debug_trace( "VMoodle_get_public_key : false");
    return false;
}

?>
