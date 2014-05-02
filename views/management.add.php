<?php
/**
 * Form for adding a virtual host.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading the library.
require_once(VMOODLE_CLASSES_DIR.'Host_Form.class.php');

// Print title (heading).
echo $OUTPUT->heading(get_string('newvmoodle', 'block_vmoodle'));

// Print beginning of a box.
echo $OUTPUT->box_start();

// Displays the form.
if (isset($SESSION->vmoodle_mg['dataform'])) {
	$platform_form = new Vmoodle_Host_Form('add', $SESSION->vmoodle_mg['dataform']);
} else {
	$platform_form = new Vmoodle_Host_Form('add', null);

	if ($CFG->block_vmoodle_automatedschema)
		if ($CFG->block_vmoodle_mnet == 'NEW'){
			$lastsubnetwork = $DB->get_field('block_vmoodle', 'MAX(mnet)', array());
			$formdata->mnet = $lastsubnetwork + 1;
		} else {
			$formdata->mnet = 0 + @$CFG->block_vmoodle_mnet;
		}
	
		$formdata->services = $CFG->block_vmoodle_services;
		$platform_form->set_data($formdata);
	}
}

$platform_form->display();
// Print ending of a box.
echo $OUTPUT->box_end();

