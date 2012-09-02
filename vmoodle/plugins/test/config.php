<?php
/**
 * Description of assisted commands for testing generic oommands.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */

// Creating category
$category = new Vmoodle_Command_Category('test');

// Adding commands
$cmd = new Vmoodle_Command_Sql(
	'Command 1',
	'Command without parameter.',
	'SELECT aa FROM bb'
);
$category->addCommand($cmd);

/********************************/

$cmd = new Vmoodle_Command_Sql(
	'Command 2',
	'Command with a boolean parameter.',
	'SELECT [[?parameter1]] FROM bb',
	new Vmoodle_Command_Parameter(
		'parameter1',
		'boolean',
		'The boolean'
	)
);
$category->addCommand($cmd);

/********************************/

$cmd = new Vmoodle_Command_Sql(
	'Command 3',
	'Command with a boolean parameter selected.',
	'SELECT [[?parameter1]] FROM bb',
	new Vmoodle_Command_Parameter(
		'parameter1',
		'boolean',
		'The boolean selected by default',
		true
	)
);
$category->addCommand($cmd);

/********************************/

$cmd = new Vmoodle_Command_Sql(
	'Command 4',
	'Command with a boolean parameter unselected.',
	'SELECT [[?parameter1]] FROM bb',
	new Vmoodle_Command_Parameter(
		'parameter1',
		'boolean',
		'The boolean unselected by default',
		false
	)
);
$category->addCommand($cmd);

/********************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'enum',
	'The enum choice',
	null,
	array(
		'value1' => get_string('value1', 'vmoodleadminset_test'),
		'value2' => get_string('value2', 'vmoodleadminset_test'),
		'value3' => get_string('value3', 'vmoodleadminset_test'),
	)
);
$cmd = new Vmoodle_Command_Sql(
	'Command 5',
	'Command with an enum choice without default value.',
	'SELECT [[?parameter1]] FROM bb',
	$param1
);
$category->addCommand($cmd);

/********************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'enum',
	'The enum choice values 2 by default',
	'value2',
	array(
		'value1' => get_string('value1', 'vmoodleadminset_test'),
		'value2' => get_string('value2', 'vmoodleadminset_test'),
		'value3' => get_string('value3', 'vmoodleadminset_test'),
	)
);
$cmd = new Vmoodle_Command_Sql(
	'Command 6',
	'Command with an enum choice with default value.',
	'SELECT [[?parameter1]] FROM bb',
	$param1
);
$category->addCommand($cmd);

/*********************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'text',
	'The free text without default value'
);
$cmd = new Vmoodle_Command_Sql(
	'Command 7',
	'Command with free text without default value.',
	'SELECT [[?parameter1]] FROM bb',
	$param1
);
$category->addCommand($cmd);

/************************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'text',
	'The free text with default value',
	'the default value'
);
$cmd = new Vmoodle_Command_Sql(
	'Command 8',
	'Command with free text with default value.',
	'SELECT [[?parameter1]] FROM bb',
	$param1
);
$category->addCommand($cmd);

/**************************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'ltext',
	'The free long text without default value'
);
$cmd = new Vmoodle_Command_Sql(
	'Command 9',
	'Command with free long text without default value.',
	'SELECT [[?parameter1]] FROM bb',
	$param1
);
$category->addCommand($cmd);

/*****************************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'ltext',
	'The free long text with default value',
	'default value'
);
$cmd = new Vmoodle_Command_Sql(
	'Command 10',
	'Command with free long text with default value.',
	'SELECT [[?parameter1]] FROM bb',
	$param1
);
$category->addCommand($cmd);

/*****************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'boolean',
	'A boolean selected by default',
	true
);
$param2 = new Vmoodle_Command_Parameter(
	'parameter2',
	'enum',
	'The enum choice values 2 by default',
	'value2',
	array(
		'value1' => get_string('value1', 'vmoodleadminset_test'),
		'value2' => get_string('value2', 'vmoodleadminset_test'),
		'value3' => get_string('value3', 'vmoodleadminset_test'),
	)
);
$param3 = new Vmoodle_Command_Parameter(
	'parameter3',
	'text',
	'The free text with default value',
	'the default value'
);
$cmd = new Vmoodle_Command_Sql(
	'Command 11',
	'Command which combine different fields.',
	'SELECT [[?parameter1]], [[?parameter2]], [[?parameter3]] FROM bb',
	array( $param1,
           $param2,
		   $param3
	)
);
$category->addCommand($cmd);

/*********************************/

$param1 = new Vmoodle_Command_Parameter(
	'parameter1',
	'boolean',
	'The boolean selected by default',
	true
);
$param2 = new Vmoodle_Command_Parameter(
	'parameter2',
	'enum',
	'The enum choice values 2 by default',
	'value2',
	array(
		'value1' => get_string('value1', 'vmoodleadminset_test'),
		'value2' => get_string('value2', 'vmoodleadminset_test'),
		'value3' => get_string('value3', 'vmoodleadminset_test'),
	)
);
$param3 = new Vmoodle_Command_Parameter_Internal(
	'parameter3',
	'explode',
	array('[[?parameter1]]', '[[?parameter2]]', '[[bibi]]', 'ba[[?parameter1:tot]]be[[prefix]]bi', true, 'aa')
);
$cmd = new Vmoodle_Command_Sql(
	'Command 12',
	'Command with a boolean parameter selected and an internal parameter.',
	'SELECT [[?parameter1]],[[?parameter2]],[[?parameter3]] FROM bb',
	array(
		$param1,
		$param2,
		$param3
	)
);
$category->addCommand($cmd);
					
function myTestFunction() {
	throw new Exception();
}

$cmd = new Vmoodle_Command_Sql(
	'Command 13',
	'Command to  try error handling.',
	'SELECT [[?parameter1]] FROM bb',
	new Vmoodle_Command_Parameter_Internal(
		'parameter1',
		'myTestFunction'
	)
);
$category->addCommand($cmd);

$test_rpcommad = new Vmoodle_Command_Sql(
	'Command 14',
	'Command with a retrieve platforms command.',
	'UPDATE bb SET value = \'aa\' WHERE name = \'cc\'',
	null
);

$param1 = new Vmoodle_Command_Parameter(
	'param',
	'text',
	'Name of config directive',
	'block_vmoodle_host_source'
);
$param2 = new Vmoodle_Command_Parameter(
	'value',
	'text',
	'Value of config directive',
	'vmoodle'
);
$cmd = new Vmoodle_Command_Sql(
	'Retrieve platforms command',
	'Command used to retrieve platforms from their original value.',
	'SELECT id FROM [[prefix]]config WHERE name = \'[[?param]]\' AND value = \'[[?value]]\'',
	array(
		$param1,
		$param2
	)
);

$test_rpcommad->attachRPCommand($cmd);
$category->addCommand($test_rpcommad);

$category->addCommand(new Vmoodle_Command_Role_Sync());
$category->addCommand(new Vmoodle_Command_Role_Capability_Sync());
$category->addCommand(new Vmoodle_Command_Role_Compare());
$category->addCommand(new Vmoodle_Command_Update());
	
// Returning the category
return $category;