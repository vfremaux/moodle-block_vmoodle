<?php

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
 * @version Moodle 2.2
 **
 * VMoodle configuration
 * Must point to a VMaster server
 *
 * Please take case VMOODLE block needs two important patchs to be completely installed :
 *
 * Patch one : Adding XML-RPC support for blocks (mnet/xmlrpc/server.php)
 * Patch two : Adding blocks subplugin discovery support (calling upgrade_subplugins generic function in blocklib.php)
 * Information in the README file.
 *
 */

if ((defined('CLI_SCRIPT') && CLI_SCRIPT) && !defined('WEB_CRON_EMULATED_CLI') && !defined('CLI_VMOODLE_OVERRIDE')) return;
require_once $CFG->dirroot."/blocks/vmoodle/bootlib.php";

// This configurations settings will tell where VMoodle can rely on a blocks_vmoodle virtual moodling
// records.

$CFG->vmasterdbhost = 'localhost';
$CFG->vmasterdbtype = 'mysqli';
$CFG->vmasterdbname = 'moodle27_generic';
$CFG->vmasterdblogin = 'root';
$CFG->vmasterdbpass = 'spr1ngb0ks';
$CFG->vmasterdbpersist =  false;
$CFG->vmasterprefix    = 'mdl_';
$CFG->vmoodledefault    = 1; // tells if the default physical config can be used as true host
$CFG->vlogfilepattern  = '/var/log/moodle/%%VHOSTNAME%%.log';

vmoodle_get_hostname();

// TODO : insert customized additional code here if required

vmoodle_boot_configuration();

// refresh trace location to reflect virtual change
// $CFG->trace     = $CFG->dataroot.'/trace.log';

