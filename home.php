<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($isRef) {
			$url="games.php?ref_id={$_SESSION['logon_uid']}&numDays=21&srch=ok";
			header("Location:$url");
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Home</div>";
		//$str = "This site is currently under development.  The data are strictly fictional -- feel free to manipulate them at your leisure.<br/><br/>";
		$str = "<br/>Welcome to the TMSL Registration Website.  Whereas traditionally, players and team representatives would go in person to the TMSL Office
		 or someone's house, now more of that will be handled online.  You, as a team representative, will need to assemble your team using this site,
		 submit it for registration for a specified season, and submit payment to TMSL.<br/>";
		$str .= "<br/>The teams listed below are the ones you represent.  Begin by clicking on one of them.  If you wish to add a new team,
		 one that's never played in TMSL before, use the 'Add a New Team' button below.  If you believe you should be the team representative for an existing
		 team which doesn't appear below, please contact us.";
		$str.="<br/><br/>This database is incomplete.  It consists of about 2500 players and 100 teams; not all the players are in the database, and not all teams have the correct players on them.  Please make sure to get the correct players on your team for the coming Summer Season.";
		$str .= "<br/><br/>Payment for initial registration must be submitted as ONE CHECK or MONEY ORDER.";
		print "<div id='mainPar'>$str</div>";
		print "<div id='myTeams' style='text-align:center'>";
		if ($adm){
			print "<span id='updateMsg'>You're an administrator.</span>";
			print "<br/><input type='button' value='Add a New Player' onclick='window.location=\"editPlayer.php?add=1\"'>";
		}else{
			$myTeams = teamList($_SESSION['logon_uid']);
			print "$myTeams";
		}
		if ($isRef) print "<span id='updateMsg'>You're a referee.</span>";
		print "</body>";
		print "</html>";
	}else include("login.php");

	function teamList($uid) {
		$sql="SELECT CONCAT(s.name,' (',l.name,')') as s_name,  tl.tname as t_name, t.uid as team_id, s.uid as season_id,
			case registered WHEN 2 THEN 'registered' WHEN 1 THEN 'registration submitted' ELSE 'not registered' END AS reg_status
			FROM tmsl_team_manager tm
			INNER JOIN tmsl_team_season tl ON tm.team_uid=tl.team_uid
			INNER JOIN tmsl_team t ON tm.team_uid=t.uid
			INNER JOIN tmsl_season s ON tl.season_uid=s.uid and tm.season_uid=s.uid
			INNER JOIN tmsl_division l ON tl.division_uid=l.uid
			WHERE tm.user_uid=$uid ORDER BY s.start_date desc, s_name, t_name";
		//print $sql;
		$res=mysql_query($sql);
		while ($rec=mysql_fetch_assoc($res)) {
			//$arr[$rec['s_name']][$rec['$team_id']]=$rec['t_name'];
			$s_name=$rec['s_name'];
			if ($s_name <> $prev_s_name) $str.= "<br/><span style='font-weight:bold'>$s_name</span><br/>";
			$prev_s_name = $s_name;
			$str.="<a href='roster.php?team_id={$rec['team_id']}&season_id={$rec['season_id']}'>{$rec['t_name']} ({$rec['reg_status']})</a><br/>";
		}
		return $str;
	}
?>
