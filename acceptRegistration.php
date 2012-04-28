<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (hasPermission($_SESSION['mask'], $team_id, $season_id)) {
			if ($commit) {
				//print_r($_POST);exit;
				$notes=mysql_escape_string($notes);
				$ret=dbUpdate('tmsl_team_season', array('registered'=>$reg_status, 'notes'=>$notes, 'registeredBy'=>$_SESSION['logon_name']),
					array('team_uid'=>$team_id, 'season_uid'=>$season_id), true, true);
				if(!$ret) {
					$division_id = getScalar('uid', $season_id, 'division_uid', 'tmsl_season');
					$arr=array('team_uid'=>$team_id, 'division_uid'=>$division_id, 'season_uid'=>$season_id, 'start_date'=>'now()',
						'registered'=>$reg_status, 'notes'=>$notes, 'registeredBy'=>$_SESSION['logon_name']);
					dbInsert('tmsl_team_season', $arr, 1);
				}
				foreach ($player_id as $pid) {
					dbUpdate('tmsl_player_team', array('registered'=>2, 'balance'=>0), array('team_uid'=>$team_id, 'season_uid'=>$season_id, 'player_uid'=>$pid));
				}
				dbUpdate('tmsl_team', array('bond_owed'=>0), array('uid'=>$team_id));
				header("Location:reportRegistered.php");
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
			$team_nm=getTeamName($team_id, $season_id);
			//$season_nm=getScalar("", "", "", "", $sql);
			$season_nm=getSeasonName($season_id);
			$arr=dbSelect('tmsl_team_season', array('registered','notes'), array('team_uid'=>$team_id, 'season_uid'=>$season_id));
			if (empty($arr)) {$status=0;$notes='';}
			else {$status = $arr[0]['registered'];$notes = $arr[0]['notes'];}

			print "<h4>Registration: $team_nm for $season_nm</h4>";

			$bond_owed=getScalar('uid', $team_id, 'bond_owed', 'tmsl_team');
			if ($bond_owed) print "<h5>$team_nm owes a bond of $".$bond_owed.".  Accepting registration will remove any amount owed.</h5>";
			print "<a href='invoice.php?team_id=$team_id&season_id=$season_id' target='_blank'>View Invoice</a><br/>";
			print "<br/>Clear the checkbox for any player who should not be registered.  Checked players will have a balance of zero after this form is submitted.<br/>";
			print "<form name='frm' id='frm' method='post'>";

			//list players
			$sql="SELECT CONCAT(lname, ', ', fname) as nm, registered,
			case registered when 2 then 'registered' when 1 then 'registration submitted' when 3 then 'registration cancelled' else 'not registered' end as reg_status,
			balance as bal,
			DATE_FORMAT(start_date, '%m/%d/%y') as sd, uid FROM tmsl_player p JOIN tmsl_player_team pt ON p.uid=pt.player_uid
				WHERE team_uid=$team_id AND season_uid=$season_id ORDER BY start_date, lname, fname";
			$playerArr=dbSelectSQL($sql);
			print "<table align='center'>";
			print "<tr><th>Accept</th><th>Name</th><th>Date Added</th><th>Reg. Status</th><th>Owes</th></tr>";
			foreach($playerArr as $p) {
				//if ($p['registered']<2) $ck="checked='true'"; else $ck="";
				print "<tr><td><input type='checkbox' name='player_id[]' value='".$p['uid']."' $ck></td>";
				print "<td>".$p['nm']."</td>";
				print "<td>".$p['sd']."</td>";
				print "<td>".$p['reg_status']."</td>";
				print "<td align='right'>".$p['bal']."</td>";
				print "</tr>";
			}
			print "<tr><td colspan='5'><a href='#' onclick='selectAll()'><span id='selText'>Select All</span></a></td></tr>";
			print "</table><br/>";

			print "You may enter notes:<br/>";
			print "<textarea name='notes' rows='4' cols='60'>$notes</textarea><br/>";
			print "<br/>Select the desired registration status for the team:<br/>";
			print "<select name='reg_status'>";
			print "  <option value=0>Not Registered</option>";
			print "  <option value=1>Registration Submitted</option>";
			print "  <option value=2 selected='true'>Registered</option>";
			print "  <option value=3>Registration Cancelled</option>";
			print "</select><br/><br/>";
			print "<input type='submit' value='ok' name='commit'>";
			print "</form>";
			print "</div>";
			print "</body>";
			print "</html>";
		}
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
</script>