<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Rollover Team</div>";

		print "<div id='mainPar'>";
		if (!$new_season_id) {
			$sql="SELECT uid, name, start_date FROM tmsl_season WHERE last_day_team > now() AND uid <> $season_id ORDER BY start_date";
			$res=mysql_query($sql);
			if (mysql_num_rows($res) > 0) {
				print "Please choose the season to roll over to:</br/></br/>";
				while ($rec=mysql_fetch_assoc($res)) {
					print "<a href='rollover.php?season_id=$season_id&team_id[]=".implode('&team_id[]=',$team_id)."&new_season_id=".$rec['uid']."'>".getSeasonName($rec['uid'])."</a><br/>";
				}
			}elseif (mysql_num_rows($res) < 1) {
				print "NO SEASON FOUND";
				if (hasRegistrationAuthority($_SESSION['mask'])) print " -- <a href='manageSeasons.php'>create one?</a>";
			}else{
				$rec=mysql_fetch_assoc($res);
				$new_season_id=$rec['uid'];
			}
			exit;
		}
		//$new_season_nm=getScalar('uid', $new_season_id, 'name', 'tmsl_season');
		$new_season_nm=getSeasonName($new_season_id);
		if ($new_season_nm) {
			foreach ($team_id as $tid) {
				$playerMsgs="";
				$division_id = getScalar('uid', $new_season_id, 'division_uid', 'tmsl_season');
				$season_start_date = getScalar('uid', $new_season_id, 'start_date', 'tmsl_season');
				$team_nm = getTeamName($tid, $season_id);
				$now=date('Y-m-d');
				$user_id=$_SESSION['logon_uid'];
				$arr=array('team_uid'=>$tid, 'division_uid'=>$division_id, 'season_uid'=>$new_season_id, 'start_date'=>$season_start_date, 'registeredBy'=>$user_id,'registeredDate'=>$now, 'tname'=>$team_nm, 'notes'=>'');
				if ($confirmed) {
					dbInsert('tmsl_team_season', $arr, 1);
					if (!empty($p_id)) foreach($p_id as $pid) {
						$msg=verifyAddPlayerToTeam($pid, $tid, $new_season_id, true);
						if ($msg=="1" || ($_SESSION['mask'] == 255)) {
							$jrsy=0;
							$sql="select jersey_no from tmsl_player_team where team_uid=$tid and season_uid=$season_id and player_uid=$pid";
							$j_arr=dbSelectSQL($sql);
							//print "--$sql--";print_r($j_arr);
							$jrsy=$j_arr[0]['jersey_no'];
							addPlayerToTeam($pid, $tid, $new_season_id, $jrsy, true);
							$nm=getUserName($pid);
							$playerMsgs.="Added $nm<br>";
						} else {
							$nm=getUserName($pid);
							$playerMsgs.="Can't add $nm -- $msg<br>";
						}
					}
					print "Team $team_nm added to $new_season_nm.<br/>";
					print $playerMsgs;
					$sql="insert tmsl_team_manager select user_uid, team_uid, 1, $new_season_id, primary_captain from tmsl_team_manager WHERE team_uid=$tid AND season_uid=$season_id";
					$res=mysql_query($sql);
				} else { //not yet confirmed
					//fyi: this should only be called if there's just one team_id
					$sql="SELECT player_uid, start_date, registered FROM tmsl_player_team WHERE team_uid=$tid AND season_uid=$season_id";
					$playerArr=dbSelectSQL($sql);
					$playerTbl="<table align='center'>";
					foreach($playerArr as $p) {
						$pid=$p['player_uid'];
						if ($p['registered'] > 1) $ck='checked=true'; else $ck='';
						$msg=verifyAddPlayerToTeam($pid, $tid, $new_season_id, true);
						if ($msg=="1") {
							$nm=getUserName($pid);
							$playerTbl.="<tr><td><input type='checkbox' $ck name='p_id[]' id='p_id[]' value='$pid'>$nm</td></tr>";
						} else {
							$nm=getUserName($pid);
							if ($_SESSION['mask'] == 255) {
								$playerTbl.="<tr><td><input type='checkbox' name='p_id[]' id='p_id[]' value='$pid'><span title='$msg'>$nm</span></td></tr>";
							} else
								$playerTbl.="<tr><td><input type='checkbox' disabled='disabled' name='p_id[]' id='p_id[]' value='$pid'><span title='$msg'>$nm</span></td></tr>";
						}
					}
					$playerTbl.="</table>";
					//$sql="insert tmsl_team_manager select user_uid, team_uid, 1, $new_season_id from tmsl_team_manager WHERE team_uid=$tid AND season_uid=$season_id";
					//mysql_query($sql);
					print "<br>To register <b>$team_nm</b> for <b>$new_season_nm</b>:<br/>";
					print "Select the players that you want to register, then click <b>ok</b> below:<br/>";
					print "<span onclick='selectAll(true)' style='cursor:pointer; color:blue'>select all</span> | ";
					print "<span onclick='selectAll(false)' style='cursor:pointer; color:blue'>select none</span><br>";
					print "<form id='frm'>";
					print "<input type='hidden' name='team_id[]' value='$tid'>";
					print "<input type='hidden' name='season_id' value='$season_id'>";
					print "<input type='hidden' name='new_season_id' value='$new_season_id'>";
					print $playerTbl;
					print $playerMsgs;
					print "<input type='submit' value='ok' name='confirmed'>";
					print "</form>";
					print "<br>Players can still be added or removed after this.<br>";
				}
			}
			if ($confirmed)
				if (hasRegistrationAuthority($_SESSION['mask'])) print "<a href='teamSeason.php?season_id=$new_season_id'>$new_season_nm</a>";
				else print "<a href='roster.php?season_id=$new_season_id&team_id={$team_id[0]}'>$new_season_nm Roster</a>";
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
<script>
function selectAll(checked) {
	var aa = document.getElementById('frm');
	for (var i =0; i < aa.elements.length; i++)
	{
	 var nm = aa.elements[i].name;
	 if (nm != 'p_id') {
	 	aa.elements[i].checked = checked;
	 }
	}
}
</script>
