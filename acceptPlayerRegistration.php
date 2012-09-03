<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (hasPermission($_SESSION['mask'], $team_id, $season_id)) {
			if ($commit) {
				$notes=mysql_escape_string($notes);
				if (!$bal) $bal=0;
				$ret=dbUpdate('tmsl_player_team', array('registered'=>$reg_status, 'notes'=>$notes, 'balance'=>$bal, 'pay_pending'=>0),
					array('player_uid'=>$uid, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
				header("Location:roster.php");
			}
			$player = new Person($uid, $team_id, $season_id);
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Registration Accepted</div>";
			print "<div id='mainPar'>";
			$player_nm=getScalar('uid', $uid, "CONCAT(fname,' ',lname) AS name", 'tmsl_player');
			//$team_nm=getScalar('uid', $team_id, 'name', 'tmsl_team');
			$team_nm=getTeamName($team_id, $season_id);
			$sql="SELECT CONCAT(s.name, ' (', d.name, ')') FROM tmsl_season s
				JOIN tmsl_division d ON s.division_uid=d.uid
				WHERE s.uid=$season_id";
			$season_nm=getScalar("", "", "", "", $sql);
			$arr=dbSelect('tmsl_player_team', array('registered','notes','balance'), array('player_uid'=>$uid, 'team_uid'=>$team_id, 'season_uid'=>$season_id));
			if (empty($arr)) {$status=0;$notes='';$bal=0;}
			else {$status = $arr[0]['registered'];$notes = $arr[0]['notes'];$bal=$arr[0]['balance'];}

			print "Registration: $player_nm  for <a href='roster.php?team_id=$team_id&season_id=$season_id'>$team_nm</a> -- $season_nm";
			print "<br/><br/>This player has a balance of $".$bal.".";
			$not1 = $player->waiverSigned ? "" : "not";
			print "<br/>This player has $not1 signed the waiver.";
			$not2 = $player->dobValidated ? "" : "not";
			print "<br/>This player's date of birth has $not2 been validated.<br/>";
			if (!$not1 && !$not2) {$register = 2; $sel2="selected='true'";}
			else {$register = 0;$sel0="selected='true'";}
			print "<br/><form method='post'>";
			print "Set the balance to <input type='text' name='bal' value='0' size='4'><br/>";
			print "<br/>Notes:<br/>";
			print "<textarea name='notes' rows='4' cols='60'>$notes</textarea><br/>";
			print "<br/><br/>Select the desired registration status:<br/>";
			print "<select name='reg_status'>";
			print "  <option value=0 $sel0>Not Registered</option>";
			print "  <option value=1>Registration Submitted</option>";
			print "  <option value=2 $sel2>Registered</option>";
			print "  <option value=3>Registration Cancelled</option>";
			print "</select><br/>";
			print "<input type='submit' value='ok' name='commit'>";
			print "</form>";
			print "</div>";
			print "</body>";
			print "</html>";
		}
	}else include("login.php");
?>
