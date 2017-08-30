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
 * Description of assisted commands for administrating configs.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @copyright valeisti (http://www.valeisti.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace vmoodleadminset_generic;
Use \block_vmoodle\commands\Command_Category;
Use \block_vmoodle\commands\Command_Parameter;
Use \vmoodleadminset_sql\Command_MultiSql;
Use \vmoodleadminset_sql\Command_Sql;

function vmoodle_config_get_plugins_params() {
    global $CFG, $DB;

    $paramslist = $DB->get_records_sql_menu("SELECT DISTINCT id,CONCAT(plugin,'/',name) FROM {config_plugins} ");
    $paramlist = array_combine(array_values($paramslist), array_values($paramslist));
    return $paramlist;
}

function vmoodle_config_get_params() {
    global $CFG, $DB;

    $paramslist = $DB->get_records_sql_menu("SELECT DISTINCT id,name FROM {config} ");
    $paramlist = array_combine(array_values($paramslist), array_values($paramslist));
    return $paramlist;
}


$category = new Command_Category('generic');

// Set on/off the maintenance mode.
$param1 = new Command_Parameter(
    'source1',
    'boolean',
    vmoodle_get_string('maintenancemode', 'vmoodleadminset_generic'),
    null,
    null
);

$param2 = new Command_Parameter(
    'source2',
    'ltext',
    vmoodle_get_string('maintenancemessage', 'vmoodleadminset_generic'),
    null,
    null
);

$cmd = new Command_MultiSql(
    vmoodle_get_string('vmoodlemaintenance', 'vmoodleadminset_generic'),
    vmoodle_get_string('vmoodlemaintenance_desc', 'vmoodleadminset_generic'),
    'UPDATE {config} SET value = [[?source1]] WHERE name = \'maintenance_enabled\' '.";\n".' UPDATE {config} SET value = [[?source2]] WHERE name = \'maintenance_message\'',
    array($param1,$param2)
);
$category->addCommand($cmd);

$cmd = new Command_PurgeCaches(
    vmoodle_get_string('vmoodlepurgecaches', 'vmoodleadminset_generic'),
    vmoodle_get_string('vmoodlepurgecaches_desc', 'vmoodleadminset_generic')
);

$category->addCommand($cmd);

// Distribute a config value to all nodes (Using SetConfig).
$param1 = new Command_Parameter(
    'key',
    'enum',
    vmoodle_get_string('configkey', 'vmoodleadminset_generic'),
    null,
    vmoodle_config_get_params()
);

$param2 = new Command_Parameter(
    'value',
    'ltext',
    vmoodle_get_string('configvalue', 'vmoodleadminset_generic'),
    null,
    null
);

$cmd = new Command_SetConfig(
    vmoodle_get_string('vmoodleconfigvalue', 'vmoodleadminset_generic'),
    vmoodle_get_string('vmoodleconfigvalue_desc', 'vmoodleadminset_generic'),
    array($param1,$param2)
);
$category->addCommand($cmd);


$param1 = new Command_Parameter(
    'key',
    'enum',
    vmoodle_get_string('configkey', 'vmoodleadminset_generic'),
    null,
    vmoodle_config_get_plugins_params()
);

$param2 = new Command_Parameter(
    'value',
    'ltext',
    vmoodle_get_string('configvalue', 'vmoodleadminset_generic'),
    null,
    null
);

$cmd = new Command_SetPluginConfig(
    vmoodle_get_string('vmoodleconfigpluginvalue', 'vmoodleadminset_generic'),
    vmoodle_get_string('vmoodleconfigpluginvalue_desc', 'vmoodleadminset_generic'),
    array($param1,$param2)
);
$category->addCommand($cmd);

return $category;