<?php // $Id: Mnet_Peer.class.php,v 1.2.2.3 2011/02/01 10:20:17 vf Exp $
/**
 * An object to represent lots of information about an RPC-peer machine
 * This is a special implementation override for vmoodle MNET admin operations
 *
 * @author  Valery fremaux valery@valeisti.fr
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mnet
 */

require_once $CFG->dirroot.'/mnet/lib.php';
// require_once $CFG->dirroot.'/blocks/vmoodle/mnet/lib.php';

class vmoodle_mnet_peer extends mnet_peer {

    //Unless stated otherwise, properties of this object are unescaped, and unsafe to
    //insert into the db without further processing.

    function vmoodle_mnet_peer() {
        parent::mnet_peer();
        $this->updateparams = new stdClass();
        return true;
    }

    function bootstrap($wwwroot, $pubkey = null, $application, $force) {

        if (substr($wwwroot, -1, 1) == '/') {
            $wwwroot = substr($wwwroot, 0, -1);
        }

        if ( ! $this->set_wwwroot($wwwroot) ) {
            $hostname = mnet_get_hostname_from_uri($wwwroot);

            // Get the IP address for that host - if this fails, it will
            // return the hostname string
            $ip_address = gethostbyname($hostname);

            // Couldn't find the IP address?
            if ($ip_address === $hostname && !preg_match('/^\d+\.\d+\.\d+.\d+$/',$hostname)) {
                $this->errors[] = 'ErrCode 2 - '.get_string("noaddressforhost", 'mnet');
                return false;
            }

            $this->name = stripslashes($wwwroot);
            $this->updateparams->name = $wwwroot;

            // TODO: In reality, this will be prohibitively slow... need another
            // default - maybe blank string
            $homepage = @file_get_contents($wwwroot);
            if (!empty($homepage)) {
                $count = preg_match("@<title>(.*)</title>@siU", $homepage, $matches);
                if ($count > 0) {
                    $this->name = $matches[1];
                    $this->updateparams->name = addslashes($matches[1]);
                }
            }

            $this->wwwroot = stripslashes($wwwroot);
            $this->updateparams->wwwroot = $wwwroot;
            $this->ip_address = $ip_address;
            $this->updateparams->ip_address = $ip_address;
            $this->deleted = 0;
            $this->updateparams->deleted = 0;

            $this->application = get_record('mnet_application', 'name', $application);
            if (empty($this->application)) {
                $this->application = get_record('mnet_application', 'name', 'moodle');
            }

            $this->applicationid = $this->application->id;
            $this->updateparams->applicationid = $this->application->id;

            // start bootstraping as usual through the system command
            $pubkeytemp = clean_param(mnet_get_public_key($this->wwwroot, $this->application), PARAM_PEM);
            
            if(empty($pubkey)) {
                // This is the key difference : force the exchange using vmoodle RPC keyswap !!
                if (empty($pubkeytemp)){
                    $pubkeytemp = clean_param(mnet_get_public_key($this->wwwroot, $this->application, $force), PARAM_PEM);
                }
            } else {
                $pubkeytemp = clean_param($pubkey, PARAM_PEM);
            }
            $this->public_key_expires = $this->check_common_name($pubkeytemp);

            if ($this->public_key_expires == false) {
                return false;
            }
            $this->updateparams->public_key_expires = $this->public_key_expires;

            $this->updateparams->public_key = $pubkeytemp;
            $this->public_key = $pubkeytemp;

            $this->last_connect_time = 0;
            $this->updateparams->last_connect_time = 0;
            $this->last_log_id = 0;
            $this->updateparams->last_log_id = 0;
        }

        return true;
    }


    /**
     * Several methods can be used to get an 'mnet_host' record. They all then
     * send it to this private method to populate this object's attributes.
     * 
     * @param   object  $hostinfo   A database record from the mnet_host table
     * @return  void
     */
    function populate($hostinfo) {
        parent::populate($hostinfo);
        $this->visible              = @$hostinfo->visible; // let it flexible if not using the host visibility hack
    }

}

?>
