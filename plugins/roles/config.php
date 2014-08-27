<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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