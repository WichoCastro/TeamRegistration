<?
	include_once("session.php");
	$sql="SELECT distinct u.name as name, email
						FROM tmsl_game_assign ga INNER JOIN tmsl_game g ON ga.game_uid=g.uid
						INNER JOIN tmsl_player p ON ga.user_uid=p.uid
						INNER JOIN tmsl_user u ON u.player_uid=p.uid
						WHERE g.game_dt = '2010-12-12' AND edit_right=2
						AND SHA1(u.name) = pwd
					ORDER BY game_tm, lname, fname";
	$arr = dbSelectSQL($sql);
	foreach ($arr as $rec) {
		$eml=$rec['email'];
		$unm=$rec['name'];
		if ($eml)
			print "mailing to $eml $unm<br>";
		else print "not mailing to $eml $unm<br>";
		$sbj = "TMSL Referee Reports";
		$bod = "TMSL would like you to use our online game reporting system beginning this weekend. The system is still being tested, and we're looking for valuable feedback to make this system as user-friendly and functional as possible. You have been assigned the username $unm with password $unm. Please log on to http://tmslregistration.com and change the password to something you'll remember. You should be directed to a list of games for which you're the center ref for this Sunday, Dec 12. To fill out the game report, click on the icon next to the game. That will open up a new window containing a game report. Fill out the score, any comments, and assign cards. Please share your thoughts on the system with Maggie and myself, and if you have any questions, don't hesitate to ask.\r\nThanks! \r\nJohn Farmer";
		if ($send==1) $xtra = "From:John Farmer<futiaz@gmail.com>\r\n";
		if ($send==2) $xtra = "From:John Farmer<futiaz@gmail.com>\r\nCc:cactusmouse@comcast.net";
		if ($send==1) mail('futiaz@gmail.com', $sbj, $bod, $xtra);
		if ($send==2) mail($eml, $sbj, $bod, $xtra);
	}
?>
