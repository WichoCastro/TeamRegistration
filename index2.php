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
		print "<div id='ttlBar'>Home</div>";
		$str = "This is the TMSL Registration site.  It is currently under development.  The data are strictly fictional -- feel free to manipulate them at your leisure.";
		$str .= "<br/>Begin by selecting a link from the menu above.";
		print "<div id='mainPar'>$str</div>";
		$myTeams = teamList($_SESSION['uid']);
		print "<div id='myTeams'>$myTeams</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");

	function teamList($uid) {
		$sql="SELECT s.name as s_name,  t.name as t_name, t.uid as team_id
			FROM tmsl_team_manager tm
			INNER JOIN tmsl_team_league tl ON tm.team_uid=tl.team_uid
			INNER JOIN tmsl_team t ON tm.team_uid=t.uid
			INNER JOIN tmsl_season s ON tl.season_uid=s.uid WHERE tm.user_uid=$uid ORDER BY s.start_date desc, s_name, t_name";
		print $sql;
		$res=mysql_query($sql);
		while ($rec=mysql_fetch_assoc($res)) {
			//$arr[$rec['s_name']][$rec['$team_id']]=$rec['t_name'];
			$s_name=$rec['s_name'];
			if ($s_name <> $prev_s_name) $str.= "<br/><span style='font-weight:bold'>$s_name</span><br/>";
			$prev_s_name = $s_name;
			$str.="<a href='roster.php?team_id={$rec['$team_id']}'>{$rec['t_name']}</a><br/>";
		}
		return $str;
	}
?>
