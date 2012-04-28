<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($XX__X && $_SESSION['mask'] & 4) {
			addPlayerToTeam($player_id, $team_id, $season_id, 0, true);
			header("Location: invoice.php?team_id=$team_id&season_id=$season_id");
		}
		if (!hasPermission($_SESSION['mask'], $team_id, $season_id) || !$team_id) $msg="You do not have permission to add a player to this team";
		else {
			// rules:  cant be on another team registered this season unless one is o45 and the other is not (yuck!)
			// age requirement (o45)
			// cannot exceed max_reg for season.  other?
			// cannot be past last_day_player

			if (!$msg) $msg=verifyAddPlayerToTeam($player_id, $team_id, $season_id);
		}

		if ($msg=='1') {
			//if the team has already registered, set this guy's registration status to 1, else 0:
			addPlayerToTeam($player_id, $team_id, $season_id);
			header("Location: invoice.php?team_id=$team_id&season_id=$season_id");
		}else{
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Add Player to Team</div>";
			print "<div id='mainPar'>";
			print "<span id='updateMsg'>$msg<br/></span><br/>";
			if ($_SESSION['mask'] & 4) {
				print "<span id='updateMsg'>As a site admin, you can override the rules -- Add player to team?<br/></span><br/>";
				print "<form><input type='hidden' name='player_id' value='$player_id'>";
				print "<input type='hidden' name='team_id' value='$team_id'>";
				print "<input type='hidden' name='season_id' value='$season_id'>";
				print "<input type='submit' value='ADD' name='XX__X'></form>";
			}
			print "</div>";
			print "</body>";
			print "</html>";
		}
	}else include("login.php");
?>
