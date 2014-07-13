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
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Check status of previous action
if (isset($SESSION->vmoodle_ma['confirm_message'])) {
    echo $OUTPUT->notification($SESSION->vmoodle_ma['confirm_message']->message, $SESSION->vmoodle_ma['confirm_message']->style);
    echo '<br/>';
    unset($SESSION->vmoodle_ma['confirm_message']);
}

// if controller results, print them
if (!empty($controllerresult)){
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

    // Defining html table
    $table = new html_table();
    $table->head = array('', "<b>$strname</b>","<b>$strhost</b>","<b>$strstatus</b>","<b>$strmnet</b>","<b>$strcrons</b>","<b>$strlastcron</b>","<b>$strlastcrongap</b>","<b>$strcmds</b>");
    $table->align = array ('CENTER', 'LEFT', 'LEFT', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
    $table->size = array('2%', '20%', '30%', '11%', '10%', '8%', '8%', '8%', '8%');
    $table->width = '98%';

    foreach ($vmoodles as $vmoodle) {

        $vmoodlecheck = '<input type="checkbox" name="vmoodleids[]" value="'.$vmoodle->id.'" />';

        $vmoodlecmd = '';
        $vmoodlecmd .= "<a href=\"view.php?view=management&amp;what=edit&amp;id={$vmoodle->id}\"><img src=\"{$OUTPUT->pix_url('t/edit','core')}\" title=\"".get_string('edithost', 'block_vmoodle')."\" /></a>";
        if ($vmoodle->enabled == 1) {

            $vmoodlecmd .= " <a href=\"view.php?view=management&amp;what=delete&amp;id={$vmoodle->id}\" onclick=\"return confirm('".get_string('confirmdelete', 'block_vmoodle')."');\"><img src=\"{$OUTPUT->pix_url('t/delete')}\" title=\"".get_string('deletehost', 'block_vmoodle')."\" /></a>";
        } else {
            $vmoodlecmd .= " <a href=\"view.php?view=management&amp;what=fulldelete&amp;id={$vmoodle->id}\" onclick=\"return confirm('".get_string('confirmfulldelete', 'block_vmoodle')."');\"><img src=\"{$OUTPUT->pix_url('t/delete')}\" title=\"".get_string('fulldeletehost', 'block_vmoodle')."\" /></a>";
        }
        $vmoodlecmd .= " <a href=\"view.php?view=management&amp;what=snapshot&amp;wwwroot={$vmoodle->vhostname}\"><img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/snapshot.gif\" title=\"".get_string('snapshothost', 'block_vmoodle')."\" /></a>";
        $vmoodlestatus = vmoodle_print_status($vmoodle, true);
        $strmnet = $vmoodle->mnet;
        if ($strmnet < 0) {
            $strmnet = get_string('mnetdisabled', 'block_vmoodle');
        } else if ($strmnet == 0) {
            $strmnet = get_string('mnetfree', 'block_vmoodle');
        }
        $vmoodlelnk = "<a href=\"{$CFG->wwwroot}/auth/mnet/jump.php?hostwwwroot=".urlencode($vmoodle->vhostname)."\" target=\"_blank\" >$vmoodle->name</a>";
        $hostlnk = "<a href=\"{$vmoodle->vhostname}\" target=\"_blank\">{$vmoodle->vhostname}</a>";
        $crongap = ($vmoodle->lastcrongap > DAYSECS) ? "<span style=\"color:red\">$vmoodle->lastcrongap s.</span>" : $vmoodle->lastcrongap ." s.";

        $table->data[] = array($vmoodlecheck, $vmoodlelnk, $hostlnk, $vmoodlestatus, $strmnet, $vmoodle->croncount, userdate($vmoodle->lastcron), $crongap, $vmoodlecmd);
    }

    $returnurl = new moodle_url('/blocks/vmoodle/view.php', array('view' => $view,'what' => $action));

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

echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'management', 'what' => 'snapshot', 'wwwroot' => $CFG->wwwroot)), get_string('snapshotmaster', 'block_vmoodle'), 'get');

// Displays buttons for adding a new virtual host and renewing all keys.

echo '<br/>';

$templates = vmoodle_get_available_templates();
if (empty($templates)) {
    echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'management', 'what' => 'add')), get_string('notemplates', 'block_vmoodle'), 'get', array('tooltip' => null, 'disabled' => true));
} else {
    echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'management', 'what' => 'add')), get_string('addvmoodle', 'block_vmoodle'), 'get');
}

echo '<br/>';
echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'management', 'what' => 'generateconfigs')), get_string('generateconfigs', 'block_vmoodle'), 'get');
echo '<br/>';
echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'management', 'what' => 'renewall')), get_string('renewallbindings', 'block_vmoodle'), 'get');
echo '<br/>';
echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/blocks/vmoodle/vcron.php'), get_string('runvcron', 'block_vmoodle'), 'get');
echo '</center>';
