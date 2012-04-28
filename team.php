<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($clearTeam) $_SESSION['team_uid']=0;
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Teams</div>";
		print "<div id='mainPar'>";
		print "<form method='get'>";
		print "<table border='1' align='center'>";

		print "<tr><td>Team Name:</td><td><input type='text' name='tname' value='$tname'></td></tr>";
		print "<tr><td colspan='2'>";
		if ($srchCri=='exact') $ck="checked='checked'";
		print "<input type='radio' name='srchCri' value='exact' $ck>Exact";
		if (!$srchCri || $srchCri=='starts_with') $ck="checked='checked'";else $ck="";
		print "<input type='radio' name='srchCri' value='starts_with' $ck>Starts With";
		if ($srchCri=='contains') $ck="checked='checked'";else $ck="";
		print "<input type='radio' name='srchCri' value='contains' $ck>Contains</td></tr>";
		print "</select></td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
		print "</table>";
		print "</form>";

		if ($srch) {
			if ($srchCri=='starts_with') {$tname.="%";}
			if ($srchCri=='contains') {$tname="%$tname%";}
			$tname=strtoupper($tname);
			$whereClauseArr=array();
			if ($tname) $whereClauseArr[]="tname like '$tname'";
			$whereClause = implode(" AND ", $whereClauseArr);
			$sql="SELECT tname, team_uid, season_uid FROM tmsl_team_season ";
			if ($whereClause) $sql .= " WHERE $whereClause ";
			$sql .= " ORDER BY tname";
			$res=mysql_query($sql);
			$noRecs=true;
			print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
			while ($rec=mysql_fetch_assoc($res)) {
				$season_nm=getSeasonName($rec['season_uid']);
				print "<tr><td>";
				print "<a href='roster.php?team_id={$rec['team_uid']}&season_id={$rec['season_uid']}'>
						<img alt='Edit' src='images/group.png' title='Roster' border='0'>
					  </a></td>";
				print "<td>".$rec['tname']." -- $season_nm</td></tr>";
				$noRecs=false;
			}
			if ($noRecs) print "<tr><td>Your search returned no results.</td></tr>";
			print "</table>";
		}

		print "</div>";

		print "</body>";
		print "</html>";
	}else include("login.php");
?>
