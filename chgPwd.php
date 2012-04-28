<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($newPwd) {
			$m=0;
			if (!strcmp($newPwd, $newPwd2)) {
				$updArr = array('pwd'=>sha1($newPwd));
				if ($name) $updArr['name'] = $name;
				if (dbUpdate('tmsl_user', $updArr, array('player_uid'=>$uid), true, true)) {
					//print "<span id='updateMsg'>Your password has been successfully changed.  </span><br/>";
					print "<script>alert('Password successfully changed.');window.location='index.php';</script>";
					//header("Location:index.php");
					$hideForm=true;
				}
			}
			else $msg= "Passwords don't match";
		}
		if ($m == '1') {
			$msg = "Your username and password match. Please change your password:<br/>";
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Change Password</div>";

		print "<div id='mainPar'>";
		if ($msg) print "<span id='updateMsg'>$msg</span><br/>";
		if (!$uid)
			$uid=$_SESSION['logon_uid'];
		$nm=getScalar('player_uid', $uid, "name", 'tmsl_user');

		if (!$hideForm){
			print "<br/>Change password for $nm:<br/>";
			print "<form method='POST'><input type='hidden' name='uid' value='$uid'";
			print "<table align='center'>";
			if (!$adm) $dis="disabled='disabled'";
			print "<tr><td>Username:</td><td><input type='text' name='name' value='$nm' $dis></td></tr>";
			print "<tr><td>New password:</td><td><input type='password' name='newPwd'></td></tr>";
			print "<tr><td>Retype new password:</td><td><input type='password' name='newPwd2'></td></tr>";
			print "<tr><td colspan='2'><input type='submit' value='ok'></td></tr>";
			print "</table>";
			print "</form>";
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
