<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
  print "<html>";
  print "<head>";
  print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
  print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
  print "</head>";
  print "<body>";
  print "<div id=container>"; 
  print $banner;
  print $navBar;
  print "<div id='ttlBar'>Change Basic Info</div>";
  print "<div id='mainPar'>";

  $uid = $_SESSION['logon_uid'];

  print "<table class='rtbl' align='center'>";

  print "<form id = 'frmBasicInfo'>";

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
  $email = getUserEmail($uid);
  print "<input type='text' size=60 value='$email' id='e_$uid' onchange='upd_email($uid)' ?>";
  print "</td>";
  print "</tr>";

  $arr = array('addr', 'city', 'state', 'zip', 'phone');
  foreach ($arr as $a) {  
    print "<tr>";
    print "<th>";
    print ucfirst($a) . ":";
    print "</th>";
    print "<td>";
    $data = getScalar('uid', $uid, "$a", 'tmsl_player');
    print "<input type='text' size=60 value='$data' id='$a' onchange='upd_fld(\"$a\")' ?>";
    print "</td>";
    print "</tr>";  
  }
  
  print "<tr>";
  print "<td colspan='2'>";
  print "<a href='basicInfo.php'>back</a>";
  print "</td>";
  print "</tr>";
    
  print "</form>";
   
  print "</table>";

  print "<div id='msg' />";
    
  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  ?>
<script>
function upd_fld(fld) {
  var url='ajax_updt.php';
  var val=$F(fld);
  var params='tbl=tmsl_player&fld='+fld+'&val='+val+'&uid=<?=$uid?>';
  var myAjax=new Ajax.Request(url, {method: 'post', parameters: params});
}
</script>
<?  
  print "</body>";
  print "</html>";
  }else include("login.php");
?>
