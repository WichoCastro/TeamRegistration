<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($_SESSION['mask'] & 1) {
			if ($commit) {
				foreach ($team_id as $tid) {
					if (hasPermission($_SESSION['mask'], $tid, $season_id)) {
						//$sql="DELETE FROM tmsl_player_team WHERE team_uid IN (".implode(',', $team_id).") AND season_uid=$season_id";
						//print $sql;
						dbDelete('tmsl_player_team', array('team_uid'=>$tid, 'season_uid'=>$season_id), true);
						//mysql_query($sql);
						//$sql="DELETE FROM tmsl_team_season WHERE team_uid IN (".implode(',', $team_id).") AND season_uid=$season_id";
						//print $sql;
						//mysql_query($sql);
						dbDelete('tmsl_team_season', array('team_uid'=>$tid, 'season_uid'=>$season_id), true);
					}
				}
				if ($_SESSION['mask'] & 2) $url="Location:teamSeason.php?season_id=$season_id";
				else $url="Location:index.php";
				header($url);
			}
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Drop Team From Season</div>";

			print "<div id='mainPar'>";
			$season_nm=getScalar('uid', $season_id, 'name', 'tmsl_season');
			print "Are you sure you want to drop the teams listed below from $season_nm?<br/><br/>";
			$sql="SELECT tname as name FROM tmsl_team_season WHERE season_uid=$season_id AND team_uid IN (".implode(',', $team_id).")";
			$res=mysql_query($sql);
			while ($rec=mysql_fetch_array($res)) {
				print $rec['name']."<br/>";
			}
			print "<br/><input type='button' value='YES' onclick='window.location=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&commit=1\"'>";
			print "</div>";
			print "</body>";
			print "</html>";
		}else print "Inadequate permissions to access this page";
	}else include("login.php");
?>
