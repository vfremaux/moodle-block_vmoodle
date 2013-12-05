<?php
/**
 * Description of assisted commands for code upgrading.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */

include_once $CFG->dirroot.'/blocks/vmoodle/plugins/upgrade/classes/Command_Upgrade.class.php';
 
// Creating category
$category = new Vmoodle_Command_Category('upgrade');

$category->addCommand(new Vmoodle_Command_Upgrade(
	'Upgrade databases',
	'Drives the logical upgrade of all Moodles in the network'));

return $category;

