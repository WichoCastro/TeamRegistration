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
  print "<div id='ttlBar'>Basic Info</div>";
  print "<div id='mainPar'>";

  $uid = $_SESSION['logon_uid'];

  print "<table class='rtbl' align='center'>";

  //email
  print "<tr>";
  print "<th>";
  print "Old Email:";
  print "</th>";
  print "<td>";
  print getUserEmail($uid);
  print "</td>";
  print "<th>";
  print "New Email:";
  print "</th>";
  print "<td>";
  print "<form><input type='text' id='email'/><button onclick='upd_fld(\"email\")'/>OK</button></form>";
  print "</td>";
  print "</tr>";
  
  print "</table>";
    
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
  var myAjax=new Ajax.Request(url, {method: 'post', parameters: params, onComplete:function(){document.location.href = 'basicInfo.php'} });
}
</script>
<?  
  print "</body>";
  print "</html>";
?>
