<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
  	$uid = $player_id ? $player_id : $_SESSION['logon_uid'];
  	if (!hasPermissionEditPlayer($_SESSION['mask'], $uid)) header('Location:index.php');
  
    print $beginning;
    print "<div id='ttlBar'>Registration Status</div>";
    print "<div id='mainPar'>";

    $arr = getRegistrationStatus($uid);
    $birthdate_val = getScalar('uid', $uid, 'DOB_validated', 'tmsl_player');

    print "<table class='rtbl' align='center'>";

  if (count($arr)) foreach($arr as $a) {

    //name
    print "<tr>";
    print "<th>";
    print "Name:";
    print "</th>";
    print "<td>";
    print getUserName($uid);
    print "</td>";
    print "</tr>";


    //team name
    print "<tr>";
    print "<th>";
    print "Team:";
    print "</th>";
    print "<td>";
    print $a['tname'];
    print "</td>";
    print "</tr>";

    //season name
    print "<tr>";
    print "<th>";
    print "Season:";
    print "</th>";
    print "<td>";
    print getSeasonName($a['season_uid']);
    print "</td>";
    print "</tr>";

    //waiver signed
    print "<tr>";
    print "<th>";
    print "Waiver:";
    print "</th>";
    print "<td>";
    switch ($a['waiver_signed']) {
      case (1): print 'Signed'; break;
      default: print "<a href='waiver.php?team_uid={$a['team_uid']}&season_uid={$a['season_uid']}'>Sign</a>"; 
    }
    print "</td>";
    print "</tr>";

    //dob validated
    print "<tr>";
    print "<th>";
    print "Birthdate Validated:";
    print "</th>";
    print "<td>";
    switch ($birthdate_val) {
      case (1): print 'Yes'; break;
      case (2): print "<a href='uploadId.php?player_id=$uid'>Pending</a>"; break;
      default: print "No. <a href='uploadId.php'>Upload Document</a>";
    }
    print "</td>";
    print "</tr>";

    //balance owed
    print "<tr>";
    print "<th>";
    print "Amount owed:";
    print "</th>";
    print "<td>";
    print "$" . $a['balance'];
    $_SESSION['Payment_Amount'] = $a['balance'];
    if ($a['pay_pending']) print " -- <a href='payBalance.php?player_id=$uid'>pending</a>";
    elseif ($a['balance']) print " -- <a href='payBalance.php?player_id=$uid'>pay</a>";
    print "</td>";
    print "</tr>";
        
    //registration status
    print "<tr>";
    print "<th>";
    print "Registration Status:";
    print "</th>";
    print "<td>";
    switch ($a['registered']) {
      case (2): print 'Registration Complete'; break;
      case (1): print 'Registration Pending'; break;
      default: print 'Not Registered'; break;
    }
    print "</td>";
    print "</tr>";  

    //eligibility (red cards)
    print "<tr>";
    print "<th>";
    print "Eligibility:";
    print "</th>";
    print "<td>";
    print isCurrentlySuspended($uid);
    print "</td>";
    print "</tr>";  
  } else print "<tr><td colspan='2' style='background:white'>You're not registered on a team.</td></tr>";
     
  print "<tr><td colspan='2' style='background:white'>&nbsp;</td></tr>";
  print "<tr><td colspan='1' style='text-align:left'><a href='basicInfo.php?player_id=$uid'><- Basic Info</a></td>";
  print "<td colspan='1' style='text-align:right'><a href='payBalance.php?player_id=$uid'>Payments -></a></td></tr>";

  print "</table>";
    
  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  print "</body>";
  print "</html>";
  }else include("login.php");
?>
