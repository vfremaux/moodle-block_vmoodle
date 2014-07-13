<?php
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
