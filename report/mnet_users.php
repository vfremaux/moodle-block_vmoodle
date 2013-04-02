<?php

	if (!defined('MOODLE_INTERNAL')) die('You cannot access this script directly');
	
	$year = optional_param('year', 0, PARAM_INT); 
	
	print_heading(get_string('users', 'block_vmoodle'));

	tao_print_static_text('tao_static_vmoodle_report_users', $CFG->wwwroot.'/blocks/vmoodle/report/view.php?view=users');

	echo "<form name=\"chooseyearform\">";
	echo "<input type=\"hidden\" name=\"view\" value=\"$view\" />";
	$years[0] = 'Sans filtrage';
	for ($i = 0 ; $i < 15 ; $i++){
		$years[2009 + $i] = 2009 + $i;
	}
	choose_from_menu($years, 'year', $year);
	$gostr = get_string('apply', 'block_vmoodle');
	echo "<input type=\"submit\" value=\"$gostr\" />";
	echo '</form>';
	
	echo "<table width=\"100%\"><tr>";
	
	$firstaccessclause = '';
	if ($year){
		$firstaccessclause = " AND YEAR(FROM_UNIXTIME(firstaccess)) <= $year ";
	}
	
	$col = 0;
	$totalusersinhoststr = get_string('totalusersinhost', 'block_vmoodle');
	foreach($vhosts as $vhost){
		
		echo "<td valign=\"top\">";
	
		$sql = "
			SELECT 
				h.name as host,
				COUNT(*) as users
			FROM 
				`{$vhost->vdbname}`.{$vhost->vdbprefix}user as u,
				`{$vhost->vdbname}`.{$vhost->vdbprefix}mnet_host as h
			WHERE 
				u.mnethostid = h.id AND
				u.deleted = 0
				$firstaccessclause
			GROUP BY
				u.mnethostid
			ORDER BY
				h.name
		";
	
		echo "<table width=\"100%\" class=\"stattable\"><tr>";
		echo "<th colspan=\"2\">$vhost->name</th></tr>";
	
		$localusertotal = 0;
		if ($users = get_records_sql($sql)){
			$r = 0;
			foreach($users as $user){
				$usercount = 0 + $user->users;
				$localusertotal = 0 + @$localusertotal + $user->users;
				echo "<tr class=\"row r$r\"><td width=\"80%\" class=\"cell c0\" style=\"border:1px solid #808080\">$user->host</td><td width=\"20%\" class=\"cell c1\" style=\"border:1px solid #808080\">{$usercount}</td></tr>";
				$r = ($r + 1) % 2;
			}
		}
		
		echo "<tr class=\"row r$r\"><td width=\"80%\" class=\"cell c0\" style=\"font-weight:bolder;border:1px solid #808080\">$totalusersinhoststr</td><td width=\"20%\" class=\"cell c1\" style=\"font-weight:bolder;border:1px solid #808080\">{$localusertotal}</td></tr>";
		echo "</table></td>";
		
		$col++;
		if ($col >= 4){
			echo '</tr><tr>';
			$col = 0;
		}
	}
	
	echo '</tr></table>';
	
	

?>