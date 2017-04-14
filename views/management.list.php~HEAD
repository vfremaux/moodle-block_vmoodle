<?php

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
	notify($SESSION->vmoodle_ma['confirm_message']->message, $SESSION->vmoodle_ma['confirm_message']->style);
	echo '<br/>';
	unset($SESSION->vmoodle_ma['confirm_message']);
}

// Retrieves all virtuals hosts.
$vmoodles = get_records('block_vmoodle', '', '', 'name,enabled');

// If one or more virtual hosts exists.
if ($vmoodles){
	$strname = get_string('name');
	$strhost = get_string('vhostname', 'block_vmoodle');
	$strstatus = get_string('status', 'block_vmoodle');
	$strmnet = get_string('mnet', 'block_vmoodle');
	$strlastcron = get_string('lastcron', 'block_vmoodle');
	$strlastcrongap = get_string('lastcrongap', 'block_vmoodle');
	$strcrons = get_string('crons', 'block_vmoodle');
	$strcmds = get_string('commands', 'block_vmoodle');
	$table->head = array("<b>$strname</b>","<b>$strhost</b>","<b>$strstatus</b>","<b>$strmnet</b>","<b>$strcrons</b>","<b>$strlastcron</b>","<b>$strlastcrongap</b>","<b>$strcmds</b>");
	$table->align = array ('LEFT', 'LEFT', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
	$table->size = array('20%', '30%', '11%', '10%', '8%', '8%', '8%', '10%');
	$table->width = '98%';

	foreach($vmoodles as $vmoodle){
		$vmoodlecmd	=	'';
		if($vmoodle->enabled == 1){
			$vmoodlecmd .= "<a href=\"view.php?view=management&amp;what=edit&amp;id={$vmoodle->id}\"><img src=\"{$CFG->pixpath}/t/edit.gif\" title=\"".get_string('edithost', 'block_vmoodle')."\" /></a>";
			$vmoodlecmd .= " <a href=\"view.php?view=management&amp;what=delete&amp;id={$vmoodle->id}\" onclick=\"return confirm('".get_string('confirmdelete', 'block_vmoodle')."');\"><img src=\"{$CFG->pixpath}/t/delete.gif\" title=\"".get_string('deletehost', 'block_vmoodle')."\" /></a>";
		}
		$vmoodlecmd .= " <a href=\"view.php?view=management&amp;what=snapshot&amp;wwwroot={$vmoodle->vhostname}\"><img src=\"{$CFG->wwwroot}/blocks/vmoodle/pix/snapshot.gif\" title=\"".get_string('snapshothost', 'block_vmoodle')."\" /></a>";
		$vmoodlestatus = vmoodle_print_status($vmoodle, true);
		$strmnet = $vmoodle->mnet;
		if($strmnet < 0)
			$strmnet = get_string('mnetdisabled', 'block_vmoodle');
		else if ($strmnet == 0)
			$strmnet = get_string('mnetfree', 'block_vmoodle');
		$vmoodlelnk = "<a href=\"$vmoodle->vhostname\" target=\"_blank\" >$vmoodle->name</a>";
		
		$crongap = ($vmoodle->lastcrongap > DAYSECS) ? "<span style=\"color:red\">$vmoodle->lastcrongap s.</span>" : $vmoodle->lastcrongap ." s.";
		
		$table->data[] = array($vmoodlelnk, $vmoodle->vhostname, $vmoodlestatus, $strmnet, $vmoodle->croncount, userdate($vmoodle->lastcron), $crongap, $vmoodlecmd);
	}

	echo '<center>';
	print_table($table);
}
// If no virtual hosts.
else {
	echo '<center>';
	print_box(get_string('novmoodles', 'block_vmoodle'));
	print_single_button('view.php', array('view' => 'management', 'what' => 'snapshot', 'wwwroot' => $CFG->wwwroot), get_string('snapshotmaster', 'block_vmoodle'));
}

// Displays buttons for adding a new virtual host and renewing all keys. 
echo '<br/>';
$templates = vmoodle_get_available_templates();
if(empty($templates)) {
	print_single_button('view.php', array('view' => 'management', 'what' => 'add'), get_string('notemplates', 'block_vmoodle'), 'get', null, null, null, true);
}
else {
	print_single_button('view.php', array('view' => 'management', 'what' => 'add'), get_string('addvmoodle', 'block_vmoodle'));
}
echo '<br/>';
print_single_button('view.php', array('view' => 'management', 'what' => 'renewall'), get_string('renewallbindings', 'block_vmoodle'));
echo '</center>';