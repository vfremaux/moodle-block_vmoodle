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

$defaultservices = $DB->get_records('mnet_service', array('offer' => 1), 'name');

// Displays the form.

$services_form	=	new Vmoodle_Services_Strategy_Form();
if($services = unserialize(get_config(null, 'block_vmoodle_services_strategy'))){
	$services_form->set_data($services);
}

// Print beginning of a box.
echo $OUTPUT->box_start();

$services_form->display();

// Print ending of a box.
echo $OUTPUT->box_end();
