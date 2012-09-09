<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($clearTeam) $_SESSION['team_uid']=0;
		print $beginning;
		print "<div id='ttlBar'>Players</div>";
		print "<div id='mainPar'>";
		print "<form method='get'>";
		print "<table border='1' align='center'>";

		print "<tr><td>Last Name:</td><td><input type='text' name='lname' value='$lname'></td></tr>";
		print "<tr><td>First Name:</td><td><input type='text' name='fname' value='$fname'>";
		print "<input type='hidden' name='addUser' value='$addUser'>";
		print "<input type='hidden' name='getTeamRep' value='$getTeamRep'>";
		print "<input type='hidden' name='team_id' value='$team_id'>";
		print "<input type='hidden' name='season_id' value='$season_id'></td></tr>";
		print "<tr><td colspan='2'>";
		if ($srchCri=='exact') $ck="checked='checked'";
		print "<input type='radio' name='srchCri' value='exact' $ck>Exact";
		if (!$srchCri || $srchCri=='sounds_like') $ck="checked='checked'";else $ck="";
		print "<input type='radio' name='srchCri' value='sounds_like' $ck>Sounds Like";
		if ($srchCri=='starts_with') $ck="checked='checked'";else $ck="";
		print "<input type='radio' name='srchCri' value='starts_with' $ck>Starts With";
		if ($srchCri=='contains') $ck="checked='checked'";else $ck="";
		print "<input type='radio' name='srchCri' value='contains' $ck>Contains</td></tr>";
		print "<tr><td>Team:</td><td><select name='teamPlayedFor_uid'>";
		print "<option value='0'>Any Team</option>";
		$sql="SELECT * FROM tmsl_team ORDER BY name";  //FIX
		$res=mysql_query($sql);
		while ($rec=mysql_fetch_array($res)) {
			if ($teamPlayedFor_uid == $rec['uid']) $sel="selected='selected'"; else $sel="";
			print "<option value='".$rec['uid']."' $sel>".$rec['name']."</option>";
		}
		print "</select></td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";

		print "</table>";
		print "</form>";

		if ($srch) {
			if ($srchCri=='starts_with') {$fname.="%";$lname.="%";}
			if ($srchCri=='contains') {$fname="%$fname%";$lname="%$lname%";}
			$fname=strtoupper($fname);
			$lname=strtoupper($lname);
			$whereClauseArr=array();
			if ($fname)
				if ($srchCri=='sounds_like') $whereClauseArr[]="SOUNDEX(fname) = SOUNDEX('$fname')";
				else $whereClauseArr[]="UPPER(fname) LIKE '$fname'";
			if ($lname)
				if ($srchCri=='sounds_like') $whereClauseArr[]="SOUNDEX(lname) = SOUNDEX('$lname')";
				else $whereClauseArr[]="UPPER(lname) LIKE '$lname'";
			if ($teamPlayedFor_uid) $whereClauseArr[]="t.uid=$teamPlayedFor_uid";
			$whereClause = implode(" AND ", $whereClauseArr);
			if ($teamPlayedFor_uid) $sql="SELECT distinct p.uid, p.lname, p.fname, p.uid FROM tmsl_player p INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
				INNER JOIN tmsl_team t ON pt.team_uid=t.uid ";  //FIX
			else $sql="SELECT p.* FROM tmsl_player p ";
			if ($whereClause) $sql .= " WHERE $whereClause ";
			$sql .= " ORDER BY lname, fname";
			$res=mysql_query($sql);
			$noRecs=true;
			if ($addUser) print "Click the green checkmark to grant the player logon privileges:";
			elseif ($team_id) print "Click the green checkmark to add the player to the team:";
			print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
			while ($rec=mysql_fetch_assoc($res)) {
				print "<tr><td>";
				if ($addUser)
					print "
						<a href='admin.php?player_id={$rec['uid']}&addUser=1'>
						<img alt='Add' src='images/accept.png' title='Add as System User' border='0'>
					   </a>";
				elseif ($getTeamRep)
					print "<a href='addTeamRep.php?player_id={$rec['uid']}&makePlayerTeamRep=1&team_id=$team_id'>
						 <img alt='Add' src='images/accept.png' title='Make Team Rep' border='0'>
					   </a>";
				elseif ($team_id)
					print "<a href='addPlayerToTeam.php?player_id={$rec['uid']}&team_id=$team_id&season_id=$season_id'>
						<img alt='Add' src='images/accept.png' title='Add To Team' border='0'>
					  </a>";
				else
					print "<a href='editPlayer.php?edit=1&uid={$rec['uid']}'>
						<img alt='Edit' src='images/pencil.png' title='Edit' border='0'>
					  </a>";
				if ($_SESSION['mask'] & 4)
					print "<a href='editPlayer.php?del=1&uid={$rec['uid']}'>
						<img alt='Delete' src='images/delete.png' title='Delete' border='0'>
					  </a>";
				//print "<a href='playerHistory.php?uid={$rec['uid']}'>
				//		<img alt='History' src='images/history.png' title='History' border='0'>
				print "</td>";
				print "<td><a href='#' onclick='window.open(\"editPlayer.php?view=1&uid={$rec['uid']}\", \"pWin\", \"width=1000, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\")'>".$rec['lname'].", ".$rec['fname']."</a></td></tr>";
				$noRecs=false;
			}
			if ($noRecs) print "<tr><td>Your search returned no results.</td></tr>";
			print "</table>";
		}
		if ($team_id) print "<br/>Player is not in TMSL's database --<a href='editPlayer.php?team_id=$team_id&season_id=$season_id&add=1'>add a new player</a>";
		elseif ($_SESSION['mask']>3) print "<br/><input type='button' value='Add a New Player' onclick='window.location=\"editPlayer.php?add=1\"'>";

		print "</div>";

		print "</body>";
		print "</html>";
	}else include("login.php");
?>
