<?
include_once("sql.inc");
include_once("functions.php");
include_once("tmsl_include.php");
session_start();
foreach ($_POST as $key=>$val) $$key=$val;
foreach ($_GET as $key=>$val) $$key=$val;
print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
print "<html>";
print "<head>";
print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
print "<script>var cal = new CalendarPopup('testdiv1');</script>";
print "</head>";
print "<body>";		
print $banner;
$navBar = 	"<div id='navBar'>
					<table align='center'>
						<tr>
							<td><a href='index.php'>Login to TMSL Registration</a></td>
						</tr>
						</table></div>";
print $navBar;
print "<div id='ttlBar'>Pay</div>";

print "<div id='mainPar'>";
print "Welcome! Here you can pay your TMSL fees. Please enter your name";

print "<form method='get'>";
print "<table border='1' align='center'>";

print "<tr><td>Last Name:</td><td><input type='text' name='lname' value='$lname'></td></tr>";
print "<tr><td>First Name:</td><td><input type='text' name='fname' value='$fname'>";
print "<tr><td colspan='2'>";
if ($srchCri=='exact') $ck="checked='checked'";
print "<input type='radio' name='srchCri' value='exact' $ck>Exact";
if ($srchCri=='sounds_like') $ck="checked='checked'";else $ck="";
print "<input type='radio' name='srchCri' value='sounds_like' $ck>Sounds Like";
if (!$srchCri || $srchCri=='starts_with') $ck="checked='checked'";else $ck="";
print "<input type='radio' name='srchCri' value='starts_with' $ck>Starts With";
if ($srchCri=='contains') $ck="checked='checked'";else $ck="";
print "<input type='radio' name='srchCri' value='contains' $ck>Contains</td></tr>";
print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
print "</table>";
print "</form>";

if ($lname || $fname) {
			if ($srchCri=='starts_with') {$fname.="%";$lname.="%";}
			if ($srchCri=='contains') {$fname="%$fname%";$lname="%$lname%";}
			$fname=strtoupper($fname);
			$lname=strtoupper($lname);
			$whereClauseArr=array();
			if ($fname)
				if ($srchCri=='sounds_like') $whereClauseArr[]="SOUNDEX(fname) = SOUNDEX('$fname')";
				else $whereClauseArr[]="UPPER(fname) LIKE '$fname'";
			if ($lname)
				if ($srchCri=='sounds_like') $whereClauseArr[]="SOUNDEX(lname) = SOUNDEX('$lname')";
				else $whereClauseArr[]="UPPER(lname) LIKE '$lname'";
			$whereClause = implode(" AND ", $whereClauseArr);
			$sql="SELECT p.* FROM tmsl_player p ";
			if ($whereClause) $sql .= " WHERE $whereClause ";
			$sql .= " ORDER BY lname, fname";
			$res=mysql_query($sql);
			$noRecs=true;

			print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
			while ($rec=mysql_fetch_assoc($res)) {
				print "<tr>";

				print "<td><a href='{$_SERVER['PHP_SELF']}?uid={$rec['uid']}'>{$rec['lname']}, {$rec['fname']}</a></td></tr>";
				$noRecs=false;
			}
			if ($noRecs) print "<tr><td>Your search returned no results.</td></tr>";
			print "</table>";
}

if ($uid) {
	if ($team_uid) {
		$_SESSION['pay_these_uid'][] = array($uid, $team_uid, $season_uid, $amt); 
		print "<script>window.opener.location.href=window.opener.location; window.close();</script>";
	}
	$sql="SELECT team_uid, season_uid, registered, balance as amt FROM tmsl_player_team pt JOIN tmsl_season s ON pt.season_uid=s.uid WHERE player_uid=$uid AND s.stop_date>now()";
	$arr = dbSelectSQL($sql);
	if (empty($arr)) print "Player is not on a team.";
	else {
		print "<h3>Current Teams for ".getPlayerName($uid)."</h3>";
		foreach ($arr as $rec) {
			//if ($rec['registered']<2) print "click to pay for registration on <a href='{$_SERVER['PHP_SELF']}?uid=$uid&team_uid={$rec['team_uid']}&season_uid={$rec['season_uid']}'>".getTeamName($rec['team_uid'], $rec['season_uid'], true)."</a><br/>";
			if ($rec['registered']<2) print "
				<form>
				<input type='hidden' name='uid' value=$uid>
				<input type='hidden' name='team_uid' value={$rec['team_uid']}>
				<input type='hidden' name='season_uid' value={$rec['season_uid']}>" .
				getTeamName($rec['team_uid'], $rec['season_uid'], true) .
				" amount due: $<input type='text' style='text-align:right; width:50px' name='amt' value='{$rec['amt']}'>
				<input type='submit' value = 'pay'>";
				
			else print "already paid for ".getTeamName($rec['team_uid'], $rec['season_uid'], true)."<br/>";
		}
	}
}

if ($clr) {
	$_SESSION['pay_these_uid'] = array(); 
	print "<script>window.opener.location.href=window.opener.location; window.close();</script>";
}

print "</body>";
print "</html>";
?>
