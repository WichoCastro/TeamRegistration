<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
  	$uid = $player_id ? $player_id : $_SESSION['logon_uid'];
  	if (!hasPermissionEditPlayer($_SESSION['mask'], $uid)) header('Location:index.php');
  print $beginning;
  print "<div id='ttlBar'>".getUserName($uid)." -- Pay</div>";
  print "<div id='mainPar'>";

  $arr = getRegistrationStatus($uid);
  if (count($arr)) foreach ($arr as $a) {
    if ($a['balance']) {
      $_SESSION['Payment_Amount'] = $a['balance'];
      $_SESSION['team_uid'] = $a['team_uid'];
      $_SESSION['season_uid'] = $a['season_uid'];
    }
    if ($a['pay_pending'])
      $pay_pending = true;
  }


  if ($_SESSION['Payment_Amount']) {
    print "Use one of the buttons below to go to PayPal to pay TMSL $" . $_SESSION['Payment_Amount'];
    print "<form action='expresscheckout.php' METHOD='POST'>
      <input type='hidden' name='player_id' value='$uid'/>
      <input type='image' name='submit' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif' border='0' align='top' alt='Check out with PayPal'/>
      </form>";
    if ($pay_pending) $styl="style='background:#8f8'"; else $styl="";
    print "<input type='button' $styl id='btnPayPromise' value='I will pay at the TMSL office' onclick='payAtOffice($uid, {$_SESSION['team_uid']}, {$_SESSION['season_uid']})'/>"; 
  } else {
    print "You don't owe anything at this time.";
  }

  print "<table class='rtbl' align='center'>";
  print "<tr><td colspan='2' style='background:white'>&nbsp;</td></tr>";
  print "<tr><td colspan='1' style='text-align:left'><a href='regStatus.php?player_id=$uid'><- Registration Status</a></td>";
  print "<td colspan='1' style='text-align:right'><span>Player Card -></span></td></tr>";
  print "</table>";

  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  print "</body>";
  print "</html>";
  }else include("login.php");
?>
