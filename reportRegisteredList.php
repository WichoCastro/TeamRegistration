<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!$start_date) $start_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')-7,date('Y')));
		if (!$stop_date) $stop_date=date('m/d/Y');
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
		print "<div id='ttlBar'>Report: Registered List</div>";

		print "<div id='mainPar'>";
		print "<form name='frm' id='frm' method='post'>";
		print "<table border='1' align='center'>";
		print "<tr><td>From:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		print "<tr><td>To:</td><td><input type='text' value='$stop_date' name='stop_date' id='stop_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].stop_date,\"anchor2\",\"MM/dd/yyyy\"); return false;' NAME='anchor2' ID='anchor2'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		$arrTeams=buildSimpleSQLArr("uid", "name", "SELECT t.uid, tl.tname as name FROM tmsl_team t INNER JOIN tmsl_team_season tl ON t.uid=tl.team_uid WHERE stop_date = '0000-00-00' ORDER BY tname");
		if (!$_SESSION['editTeams']) $sql.=" AND t.uid IN (".implode("", $myTeams).")";
		print "<tr><td>Team:</td><td>";
		print getSelect("team_id", $arrTeams, array(0=>"--Any--"), $team_id, "");
		print "</td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
		print "</table>";
		print "</form>";
		$sql = "select fname, lname, tname, pt.start_date, pt.registered, pt.notes 
			from tmsl_player_team pt 
			inner join tmsl_player p on p.uid=pt.player_uid 
			inner join tmsl_team_season ts on ts.team_uid = pt.team_uid and ts.season_uid=pt.season_uid 
			where pt.start_date between '$start_date_sql' and '$stop_date_sql'";
		if ($team_id) $sql .= " AND pt.team_uid=$team_id";
		$sql .= " ORDER BY pt.start_date, tname, p.lname, p.fname";
		$res=mysql_query($sql);
		print "Added:<br/>";
		print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
		print "<tr><th>Date</th><th>Player</th><th>Team</th><th>Status</th><th>Notes</th></tr>";
		while ($rec=mysql_fetch_Array($res)) {
			$haveData=true;
			print "<tr>";
			print "<td>".$rec['start_date']."</td>";
			print "<td>".$rec['fname']." ".$rec['lname']."</td>";
			print "<td>".$rec['tname']."</td>";
			if ($rec['registered'] == 2) print "<td>reg</td>";
			else print "<td>not reg</td>";
			print "<td>".$rec['notes']."</td>";
			print "</tr>";
		}
		if (!$haveData) print "<tr><td colspan='5'>No adds.</td></tr>";
		print "</table>";
		$haveData=false;
		$sql = "select fname, lname, tname, pt.start_date, pt.registered, pt.notes 
			from tmsl_player_team pt 
			inner join tmsl_player p on p.uid=pt.player_uid 
			inner join tmsl_team_season ts on ts.team_uid = pt.team_uid and ts.season_uid=pt.season_uid 
			where pt.start_date between '$start_date_sql' and '$stop_date_sql'";
		if ($team_id) $sql .= " AND pt.team_uid=$team_id";
		$sql .= " ORDER BY pt.start_date, tname, p.lname, p.fname";
		$res=mysql_query($sql);
		print "<br/><br/>Dropped:<br/>";
		print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
		print "<tr><th>Date</th><th>Player</th><th>Team</th><th>Status</th><th>Notes</th></tr>";
		while ($rec=mysql_fetch_Array($res)) {
			$haveData=true;
			print "<tr>";
			print "<td>".$rec['start_date']."</td>";
			print "<td>".$rec['fname']." ".$rec['lname']."</td>";
			print "<td>".$rec['tname']."</td>";
			print "<td>dropped</td>";
			print "<td>".$rec['notes']."</td>";
			print "</tr>";
		}
		if (!$haveData) print "<tr><td colspan='5'>No drops.</td></tr>";
		print "</table>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
