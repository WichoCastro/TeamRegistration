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
		$str = "<div class='listHdr'>Players from registered teams that are awaiting registration approval:</div>";
		$sql="SELECT CONCAT(lname,', ', fname) as p_name,  CONCAT(ts.tname, ' (', d.name, ' -- ', s.name, ')') as t_name, t.uid as team_id, p.uid as player_id, s.uid as season_id, pt.notes
			FROM tmsl_player_team pt
			INNER JOIN tmsl_team t ON pt.team_uid=t.uid
			INNER JOIN tmsl_player p ON pt.player_uid=p.uid
			INNER JOIN tmsl_team_season ts ON ts.team_uid=t.uid
			INNER JOIN tmsl_season s ON s.uid=ts.season_uid
			INNER JOIN tmsl_division d ON ts.division_uid=d.uid
			WHERE pt.registered<>2 and ts.registered=2
			AND pt.season_uid=s.uid
			AND s.stop_date>now() 
			ORDER BY s.stop_date desc, d.name, s.name, t_name, p_name";

		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
			//print_r($rec);
			$t_name=$rec['t_name'];
			if ($t_name <> $prev_t_name) $str.= "<br/><span style='font-weight:bold'><a href='roster.php?team_id=".$rec['team_id']."&season_id=".$rec['season_id']."'>$t_name</a></span><br/>";
			$prev_t_name = $t_name;
			$str.="<a href='acceptPlayerRegistration.php?uid=".$rec['player_id']."&team_id=".$rec['team_id']."&season_id=".$rec['season_id']."'><img src='images/wrench.png' alt='change registration status' title='change registration status' border='0'></a>";
			$str.="<a href='editPlayer.php?edit=1&team_id={$rec['team_id']}&uid={$rec['player_id']}'>{$rec['p_name']}</a><br/>";
			if ($rec['notes']) $str.="<span class='noteBox'>".$rec['notes']."</span><br/>";
		}



		print $str;
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
