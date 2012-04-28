<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($accpt) {
			dbUpdate('tmsl_player', array('DOB_validated'=>1), array('uid'=>$player_id));
			$redir="Location:roster.php";
			header($redir);
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>TMSL</div>";
		print "<div id='mainPar'>";
		$arr=dbSelect('tmsl_player', array('fname', 'lname', 'DOB'), array('uid'=>$player_id));
		print "I accept that {$arr[0]['fname']} {$arr[0]['lname']}'s birthday is {$arr[0]['DOB']}<br/>";
		print "<input type='button' value='ok' onclick='window.location=\"validate_dob.php?player_id=$player_id&accpt=1\"'>";
		print "<input type='button' value='no' onclick='window.location=\"roster.php\"'>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
