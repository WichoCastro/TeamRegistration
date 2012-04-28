<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($sbm) {
			dbDelete('tmsl_game', array('uid'=>$uid));
			print "<script>window.opener.location.href=window.opener.location;window.close();</script>";
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<style>td {font-size:0.7em;}th {font-size:0.7em;} table {border-collapse:collapse;} body {margin-top:0;}</style>";
		print "</head>";
		print "<body>";
    print "<div id='ttlBar'>DELETE GAME?</div>";
		print "<div ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></div>";
		print "<div id='mainPar'>";
		print "<div style='font-size:14pt'>DELETE GAME #$uid?</div>";
		$sql="SELECT DATE_FORMAT(game_dt, '%M %e, %Y') as game_date,
						DATE_FORMAT(game_tm, '%h:%i') as game_time, game_loc as field,
						th.tname as home_team, g.team_h_score as home_goals,
						tv.tname as visitor, g.team_v_score as visitor_goals,
						s.name as season, d.name as division
						FROM tmsl_game g JOIN tmsl_team_season th ON g.season_uid=th.season_uid AND g.team_h=th.team_uid
						JOIN tmsl_team_season tv ON g.season_uid=tv.season_uid AND g.team_v=tv.team_uid
						JOIN tmsl_season s ON g.season_uid=s.uid
						JOIN tmsl_division d ON d.uid=s.division_uid
						WHERE g.uid=$uid";

		print printTable($sql);


		print "<br/>Are you sure you want to delete this game?";

		print "<form>";
		print "<input type='submit' style='background-color:red' value='DELETE THIS GAME!' name='sbm'>";
		print "<input type='hidden' value='$uid' name='uid'>";
		print "</form>";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
