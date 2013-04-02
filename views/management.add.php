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
print_heading(get_string('newvmoodle', 'block_vmoodle'));

// Print beginning of a box.
print_box_start();

// Displays the form.
if (isset($SESSION->vmoodle_mg['dataform'])) {
	$platform_form = new Vmoodle_Host_Form('add', $SESSION->vmoodle_mg['dataform']);
} else if (!isset($platform_form)) {
	$platform_form = new Vmoodle_Host_Form('add', null);
}
$platform_form->display();
// Print ending of a box.
print_box_end();

?>