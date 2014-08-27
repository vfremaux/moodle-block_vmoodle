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
 * Description of assisted commands for code upgrading.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */

namespace vmoodleadminset_upgrade;
Use \block_vmoodle\commands\Command_Category;

// Creating category
$category = new Command_Category('upgrade');

$category->addCommand(new Command_Upgrade(
    'Upgrade databases',
    'Drives the logical upgrade of all Moodles in the network'));

return $category;
