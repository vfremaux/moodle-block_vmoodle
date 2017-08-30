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
 * Redirection to a certain page of Vmoodle management.
 *
 * @package block_vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

// Check status of previous action
if (isset($SESSION->vmoodle_ma['confirm_message'])) {
    if (is_object($SESSION->vmoodle_ma['confirm_message'])) {
        echo $OUTPUT->notification($SESSION->vmoodle_ma['confirm_message']->message, $SESSION->vmoodle_ma['confirm_message']->style);
    } else {
        echo $OUTPUT->notification($SESSION->vmoodle_ma['confirm_message']);
    }
    echo '<br/>';
    unset($SESSION->vmoodle_ma['confirm_message']);
}

// if controller results, print them
if (!empty($controllerresult)) {
    echo '<pre>';
    echo $controllerresult;
    echo '</pre>';
}

$page = optional_param('vpage', 0, PARAM_INT);
$perpage = 35;

// Retrieves all virtuals hosts.
$totalcount = $DB->count_records('block_vmoodle', array());
$vmoodles = $DB->get_records('block_vmoodle', null, 'name,enabled', '*', $page * $perpage, $perpage);

// If one or more virtual hosts exists.
if ($vmoodles) {
    $strname = get_string('name');
    $strhost = get_string('vhostname', 'block_vmoodle');
    $strstatus = get_string('status', 'block_vmoodle');
    $strmnet = get_string('mnet', 'block_vmoodle');
    $strlastcron = get_string('lastcron', 'block_vmoodle');
    $strlastcrongap = get_string('lastcrongap', 'block_vmoodle');
    $strcrons = get_string('crons', 'block_vmoodle');
    $strcmds = get_string('commands', 'block_vmoodle');

    // Defining html table.
    $table = new html_table();
    $table->head = array('', "<b>$strname</b>","<b>$strhost</b>","<b>$strstatus</b>","<b>$strmnet</b>","<b>$strcrons</b>","<b>$strlastcron</b>","<b>$strlastcrongap</b>","<b>$strcmds</b>");
    $table->align = array ('CENTER', 'LEFT', 'LEFT', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
    $table->size = array('2%', '20%', '30%', '11%', '10%', '8%', '8%', '8%', '8%');
    $table->width = '98%';

    foreach ($vmoodles as $vmoodle) {

        $vmoodlecheck = '<input type="checkbox" name="vmoodleids[]" value="'.$vmoodle->id.'" />';

        $vmoodlecmd = '';
        $editurl = new moodle_url('/blocks/vmoodle/view.php', array('view' => 'management', 'what' => 'edit', 'id' => $vmoodle->id));
        $pix = $OUTPUT->pix_url('t/edit','core');
        $label = get_string('edithost', 'block_vmoodle');
        $vmoodlecmd .= '<a href="'.$editurl.'"><img src="'.$pix.'" title="'.$label.'" /></a>';

        if ($vmoodle->enabled == 1) {
            $deleteurl = new moodle_url('/blocks/vmoodle/view.php', array('view' => 'management', 'what' => 'delete', 'id' => $vmoodle->id));
            $pix = $OUTPUT->pix_url('t/delete');
            $label = get_string('deletehost', 'block_vmoodle');
            $vmoodlecmd .= '&nbsp;<a href="'.$deleteurl.'" onclick="return confirm(\''.get_string('confirmdelete', 'block_vmoodle').'\');"><img src="'.$pix.'" title="'.$label.'" /></a>';
        } else {
            $fulldeleteurl = new moodle_url('/blocks/vmoodle/view.php', array('view' => 'management', 'what' => 'fulldelete', 'id' => $vmoodle->id));
            $pix = $OUTPUT->pix_url('t/delete');
            $label = get_string('fulldeletehost', 'block_vmoodle');
            $vmoodlecmd .= '&nbsp;<a href="'.$fulldeleteurl.'" onclick="return confirm(\''.get_string('confirmfulldelete', 'block_vmoodle').'\');"><img src="'.$pix.'" title="'.$label.'" /></a>';
        }

        $snapurl = new moodle_url('/blocks/vmoodle/view.php', array('view' => 'management', 'what' => 'snapshot', 'wwwroot' => $vmoodle->vhostname));
        $pix = $OUTPUT->pix_url('snapshot', 'block_vmoodle');
        $label = get_string('snapshothost', 'block_vmoodle');
        $vmoodlecmd .= '&nbsp;<a href="'.$snapurl.'"><img src="'.$pix.'" title="'.$label.'" /></a>';
        $vmoodlestatus = vmoodle_print_status($vmoodle, true);
        $strmnet = $vmoodle->mnet;
        if ($strmnet < 0) {
            $strmnet = get_string('mnetdisabled', 'block_vmoodle');
        } else if ($strmnet == 0) {
            $strmnet = get_string('mnetfree', 'block_vmoodle');
        }

        $auth = is_enabled_auth('multimnet') ? 'multimnet' : 'mnet';
        $jumpurl = new moodle_url('/auth/'.$auth.'/jump.php', array('hostwwwroot' => $vmoodle->vhostname));
        $vmoodlelnk = '<a href="'.$jumpurl.'" target="_blank" >'.$vmoodle->name.'</a>';

        $hostlnk = "<a href=\"{$vmoodle->vhostname}\" target=\"_blank\">{$vmoodle->vhostname}</a>";
        $crongap = ($vmoodle->lastcrongap > DAYSECS) ? "<span style=\"color:red\">$vmoodle->lastcrongap s.</span>" : $vmoodle->lastcrongap ." s.";

        $table->data[] = array($vmoodlecheck, $vmoodlelnk, $hostlnk, $vmoodlestatus, $strmnet, $vmoodle->croncount, userdate($vmoodle->lastcron), $crongap, $vmoodlecmd);
    }

    $returnurl = new moodle_url('/blocks/vmoodle/view.php', array('view' => $view, 'what' => $action));

    echo '<center>';
    echo '<p>'.$OUTPUT->paging_bar($totalcount, $page, $perpage, $returnurl, 'vpage').'</p>';
    echo '<form name="vmoodlesform" action="'.$returnurl.'" method="POST" >';
    echo html_writer::table($table);

    echo '<div class="vmoodle-group-cmd">';
    print_string('withselection', 'block_vmoodle');
    $cmdoptions = array(
        'enableinstances' => get_string('enableinstances', 'block_vmoodle'),
        'disableinstances' => get_string('disableinstances', 'block_vmoodle'),
        'deleteinstances' => get_string('deleteinstances', 'block_vmoodle'),
    );
    echo html_writer::select($cmdoptions, 'what', '', array('' => 'choosedots'), array('onchange' => 'return vmoodle_manager_confirm(this, \''.get_string('deleteconfirm', 'block_vmoodle').'\');'));
    echo '</div>';
    echo '</form>';
    echo '</center>';
} else {
    echo $OUTPUT->box(get_string('novmoodles', 'block_vmoodle'));
}

$params = array('view' => 'management', 'what' => 'snapshot', 'wwwroot' => $CFG->wwwroot);
echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/view.php', $params), get_string('snapshotmaster', 'block_vmoodle'), 'get');

// Displays buttons for adding a new virtual host and renewing all keys.

echo '<br/>';

$templates = vmoodle_get_available_templates();
$params = array('view' => 'management', 'what' => 'add');
if (empty($templates)) {
    echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/view.php', $params), get_string('notemplates', 'block_vmoodle'), 'get', array('tooltip' => null, 'disabled' => true));
} else {
    echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/view.php', $params), get_string('addvmoodle', 'block_vmoodle'), 'get');
}

echo '<br/>';
echo '<div class="vmoodle-tools-row">';
echo '<div class="vmoodle-tool">';
$params = array('view' => 'management', 'what' => 'generateconfigs');
echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/view.php', $params), get_string('generateconfigs', 'block_vmoodle'), 'get');
echo '</div>';
echo '<div class="vmoodle-tool">';
echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/tools/generatecopyscripts.php', $params), get_string('generatecopyscripts', 'block_vmoodle'), 'get');
echo '</div>';
echo '<div class="vmoodle-tool">';
echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/tools/generatecustomscripts.php', $params), get_string('generatecustomscripts', 'block_vmoodle'), 'get');
echo '</div>';
echo '<div class="vmoodle-tool">';
$params = array('view' => 'management', 'what' => 'renewall');
echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/view.php', $params), get_string('renewallbindings', 'block_vmoodle'), 'get');
echo '</div>';
echo '<div class="vmoodle-tool">';
echo $OUTPUT->single_button(new moodle_url('/blocks/vmoodle/vcron.php'), get_string('runvcron', 'block_vmoodle'), 'get');
echo '</div>';
echo '</div>';
echo '</center>';
