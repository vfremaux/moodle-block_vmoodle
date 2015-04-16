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
 * This is a fake alternative virtual configuration that must be included before calling to
 * lib/setup.php in master configuration.
 *
 * The VMASTER host must point to a Moodle setup that holds the effective vmoodle block
 * holding the virtual configs. The basic configuration uses the same configuration
 * values as the original one (the configuration from config.php). Say, the physical
 * moodle is also the master of the virtual system.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * VMoodle configuration
 * Must point to a VMaster server
 *
 * Please take case VMOODLE block needs two important patchs to be completely installed :
 *
 * Patch one : Adding XML-RPC support for blocks (mnet/xmlrpc/server.php)
 * Patch two (moodle < 2.6) : Adding blocks subplugin discovery support (calling upgrade_subplugins generic function in blocklib.php)
 * Information in the README file.
 *
 */

if ((defined('CLI_SCRIPT') && CLI_SCRIPT) && !defined('WEB_CRON_EMULATED_CLI') && !defined('CLI_VMOODLE_OVERRIDE')) return;
require_once $CFG->dirroot."/blocks/vmoodle/bootlib.php";

// EDIT A CONFIGURATION FOR MASTER MOODLE

$CFG->vmasterdbhost = 'localhost';
$CFG->vmasterdbtype = 'mysqli';
$CFG->vmasterdbname = '';
$CFG->vmasterdblogin = '';
$CFG->vmasterdbpass = '';
$CFG->vmasterdbpersist =  false;
$CFG->vmasterprefix    = 'mdl_';
$CFG->vmoodledefault    = 1; // Tells if the default physical config can be used as true host.

vmoodle_get_hostname();

// TODO : insert customized additional code here if required


//

vmoodle_boot_configuration();
