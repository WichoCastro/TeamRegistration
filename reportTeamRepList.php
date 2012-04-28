<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Report -- Player Registration</div>";

		print "<div id='mainPar'>";
		//print "<h5>Showing players from registered teams that are awaiting registration approval:</h5>";

		//List players that have submitted registration for a registered team:
		$str = "<div class='listHdr'>Team Reps for the Current Season:</div>";
		$sql="select t.name as t_name, fname, lname, case email when '' then '-none-' else email end as email 
			from tmsl_team_manager tm 
			inner join tmsl_season s ON tm.season_uid = s.uid 
			inner join tmsl_player p ON tm.user_uid=p.uid 
			inner join tmsl_team_season ts ON ts.season_uid=s.uid 
			inner join tmsl_team t ON t.uid=ts.team_uid 
			where s.stop_date > now()
			and tm.team_uid=t.uid
			and tm.active=1
			and ts.registered=2
			order by t_name, lname, fname";

		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
			//print_r($rec);continue;
			//$t_name=$rec['t_name'];
			//if ($t_name <> $prev_t_name) 
			$str.= "<br/><span>{$rec['t_name']}, {$rec['fname']} {$rec['lname']}, {$rec['email']}</span>";
			///$prev_t_name = $t_name;
		}



		print $str;
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
