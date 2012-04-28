<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($sb) {
			dbUpdate('tmsl_team_season', array('tname'=>"$tname"), array('team_uid'=>$team_id, 'season_uid'=>$season_id));
			$url="Location:roster.php?team_id=$team_id&season_id=$season_id";
			header($url);
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Change Team Name</div>";
		print "<div id='mainPar'>";
		$tname=getTeamName($team_id, $season_id);
		print "<form>";
		print "<input type='text' name='tname' value=\"$tname\">";
		print "<input type='hidden' name='team_id' value='$team_id'>";
        print "<input type='hidden' name='season_id' value='$season_id'>";
		print "<input type='Submit' name='sb' value='ok'>";
		print "</form>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
