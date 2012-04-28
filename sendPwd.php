<?
		include_once("session.php");
		if ($username) {
			$email=getScalar('u.name', trim($username), 'email', 'tmsl_user u JOIN tmsl_player p ON u.player_uid=p.uid');
			if ($email) {
				$sha1=getScalar('u.name', trim($username), 'pwd', 'tmsl_user u JOIN tmsl_player p ON u.player_uid=p.uid');
				$msg = "An email has been sent to: $email";
				$link="http://tmslregistration.com/pwdReset.php?u1=$username&s=$sha1&u=".sha1(strtolower($username));
				$body="You can reset your TMSL password by clicking this link: $link";
				$body.= "  Your password will then be the same as your username (in lower case).";
				mail($email, 'TMSL info', $body, 'FROM:noreply@tmslregistration.com');
			}else{
				$msg = "No email found.  Either you entered an invalid username or there is no email on file for that username.  Please contact TMSL.";
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
			print "<div class='error'>$msg<br/></div><br/>";
		}
			print "Please enter your username, and click ok to have your password reset.
				The username is typically first initial followed by last name, like jsmith.
				An email will be sent to the email address we have on file; there will be a link for you to click,
				and you can change your password there.<br/><br/>";
			print "<form>Username: <input type='text' name='username'><br/>";
			print "<input type='submit' value='ok'></form>";

		print "</div>";
		print "</body>";
		print "</html>";
?>
