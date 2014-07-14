<?php

namespace vmoodleadminset_roles;
Use \block_vmoodle\commands\Command;
Use \block_vmoodle\commands\Command_Exception;
Use \block_vmoodle\commands\Command_Parameter;
Use \context_system;

require_once($CFG->libdir.'/accesslib.php');

/**
 * Describes a role comparison command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class Command_Role_Compare extends Command {

    /**
     * The role capabilities
     */
    private $capabilities = array();

    /**
     * The html report
     */
    private $report;

    /**
     * Constructor.
     * @throws            Command_Exception.
     */
    public function __construct() {
        global $DB;
        
        // Getting command description
        $cmd_name = vmoodle_get_string('cmdcomparename', 'vmoodleadminset_roles');
        $cmd_desc = vmoodle_get_string('cmdcomparedesc', 'vmoodleadminset_roles');

        // Getting role parameter
        $roles = role_fix_names(get_all_roles(), \context_system::instance(), ROLENAME_ORIGINAL);
        $rolemenu = array();
        foreach($roles as $r){
            $rolemenu[$r->shortname] = $r->localname;
        }
        $role_param = new Command_Parameter('role', 'enum', vmoodle_get_string('roleparamcomparedesc', 'vmoodleadminset_roles'), null, $rolemenu);

        // Creating command.
        parent :: __construct($cmd_name, $cmd_desc, $role_param);
    }

    /**
     * Execute the command.
     * @param    $hosts        mixed            The host where run the command (may be wwwroot or an array).
     * @throws                Command_Exception.
     */
    public function run($hosts) {
        global $CFG, $USER;

        // Adding constants.
        require_once $CFG->dirroot.'/blocks/vmoodle/rpclib.php';

        // Checking capabilities.
        if (!has_capability('block/vmoodle:execute', \context_system::instance())) {
            throw new Command_Exception('insuffisantcapabilities');
        }

        // Getting role.
        $role = $this->getParameter('role')->getValue();

        // Creating XMLRPC client to read role configuration.
        $rpc_client = new \block_vmoodle\XmlRpc_Client();
        $rpc_client->set_method('blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_get_role_capabilities');
        $rpc_client->add_param($role, 'string');

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
                    'status' => RPC_FAILURE,
                    'error' => get_string('couldnotcreateclient', 'block_vmoodle', $host)
                );
            }
        }

        // Sending requests.
        foreach ($mnet_hosts as $mnet_host) {
            // Sending request
            if (!$rpc_client->send($mnet_host)) {
                $response = new stdclass;
                $response->status = RPC_FAILURE;
                $response->errors[] = implode('<br/>', $rpc_client->getErrors($mnet_host));
                if (debugging()) {
                    echo '<pre>';
                    var_dump($rpc_client);
                    echo '</pre>';
                }
            } else {
                $response = json_decode($rpc_client->response);
            }
            // Recording response
            $responses[$mnet_host->wwwroot] = $response;
            // Recording capabilities
            if ($response->status == RPC_SUCCESS)
                $this->capabilities[$mnet_host->wwwroot] = $response->value;
        }
        // Saving results
        $this->results = $responses + $this->results;

        // Processing results.
        $this->_process();
    }

    /**
     * Get the result of command execution for one host.
     * @param    $host        string            The host to retrieve result (optional, if null, returns general result).
     * @param    $key        string            The information to retrieve (ie status, error / optional).
     * @return                mixed            The result or null if result does not exist.
     * @throws                Command_Exception.
     */
    public function getResult($host = null, $key = null) {
        // Checking if command has been runned.
        if (!$this->isRunned()) {
            throw new Command_Exception('commandnotrun');
        }

        // Checking host (general result isn't provide in this kind of command).
        if (is_null($host)) {
            return $this->report;
        } else {
            if (!array_key_exists($host, $this->results)) {
                return null;
            }
        }
        $result = $this->results[$host];

        // Checking key.
        if (is_null($key)) {
            return $result;
        } else {
            if (property_exists($result, $key)) {
                return $result-> $key;
            } else {
                return null;
            }
        }
    }

    /**
     * Process the role comparison.
     * @throws            Commmand_Exception.
     */
    private function _process() {
        global $CFG,$DB,$OUTPUT;

        // Checking if command has been runned.
        if (!$this->isRunned()) {
            throw new Command_Exception('commandnotrun');
        }

        // Defining capabilities values.
        $cap_permissions = array(
            CAP_ALLOW => array('count' => 0, 'label' => 1, 'name' => 'allow'),
            CAP_PREVENT => array('count' => 0, 'label' => 2, 'name' => 'prevent'),
            CAP_PROHIBIT => array('count' => 0, 'label' => 3, 'name' => 'prohibit')
        );

        // Defining capabilities context.
        $cap_contexts = array(
            CONTEXT_BLOCK => array('count' => 0, 'label' => 'B', 'name' => 'block'),
            CONTEXT_COURSE => array('count' => 0, 'label' => 'C', 'name' => 'course'),
            CONTEXT_COURSECAT => array('count' => 0, 'label' => 'CC', 'name' => 'coursecat'),
        /*    CONTEXT_GROUP => array('count' => 0, 'label' => 'G', 'name' => 'group'),*/
            CONTEXT_MODULE => array('count' => 0, 'label' => 'M', 'name' => 'module'),
            CONTEXT_SYSTEM => array('count' => 0, 'label' => 'S', 'name' => 'system'),
            CONTEXT_USER => array('count' => 0, 'label' => 'U', 'name' => 'user')
        );

        // Getting role name.
        $role = $this->getParameter('role')->getValue();
        $role = $DB->get_record('role', array('shortname' => $role));
          
        // Getting hosts.
        $hosts = array_keys($this->capabilities);
        $host_labels = get_available_platforms();

        // Getting capabilities.
        $records_capabilities = $DB->get_records('capabilities', null, '', 'name,contextlevel,component');

        // Getting lang.
        $lang = str_replace('_utf8', '', current_language());
        $strcapabilities = s(get_string('capabilities', 'role')); // 'Capabilities' MDL-11687

        // Getting all capabilities names.
        $capability_names = array();
        foreach ($this->capabilities as $platform_capabilities) {
            $platform_capabilities = array_keys((array) $platform_capabilities);
            $capability_names = array_merge($capability_names, $platform_capabilities);
        }
        $capability_names = array_unique($capability_names);
        // Getting problematic component name
        $problematic_component_name = get_string('problematiccomponent', 'vmoodleadminset_roles');

        // Creating normalized capabilities.
        $capabilities = array();
        foreach ($capability_names as $capability_name) {
            // Creating capability.
            $capability = new stdclass;
            $capability->name = $capability_name;

            // Initializing counters.
            $cap_permissions[CAP_ALLOW]['count'] = $cap_permissions[CAP_PREVENT]['count'] =
            $cap_permissions[CAP_PROHIBIT]['count'] = 0;
            $cap_contexts[CONTEXT_BLOCK]['count'] = $cap_contexts[CONTEXT_COURSE]['count'] =
            $cap_contexts[CONTEXT_COURSECAT]['count'] = /*$cap_contexts[CONTEXT_GROUP]['count'] =*/
            $cap_contexts[CONTEXT_MODULE]['count'] = $cap_contexts[CONTEXT_SYSTEM]['count'] =
            $cap_contexts[CONTEXT_USER]['count'] = 0;

            // Counting.
            foreach ($this->capabilities as $platform_capabilities) {
                if (!property_exists($platform_capabilities, $capability_name) || is_null($platform_capabilities->$capability_name)) {
                    continue;
                }
                $platform_capability = $platform_capabilities->$capability_name;
                $cap_permissions[$platform_capability->permission]['count']++;
                $cap_contexts[$platform_capability->contextlevel]['count']++;
            }

            // Getting major values.
            $nbr_value_max = max(array_map(array($this, '_getCounterValue'), $cap_permissions));
            $nbr_context_max = max(array_map(array($this, '_getCounterValue'), $cap_contexts));

            // Setting major permission.
            foreach ($cap_permissions as $permission => $cap_permission) {
                if ($cap_permission['count'] == $nbr_value_max) {
                    $capability->major_permission = $permission;
                    break;
                }
            }

            // Setting major contexlevel.
            foreach ($cap_contexts as $contextlevel => $cap_context) {
                if ($cap_context['count'] == $nbr_context_max) {
                    $capability->major_contextlevel = $contextlevel;
                    break;
                }
            }

            // Setting component.
            $capability->component = isset($records_capabilities[$capability_name]) ? $records_capabilities[$capability_name]->component : $problematic_component_name;

            // Setting capability contextlevel.
            $capability->contextlevel = isset($records_capabilities[$capability_name]) ? $records_capabilities[$capability_name]->contextlevel : CONTEXT_SYSTEM;

            // Adding capability.
            $capabilities[$capability_name] = $capability;
        }

        // Sort capabilities on contextlevel, component and name.
        uasort($capabilities, array($this, '_orderCapability'));

        /*
         * Creating html report.
         */

        // Creating header.
        $this->report = '<h3>'.get_string('comparerole', 'vmoodleadminset_roles', $role->name).help_button_vml('rolelib', 'rolecompare', 'rolecompare').'</h3>';

        // Adding edit role link.
        $this->report.= '<center><p>'.$OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/admin/roles/define.php', array('roleid' => $role->id, 'action' => 'edit')), get_string('editrole', 'vmoodleadminset_roles'), 'get').'</p></center>';

        // Creation form.
        $this->report.= '<form action="'.$CFG->wwwroot.'/blocks/vmoodle/plugins/roles/controller.rolelib.sadmin.php?what=syncrole" method="post" onsubmit="return validate_syncrole()"><input id="capability" type="hidden" name="capability" value=""/><input id="source_platform" type="hidden" name="source_platform" value=""/>';

        // Creating table.
        $this->report.= '<table id="rolecompare" cellspacing="1" cellpadding="5" class="generaltable boxaligncenter" style="min-width: 75%;"><tbody>';

        // Creating header.
        $this->report.= '<tr><th scope="col" class="header c0" style="vertical-align: bottom; text-align: left;">&nbsp</th>';
        $col = 1;
        foreach ($hosts as $host) {
            $this->report.= '<th id="cap_'.$col.'" scope="col" class="header c'.$col.'" style="vertical-align: bottom; text-align: center;"><label for="platform_'.$col.'"><img src="'.$CFG->wwwroot.'/blocks/vmoodle/plugins/roles/draw_platformname.php?caption='.urlencode($host_labels[$host]).'" alt="'.$host_labels[$host].'"/></label><br/><input id="platform_'.$col.'" type="checkbox" name="platforms[]" value="'.$host.'" disabled="disabled"/></th>';
            $col++;
        }
        $this->report.= '</tr>';

        // Initializing variables.
        $row = 0;
        $contextlevel = 0;
        $component = '';

        // Creating table data.
        foreach ($capabilities as $capability) {
            $col = 1;
            // Adding contextual heading.
            if (component_level_changed($capability, $component, $contextlevel)) {
                $this->report.= '<tr><td colspan="'.(count($hosts)+1).'" class="header"><strong>'.($capability->component == $problematic_component_name ? $problematic_component_name : get_component_string($capability->component, $capability->contextlevel)).'</strong></td></tr>';
            }

            // Recording context.
            $contextlevel = $capability->contextlevel;
            $component = $capability->component;
            $this->report.= '<tr class="r'.($row % 2).'"><td id="cap_0_'.$row.'" class="cell c0" style="vertical-align: middle; text-align: left;"><a onclick="this.target=\'docspopup\'" href="'.$CFG->docroot.'/'.$lang.'/'.$strcapabilities.'/'.$capability->name.'">'.get_capability_string($capability->name).'</a><br/>'.$capability->name.'</td>';

            foreach ($hosts as $host) {
                $extra_class = false;
                $title = get_capability_string($capability->name).' | '.$host_labels[$host];
                if (array_key_exists($host, $this->capabilities) && property_exists($this->capabilities[$host], $capability->name)) {
                    $platform_capability = $this->capabilities[$host]->{$capability->name};
                    if (is_null($platform_capability)) {
                        $cell = '<img src="'.$CFG->wwwroot.'/blocks/vmoodle/plugins/roles/pix/norolecapability.png" alt="No role capability" title="'.$title.'" onclick="setCapability('.$col.','.$row.',\''.$capability->name.'\',\''.$host.'\');"/>';
                    } else {
                        $cell = '<img src="'.$CFG->wwwroot.'/blocks/vmoodle/plugins/roles/pix/compare'.$cap_permissions[$platform_capability->permission]['label'].$cap_contexts[$platform_capability->contextlevel]['label'].'.png" alt="Permission: '.$cap_permissions[$platform_capability->permission]['name'].' | Context: '.$cap_contexts[$platform_capability->contextlevel]['name'].'" title="'.$title.'" onclick="setCapability('.$col.','.$row.',\''.$capability->name.'\',\''.$host.'\');"/>';
                        if ($platform_capability->permission != $capabilities[$platform_capability->capability]->major_permission) {
                            $extra_class = 'wrongvalue';
                        } elseif ($platform_capability->contextlevel != $capabilities[$platform_capability->capability]->major_contextlevel) {
                            $extra_class = 'wrongcontext';
                        }
                    }
                } else {
                    $cell = '<img src="'.$CFG->wwwroot.'/blocks/vmoodle/plugins/roles/pix/nocapability.png" alt="No capability" title="'.$title.'"/>';
                }
                $this->report.= '<td id="cap_'.$col.'_'.$row.'" class="cell c'.$col.($extra_class ? ' '.$extra_class : '').'" style="vertical-align: middle; text-align: center;" onmouseout="cellOut('.$col.','.$row.');" onmouseover="cellOver('.$col.','.$row.');">'.$cell.'</td>';
                $col++;
            }
            $this->report.= '</tr>';
            $row++;
        }

        // Closing table.
        $this->report.= '</tboby></table><br/><center><input type="submit" value="'.get_string('synchronize', 'vmoodleadminset_roles').'"/><div id="rolecompare_validation_message"></div></center></form><br/><br/>';
    }

    /**
     * Return counter value.
     * @param    $counter    array            The counter.
     * @return                int                The counter value.
     */
    private function _getCounterValue($counter) {
        return $counter['count'];
    }

    /**
     * Give an order to capabilities (on component, contextlevel then name).
     * @param    $cap1        object        The first capability to compare.
     * @param    $cap2        object        The second capability to compare.
     * @return                int            Return -1 if $cap1 is less than $cap2, 1 if more than $cap2, 0 otherwise.
     */
    private function _orderCapability($cap1, $cap2) {
        if (!($cmp = strcmp($cap1->component, $cap2->component))) {
            return $cmp;
        } elseif ($cap1->contextlevel < $cap2->contextlevel) {
            return -1;
        } elseif ($cap1->contextlevel > $cap2->contextlevel) {
            return 1;
        } else {
            return strcmp($cap1->name, $cap2->name);
        }
    }
}