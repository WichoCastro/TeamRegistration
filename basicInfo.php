<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
  	$uid = $player_id ? $player_id : $_SESSION['logon_uid'];
  	if (!hasPermissionEditPlayer($_SESSION['mask'], $uid)) header('Location:index.php');
   
  print $beginning;
  print "<div id='ttlBar'>Basic Info</div>";
  print "<div id='mainPar'>";

  print "<table class='rtbl' align='center'>";

  //name
  print "<tr>";
  print "<th>";
  print "Name:";
  print "</th>";
  print "<td>";
  print getUserName($uid);
  print "</td>";
  print "</tr>";

  //email
  print "<tr>";
  print "<th>";
  print "Email:";
  print "</th>";
  print "<td>";
  print getUserEmail($uid);
  print "</td>";
  print "</tr>";

  //addr
  print "<tr>";
  print "<th>";
  print "Address:";
  print "</th>";
  print "<td>";
  print getScalar('uid', $uid, "addr", 'tmsl_player');
  print "</td>";
  print "</tr>";

  //city
  print "<tr>";
  print "<th>";
  print "City:";
  print "</th>";
  print "<td>";
  print getScalar('uid', $uid, "city", 'tmsl_player');
  print "</td>";
  print "</tr>";  

  //state
  print "<tr>";
  print "<th>";
  print "State:";
  print "</th>";
  print "<td>";
  print getScalar('uid', $uid, "state", 'tmsl_player');
  print "</td>";
  print "</tr>";

  //zip
  print "<tr>";
  print "<th>";
  print "Zip:";
  print "</th>";
  print "<td>";
  print getScalar('uid', $uid, "zip", 'tmsl_player');
  print "</td>";
  print "</tr>";  

  //phone
  print "<tr>";
  print "<th>";
  print "Phone:";
  print "</th>";
  print "<td>";
  print getScalar('uid', $uid, "phone", 'tmsl_player');
  print "</td>";
  print "</tr>";  

  //change info
  if ($uid == $_SESSION['logon_uid']) {
    print "<tr>";
    print "<td colspan='2'>";
    print "<a href='chgBasicInfo.php'>Change My Info</a>";
    print "</td>";
    print "</tr>";
  
    //change password
    print "<tr>";
    print "<td colspan='2'>";
    print "<a href='pwdInit.php?uid=$uid&p=".sha1($uid)."'>Change Password</a>";
    print "</td>";
    print "</tr>";
  }
   
  print "<tr><td colspan='2' style='background:white'>&nbsp;</td></tr>";
  print "<tr><td colspan='1'><a href='regStatus.php?player_id=$uid'>&nbsp;</a></td>";
  print "<td colspan='1' style='text-align:right'><a href='regStatus.php?player_id=$uid'>Registration Status -></a></td></tr>";

  print "</table>";
  $p_uid=str_pad($uid, 5, "0", STR_PAD_LEFT);
  if (file_exists("main/$p_uid.jpg")) $img="$p_uid.jpg";
  if (!$img) if (file_exists("main/$p_uid.png")) $img="$p_uid.png";
  if (!$img) $img='image_not_found.jpg';
  print "<a href='main/$img' target='_blank'><img src='main/$img' alt='photo' width='300' border='0'></a><br/>";
  if ($uid == $_SESSION['logon_uid']) print "<a href='uploadPhoto.php'>Upload New Photo</a>";  

  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  print "</body>";
  print "</html>";
  }else include("login.php");
?>
