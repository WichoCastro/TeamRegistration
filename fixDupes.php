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
		print "<div id='ttlBar'>Fix Dupes</div>";
		print "<div id='mainPar'>";
		$sql="SELECT a.uid, b.uid as b_uid, a.fname, a.lname FROM tmsl_player a JOIN tmsl_player b ON a.fname=b.fname and a.lname=b.lname and a.uid < b.uid";
		$res=mysql_query($sql) or die("$sql --".mysql_error());
		while ($rec=mysql_fetch_assoc($res)) {
		  print "<a href='editPlayer.php?edit=1&uid=";
		  print $rec['uid'];
		  print "' target='_blank'> ";
		  print $rec['uid'];
		  print "</a> ";
		  print "<a href='editPlayer.php?del=1&uid={$rec['uid']}'>X</a>  ";
		  print "<a href='editPlayer.php?edit=1&uid=";
		  print $rec['b_uid'];
		  print "' target='_blank'> ";
		  print $rec['b_uid'];
		  print "</a> ";
		  print "<a href='editPlayer.php?del=1&uid={$rec['b_uid']}'>X</a>  ";
		  print $rec['fname'];
		  print " ";
		  print $rec['lname'];
		  $sq="SELECT player_uid  FROM tmsl_suspended where player_uid IN ({$rec['uid']}, {$rec['b_uid']})";
		  $rs=mysql_query($sq);
		  $rc=mysql_fetch_assoc($rs);
		  if ($rc['player_uid']) print " s=".$rc['player_uid'];
		  $sq="SELECT player_uid, team_uid, season_uid  FROM tmsl_player_team where player_uid IN ({$rec['uid']}, {$rec['b_uid']})";
		  $rs=mysql_query($sq);
		  while ($rc=mysql_fetch_assoc($rs))
		    if ($rc['player_uid']) print " tm=<a href='roster.php?team_id={$rc['team_uid']}&season_id={$rc['season_uid']}'>".$rc['player_uid']."</a>";
		  print "<br/>";
		}
		print "This page is for fixing Dupes";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
