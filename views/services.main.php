<?php
/**
 * Displays default services strategy.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Loading the library.
require_once(VMOODLE_CLASSES_DIR.'ServicesStrategy_Form.class.php');

// Print beginning of a box.
print_box_start();

// Displays the form.
$services_form	=	new Vmoodle_Services_Strategy_Form();
$services_form->display();

// Print ending of a box.
print_box_end();
