<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Log in to system</title>
<link href="tmsl.css" rel="stylesheet" type="text/css">
</head>
<body onLoad="document.loginform.loginname.focus()">
<div id='banner'>TMSL</div>
<div style='position:relative; top:200; text-align:center'>
		This is the Registration Site for the Tucson Metro Soccer League.
		Team representatives manage their rosters and register here.<br/>
		The site is password-protected; if you would like to register a team, or already are a team representative,
		you can request a userid and password by sending an email
		to <a href='mailto:admin@tmslregistration.com'>admin@tmslregistration.com</a>.<br/>
<table align="center" valign="center">
    <form action="<?php print("http://" .$_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI']); ?>" name="loginform" id="loginform" method="post">
	<? if($try == 1)  print "<tr><td colspan='2' class='error' align='center'>Invalid Credentials. Please try again.</td></tr>"; ?>
	<tr><td colspan="2" align="center">
		<br/><br/>Please enter your login credentials:</td></tr>
	<tr><td class="body">Username:<td><input type="text" name="loginname" id="loginname" class="smallform"></td></tr>
	<tr><td class="body">Password:<td><input type="password" name="pwd" class="smallform"></td></tr>
	<tr><td>&nbsp;<td><input type="submit" name="submit" value="Login" class="pointer"></td></tr>
		<input type="hidden" name="try" value="1">
	</form>
</table>
<div id='mainPar'>Trouble logging in?
  <br/><a href='sendPwd.php'>Request forgotten password</a>
  <br/><a href='mailto:support@tmslregistration.com'>Contact Webmaster</a>
</div>
</div>
</body>
</html>
