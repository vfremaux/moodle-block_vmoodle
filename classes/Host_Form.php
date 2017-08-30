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
 * Define form for adding or editing a vmoodle host.
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace block_vmoodle;

require_once($CFG->libdir.'/formslib.php');

class Host_Form extends \moodleform {

    /**
     * Action to call from controller.
     */
    private $mode;

    /**
    * Data array for the form.
    */
    private $platform_form;

    /**
     * Constructor.
     * @param string $mode The action to call from controler.
     * @param array $platform_form Data to input in fields.
     */
    public function __construct($mode, $platform_form = null) {
        // Settings mode and data.
        $this->mode = $mode;
        $this->platform_form = $platform_form;

        // Calling parent's constructor.
        parent::__construct('view.php?view=management&what=do'.$this->mode.'&page='.$this->mode);
    }

    /**
     * Describes the form (each elements' name  corresponds to its name in database).
     */
    public function definition() {
        // Global configuration.
        global $CFG,$DB;

        // Settings variables.
        $mform = &$this->_form;
        $size_input_text = 'size="30"';
        $size_input_text_big = 'size="60"';

        /*
         * Host's id.
         */
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        /*
         * Features fieldset.
         */
        $mform->addElement('header', 'featuresform', get_string('addformfeaturesgroup', 'block_vmoodle'));
        // Name.
        $mform->addElement('text', 'name', get_string('addformname', 'block_vmoodle'), $size_input_text);
        $mform->addHelpButton('name', 'name','block_vmoodle');
        $mform->setType('name', PARAM_TEXT);
        if ($this->isInAddMode()) {
            // Shortname.
            $elmname = get_string('addformshortname', 'block_vmoodle');
            $mform->addElement('text', 'shortname', $elmname, ($this->mode == 'edit' ? 'disabled="disabled" ' : ''));
            $mform->addHelpButton('shortname', 'shortname', 'block_vmoodle');
            $mform->setType('shortname', PARAM_TEXT);
        }

        // Description.
        $elmname = get_string('addformdescription', 'block_vmoodle');
        $mform->addElement('textarea', 'description', $elmname, 'rows="15" cols="40"');
        $mform->addHelpButton('description', 'description', 'block_vmoodle');
        $mform->setType('description', PARAM_TEXT);

        // Change : Let us change the URL if needed.
        $elmname = get_string('vhostname', 'block_vmoodle');
        $mform->addElement('text', 'vhostname', $elmname, $size_input_text);
        $mform->addHelpButton('vhostname', 'vhostname', 'block_vmoodle');
        $mform->addElement('checkbox', 'forcedns', get_string('forcedns', 'block_vmoodle'));
        $mform->setType('vhostname', PARAM_URL);

        $mform->closeHeaderBefore('dbform');

        /*
         * Database fieldset.
         */
        $mform->addElement('header', 'dbform', get_string('addformdbgroup', 'block_vmoodle'));

        // Database type.
        $dbtypearray = array('mysqli' => 'MySQL', 'postgres' => 'PostgreSQL');
        $mform->addElement('select', 'vdbtype', get_string('vdbtype', 'block_vmoodle'), $dbtypearray);
        $mform->addHelpButton('vdbtype', 'vdbtype', 'block_vmoodle');
        $mform->setType('vdbtype', PARAM_TEXT);

        // Database host.
        $mform->addElement('text', 'vdbhost', get_string('vdbhost', 'block_vmoodle'));
        //$mform->addHelpButton('vdbhost', 'vdbhost', 'block_vmoodle');
        $mform->setType('vdbhost', PARAM_TEXT);

        // Database login.
        $mform->addElement('text', 'vdblogin', get_string('vdblogin', 'block_vmoodle'));
        $mform->setType('vdblogin', PARAM_TEXT);

        // Database password.
        $mform->addElement('password', 'vdbpass', get_string('vdbpass', 'block_vmoodle'));
        $mform->setType('vdbpass', PARAM_RAW);

        // Button for testing database connection.
        $mform->addElement('button', 'testconnection', get_string('testconnection', 'block_vmoodle'), 'onclick="opencnxpopup(\''.$CFG->wwwroot.'\'); return true;"');

        // Database name.
        $mform->addElement('text', 'vdbname', get_string('vdbname', 'block_vmoodle'));
        $mform->addHelpButton('vdbname', 'vdbname', 'block_vmoodle');
        $mform->setType('vdbname', PARAM_TEXT);

        // Table's prefix.
        $mform->addElement('text', 'vdbprefix', get_string('vdbprefix', 'block_vmoodle'));
        $mform->setType('vdbprefix', PARAM_TEXT);

        // Connection persistance.
        $noyesarray = array('0' => get_string('no'), '1' => get_string('yes'));
        $mform->addElement('select', 'vdbpersist', get_string('vdbpersist', 'block_vmoodle'), $noyesarray);
        $mform->addHelpButton('vdbpersist', 'vdbpersist', 'block_vmoodle');
        $mform->setType('vdbpersist', PARAM_BOOL);
        $mform->closeHeaderBefore('nfform');

        /*
         * Network and data fieldset.
         */
        $mform->addElement('header', 'nfform', get_string('addformnfgroup', 'block_vmoodle'));

        // Path for "moodledata".
        $mform->addElement('text', 'vdatapath', get_string('vdatapath', 'block_vmoodle'), $size_input_text_big);
        $mform->addHelpButton('vdatapath', 'vdatapath', 'block_vmoodle');
        $mform->setType('vdatapath', PARAM_TEXT);

        // Button for testing datapath.
        $elmname = get_string('testdatapath', 'block_vmoodle');
        $mform->addElement('button', 'testdatapath', $elmname, 'onclick="opendatapathpopup(\''.$CFG->wwwroot.'\'); return true;"');

        // MultiMNET.
        $subnetworks = array('-1' => get_string('nomnet', 'block_vmoodle'));
        $subnetworks['0'] = get_string('mnetfree', 'block_vmoodle');
        $sql = "
            SELECT
                *
            FROM
                {block_vmoodle}
            WHERE
                mnet > 0
            ORDER BY
                mnet
        ";
        $subnetworksrecords = $DB->get_records_sql($sql);

        $newsubnetwork = 1;
        if (!empty($subnetworksrecords)) {
            $maxmnet = 0;
            foreach ($subnetworksrecords as $subnetworksrecord) {
                $subnetworks[$subnetworksrecord->mnet] = $subnetworksrecord->mnet;
                $maxmnet = max($maxmnet, $subnetworksrecord->mnet);
            }
            $newsubnetwork = $maxmnet + 1;
        }
        $subnetworks[$newsubnetwork] = $newsubnetwork.' ('.get_string('mnetnew', 'block_vmoodle').')';
        $mform->addElement('select', 'mnet', get_string('multimnet', 'block_vmoodle'), $subnetworks, 'onchange="switcherServices(\''.$newsubnetwork.'\'); return true;"');
        $mform->addHelpButton('mnet', 'mnet', 'block_vmoodle');
        $mform->setType('mnet', PARAM_TEXT);

        // Services strategy.
        $services_strategies = array(
            'default' => get_string('servicesstrategydefault', 'block_vmoodle'),
            'subnetwork' => get_string('servicesstrategysubnetwork', 'block_vmoodle')
        );
        $mform->addElement('select', 'services', get_string('servicesstrategy', 'block_vmoodle'), $services_strategies);
        $mform->addHelpButton('services', 'services', 'block_vmoodle');
        $mform->setType('services', PARAM_TEXT);

        if ($this->isInAddMode()) {
            // Template.
            $templatesarray = vmoodle_get_available_templates();
            $mform->addElement('select', 'vtemplate', get_string('vtemplate', 'block_vmoodle'), $templatesarray);
            $mform->addHelpButton('vtemplate', 'vtemplate', 'block_vmoodle');
            $mform->setType('vtemplate', PARAM_TEXT);
        }
        $mform->closeHeaderBefore('submitbutton');

        // Control buttons.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string(($this->mode == 'edit' ? 'edit' : 'create')));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'controlbuttons', '', array(' '), false);

        // Rules for the add mode.
        if ($this->isInAddMode()) {
            $mform->addRule('name', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('shortname', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('vhostname', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('vdbhost', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('vdblogin', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('vdbname', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('vdbprefix', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
            $mform->addRule('vdatapath', get_string('addforminputtexterror', 'block_vmoodle'), 'required', null, 'client');
        }
    }

    /**
     * Test connection validation.
     * @see lib/moodleform#validation($data, $files)
     */
    function validation($data, $files = null) {
        global $CFG, $DB;

        // Empty array.
        $errors = parent::validation($data, null);

        // Checks database connection again, after Javascript test.
        $database    = new \stdClass;
        $database->vdbtype = $data['vdbtype'];
        $database->vdbhost = $data['vdbhost'];
        $database->vdblogin = $data['vdblogin'];
        $database->vdbpass = $data['vdbpass'];

        if (!vmoodle_make_connection($database, false)) {
            $errors['vdbhost'] = get_string('badconnection', 'block_vmoodle');
            $errors['vdblogin'] = get_string('badconnection', 'block_vmoodle');
            $errors['vdbpass'] = get_string('badconnection', 'block_vmoodle');
        }

        // Checks if database's name doesn't finish with '_'.
        if ($data['vdbname'][strlen($data['vdbname']) -1] == '_') {
            $errors['vdbname'] = get_string('baddatabasenamecoherence', 'block_vmoodle');
        }

        // Checks if database's name doesn't finish with '_'.
        if (strstr($data['vdbname'], '-') !== false) {
            $errors['vdbname'] = get_string('badnohyphensindbname', 'block_vmoodle');
        }

        // Checks if table's prefix doesn't begin with restricted values (which can evolve).
        $restrictedvalues = array(
            'vmoodle_'
        );
        foreach ($restrictedvalues as $restrictedvalue) {
            if ($data['vdbprefix'] == $restrictedvalue) {
                $errors['vdbprefix'] = get_string('baddatabaseprefixvalue', 'block_vmoodle');
            }
        }

        /*
         * ATTENTION Checks if user has entered a datapath with only one backslash between each folder
         * and/or file.
         */
        if (isset($CFG->ostype)
                && ($CFG->ostype == 'WINDOWS')
                    && (preg_match('#\\\{3,}#', $data['vdatapath']) > 0)){
            $errors['vdatapath'] = get_string('badmoodledatapathbackslash', 'block_vmoodle');
            return $errors;
        }

        // Test of values which have to be well-formed and can not be modified after.
        if ($this->isInAddMode()) {

            // Checks 'shortname', which must have no spaces.
            $shortname = $data['shortname'];
            if (strstr($shortname, ' ')) {
                $errors['shortname'] = get_string('badshortname', 'block_vmoodle');
            }

            // Checks 'vhostname', if not already used.
            if ($this->isEqualToAnotherVhostname($data['vhostname'])) {
                // Check if the vhostname is deleted.
                $sql = "
                    SELECT
                        m.deleted
                    FROM
                        {block_vmoodle} b,
                        {mnet_host} m
                    WHERE
                        b.vhostname = ?
                    AND
                        b.vhostname = m.wwwroot
                ";
                $resultsqlrequest = $DB->get_record_sql($sql, array($data['vhostname']));
                if (!empty($resultsqlrequest)) {
                    if($resultsqlrequest->deleted == 0) {
                        $errors['vhostname'] = get_string('badhostnamealreadyused', 'block_vmoodle');
                    } else {
                        // Id the plateforme is deleted and the user want to reactivate the vhostname.
                        if ($data['vtemplate'] == 0) {
                            $sql = "
                                SELECT
                                    id,
                                    vdatapath,
                                    vdbname
                                FROM
                                    {block_vmoodle}
                                WHERE
                                    vhostname = ?
                            ";
                            $resultsqlrequest = $DB->get_record_sql($sql, array($data['vhostname']));

                            // Checks if datapath and vdbname of vhostname are the same on the form.
                            if ($resultsqlrequest->vdatapath != stripslashes($data['vdatapath'])
                                    && $resultsqlrequest->vdbname != $data['vdbname']) {
                                $errors['vdatapath'] = get_string('errorreactivetemplate', 'block_vmoodle');
                                $errors['vdbname'] = get_string('errorreactivetemplate', 'block_vmoodle');
                            }
                        }
                    }
                }
            }

            // Checks 'vhostname' consistency, with a regular expression.
            $vhostname = $data['vhostname'];
            if (!preg_match('/^http(s)?:\/\//', $vhostname)) {
                $errors['vhostname'] = get_string('badvhostname', 'block_vmoodle');
            }

            // Checks 'vdatapath', if not already used.
            if ($this->isEqualToAnotherDataRoot($data['vdatapath'])) {
                if ($data['vtemplate'] === 0) {
                } else {
                    $errors['vdatapath'] = get_string('badmoodledatapathalreadyused', 'block_vmoodle');
                }
            }

            // Checks 'vdbname', if not already used.
            if ($this->isEqualToAnotherDatabaseName($data['vdbname']) && $data['vtemplate'] != 0) {
                $errors['vdbname'] = get_string('baddatabasenamealreadyused', 'block_vmoodle');
            }
        }

        return $errors;
    }

    /**
     * Test if form is in add mode.
     * @return bool If true, form is in add mode, else false.
     */
    protected function isInAddMode() {
        return ($this->mode == 'add');
    }

    /**
     * Test if form is in edit mode.
     * @return bool If true, form is in edit mode, else false.
     */
    protected function isInEditMode() {
        return ($this->mode == 'edit');
    }

    /**
     * Checks if the new virtual host's selected hostname is already used.
     * @param string $vhostname The hostname to check.
     * @return bool If TRUE, the chosen hostname is already used, else FALSE.
     */
    private function isEqualToAnotherVhostname($vhostname) {
        global $DB;

        $sql = "
            SELECT
                *
            FROM
                {block_vmoodle}
            WHERE
                vhostname LIKE ?
        ";
        $block_vmoodles = $DB->get_records_sql($sql, array('%'.$vhostname));
        return (empty($block_vmoodles) ? false : true);
    }

    /**
     * Checks if the new virtual host's datapath is already used.
     * @param  string $vdatapath The datapath to check.
     * @return bool If TRUE, the chosen datapath is already used, else FALSE.
     */
    private function isEqualToAnotherDataRoot($vdatapath) {
        global $DB;

        $vmoodles = $DB->get_records('block_vmoodle');
        if (!empty($vmoodles)) {
            // Retrieves all the vmoodles datapaths.
            $vdatapaths = array();
            foreach ($vmoodles as $vmoodle) {
                $vdatapaths[] = $vmoodle->vdatapath;
            }

            return in_array(stripslashes($vdatapath), $vdatapaths);
        }
        return false;
    }

    /**
     * Checks if the new virtual host's selected database name is already used.
     * @param string $vdbname The database name to check.
     * @return bool If TRUE, the chosen database name is already used, else FALSE.
     */
    private function isEqualToAnotherDatabaseName($vdbname) {
        global $DB;

        $vdbs = $DB->get_records_sql('SHOW DATABASES');
        if (!empty($vdbs)) {
            // Retrieves all the databases names.
            $vdbnames = array();
            foreach ($vdbs as $vdb) {
                $vdbnames[] = $vdb->database;
            }
            return in_array($vdbname, $vdbnames);
        }
        return false;
    }
}