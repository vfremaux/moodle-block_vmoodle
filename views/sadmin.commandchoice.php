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
 * The first step of wizard.
 * Displays all assisted commands.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 
require_once $CFG->dirroot.'/blocks/vmoodle/classes/commands/Command_Form.php';

// Retrieving configuration files.
$assistedcommands_conffiles = glob($CFG->dirroot.'/blocks/vmoodle/plugins/*/config.php');

// Reading categories.
$assistedcommands_categories = array();
foreach ($assistedcommands_conffiles as $conffile) {
    $path = explode('/', $conffile);
    $assistedcommands_category = $path[count($path)-2];
    if ($assistedcommands_category[0] != '_') {
        $assistedcommands_categories[] = $assistedcommands_category;
    }
}

// Displaying commands categories.
foreach ($assistedcommands_categories as $key => $category) {
    // Reading commands
    try {
        $vmoodle_category = load_vmplugin($category);
        // Displaying a command's form

        print_collapsable_bloc_start($vmoodle_category->getPluginName(), $vmoodle_category->getName(), null, false);
        foreach ($vmoodle_category->getCommands() as $command) {
            $command_form = new Command_Form($command, Command_Form::MODE_COMMAND_CHOICE);
            $command_form->display();
        }
        print_collapsable_block_end();
    } catch (Exception $vce) {
        print_collapsable_block_end();
        echo $OUTPUT->notification($vce->getMessage());
    }
}

// Display link to the advanced mode.
echo '<br/><center>';
$btitle = get_string('advancedmode', 'block_vmoodle');
echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin', 'what' => 'switchtoadvancedcommand')), $btitle, 'get');

echo '<br/>';

$btitle = get_string('administration', 'block_vmoodle');
echo $OUTPUT->single_button(new moodle_url('admin.php'), $btitle, 'get');
echo '</center>';