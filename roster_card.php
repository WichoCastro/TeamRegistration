<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<style>td {font-size:0.7em;}th {font-size:0.7em;} table {border-collapse:collapse;} body {margin-top:0;}</style>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "</head>";
		print "<body>";
		print "<div ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></div>";
		print "<div id='mainPar'>";
		print "<div style='font-size:14pt'>TUCSON METRO SOCCER LEAGUE GAME REPORT ROSTER</div>";

		if ($game_uid) {
			$arr = dbSelect('tmsl_game', array('*'), array('uid'=>$game_uid));
			foreach($arr[0] as $f=>$v) $$f = $v;
			//print_r($arr);
			$game_date = $game_dt;
			$home_team = getTeamName($team_h, $season_uid);
			$visiting_team = getTeamName($team_v, $season_uid);
			$season_id=$season_uid;
		}

		if (isset($game_date)) {
			if (strtotime($game_date) < strtotime(date('m/d/Y'))) {print "<span class='error'>Please select a date that is not before today.</span>";$game_date='';}
				if (isset($game_date)) {
				$sql="SELECT datediff(str_to_date('$game_date', '%m/%d/%Y'), now()) as dtdif";
				$arr=dbSelectSQL($sql);
				$d=$arr[0]['dtdif'];
				if ($d > 3)  {print "<span class='error'>The game date must be no more than 3 days away. Please print it later.</span>";$game_date='';}
				else $game_date = (strtotime($game_date)) ? date('m/d/Y', strtotime($game_date)) : '';
			}
		}
		if ($sbm && !$game_date) print "<br/><span class='error'>The game date you have selected is not valid.</span>";
		print "<form id='frm'>";
		print "<input type='hidden' name='team_id' value='$team_id'>";
		print "<input type='hidden' name='season_id' value='$season_id'>";
		print "<table align='center'>";
		if (!$game_date) {
//			print "<tr><td colspan='2'>Select the date of the game to generate the roster:</td></tr>";
//			print "<tr><td>Game Date:</td><td><input type='text' value='$game_date' name='game_date' id='game_date'>";
//			print "<a href='#' onClick='cal.select(document.forms[\"frm\"].game_date,\"anchor1\",\"MM/dd/yyyy\");return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Game Date'></a></td>";
//			print "<tr><td colspan=2 align='center'><input type='submit' name='sbm' value='create game card'></td></tr>";
			print "<tr><td colspan='2'>Select the game:<table class='rtbl'>";
			$sql="SELECT g.uid, team_h, team_v, game_loc, DATE_FORMAT(game_dt, '%Y-%m-%d') as game_dt,
					DATE_FORMAT(game_tm, '%H:%i') as game_tm, 0 as edit_right,
					DATEDIFF(game_dt, now()) as dif, g.season_uid
					FROM tmsl_team_manager mgr, tmsl_game g
					WHERE user_uid = {$_SESSION['logon_uid']} AND game_dt >= current_date() AND (team_h=mgr.team_uid OR team_v=mgr.team_uid)
					AND mgr.season_uid=g.season_uid
					ORDER BY game_dt, game_tm";
			$arr=dbSelectSQL($sql);
			print "<tr><th>Field</th><th>Date</th><th>Time</th><th>Home</th><th>Away</th><th>&nbsp</th></tr>";
			foreach ($arr as $rec) {
				print "<tr>";
				print "<td>{$rec['game_loc']}</td>";
				print "<td>{$rec['game_dt']}</td>";
				print "<td>{$rec['game_tm']}</td>";
				print "<td>".getTeamName($rec['team_h'], $rec['season_uid'])."</td>";
				print "<td>".getTeamName($rec['team_v'], $rec['season_uid'])."</td>";
				if ($rec['dif'] >= 0 && $rec['dif'] < 3) {
					if (hasPermission(1, $rec['team_h'], $rec['season_uid']))
						print "<td><img src='images/page_white_edit.png' style='cursor:pointer'
											onclick='window.open(\"roster_card.php?team_id={$rec['team_h']}&game_uid={$rec['uid']}\",
											 \"roster_win\",
											 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'
											title='print game card' alt='print game card' border='0'></td>";
					if (hasPermission(1, $rec['team_v'], $rec['season_uid']))
						print "<td><img src='images/page_white_edit.png' style='cursor:pointer'
											onclick='window.open(\"roster_card.php?team_id={$rec['team_v']}&game_uid={$rec['uid']}\",
											 \"roster_win\",
											 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'
											title='print game card' alt='print game card' border='0'></td>";
				}
				print "</tr>";
			}
			print "</table></td></tr>";
		} else {
			print "<tr><td>Game Date:</td><td><span class='fake_field'>$game_date</span></td>";
			print "<td>Time:</td><td><span class='fake_field'>$game_tm</span></td>";
			print "<td>Field:</td><td><span class='fake_field'>$game_loc</span></td></tr>";
			print "<tr><td>Home Team:</td><td><span class='fake_field'>$home_team</span></td>";
			print "<td>Score:</td><td><input type='text' name='home_team_score' value='$home_team_score' size='6'></td></tr>";
			print "<tr><td>Visiting Team:</td><td><span class='fake_field'>$visiting_team</span></td>";
			print "<td>Score:</td><td><input type='text' name='visiting_team_score' value='$visiting_team_score' size='6'></td></tr>";
			print "<tr><td colspan='4' align='center'>printed on ".date('m/d/Y')."</td></tr>";
		}
		print "</table>";

		if ($game_date) {
			$tm_nm=getTeamName($team_id, $season_id);
			$pflds=array("jersey_no"=>"num", "'&nbsp;'"=>"ckd_in", "CONCAT(p.lname, ', ', p.fname)"=>"name", " '&nbsp;'"=>"num_Y", "'&nbsp;' "=>"num_R");
			$pflds["CONCAT(p.lname, ', ', p.fname)"]="'$tm_nm'";
			foreach ($pflds as $colName=>$display) {
				$flds[]="$colName AS $display";
				if (!$hideField[$display]) $tblHdr.="<th>$display</th>";
			}
			$fldStr=implode(", ",$flds);
			$sql="SELECT $fldStr
				FROM tmsl_player p INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
				INNER JOIN tmsl_season s ON s.uid=pt.season_uid
				WHERE s.uid=$season_id AND pt.team_uid=$team_id
				AND pt.registered=2
				AND p.uid NOT IN
				  (SELECT player_uid FROM tmsl_suspended WHERE ".
				  //team_uid=$team_id AND (this is not getting populated...)
				  " str_to_date('$game_date', '%m/%d/%Y') >= start_date AND
				    str_to_date('$game_date', '%m/%d/%Y') <= stop_date
				  )
				ORDER BY lname, fname";

				if ($debug) print $sql;

			$hdrStyl=array("", "", "width=400","","");
			$rowStyl=array("align='right'", "", "");

			print printTable($sql, $hdrStyl, $rowStyl, 0, 0);

			print"<div class='refInstr'><br><b>REMARKS:</b>___________________________________________________________________________________________________<br>
				Please fill out report <b>COMPLETELY</b>, marking both yellow and red cards next to the offending player's name.  Include an explanation of
events resulting in a player’s ejection (red card) from play using the misconduct report that will open in a separate window when you click
the red card box next to the name of the player who was ejected.  All injuries (or any other unusual event) must be noted in the comment box.<br/>

				To receive payment, game report and any player passes must be received within three (3) days of the match.<br></div>
				<table align='center'><tr><td>Mail forms to:</td><td>&nbsp;</td></tr>
				<tr><td>Bob & Maggie Barton</td><td>					Referee:_________________________</td></tr>
				<tr><td>PMB 313</td><td></td></tr>
				<tr><td>7320 North La Cholla #154	</td><td>				AR1:___________________________</td></tr>
				<tr><td>Tucson, Arizona 85741-2305</td><td></td></tr>
				<tr><td>Fax:797-1901<br/>Email:cactusmouse@comcast.net	</td><td>		AR2:___________________________</td></tr>
				</table>
				";
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
