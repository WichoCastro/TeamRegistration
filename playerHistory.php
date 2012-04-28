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
		print "<div id='ttlBar'>Player History</div>";

		print "<div id='mainPar'>";
		$sql = "SELECT c.tname, c.team_uid, c.start_date, c.season_uid, a.stop_date, 0 as dropped from tmsl_player_team a
			INNER JOIN tmsl_team b ON a.team_uid=b.uid
                        INNER JOIN tmsl_team_season c ON c.team_uid=b.uid AND c.season_uid=a.season_uid
                        WHERE a.player_uid=$uid and a.registered=2 AND c.registered=2
			UNION
			SELECT c.tname, c.team_uid, c.start_date, c.season_uid, a.drop_date as stop_date, 1 as dropped from tmsl_dropped a
			INNER JOIN tmsl_team b ON a.team_uid=b.uid
                        INNER JOIN tmsl_team_season c ON c.team_uid=b.uid AND c.season_uid=a.season_uid
                        WHERE a.player_uid=$uid and a.registered=2 AND c.registered=2
			ORDER BY start_date DESC, tname";
		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
		  $season=getSeasonName($rec['season_uid']);
		  print "-------------------------<br/>";
		  print "<a href='roster.php?team_id=".$rec['team_uid']."&season_id=".$rec['season_uid']."'>".$rec['tname']." -- $season</a>";
		  if ($rec['dropped']) print " (dropped ".$rec['stop_date'].")";
		  $susp = suspensionHistory($uid, $rec['season_uid']);
		  if (count($susp))
				foreach($susp as $fld=>$val) {
					print "<br/>";
					if ($adm) print "<a href='reportSuspended.php?edit=1&s_uid={$val['uid']}''><img alt='Suspension Details' src='images/pencil.png' title='Suspension Details' border='0'></a>";
					print "SUSPENSION: {$val['start_date']} - {$val['stop_date']} {$val['reason']}<br/>";
				}
		  print "<br/>";
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
