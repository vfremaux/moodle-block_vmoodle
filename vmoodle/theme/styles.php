<?php header('Content-type: text/css');
require_once('../../../config.php');
?>
div.bvmc {
	border: 1px solid #B9BABB;
	margin: 10px auto 15px;
	width: 95%;
}
div.bvmc div.header h2 {
	margin: 5px 0;
	padding: 0 0 0 22px;
}
div.bvmc div.header div.title {
	margin: 0px 5px;
}
div.bvmc div.header .hide-show-image {
	float: right;
	height: 16px;
	width: 16px;
	margin-top: 0.1em;
}
div.bvmc div.content {
	background: #FFFFFF url(<?php echo $CFG->wwwroot; ?>/theme/pairformance_exm/prfpix/droite_fond.jpg) repeat-x scroll;
	padding: 5px 0 10px;
}
div.bvmc.hidden div.content {
	display: none;
}

#platformschoice div.fitemtitle {
	display: none;
}
#id_platformsgroup_aplatforms, #id_platformsgroup_splatforms {
	width: 50%;
}
#platformschoice .fgroup {
	white-space: nowrap;
}
table.pfilter {
	width: 90%;
} 
table.pfilter td.pfilter_type {
	width: 50%;
	text-align: right;
}
table.pfilter td.pfilter_value {
	width: 30%;
	padding-left: 6%;
	text-align: left;
}
table.pfilter td.pfilter_action {
	width: 20%;
	text-align: center;
}
<?php
	// Adding plugin libraries styles
	$plugins = get_list_of_plugins('/blocks/vmoodle/plugins/libs');
	foreach($plugins as $plugin)
		if(file_exists($CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/theme/styles.php'))
			include_once $CFG->dirroot.'/blocks/vmoodle/plugins/libs/'.$plugin.'/theme/styles.php';