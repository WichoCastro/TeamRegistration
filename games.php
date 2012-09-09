<?
	include_once("session.php");
	if ($_SESSION['mask'] & 4) $adm=1; else $adm=0; // necessary if adm=1 passed in query string
	if (!$adm) $edit=0;
	if ($sbm || $add) {
		//print_r($_GET);
		foreach($_GET as $key=>$val) {
			$p=strrpos($key, '_');
			$id=substr($key, $p+1);
			if (!$id) continue;
			$fld=substr($key, 0, $p);
			//print "fld $fld uid $id val $val<br>";
			if (is_numeric($id)) $arr[$id][$fld]=$val;
		}
		foreach($arr as $id=>$vals) {
			$setArr=array();
			foreach($vals as $fld=>$val) {
				$setArr[]="$fld='$val'";
			}
			$setClause=implode(", ", $setArr);
			$sql="UPDATE tmsl_game SET $setClause WHERE uid=$id";
			mysql_query($sql);
		}
	}
	if ($add) {
		dbInsert('tmsl_game', array('game_dt'=>$dt, 'season_uid'=>$season_id, 'game_loc'=>"$location"));
	}
	if ($season_id) $_SESSION['season_uid']=$season_id;
	//$season_id=$_SESSION['season_uid'];
	if ($isRef && !$adm && !$_SESSION['editTeams']) $ref_id = $_SESSION['logon_uid'];
	print $beginning;
	print "<div id='ttlBar'>TMSL Games</div>";
	print "<div id='mainPar'>";
	//is there a date? if so, show games for that date
	if (!$dt) $dt=date('Y-m-d');
	if (!$numDays) $numDays=7;
        if ($numDays % 7) {$numDays += (7 - ($numDays % 7)); print "numD=$numDays";}
	if (!$srch) $srch = 1;
	//verify that the date is good, and change its form
	$dt=strtotime($dt);
	if ($dt) $dt=date('Y-m-d', $dt);
	else die('bad date');

	print "<form method='get' id='frm'>";
	print "<table border='1' align='center' style='border-collapse:collapse'>";

	$wkAgo = date('Y-m-d', mktime(0,0,0,substr($dt,5,2), substr($dt,8,2) - 7, substr($dt,0,4)));
	$wkAhead = date('Y-m-d', mktime(0,0,0,substr($dt,5,2), substr($dt,8,2) + 7, substr($dt,0,4)));
	$q=explode('&',$_SERVER['QUERY_STRING']);
	foreach($q as $pr) {
		$kvp=explode('=',$pr);
		if ($kvp[0]!='dt')
			$qry.="&".$pr;
	}
	$qry1 = "dt=$wkAgo$qry";
	$qry2 = "dt=$wkAhead$qry";
	$lnkWkAgo = "<a href='games.php?$qry1' title='go back one week'><</a>";
	$lnkWkAhead = "<a href='games.php?$qry2' title='go forward one week'>></a>";
	print "<tr><td>$lnkWkAgo Date: $lnkWkAhead</td><td>";

	print "<input type='text' value='$dt' name='dt' id='start_date'>";
	print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='game date'></a></td></tr> ";
	$sql=" SELECT DISTINCT s.uid, CONCAT( d.name, ' -- ', s.name ) AS nm
		FROM tmsl_season s
		INNER JOIN tmsl_division d ON s.division_uid = d.uid
		WHERE s.stop_date >= '$dt' AND s.start_date <= DATE_ADD('$dt' , INTERVAL $numDays DAY)
		ORDER BY d.rank, s.start_date desc";
		$arrSeasons=buildSimpleSQLArr("uid", "nm", $sql);
		print "<tr><td>Season:</td>";
		print "<td>".getSelect("season_id", $arrSeasons, array(0=>"--Show All--"), $season_id, "onchange=\"showTeams(0);\"")."</td></tr>";
		print "<tr><td><div id='teamLabel'/></td><td><div id='teamDDL'/></td>";
		$sql = "SELECT CONCAT(lname, ', ', fname) as nm, player_uid FROM tmsl_user u INNER JOIN tmsl_player p ON u.player_uid=p.uid WHERE isReferee=1 ORDER by lname, fname";
		$arrRefs=buildSimpleSQLArr("player_uid", "nm", $sql);
		$refSelect = getSelect("ref_id", $arrRefs, array(0=>"--All--"), $ref_id, "");
		if ($adm) print "<tr><td>Center Referee:</td><td>$refSelect</td>";
		print "<tr><td>Weeks to show:</td>";
		print "<td><select name='numDays'>";
		for ($i=1;$i<53;$i++) {
			$j=7*$i;
			if ($j==$numDays) $sel="selected='selected'";
			else $sel="";
			print "<option value=$j $sel>";
			print $i;
			print "</option>";
		}
		print "</select></td></tr>";
		$arrLocs=buildSimpleSQLArr("game_loc", "game_loc", "SELECT DISTINCT game_loc FROM tmsl_game ORDER BY game_loc");
		print "<tr><td>Location:</td>";
		print "<td>".getSelect("location", $arrLocs, array(''=>"Any"), $location, "")."</td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='ok'></td></tr>";

	print "</table>";
	print "</form>";

	if ($season_id || $srch) {
		if ($location) $locClause=" AND game_loc LIKE '$location' ";
		if ($season_id) $sznClause=" AND season_uid=$season_id ";
		if ($team_id) $tmClause=" AND (team_h=$team_id OR team_v=$team_id) ";
		if ($ref_id) $refClause=" AND user_uid=$ref_id ";

		$sql="SELECT g.uid FROM tmsl_game g LEFT JOIN tmsl_game_assign ga ON g.uid=ga.game_uid AND edit_right=2 WHERE game_dt >= '$dt' AND game_dt < DATE_ADD('$dt', INTERVAL $numDays DAY)
					$sznClause $locClause $tmClause $refClause ORDER BY game_dt, season_uid, game_tm";					
		$gms=dbSelectSQL($sql);
		//if ($adm && $season_id) print "<a href='index.php?dt=$dt&season_id=$season_id&numWeeks=$numWeeks&add=1'>New Game</a>";
		$arrDt=dbSelectSQL("SELECT DATE_FORMAT('$dt', '%M %e') as sd, DATE_FORMAT(DATE_ADD('$dt', INTERVAL $numDays DAY), '%M %e') as ed");
		$sd=$arrDt[0]['sd'];
		$ed=$arrDt[0]['ed'];
		if ($ref_id) $refTxt = "for ".getUserName($ref_id);
		print "<h3>Games $refTxt from $sd to $ed</h3>";
		if (!empty($gms)) {
			print "<form id='frm2'>";
			print "<input type='hidden' name='dt' value='$dt'>";
			print "<input type='hidden' name='season_id' value='$season_id'>";
			print "<input type='hidden' name='numDays' value='$numDays'>";
			print "<input type='hidden' name='location' value='$location'>";
			//print "<table border='1' align='center' style='border-collapse:collapse'>";
			print "<table class='rtbl' align='center'>";

			if ($season_id) $sznClause=" WHERE season_uid=$season_id "; else $sznClause=" WHERE s.stop_date >='$dt' AND s.start_Date <= '$dt'";
if ($debug) print "SELECT team_uid, UPPER(tname) as tname FROM tmsl_team_season $sznClause ORDER BY tname"; 
			$team_list_sql="select team_uid, UPPER(tname) as tname from tmsl_team_season ts inner join tmsl_season s on ts.season_uid=s.uid $sznClause ORDER BY tname";
			//$arrTms=buildSimpleSQLArr('team_uid', 'tname', "SELECT team_uid, UPPER(tname) as tname FROM tmsl_team_season $sznClause ORDER BY tname");
			$arrTms=buildSimpleSQLArr('team_uid', 'tname', $team_list_sql);

			foreach($gms as $rec) {
				$uid=$rec['uid'];
				$sql="SELECT DATE_FORMAT(game_dt, '%Y-%m-%d') as game_dt,
					DATE_FORMAT(game_tm, '%H:%i') as game_tm,
					DATEDIFF(game_dt, now()) as dif,
					team_h, team_v, season_uid, game_loc, team_h_pts, team_v_pts, team_h_score, team_v_score
					FROM tmsl_game WHERE uid=$uid";					
				$arr=dbSelectSQL($sql);
				$rec2=$arr[0];

				$str = "";

				if (!$season_id && ($rec2['game_dt'] <> $last_dt || $rec2['season_uid'] <> $last_szn))
					$str .= "<tr><td colspan=10 style='border-bottom: 3px solid black;'>".getSeasonName($rec2['season_uid'])."</td></tr>";

				$str .= "<tr>";

				if ($_SESSION['logged_in']) {
					if ($adm) {
						$str .= "<td>";
						$str .= "<img src='images/soccer.png' title='manage referee assignments' alt='manage referee assignments' border='0'
										onclick='window.open(\"game_refs.php?uid={$rec['uid']}\",
										 \"ref_win\",
										 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'>";
						$str .= "</td>";
					}
					if ($adm || permissionGameReport($rec['uid'], $_SESSION['logon_uid'])) {
						$str .= "<td>";
						$str .= "<img src='images/report.png' title='fill out game report' alt='fill out game report' border='0'
										onclick='window.open(\"game_report.php?uid={$rec['uid']}\",
										 \"game_report_win\",
										 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'>";
						$str .= "</td>";
					} else $str .= "<td>&nbsp;</td>";
				}

				if ($edit) $str .= "<td><input type='text' id='game_dt_$uid' name='game_dt_$uid' value='{$rec2['game_dt']}' onchange='upd_fld()'>
					<a href='#' onClick='cal.select(document.forms[\"frm2\"].game_dt_$uid,\"anchor$uid\",\"yyyy-MM-dd\"); upd_fld(); return false;' NAME='anchor$uid' ID='anchor$uid'><img src='calendar.png' border='0' alt='cal' title='game date'></a>
					</td>";
				else $str .= "<td>{$rec2['game_dt']}</td>";

				$str .= "<td>".showTimeDropDown("game_tm_$uid", 800, 2200, 15, $rec2['game_tm'], $edit, array(0=>"--Select--"), "onchange='upd_fld()'")."</td>";
				//$str .= "<td>".getTeamName($rec2['team_h'], $rec2['season_uid']);
				$str .= "<td><a href='".getTmLnk($rec2['team_h'], $rec2['season_uid'])."'>".getSelect("team_h_$uid", $arrTms, $arrFirstOpts=array(0=>"--Select--"), $rec2['team_h'], "onchange='upd_fld()'", $edit)."</a>";
				if ($adm && $rec2['dif']>=0 && $rec2['dif']<$days_before)
					$str .= "<img src='images/printer.png'
										onclick='window.open(\"roster_card.php?team_id={$rec2['team_h']}&game_uid={$uid}\",
										 \"roster_win\",
										 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'
										title='print game card' alt='print game card' border='0'>";
				$str .= "</td>";
				if ($rec2['team_h_pts']>=0) {
					$str .= "<td>{$rec2['team_h_score']}</td><td>{$rec2['team_v_score']}</td>";
				} else $str .= "<td>&nbsp;-&nbsp; </td><td> &nbsp;-&nbsp; </td>";
				$str .= "<td><a href='".getTmLnk($rec2['team_v'], $rec2['season_uid'])."'>".getSelect("team_v_$uid", $arrTms, $arrFirstOpts=array(0=>"--Select--"), $rec2['team_v'], "onchange='upd_fld()'", $edit)."</a>";
				if ($adm && $rec2['dif']>=0 && $rec2['dif']<$days_before)
					$str .= "<img src='images/printer.png'
										onclick='window.open(\"roster_card.php?team_id={$rec2['team_v']}&season_id={$rec2['season_uid']}&sbm=1&game_date={$rec2['game_dt']}\",
										 \"roster_win\",
										 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'
										title='print game card' alt='print game card' border='0'>";
				$str .= "</td>";
				if ($edit) $str .= "<td><input type='text' id='game_loc_$uid' name='game_loc_$uid' value='{$rec2['game_loc']}' onchange='upd_fld()'></td>";
				else $str .= "<td>{$rec2['game_loc']}</td>";
				if ($edit) $str .= "<td><img src='images/delete.png' title='delete this game' alt='delete this game' border='0'
										onclick='window.open(\"game_delete.php?uid={$rec['uid']}\",
										 \"ref_win\",
										 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=no\")'></td>";
				$str .= "</tr>";
				print $str;
				$last_dt=$rec2['game_dt'];
				$last_szn=$rec2['season_uid'];
			}
			if ($adm) {
				if ($edit) print "<tr><td colspan='10'><input type='submit' name='sbm' id='sbm' disabled='true' style='color:#ccc; background-color:#ddd' title='no changes to save' value='save changes'>";
				else {
				  print "<tr><td colspan='10'><input type='submit' name='edit' value='edit'><input type='button' onclick='window.location=\"upl.php\"' value='upload CSV'>";
				  print "<input type='button' onclick='window.location=\"assignr_pull.php\"' value='sync with assignr' title='Pull data from assignr.com for the next week'>";
				}  
				if ($season_id) print "<input type='submit' name='add' value='New Game'>";
				print "</td></tr>";
			}
			print "</table>";
			print "</form>";
		} else {
				print "No Games to Show";
				if ($adm) {
				  print "<br/><input type='button' onclick='window.location=\"assignr_pull.php\"' value='sync with assignr' title='Pull data from assignr.com for the next week'>";
				  print "<input type='button' onclick='window.location=\"upl.php\"' value='upload CSV'>";
				  if ($season_id) print "<input type='submit' name='add' value='New Game'>";
				}
		}
	} else print "Select the criteria for the games you wish to see and press ok.";
	print "</div>";
	print "<div id='footer-spacer'></div>";
	print "</div >"; //end container
	print $footer;
?>
	<script>
		function upd_fld() {
			document.forms['frm2'].sbm.disabled=false;
			document.forms['frm2'].sbm.style.color='red';
			document.forms['frm2'].sbm.title='click to save changes';
		}
		function showTeams(s) {
			document.getElementById('teamLabel').innerHTML = 'Team:';
			var url='ajax_DDL.php';
			var val=$F('season_id');
			if (s != 0) val=s;
			var params='tbl=tmsl_team_season&keyCol=team_uid&valCol=tname&id=team_id&whr=where%20season_uid%3D' + val
			params += '&ordr=order%20by%20tname&selectedVal=<?=$team_id?>&firstOpts=1';
			var myAjax=new Ajax.Updater('teamDDL', url, {method: 'post', parameters: params });
		}
	</script>
<?
	print "</body>";
	print "</html>";
?>
