<?php

	if (!defined('MOODLE_INTERNAL')) die('You cannot access this script directly');

	$year = optional_param('year', 0, PARAM_INT); 
	$context = optional_param('context', CONTEXT_COURSE, PARAM_INT); 
	
	print_heading(get_string('roles', 'block_vmoodle'));

	tao_print_static_text('tao_static_vmoodle_report_roles', $CFG->wwwroot.'/blocks/vmoodle/report/view.php?view=roles');
	
	echo "<form name=\"chooseyearform\">";
	echo "<input type=\"hidden\" name=\"view\" value=\"$view\" />";

	$years[0] = 'Sans filtrage';
	for ($i = 0 ; $i < 15 ; $i++){
		$years[2009 + $i] = 2009 + $i;
	}
	choose_from_menu($years, 'year', $year);

	$contexts = array(CONTEXT_COURSE => get_string('course'), CONTEXT_COURSECAT => get_string('category'), 100 => get_string('site'), CONTEXT_SYSTEM => get_string('system', 'block_vmoodle'));
	choose_from_menu($contexts, 'context', $context);
	$gostr = get_string('apply', 'block_vmoodle');
	echo "<input type=\"submit\" value=\"$gostr\" />";
	echo '</form>';

	echo "<table width=\"100%\" cellspacing=\"10\"><tr>";
	
	$timeassignclause = '';
	if ($year){
		$timeassignclause = " AND YEAR(FROM_UNIXTIME(ra.timestart)) <= $year ";
	}
	
	$contextclause = '';
	switch($context){
		case CONTEXT_COURSE :
			$contextclause = ' AND c.contextlevel = 50 ';
			break;
		case CONTEXT_COURSECAT :
			$contextclause = ' AND c.contextlevel = 30 ';
			break;
		case 100 :
			$contextclause = ' AND c.contextlevel = 50 AND c.id = 1 ';
			break;
		case CONTEXT_SYSTEM :
			$contextclause = ' AND c.contextlevel = 10 ';
			break;
	}
	
	$col = 0;
	foreach($vhosts as $vhost){

		$vdbprefix = $vhost->vdbprefix;
		$vdbname = $vhost->vdbname;
		
		echo "<td valign=\"top\">";
	
		$sql = "
			SELECT 
				r.name,
				COUNT(DISTINCT u.id) as users
			FROM 
				`{$vdbname}`.{$vdbprefix}user as u,
				`{$vdbname}`.{$vdbprefix}role_assignments as ra,
				`{$vdbname}`.{$vdbprefix}context as c,
				`{$vdbname}`.{$vdbprefix}role as r
			WHERE 
				u.id = ra.userid AND
				u.deleted = 0 AND
				ra.contextid = c.id AND
				ra.roleid = r.id
				$timeassignclause
				$contextclause
			GROUP BY
				r.name
			ORDER BY
				r.sortorder
		";
	
		echo "<table width=\"100%\" class=\"stattable\"><tr>";
		echo "<th colspan=\"2\"><b>$vhost->name</b></th></tr>";
	
		if ($users = get_records_sql($sql)){
			$r = 0;
			foreach($users as $user){
				$usercount = 0 + $user->users;
				echo "<tr class=\"row r$r\"><td width=\"80%\"  class=\"cell c0\" style=\"border:1px solid #808080\">$user->name</td><td width=\"20%\" class=\"cell c1\" style=\"border:1px solid #808080\">{$usercount}</td></tr>";
				$r = ($r + 1) % 2;
			}
		} else {
			echo '<tr><td>'.get_string('nodata', 'block_vmoodle').'</td></tr>';
		}
		
		echo "</td></tr></table></td>";
		
		$col++;
		if ($col >= 4){
			echo '</tr><tr>';
			$col = 0;
		}
	}
	
	echo '</tr></table>';



?>