<?php
/**
 * Description of assisted commands for code upgrading.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */

include_once $CFG->dirroot.'/blocks/vmoodle/plugins/libs/updatelib/classes/Command_Update.class.php';
 
// Creating category
$category = new Vmoodle_Command_Category('upgrade');

$category->addCommand(new Vmoodle_Command_Update(
	'Upgrade databases',
	'Drives the logical upgrade of all Moodles in the network'));

return $category;

?>