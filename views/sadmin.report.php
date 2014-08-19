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
 * The final step of wizard.
 * Displays report of command command.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Adding requirements.
require_once($CFG->dirroot.'/blocks/vmoodle/rpclib.php');

// Getting platforms.
$platforms = $SESSION->vmoodle_sa['platforms'];

// Checking commands' states.
$successfull_platforms = array();
$failed_platforms = array();
foreach ($platforms as $host => $platform) {
    if ($command->getResult($host, 'status') == RPC_SUCCESS) {
        $successfull_platforms[$host] = $platform;
    } else {
        $failed_platforms[$host] = $platform;
    }
}

// Displaying general result.
if (!is_null($command->getResult())) {
    echo $command->getResult();
}

// Displaying successfull commands.
$i = 0;
if (!empty($successfull_platforms)) {
    echo '<table width="95%" cellspacing="1" cellpadding="5" class="generaltable boxaligncenter">' .
            '<tbody>' .
                '<tr>' .
                    '<th scope="col" class="header c0" style="vertical-align: top; text-align: left; width: 20%; white-space: nowrap;" colspan="2"><b>'.get_string('successfullplatforms', 'block_vmoodle').'</b></th>' .
                '</tr>';
    foreach ($successfull_platforms as $host => $platform) {
        echo '<tr class="r'.$i.'">' .
                '<td><b>'.$platform.'</b></td>' .
                '<td>'.get_string('rpcstatus'.$command->getResult($host, 'status'), 'block_vmoodle').'</td>' .
                '<td style="width: 25%;">'.
                $command->getResult($host, 'message').
                '</td>'.
            '</tr>';
        $i = ($i+1)%2;
    }
    echo '</tbody>' .
        '</table><br/>';
}

// Displaying failed commands.
$i = 0;
if (!empty($failed_platforms)) {
    echo '<table width="95%" cellspacing="1" cellpadding="5" class="generaltable boxaligncenter">' .
            '<tbody>' .
                '<tr>' .
                    '<th scope="col" class="header c0" style="vertical-align: top; text-align: left; width: 20%; white-space: nowrap;" colspan="3"><b>'.get_string('failedplatforms', 'block_vmoodle').'</b></th>' .
                '</tr>';
    foreach ($failed_platforms as $host => $platform) {
        echo '<tr class="r'.$i.'">' .
                '<td><b>'.$platform.'</b></td>' .
                '<td style="text-align: left;">'.get_string('rpcstatus'.$command->getResult($host, 'status'), 'block_vmoodle').'</td>' .
                '<td style="width: 25%;">';
        if ($command->getResult($host, 'status') > 200 && $command->getResult($host, 'status') < 520) {
            echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin', 'what' => 'runcmdagain', 'platform' => urlencode($host))), get_string('runcmdagain', 'block_vmoodle'), 'get');
        } else {
            echo '&nbsp;';
        }
        echo     '</td>' .
            '</tr>' .
            '<tr class="r'.$i.'" valign="top">' .
                '<td>'.get_string('details', 'block_vmoodle').'</td>' .
                '<td colspan="2">'.implode('<br/>', $command->getResult($host, 'errors')).'</td>' .
            '</tr>';
        $i = ($i+1)%2;
    }
    echo '</tbody>' .
        '</table><br/>';
}

// Displaying controls.
echo '<center>';
echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin', 'what' => 'runotherpfm')), get_string('runotherplatforms', 'block_vmoodle'), 'get');
echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin', 'what' => 'runothercmd')), get_string('runothercommand', 'block_vmoodle'), 'get');
echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin', 'what' => 'newcommand')), get_string('runnewcommand', 'block_vmoodle'), 'get');
echo '</center>';
