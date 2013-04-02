<?php
/**
 * Form for editing a virtual host.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading the library.
require_once(VMOODLE_CLASSES_DIR.'Host_Form.class.php');

// Print title (heading).
print_heading(get_string('editvmoodle', 'block_vmoodle'));

// Print beginning of a box.
print_box_start();

// Displays the form with data (and errors).
if (!isset($platform_form)) {
	$datas = (isset($SESSION->vmoodle_mg['dataform']) ? $SESSION->vmoodle_mg['dataform'] : null);
	$platform_form = new Vmoodle_Host_Form('edit', $datas);
}
$platform_form->display();

// Print ending of a box.
print_box_end();
