<?
session_start();
include_once("sql.inc");
include_once("functions.php");
include_once("tmsl_include.php");
foreach ($_POST as $key=>$val) $$key=$val;
foreach ($_GET as $key=>$val) $$key=$val;

require_once ("paypalfunctions.php");


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
print "Welcome! Here you can pay your TMSL fees. Please add the names of the players you wish to pay for";

print "<form method='get'>";
print "<table border='1' align='center'>";
print "<tr><th>Player Name</th><th>Team</th><th>Amount</th></tr>";
if (!empty($_SESSION['pay_these_uid'])) {
	foreach ($_SESSION['pay_these_uid'] as $arr) {
	 $pay_uid=$arr[0];
	 $team_uid=$arr[1];
	 $season_uid=$arr[2];
	 $nm = getUserName($pay_uid);
	 if (!$nm) {print "<tr><td align='center' colspan=4>User #$pay_uid not found</td></tr>";continue;}
	 $tm = getTeamName($team_uid, $season_uid, true);
	 $amt=$arr[3];
	 print "<tr><td>$nm</td><td>$tm</td><td align='right'>$amt</td></tr>";
	 $ttl += $amt;
	}
	print "<tr><td colspan='2'>Total Due:</td><td align='right'>$ttl</td></tr>";
	$_SESSION["Payment_Amount"] = $ttl;
}
print "<tr><td align='center' colspan = 4><input type='button' value='Click here to add name' onclick='window.open(\"pay_pop_up.php\", \"pWin\", \"width=1000, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\");'><input type='button' value='Clear Form' onclick='window.open(\"pay_pop_up.php?clr=1\", \"pWin\", \"width=1000, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\");'></form><form action='expresscheckout.php' METHOD='POST'>
<input type='image' name='submit' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif' border='0' align='top' alt='Check out with PayPal'/>
</form></td></tr>";
print "</table>";
//print "</form>";

print "</body>";
print "</html>";
?>
