<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($sbm) {
			$sql="SELECT count(*) as ct FROM tmsl_team WHERE name='$team_name' AND uid <> $team_id";
			$res=mysql_query($sql);
			$rec=mysql_fetch_array($res);
			if ($rec['ct'] > 0) print "That name ($team_name) belongs to another team.";
			else {
				$sql="UPDATE tmsl_team SET name='$team_name',  colors= '$colors' WHERE uid=$team_id";
				mysql_query($sql) or die(mysql_error());
				$sql="UPDATE tmsl_team_season SET name='$team_name' WHERE stop_date > now() and team_uid=$team_id";
				$res=mysql_query($sql);
				print "<script>window.opener.location.href=\"roster.php?team_id=$team_id&season_id=$season_id\";window.close();</script>";
			}
		}
		$sql="SELECT name, colors FROM tmsl_team where uid=$team_id";
		$res=mysql_query($sql);
		$rec=mysql_fetch_assoc($res);
		$nm=$rec['name'];
		$colors=$rec['colors'];
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		//print $banner;
		//print $navBar;
		//print "<div id='ttlBar'>Edit Team</div>";
		print "<div id='mainPar'>";
		print "<form method='post'>";
		print "<input type='hidden' name='team_id' value='$team_id'>";
		print "<input type='hidden' name='season_id' value='$season_id'>";
		print "<table align='center'>";
		print "<tr><td>Team Name:</td><td><input type='text' name='team_name' value='$nm' style='width:200px'></td></tr>";
		print "<tr><td>Team Colors:</td><td><input type='text' name='colors' value='$colors' style='width:200px'></td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name ='sbm' value='Save'></td></tr>";
		print "</table>";
		print "</form>";
		print "<input type='button' value='Close Window' onclick='window.close();'>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
