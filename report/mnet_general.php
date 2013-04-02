<?php

	if (!defined('MOODLE_INTERNAL')) die('You cannot access this script directly');

	$year = optional_param('year', 2010, PARAM_INT); 

	print_heading(get_string('cnxs', 'block_vmoodle'));
	
	tao_print_static_text('tao_static_vmoodle_report_general', $CFG->wwwroot.'/blocks/vmoodle/report/view.php');

	echo "<form name=\"chooseyearform\">";
	for ($i=0;$i < 15;$i++){
		$years[2009 + $i] = 2009 + $i;
	}
	choose_from_menu($years, 'year', $year);
	$gostr = get_string('apply', 'block_vmoodle');
	echo "<input type=\"submit\" value=\"$gostr\" />";
	echo '</form>';
	
	echo "<table width=\"100%\"><tr>";

	$col = 0;
	$overall = 0 ;
	$yearlytotalstr = get_string('totalyearly', 'block_vmoodle');		
	$shortyearlytotalstr = get_string('totalyearlyshort', 'block_vmoodle');		
	foreach($vhosts as $vhost){
		echo "<td valign=\"top\">";
		$sql = "
			SELECT 
				MONTH(FROM_UNIXTIME(time)) as month,
				COUNT(*) as cnxs
			FROM 
				`{$vhost->vdbname}`.{$vhost->vdbprefix}log
			WHERE 
				ACTION = 'login' AND 
				YEAR( FROM_UNIXTIME(time)) = $year
			GROUP 
				BY MONTH( FROM_UNIXTIME(time))
			ORDER BY
				month
		";
	
		echo "<table width=\"100%\" class=\"stattable\">";
		echo "<tr><th colspan=\"2\" style=\"height:3em\" >$vhost->name</th></tr>";
	
		$yearly = 0;
		if ($connections = get_records_sql($sql)){
			$r = 0;
			for($m = 1 ; $m <= 12 ; $m++){
				$cnxs = 0 + @$connections[$m]->cnxs;
				$yearly = $yearly + $cnxs;
				$overalmonthly[$m] = @$overalmonthly[$m] + $cnxs;
				$overall = $overall + $cnxs;
				echo "<tr class=\"row r$r\"><td width=\"80%\" class=\"cell c0\" style=\"border:1px solid #808080\">$m</td><td width=\"20%\" class=\"cell c1\" style=\"border:1px solid #808080\">{$cnxs}</td></tr>";
				$r = ($r + 1) % 2;
			}
		}
		echo "<tr class=\"row r$r\"><td width=\"80%\" class=\"cell c0\" style=\"font-weight:bolder;border:1px solid #808080\">$shortyearlytotalstr</td><td width=\"20%\" class=\"cell c1\" style=\"font-weight:bolder;border:1px solid #808080\">{$yearly}</td></tr>";
		echo "</table></td>";
		
		$col++;
		if ($col >= 4){
			echo '</tr><tr>';
			$col = 0;
		}
	}
	
	echo '</tr></table>';

	print_heading(get_string('totalcnxs', 'block_vmoodle'));

	$overalmonthlystr = get_string('totalmonthly', 'block_vmoodle');
	echo "<table width=\"250\" class=\"stattable\">";
	echo "<tr><th colspan=\"2\">$overalmonthlystr</th></tr>";

	$r = 0;
	for($m = 1 ; $m <= 12 ; $m++){
		$om = 0 + @$overalmonthly[$m];
		echo "<tr class=\"row r$r\"><td class=\"cell c0\" style=\"border:1px solid #808080\">$m</td><td class=\"cell c1\" style=\"border:1px solid #808080\">{$om}</td></tr>";
		$r = ($r + 1) % 2;
	}
	echo "<tr class=\"row r$r\"><td class=\"cell c0\" style=\"border:1px solid #808080\">$yearlytotalstr</td><td class=\"cell c1\" style=\"border:1px solid #808080\">{$overall}</td></tr>";
	echo "</table></td>";
	
?>