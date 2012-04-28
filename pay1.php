<?
include_once("sql.inc");
include_once("functions.php");
include_once("tmsl_include.php");
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
if (!$srchCri || $srchCri=='exact') $ck="checked='checked'";
print "<input type='radio' name='srchCri' value='exact' $ck>Exact";
if ($srchCri=='sounds_like') $ck="checked='checked'";else $ck="";
print "<input type='radio' name='srchCri' value='sounds_like' $ck>Sounds Like";
if ($srchCri=='starts_with') $ck="checked='checked'";else $ck="";
print "<input type='radio' name='srchCri' value='starts_with' $ck>Starts With";
if ($srchCri=='contains') $ck="checked='checked'";else $ck="";
print "<input type='radio' name='srchCri' value='contains' $ck>Contains</td></tr>";
print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
print "</table>";
print "</form>";

if ($lname) {
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
				print "<tr><td>";
				if ($addUser)
					print "
						<a href='admin.php?player_id={$rec['uid']}&addUser=1'>
						<img alt='Add' src='images/accept.png' title='Add as System User' border='0'>
					   </a>";
				elseif ($getTeamRep)
					print "<a href='addTeamRep.php?player_id={$rec['uid']}&makePlayerTeamRep=1&team_id=$team_id'>
						 <img alt='Add' src='images/accept.png' title='Make Team Rep' border='0'>
					   </a>";
				elseif ($team_id)
					print "<a href='addPlayerToTeam.php?player_id={$rec['uid']}&team_id=$team_id&season_id=$season_id'>
						<img alt='Add' src='images/accept.png' title='Add To Team' border='0'>
					  </a>";
				else
					print "<a href='editPlayer.php?edit=1&uid={$rec['uid']}'>
						<img alt='Edit' src='images/pencil.png' title='Edit' border='0'>
					  </a>";
				print "</td>";
				print "<td><a href='#' onclick='window.open(\"editPlayer.php?view=1&uid={$rec['uid']}\", \"pWin\", \"width=1000, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\")'>".$rec['lname'].", ".$rec['fname']."</a></td></tr>";
				$noRecs=false;
			}
			if ($noRecs) print "<tr><td>Your search returned no results.</td></tr>";
			print "</table>";
}

print "</body>";
print "</html>";
?>
