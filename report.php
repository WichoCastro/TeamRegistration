<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print $beginning;
		$str = "<table align='center'>";
		$str .= "<tr><td><a href='reportRegisteredList.php'>Registered By Date</a></td></tr>";
		$str .= "<tr><td><a href='reportCards.php'>Red & Yellow Cards</a></td></tr>";
		$str .= "<tr><td><a href='reportRefList.php'>Referees for a Given Date</a></td></tr>";
		$str .= "<tr><td><a href='reportAddedDropped.php'>Added/Dropped Players</a></td></tr>";
		$str .= "<tr><td><a href='reportSuspended.php'>Suspended Players</a></td></tr>";
		$str .= "<tr><td><a href='reportRegistered.php'>Team Registration</a></td></tr>";
		$str .= "<tr><td><a href='reportTeamRepList.php'>Current Team Rep List</a></td></tr>";
		$str .= "<tr><td><a href='reportPlayerRegistration.php'>Player Registration</a></td></tr>";
		$str .= "<tr><td><a href='reportYoungsters.php'>Players Under 45 in the Over 45 Division</a></td></tr>";
		$str .= "<tr><td><a href='reportExport.php'>Export Data To CSV</a></td></tr>";
		$str .= "<tr><td><a href='reportAccessLog.php'>Access Log</a></td></tr>";
		$str .= "<tr><td><a href='reportPayPalLog.php'>PayPal Log</a></td></tr>";
		$str .= "<tr><td><a href='reportChangeLog.php'>Change Log</a></td></tr>";
		$str .= "<tr><td><a href='reportBadQueryLog.php'>Bad Query Log</a></td></tr>";
		$str .= "</table>";
		print "<div id='mainPar'>$str</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
