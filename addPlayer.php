<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		//This page needs a team_uid to exist.  If not, redir to teams page.
		if (!$team_uid) header("Location:roster.php");
		if ($player_exists == '0') {
			header("Location: editPlayer.php?add=1&team_id=$team_id&season_id=$season_id");
		}else{
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Add Player</div>";
			print "<div id='mainPar'>";
			if ($player_exists == '1') {
				//print "Here we search and then redirect to edit player";
				print "<form method='post'>";
				print "Last Name: <input type='text' name='lname'><br/>";
				print "First Name: <input type='text' name='fname'><br/>";
				print "<input type='hidden' name='team_uid' value='$team_uid'>";
				print "<input type='hidden' name='season_uid' value='$season_uid'>";
				print "<input type='submit' name='srch' value='Search'>";
				print "</form>";
			}elseif ($srch) {
				print "Click on one of the players below or <a href='addPlayer.php'>search again</a>...<br/>";
				$sql="SELECT * FROM tmsl_player WHERE lname like '%$lname%' AND fname like '%$fname%'";
				$res=mysql_query($sql);
				$noRecs=true;
				while ($rec=mysql_fetch_assoc($res)) {
					print "<form action='addPlayerToTeam.php' method='post'>
						<input type='hidden' name='player_id' value='".$rec['uid']."'>
						<input type='hidden' name='team_uid' value='$team_uid'>
						<input type='hidden' name='season_uid' value='$season_uid'>
						<input type='radio' onclick='submit();'>";
					print $rec['lname'].", ".$rec['fname']."</form>";
					$noRecs=false;
				}
				if ($noRecs) print "<br/>Your search returned no results.";
			}else{
			  print "If this is a new player, or the player is not in the TMSL Database, use this button:<br/>";
				print "<form method='get' action='editPlayer.php'>";
				print "<input type='hidden' name='team_id' value='$team_uid'>";
				print "<input type='hidden' name='season_id' value='$season_uid'>";
				print "<input type='hidden' name='add' value='1'>";
				print "<input type='submit' name='player_exists_not' value='Add a new player to the TMSL Database'>";
				print "</form>";

				print "If the player has played for TMSL before, use this button first:<br/>";
				print "<form method='get' action='player.php'>";
				print "<input type='hidden' name='team_id' value='$team_uid'>";
				print "<input type='hidden' name='season_id' value='$season_uid'>";
				print "<input type='hidden' name='add' value='1'>";
				print "<input type='submit' name='player_exists_not' value='Search the TMSL Database for the player'>";
				print "</form>";
			}
			print "</div>";
			print "</body>";
			print "</html>";
		}
	}else include("login.php");
?>
