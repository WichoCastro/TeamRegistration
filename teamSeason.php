<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!hasRegistrationAuthority($_SESSION['mask'])) header("Location:roster.php");
		if ($team_id_arr) {
			if ($actn == 'del') {
				//shouldn't happen for now, till we iron out the details
			}
			if ($actn == 'rollover') {
				$url="Location: rollover.php?season_id=$season_id&team_id[]=".implode('&team_id[]=',$team_id_arr);
				header($url);
			}
			if ($actn == 'drop') {
				$url="Location: dropTeamFromSeason.php?season_id=$season_id&team_id[]=".implode('&team_id[]=',$team_id_arr);
				header($url);
			}
		}
		//if ($season_id) {$_SESSION['season_uid']=$season_id;dbUpdate('tmsl_user', array('last_season_uid'=>$season_id), array(player_uid=>$_SESSION['logon_uid']));}
		$_SESSION['team_uid']=0;
		
		$season = new Season($season_id);
		
		print $beginning;
		
		print "<div id='ttlBar'>Season Details; Teams by Season</div>";
		print "<div id='mainPar'>";

		if ($actn == 'change_division') {
			$tmz = implode(',', $_POST['team_id_arr']);
			print "Select the season to move the team to:<br/>";
			//print names of active seasons
			print "<form method='get' name='frmSzn' id='frmSznTm'>";
    		print "Change to: ";
    		print getSeasonDropdown (date('Y-m-d'), $season_id, "mvTm()");
    		print "</form>";
		} else {
		
			$sql="SELECT * FROM tmsl_season WHERE uid=$season_id";
			$res=mysql_query($sql);
			$rec=mysql_fetch_assoc($res);
			foreach($rec as $key=>$val)
				$$key=$val;
	
			$sql="SELECT a.uid FROM tmsl_season a, tmsl_season b WHERE a.division_uid = b.division_uid AND b.uid=$season_id AND a.start_date < b.start_date ORDER BY a.start_date DESC LIMIT 1";
			$prev_id=getScalar('','','','', $sql);
			if ($prev_id) print "<a href='teamSeason.php?season_id=$prev_id'><img alt='Edit' src='images/arrow_left.png' title='Previous' border='0'></a>";
			print "Season: $name";
			$sql="SELECT a.uid FROM tmsl_season a, tmsl_season b WHERE a.division_uid = b.division_uid AND b.uid=$season_id AND a.start_date > b.start_date ORDER BY a.start_date LIMIT 1";
			$next_id=getScalar('','','','', $sql);
			if ($next_id) print "<a href='teamSeason.php?season_id=$next_id'><img alt='Edit' src='images/arrow_right.png' title='Previous' border='0'></a>";
			print "<br/>";
			$div_nm=getScalar('uid', $division_uid, 'name', 'tmsl_division');
			print "Division: $div_nm<br/>";
			print "Min players to Register: $min_players<br/>";
			print "Max players to Register: $max_players<br/>";
			print "Cost Per Player: $cost_per_player<br/>";
			print "Last Date to Register a Team: $last_day_team<br/>";
			print "Last Date to Register a Player: $last_day_player<br/>";
			print "<a href='manageSeasons.php?editSeason=1&season_id=$season_id'><img alt='Edit' src='images/pencil.png' title='Edit Details' border='0'>Edit Season Details</a><br/><br/>";
			$sql="SELECT uid, tname, notes, case registered when 2 then 'registered' when 1 then 'registration submitted' when 3 then 'registration cancelled' else 'not registered' end as reg_status,
				(select count(*) FROM tmsl_player_team inner_t WHERE team_uid=t.uid AND season_uid=tl.season_uid) as ct
				FROM tmsl_team_season tl INNER JOIN tmsl_team t ON tl.team_uid=t.uid WHERE season_uid=".$_GET['season_id']." ORDER BY name";
			$res=mysql_query($sql);
	
	
			if (mysql_num_rows($res)) {
				print "<h4>Teams:</h4>";
				print "<form method='post' name='frm' id='frm'>";
				print "<input type='hidden' name='season_id' value='$season_id'>";
				print "<table align='center'>";
				while ($rec=mysql_fetch_array($res)) {
					print "<tr>";
					print "<td>";
					print "<a href='roster.php?team_id=".$rec['uid']."&season_id=".$_GET['season_id']."'>
									<img alt='Edit' src='images/group.png' title='Edit Roster' border='0'>
									</a>";
					print "</td>";
					print "<td valign='top'>";
					print "<input type='checkbox' name='team_id_arr[]' value='".$rec['uid']."'>";
					print " ".$rec['tname']."  (".$rec['ct'].") <span class='status'> ".$rec['reg_status']."</span>";
					if ($rec['notes']) print "<img src='images/comment.png' alt='".$rec['notes']."' title='".$rec['notes']."' border ='0'>";
					print "</td>";
					print "</tr>";
				}
				print "<tr><td>&nbsp;</td><td><input type=checkbox onclick='selectAll();'><span id='selText'> Select All</span></td></tr>";
				print "<tr><td colspan='2' align='center'><em>With selected:</em>
						<table>";
				if (strtotime($season->stopDate) >= time()) 
					print	"<tr><td><input type='radio' name='actn' value='del' disabled='true'>Delete</td></tr>
						<tr><td><input type='radio' name='actn' value='drop'>Drop from this season</td></tr>
						<tr><td><input type='radio' name='actn' value='change_division'>Change Division</td></tr>";
				print "<tr><td><input type='radio' name='actn' value='rollover' checked='true'>Rollover to next season</td></tr>
						<tr><td><input type='submit' value='ok'></td></tr>
						</table>
					</td></tr>";
				print "</table>";
			}else{
				print "There are no teams for this season.  Click the button below, or rollover teams from a previous season.<br/>";
			}
			print "</form>";
			print "<br/><input type='button' value='Add a New Team' onclick='window.location=\"addTeam.php\"'></div>";
		}
		print "</div>";
		print "</body>";
		print "</html>";
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
function mvTm() {
	var s = <?=$season_id?>;
	var t = '<?=$tmz?>';
	moveTeam(s,t);
}
</script>
