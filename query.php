<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (true || $_SESSION['mask']==255) {
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Query the DB</div>";
			print "<div id='mainPar'>";
			print "You have all the power here -- I hope to God you know what you're doing!  If not, GO AWAY!!<br/>";
			if (!$dbq) $dbq="show tables where Tables_in_tmsl like 'tmsl%'";
			$dbq=trim(stripslashes($dbq));
			print "
				<form method='post'>
					<textarea name='dbq' cols='80' rows='8'>$dbq</textarea><br/>
					<input type='submit' value='ok' class='smallform'>
				</form>";
			print "query -- $dbq<br/>";
			print printTable($dbq);
			print "</div>";
			print "</body>";
			print "</html>";
		}
	}else include("login.php");
?>
