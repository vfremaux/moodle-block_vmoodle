<?php

if (!defined('MOODLE_INTERNAL')) die ("You cannot use this script this way");

if (!$DB->get_records('block', array('name' => 'vmoodle'))) return;

$ADMIN->add('server', new admin_externalpage('vmoodle', get_string('vmoodleadministration', 'block_vmoodle'), $CFG->wwwroot . '/blocks/vmoodle/view.php', 'block/vmoodle:managevmoodles'));

if ($ADMIN->fulltree) {
	$yesnoopts[0] = get_string('no');
	$yesnoopts[1] = get_string('yes');
	
	$settings->add(new admin_setting_configselect('block_vmoodle_automatedschema', get_string('automateschema', 'block_vmoodle'), get_string('automateschema_desc', 'block_vmoodle'), 1, $yesnoopts));
	
	$settings->add(new admin_setting_heading('siteschema', get_string('siteschema', 'block_vmoodle'), ''));
	$settings->add(new admin_setting_configtext('block_vmoodle_vmoodlehost', get_string('vmoodlehost', 'block_vmoodle'), get_string('vmoodlehost_desc', 'block_vmoodle'), 'http://<%%INSTANCE%%>'));
	$settings->add(new admin_setting_configtext('block_vmoodle_vmoodleip', get_string('vmoodleip', 'block_vmoodle'), get_string('vmoodleip_desc', 'block_vmoodle'), ''));
	
	$dbopts['mysqli'] = "MySQLi";
	$dbopts['postgres'] = "Postgres";
	$settings->add(new admin_setting_heading('dbschema', get_string('dbschema', 'block_vmoodle'), ''));
	$settings->add(new admin_setting_configselect('block_vmoodle_dbtype', get_string('vdbtype', 'block_vmoodle'), get_string('vdbtype_desc', 'block_vmoodle'), 'mysqli', $dbopts));
	$settings->add(new admin_setting_configtext('block_vmoodle_vdbhost', get_string('vdbhost', 'block_vmoodle'), get_string('vdbhost_desc', 'block_vmoodle'), 'localhost'));
	$settings->add(new admin_setting_configtext('block_vmoodle_vdblogin', get_string('vdblogin', 'block_vmoodle'), get_string('vdblogin_desc', 'block_vmoodle'), 'root'));
	$settings->add(new admin_setting_configpasswordunmask('block_vmoodle_vdbpass', get_string('vdbpass', 'block_vmoodle'), get_string('vdbpass_desc', 'block_vmoodle'), ''));
	$settings->add(new admin_setting_configtext('block_vmoodle_vdbbasename', get_string('vdbname', 'block_vmoodle'), get_string('vdbname_desc', 'block_vmoodle'), 'vmdl_<%%INSTANCE%%>'));
	$settings->add(new admin_setting_configtext('block_vmoodle_vdbprefix', get_string('vdbprefix', 'block_vmoodle'), get_string('vdbprefix_desc', 'block_vmoodle'), 'mdl_'));
	$settings->add(new admin_setting_configselect('block_vmoodle_dbpersist', get_string('vdbpersist', 'block_vmoodle'), get_string('vdbpersist_desc', 'block_vmoodle'), 0, $yesnoopts));
	
	$settings->add(new admin_setting_heading('fileschema', get_string('fileschema', 'block_vmoodle'), ''));
	$settings->add(new admin_setting_configtext('block_vmoodle_vdatapathbase', get_string('vdatapath', 'block_vmoodle'), get_string('vdatapath_desc', 'block_vmoodle'), '/var/moodledata/<%%INSTANCE%%>'));
	
	$settings->add(new admin_setting_heading('mnetschema', get_string('mnetschema', 'block_vmoodle'), ''));

	$subnetworks = array('-1' => get_string('nomnet', 'block_vmoodle'));
	$subnetworks['0'] = get_string('mnetfree', 'block_vmoodle');
	$subnetworksrecords = $DB->get_records_sql('select * from {block_vmoodle} where mnet > 0  order by mnet');
	$newsubnetwork = 1;
	if(!empty($subnetworksrecords)){
		foreach ($subnetworksrecords as $subnetworksrecord) {
			$subnetworks[$subnetworksrecord->mnet] = $subnetworksrecord->mnet;
		}
		$newsubnetwork = array_pop($subnetworksrecords)->mnet + $newsubnetwork;
	}
	$subnetworks[$newsubnetwork] = $newsubnetwork.' ('.get_string('mnetnew', 'block_vmoodle').')';
	$settings->add(new admin_setting_configselect('block_vmoodle_mnet', get_string('multimnet', 'block_vmoodle'), get_string('multimnet_desc', 'block_vmoodle'), 0, $subnetworks));

	// Services strategy.
	$services_strategies = array(
		'default' => get_string('servicesstrategydefault', 'block_vmoodle'), 
		'subnetwork' => get_string('servicesstrategysubnetwork', 'block_vmoodle')
	);
	$settings->add(new admin_setting_configselect('block_vmoodle_services', get_string('servicesstrategy', 'block_vmoodle'), get_string('servicesstrategy_desc', 'block_vmoodle'), 0, $services_strategies));

	$settings->add(new admin_setting_heading('key_autorenew_parms', get_string('tools', 'block_vmoodle'), ''));

	$onoffopts[0] = get_string('off', 'block_vmoodle');
	$onoffopts[1] = get_string('on', 'block_vmoodle');
	$settings->add(new admin_setting_configselect('mnet_key_autorenew', get_string('mnetkeyautorenew', 'block_vmoodle'), get_string('mnetkeyautorenew_desc', 'block_vmoodle'), 1, $onoffopts));
	$settings->add(new admin_setting_configtext('mnet_key_autorenew_gap', get_string('mnetkeyautorenewgap', 'block_vmoodle'), get_string('mnetkeyautorenewgap_desc', 'block_vmoodle'), 24 * 3));
	$settings->add(new admin_setting_configtime('mnet_key_autorenew_time_hour', 'mnet_key_autorenew_time_min', get_string('mnetkeyautorenewtime', 'block_vmoodle'), '', array('h' => 0, 'm' => 0)));

	$settings->add(new admin_setting_heading('tools', get_string('tools', 'block_vmoodle'), ''));
	$settings->add(new admin_setting_configtext('block_vmoodle_cmd_mysql', get_string('mysqlcmd', 'block_vmoodle'), get_string('systempath_desc', 'block_vmoodle'), '/usr/bin/mysql'));
	$settings->add(new admin_setting_configtext('block_vmoodle_cmd_mysqldump', get_string('mysqldumpcmd', 'block_vmoodle'), get_string('systempath_desc', 'block_vmoodle'), '/usr/bin/mysqldump'));
	$settings->add(new admin_setting_configtext('block_vmoodle_cmd_pgsql', get_string('pgsqlcmd', 'block_vmoodle'), get_string('systempath_desc', 'block_vmoodle'), '/usr/bin/psql'));
	$settings->add(new admin_setting_configtext('block_vmoodle_cmd_pgsqldump', get_string('pgsqldumpcmd', 'block_vmoodle'), get_string('systempath_desc', 'block_vmoodle'), '/usr/bin/pg_dump'));

	$settings->add(new admin_setting_heading('massdeployment', get_string('massdeployment', 'block_vmoodle'), ''));

	$encodingopts[0] = 'UTF-8';
	$encodingopts[1] = 'ISO-5889-1';
	$settings->add(new admin_setting_configselect('block_vmoodle_encoding', get_string('csvencoding', 'block_vmoodle'), get_string('csvencoding_desc', 'block_vmoodle'), 1, $encodingopts));
}

