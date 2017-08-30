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
 * Web service local plugin template external functions and service definitions.
 *
 * @package block_vmoodle
 * @copyright 2016 Valery Fremaux
 * @license
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(
        'block_vmoodle_add_vmoodle_instance' => array(
                'classname' => 'block_vmoodle_external',
                'methodname' => 'add_vmoodle_instance',
                'classpath' => 'blocks/vmoodle/externallib.php',
                'description' => '',
                'type' => 'write',
        )

);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'vmoodle_services' => array(
                'functions' => array ('block_vmoodle_addd_vmoodle_instance'),
                'restrictedusers' => 0,
                'enabled' => 1,
        )
);
