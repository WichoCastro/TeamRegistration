<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>TMSL Contact Info</div>";

		print "<div id='mainPar'>";
		print "The TMSL Office is at <strong><br/>TMSL OFFICE<br/>
			4651 N. First Ave., Suite 204<br/>
			Tucson, AZ 85718<br/><br/></strong>";

		if (!isset($show) || $show=='board') {
		  $select_flds="SELECT CONCAT(fname, ' ', lname) as Name, CONCAT('<a href=''mailto:',email,'''>',email,'</a>') as Email,
		    phone ";
		  $sql_from_where="FROM tmsl_player Where boardMember = 1 ";
		  $sql_order="ORDER BY lname";
		  $hot='hot';
		}
		print "<span class='tab$hot'><a href='contact.php?show=board'>Board</a></span>";

		// show the active divisions:
		$div_sql="SELECT distinct d.name, d.uid FROM tmsl_division d inner join tmsl_team_season ts ON d.uid=ts.division_uid
		  inner join tmsl_season s ON ts.season_uid=s.uid
		  WHERE s.stop_date > now() ORDER BY rank";
		$res=mysql_query($div_sql);
		while ($rec=mysql_fetch_assoc($res)) {
			$hot='';
			if ($show==$rec['uid']) {
				$select_flds="SELECT DISTINCT ts.tname as Team,
					ts.colors as Colors,
					CONCAT(fname, ' ', lname) as Name,
					CONCAT('<a href=''mailto:',email,'''>',email,'</a>') as Email,
					phone ";
				$sql_from_where="FROM tmsl_player p
					INNER JOIN tmsl_user u ON p.uid = u.player_uid
					INNER JOIN tmsl_team_manager tm ON u.player_uid=tm.user_uid
					INNER JOIN tmsl_team t ON tm.team_uid=t.uid
					INNER JOIN tmsl_season s ON tm.season_uid=s.uid
					INNER JOIN tmsl_team_season ts ON ts.season_uid=s.uid AND t.uid=ts.team_uid
					WHERE s.division_uid=$show AND s.stop_date > now()
					AND ts.registered=2 ";
				$sql_order="ORDER BY Team, lname";
				$hot='hot';
			}
			print "<span class='tab$hot'><a href='contact.php?show={$rec['uid']}'>{$rec['name']}</a></span>";
		}
		$sql=$select_flds.$sql_from_where.$sql_order;
		$eml_sql="SELECT email $sql_from_where ORDER BY email";
		$res=mysql_query($eml_sql);
		while ($rec=mysql_fetch_assoc($res)) {
			if ($rec['email']) $eml .= $rec['email'].";";
		}
		print "<br><br>";
		print printTable($sql);
		print "<br><a href='mailto:$eml'>Email All</a><br>";
		print "<br/>For technical matters concerning this site, send an email to <a href='mailto:support@tmslregistration.com'>support@tmslregistration.com</a><br/>";
		print "For other TMSL business, send an email to <a href='mailto:admin@tmslregistration.com'>admin@tmslregistration.com</a> or contact one of the board members listed above.";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
