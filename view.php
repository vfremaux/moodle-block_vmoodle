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
 * view.php
 * 
 * This file is the main page of vmoodle module which deals with
 * management et super-administration controlers.
 *
 * @package block-vmoodle
 * @category blocks
 */

// Adding requirements.

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/vmoodle/lib.php');
require_once($CFG->dirroot.'/blocks/vmoodle/debuglib.php');
require_once($CFG->dirroot.'/mnet/lib.php');

// Loading jQuery.
global $JQUERYVERSION;
if (empty($JQUERYVERSION)) {
    $JQUERYVERSION = '1.7.2';
    $PAGE->requires->js('/blocks/vmoodle/js/lib/jquery-1.7.2.min.js');
}

// Loading javascript files.

$PAGE->requires->js('/blocks/vmoodle/js/strings.php');
$PAGE->requires->js ('/blocks/vmoodle/js/target_choice.js');
$PAGE->requires->js('/blocks/vmoodle/js/management.js');

$PAGE->requires->css ('/blocks/vmoodle/theme/styles.php');

// Report dead end trap.
// Checking if command were executed and return back to idle state.

if ((@$SESSION->vmoodle_sa['wizardnow'] == 'report')
        && !(isset($SESSION->vmoodle_sa['command'])
             && ($command = unserialize($SESSION->vmoodle_sa['command']))
                && $command->isRunned())) {
    $SESSION->vmoodle_sa['wizardnow'] = 'commandchoice';
    redirect($CFG->wwwroot.'/blocks/vmoodle/view.php?view=sadmin');
}

// Declaring parameters.

$view = optional_param('view', 'management', PARAM_TEXT);
$action = optional_param('what', '', PARAM_TEXT);

// Security.

$system_context = context_system::instance();
require_login();
require_capability('block/vmoodle:managevmoodles', $system_context);

$plugins = get_list_of_plugins('/blocks/vmoodle/plugins');
foreach ($plugins as $plugin) {
    if (file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/js/strings.php')) {
        $js_file = '/blocks/vmoodle/plugins/'.$plugin.'/js/strings.php';
        $PAGE->requires->js($js_file);
    }

    foreach (glob($CFG->dirroot.'/blocks/vmoodle/plugins/'.$plugin.'/js/*.js') as $file) {
         $PAGE->requires->js( str_replace($CFG->dirroot, '', $file));
    }
}

// Printing headers.

$strtitle = get_string('vmoodlemanager', 'block_vmoodle');

$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/vmoodle/theme/styles.php';

// Generating header.

ob_start();
$PAGE->set_context($system_context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strtitle,'view.php?view='.$view,'misc');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');

$url = new moodle_url('/blocks/vmoodle/view.php');
$PAGE->set_url($url,array('view' => $view,'what' => $action));

// Capturing action.

if ($action != '') {
    try {
        switch ($view) {
            case 'management': {
                $result = include 'controller.management.php';
            }
            break;
            case 'sadmin': {
                $result = include 'controller.sadmin.php';
            }
            break;
            case 'services': {
                $result = include 'controller.services.php';
            }
            break;
            default: {
                $result = -1;
            }
        }
        if ($result == -1) {
            echo $OUTPUT->footer();
            exit();
        }
    }
    catch (Exception $e) {
        echo $OUTPUT->header(); 
        echo $OUTPUT->notification($e->getMessage());
        echo $OUTPUT->footer();
        exit();
    }
}

echo $OUTPUT->header();

// Adding heading.

echo $OUTPUT->heading(get_string('vmoodleadministration', 'block_vmoodle'));

// Adding tabs.

$tabname = get_string('tabpoolmanage', 'block_vmoodle');
$row[] = new tabobject('management', $CFG->wwwroot."/blocks/vmoodle/view.php?view=management", $tabname);
$tabname = get_string('tabpoolsadmin', 'block_vmoodle');
$row[] = new tabobject('sadmin', $CFG->wwwroot."/blocks/vmoodle/view.php?view=sadmin", $tabname);
$tabname = get_string('tabpoolservices', 'block_vmoodle');
$row[] = new tabobject('services', $CFG->wwwroot."/blocks/vmoodle/view.php?view=services", $tabname);
$tabrows[] = $row;
print_tabs($tabrows, $view);

// Displaying headers.

ob_end_flush();

// Including contents.

switch($view) {
    case 'management': {
        include $CFG->dirroot.'/blocks/vmoodle/views/management.main.php';
    }
    break;
    case 'sadmin': {
        include $CFG->dirroot.'/blocks/vmoodle/views/sadmin.main.php';
    }
    break;
    case 'services': {
        include $CFG->dirroot.'/blocks/vmoodle/views/services.main.php';
    }
    break;
}

echo $OUTPUT->footer();