<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($_SESSION['mask']==255) {
			if ($uid && $team_id) {
				if ($addTeam) {
					$addTeam=0;
					if (!dbInsert('tmsl_team_manager', array('user_uid'=>$uid, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, false))
						dbUpdate('tmsl_team_manager', array('active'=>1), array('user_uid'=>$uid, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
					dbUpdate('tmsl_user', array('last_team_uid'=>$team_id), array('player_uid'=>$uid), true, true);
				}
				if ($deleteTeam) {dbDelete('tmsl_team_manager', array('user_uid'=>$uid, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true);$url="Location:admin.php?uid=$uid";header($url);}
			}
			if ($sysAdmin) dbUpdate('tmsl_user', array('mask'=>$sysAdmin), array('player_uid'=>$uid), true);
			if ($is_ref>-1) {dbUpdate('tmsl_user', array('isReferee'=>$is_ref), array('player_uid'=>$uid), true);}
			if ($bm>-1) dbUpdate('tmsl_player', array('boardMember'=>$bm), array('uid'=>$uid), true);

			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Admin</div>";
			print "<div id='mainPar'>";

			if ($mail_x || $mail_y) {
				$em_arr=array();
				foreach($player_ids as $pid) {
					$em=getScalar('uid', $pid, 'email', 'tmsl_player');
					if ($em) $em_arr[]=$em;
				}
				$em_str="mailto:".implode(";", $em_arr);
				print "<a href='$em_str'>$em_str</a>";
			}
			if ($addUser) {
				$e = getUserEmail($player_id); 
				addPlayerToUserTbl($uid, array('name'=>$e));
				$msg = "user $e added";
			}
			if ($msg) print "<span id='updateMsg'>$msg</span><br/>";
			if ($deleteUser) {
				//if (dbUpdate('tmsl_user', array('active'=>0), array('uid'=>$uid), true, true))
				if (dbDelete('tmsl_user', array('player_uid'=>$uid), true)) {
					print "<span id='updateMsg'>User deleted.</span><br/>";
					dbDelete ('tmsl_team_manager', array('user_uid'=>$uid), true, true);
				}
				$uid=0;
			}
			if (!$uid) {
				//show filter
				if (!isset($show)) $show=array();
				$whrArr=array();
				$whrLNArr=array();
				print "<form>";
				print "<table align='center'>";
				print "<tr><th>Last name starts with:</th>";
				print "<td colspan='3'>";
				for ($i=65; $i <=90; $i++) { 
					print "<a href='{$_SERVER['PHP_SELF']}?flln=".chr($i)."'>".chr($i)."</a> ";
				}
				print "</td>";
				print "</tr>";
				print "</table>";
				print "</form>";
				if (in_array(1,$show)) $whrArr[] = "u.mask=255";
				if (in_array(2,$show)) $whrArr[] = "p.boardMember=1";
				if (in_array(4,$show)) $whrArr[] = "u.isReferee=1";
				if (in_array(8,$show)) $whrArr[] = "1";
				if ($flln) $whrLNArr[] = "p.lname LIKE '{$flln}%'";
				$whr = implode(' OR ', $whrArr);
				$whrLN = implode(' AND ', $whrLNArr);
				if ($whr || $whrLN) {
					if ($whr) $whr = "AND ($whr)";
					if ($whrLN) $whrLN = "AND $whrLN";
					print "<h3>Users:</h3>";
					print "<div><img src='images/helm.jpg' alt='admin' title='Administrator'> denotes a site admin</div>";
					print "<div><img src='images/tux.png' alt='board' title='Board Member'> denotes a board member</div>";
					print "<div><img src='images/whistle.jpg' alt='board' title='Ref'> denotes a referee</div>";
					print "<div>Click the <img src='images/pencil.png' alt='edit' title='Edit'> to edit a user</div>";

					$sql="SELECT DISTINCT u.name, u.player_uid, boardMember, isReferee, CONCAT(fname,' ', lname) as fullname, mask
						FROM tmsl_user u JOIN tmsl_player p ON u.player_uid=p.uid
						WHERE u.active=1 $whr $whrLN ORDER BY fullname";
					$res=mysql_query($sql) or die("$sql --".mysql_error());
					print "<form method='post' name='frm' id='frm'>";
					print "<table align='center'>";
					while ($rec=mysql_fetch_assoc($res)) {
						if (in_array(8,$show)) {
							$sql="SELECT DISTINCT tname as name FROM tmsl_team t JOIN tmsl_team_manager tm ON t.uid=tm.team_uid JOIN
								tmsl_team_season ts ON t.uid=ts.team_uid AND tm.season_uid=ts.season_uid
								WHERE user_uid={$rec['player_uid']} AND ts.season_uid IN (SELECT uid FROM tmsl_season WHERE stop_date>now())";
							$arr=dbSelectSQL($sql);
							$tms=array();
							if (!empty($arr)) foreach($arr as $rc) $tms[]=$rc['name'];
							if (empty($tms)) continue;
						}
						$ck="";
						if (is_array($player_ids) && in_array($rec['player_uid'], $player_ids)) $ck="checked='true'";
						//if ($sel=='B' && $rec['boardMember']) $ck="checked='true'";
						//if ($sel=='R' && !empty($tms)) $ck="checked='true'";
						print "<td><a href='admin.php?uid=".$rec['player_uid']."'><img src='images/pencil.png' alt='edit' title='Edit' border='0'></a></td>";
						print "<td><input type='checkbox' name='player_ids[]' value='".$rec['player_uid']."' $ck>";
						print $rec['name']." (".$rec['fullname'].")";
						if (!empty($tms)) print " (".implode(',', $tms).") ";
						if ($rec['mask']=='255') print "<img src='images/helm.jpg' alt='admin' title='Administrator'>";
						if ($rec['boardMember']) print "<img src='images/tux.png' alt='BM' title='Board Member'>";
						if ($rec['isReferee']) print "<img src='images/whistle.jpg' alt='BM' title='Referee'>";
						print "</td></tr>";
					}
					print "<tr><td colspan='3'><input type=checkbox onclick='selectAll();'><span id='selText'> Select All</span></td></tr>";
					//print "<tr><td colspan='3'><input type=checkbox onclick='selectBM();'><span id='selBMText'> Select Board Members</span></td></tr>";
					//print "<tr><td colspan='3'><input type=checkbox onclick='selectReps();'><span id='selRepText'> Select Team Reps</span></td></tr>";
					print "</table>";
					print "<input type='image' src='images/email.png' name='mail' title='Mail Selected'>";
					print "</form>";
				}
			}else{
				//one user selected
				$sql="SELECT mask, name, boardMember, isReferee, CONCAT(fname,' ', lname) as fullname FROM tmsl_user u JOIN tmsl_player p ON u.player_uid=p.uid WHERE player_uid=$uid";
				$res=mysql_query($sql) or die("$sql --".mysql_error());
				$rec=mysql_fetch_assoc($res);
				$user=$rec['name']." (".$rec['fullname'].")";
				$bm=$rec['boardMember'];
				$is_ref=$rec['isReferee'];
				print "User $user";
				if ($rec['mask']==255) {
					print " is a system admin & has access to all teams<br/>";
					print "</br/><a href='admin.php?uid=$uid&sysAdmin=1'>Remove System Admin Privilege</a><br/>";
				}else {
					print " is authorized to edit the following teams:<br/>";
					//$sql="SELECT t.uid, t.name FROM tmsl_team_manager tm INNER JOIN tmsl_team t ON tm.team_uid=t.uid WHERE tm.user_uid=$uid AND tm.active=1 ORDER BY t.name";
					$sql="SELECT DISTINCT t.season_uid, t.team_uid as uid, t.tname as name FROM tmsl_team_manager tm INNER JOIN tmsl_team_season t ON tm.team_uid=t.team_uid INNER JOIN tmsl_season s ON t.season_uid=s.uid AND tm.season_uid=s.uid WHERE tm.user_uid=$uid AND tm.active=1 ORDER BY s.start_date DESC, t.tname";
					$res=mysql_query($sql) or die("$sql --".mysql_error());
					while ($rec=mysql_fetch_assoc($res)) {
						$hasTeams=true;
						print "<a href='admin.php?uid=$uid&team_id=".$rec['uid']."&season_id={$rec['season_uid']}&deleteTeam=1'><img src='images/delete.png' alt='delete' title='Remove as Team Rep' border='0'></a>";
						print "<a href='roster.php?team_id=".$rec['uid']."&season_id={$rec['season_uid']}' title='View Roster'>".$rec['name']." -- " .getSeasonName($rec['season_uid'])."</a><br/>";
					}
					if (!$hasTeams) print "--none--<br/>";
					print "<br/><a href='admin.php?uid=$uid&addTeam=1'>Add a team for $user to manage</a><br/>";
					if($addTeam){
						print "<form method='get'>";
						print "<input type='hidden' name='uid' value='$uid'>";
						print "<input type='hidden' name='addTeam' value='1'>";
						$sql="SELECT s.uid, CONCAT(s.name, ' -- ', d.name) as name FROM tmsl_season s INNER JOIN tmsl_division d ON s.division_uid=d.uid WHERE stop_date > now() ORDER BY start_date DESC, name";
						$arrLeagues=buildSimpleSQLArr("uid", "name", $sql);
						print "Season: ";
						print getSelect("season_id", $arrLeagues, array(0=>"--Select--"), $season_id, "onchange=submit()");
						if ($season_id) {
							$sql="SELECT t.uid as uid, tname as name FROM tmsl_team t INNER JOIN tmsl_team_season tl ON t.uid=tl.team_uid
								WHERE tl.season_uid = $season_id";
							$arrTeams=buildSimpleSQLArr("uid", "name", $sql);
							print "<br/>Team: ";
							print getSelect("team_id", $arrTeams, array(0=>"--Select--"), "", "onchange=submit()");
						}
						print "</form>";
					}
					print "<br/><a href='admin.php?uid=$uid&sysAdmin=255'>Promote to System Admin (Full Access)</a><br/>";
				}
				if ($is_ref)
					print "<br/><a href='admin.php?uid=$uid&is_ref=0'>Mark as NOT a Referee</a><br/>";
				else
					print "<br/><a href='admin.php?uid=$uid&is_ref=1'>Mark as Referee</a><br/>";
				if ($bm)
					print "<br/><a href='admin.php?uid=$uid&bm=0'>Mark as NOT a Board Member</a><br/>";
				else
					print "<br/><a href='admin.php?uid=$uid&bm=1'>Mark as Board Member</a><br/>";
				print "</br/><a href='chgPwd.php?uid=$uid'>Change Password</a><br/>";
				print "</br/><a href='editPlayer.php?edit=1&uid=$uid'>Edit Contact Info</a><br/>";
				print "</br/><a href='admin.php?deleteUser=1&uid=$uid'>Delete</a><br/>";
				print "</br/><a href='admin.php'>Back to Full List of Users</a><br/>";
			}
			print "<br/><a href='player.php?addUser=1'>Add User</a><br/>";
			print "</div>";
			print "</body>";
			print "</html>";
		}else header("Location:index.php");
	}else include("login.php");
?>
<script>
checked=false;
txt='Select All';

function selectAll() {
	var aa = document.getElementById('frm');
	 if (checked == false)
          {
           checked = true;
           txt='Select None';
          }
        else
          {
          checked = false;
          txt='Select All';
          }
	for (var i =0; i < aa.elements.length; i++)
	{
	 var nm = aa.elements[i].name;
	 if (nm != 'actn') {
	 	aa.elements[i].checked = checked;
	 }
	}
	var bb = document.getElementById('selText');
	bb.innerHTML = txt;
}
function selectBM() {
	window.location='admin.php?sel=B';
}
function selectReps() {
	window.location='admin.php?sel=R';
}
</script>
