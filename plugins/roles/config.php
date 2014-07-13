<?php
/**
 * Description of assisted commands for role purpose.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace vmoodleadminset_roles;
Use \block_vmoodle\commands\Command_Category;

// Creating category
$category = new Command_Category('roles');

// Adding commands
$category->addCommand(new Command_Role_Sync());
$category->addCommand(new Command_Role_Compare());
$category->addCommand(new Command_Role_Allow_Sync());
$category->addCommand(new Command_Role_Allow_Compare());

// Returning the category
return $category;