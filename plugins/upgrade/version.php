<?php
/**
 * Description of Update plugin library.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */
$plugin = new Stdclass();
$plugin->version = 2014071300;
$plugin->component = 'vmoodleadminset_upgrade';
$plugin->requires = 2013111800;
$plugin->dependencies = array('block_vmoodle' => 2014020400);
