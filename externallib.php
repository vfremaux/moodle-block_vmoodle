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
 * this API opens to rest and soap access internal rpcs of vmoocle
 *
 * @package    core_enrol
 * @category   external
 * @copyright  2010 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Enrol external functions
 *
 * @package    block_vmoodle
 * @category   external
 * @copyright  2016 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class block_vmoodle_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_vmoodle_instance_parameters() {
        return new external_function_parameters(
            array(
                'vhostname' => new external_value(PARAM_URL, 'virtual host name'),
                'shortname' => new external_value(PARAM_TEXT, 'site short name'),
                'name' => new external_value(PARAM_TEXT, 'site full name'),
                'adminemail' => new external_value(PARAM_TEXT, 'admin email'),
                'description' => new external_value(PARAM_CLEANHTML, 'site description'),
                'vdbtype' => new external_value(PARAM_ALPHA, 'DB type'),
                'vdbhost' => new external_value(PARAM_TEXT, 'DB host'),
                'vdbname' => new external_value(PARAM_TEXT, 'DB name'),
                'vdblogin' => new external_value(PARAM_TEXT, 'DB login'),
                'vdbpass' => new external_value(PARAM_RAW, 'DB password'),
                'vdbpersist' => new external_value(PARAM_BOOLEAN, 'sql persistance'),
                'vdbprefix' => new external_value(PARAM_TEXT, 'database prefix'),
                'vtemplate' => new external_value(PARAM_TEXT, 'template for feeding data'),
                'vdatapath' => new external_value(PARAM_TEXT, 'moodledata path'),
                'mnet' => new external_value(PARAM_TEXT, 'mnet or submnet'),
                'mnetbinding' => new external_value(PARAM_INT, 'binding sheme'),
            )
        );
    }

    /**
     * deploys a full running vmoodle instance based on a given template in a full automated mode.
     *
     * @param url $vhostname
     * @return deployment status
     */
    public static function add_vmoodle_instance($vhostname, $shortname, $name, $adminemail, $description, $vdbtype, $vdbhost, $vdbname,
        $vdblogin, $vdbpass, $vdbpersist, $vdbprefix, $vtemplate, $vdatapath, $mnet, $mnetbinding) {
        global $SESSION, $CFG;

        /*
         * Do basic automatic PARAM checks on incoming data, using params description
         * If any problems are found then exceptions are thrown with helpful error messages
         */
        $params = self::validate_parameters(self::add_vmoodle_instance_parameters(),
                    array('vhostname' => $vhostname,
                          'shortname' => $shortname,
                          'name' => $name,
                          'adminemail' => $adminemail,
                          'description' => $description,
                          'vdbtype' => $vdbtype,
                          'vdbhost' => $vdbhost,
                          'vdbname' => $vdbname,
                          'vdblogin' => $vdblogin,
                          'vdbpass' => $vdbpass,
                          'vdbpersist' => $vdbpersist,
                          'vdbprefix' => $vdbprefix,
                          'vtemplate' => $vtemplate,
                          'vdatapath' => $vdatapath,
                          'mnet' => $mnet,
                          'mnetbinding' => $mnetbinding));

        $SESSION->vmoodledata['vhostname'] = $vhostname;
        $SESSION->vmoodledata['name'] = $name;
        $SESSION->vmoodledata['shortname'] = $shortname;
        $SESSION->vmoodledata['description'] = $description;
        $SESSION->vmoodledata['vdbtype'] = $dbtype;
        $SESSION->vmoodledata['vdbname'] = $vdbname;
        $SESSION->vmoodledata['vdblogin'] = $vdbuser;
        $SESSION->vmoodledata['vdbpass'] = $vdbpass;
        $SESSION->vmoodledata['vdbhost'] = $vdbhost;
        $SESSION->vmoodledata['vdbpersist'] = $vdbpersist;
        $SESSION->vmoodledata['vdbprefix'] = $vdbprefix;
        $SESSION->vmoodledata['vtemplate'] = $vtemplate;
        $SESSION->vmoodledata['vdatapath'] = $vdatapath;
        $SESSION->vmoodledata['mnet'] = $vmnet;
        $SESSION->vmoodledata['mnetbinding'] = $vmnetbinding;

        $action = 'doadd';
        $automation = true;

        for ($step = 0 ; $step <= 4; $step++) {
            require($CFG->dirroot.'/local/vmoodle/controller.management.php');
        }

        $result[] = array('status' => 0);

        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function add_vmoodle_instance_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'status'        => new external_value(PARAM_INT, 'final status'),
                )
            )
        );
    }
}
