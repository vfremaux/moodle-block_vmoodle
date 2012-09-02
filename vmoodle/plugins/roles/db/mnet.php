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
 * This file contains the mnet services for the user_mnet_host plugin
 *
 * @since 2.0
 * @package blocks
 * @subpackage vmoodle
 * @copyright 2012 Valery Fremaux
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$publishes = array(
    'mnetadmin' => array(
		'servicename' => 'mnetadmin',
		'description' => get_string('mnetadmin_name', 'block_vmoodle'),
        'apiversion' => 1,
        'classname'  => '',
        'filename'   => 'rpclib.php',
        'methods'    => array(
            'mnetadmin_rpc_get_role_capabilities',
            'mnetadmin_rpc_set_role_capabilities',
            'mnetadmin_rpc_has_role',
            'mnetadmin_rpc_assign_role',
            'mnetadmin_rpc_user_exists',
            'mnetadmin_rpc_create_user',
            'mnetadmin_rpc_remote_enrol'        
        ),
    ),
);
$subscribes = array(
    'mnetadmin' => array(
        'mnetadmin_rpc_get_role_capabilities' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_get_role_capabilities',
        'mnetadmin_rpc_set_role_capabilities' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_set_role_capabilities',
        'mnetadmin_rpc_has_role' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_has_role',
        'mnetadmin_rpc_assign_role' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_assign_role',
        'mnetadmin_rpc_user_exists' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_user_exists',
        'mnetadmin_rpc_create_user' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_create_user',
        'mnetadmin_rpc_remote_enrol' => 'blocks/vmoodle/plugins/roles/rpclib.php/mnetadmin_rpc_remote_enrol',
    ),
);
