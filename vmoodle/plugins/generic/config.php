<?php
/**
 * Description of assisted commands for Pairform@ance.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
 
function vmoodle_config_get_plugins_params() {
	global $CFG;
	
	$paramslist = get_records_sql_menu("SELECT DISTINCT id,CONCAT(plugin,'/',name) FROM {$CFG->prefix}config_plugins");
	$paramlist = array_combine(array_values($paramslist), array_values($paramslist));
	return $paramlist;
}

function vmoodle_config_get_params() {
	global $CFG;

	$paramslist = get_records_sql_menu("SELECT DISTINCT id,name FROM {$CFG->prefix}config");
	$paramlist = array_combine(array_values($paramslist), array_values($paramslist));
	return $paramlist;
}
 
$category = new Vmoodle_Command_Category('generic');

$param1 = new Vmoodle_Command_Parameter(
	'source1',
	'enum',
	'Config Key',
	null,
	vmoodle_config_get_params()
);

$param2 = new Vmoodle_Command_Parameter(
	'source2',
	'text',
	'Config Value',
	null,
	null
);

$cmd = new Vmoodle_Command_Sql(
	'Vmoodle Config Value',
	'Distributing a configuration value',
	'UPDATE [[prefix]]config SET value = \'[[?source1]]\' WHERE name = \'[[?source2]]\';',
	array($param1,$param2)
);
$category->addCommand($cmd);

$param1 = new Vmoodle_Command_Parameter(
	'source1',
	'enum',
	'Config Key',
	null,
	vmoodle_config_get_plugins_params()
);

$param2 = new Vmoodle_Command_Parameter(
	'source2',
	'text',
	'Config Value',
	null,
	null
);

$cmd = new Vmoodle_Command_Sql(
	'Vmoodle Plugin Config Value',
	'Distributing a configuration value in Config Plugin',
	'UPDATE [[prefix]]config_plugins SET value = \'[[?source2]]\' WHERE CONCAT (plugin,\'/\',name) = \'[[?source1]]\' ;',
	array($param1,$param2)
);
$category->addCommand($cmd);

return $category;