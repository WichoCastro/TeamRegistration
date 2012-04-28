<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($register) {
			//exit;
			$valArr=array('registered'=>'1');
			$whrArr=array('team_uid'=>$team_id,'season_uid'=>$season_id);
			dbUpdate('tmsl_team_season', $valArr, $whrArr, 1, 1);
			dbUpdate('tmsl_player_team', $valArr, $whrArr, 1, 1);
			$str="Location:invoice.php?team_id=$team_id&season_id=$season_id";
			header($str);
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Registration</div>";

		print "<div id='mainPar'>";
		if (hasPermission($_SESSION['mask'], $team_id, $season_id)) {

			$team_nm=getTeamName($team_id, $season_id);
			print "Registration for $team_nm<br/>";
			$sql="SELECT count(1) as ct FROM `tmsl_player_team` WHERE team_uid=$team_id and season_uid=$season_id";
/* Don't do this here - send to invoice
			$res=mysql_query($sql) or die("$sql --".mysql_error());
			$rec=mysql_fetch_assoc($res);
			$numPlayersOnRoster=$rec['ct'];
			$sql="SELECT count(1) as ct FROM `tmsl_player_team` pt INNER JOIN tmsl_player p ON pt.player_uid=p.uid
				WHERE team_uid=$team_id and season_uid=$season_id and p.boardMember>0";
			$res=mysql_query($sql) or die("$sql --".mysql_error());
			$rec=mysql_fetch_assoc($res);
			$numBoardMembersOnRoster=$rec['ct'];
			print "There are $numPlayersOnRoster players on the team.<br/>";
			if ($numBoardMembersOnRoster) {
				print "There are $numBoardMembersOnRoster non-paying players on the team.<br/>";
				$numPlayersOnRoster -= $numBoardMembersOnRoster;
			}
			$costPerPlayer=getScalar('uid', $season_id, 'cost_per_player', 'tmsl_season');
			print "The cost per player is $".$costPerPlayer."<br/>";
			print "Your total amount due is $".($costPerPlayer * $numPlayersOnRoster)."<br/>";
*/
			print "Click the green button to register, and follow the instructions regarding payment on the next page<br/>";
			print "<form>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "<input type='hidden' name='season_id' value='$season_id'>";
			print "<input type='submit' value='Register' name='register' class='majAction'>";
			print "<input type='button' value=\"Don't Register\" onClick='window.location=\"roster.php?team_id=$team_id&season_id=$season_id\"' class='pointer'>";
			print "</form>";
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
