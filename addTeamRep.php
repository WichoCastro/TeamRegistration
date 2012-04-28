<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!hasPermission($_SESSION['mask'], $team_id, $season_id) || !$team_id) header("Location:roster.php");

		if ($player_id) {
			$arr=dbSelect('tmsl_player', array('fname', 'lname', 'email'), array('uid'=>$player_id));
			$fname=$arr[0]['fname'];
			$lname=$arr[0]['lname'];
			$email=$arr[0]['email'];
			$username=strtolower(substr($fname, 0, 1).$lname);
			dbInsert('tmsl_team_manager', array('user_uid'=>$player_id, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
			$exists=getScalar('player_uid', $player_id, 'player_uid', 'tmsl_user');
			if (!$exists) {
				$user_arr=array('name'=>$username, 'pwd'=>sha1(strtolower($username)), 'player_uid'=>$player_id);
				dbInsert('tmsl_user', $user_arr, true, true);
			}
			$msg .=  "User $username added.  Password set to $username -- please inform user to change it.<br/>";
			if (!$msg) header("Location:roster.php");
		}


		if ($addUserCommit) {
			/*User may have typed in a name we already have - check for that*/
			if (!$player_id) {
				$player_id=getScalar('LOWER(CONCAT(fname,lname))', strtolower($fname.$lname), 'uid', 'tmsl_player');
				if ($player_id) $msg.="Found existing player with that name.<br/>";
			}
			/*If username is not filled in, use first initial plus last name*/
			if (!$username) $username=strtolower(substr($fname, 0, 1).$lname);
			/*See if the username exists*/
			$uid=getScalar('name', $username, 'player_uid', 'tmsl_user');

			if (!$player_id) {
				$player_arr=array('fname'=>$fname, 'lname'=>$lname,
					'addr'=>$addr,'city'=>$city,'state'=>$state,'zip'=>$zip,'email'=>$email,'phone'=>$phone);
				$user_arr=array('name'=>$username, 'pwd'=>sha1(strtolower($username)));
				$player_id=dbInsert('tmsl_player', $player_arr, true, false);
				$user_arr['player_uid']=$player_id;
				dbInsert('tmsl_user', $user_arr, true, false);
				dbInsert('tmsl_team_manager', array('user_uid'=>$player_id, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
				$msg .=  "User $username added.  Password set to $username -- please inform user to change it.<br/>";
			}else{
				/*if a pleyer_id was passed in, it must match the id that goes with the username*/
				if ($player_id && $uid && $player_id != $uid) {$okay=false;$msg="Another person has username $username -- please specify a different one.";}
				else	{
					dbInsert('tmsl_team_manager', array('user_uid'=>$player_id, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
					$exists=getScalar('player_uid', $player_id, 'player_uid', 'tmsl_user');
					if (!$exists) {
						$user_arr=array('name'=>$username, 'pwd'=>sha1(strtolower($username)), 'player_uid'=>$player_id);
						dbInsert('tmsl_user', $user_arr, true, true);
					}
					$msg .=  "User $username added.  Password set to $username -- please inform user to change it.<br/>";
				}
			}
			if (!$msg) header("Location:roster.php");
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Add Team Rep</div>";
		print "<div id='mainPar'>";
		if ($msg) print "<span id='updateMsg'>$msg<br/></span><br/>";
		else {
			print "Since TMSL communicates primarily through email, an email address is required to be a team representative.<br>";
			print "Please make sure the email address is valid and that email is checked on a regular basis.<br>";
			print "These are the players on the team with email addresses.  ";
			print "Select the player to add as a team representative:";
			print "<form>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "<input type='hidden' name='season_id' value='$season_id'>";
			print "<input type='hidden' name='player_id' value='$player_id'>";
			$sql="SELECT DISTINCT p.uid, CONCAT(p.lname,', ',p.fname) as name FROM tmsl_player p INNER JOIN
				tmsl_player_team pt ON pt.player_uid=p.uid WHERE pt.team_uid=$team_id
				AND pt.player_uid NOT IN (SELECT user_uid FROM tmsl_team_manager WHERE team_uid=$team_id AND season_uid=$season_id)
				AND email <> ''
				ORDER BY name";
			$arrPlayers=buildSimpleSQLArr("uid", "name", $sql);
			print getSelect("player_id", $arrPlayers, array(0=>'--Select--'), $player_id, "onchange=submit()");
			print "<input type='submit' name='makePlayerTeamRep' value='ok'>";
			print "</form>";
		}
		print "<input type='button' value='Back to Roster' onclick='window.location=\"roster.php?team_id=$team_id&season_id=$season_id\"'>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
