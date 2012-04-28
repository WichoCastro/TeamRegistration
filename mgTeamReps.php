<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!hasPermission($_SESSION['mask'], $team_id, $season_id) || !$team_id) header("Location:roster.php");
		if (!$team_id || !$season_id) {
		  $msg = "ERROR OCCURRED";
		}
		if ($makePlayerTeamRep) {
			addTeamRep($player_id, $team_id, $season_id);
			$arr=dbSelect('tmsl_user', array('name'), array('player_uid'=>$player_id));
			$uname=$arr[0]['name'];
			$msg="$uname added as team rep";
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Manage Team Reps</div>";
		print "<div id='mainPar'>";
		if ($msg) print "<span id='updateMsg'>$msg<br/></span><br/>";
		else {
			print "Since TMSL communicates primarily through email, an email address is required to be a team representative.<br>";
			print "Please make sure the email address is valid and that email accountis checked on a regular basis.<br>";
			print "These are the players on the team with email addresses.  ";
			print "Select the player to add as a team representative:";
		}
		print "<form>";
		print "<input type='hidden' name='team_id' value='$team_id'>";
		print "<input type='hidden' name='season_id' value='$season_id'>";
		//print "<input type='hidden' name='player_id' value='$player_id'>";
		$sql="SELECT DISTINCT p.uid, CONCAT(p.lname,', ',p.fname) as name FROM tmsl_player p INNER JOIN
			tmsl_player_team pt ON pt.player_uid=p.uid WHERE pt.team_uid=$team_id
			AND pt.player_uid NOT IN (SELECT user_uid FROM tmsl_team_manager WHERE team_uid=$team_id AND season_uid=$season_id)
			AND email <> ''
			AND pt.season_uid=$season_id
			ORDER BY name";
		$arrPlayers=buildSimpleSQLArr("uid", "name", $sql);
		print getSelect("player_id", $arrPlayers, array(0=>'--Select--'), $player_id, "");
		print "<input type='submit' name='makePlayerTeamRep' value='ok'>";
		print "</form>";
		$sql="SELECT fname, lname, p.uid, primary_captain
			FROM tmsl_team_manager tm
			INNER JOIN tmsl_player p ON tm.user_uid = p.uid
			WHERE team_uid=$team_id AND season_uid=$season_id";
		$res=mysql_query($sql);
		//print "<table align='center'><th>Primary Captain</th><th>name</th></tr>";
		print "<table align='center'><th>name</th></tr>";
		while ($rec=mysql_fetch_assoc($res)) {
			//if ($rec['primary_captain']) $sel="checked='checked'"; else $sel="";
			//print "<tr><td><input type='radio' value='{$rec['uid']}' name='primary_captain' $sel></td>";
			print "<tr>";
			print "<td>{$rec['fname']} {$rec['lname']}</td></tr>";
		}
		print "</table>";
		print "<p>To delete a team rep, please contact a site administrator.</p>";
		print "<input type='button' value='Back to Roster' onclick='window.location=\"roster.php?team_id=$team_id&season_id=$season_id\"'>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
