<?php
/**
 * Description of assisted commands for role purpose.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Creating category
$category = new Vmoodle_Command_Category('plugins');

// Adding commands
$category->addCommand(new Vmoodle_Command_Plugin_Set_State());
$category->addCommand(new Vmoodle_Command_Plugins_Sync());
$category->addCommand(new Vmoodle_Command_Plugins_Compare());
					
// Returning the category
return $category;