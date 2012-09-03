<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($_SESSION['mask'] & 4) $adm=true;else $adm=false;
		if ($adm && isset($bond)) dbUpdate('tmsl_team', array('bond_owed'=>$bond), array('uid'=>$team_id),1,1);
		if (isset($owes)) {
			dbUpdate('tmsl_player_team', array('balance'=>$owes, 'pay_pending'=>0), array('player_uid'=>$player_id,'team_uid'=>$team_id, season_uid=>$season_id),1,1);
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Invoice</div>";
		print "<div id='mainPar'>";
		if (hasPermission($_SESSION['mask'], $team_id, $season_id)) {
			$team_nm=getTeamName($team_id, $season_id);
			$bond_owed=getScalar('uid', $team_id, 'bond_owed', 'tmsl_team');
			print "<h4>Registration for $team_nm</h4>";
			//$arr=dbSelect('tmsl_season', array('case when now() > halfway_date then (cost_per_player/2+10) else cost_per_player end as cpp'), array('uid'=>$season_id));
			$arr=dbSelect('tmsl_season', array('case when now() > halfway_date then (cost_per_player-10) else cost_per_player end as cpp'), array('uid'=>$season_id));
			$costPerPlayer=$arr[0]['cpp'];
			$sql="SELECT CONCAT(lname,', ', fname) as nm, registered, player_uid,
				case registered when 2 then 'registered' when 1 then 'registration submitted' when 3 then 'registration cancelled' else 'not registered' end as reg_status,
				balance as owed,
				case boardMember when 1 then 'Board Member' else '&nbsp;' end as bm,
				case boardMember when 1 then 0 else (case when registered<2 then $costPerPlayer else 0 end) end as owes
				FROM `tmsl_player_team` pt join tmsl_player p on pt.player_uid=p.uid
			  WHERE team_uid=$team_id and season_uid=$season_id
			  ORDER BY lname, fname";
			 $res=mysql_query($sql);
			print "<table align='center' border='1'>";
			if (!$bond_owed) $bond_owed=0;
			if($adm) print "<tr><td colspan='2'>BOND</td><td>
			  <form>$<input type='text' name='bond' value='$bond_owed' onchange='submit();' onfocus='select();'>
			  <input type='hidden' name='team_id' value='$team_id'>
			  <input type='hidden' name='season_id' value='$season_id'>
			  </form></td></tr>";
			else print "<tr><td colspan='2'>BOND</td><td>$".$bond_owed."</td></tr>";
				$owed=$bond_owed;
			while ($rec=mysql_fetch_assoc($res)) {
				$owes=$rec['owed'];
				print "<tr><td>{$rec['nm']}</td>";
				print "<td>{$rec['reg_status']}</td>";
				if ($adm)
				  print "<td><form>$<input type='text' name='owes' value='$owes' onchange='submit();' onfocus='select();'>
				  <input type='hidden' name='team_id' value='$team_id'>
				  <input type='hidden' name='season_id' value='$season_id'>
				  <input type='hidden' name='player_id' value='{$rec['player_uid']}'>
				  </form></td>";
				else
					print "<td>$".$owes."</td>";
				print "</tr>";
				$owed += (float)$owes;
			}
			print "</table>";
		}
		print "<h4>Amount owed: $".$owed.".</h4>";
		print "<h5>Your team will be able to play when the amount owed is submitted to TMSL.<br/>Unless otherwise specified, payment must be received as ONE check or money order.<br/>Checks may be brought in or mailed to:<br/>TMSL OFFICE<br/>4651 N. First Ave., Suite 204<br/>Tucson, AZ 85718<br/>A bounced check fee of $25 will be assessed for checks that don't clear.  </h5>";
		print "<input type='button' value='Back to Roster' onClick='window.location=\"roster.php?team_id=$team_id&season_id=$season_id\"' class='pointer'>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
