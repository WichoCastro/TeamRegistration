<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!$start_date) $start_date = date('m/d/Y');
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "</head>";
		print "<body>";
		//echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Not-yet 45 playing O-45</div>";
		//print "<form name='frm' id='frm'>";print "<table>";
		//print "<tr><td>Show change log dating back to:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		//print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a>";
		//print "<input type='submit' value='ok'></td></tr>";
		//print "</table>";print "</form>";

		print "<div id='mainPar'>";
		if ($start_date) {
			$sql="SELECT DISTINCT p.fname, p.lname, ts.tname, dob 
			  FROM tmsl_player p INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
			  INNER JOIN tmsl_team_season ts ON pt.team_uid=ts.team_uid AND pt.season_uid=ts.season_uid
			  INNER JOIN tmsl_season s ON ts.season_uid=s.uid
			  WHERE s.stop_date > now()
			  AND s.division_uid=1
			  AND DATE_ADD(dob, INTERVAL 45 YEAR) > now()
			  AND pt.registered=2
			  ORDER BY DOB";
			//print $sql;
			print printTable($sql);
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
