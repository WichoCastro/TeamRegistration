<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($sbm) {
			if (!$ref_id) $ref_uid=0;
			$uid=getScalar('','','','',"SELECT uid FROM tmsl_misconduct WHERE player_uid=$player_uid and game_uid=$game_uid");
			$fldArr=array('ref_uid', 'game_uid', 'player_uid', 'team_name', 'season_uid', 'jersey_no', 'offense', 'explanation', 'ref_phone');
			$offense=implode(',', $offense);
			foreach ($fldArr as $fld) if (isset($$fld)) $arr[$fld]=$$fld;
			$arr['explanation']=mysql_real_escape_string($arr['explanation']);
			if ($uid)
				dbUpdate('tmsl_misconduct', $arr, array('uid'=>$uid));
			else
				dbInsert('tmsl_misconduct', $arr);
			print "<script>window.opener.location.href=window.opener.location;window.close();</script>";
		}
		if ($uid) {
			$arr = dbSelect('tmsl_card', array('player_uid', 'game_uid'), array('uid'=>$uid));
			$player_uid=$arr[0]['player_uid'];
			$game_uid=$arr[0]['game_uid'];
		}
		if (!$adm && !permissionGameReport($game_uid, $_SESSION['logon_uid'])) $dis="disabled='disabled'";
		$offenses = array('Serious Foul Play', 'Foul Language (profanity)', 'Acts Detrimental to Soccer', 'Referee Assault', 'Violent Conduct', 'Abusive Language/Gesture', 'Second Yellow Card');
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<style>td {font-size:0.7em;}th {font-size:0.7em;} table {border-collapse:collapse;} body {margin-top:0;}</style>";
		print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
		print "</head>";
		print "<body>";
		print "<div id='ttlBar'>TUCSON METRO SOCCER LEAGUE MISCONDUCT REPORT</div>";
		print "<div ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></div>";
		print "<div id='mainPar'>";
		print "<div style='font-size:14pt'>MISCONDUCT REPORT FOR GAME #$game_uid</div><br/>";
		$arr = dbSelectSQL("SELECT * FROM tmsl_misconduct WHERE player_uid=$player_uid and game_uid=$game_uid");
		$details=$arr[0];
		$details['offense']=explode(',', $details['offense']);

		print "<form method='POST' name='frm'>";
		print "<input type='hidden' name='game_uid' value='$game_uid'>";
		print "<input type='hidden' name='player_uid' value='$player_uid'>";

		$sql="SELECT CONCAT(fname,' ',lname) as nm, phone, user_uid
						FROM tmsl_game g JOIN tmsl_game_assign ga ON g.uid=ga.game_uid
						JOIN tmsl_player p ON ga.user_uid = p.uid
						WHERE ga.edit_right=2 AND g.uid=$game_uid";
		$arr=dbSelectSQL($sql);
		$ref=$arr[0];
		$ref_id = $ref['user_uid'];
		print "<input type='hidden' name='ref_uid' value='$ref_id'>";

		print "<table align='center' border='1'>";
		$ref_nm = ($details['ref_uid']) ? getUserName($details['ref_uid']) : $ref['nm'];
		print "<tr><th>Referee Name: </td><td>$ref_nm</td></tr>";

		$ref_phone = ($details['ref_phone']) ? $details['ref_phone'] : $ref['phone'];
		print "<tr><th>Referee Phone: </td><td><input type='text' name='ref_phone' value='$ref_phone' size='50'></td></tr>";

		$sql="SELECT DATE_FORMAT(game_dt, '%M %e, %Y') as game_date,
						DATE_FORMAT(game_tm, '%h:%i') as game_time, game_loc as field,
						th.tname as home_team,
						tv.tname as visitor,
						s.name as season, d.name as division
						FROM tmsl_game g JOIN tmsl_team_season th ON g.season_uid=th.season_uid AND g.team_h=th.team_uid
						JOIN tmsl_team_season tv ON g.season_uid=tv.season_uid AND g.team_v=tv.team_uid
						JOIN tmsl_season s ON g.season_uid=s.uid
						JOIN tmsl_division d ON d.uid=s.division_uid
						WHERE g.uid=$game_uid";
		print "<tr><th>Game Info:</th><td>".printTable($sql)."</td></tr>";

		$player_nm = getUserName($player_uid);
		print "<tr><th>Player Name: </td><td>$player_nm</td></tr>";
		$sql="SELECT jersey_no FROM tmsl_player_team pt WHERE player_uid=$player_uid
			AND (team_uid=(SELECT team_h FROM tmsl_game WHERE uid=$game_uid) OR team_uid=(SELECT team_v FROM tmsl_game WHERE uid=$game_uid))
			AND season_uid=(SELECT season_uid FROM tmsl_game WHERE uid=$game_uid)";
		$jersey = $details['jersey_no'];
		if (!$jersey) $jersey = getScalar('','','','',$sql);
		print "<tr><th>Jersey No.: </td><td><input type='text' name='jersey_no' value='$jersey' size='50'></td></tr>";

		$sql="SELECT tname FROM tmsl_team_season ts
			WHERE team_uid =
				(SELECT team_uid FROM tmsl_player_team WHERE player_uid=$player_uid
				AND (team_uid=(SELECT team_h FROM tmsl_game WHERE uid=$game_uid) OR team_uid=(SELECT team_v FROM tmsl_game WHERE uid=$game_uid))
				AND season_uid=(SELECT season_uid FROM tmsl_game WHERE uid=$game_uid))";
		$team_nm = $details['team_name'];
		if (!$team_nm) $team_nm = getScalar('','','','',$sql);
		print "<tr><th>Team.: </td><td><input type='text' name='team_name' value='$team_nm' size='50'></td></tr>";
		print "<tr><th>Send Off Offense:</th><td>";
		print "<table><tr><td>";
		foreach($offenses as $offense) {
			//if ($details['offense'] == $offense) $chk="checked='checked'"; else $chk="";
			if (in_array($offense, $details['offense'])) $chkd='checked';else $chkd='';
			print "<input type='checkbox' name='offense[]' value='$offense' $chkd> $offense<br/>";
			if (!(++$j % 4)) print "</td><td>";
		}
		print "</table>";
		print "</tr>";
		print "<tr><th colspan='2'>Explanation of Offense:</th></tr>";
		print "<tr><td colspan='2'><textarea style='width:100%' name='explanation' rows='4' cols='80'>{$details['explanation']}</textarea></td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' value='ok' name='sbm' $dis></td></tr>";
		print "</table>";
		print "</form>";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
