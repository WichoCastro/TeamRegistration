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
  print "<div id='ttlBar'>Create Password</div>";
  print "<div id='mainPar'>";
  if (!$uid) {
    print "<script>window.location='firstTimeUser.php'</script>";
  } else { 
    print "<h3>".getUserName($uid)."</h3>";
    //check uid and p
    if ($p == sha1($uid)) {
      if($e) {
        //if (dbupdate('tmsl_player', array('email'=>urldecode($e)), array('uid'=>$uid))) print "Your email has been updated. <a href='basicInfo.php'>Back to Basic Info</a>";
        print addEmail($uid, urldecode($e));
        print "<br/><a href='basicInfo.php'>Back to Basic Info</a>";
      } 
        print "
          Set your password here. It must be between 4 and 20 characters.<br/><br/><br/>
          <table align='center' class='rtbl'>
          <tr>
          <td>New Password:</td><td><input type='password' id='pwd' name='pwd'></td>
          <tr>
          <td>Retype Password:</td><td><input type='password' id='pwd2' name='pwd2'></td>
          <tr>
          <td colspan='2'><button value='OK' onClick='setPwd($uid)'>OK</button></td>
          </table>
          <br/>
          <input type='hidden' name='uid' value='$uid'>
          ";
      
    }
  } //end we have a player_id  
  print "<div id='msgDiv' style='margin:20px;'></div>";
  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
?>
  <script>
    function setPwd(uid) {
      if($F('pwd') != $F('pwd2')) {
        $('msgDiv').innerHTML='The passwords do not match.';
      } else if($F('pwd').length < 4) {
        $('msgDiv').innerHTML='The password is too short.';
      } else if($F('pwd').length > 20) {
        $('msgDiv').innerHTML='The password is too long.';
      } else {
        var url = 'ajax_create_user.php';
        var params = 'uid=' + uid + '&pwd=' + $F(pwd);
        var myAjax = new Ajax.Request(url, {method: 'post', parameters: params, onComplete:function() {$('msgDiv').innerHTML="Your password has been succesfully set. You can <a href='index.php'>log in</a> with your email address and password.";}});
      }
    }
  </script>
<?
  print "</body>";
  print "</html>";
?>
