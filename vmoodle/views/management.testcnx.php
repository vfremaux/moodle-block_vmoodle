<?php
/**
 * Tests database connection.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading $CFG configuration.
require('../../../config.php');
require_once $CFG->dirroot.'/blocks/vmoodle/bootlib.php';

// Retrieve parameters for database connection test.
$database = new stdClass;
$database->vdbtype	= required_param('vdbtype');
$database->vdbhost	= required_param('vdbhost');
$database->vdblogin	= required_param('vdblogin');
$database->vdbpass	= required_param('vdbpass');

// Works, but need to improve the style...
if(vmoodle_make_connection($database, false)) {
	echo(get_string('connectionok', 'block_vmoodle'));
} else {
	echo(get_string('badconnection', 'block_vmoodle'));
}
