<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($clean) {
			$sql="TRUNCATE TABLE tmsl_bad_query_log";
			mysql_query($sql);
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Bad Query Log</div>";
		print "<div id='mainPar'>";
		print "For the programmer, this shows bad db queries that the system has generated.<br/>";
		$sql="SELECT * FROM tmsl_bad_query_log ORDER BY action_date DESC";
		print printTable($sql);
		print "<form><input type='Submit' name='clean' value='Clean Out'></form>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
