<?
	include_once("functions.php");
	include_once("sql.inc");
	$gamedatafields=array('season_uid', 'team_h', 'team_v', 'game_loc', 'game_dt', 'game_tm');


  date_default_timezone_set('America/Phoenix');
  //time now is
  $tm = time();
  //one week would be
  $week_from_now = $tm + 14*24*3600;
  //date now is 
  $dt = date('M j, Y');
  //date in one week
  $next_week = date('M j Y', $week_from_now);
  $x = getGames($dt, $next_week);
  $games = $x['games'];
  foreach($games as $g) {
    $msg = "";
    $gm_id=0;
    $game_dt = date('Y-m-d', strtotime($g['start_time']));
    $game_tm = date('H:i', strtotime($g['start_time']));
    $game_loc = $g['venue_name'];
    $team_h = getTeamIDbyName($g['home_team_name']);
    $team_v = getTeamIDbyName($g['away_team_name']);
    $s = $g['age_group_name'];
    $season_uid = getSeasonIDbyName($s, $game_dt, '%Y-%m-%d');
    foreach($gamedatafields as $fld) $gamedata[$fld]="'".$$fld."'";
    $ref = $g['existing_assignments'][0]['name']; //center ref
    $ar1 = $g['existing_assignments'][1]['name']; 
    $ar2 = $g['existing_assignments'][2]['name']; 
	$center_ref_assignr_id = $g['existing_assignments'][0]['id'];
    if (!$team_h) {$msg .= "Team {$g['home_team_name']} not found ($game_dt $game_loc) -- ignoring;"; }
    if (!$team_v) {$msg .= "Team {$g['away_team_name']} not found ($game_dt $game_loc) -- ignoring;"; }
    if (!$season_uid) {$msg .= "No season for $s found ($game_dt $game_loc) -- ignoring;"; }
    if (!$game_loc) {$game_loc='TBD';}
    if ($msg) {showMessage($msg); continue;}
    //check for confilicts
    if (strtoupper($game_loc) != 'TBD') {
      //check to see if there's already a game for this time & place
      $sql="SELECT uid FROM tmsl_game WHERE game_dt='$game_dt' AND game_tm='$game_tm' AND game_loc LIKE '$game_loc'";
      $arrSameTimePlace=dbSelectSQL($sql);
      $gm_id=$arrSameTimePlace[0]['uid'];
      //do these teams already play on this date? ie did the time or place get moved?
      $sql="SELECT uid FROM tmsl_game WHERE game_dt='$game_dt' AND (team_h=$team_h OR team_h=$team_v) AND (team_v=$team_h OR team_v=$team_v)";
      $arrSameTeamsSameDate=dbSelectSQL($sql);
      $gm_id=$arrSameTeamsSameDate[0]['uid'];
    }  
    if ($gm_id) {
      dbUpdate('tmsl_game', $gamedata, array('uid'=>$gm_id), 0, 0, false, false);
      print "Game updated -- {$g['home_team_name']} v. {$g['away_team_name']} {$g['start_time']}@{$g['venue_name']}<br/>";
    } else {
      $sql="INSERT INTO tmsl_game (season_uid, team_h, team_v, game_loc, game_dt, game_tm)
           VALUES ($season_uid, $team_h, $team_v, '$game_loc', '$game_dt', '$game_tm')";
      //print $sql;
      print "Game added -- {$g['home_team_name']} v. {$g['away_team_name']} {$g['start_time']}@{$g['venue_name']}<br/>";
      mysql_query($sql);
      $gm_id=mysql_insert_id();
    }
    if ($gm_id) {
      $ref_id = getRefIDbyName($ref);
      if (!$ref_id) {
      	$email = getRefEmail($center_ref_assignr_id);
      	$ref_id=addRef($ref, true, $email);
	  }
      $ar1_id=getRefIDbyName($ar1);
      if (!$ar1_id) $ar1_id=addRef($ar1);
      $ar2_id=getRefIDbyName($ar2);
      if (!$ar2_id) $ar2_id=addRef($ar2); 
      dbDelete('tmsl_game_assign', array('game_uid'=>$gm_id));
      if($ref_id) mysql_query("INSERT INTO tmsl_game_assign (game_uid, user_uid, edit_right) VALUES ($gm_id, $ref_id, 2)");
      if($ar1_id) mysql_query("INSERT INTO tmsl_game_assign (game_uid, user_uid, edit_right) VALUES ($gm_id, $ar1_id, 1)");
      if($ar2_id) mysql_query("INSERT INTO tmsl_game_assign (game_uid, user_uid, edit_right) VALUES ($gm_id, $ar2_id, 1)");
    }
  } 
  print "<a href='games.php'>back to games</a>";
  
	
  function getGames($fd, $td) {
    $fd = urlencode($fd);
    $td = urlencode($td);
    $qry = "https://jfarmer:ecb95b694ee9d27e821ee2e21af530bb7ba6ea7b@api.assignr.com/api/v1/games.json?search=after:%22".$fd."%22%20before:%22".$td."%22%20league:TMSL";
    $data = searchAssignrDB($qry);
    return $data;
  }
  
  function getRefEmail($center_ref_assignr_id) {
  	$qry = "https://jfarmer:ecb95b694ee9d27e821ee2e21af530bb7ba6ea7b@api.assignr.com/api/v1/users/{$center_ref_assignr_id}.json";
	$data = searchAssignrDB($qry);
    return $data['user']['email'];
  }

  function searchAssignrDB($query) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response,1);
  }

  function showMessage($msg) {
    print $msg;
  }

?>
