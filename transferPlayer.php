<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($toSeason && $toTeam) {
			//if the team has already registered, set this guy's registration status to 1, else 0:
			$team_reg_status=dbSelectSQL("SELECT registered FROM tmsl_team_season WHERE team_uid=$toTeam AND season_uid=$toSeason");
			if ($team_reg_status[0]['registered']>0) $player_reg_status=1;
			else $player_reg_status=0;
			$arr=dbSelectSQL("SELECT registered, balance FROM tmsl_player_team WHERE player_uid=$playerid AND team_uid=$fromTeam AND season_uid=$fromSeason");
			$reg=$arr[0]['registered'];
			if (!$reg) $reg=$player_reg_status;
			$bal=$arr[0]['balance']+$fee;
			dbInsert('tmsl_dropped', array('player_uid'=>$playerid, 'team_uid'=>$fromTeam, 'season_uid'=>$fromSeason, 'drop_date'=>date('Y-m-d'), 'registered'=>$reg), true, true);
			dbDelete('tmsl_player_team', array('player_uid'=>$playerid, 'team_uid'=>$fromTeam, 'season_uid'=>$fromSeason), true);
			dbInsert('tmsl_player_team', array('player_uid'=>$playerid, 'team_uid'=>$toTeam, 'season_uid'=>$toSeason,
				'registered'=>$player_reg_status,'start_date'=>date('Y-m-d'),'balance'=>"$bal"), true, true);
			header("Location: invoice.php?team_id=$toTeam&season_id=$toSeason");
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Transfer Player</div>";
		print "<div id='mainPar'>";
		if ($msg) {
			print "<span id='updateMsg'>$msg<br/></span><br/>";
			print "<span id='updateMsg'>As a site admin, you can override the rules -- Add player to team?<br/></span><br/>";
			print "<form><input type='hidden' name='playerid' value='$playerid'>";
			print "<input type='hidden' name='toTeam' value='$toTeam'>";
			print "<input type='hidden' name='toSeason' value='$toSeason'>";
			print "<input type='hidden' name='fee' value='$fee'>";
			print "<input type='submit' value='ADD' name='XX__X'></form>";
		}
		//show name, current team, list of teams, fee (default $5)
		//use function verifyAddPlayerToTeam
		$nm=getUserName($playerid);
		//$tm=getScalar('uid', $fromTeam, 'name', 'tmsl_team');
		$tm=getTeamName($fromTeam, $fromSeason);
		//$sz=getScalar('uid', $fromSeason, 'name', 'tmsl_season');
		$sz=getSeasonName($fromSeason);
		print "Transfer $nm from $tm ($sz):<br/>";
		print "<form>";
		print "<input type='hidden' name='fromTeam' value='$fromTeam'>";
		print "<input type='hidden' name='fromSeason' value='$fromSeason'>";
		print "<input type='hidden' name='playerid' value='$playerid'>";
		if (!$toSeason) $toSeason=$fromSeason;
		print "Season: ".getSelect('toSeason', buildSimpleSQLArr('uid', 'name', "SELECT s.uid, CONCAT(s.name,' - ',d.name)  as name FROM tmsl_season s INNER JOIN tmsl_division d ON s.division_uid=d.uid WHERE stop_date>now() ORDER BY start_date, name"), array(0=>"--Select--"), "$toSeason", "onchange='submit();'")."<br/>";
		if ($toSeason) print "Team: ".getSelect('toTeam', buildSimpleSQLArr('uid', 'name', "SELECT t.uid, t.name FROM tmsl_team t INNER JOIN tmsl_team_season ts ON t.uid=ts.team_uid WHERE season_uid=$toSeason"), array(0=>"--Select--"), "$toTeam", "")."<br/>";
		if (!isset($fee)) $fee=5;
		print "Add a charge of $<input type='text' name='fee' value='$fee' size='4'>";
		print "<br/><input type='submit' value='ok'>";
		print "</form>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
