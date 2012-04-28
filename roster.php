<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
    if (!$team_id) $team_id=$_SESSION['team_uid'];
    if (!$season_id) {
      $season_id_arr=dbSelect('tmsl_team_season', array('season_uid'), array('team_uid'=>$team_id), array('season_uid desc'));
      $season_id=$season_id_arr[0]['season_uid'];
    }
    if (hasPermission($_SESSION['mask'], $team_id, $season_id)) $mgr=true;else $mgr=false;
    if ($_SESSION['mask'] & 4) $adm=true;else $adm=false;
    print "<html>";
    print "<head>";
    print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
    print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
    ?>
    <script>
    	function upd_jrsy(playerid) {
				var url='ajax_updt.php';
				var str='j_'+playerid;
				var j_num=$F(str);
				var params='tbl=tmsl_player_team&fld=jersey_no&team_id=<?=$team_id?>&uid='+playerid+'&val='+j_num;
				var dv='j_'+playerid;
				var myAjax=new Ajax.Updater(dv, url, {method: 'post', parameters: params });
			}
		</script>
		<?
    print "</head>";
    print "<body>";
    print $banner;
    print $navBar;
    print "<div id='ttlBar'>Team Roster <span onclick='help_win()'><img src='images/q.jpg' alt='Help' title='Help' border='0'></span></div>";
    print "<div id='mainPar'>";

		//has the season ended?
		if ($season_id) {
			$tmp=dbSelectSQL("SELECT (now() > stop_date) as toast FROM tmsl_season WHERE uid=$season_id");
			if ($tmp[0]['toast']) $season_over=true;
		}

    print "<form method='get'>";
    $sd=getScalar('uid', $season_id, 'start_date','tmsl_season');
    $ed=getScalar('uid', $season_id, 'stop_date','tmsl_season');
		if (!$sd) $sd=date('m-d-Y');
		if (!$ed) $ed=date('m-d-Y');
    //$sql="SELECT DISTINCT s.uid, CONCAT(s.name, ' (', l.name,')') as name FROM tmsl_season s
    //  INNER JOIN tmsl_division l ON s.division_uid=l.uid WHERE s.stop_date >= '$sd' ";

    if ($season_id) $ssd="'".getScalar('uid', $season_id, 'start_date', 'tmsl_season')."'";
    else $ssd='DATE_SUB(now(), INTERVAL 1 MONTH )';
    $sql=" SELECT DISTINCT s.uid, CONCAT( d.name, ' -- ', s.name ) AS nm
			FROM tmsl_season s
			INNER JOIN tmsl_division d ON s.division_uid = d.uid
			WHERE s.stop_date >= $ssd
			ORDER BY d.rank, s.start_date desc";

    $arrSeasons=buildSimpleSQLArr("uid", "nm", $sql);
    print "Season: ";
    print getSelect("season_id", $arrSeasons, array(0=>"--Select--"), $season_id, "onchange=submit()");
    if ($season_id) {
      $sql="SELECT DISTINCT t.uid, tl.tname as name FROM tmsl_team t INNER JOIN tmsl_team_season tl ON t.uid=tl.team_uid
        INNER JOIN tmsl_season s ON tl.season_uid=s.uid";
      $sql.= " WHERE tl.season_uid = ".$season_id;

      $arrTeams=buildSimpleSQLArr("uid", "name", $sql." ORDER BY tname");
      print "<br/>Team: ";
      print getSelect("team_id", $arrTeams, array(0=>"--Select--"), $team_id, "onchange=submit()");
    }
    print "</form>";
    if ($season_id && $team_id) {
        if ($team_id) {$_SESSION['team_uid']=$team_id;dbUpdate('tmsl_user', array('last_team_uid'=>$team_id), array(player_uid=>$_SESSION['logon_uid']));}
        if ($season_id) {$_SESSION['season_uid']=$season_id;dbUpdate('tmsl_user', array('last_season_uid'=>$season_id), array(player_uid=>$_SESSION['logon_uid']));}

        foreach ($arrPlayerFields as $colName=>$display) {
          $flds[]="$colName AS $display";
          if (!$hideField[$display]) $tblHdr.="<th>$display</th>";
        }
        $fldStr=implode(", ",$flds);
        $sql="SELECT $fldStr
          FROM tmsl_player p INNER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
          INNER JOIN tmsl_season s ON s.uid=pt.season_uid
          WHERE s.uid=".$season_id." AND pt.team_uid=".$team_id;
        $sql .= " AND pt.stop_date='0000-00-00' ORDER BY name";
        //print $sql;
        $res=mysql_query($sql) or die (mysql_error());
        while ($rec=mysql_fetch_array($res)) {
          foreach ($arrPlayerFields as $colName=>$display)
            $playerArr[$rec['ID']][$display]=$rec[$display];
          //if ($rec['Registered']==2) $registeredPlayers++;
        }
				$rpArr=dbSelectSQL("SELECT count(*) as ct FROM tmsl_player_team WHERE registered=2 and team_uid=$team_id and season_uid=$season_id");
				$registeredPlayers=$rpArr[0]['ct'];
        $team_nm=getTeamName($team_id, $season_id);
        if (!$team_nm) exit;
        print "<div id='teamInfoBox'>";
        $division_id=getScalar('uid', $key=$season_id, 'division_uid', 'tmsl_season');
        $sql="SELECT uid from tmsl_season s JOIN tmsl_team_season ts ON s.uid=ts.season_uid
          WHERE s.start_date < (select start_date FROM tmsl_season WHERE uid=$season_id) AND team_uid=$team_id
          ORDER BY s.start_date DESC LIMIT 1";
        $prev_season_id=getScalar('','','','',$sql);
        if ($prev_season_id) print "<a href='roster.php?team_id=$team_id&season_id=$prev_season_id'><img alt='Edit' src='images/arrow_left.png' title='Previous' border='0'></a>";
        print "<span style='font-size:18pt'>$team_nm</span>";
        $sql="SELECT uid from tmsl_season s JOIN tmsl_team_season ts ON s.uid=ts.season_uid
          WHERE s.start_date > (select start_date FROM tmsl_season WHERE uid=$season_id) AND team_uid=$team_id
          ORDER BY s.start_date LIMIT 1";
        $next_season_id=getScalar('','','','',$sql);
        if ($next_season_id) print "<a href='roster.php?team_id=$team_id&season_id=$next_season_id'><img alt='Edit' src='images/arrow_right.png' title='Next' border='0'></a>";

				//team colors
				$colors=getScalar('uid', $team_id, 'colors', 'tmsl_team');
				if (!$colors) $colors='Not Defined';
				print "<br><span style='font-size:16pt'>Colors: ";
				if ($mgr) print "<a href='#' onclick=\"javascript:window.open('editTmDetails.php?team_id=$team_id', 'etd', 'height=400; width=800');\">$colors</a></span>";
				else print "$colors</span>";

        //get team rep info
        print "<br/>Team Reps:<br/>";
        $arr=dbSelect('tmsl_team_manager tm join tmsl_player p on tm.user_uid=p.uid', array('uid', 'fname', 'lname', 'email', 'phone'), array('team_uid'=>$team_id, 'season_uid'=>$season_id));
        if (count($arr))
          foreach($arr as $rep) {
            if (strpos($rep['email'],'@')) print "<a href='mailto:".$rep['email']."'>".$rep['fname']." ".$rep['lname']."</a> ".$rep['phone']."<br/>";
            else print $rep['fname']." ".$rep['lname']." ".$rep['phone']."<br/>";
          }
        else print "None";
        if($mgr) print "<a href='mgTeamReps.php?team_id=$team_id&season_id=$season_id'>Manage Team Reps</a>";
        print "</div><br/>";
        if ($mgr) {
        $sql="SELECT DATE_FORMAT(stop_date, '%m/%d/%Y') as last_day_player
          FROM tmsl_season WHERE uid=$season_id";
        $res=mysql_query($sql);
        $rec=mysql_fetch_assoc($res);
        $last_day_player=$rec['last_day_player'];

        $sql="SELECT notes, registered FROM tmsl_team_season WHERE team_uid=$team_id and season_uid=$season_id";
        $res=mysql_query($sql);
        $rec=mysql_fetch_assoc($res);
        $registered=$rec['registered'];
        $notes=$rec['notes'];
        if ($registered==2) print "This team is registered; $registeredPlayers registered players.<br/>";
        elseif ($registered==1) {
          print "Registeration has been submitted -- awaiting confirmation.<br/>";
          if (hasRegistrationAuthority($_SESSION['mask']))
            print "<br/><input type='button' class='majAction' value='ACCEPT REGISTRATION' onClick='window.location=\"acceptRegistration.php?team_id=$team_id&season_id=$season_id\"'><br/>";
        }
        elseif ($registered==3) {
          print "Registeration has been cancelled.<br/>";
          if (hasRegistrationAuthority($_SESSION['mask']))
            print "<br/><input type='button' class='majAction' value='ACCEPT REGISTRATION' onClick='window.location=\"acceptRegistration.php?team_id=$team_id&season_id=$season_id\"'><br/>";
        }
        else {
          if ($mgr) {
            if ($notes) print "<span id='updateMsg'>$notes</span><br/>";
            //if team has not registered and criteria are met (date and number of players), show register button
            $sql="SELECT min_players, max_players, DATE_FORMAT(last_day_team, '%m/%d/%Y') as last_day_team,
              DATE_FORMAT(stop_date, '%m/%d/%Y') as last_day_player
              FROM tmsl_season WHERE uid=$season_id";
            $res=mysql_query($sql);
            $rec=mysql_fetch_assoc($res);
            //print_r($rec);
            $min_players=$rec['min_players'];
            $max_players=$rec['max_players'];
            $last_day_team=$rec['last_day_team'];
            $last_day_player=$rec['last_day_player'];
            $numOnRoster=count($playerArr);
            if (time() <= strtotime($last_day_team) + 1440*60 ) {
              print "The last day to register a team is $last_day_team.<br/>";
              if ($numOnRoster >= $min_players) {
                if ($numOnRoster <= $max_players) {
									if ($colors == 'Not Defined')
										print "<br><span style='font-size:16pt'><a href='#' onclick=\"javascript:window.open('editTmDetails.php?team_id=$team_id', 'etd', 'height=400; width=800');\">Please click here to list team colors before registering.</a></span><br><br>";
                  else
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
        //print_r($playerArr);

        if (!empty($playerArr)) {
          print "<table border='1' align='center'>";
          print "<tr>";
          if ($mgr) print "<th></th>";
          print "$tblHdr</tr>";
          foreach ($playerArr as $playerid=>$playerDetails) {
						$susp = dbSelectSQL("SELECT * from tmsl_suspended WHERE player_uid=$playerid and now() >= start_date and now() <= stop_date");    
					  if (count($susp)) 					  $playerDetails['Suspended']=1;
            $registered_color='';
            if ($playerDetails['Suspended']) $suspended_color="bgcolor='yellow'";
            else $suspended_color="";
            if ($playerDetails['Registered']==0)$registered_color="bgcolor='#bbbbbb'";
            elseif ($playerDetails['Registered']=='1')$registered_color="bgcolor='#dddddd'";
            elseif ($playerDetails['Registered']=='3')$registered_color="bgcolor='#bb4444'"; // 3 -> denied!
            if ($suspended_color)
              $col=$suspended_color;
            else
              $col=$registered_color;
            print "<tr $col>";
            if ($mgr) {
              print "<td><table><tr>
                <td>
                  <a href='editPlayer.php?edit=1&uid=$playerid&team_id=$team_id&season_id=$season_id'>
                  <img alt='Edit' src='images/pencil.png' title='Edit' border='0'>
                  </a>
                </td>
                <td>
                  <a href='editPlayer.php?drop=1&uid=$playerid&team_id=$team_id&season_id=$season_id'>
                  <img alt='Drop' src='images/delete.png' title='Drop' border='0'>
                  </a>
                </td>";
              if ($adm && $registered) {
                if (!$playerDetails['Suspended'])
                  print "
                    <td>
                      <a href='editPlayer.php?suspend=1&uid=$playerid&team_id=$team_id&season_id=$season_id'>
                      <img alt='Suspend' src='images/vcard_delete.png' title='Suspend' border='0'>
                      </a>
                    </td>";
                  else {
                  $susp_id=getScalar('','','','',"SELECT uid FROM tmsl_suspended WHERE player_uid=$playerid ORDER BY start_date DESC LIMIT 1");
                  print "
                    <td>
                      <a href='reportSuspended.php?edit=1&s_uid=$susp_id'><img alt='Suspend' src='images/vcard_delete.png' title='Suspend' border='0'></a>
                    </td>";
                  }
                if ($playerDetails['Registered']!=2)
                  print "
                    <td>
                      <a href='acceptPlayerRegistration.php?register=2&uid=$playerid&team_id=$team_id&season_id=$season_id'>
                      <img alt='Accept Registration' src='images/accept.png' title='Accept Registration' border='0'>
                      </a>
                    </td>";
                else  print "
                    <td>
                      <a href='acceptPlayerRegistration.php?register=1&uid=$playerid&team_id=$team_id&season_id=$season_id'>
                      <img alt='Mark as Not Registered' src='images/cancel.png' title='Mark as Not Registered' border='0'>
                      </a>
                    </td>";
              }
              if ($mgr && !$playerDetails['Pic'])
                print "
                  <td>
                    <a href='uploadPhoto.php?player_id=$playerid'>
                    <img alt='Camera' src='images/camera.png' title='Please Upload Photo' border='0'>
                    </a>
                  </td>";
              if ($mgr && !$playerDetails['dob_val']) {
                print "<td>";
								if ($adm) print "<a href='validate_dob.php?player_id=$playerid'>";
                print "<img alt='Red Flag' src='images/flag_red.png' title='Age Must Be Validated' border='0'>";
                if ($adm) print "</a>";
                print "</td>";
	      			}
              if ($adm && $playerDetails['Notes'])
                print "
                  <td>
                    <img alt='Notes' src='images/comment.png' title=\"{$playerDetails['Notes']}\" border='0'>
                  </td>";
              if ($adm)
                print "
                  <td>
                    <a href='transferPlayer.php?playerid=$playerid&fromTeam=$team_id&fromSeason=$season_id'>
                    <img alt='Transfer' src='images/arrow_right.png' title=\"Transfer Player\" border='0'>
                    </a>
                  </td>";
              $sql_cards="SELECT c.card_type, g.game_dt, c.uid as card_uid
              							FROM tmsl_card c INNER JOIN tmsl_game g ON c.game_uid=g.uid
              							WHERE c.player_uid=$playerid AND c.team_uid=$team_id AND c.season_uid=$season_id
              							ORDER BY c.card_type DESC, g.game_dt";
              $arr_cards=dbSelectSQL($sql_cards);
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
            }
            foreach ($arrPlayerFields as $colName=>$display) {
              if (!$hideField[$display]) {
                print "<td>";
                if ($mgr && $display=='Jersey') print "<form><input type='text' id='j_{$playerid}' value='{$playerDetails[$display]}'
                	size='5' onchange='upd_jrsy($playerid);' onclick='select();'></form>";
                else print $playerDetails[$display];
                print "</td>";
              }
            }
            print "</tr>";
          }
          print "</table>";


        }else{
          print "There are no players on this team.<br/>";
        }
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
          if (!$season_over) print "<td><form>
                <input type='button' value='Game Card'
                onclick='window.open(\"roster_card.php?team_id=$team_id&season_id=$season_id\",
                	\"roster_win\",
                	\"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes, toolbar=yes\")'>
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
    }
    print "<input type='button' value='Search Teams' onclick='window.location=\"team.php\"'>";
    //print "<input type='button' value='Add a New Team' onclick='window.location=\"addTeam.php\"'>";
    if ($mgr) print "<input type='button' value='View Balance' onClick='window.location=\"invoice.php?team_id=$team_id&season_id=$season_id\"' class='pointer'>";
    }
    print "</div>";
    print "</body>";
    print "</html>";
  }else include("login.php");
?>
<script>
  function help_win() {
    window.open('rosterHelp.php', 'helpWin', 'width=500, height=600, location=no, toolbar=no, menubar=no, titlebar=no, scrollbars=yes');
  }
</script>
