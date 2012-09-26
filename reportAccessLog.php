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
		echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Access Log</div>";
		print "<form name='frm' id='frm'>";print "<table>";
		print "<tr><td>Show access log for:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a>";
		print "<input type='submit' value='ok'></td></tr>";
		print "</table>";print "</form>";

		print "<div id='mainPar'>";
		if ($start_date) {
			$mm=substr($start_date, 0, 2);
			$dd=substr($start_date, 3, 2);
			$yyyy=substr($start_date, 6, 4);
			$stop_date=date('m/d/Y',mktime(0,0,0,$mm,$dd+1,$yyyy));
			$sql="SELECT ts, lname, fname, ip FROM tmsl_login L INNER JOIN tmsl_player P ON L.uid = P.uid";
			$sql.=" WHERE ts >= STR_TO_DATE('$start_date', '%m/%d/%Y') AND ts < STR_TO_DATE('$stop_date', '%m/%d/%Y')";
			$sql.=" ORDER BY ts DESC";
			///print $sql;
			print printTable($sql);
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
