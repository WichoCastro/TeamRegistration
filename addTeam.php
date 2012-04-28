<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($season_id && $team_name) {
			$sql="INSERT tmsl_team (bond_owed, name, colors) VALUES (100, '$team_name', '$colors')";
			mysql_query($sql) or die("That name already exists");
			$team_id=mysql_insert_id();
			$_SESSION['team_uid']=$team_id;
			$_SESSION['season_uid']=$season_id;
			$sql="SELECT division_uid FROM tmsl_season WHERE uid=$season_id";
			$res=mysql_query($sql);
			$rec=mysql_fetch_array($res);
			$league_id=$rec['division_uid'];
			$sql="INSERT tmsl_team_season (tname, team_uid, division_uid, season_uid, start_date) VALUES ('$team_name', $team_id, $league_id, $season_id, now())";
			mysql_query($sql) or die("$sql".mysql_error());
			dbInsert('tmsl_team_manager', array('user_uid'=>$_SESSION['logon_uid'], 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
			if (!$url) $url='roster.php';
			$str="Location:$url";
			header($str);
		}else{
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Add A Team</div>";
			print "<div id='mainPar'>";
			print "Use this page to create a new team.  The name must be unique.<br/>";
			$arrSeasons=buildSimpleSQLArr("uid", "name", "SELECT s.uid, CONCAT(s.name, ' (', l.name,')') as name FROM tmsl_season s INNER JOIN tmsl_division l ON s.division_uid=l.uid WHERE s.stop_date > now()");
			print "<form method='post'>";
			//print $_SERVER['HTTP_REFERER'];
			print "<input type='hidden' name='url' value='".$_SERVER['HTTP_REFERER']."'>";
			print "<table align='center'>";
			print "<tr><td>Season:</td><td>";
			print getSelect("season_id", $arrSeasons, array(0=>"--Select--"), $_SESSION['season_uid'], "");
			print "</td></tr><tr><td>Team Name:</td><td><input type='text' name='team_name' style='width:200px'></td></tr>";
			print "<tr><td>Team Colors:</td><td><input type='text' name='colors' style='width:200px'></td></tr>";
			print "<tr><td colspan='2' align='center'><input type='submit' value='ok'></td></tr>";
			print "</table>";
			print "</form>";
			print "<input type='button' value='Search Teams' onclick='window.location=\"team.php\"'>";
			print "</div>";
			print "</body>";
			print "</html>";
		}
	}else include("login.php");
?>
