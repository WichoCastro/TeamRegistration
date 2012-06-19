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
  print "<div id='ttlBar'>Create Account</div>";
  print "<div id='mainPar'>";
  if (!$player_id) {
  print "<h3>Who are you?</h3>";
  print "<div class='dashBox'>";
  print "<h4>Select a season and a team to let us know who you are:</h4>";    
  print "<form>";
  print "Season: " . getSeasonDropdown(date('Y-m-d', 1284267600), $season_id);
  if ($season_id) print "<br/>Team: " . getTeamDropdown($season_id, $team_id);
  if ($team_id) print "<br/>Player: " . getPlayerDropdown($season_id, $team_id, $player_id);
  print "</div >";
  print "<h4>--or--</h4>";
  print "<div class='dashBox'>";
  print "Search for your name:<br/>";
  print "<table border='1' align='center'>";
  print "<tr><td>Last Name:</td><td><input type='text' name='lname' value='$lname'></td></tr>";
  print "<tr><td>First Name:</td><td><input type='text' name='fname' value='$fname'></td></tr>";
  print "<tr><td colspan='2'>";
  print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='Search' class='pointer'></td></tr>";
  if ($srch) {
    $whrArr = array();
    if ($lname) $whrArr[] = "lname LIKE '$lname'"; 
    if ($fname) $whrArr[] = "fname LIKE '$fname'"; 
    $whr = implode(' AND ', $whrArr);
    $sql = "SELECT uid, CONCAT(lname, ', ', fname) as p_name FROM tmsl_player WHERE $whr";
    $res = mysql_query($sql);
    if (!mysql_num_rows($res)) {
       $whrArr = array();
      if ($lname) $whrArr[] = "SOUNDEX(lname) = SOUNDEX('$lname')"; 
      if ($fname) $whrArr[] = "SOUNDEX(fname) = SOUNDEX('$fname')"; 
      $whr = implode(' AND ', $whrArr);
      $sql = "SELECT uid, CONCAT(lname, ', ', fname) as p_name FROM tmsl_player WHERE $whr";
      $res = mysql_query($sql);
    }
    
    while ($rec = mysql_fetch_array($res)) {
      $uid=$rec['uid']; 
      print "<tr><td><a href='{$_SERVER['PHP_SELF']}?player_id=$uid'>{$rec['p_name']}</a></td><td>";
      $sql_h = "SELECT c.tname, c.team_uid, c.start_date, c.season_uid, a.stop_date, 0 as dropped from tmsl_player_team a
        INNER JOIN tmsl_team b ON a.team_uid=b.uid
        INNER JOIN tmsl_team_season c ON c.team_uid=b.uid AND c.season_uid=a.season_uid
        WHERE a.player_uid=$uid and a.registered=2 AND c.registered=2
        UNION
        SELECT c.tname, c.team_uid, c.start_date, c.season_uid, a.drop_date as stop_date, 1 as dropped from tmsl_dropped a
        INNER JOIN tmsl_team b ON a.team_uid=b.uid
        INNER JOIN tmsl_team_season c ON c.team_uid=b.uid AND c.season_uid=a.season_uid
        WHERE a.player_uid=$uid and a.registered=2 AND c.registered=2
        ORDER BY start_date DESC, tname";
      $res_h=mysql_query($sql_h) or die("$sql_h --".mysql_error());
      while ($rec_h=mysql_fetch_assoc($res_h)) {
        $season=getSeasonName($rec_h['season_uid']);
        print $rec_h['tname']." -- $season";
        if ($rec_h['dropped']) print " (dropped ".$rec_h['stop_date'].")";  
        print "<br/>";
      }
      print "</td></tr>";
    }
  }  
  print "</table>";
  print "</div >";
  print "</form>";
  } else { //we have a player_id
    $pwd = getScalar('player_uid', $player_id, 'pwd', 'tmsl_user');
    if ($pwd) { //he can already log in
      print "You are already registered with our system. Go to the <a href='pReg.php'>player registration page</a>.";
    } elseif($p) { //he clicked the email link 
      print "Your username will be the email address you registered with. Set your password here.";
      print "<form method='POST'>
        Password: <input type='password' name='pwd'>
        Retype Password: <input type='password' name='pwd'>
        <input type='submit' value='ok'>
        </form>";
    } else { //he has not yet got the email and is not a user in the system
    print "<h3>".getUserName($player_id)."</h3>";
    //check db to see if has an email on file:
    $existing_email = getScalar('uid', $player_id, 'email', 'tmsl_player');
    //if no email on file:
    if (!$existing_email) {
      print "<br/>In order to register online, you must have an email address. Please enter a current email address. A confirmation email will be sent with further instructions.";
      print "<br/><br/><form><input type='hidden' name='player_id' value='$player_id'>Email: <input name='email' size='40'><input type='submit' value='ok'></form>";
      if ($email) {
          print "An email has been sent to: $email";
        $link="http://tmslregistration.com/pwdInit.php?u1=$username&s=$sha1&u=".sha1(strtolower($username));
        $body="Click this link to create your TMSL account: $link";
        $body.= "  You will be asked to create a password and then electronically sign some forms.";
        mail($email, 'TMSL account', $body, 'FROM:noreply@tmslregistration.com');
      }
    } else { //email exists
      print "<br/>This is the email we have on file for you. If you wish to change it, use the space below.";
      print "<br/>Email: $existing_email";
      print "<br/><form>New Email: <input name='email' size='40'><input type='submit' value='ok'></form>";
      print "<a href='pwdInit.php?p=$player_id'>Proceed to login page</a>.";
    }          
  } //end else (has no log in)
  } //end we have a player_id  

  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  print "</body>";
  print "</html>";
?>
