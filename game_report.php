<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($sv || $sbm) {
			$cm=mysql_real_escape_string($comments);
			if (!team_h_score) $team_h_score=0;
			if (!team_v_score) $team_v_score=0;
			if ($team_h_score > $team_v_score) {$team_h_pts=3;$team_v_pts=0;}
			elseif ($team_h_score < $team_v_score) {$team_h_pts=0;$team_v_pts=3;}
			else {$team_h_pts=1;$team_v_pts=1;}
			$sql="UPDATE tmsl_game SET team_h_score=$team_h_score, team_v_score=$team_v_score,
				team_h_pts=$team_h_pts, team_v_pts=$team_v_pts,comments='$cm' WHERE uid=$uid";
			mysql_query($sql);
		}
		if ($clr) {
			$sql="UPDATE tmsl_game SET team_h_score=0, team_v_score=0,
							team_h_pts=-1, team_v_pts=-1, comments='' WHERE uid=$uid";
			mysql_query($sql);
		}
		if ($sbm) {
			$sql="UPDATE tmsl_game SET game_report_submitted=1 WHERE uid=$uid";
			mysql_query($sql);
			print "<script>window.opener.location.href=window.opener.location;window.close();</script>";
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<style>td {font-size:0.7em;}th {font-size:0.7em;} table {border-collapse:collapse;} body {margin-top:0;}</style>";
		print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
		?>			<script>
							function add_card(plyr, HorV, tm, season_id, crd) {
								if (crd == 'R') window.open('misconductReport.php?player_uid='+plyr+'&game_uid=<?=$uid?>',
											'misconductWin', 'width=800, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes');
								var url='ajax_insrt.php';
								var params='tbl=tmsl_card&player_id='+plyr+'&card_type='+crd+'&team_id='+tm+'&season_id='+season_id+'&game_id='+<?=$uid?>;
								var myAjax=new Ajax.Request(url, {method: 'post', parameters: params,
									onComplete:function() {
										document.location.href=document.location;
									}
								});
							}
							function remove_card(card_id) {
								if (confirm("Click ok to remove the card; cancel if you don't want to remove it")) {
									var url='ajax_del.php';
									var params='tbl=tmsl_card&&uid='+card_id;
									var myAjax=new Ajax.Request(url, {method: 'post', parameters: params, onComplete:function() {document.location.href=document.location;} });
								}
							}
							function upd_fld(fld) {
								var url='ajax_updt.php';
								var val=$F(fld);
								var params='tbl=tmsl_game&fld='+fld+'&val='+val+'&uid=<?=$uid?>';
								var myAjax=new Ajax.Request(url, {method: 'post', parameters: params });
							}
							function calc_pts() {
								var score_h = $F('team_h_score');
								var score_v = $F('team_v_score');
								if (score_h > score_v) {team_h_pts=3;team_v_pts=0;}
								else if (score_v > score_h) {team_v_pts=3;team_h_pts=0;}
								else {team_v_pts=1;team_h_pts=1;}
								var url='ajax_updt.php';
								var params='tbl=tmsl_game&fld=team_h_pts&val='+team_h_pts+'&uid=<?=$uid?>';
								var myAjax=new Ajax.Request(url, {method: 'post', parameters: params });
								var params='tbl=tmsl_game&fld=team_v_pts&val='+team_v_pts+'&uid=<?=$uid?>';
								var myAjax=new Ajax.Request(url, {method: 'post', parameters: params });
							}
						</script>
		<?
		print "</head>";
		print "<body>";
    //print $banner;
    //print $navBar;
    print "<div id='ttlBar'>TUCSON METRO SOCCER LEAGUE GAME REPORT</div>";
		print "<div ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></div>";
		print "<div id='mainPar'>";
		print "<div style='font-size:14pt'>GAME REPORT FOR GAME #$uid</div>";
		$sql="SELECT DATE_FORMAT(game_dt, '%m/%d/%Y') as game_date,
						DATE_FORMAT(game_tm, '%h:%i') as game_time, game_loc as field,
						g.team_h_score as home_goals,
						g.team_v_score as visitor_goals,
						game_report_submitted,
						s.name as season, d.name as division, g.comments,
						s.uid as season_id, g.team_h as tm_h, g.team_v as tm_v
						FROM tmsl_game g
						JOIN tmsl_season s ON g.season_uid=s.uid
						JOIN tmsl_division d ON d.uid=s.division_uid
						WHERE g.uid=$uid";
		$arr=dbSelectSQL($sql);
		$rec=$arr[0];
		foreach($rec as $key=>$val) $$key=$val;
		//just in case a team has switched divisions:
		$gm_dt = date('Y-m-d', strtotime($rec['game_date']));
		$h_season_id=getSeason($tm_h, $gm_dt);
		$v_season_id=getSeason($tm_v, $gm_dt);
		$home_team=getTeamName($tm_h, $h_season_id);
		$visitor=getTeamName($tm_v, $v_season_id);
		if ($game_report_submitted && !$adm) $edit=false; else $edit=true;
		if (!$edit) $dis="disabled='disabled'";
		else $crs="cursor:pointer";

		print "<form id='frm'>";
		print "<input type='hidden' name='uid' value='$uid'>";
		print "<input type='hidden' name='team_h' value='$tm_h'>";
		print "<input type='hidden' name='team_v' value='$tm_v'>";
		print "<input type='hidden' name='season_id' value='$season_id'>";
		print "<table border='1' align='center'>";
		print "<tr>";
		print "<td colspan='2' align='center'><b>Date:</b> $game_date</td>";
		print "<td colspan='2' align='center'><b>Time:</b> $game_time</td>";
		print "<td colspan='2' align='center'><b>Field:</b> $field</td>";
		print "</tr>";
		print "<tr>";
		print "<td colspan='3' width='50%' align='center'><b>Home Team:</b> $home_team</td>";
		print "<td colspan='3' align='center'><b>Score:</b> <input $dis name='team_h_score' id='team_h_score' size='5' value='$home_goals' onclick='select();' onchange='upd_fld(\"team_h_score\");calc_pts();'></td>";
		print "</tr>";
		print "<tr>";
		print "<td colspan='3' align='center'><b>Visiting Team:</b> $visitor</td>";
		print "<td colspan='3' align='center'><b>Score:</b> <input $dis name='team_v_score' id='team_v_score' size='5' value='$visitor_goals' onclick='select();' onchange='upd_fld(\"team_v_score\");'></td>";
		print "</tr>";


		print "<td align='center' width='15%'><b>Comments:</b></td>";
		print "<td colspan='5'><textarea style='width:100%' $dis name='comments' id='comments' cols='80' rows='5'  onchange='upd_fld(\"comments\");'>$comments</textarea></td>";
		print "</tr>";
		print "<tr>";
		print "<td valign='top' colspan='3' align='center'>";
		$sql="SELECT CONCAT(lname, ', ', fname) as nm, p.uid, card_type, jersey_no, c.uid as card_id
						FROM tmsl_player p INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
						INNER JOIN tmsl_season s ON s.uid=pt.season_uid
						LEFT OUTER JOIN (SELECT uid, card_type, player_uid FROM tmsl_card WHERE game_uid=$uid) c ON p.uid=c.player_uid
						WHERE s.uid=$h_season_id AND pt.team_uid=$tm_h
						AND pt.registered=2
						AND p.uid NOT IN
							(SELECT player_uid FROM tmsl_suspended WHERE  str_to_date('$game_date', '%m/%d/%Y') >= start_date AND
								str_to_date('$game_date', '%m/%d/%Y') <= stop_date
							)
						ORDER BY lname, fname";
		$arrPlayersH=dbSelectSQL($sql);
		print "<table id='playerTblGmRptH' border='1'>";
		print "<tr><th>jersey</th><th>name</th><th>Y</th><th>R</th></tr>";
		foreach ($arrPlayersH as $rec) {
			print "<tr>";
			print "<td>{$rec['jersey_no']}</td>";
			print "<td>{$rec['nm']}</td>";
			if ($rec['card_type']=='Y') {
				if ($edit) {
					$onclk1="onclick='remove_card({$rec['card_id']});'";
					$onclk2="onclick='add_card({$rec['uid']}, \"h\", $tm_h, $h_season_id, \"R\");'";
				}
				print "<td><span style='background-color:yellow; border: 1px solid black; $crs' $onclk1>&nbsp;&nbsp;&nbsp;</span><td><span $onclk2 style='$crs'>&nbsp;&nbsp;&nbsp;</span></td>";
			} elseif ($rec['card_type']=='R') {
				if ($edit) {
					$onclk1="onclick='add_card({$rec['uid']}, \"h\", $tm_h, $h_season_id, \"Y\");'";
					$onclk2="onclick='remove_card({$rec['card_id']});'";
				}
				print "<td><span $onclk1 style='$crs'>&nbsp;&nbsp;&nbsp;</span></td><td><span style='background-color:red; border: 1px solid black; $crs' $onclk2>&nbsp;&nbsp;&nbsp;</span></td>";
			}
			else {
				if ($edit) {
					$onclk1="onclick='add_card({$rec['uid']}, \"h\", $tm_h, $h_season_id, \"Y\");'";
					$onclk2="onclick='add_card({$rec['uid']}, \"h\", $tm_h, $h_season_id, \"R\");'";
				}
				print "<td><span $onclk1 style='$crs'>&nbsp;&nbsp;&nbsp;</span></td><td><span $onclk2 style='$crs'>&nbsp;&nbsp;&nbsp;</span></td>";
			}
			print "</tr>";
		}
		print "</table>";
		print "</td>";

		print "<td valign='top' colspan='3' align='center'>";
		$sql="SELECT CONCAT(lname, ', ', fname) as nm, p.uid, card_type, jersey_no, c.uid as card_id
						FROM tmsl_player p INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
						INNER JOIN tmsl_season s ON s.uid=pt.season_uid
						LEFT OUTER JOIN (SELECT uid, card_type, player_uid FROM tmsl_card WHERE game_uid=$uid) c ON p.uid=c.player_uid
						WHERE s.uid=$v_season_id AND pt.team_uid=$tm_v
						AND pt.registered=2
						AND p.uid NOT IN
							(SELECT player_uid FROM tmsl_suspended WHERE  str_to_date('$game_date', '%m/%d/%Y') >= start_date AND
								str_to_date('$game_date', '%m/%d/%Y') <= stop_date
							)
						ORDER BY lname, fname";
		$arrPlayersV=dbSelectSQL($sql);
		print "<table id='playerTblGmRptV' border='1'>";
		print "<tr><th>jersey</th><th>name</th><th>Y</th><th>R</th></tr>";
		foreach ($arrPlayersV as $rec) {
			print "<tr>";
			print "<td>{$rec['jersey_no']}</td>";
			print "<td>{$rec['nm']}</td>";
			if ($rec['card_type']=='Y')
				print "<td><span style='background-color:yellow; border: 1px solid black; $crs' onclick='remove_card({$rec['card_id']});'>&nbsp;&nbsp;&nbsp;</span><td><span onclick='add_card({$rec['uid']}, \"v\", $tm_v, $v_season_id, \"R\");' style='$crs'>&nbsp;&nbsp;&nbsp;</span></td>";
			elseif ($rec['card_type']=='R')
				print "<td><span onclick='add_card({$rec['uid']}, \"v\", $tm_v, $v_season_id, \"Y\");' style='$crs'>&nbsp;&nbsp;&nbsp;</span></td><td><span style='background-color:red; border: 1px solid black; $crs' onclick='remove_card({$rec['card_id']});'>&nbsp;&nbsp;&nbsp;</span></td>";
			else print "<td><span onclick='add_card({$rec['uid']}, \"v\", $tm_v, $v_season_id, \"Y\");' style='$crs'>&nbsp;&nbsp;&nbsp;</span></td><td><span onclick='add_card({$rec['uid']}, \"v\", $tm_v, $v_season_id, \"R\");' style='$crs'>&nbsp;&nbsp;&nbsp;</span></td>";
			print "</tr>";
		}
		print "</table>";
		print "</td>";

		print "</tr>";
		print "<tr>";
		print "<td colspan='6' align='center'><input type='submit' name='sv' value='save' $dis>
			<input type='button' value='close' onclick='window.opener.location.href=window.opener.location;window.close();'>
			<input type='submit' value='submit' name='sbm' $dis>
			<input type='submit' value='reset' name='clr' $dis></td>";
		print "</tr>";
		print "<tr>";
		print "<td colspan='6' align='center'>";
		print "<div class='instrTtl' style='width:600px'>Instructions</div>";
		print "<div class='instr' style='width:600px'>Please fill in the score for both teams, and comments. If any yellow or red cards were issued, indicate by clicking in the Y or R column by the player's name. A second click makes the card disappear. Issuing a red card brings up a new window with the Miscondict Report. Reset resets the score and comments only -- reset cards one by one. Click submit when finished -- no more editing will be allowed after that.</div>";
		print "</td>";
		print "</tr>";

		print "</table>";
		print "</form>";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
