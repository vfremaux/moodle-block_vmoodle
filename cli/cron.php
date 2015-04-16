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
 * CLI cron
 *
 * This script looks through all the module directories for cron.php files
 * and runs them.  These files can contain cleanup functions, email functions
 * or anything that needs to be run on a regular basis.
 * this script will override a virtual Moodle identity on base of an input parameter
 *
 * @package    core
 * @subpackage cli
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
global $CLI_VMOODLE_PRECHECK;

$CLI_VMOODLE_PRECHECK = true; // Force first config to be minimal.

// Config preload to get real roots.
require('../../../config.php');
require_once($CFG->dirroot.'/lib/clilib.php');      // cli only functions
require_once($CFG->dirroot.'/lib/cronlib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help' => false, 'host' => true),
                                               array('h' => 'help', 'H' => 'host'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute periodic cron actions.

Options:
-h, --help            Print out this help
-H, --host            The host name to work for

Example:
\$sudo -u www-data /usr/bin/php admin/cli/cron.php
";

    echo $help;
    die;
}

if (!empty($options['host'])) {
    // arms the vmoodle switching
    echo('Arming for '.$options['host']); // mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.
require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
echo('Config check : playing for '.$CFG->wwwroot);

cron_run();
