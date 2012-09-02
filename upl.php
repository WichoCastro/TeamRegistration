<?
	include_once("session.php");
	$gamedatafields=array('season_uid', 'team_h', 'team_v', 'game_loc', 'game_dt', 'game_tm');
	print "<html>";
	print "<head>";
	print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
	print "</head>";
	print "<body style='margin: 0 auto; width:800;text-align:center;'>";

	print "<div id='pageHdr'>File Upload</div>";
	print "<a href='games.php'>Back to Games</a><br/>";
	if (isset($_POST['uploadBtn']) ) { //user has uploaded a file for review
		if (isset($_POST['addRefs'])) $addRefs=true;
		if (isset($_POST['addARs'])) $addARs=true;
		$valid_fields=array('home team', 'away team', 'venue', 'position 1', 'position 2', 'position 3', 'time', 'date', 'age group');
		$lines = array();
		$parsedLines = array();
		$arrTokens = array();
		$tmp				 = $_FILES['upload_file'];
		$uf_file		 = $tmp['tmp_name'];
		$uf_size		 = $tmp['size'];
		if ($uf_size>0) {
			$max_file_sz = 100000;
			if ($uf_size > $max_file_sz) {print "File is too large"; exit;}
			$handle = fopen($uf_file, 'r');

			$contents = fread($handle, $uf_size);
			$lines = preg_split("/(\r\n|\r|\n)/", $contents);
      foreach ($lines as $curline) {
        //if you find anything in quotes, replace comma with "--comma--"
        $startQuoted = strpos($curline, '"');
        $j=1;
        while (is_numeric($startQuoted) && $j<100) {
          $endQuoted = strpos($curline, '"', 1+$startQuoted);
          if ($endQuoted) {
            $theQuote=substr($curline, 1+$startQuoted, $endQuoted-$startQuoted-1);
            $theNewQuote=str_replace(',', '--comma--', $theQuote);
            $curline=substr($curline, 0, $startQuoted).$theNewQuote.substr($curline, $endQuoted+1);
          }
          $startQuoted = strpos($curline, '"', min(1+$endQuoted, strlen($curline)));
          $j++;
        }
        $parsedLines[]=$curline;
      }
		}
		if (is_array($parsedLines)) {
			$j=0;
			foreach ($parsedLines as $csv) {
				$rw=explode(',', trim($csv));
				//undo the comma replacement from above
				$rw=str_replace("--comma--", ",", $rw);
				$arrTokens[$j]=$rw;
				$j++;
			}
			$flds=$arrTokens[0];
			if (strlen($flds[0]) > 100) {print "This file appears to be the wrong filetype. Please save as a csv, then re-upload.";exit;}
			$data=array_slice($arrTokens, 1, sizeof($arrTokens));
			foreach ($flds as $key=>$val) {
				if (!in_array(strtolower($val), $valid_fields)) continue;
				$fields[$key]=strtolower($val);
			}
			foreach($data as $rw=>$currentRow) {
				foreach($fields as $key=>$f)
					$final_data[$rw][$f]=$currentRow[$key];
			}
			//print "<br>fd<br>";
			//print_r($final_data);
			foreach ($final_data as $rw) {
				$msg="";
				//time
				$tm=$rw['time'];
if ($debug) print "time=$tm ";
				if(!$tm) {$msg .= "No time specified ";}

				//date
				$dt=$rw['date'];
if ($debug) print "date=$dt ";
				if(!$dt) {$msg .= "No date specified ";}


				if(!$dtFormat) {
					if (substr($dt,4,1) == '-') $dtFormat="%Y-%m-%d";
					else $dtFormat="%c/%e/%Y";
				}

				//teams
				$h=$rw['home team'];
				$team_h=getTeamIDbyName($h);
				if (!$team_h) {$msg .= "Team $h not found "; }

				$v=$rw['away team'];
				$team_v=getTeamIDbyName($v);
				if (!$team_v) {$msg .= "Team $v not found "; }

				//season
				$s=$rw['age group'];
				$season_uid=getSeasonIDbyName($s, $dt, $dtFormat);
if ($debug) print "season=$season_uid ";
				if (!$season_uid) {$msg .= "No season for $s found ($dt)"; }

				//venue
				$game_loc=$rw['venue'];
				if (!$game_loc) {$game_loc='TBD';}

				$gamedata=array();
				foreach($gamedatafields as $fld) $gamedata[$fld]="'".$$fld."'";
				$gamedata['game_dt']="STR_TO_DATE('$dt','$dtFormat')";
				$gamedata['game_tm']="TIME(STR_TO_DATE('$tm', '%l:%i %p'))";

				if ($msg) {print $msg; print "Not using row";print implode(',',$rw);print "<br/>";continue;}

				//check to see if there's already a game at this time-date-venue (if not TBD)
				if (strtoupper($game_loc) != 'TBD') {
//print "Checking to see if there's a game with <pre>";print_r($gamedata);print "</pre>";
					$sql="SELECT uid FROM tmsl_game WHERE game_dt=STR_TO_DATE('$dt','$dtFormat') AND game_tm=TIME(STR_TO_DATE('$tm', '%l:%i %p')) AND game_loc LIKE '$game_loc'";
					//print $sql;
					$arrSameTimePlace=dbSelectSQL($sql);
					$gm_id=$arrSameTimePlace[0]['uid'];
					$sql="SELECT uid FROM tmsl_game WHERE game_dt=STR_TO_DATE('$dt','$dtFormat') AND (team_h=$team_h OR team_h=$team_v) AND (team_v=$team_h OR team_v=$team_v)";
//print $sql;
					$arrSameTeamsSameDate=dbSelectSQL($sql);
					$gm_id=$arrSameTeamsSameDate[0]['uid'];
					if ($gm_id) {
//print "game exists...<br>";
						$game_exists=true;
						if ($overwriteGames) {
							if ($displayOnly) {print "update $gm_id with <pre>";print_r($gamedata);print "</pre>";}
							else dbUpdate('tmsl_game', $gamedata, array('uid'=>$gm_id), 1, 1, false, false);
						} else print "There is already a game (#$gm_id) scheduled at $game_loc on $dt at $tm<br/><br/>";
					} else {
							if ($displayOnly) {print "insert <pre>";print_r($gamedata);print "</pre>";}
							else {
								$sql="INSERT INTO tmsl_game (season_uid, team_h, team_v, game_loc, game_dt, game_tm)
									VALUES ($season_uid, $team_h, $team_v, '$game_loc', STR_TO_DATE('$dt','$dtFormat'), TIME(STR_TO_DATE('$tm', '%l:%i %p')))";
								mysql_query($sql) or die(">>".$sql."<<".mysql_error());
								$gm_id=mysql_insert_id();
								print "Game #$gm_id now scheduled at $game_loc on $dt at $tm<br/>";
							}
					}
					if ($gm_id) {
						//ref assignments
						$ref=$rw['position 1'];
						$ar1=$rw['position 2'];
						$ar2=$rw['position 3'];
						$ref_id=getRefIDbyName($ref);
						$ar1_id=getRefIDbyName($ar1);
						$ar2_id=getRefIDbyName($ar2);
						//print "ref $ref $ref_id ar1 $ar1_id ar2 $ar2_id<br/>";
						if (!$ref_id) {
							if ($addRefs) {
								if ($displayOnly) print "Referee $ref add to the database with logon rights<br/>";
								else {
									$ref_id=addRef($ref, true);
									print "Referee $ref add to the database, has logon rights<br/>";
								}
							} else print "Referee $ref is not in the system<br/>";
						}
						if ($addARs && !$ar1_id) {
								if ($displayOnly) print "Assistant referee $ref add to the database without logon rights<br/>";
								else {
									$ar1_id=addRef($ar1);
									print "Assistant referee $ref add to the database<br/>";
								}
						}
						if ($addARs && !$ar2_id) {
								if ($displayOnly) print "Assistant referee $ref add to the database without logon rights<br/>";
								else {
									$ar2_id=addRef($ar2);
									print "Assistant referee $ref add to the database<br/>";
								}
						}
						if (!$displayOnly) {
							dbDelete('tmsl_game_assign', array('game_uid'=>$gm_id));
							if($ref_id) mysql_query("INSERT INTO tmsl_game_assign (game_uid, user_uid, edit_right) VALUES ($gm_id, $ref_id, 2)");
							if($ar1_id) mysql_query("INSERT INTO tmsl_game_assign (game_uid, user_uid, edit_right) VALUES ($gm_id, $ar1_id, 1)");
							if($ar2_id) mysql_query("INSERT INTO tmsl_game_assign (game_uid, user_uid, edit_right) VALUES ($gm_id, $ar2_id, 1)");
						}
					}
				}
			}
		}
	}
?>
		<div id='borderedContainer'>
		<div id='simpleInstr'>Upload a csv file with game information. Note: Please make sure it's in csv format. To save an Excel doc as a csv, from Excel, choose "File | Save As",
		then at the very bottom of the dialog box, from the "Save as Type" dropdown menu, select "CSV (comma delimited)".</div>
		<form enctype='multipart/form-data' method='post'>
		<input type='file' class='smallform' name='upload_file' value='<?=$_FILES['upload_file']['name']?>' size='50'><br/><br/>
		<input type='checkbox' name='displayOnly'>Display Results Only -- Do not write to the DB.<br/>
		<input type='checkbox' name='overwriteGames' checked='checked'>Overwrite Game Information if the csv contains a game in the same time and place or if the csv contains a game on the same date for the two teams as a game already in the system.<br/>
		<input type='checkbox' name='addRefs' "checked='checked'">Add Center Refs that aren't in the system.<br/>
		<input type='checkbox' name='addARs' <? if ($addARs) print "checked='checked'";?>>Add Assistant Refs that aren't in the system.<br/>
		<input type=submit value=OK name='uploadBtn'>
		</form>
		</div>
<?
	print "</div>";
	print "</body>";
	print "</html>";
?>
