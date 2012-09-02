<?
  include_once("session.php");
  print "<html>";
  print "<head>";
  print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
  print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
  print "</head>";
  print "<body>";
  print "<div id=container>"; 
  print $banner;
  print $navBar;
  print "<div id='ttlBar'>Player Card</div>";
  print "<div id='mainPar'>";

  $uid = $_SESSION['logon_uid'];

  print "<div class='playerCard'>";
  print "Name: ";
  print "</div>";

  print "<table class='rtbl' align='center'>";
  print "<tr><td colspan='2' style='background:white'>&nbsp;</td></tr>";
  print "<tr><td colspan='2'><a href='index.php'>Home</a></td>";
  print "</table>";


  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  print "</body>";
  print "</html>";
?>
