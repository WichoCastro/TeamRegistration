<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!$team_id) 		$team_id=$_GET['team_id'];
		if (!$season_id) 	$season_id=$_GET['season_id'];
		if (!hasPermission($_SESSION['mask'], $team_id, $season_id) || !$team_id || !$season_id) header("Location:roster.php");
/*
		if ($new_team_name) {
			$sql="UPDATE tmsl_team SET name='$new_team_name' WHERE uid=$team_id";
			header("Location:team.php");
		}
*/
		if ($rollover_season_to_id) {
			$url="Location:rollover.php?team_id[]=$team_id&season_id=$rollover_from_season_id&new_season_id=$rollover_season_to_id";
			header($url);
		}

		if ($drop_season_id) {
			$league_id = getScalar('uid', $drop_season_id, 'division_uid', 'tmsl_season');
			dbDelete('tmsl_team_season',array('team_uid'=>$team_id,'season_uid'=>$drop_season_id));
			dbDelete('tmsl_player_team',array('team_uid'=>$team_id,'season_uid'=>$drop_season_id));
		}

		if ($reallyDeleteTeamRep)
			dbDelete('tmsl_team_manager', array('user_uid'=>$uid, 'team_uid'=>$team_id), true);

		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;

		//get team name & season
		//$season_name=getSeasonName($season_id);
		$team_name =getTeamName($team_id, $season_id);

		print "<div id='ttlBar'>$team_name</div>";
		print "<div id='mainPar'>";

		if ($deleteTeamRep) {
			$nm=strtoupper(getUserName($uid));
			print "REALLY DROP $nm AS TEAM REP?<br/>";
			print "<form name='frm' id='frm' method='get'>";
			print "<input type='hidden' name='uid' value='$uid'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "<input type='hidden' name='season_id' value='$season_id'>";
			print "<input type='submit' value='YES' name='reallyDeleteTeamRep' style='color:red'>";
			print "<input type='button' value='NO' onclick='window.location=\"editTeamDetails.php?team_id=$team_id&season_id=$season_id\"'>";
			print "</form>";
			print "If you drop a team rep, (s)he can't edit the data on this website.";
			exit;
		}

		//get team rep info
		print "Team Reps:<br/>";
		$arr=dbSelect('tmsl_team_manager tm join tmsl_player p on tm.user_uid=p.uid', array('uid', 'fname', 'lname', 'email'), array('team_uid'=>$team_id));
		if (count($arr))
			foreach($arr as $rep) {
				print "<a href='editTeamDetails.php?uid=".$rep['uid']."&team_id=$team_id&season_id=$season_id&deleteTeamRep=1'><img src='images/delete.png' alt='delete' title='delete' border='0'></a>";
				print "<a href='mailto:".$rep['email']."'>".$rep['fname']." ".$rep['lname']."</a><br/>";
			}
		else print "None<br/>";
		print "<a href='addTeamRep.php?team_id=$team_id'><img src='images/add.png' alt='add' title='add team rep' border='0'>Add Team Rep</a><br/><br/>";

/*
		//change name
		print "<form method='post'>";
		print "Change Name To:<input type='text' name='new_team_name' value='$team_name'><br/>";
		print "<input type='submit' value='Change Name'>";
		print "</form>";
*/
		//roll over to next season
		print "<form method='post'>";
		print "Copy roster from: ";
		$sql="SELECT CONCAT(s.name, ' (',l.name,')') as name, s.uid FROM tmsl_season s
					INNER JOIN tmsl_division l ON s.division_uid=l.uid
					WHERE s.uid IN (SELECT season_uid FROM tmsl_team_season WHERE team_uid=$team_id)
					ORDER BY name";
		$arrSeasons=buildSimpleSQLArr("uid", "name", $sql);
		print getSelect("rollover_from_season_id", $arrSeasons, array(0=>"--Select--"), $season_id, "");
		if ($_SESSION['mask'] == 255) {
			$sql="SELECT uid, name FROM tmsl_team ORDER BY name";
		}
		print " to: ";
		$sql="SELECT CONCAT(s.name, ' (',l.name,')') as name, s.uid FROM tmsl_season s
					INNER JOIN tmsl_division l ON s.division_uid=l.uid
					WHERE s.stop_date > now() and s.uid NOT IN (SELECT season_uid FROM tmsl_team_season WHERE team_uid=$team_id)";
		$arrSeasons=buildSimpleSQLArr("uid", "name", $sql);
		print getSelect("rollover_season_to_id", $arrSeasons, array(0=>"--Select--"), "", "");
		print "<br/><input type='submit' value='Roll Over'>";
		print "</form>";

		//drop from a season
		$sql="SELECT CONCAT(s.name, ' (',l.name,')') as name, s.uid FROM tmsl_season s
					INNER JOIN tmsl_division l ON s.division_uid=l.uid
					WHERE s.stop_date > now() and s.uid IN (SELECT season_uid FROM tmsl_team_season WHERE team_uid=$team_id)";
		//print $sql;
		$arrSeasons=buildSimpleSQLArr("uid", "name", $sql);
		print "<form method='post'>";
		print "Drop team from: ";
		print getSelect("drop_season_id", $arrSeasons, array(0=>"--Select--"), "", "");
		print "<br/><input type='submit' value='Drop'>";
		print "</form>";

		//join a different division this season
		$sql="SELECT CONCAT(s.name, ' (',l.name,')') as name, s.uid FROM tmsl_season s
					INNER JOIN tmsl_division l ON s.division_uid=l.uid
					WHERE s.stop_date > now() AND s.uid <> $season_id
					AND s.stop_date > (SELECT start_date FROM tmsl_season WHERE uid=$season_id)
					AND s.start_date < (SELECT stop_date FROM tmsl_season WHERE uid=$season_id)";
		//print $sql;
		$arrDivisions=buildSimpleSQLArr("uid", "name", $sql);
		print "<form method='post'>";
		print "Move team to another division: ";
		print getSelect("move_season_id", $arrDivisions, array(0=>"--Select--"), "", "");
		print "<br/><input type='submit' value='Switch'>";
		print "</form>";

		//provide links to rosters, past, present & future
		print "Rosters:<br/>";
		$sql="SELECT * FROM tmsl_team_season WHERE team_uid=$team_id ORDER BY start_date";
		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
			$season=getSeasonName($rec['season_uid']);
		  print "<a href='roster.php?team_id=$team_id&season_id=".$rec['season_uid']."'>$season</a><br/>";
		}

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
