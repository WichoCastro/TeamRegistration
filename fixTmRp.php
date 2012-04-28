<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print "fixing team reps<br/>";
		$ssns=dbSelectSQL("SELECT uid FROM tmsl_season WHERE start_date > now()");
		foreach($ssns as $r) {
			$season_id=$r['uid'];
			print "s=$season_id<br>";
			$a1=dbSelectSQL("SELECT team_uid FROM tmsl_team_season WHERE season_uid=$season_id");
			foreach($a1 as $tm) {
				$team_id=$tm['team_uid'];
				print "t=$team_id<br>";
				//already copied over?
				$a2=dbSelectSQL("SELECT count(*) as ct FROM tmsl_team_manager WHERE season_uid=$season_id and team_uid=$team_id");
				if ($a2[0]['ct']>0) {print "$team_id already done<br>";continue;}
				else {
					//find last season
					$a3=dbSelectSQL("SELECT season_uid FROM tmsl_team_season WHERE season_uid <> $season_id AND team_uid=$team_id ORDER BY start_date DESC");
					$last_season_id=$a3[0]['season_uid'];
					$sql="INSERT tmsl_team_manager SELECT user_uid, team_uid, active, $season_id, primary_captain FROM tmsl_team_manager WHERE  season_uid=$last_season_id and team_uid=$team_id";
					print "sql=$sql<br>";
					if (mysql_query($sql)) print "now $team_id is done<br>";
					
				}	
			}
		}
	}else include("login.php");
?>
