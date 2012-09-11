<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {

    //FIX handle these lines in fcn
    if ($season_id) {
      $_SESSION['season_uid']=$season_id;
      dbUpdate('tmsl_user', array('last_season_uid'=>$season_id), array(player_uid=>$_SESSION['logon_uid']));
    } else 
      $season_id=$_SESSION['season_uid'];
    if ($team_id) {
      $_SESSION['team_uid']=$team_id;
      dbUpdate('tmsl_user', array('last_team_uid'=>$team_id), array(player_uid=>$_SESSION['logon_uid']));
    } else {
      $team_id = $_SESSION['team_uid'];
    }  

    //FIX the following shit. Should not be necessary if define SESS['szuid']
    if (!$season_id) {
      $season_id_arr=dbSelect('tmsl_team_season', array('season_uid'), array('team_uid'=>$team_id), array('season_uid desc'));
      $season_id=$season_id_arr[0]['season_uid'];
    }

    $season = new Season($season_id);
    $person = array();

    //FIX when ready
    if (hasPermission($_SESSION['mask'], $team_id, $season_id)) $mgr=true;else $mgr=false;
    if ($_SESSION['mask'] & 4) $adm=true;else $adm=false;

    print $beginning;
    print "<div id='ttlBar'>Team Roster <span onclick='help_win()'><img src='images/q.jpg' alt='Help' title='Help' border='0'></span></div>";
    print "<div id='mainPar'>";

    //season and team dropdowns
    print "<form method='get' name='frmSzn' id='frmSznTm'>";
    print "Season: ";
    print getSeasonDropdown (0, $season_id, "seasonChanged()");
    print "<br/>Team: ";
    print getTeamDropdown ($season_id, $team_id);
    print "</form>";

    if ($team_id) {

        $team = new Team($team_id, $season_id);

        foreach ($team->players as $p) 
          $person[$p] = new Person($p, $team_id, $season_id); 

        print "<div id='teamInfoBox'>";
        $prev_season_id = $team->getNextSeason(-1);
        if ($prev_season_id) 
          print "<a href='roster.php?team_id=$team_id&season_id=$prev_season_id'><img alt='Edit' src='images/arrow_left.png' title='Previous Season' border='0'></a>";
        print "<span style='font-size:18pt'>" . $team->name . "</span>";
        $next_season_id = $team->getNextSeason(1);
        if ($next_season_id) 
          print "<a href='roster.php?team_id=$team_id&season_id=$next_season_id'><img alt='Edit' src='images/arrow_right.png' title='Next Season' border='0'></a>";
        if ($mgr) print "<br><span style='font-size:16pt'>Colors: 
          <span id='sp_colrz'><input id='colrz' onchange='updt_colors()' onclick='select();' value='{$team->colors}' size=50></span></span>";

        print "<br/>Team Reps:<br/>";
        foreach($team->reps as $rep_id) {
          print $person[$rep_id]->fullName . "<br/>";
        }

        //FIX -- mgr doesn't cut it, ese
        if($mgr) print "<a href='mgTeamReps.php?team_id=$team_id&season_id=$season_id'>Manage Team Reps</a>";
        
        print "</div><br/>"; //end teamInfoBox

        $min_players=$season->minPlayers;
        $max_players=$season->maxPlayers;
        $last_day_team=$season->lastDateTeam;
        $last_day_player = $season->lastDatePlayer;
        $registered = $team->registered;
        $notes = $team->notes;
        $numOnRoster = $team->numPlayers;

        //FIX 
        if ($registered==2) print "This team is registered with " . $team->numPlayers . " registered players.<br/>";
        elseif ($registered==1) {
          print "Registration has been submitted -- awaiting confirmation.<br/>";
          if (hasRegistrationAuthority($_SESSION['mask']))
            print "<br/><input type='button' class='majAction' value='ACCEPT REGISTRATION' onClick='window.location=\"acceptRegistration.php?team_id=$team_id&season_id=$season_id\"'><br/>";
        }
        elseif ($registered==3) {
          print "Registration has been cancelled.<br/>";
          if (hasRegistrationAuthority($_SESSION['mask']))
            print "<br/><input type='button' class='majAction' value='ACCEPT REGISTRATION' onClick='window.location=\"acceptRegistration.php?team_id=$team_id&season_id=$season_id\"'><br/>";
        }
        else { // 0 is the only other state. The team has not submitted registration yet.
          if ($mgr) {
            if ($notes) print "<span id='updateMsg'>$notes</span><br/>";
            //if team has not registered and criteria are met (date and number of players), show register button
            if (time() <= strtotime($last_day_team) + 1440*60 ) {
              print "The last day to register a team is $last_day_team.<br/>";
              if ($numOnRoster >= $min_players) {
                if ($numOnRoster <= $max_players) {
                  print "<input type='button' value='REGISTER' onClick='window.location=\"registerTeam.php?team_id=$team_id&season_id=$season_id\"' class='majAction'><br/>";
                  print "$numOnRoster on the roster<br/>";
                }else{
                  print "You have $numOnRoster on the roster -- you can't register more than $max_players.<br/>";
                }
              }else{
                print "You have $numOnRoster on the roster -- you need at least $min_players to register.<br/>";
              }
            }else{
              print "The last chance to register teams was $last_day_team.<br/>";
            }
          }
        }
        if ($mgr) {
          if (time() <= strtotime($last_day_player) + 1440*60 )
            print "The last day to register a player is $last_day_player.<br/>";
          else
            print "The last day to register a player was $last_day_player.<br/>";
        }

        print "<div id='msg'></div>";

        if (!empty($person)) { //player table start
          print "<table class='rtbl' border='1' align='center'>";
          print "<tr>";
          if ($mgr) print "<th></th><th>Name</th><th>Email</th><th>Jersey</th></tr>";
          else print "<th>Name</th></tr>";
          foreach ($person as $player) {
            switch($player->registered) {
              case(1): $cls = "style='background-color:#ccf'"; break;
              case(2): $cls = "style='background-color:#eef'"; break;
              case(3): $cls = "style='background-color:#faa'"; break;
              default: $cls = "";   
            }
            if ($player->suspended) $cls = "style='background-color:#ff4'"; 
            print "<tr $cls>";

            if ($mgr) { //column one has all the icons
              print "<td><table class='stbl'><tr $cls>
                <td>
                  <a href='editPlayer.php?edit=1&uid={$player->id}&team_id=$team_id&season_id=$season_id'>
                  <img alt='Edit' src='images/pencil.png' title='Edit' border='0'>
                  </a>
                </td>
                <td>
                  <a href='editPlayer.php?drop=1&uid={$player->id}&team_id=$team_id&season_id=$season_id'>
                  <img alt='Drop' src='images/delete.png' title='Drop' border='0'>
                  </a>
                </td>";
              if ($adm && $registered) {
                if ($player->suspended)
                  print "
                    <td>
                      <a href='editPlayer.php?suspend=1&uid={$player->id}&team_id=$team_id&season_id=$season_id'>
                      <img alt='Suspend' src='images/vcard_delete.png' title='Suspend' border='0'>
                      </a>
                    </td>";
                  else {
                  $susp_id=getScalar('','','','',"SELECT uid FROM tmsl_suspended WHERE player_uid={$player->id} ORDER BY start_date DESC LIMIT 1");
                  print "
                    <td>
                      <a href='reportSuspended.php?edit=1&s_uid=$susp_id'><img alt='Suspend' src='images/vcard_delete.png' title='Suspend' border='0'></a>
                    </td>";
                  }
                if ($player->waiverSigned)
                  print "
                    <td>
                      <span id='waiver_{$player->id}' onclick='unSignWaiver({$player->id});'>
                      <img alt='Waiver Signed' src='images/waiver_signed.png' title='Waiver Signed' border='0'>
                      </span>
                    </td>";
                else
                  print "
                    <td>
                      <span id='waiver_{$player->id}' onclick='signWaiver({$player->id});'>
                      <img alt='Waiver Not Signed' src='images/waiver_unsigned.png' title='Waiver Not Signed; Click to Mark As Signed.' border='0'>
                      </span>
                    </td>";
                if ($player->balance)
                  print "
                    <td>
                      <span id='balance_{$player->id}' 
                       onclick='window.location=\"acceptPlayerRegistration.php?uid={$player->id}&team_id=$team_id&season_id=$season_id\";'>
                      <img alt='Waiver Signed' src='images/paid_no.png' title='Click to mark as paid' border='0'>
                      </span>
                    </td>";
                else
                  print "
                    <td>
                      <span id='balance_{$player->id}' 
                       onclick='window.location=\"acceptPlayerRegistration.php?uid={$player->id}&team_id=$team_id&season_id=$season_id\";'>
                      <img alt='Waiver Signed' src='images/paid_yes.png' title='Click to mark as NOT paid' border='0'>
                      </span>
                    </td>";    
                if ($player->registered != 2)
                  print "
                    <td>
                      <a href='acceptPlayerRegistration.php?register=2&uid={$player->id}&team_id=$team_id&season_id=$season_id'>
                      <img alt='Accept Registration' src='images/accept.png' title='Accept Registration' border='0'>
                      </a>
                    </td>";
                else  print "
                    <td>
                      <a href='acceptPlayerRegistration.php?register=1&uid={$player->id}&team_id=$team_id&season_id=$season_id'>
                      <img alt='Mark as Not Registered' src='images/cancel.png' title='Mark as Not Registered' border='0'>
                      </a>
                    </td>";
              }
              if ($mgr && !($player->pic))
                print "
                  <td>
                    <a href='uploadPhoto.php?player_id={$player->id}'>
                    <img alt='Camera' src='images/camera.png' title='Please Upload Photo' border='0'>
                    </a>
                  </td>";
              if ($mgr && !($player->dobValidated == 1)) {
                print "<td>";
                if ($adm) print "<a href='validate_dob.php?player_id={$player->id}'>";
				elseif($mgr) print "<a href='uploadId.php?player_id={$player->id}'>";
                switch($player->dobValidated) {
                  case(0):
                    print "<img alt='Red Flag' src='images/flag_red.png' title='Age Must Be Validated' border='0'>";
                    break;
                  case(2):
                    print "<img alt='Red Flag' src='images/flag_yellow.png' title='Age Validation Pending' border='0'>";
                }
                if ($adm || $mgr) print "</a>";
                print "</td>";
              }
              if ($adm && $player->notes)
                print "
                  <td>
                    <img alt='Notes' src='images/comment.png' title=\"{$playerDetails['Notes']}\" border='0'>
                  </td>";
              if ($adm)
                print "
                  <td>
                    <a href='transferPlayer.php?playerid={$player->id}&fromTeam=$team_id&fromSeason=$season_id'>
                    <img alt='Transfer' src='images/arrow_right.png' title=\"Transfer Player\" border='0'>
                    </a>
                  </td>";

              //FIX -- use Card Object
              print "<td>";
              if (!empty($arr_cards))
                foreach ($arr_cards as $crd) {
                  $mousover="";$onclk="";$stylz="";
                  if ($crd['card_type']=='Y') {
                    $colr='#ff0';
                    if ($adm) $mousover="title='{$crd['game_dt']}'";
                  } else {
                    $colr='#f00';
                    if ($adm) {
                      $mousover="title='{$crd['game_dt']} (Click to see report)'";
                      $onclk="onclick='window.open(\"misconductReport.php?uid={$crd['card_uid']}\", \"misconductWin\", \"width=800, height=1000, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes\")'";
                      $stylz="cursor:pointer;";
                    }
                  }
                  print "<span style='background-color:$colr; border: 1px solid black; $stylz' $mousover $onclk>&nbsp;&nbsp;&nbsp;&nbsp;</span> ";
                }
              print "</td>";

              print "</tr></table></td>";
            } // end column one

            //Name, Email, Jersey
            print "<td>{$player->fullName}</td>";
            if ($mgr) print "<td>{$player->email}</td>";
            if ($mgr) print "<td>{$player->jersey}</td>";

            print "</tr>";
          } //end foreach player
          print "</table>";
        }else{
          print "There are no players on this team.<br/>";
        } //end player table
        
        //legend
        print "<table align='center' class='rtbl'><tr><th colspan='3'>Legend</th></tr>
          <tr><td>Registered</td><td>Registeration Pending</td><td>Suspended</td></tr>
          <tr><td><div style='background:#eef; border: 1px solid black;'>&nbsp;</div></td>
          <td><div style='background:#ccf; border: 1px solid black;'>&nbsp;</div></td>
              <td><div style='background:#ff4; border: 1px solid black;'>&nbsp;</div></td></tr>
          </table>";

        if ($mgr) {
          print "<table align='center'><tr>";

          if (time() <= strtotime($last_day_player) + 1440*60 ) {
            print "<td><form method='get' action='player.php'>
                  <input type='hidden' name='team_id' value='".$team_id."'>
                  <input type='hidden' name='season_id' value='".$season_id."'>
                  <input type='hidden' name='add' value='1'>
                  <input type='submit' class='smallform' value='Add player'>
                </form></td>";
          }
          if ($adm) {
            print "<td><form method='get' action='dropTeamFromSeason.php'>
                  <input type='hidden' name='team_id[]' value='".$team_id."'>
                  <input type='hidden' name='season_id' value='".$season_id."'>
                  <input type='submit' value='Drop Team'>
                </form></td>";
          }
          print "<td><form method='get' action='rollover.php'>
                <input type='hidden' name='team_id[]' value='".$team_id."'>
                <input type='hidden' name='season_id' value='".$season_id."'>
                <input type='submit' value='Copy to Next Season'>
              </form></td>";
          if (!$season->seasonOver) print "<td><form>
                <input type='button' value='Game Card'
                onclick='window.location=\"games.php?season_id=$season_id&team_id=$team_id\"'>
              </form></td>";
          if ($adm) {
            print "<td><form method='get' action='changeTeamName.php'>
                  <input type='hidden' name='team_id' value='".$team_id."'>
                  <input type='hidden' name='season_id' value='".$season_id."'>
                  <input type='submit' value='Change Name'>
                </form></td>";
          }
          print "</tr></table>";
        }
    print "<input type='button' value='Search Teams' onclick='window.location=\"findTeam.php\"'>";
    if ($mgr) print "<input type='button' value='View Balance' onClick='window.location=\"invoice.php?team_id=$team_id&season_id=$season_id\"' class='pointer'>";
    }
    print "</div>";
    print "<div id='footer-spacer'></div>";
    print "</div >"; //end container
    print $footer;
    print "</body>";
    print "</html>";
?>
<script language='JavaScript' type='text/javascript' src='prototype.js'></script>
<script language='JavaScript' type='text/javascript' src='tmsl.js'></script>
<script>
  function help_win() {
    window.open('rosterHelp.php', 'helpWin', 'width=500, height=600, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes');
  }
  function signWaiver(player_uid) {
    var url='ajax_sign_waiver.php';
    var params="uid=" + player_uid + "&team_id=<?=$team_id?>&season_id=<?=$season_id?>";
    var myAjax=new Ajax.Request(url, {method: 'post', parameters: params, onComplete:function() {toggle_waiver_icon(player_uid, true);} });
  }
  function unSignWaiver(player_uid) {
    var url='ajax_sign_waiver.php';
    var params="uid=" + player_uid + "&team_id=<?=$team_id?>&season_id=<?=$season_id?>&actn=unsign";
    var myAjax=new Ajax.Request(url, {method: 'post', parameters: params, onComplete:function() {toggle_waiver_icon(player_uid, false);} });
  }
  function toggle_waiver_icon(player_uid, s) {
    if (s) {
      $('waiver_' + player_uid).innerHTML = "<img alt='Waiver Signed' src='images/waiver_signed.png' title='Waiver Signed' border='0'>";
      $('waiver_' + player_uid).onclick = function() {unSignWaiver(player_uid);}
    } else {
      $('waiver_' + player_uid).innerHTML = "<img alt='Waiver Not Signed' src='images/waiver_unsigned.png' title='Waiver Not Signed; Click to Mark as Signed.' border='0'>";
      $('waiver_' + player_uid).onclick = function() {signWaiver(player_uid);}
    }  
  }
  function updt_colors() {
    var url='ajax_updt.php';
    var inpt_id = 'colrz';
    var col = $F(inpt_id);
    var params = 'tbl=tmsl_team_season&fld=colors&team_id=<?=$team_id?>&season_id=<?=$season_id?>&val='+col;
    var dv='sp_colrz';
    var myAjax=new Ajax.Request(url, {method: 'post', parameters: params });
  }
</script>
<?  
  }else include("login.php");
?>
