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
 * Describes meta-administration plugin's SQL command.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace vmoodleadminset_sql;

use \block_vmoodle\commands\Command;
use \block_vmoodle\commands\Command_Exception;
use \StdClass;

class Command_Sql extends Command {

    /**
     * SQL command
     */
    private $sql;

    /**
     * If command's result should be returned
     */
    private $returned;

    /**
     * if commands has place holders, they are converted into Moodle SQL named variables
     */
    private $values;

    /**
     * Constructor.
     * @param string $name Command's name.
     * @param string $description Command's description.
     * @param string $sql SQL command.
     * @param mixed $parameters Command's parameters (optional / could be null, Command_Parameter object or Command_Parameter array).
     * @param Command $rpcommand Retrieve platforms command (optional / could be null or Command object).
     * @throws Command_Exception
     */
    public function __construct($name, $description, $sql, $parameters = null, $rpcommand = null) {
        global $vmcommands_constants;

        // Creating Command.
        parent::__construct($name, $description, $parameters, $rpcommand);

        // Checking SQL command.
        if (empty($sql)) {
            throw new Command_Sql_Exception('sqlemtpycommand', $this->name);
        } else {
            // Looking for parameters.
            preg_match_all(Command::placeholder, $sql, $sql_vars);

            // Checking parameters to show.
            foreach ($sql_vars[2] as $key => $sql_var) {
                $is_param = !(empty($sql_vars[1][$key]));
                if (!$is_param && !array_key_exists($sql_var, $vmcommands_constants)) {
                    throw new Command_Sql_Exception('sqlconstantnotgiven', (object)array('constant_name' => $sql_var, 'command_name' => $this->name));
                } else if ($is_param && !array_key_exists($sql_var, $this->parameters)) {
                    throw new Command_Sql_Exception('sqlparameternotgiven', (object)array('parameter_name' => $sql_var, 'command_name' => $this->name));
                }
            }
            $this->sql = $sql;
        }
        $this->values = array();
    }

    /**
     * Execute the command.
     * @param mixed $host The hosts where run the command (may be wwwroot or an array).
     * @throws Command_Sql_Exception
     */
    public function run($hosts) {
        global $CFG, $USER;

        // Adding constants.
        require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

        // Checking host.
        if (!is_array($hosts)) {
            $hosts = array($hosts => 'Unnamed host');
        }

        // Checking capabilities.
        if (!has_capability('block/vmoodle:execute', \context_system::instance())) {
            throw new Command_Sql_Exception('insuffisantcapabilities');
        }

        // Initializing responses.
        $responses = array();

        // Creating peers.
        $mnet_hosts = array();

        foreach ($hosts as $host => $name) {
            $mnet_host = new \mnet_peer();
            if ($mnet_host->bootstrap($host, null, 'moodle')) {
                $mnet_hosts[] = $mnet_host;
            } else {
                $responses[$host] = (object) array(
                    'status' => MNET_FAILURE,
                    'error' => get_string('couldnotcreateclient', 'block_vmoodle', $host)
                );
            }
        }

        // Getting command.
        $return = $this->isReturned();

        // Creating XMLRPC client.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/sql/rpclib.php/mnetadmin_rpc_run_sql_command');
        $rpc_client->add_param($this->_getGeneratedCommand(), 'string');
        $rpc_client->add_param($this->values, 'array');
        $rpc_client->add_param($return, 'boolean');

        // Sending requests.
        foreach ($mnet_hosts as $mnet_host) {

            // Sending request.
            if (!$rpc_client->send($mnet_host)) {
                $response = new StdClass;
                $response->status = MNET_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                if (debugging()) {
                    print_object($rpc_client);
                }
            } else {
                $response = json_decode($rpc_client->response);
            }

            // Recording response.
            $responses[$mnet_host->wwwroot] = $response;
        }

        // Saving results.
        $this->results = $responses + $this->results;
    }

    /**
     * Get the result of command execution for one host.
     * @param string $host The host to retrieve result (optional, if null, returns general result).
     * @param string $key The information to retrieve (ie status, error / optional).
     * @throws Command_Sql_Exception
     */
    public function getResult($host = null, $key = null) {

        // Checking if command has been runned.
        if (is_null($this->results)) {
            throw new Command_Exception('commandnotrun');
        }

        // Checking host (general result isn't provide in this kind of command).
        if (is_null($host) || !array_key_exists($host, $this->results)) {
            return null;
        }
        $result = $this->results[$host];

        // Checking key.
        if (is_null($key)) {
            return $result;
        } else if (property_exists($result, $key)) {
            return $result->$key;
        } else {
            return null;
        }
    }

    /**
     * Get SQL command.
     * @return SQL command.
     */
    public function getSql() {
        return $this->sql;
    }

    /**
     * Get if the command's result is returned.
     * @return boolean True if the command's result should be returned, false otherwise.
     */
    public function isReturned() {
        return $this->returned;
    }

    /**
     * Set if the command's result is returned.
     * @param boolean $returned True if the command's result should be returned, false otherwise.
     */
    public function setReturned($returned) {
        $this->returned = $returned;
    }

    /**
     * Get the command to execute.
     * @return string The final SQL command to execute.
     */
    private function _getGeneratedCommand() {
        return preg_replace_callback(self::placeholder, array($this, '_replaceParametersValues'), $this->getSql());
    }

    /**
     * Bind the replace_parameters_values function to create a callback.
     * @param array $matches The placeholders found.
     * @return string|array The parameters' values.
     */
    private function _replaceParametersValues($matches) {

        list($paramname, $paramvalue) = replace_parameters_values($matches, $this->getParameters(), true, false);

        $this->values[$paramname] = $paramvalue;

        // Return the named placeholder.
        return ':'.$paramname;
    }
}