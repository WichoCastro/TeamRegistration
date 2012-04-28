<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!$start_date) $start_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')-7,date('Y')));
		if (!$stop_date) $stop_date=date('m/d/Y');
		$start_date_sql=date('Y-m-d', strtotime($start_date));
		$stop_date_sql=date('Y-m-d', strtotime($stop_date));
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "</head>";
		print "<body>";
		echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Report: Cards</div>";

		print "<div id='mainPar'>";
		print "<form name='frm' id='frm' method='get'>";
		print "<table border='1' align='center'>";
		print "<tr><td>From:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		print "<tr><td>To:</td><td><input type='text' value='$stop_date' name='stop_date' id='stop_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].stop_date,\"anchor2\",\"MM/dd/yyyy\"); return false;' NAME='anchor2' ID='anchor2'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		$arrTeams=buildSimpleSQLArr("uid", "name", "SELECT t.uid, ts.tname as name FROM tmsl_team t
			INNER JOIN tmsl_team_season ts ON t.uid=ts.team_uid
			INNER JOIN tmsl_season s ON ts.season_uid=s.uid
			WHERE s.stop_date > now() AND ts.registered=2 ORDER BY tname");
		if (!$_SESSION['editTeams']) $sql.=" AND t.uid IN (".implode("", $myTeams).")";
		print "<tr><td>Team:</td><td>";
		print getSelect("team_id", $arrTeams, array(0=>"--Any--"), $team_id, "");
		print "</td></tr>";
		print "<tr><td>Card:</td><td>";
		print getSelect("card_type", array('R'=>'Red', 'Y'=>'Yellow'), array(0=>"--Any--"), $card_type, "");
		print "</td></tr>";
		if ($gp) $ck="checked='checked'";
		print "<tr><td colspan='2' align='center'><input type='checkbox' name='gp' value='1' $ck> Show Number of Cards</td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
		print "</table>";
		print "</form>";
		print "</table>";

		if ($team_id) $tmClause = " AND ts.team_uid=$team_id ";
		if ($card_type) $ctClause = " AND c.card_type='$card_type' ";

		$sql = "SELECT CONCAT('<img src=''images/report.png'' onclick=''window.open(\"game_report.php?uid=', g.uid,
								'\", \"game_report_win\", \"height=1000; width=1200, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\");''
								border=''0'' alt=''game report'' title=''game report''>') AS game_report,
						CONCAT('<a href=''editPlayer.php?edit=1&uid=',p.uid,'''>',lname,', ',fname,'</a>') AS player_name,
						CONCAT('<a href=''roster.php?team_id=',pt.team_uid,'&season_id=',pt.season_uid,'''>',tname,'</a>') AS team_name,
						case card_type when 'R' then
							CONCAT('<span class=''pointer'' style=''background-color:red'' onclick=''window.open(\"misconductReport.php?uid=', c.uid,
								'\", \"misconductWin\", \"width=800, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\");''>R</span>')
							else 'Y' end as card_type,
							game_dt as game_date
						FROM tmsl_card c INNER JOIN tmsl_player p ON c.player_uid=p.uid
						  INNER JOIN tmsl_game g ON c.game_uid=g.uid
						  INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid AND pt.season_uid=g.season_uid
						  INNER JOIN tmsl_team_season ts ON g.season_uid=ts.season_uid AND ts.team_uid=pt.team_uid
						WHERE (g.team_h = ts.team_uid OR g.team_v = ts.team_uid) AND g.game_dt >= '$start_date_sql' AND game_dt <= '$stop_date_sql'
						$tmClause $ctClause
						ORDER BY game_dt DESC, team_name, player_name";

		if ($gp)
			$sql = "SELECT CONCAT('<a href=''editPlayer.php?edit=1&uid=',p.uid,'''>',lname,', ',fname,'</a>') AS player_name,
						CONCAT('<a href=''roster.php?team_id=',pt.team_uid,'&season_id=',pt.season_uid,'''>',tname,'</a>') AS team_name,
						 card_type , count(*) as number
						FROM tmsl_card c INNER JOIN tmsl_player p ON c.player_uid=p.uid
						  INNER JOIN tmsl_game g ON c.game_uid=g.uid
						  INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid AND pt.season_uid=g.season_uid
						  INNER JOIN tmsl_team_season ts ON g.season_uid=ts.season_uid AND ts.team_uid=pt.team_uid
						WHERE (g.team_h = ts.team_uid OR g.team_v = ts.team_uid) AND g.game_dt >= '$start_date_sql' AND game_dt <= '$stop_date_sql'
						$tmClause $ctClause
						GROUP BY card_type, CONCAT(lname,', ',fname) , tname
						ORDER BY card_type, count(*) DESC, team_name, player_name";

		print printTable($sql);

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
