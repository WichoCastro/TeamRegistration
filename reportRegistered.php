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
		print "<div id='ttlBar'>Report -- Registered Teams</div>";

		print "<div id='mainPar'>";

		//List teams that have submitted registration for an open season
		$str = "<div class='listHdr'>Registration Submitted; Pending:</div>";
		$sql="SELECT CONCAT(s.name,' (',l.name,')') as s_name,  t.name as t_name, t.uid as team_id, s.uid as season_id, tl.notes
			FROM tmsl_team_season tl
			INNER JOIN tmsl_team t ON tl.team_uid=t.uid
			INNER JOIN tmsl_season s ON tl.season_uid=s.uid
			INNER JOIN tmsl_division l ON tl.division_uid=l.uid
			WHERE registered=1 ORDER BY s.start_date desc, s_name, t_name";
		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
			$s_name=$rec['s_name'];
			$tm=getTeamName($rec['team_id'], $rec['season_id']);
			if ($s_name <> $prev_s_name) $str.= "<br/><span style='font-weight:bold'><a href='teamSeason.php?season_id=".$rec['season_id']."'>$s_name</a></span><br/>";
			$prev_s_name = $s_name;
			$str.="<a href='acceptRegistration.php?team_id=".$rec['team_id']."&season_id=".$rec['season_id']."'><img src='images/wrench.png' alt='change registration status' title='change registration status' border='0'></a>";
			$str.="<a href='roster.php?team_id={$rec['team_id']}&season_id={$rec['season_id']}'>{$tm}</a><br/>";
			if ($rec['notes']) $str.="<span class='noteBox'>".$rec['notes']."</span><br/>";
		}

		$prev_s_name="";

		//List teams that have not yet submitted registration
		$str .= "<div class='listHdr'>Registration Not Yet Submitted:</div>";
		$sql="SELECT CONCAT(s.name,' (',l.name,')') as s_name,  t.name as t_name, t.uid as team_id, s.uid as season_id, tl.notes
			FROM tmsl_team_season tl
			INNER JOIN tmsl_team t ON tl.team_uid=t.uid
			INNER JOIN tmsl_season s ON tl.season_uid=s.uid
			INNER JOIN tmsl_division l ON tl.division_uid=l.uid
			WHERE registered=0 AND s.last_day_team> now() ORDER BY s.start_date desc, s_name, t_name";
		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
			$s_name=$rec['s_name'];
			$tm=getTeamName($rec['team_id'], $rec['season_id']);
			if ($s_name <> $prev_s_name) $str.= "<br/><span style='font-weight:bold'><a href='teamSeason.php?season_id=".$rec['season_id']."'>$s_name</a></span><br/>";
			$prev_s_name = $s_name;
			$str.="<a href='acceptRegistration.php?team_id=".$rec['team_id']."&season_id=".$rec['season_id']."'><img src='images/wrench.png' alt='change registration status' title='change registration status' border='0'></a>";
			$str.="<a href='roster.php?team_id={$rec['team_id']}&season_id={$rec['season_id']}'>{$tm}</a><br/>";
			if ($rec['notes']) $str.="<span class='noteBox'>".$rec['notes']."</span>";
		}

		$prev_s_name="";

		//List teams that are registered
		$str .= "<div class='listHdr'>Registered Teams:</div>";
		$sql="SELECT CONCAT(s.name,' (',l.name,')') as s_name,  t.name as t_name, t.uid as team_id, s.uid as season_id, tl.notes
			FROM tmsl_team_season tl
			INNER JOIN tmsl_team t ON tl.team_uid=t.uid
			INNER JOIN tmsl_season s ON tl.season_uid=s.uid
			INNER JOIN tmsl_division l ON tl.division_uid=l.uid
			WHERE registered=2 AND s.stop_date > now() ORDER BY s.start_date desc, s_name, t_name";
		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
			$s_name=$rec['s_name'];
			$tm=getTeamName($rec['team_id'], $rec['season_id']);			
			if ($s_name <> $prev_s_name) $str.= "<br/><span style='font-weight:bold'><a href='teamSeason.php?season_id=".$rec['season_id']."'>$s_name</a></span><br/>";
			$prev_s_name = $s_name;
			$str.="<a href='acceptRegistration.php?team_id=".$rec['team_id']."&season_id=".$rec['season_id']."'><img src='images/wrench.png' alt='change registration status' title='change registration status' border='0'></a>";
			$str.="<a href='roster.php?team_id={$rec['team_id']}&season_id={$rec['season_id']}'>{$tm}</a><br/>";
			if ($rec['notes']) $str.="<span class='noteBox'>".$rec['notes']."</span><br/>";
		}

		print $str;
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
