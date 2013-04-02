<?php
/**
 * The first step of wizard.
 * Displays all assisted commands.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

	// Loading the library
	require_once(VMOODLE_CLASSES_DIR.'Command_Form.class.php');

	// Retrieving configuration files
	$assistedcommands_conffiles = glob($CFG->dirroot.'/blocks/vmoodle/plugins/*/config.php');
	// Reading categories
	$assistedcommands_categories = array();
	foreach ($assistedcommands_conffiles as $conffile) {
		$path = explode('/', $conffile);
		$assistedcommands_category = $path[count($path)-2];
		if ($assistedcommands_category[0] != '_')
			$assistedcommands_categories[] = $assistedcommands_category;
	}
	// Displaying commands categories
	foreach ($assistedcommands_categories as $key => $category) {		
		// Reading commands
		try {
			$vmoodle_category = load_vmplugin($category);
			// Displaying a command's form
			print_collapsable_bloc_start($vmoodle_category->getPluginName(), $vmoodle_category->getName(), null, false);
			foreach ($vmoodle_category->getCommands() as $command) {
				$command_form = new Vmoodle_Command_Form($command, Vmoodle_Command_Form::MODE_COMMAND_CHOICE);
				$command_form->display();
			}
			print_collapsable_block_end();
		}
		catch(Exception $vce) {
			echo $OUTPUT->notification($vce->getMessage());
		}
	}
	// Display link to the advanced mode
	echo '<br/><center>';
	echo $OUTPUT->single_button(new moodle_url('view.php', array('view' => 'sadmin', 'what' => 'switchtoadvancedcommand')), get_string('advancedmode', 'block_vmoodle'), 'get');
	echo '<br/>';
	echo $OUTPUT->single_button('admin.php', get_string('administration', 'block_vmoodle'), 'get');
	echo '</center>';