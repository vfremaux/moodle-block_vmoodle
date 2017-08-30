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
 * Improvement of default MNET XML_RPC client.
 *
 * Improvements :
 * - Support multiple send.
 * - Support local calls.
 * - Error localisation.
 * - Auto add current user.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace block_vmoodle;

require_once($CFG->dirroot.'/mnet/peer.php');
require_once($CFG->dirroot.'/mnet/xmlrpc/client.php');

class XmlRpc_Client extends \mnet_xmlrpc_client {

    /**
     * Errors by host
     */
    private $host_errors = array();

    /**
     * Create a client and set the user.
     * @param array $user The calling user (optional / current platform user by default).
     */
    public function __construct($user=null) {
        global $CFG, $USER, $DB;

        // Calling parent constructor.
        parent::__construct();

        // Checking user
        if (is_null($user)) {
            // Creating current user.
            if (!($user_mnet_host = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))))
                throw new \block_vmoodle\commands\Command_Exception('unknownuserhost');
            $user = array(
                        'username' => $USER->username,
                        'remoteuserhostroot' => $user_mnet_host->wwwroot,
                        'remotehostroot' => $CFG->wwwroot
                    );
            $this->add_param($user, 'array');
        } else if (array_key_exists('username', $user) && array_key_exists('remoteuserhostroot', $user) && array_key_exists('remotehostroot', $user)) {
            $this->add_param($user, 'array');
        } else {
            throw new \block_vmoodle\commands\Command_Exception('badclientuser');
        }
    }

    /**
     * Set method to call.
     * @param    xmlrpcpath        string                The method to call.
     */
    public function set_method($xmlrpcpath) {
        // Save parameters
        $temp = $this->params;
        // Set methods
        if (parent::set_method($xmlrpcpath)) {
            // Restore parameters
            $this->params = $temp;
        }
    }

    /**
     * Empties all param stack.
     */
    public function reset_method() {
        $this->params = array();
    }

    /**
     * Get host errors.
     * @param mnet_peer $host An host to get errors (optional).
     * @return array The host error.
     */
    public function getErrors($host = null) {
        if (is_null($host)) {
            return $this->host_errors;
        } else if (array_key_exists($host->wwwroot, $this->host_errors)) {
            return $this->host_errors[$host->wwwroot];
        } else {
            return null;
        }
    }

    /**
     * Send the request to the server or execute function if is a local call.
     * @param mnet_peer $host A mnet_peer object with details of the remote host we're connecting to.
     * @return boolean True if the request is successfull, False otherwise.
     */
    public function send($host) {
        global $CFG;

        // Defining result.
        $return = false;
        $this->error = array();

        // Checking if is a local call.
        if ($host->wwwroot == $CFG->wwwroot) {

            // Getting method.
            $uri = explode('/', $this->method);
            $method = array_pop($uri);
            $file = implode('/', $uri);

            // Adding librairie.
            if (!include_once($CFG->dirroot.'/'.$file)) {
                $this->error[] = 'No such file.';
            } else if (!function_exists($method)) {
                // Checking local function existance.
                $this->error[] = 'No such function.';
            } else {
                // Making a local call.
                $this->response = call_user_func_array($method, $this->params);
                $result = true;
            }
        } else {
            // Make the default remote call.
            $result = parent::send($host);
        }


        // Capturing host errors.
        $this->host_errors[$host->wwwroot] = $this->error;

        // Reseting errors for next send.
        $this->error = array();

        // Returning result.
        return $result;
    }
}