<?php
/**
 * Description of assisted commands for role purpose.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Creating category
$category = new Vmoodle_Command_Category('roles');

// Adding commands
$category->addCommand(new Vmoodle_Command_Role_Sync());
$category->addCommand(new Vmoodle_Command_Role_Compare());
					
// Returning the category
return $category;