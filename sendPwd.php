<?
		include_once("session.php");
		if ($username) {
			$uid=getScalar('email', trim($username), 'uid', 'tmsl_player');
			$email=$username;
			if (!$uid) $msg = "There is no record with the email $email in our database. Try the <a href='firstTimeUser.php'>New Account Page</a> or <a href='sendPwd.php'>enter your email again</a>."; 
			else {
				$sha1=getScalar('u.name', trim($username), 'pwd', 'tmsl_user u JOIN tmsl_player p ON u.player_uid=p.uid');
				$msg = "An email has been sent to: $email. Follow the instructions there.";
				$link="$site_url/pwdInit.php?uid=$uid&p=".sha1($uid);
				$body="You can reset your TMSL password by clicking this link: $link";
				$body.= "  Your password will then be the same as your username (in lower case).";
				mail($email, 'TMSL info', $body, "$noreply");
			}
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Lost Password</div>";
		print "<div id='mainPar'>";
		if ($msg) {
			print "<div class='tmslBig'>$msg<br/></div><br/>";
		} else {
			print "Please enter the email you registered with, and click ok to have your password reset.
				An email will be sent; there will be a link for you to click,
				and you can change your password there.<br/><br/>";
			print "<form>Email: <input type='text' name='username' size='60'><br/>";
			print "<input type='submit' value='ok'></form>";
    }
		print "</div>";
		print "</body>";
		print "</html>";
?>
