<?
	//FIX
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!hasRegistrationAuthority($_SESSION['mask'])) header("Location:index.php");
		if ($del) {
			$msg .= "Warning: Please delete suspensions ONLY in the case that they were erroneously assigned. Otherwise the record of this suspension is deleted from the database. To lift a suspension that has been served, set the 'end date' accordingly and amount owed to 0.<br/><br/>";
			$msg .= "<a href='{$_SERVER['PHP_SELF']}?reallyDel=1&s_uid=$s_uid'>I REALLY WANT TO DELETE THE RECORD!</a>";

		}			
		if ($reallyDel)
			dbDelete('tmsl_suspended', array('uid'=>$s_uid), true);
		if ($s_uid_chg) {
			$s_d=strtotime($start_date);
			$e_d=strtotime($stop_date);
			$arr=array('reason'=>$reason, 'fine'=>$fine);
			if ($s_d) {$s_d=date('Y-m-d', $s_d);$arr['start_date']=$s_d;}
			if ($e_d) {$e_d=date('Y-m-d', $e_d);$arr['stop_date']=$e_d;}
			dbUpdate('tmsl_suspended', $arr, array('uid'=>$s_uid_chg), true, true);
		}
		if (!$start_date) $start_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')-7,date('Y')));
		if (!$stop_date) $stop_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')+7,date('Y')));
		$start_date_sql=date('Y-m-d', strtotime($start_date));
		$stop_date_sql=date('Y-m-d', strtotime($stop_date));
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "</head>";
		print "<body>";
		echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Report: Suspended</div>";

		print "<div id='mainPar'>";
		if ($msg) {
			print "<span id='updateMsg'>$msg<br/></span><br/>";
			exit;
		}
		print "<form name='frm' id='frm' method='get' action='reportSuspended.php'>";
		print "<table border='1' align='center'>";
		print "<tr><td>From:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		print "<tr><td>To:</td><td><input type='text' value='$stop_date' name='stop_date' id='stop_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].stop_date,\"anchor2\",\"MM/dd/yyyy\"); return false;' NAME='anchor2' ID='anchor2'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		if ($showOwesFine) $ck="checked='true'";
		print "<tr><td colspan='2' align='center'><input type='checkbox' name='showOwesFine' $ck><span style='font-size:8pt'>Show those that owe fines, regardless of date</span></td></tr>";
		//$arrTeams=buildSimpleSQLArr("uid", "name", "SELECT t.uid, t.name FROM tmsl_team t INNER JOIN tmsl_team_season tl ON t.uid=tl.team_uid");
		//if (!$_SESSION['editTeams']) $sql.=" AND t.uid IN (".implode("", $myTeams).")";
		//print "<tr><td>Team:</td><td>";
		//print getSelect("team_id", $arrTeams, array(0=>"--Any--"), $team_id, "");
		//print "</td></tr>";
		$arrSortBy=array('lname'=>'Last Name', 's.start_date'=>'From', 's.stop_date'=>'To', 's.fine'=>'Fine');
		print "<tr><td>Order By:</td><td>";
		print getSelect("ord", $arrSortBy, array(), $ord, "");
		if ($desc) $desc_ck="checked='true'";
		print " <input type='checkbox' name='desc' $desc_ck><span style='font-size:8pt;'>In descending order</span>";
		print "</td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
		print "</table>";
		print "</form>";
		$sql="SELECT distinct s.uid as s_uid, DATE_FORMAT(s.start_date, '%m/%d/%Y') as start_date, DATE_FORMAT(s.stop_date, '%m/%d/%Y') as stop_date,
		  s.player_uid, p.fname, p.lname, s.reason, s.fine FROM tmsl_suspended s INNER JOIN tmsl_player p ON s.player_uid=p.uid
		  WHERE (s.start_date <= '$stop_date_sql' AND s.stop_date >= '$start_date_sql') OR (s.fine > 0)";
		//if ($team_id) $sql .= " AND pt.team_uid=$team_id";
		if ($ord) $sql.= " ORDER BY $ord";
		if ($desc) $sql .= " DESC";
		if ($edit) {
			$sql="SELECT s.uid as s_uid, DATE_FORMAT(s.start_date, '%m/%d/%Y') as start_date, DATE_FORMAT(s.stop_date, '%m/%d/%Y') as stop_date, p.fname, p.lname, t.name, s.reason, s.fine FROM tmsl_suspended s INNER JOIN tmsl_player p ON s.player_uid=p.uid
			LEFT JOIN tmsl_team t ON s.team_uid=t.uid WHERE s.uid=$s_uid";
		}
		$res=mysql_query($sql);
		print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
		if ($edit) print "<form>";
		print "<tr>";
		if (!$edit) print "<th>Edit</th>";
		print "<th>From</th><th>To</th><th>Player</th><th>Reason</th><th>Fine</th></tr>";
		while ($rec=mysql_fetch_Array($res)) {
			$haveData=true;
			$s_uid=$rec['s_uid'];
			print "<tr>";
			if (!$edit) print "<td nowrap><a href='reportSuspended.php?edit=1&s_uid=$s_uid'>
							<img alt='Modify Details' src='images/pencil.png' title='Modify Details' border='0'>
							</a>
							<a href='reportSuspended.php?del=1&s_uid=$s_uid'>
							<img alt='Remove Suspension' src='images/delete.png' title='Remove Suspension' border='0'>
							</a></td>";
			if ($edit) {
				print "<td><input type='text' name='start_date' value='".$rec['start_date']."'></td>";
				print "<td><input type='text' name='stop_date' value='".$rec['stop_date']."'></td>";
				print "<td>".$rec['fname']." ".$rec['lname']."</td>";
				//print "<td>".$rec['name']."</td>";
				print "<td><textarea name='reason' rows='4' cols='40'>".$rec['reason']."</textarea></td>";
				print "<td><input type='text' name='fine' value='".$rec['fine']."'></td>";
			} else {
				print "<td>".$rec['start_date']."</td>";
				print "<td>".$rec['stop_date']."</td>";
				print "<td nowrap><a href='editPlayer.php?edit=1&uid={$rec['player_uid']}'>".$rec['fname']." ".$rec['lname']."</a></td>";
				/*get the teams player is on between those dates:
				$sq="SELECT distinct t.name FROM tmsl_team t JOIN tmsl_team_season ts ON t.uid=ts.team_uid
					JOIN tmsl_season s ON ts.season_uid=s.uid JOIN tmsl_player_team pt ON pt.team_uid=t.uid
					JOIN tmsl_player p ON p.uid=pt.player_uid WHERE s.start_date <= '$stop_date_sql' AND
					s.stop_date >= '$start_date_sql' AND p.uid={$rec['player_uid']} AND ts.registered = 2";
				$arr=dbSelectSQL($sq);
				$tms=array();
				if (!empty($arr)) foreach ($arr as $rc) $tms[]=$rc['name'];
				print "<td>".implode(',', $tms)."</td>";
				*/
				print "<td>".$rec['reason']."</td>";
				print "<td>".$rec['fine']."</td>";
			}
			print "</tr>";
		}
		if ($edit) print "<tr><td colspan='5'>
							<input type='hidden' name='s_uid_chg' value='$s_uid'><input type='submit' value='ok'></td></tr></form>";
		if (!$haveData) print "<tr><td colspan='5'>No suspensions.</td></tr>";
		print "</table>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
