<?php

Use \block_vmoodle\commands\Command_Category;
Use \vmoodleadminset_plugins\Command_Plugin_Set_State;
Use \vmoodleadminset_plugins\Command_Plugins_Sync;
Use \vmoodleadminset_plugins\Command_Plugins_Compare;

/**
 * Description of assisted commands for role purpose.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Creating category
$category = new Command_Category('plugins');

// Adding commands
$category->addCommand(new Command_Plugin_Set_State());
$category->addCommand(new Command_Plugins_Sync());
$category->addCommand(new Command_Plugins_Compare());

// Returning the category
return $category;