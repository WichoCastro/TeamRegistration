<?
	function buildSimpleSQLArr($keyCol, $valCol, $sql) {
		$res=mysql_query($sql) or die($sql." ".mysql_error());
		while ($rec=mysql_fetch_array($res)) {
			$sqlArr[$rec[$keyCol]]=$rec[$valCol];
		}
		return $sqlArr;
	}

	function getScalar($keyCol='', $key='', $col='', $tbl='', $sql_only='') {
		if ($sql_only) $sql=$sql_only;
		else $sql="SELECT $col FROM $tbl WHERE $keyCol = '$key'";
		$res=mysql_query($sql);
		if (!$res) {badQueryLog('getScalar', $sql);return '';}
		$rec=mysql_fetch_array($res);
		return $rec[0];
	}

	function getUserName($uid) {
		return getScalar('uid', $uid, "CONCAT(fname,' ',lname)", 'tmsl_player');
	}

	function getUserEmail($uid) {
		return getScalar('uid', $uid, "email", 'tmsl_player');
	}

	function getSelect($id, $arrOpts, $arrFirstOpts=array(0=>"--Select--"), $selectedVal="", $special="", $editable=1) {
		$ret="<select name='$id' id='$id' $special>";
		if (!empty($arrFirstOpts))
			foreach ($arrFirstOpts as $key=>$val) {
				$ret .= "<option value='$key' $sel>$val</option>";
			}
		if (!empty($arrOpts))
			foreach ($arrOpts as $key=>$val) {
				if ($selectedVal==$key) $sel="selected='1'";
				else $sel="";
				$ret .= "<option value='$key' $sel>$val</option>";
			}
		$ret .= "</select>";
		if (!$editable) $ret=$arrOpts[$selectedVal];
		return $ret;
	}

	function addBitMask($bit, $usr, $del=false) {
		$sql="SELECT bitmask FROM permissions WHERE user='$usr'";
		$res=mysql_query($sql);
		$rec=mysql_fetch_array($res);
		$bm=$rec['bitmask'];
		if ($del) {
			if ($bm & $bit) {
				$bm -= $bit;
				$sql="UPDATE permissions SET bitmask='$bm' WHERE user='$usr'";
				mysql_query($sql);
			}
		}else{
			if ($bm) {
				if (!($bm & $bit)) {
					$bm += $bit;
					$sql="UPDATE permissions SET bitmask='$bm' WHERE user='$usr'";
					mysql_query($sql);
				}
			}else{
				if (!($bit & 1)) $bit += 1; //always set '1' on
				$sql="INSERT permissions (bitmask, user) VALUES ('$bit', '$usr')";
				mysql_query($sql);
			}
		}
	}

	function showPermissions($bitmask, $perms, $usr) {
		if ($bitmask==255) return "Admin";
		else {
			foreach($perms as $key=>$val) {
				if ($key > 1) {
					if ($key & $bitmask) {
						if ($key==4)
							$str.="<a href='sectionEditPrivs.php?user=$usr'>$val</a>";
						else
							$str.=$val;
						$str.="<span onClick='delPerm($key, \"$usr\")' style='cursor:pointer'><img src='images/delete.png' alt='del' border='0'></span>";
						$str.="; ";
					}
				}
			}
			return $str;
		}
	}

	function getSeason($team_uid, $dt=0) {
	  if ($dt) $dt_cls = " AND s.start_date <= '$dt' AND s.stop_date >= '$dt' "; 
		$sql="select season_uid from tmsl_team_season tl INNER JOIN tmsl_season s ON tl.season_uid=s.uid
			where team_uid=$team_uid $dt_cls ORDER BY s.start_date desc, s.active DESC";
		$res=mysql_query($sql);
		$rec=mysql_fetch_array($res);
		//$division_uid=$rec['division_uid'];
		//$league_name=$rec['name'];
		//$sql="select uid, name from tmsl_season where division_uid=$division_uid and active=1";
		//$res=mysql_query($sql);
		//$rec=mysql_fetch_array($res);
		//return array($rec['name']." ($league_name)", $rec['uid']);
		return $rec['season_uid'];
	}

	function getSeasonName($season_id) {
		$sql="select CONCAT(a.name,' (',b.name,')') as nm from tmsl_season a INNER JOIN tmsl_division b ON a.division_uid=b.uid where a.uid=$season_id";
		$res=mysql_query($sql);
		if ($res) {
		  $rec=mysql_fetch_array($res);
		  return $rec['nm'];
		} else return "";
	}

	function hasPermission($mask, $team_uid, $season_uid) {
		if ($mask & 128) return 1; //right to edit any team
		$sql="SELECT active FROM tmsl_team_manager WHERE team_uid=$team_uid AND season_uid=$season_uid AND user_uid=".$_SESSION['logon_uid'];
		$res=mysql_query($sql);
		$rec=mysql_fetch_array($res);
		if ($rec['active']) return 1;
		return 0;
	}

	function hasPermissionEditPlayer ($mask, $player_id) {
		if ($mask & 128) return 1; //right to edit any team
		if ($player_id == $_SESSION['logon_uid']) return 1; //edit self
		$sql="SELECT team_uid, season_uid FROM tmsl_player_team WHERE player_uid=$player_id";
		$res=mysql_query($sql) or die($sql." ".mysql_error());
		while ($rec=mysql_fetch_array($res)) {
			if (hasPermission($mask, $rec['team_uid'], $rec['season_uid'])) return 1;
		}
		return 0;
	}

	function hasRegistrationAuthority($mask) {
		if ($mask & 4) return 1;
	}

	function dbUpdate($tbl, $valArr, $whrArr, $log=0, $logBad=0, $ticks=true, $debug=false) {
		foreach ($valArr as $key=>$val) {
			if ($ticks)
				$vArr[]="$key='$val'";
			else
				$vArr[]="$key=$val";
		}
		foreach ($whrArr as $key=>$val) {
			if ($ticks)
				$wArr[]="$key='$val'";
			else
				$wArr[]="$key=$val";
		}
		if (!empty($vArr)) $setVals=implode(",",$vArr);
		$whr=implode(" AND ",$wArr);
		if ($setVals) {
			$sql="UPDATE $tbl SET $setVals";
			if ($whr) $sql.= " WHERE $whr";
			if ($debug) print $sql;
			$res=mysql_query($sql);
			if ($res) {
				if ($log) chgLog('update', $tbl, $whr, $setVals);
				return mysql_affected_rows();
			}else
				if ($logBad) badQueryLog('update', $sql);
		}
		return 0;
	}

	function dbInsert($tbl, $valArr, $log=0, $logBad=0, $debug=false) {
		foreach ($valArr as $key=>$val) {
			$vArr[]="'$val'";
			$fArr[]=$key;
		}
		if (!empty($vArr)) $vStr=implode(",",$vArr);
		if (!empty($fArr)) $fStr=implode(",",$fArr);
		if ($vStr) {
			$sql="INSERT $tbl ($fStr) VALUES ($vStr)";
			if ($debug) print $sql;
			$res=mysql_query($sql);
			$uid=mysql_insert_id();
			if ($res) {
				if ($log) chgLog('insert', $tbl, '', $fStr.";".$vStr);
				return $uid;
			}else
				if ($logBad) badQueryLog('insert', $sql);
		}
		return 0;
	}

	function dbDelete($tbl, $whrArr, $logBad=0) {
			foreach ($whrArr as $key=>$val)
				$wArr[]="$key='$val'";
			$whr=implode(" AND ",$wArr);
			if ($whr) {
				$sql="DELETE FROM $tbl";
				$sql.= " WHERE $whr";
				$res=mysql_query($sql);
				if ($res) {
					chgLog('delete', $tbl, $whr);
					return mysql_affected_rows();
				}else
					if ($logBad) badQueryLog('delete', $sql);
			}
			return 0;
	}

	function dbSelect($tbl, $colArr, $whrArr, $orderArr="") {
		if (!empty($whrArr)) {
			foreach ($whrArr as $key=>$val)
				$wArr[]="$key='$val'";
			$whr=implode(" AND ",$wArr);
		}
		if (!empty($colArr)) $cols=implode(',', $colArr);
		else $cols='*';
		if (!empty($orderArr)) $order=implode(',', $orderArr);
		if ($cols) {
			$sql="SELECT $cols FROM $tbl";
			if ($whr) $sql.= " WHERE $whr";
			if ($order) $sql.= " ORDER BY $order";
			$res=mysql_query($sql) or die (mysql_error()."in $sql");
			while ($rec=mysql_fetch_assoc($res)) {
				$arr[]=$rec;
			}
		}
		return $arr;
	}

	function dbSelectSQL($sql) {
		$res=mysql_query($sql);
		if (!$res) {
			badQueryLog('dbSelectSQL', $sql);
			return array();
		}
		while ($rec=mysql_fetch_assoc($res)) {
			$arr[]=$rec;
		}
		return $arr;
	}

	function verifyAddPlayerToTeam($player_id, $team_id, $season_id) {
		$o45_div_id = 1;
		$div_id=getScalar('uid', $season_id, 'division_uid', 'tmsl_season');
		//too late?
		$sql="SELECT case when now() > DATE_ADD(last_day_player, INTERVAL 1 DAY) then 1 else 0 end as tooLate FROM tmsl_season where uid=$season_id";
		$tooLate=getScalar('', '', '', '', $sql);
		if ($tooLate) $msg = "The last day to add a player has passed.";
		else {
			//team full?
			$max_players=getScalar('uid', $season_id, 'max_players', 'tmsl_season');
			$roster_size=getScalar('', '', '', '', "SELECT count(*) FROM tmsl_player_team WHERE team_uid=$team_id AND season_uid=$season_id");
			if ($roster_size >= $max_players) $msg = "Roster is full -- the max is $max_players";
			else {

				//get teams player is on with times overlapping given season
				$sql="SELECT team_uid, season_uid FROM tmsl_player_team WHERE season_uid IN
					(SELECT s1.uid FROM tmsl_season s1, tmsl_season s2 WHERE s2.uid=$season_id AND s1.start_date < s2.stop_date
						AND s1.stop_date > s2.start_date)
					AND player_uid=$player_id";
				$arr=dbSelectSQL($sql);
				if (count($arr)==1) {
					$div_id2=getScalar('uid', $arr[0]['season_uid'], 'division_uid', 'tmsl_season');
					if (($div_id == $o45_div_id && $div_id2 != $o45_div_id) || ($div_id2 == $o45_div_id && $div_id != $o45_div_id))
						$okay=true;
					else {
						$tid=$arr[0]['team_uid'];
						$sid=$arr[0]['season_uid'];
						$tmName=getTeamName($tid, $sid);
						$msg="Player is already on <a href='roster.php?team_id=$tid&season_id=$sid'>$tmName</a>.";
						if ($tid != $team_id) $msg.="  Player must be released from that team before registereing with yours.  Contact the team rep or the TMSL board.";
					}
				}
				elseif (count($arr)>1)
					$msg="Player is already on more than one team for the season";
				else $okay=true;
			}
		}
		$start_date=getScalar('uid', $season_id, 'case when start_date>now() then start_date else now() end as start_date', 'tmsl_season');
		if ($okay && $div_id==$o45_div_id) {
			$sql="SELECT case when str_to_date('$start_date', '%Y-%m-%d') > DATE_ADD(dob, INTERVAL 45 YEAR) then 1 else 0 end as over_45  FROM tmsl_player where uid=$player_id";
			$over_45=getScalar('', '', '', '', $sql);
			if (!$over_45) {
				$dob=getScalar('uid', $player_id, "DATE_FORMAT(dob, '%m/%d/%Y')", 'tmsl_player');
				$msg="Player Does not meet age requirements for this league.";
				$msg.="<br/>Must be 45 by $start_date";
				if ($dob) $msg.=" Birthdate listed as $dob";
				else $msg.=" No birthdate on file";
				$okay=false;
			}
		}
		if ($okay) {
		//check long-term suspension
			$sql="select start_date, stop_date from tmsl_suspended where player_uid=$player_id ORDER BY start_date desc";
			$arr=dbSelectSQL($sql);
			$susp_start=$arr[0]['start_date'];
			$susp_stop=$arr[0]['stop_date'];
		 	if ($susp_start) {
				$sql="SELECT case when '$start_date' < '$susp_stop' then 1 else 0 end as susp";
				$susp=getScalar('', '', '', '', $sql);
				if ($susp) {
					$okay=false;
					$msg.="<br/>Player is suspended until $susp_stop";
				}
			}
		}
		if ($okay) return "1";
		else return $msg;
	}

	function chgLog($act, $tbl, $whr, $set="") {
		$whr=mysql_escape_string($whr);
		$set=mysql_escape_string($set);
		if (!isset($_SESSION['logon_uid'])) return 0;
		$sql = "INSERT INTO tmsl_change_log VALUES (null,".$_SESSION['logon_uid'].", '$act', '$tbl', '$whr', '$set', '".$_SERVER['REMOTE_ADDR']."', now())";
		mysql_query($sql) or die($sql." ".mysql_error());
		return 0;
	}

	function badQueryLog($act, $badsql) {
		$badsql=mysql_escape_string($badsql);
		$sql = "INSERT INTO tmsl_bad_query_log VALUES (null,".$_SESSION['logon_uid'].", '$act', '$badsql', '".$_SERVER['REMOTE_ADDR']."', now())";
		mysql_query($sql) or die($sql." ".mysql_error());
	}

	function getTeamName($team_id, $season_id=0) {
		if (!$team_id) return "";
		if(!$season_id) $arr=dbSelect('tmsl_team_season', array('tname'), array('team_uid'=>$team_id), array('start_date desc'));
		else $arr=dbSelect('tmsl_team_season', array('tname'), array('team_uid'=>$team_id, 'season_uid'=>$season_id));
		$ret=$arr[0]['tname'];
		if (!$ret && $season_id) return getTeamName($team_id);
		return $ret;
	}

	function printTable($sql, $hdrStyl=array(), $rowStyl=array(), $blankRows=0, $totalRows=0) {
		$res=mysql_query($sql) or die("$sql -- ".mysql_error());
		$str="<table border='1' align='center'>";
		while ($rec=mysql_fetch_assoc($res)) {
			if (!$hdr) {
				$str.="<tr>";
				foreach($rec as $key=>$val) {
					if (!empty($hdrStyl)) $x=array_shift($hdrStyl);
					$str.="<th $x>".str_replace("_","<br>",$key)."</th>";
					$numCols++;
				}
				$str.="</tr>";
				$hdr=true;
			}
			$r=$rowStyl;
			foreach($rec as $key=>$val) {
				if (!empty($r)) $y=array_shift($r);
				//if (!$y) $y="align='center'";
				$str.="<td $y>$val</td>";
			}
			$str.="</tr>";
		}
		if ($totalRows) {
			$blankRows = $totalRows - mysql_num_rows($res);
		}
		if ($blankRows) {
			for ($i=0; $i<$blankRows; $i++) {
				$r=$rowStyl;
				$str.="<tr>";
				for ($j=0; $j<$numCols; $j++) {
					if (!empty($r)) $y=array_shift($r);
					$str.="<td $y>&nbsp;</td>";
				}
				$str.="</tr>";
			}
		}
		$str.="</table>";
		if ($hdr)
			return $str;
		else
			return "-empty-";
	}

	function addPlayerToTeam($player_id, $team_id, $season_id, $jrsy=0, $override=0) {
		if (!$override) $msg=verifyAddPlayerToTeam($player_id, $team_id, $season_id);
		else $msg=1;
		if ($msg != "1") return $msg;
		//$arr=dbSelect('tmsl_season', array('case when now() > halfway_date then (cost_per_player/2+10) else cost_per_player end as cpp'), array('uid'=>$season_id));
		$arr=dbSelect('tmsl_season', array('case when now() > halfway_date then (cost_per_player-10) else cost_per_player end as cpp'), array('uid'=>$season_id));
		$fee=$arr[0]['cpp'];
		$bm=getScalar('uid', $player_id, 'boardMember', 'tmsl_player');
		if ($bm) $fee=0;
		$team_reg_status=dbSelectSQL("SELECT registered FROM tmsl_team_season WHERE team_uid=$team_id AND season_uid=$season_id");
		if ($team_reg_status[0]['registered']>0) $player_reg_status=1;
		else $player_reg_status=0;
		dbInsert('tmsl_player_team', array('player_uid'=>$player_id, 'team_uid'=>$team_id, 'season_uid'=>$season_id, 'jersey_no'=>$jrsy,
			'registered'=>$player_reg_status,'balance'=>$fee,'start_date'=>date('Y-m-d')), true, true);
		return 1;
	}

	function getLatestTeams($player_id) {
		$sql="SELECT pt.team_uid, ts.tname, ts.season_uid FROM tmsl_team_season ts JOIN tmsl_player_team pt
		  ON ts.team_uid=pt.team_uid AND ts.season_uid=pt.season_uid JOIN tmsl_season s ON ts.season_uid=s.uid
		  WHERE DATE_ADD(s.stop_date, INTERVAL 360 DAY) > now() AND pt.player_uid=$player_id";
		$sql = "SELECT c.tname, c.team_uid, c.start_date, c.season_uid, a.stop_date, 0 as dropped from tmsl_player_team a
                        INNER JOIN tmsl_team b ON a.team_uid=b.uid
                        INNER JOIN tmsl_team_season c ON c.team_uid=b.uid AND c.season_uid=a.season_uid
                        INNER JOIN tmsl_season s ON c.season_uid=s.uid
                        WHERE a.player_uid=$player_id and a.registered=2 AND c.registered=2
                        AND DATE_ADD(s.stop_date, INTERVAL 720 DAY) > now() 
                        UNION
            SELECT c.tname, c.team_uid, c.start_date, c.season_uid, a.drop_date as stop_date, 1 as dropped from tmsl_dropped a
                        INNER JOIN tmsl_team b ON a.team_uid=b.uid
                        INNER JOIN tmsl_team_season c ON c.team_uid=b.uid AND c.season_uid=a.season_uid
                        INNER JOIN tmsl_season s ON c.season_uid=s.uid
                        WHERE a.player_uid=$player_id and a.registered=2 AND c.registered=2
                        AND DATE_ADD(s.stop_date, INTERVAL 720 DAY) > now() 
                        ORDER BY start_date DESC, tname";
		$arr=dbSelectSQL($sql);
		if (empty($arr)) return "-none in last 2 years-<br/>";
		foreach($arr as $rec) {
			$snm = getSeasonName($rec['season_uid']);
			$str .= "<a href='roster.php?team_id={$rec['team_uid']}&season_id={$rec['season_uid']}'>{$rec['tname']} ($snm)</a>";
			if ($rec['dropped']) $str.= " (dropped ".$rec['stop_date'].")";
		        $str .= "<br/>";
		}
		return $str;
	}

	function showGame($uid, $adm) {
		$sql="SELECT * FROM tmsl_game WHERE uid=$uid";
		$arr=dbSelectSQL($sql);
		$rec=$arr[0];
		$str .= "<tr>";
		$str .= "<td>{$rec['game_time']}</td>";
		$str .= "<td>".getTeamName($rec['team_h'], $rec['season_uid'])."</td>";
		$str .= "<td>".getTeamName($rec['team_v'], $rec['season_uid'])."</td>";
		if ($adm) $str .= "<td><form><input type='text' id='game_loc_$uid' value='{$rec['game_loc']}' onchange='upd_fld(\"game_loc\", $uid)'></form></td>";
		else $str .= "<td>{$rec['game_loc']}</td>";
		$str .= "</tr>";
		return $str;
	}

	function permissionGameReport($game_id, $user_id) {
		$sql="SELECT edit_right FROM tmsl_game_assign WHERE game_uid=$game_id AND user_uid=$user_id AND edit_right=2";
		$arr=dbSelectSQL($sql);
		return $arr[0]['edit_right'];
	}

	function showTimeDropDown($id, $start_time, $stop_time, $interval_mins, $selectedVal="", $editable=1, $arrFirstOpts=array(0=>"--Select--"), $special="") {
		for ($tm=$start_time; $tm<=$stop_time; $tm += $interval_mins) {
			$m=substr($tm,-2);
			if ($m >=60) {$tm += 40; $m=$m-60;}
			$h=substr($tm,0,-2);
			$hd=str_pad($h, 2, '0', STR_PAD_LEFT);
			$md=str_pad($m, 2, '0', STR_PAD_LEFT);
			if ($h >= 12) {$amPm='p';}
			else $amPm='a';
			if ($h > 12) {$h=$h-12;}
			$t="$hd:$md";
			$t_disp="$h:$md$amPm";
			$arrOpts[$t]=$t_disp;
		}
		return getSelect($id, $arrOpts, $arrFirstOpts, $selectedVal, $special, $editable);
	}

	function scrubTeamName($nm) {
		$nm=strtolower($nm);
		$alias=getScalar('nickname', $nm, 'translation', 'tmsl_alias');
		if ($alias) $nm=$alias;
		$nm=str_replace('.','',$nm);
		$nm=str_replace(' ','',$nm);
		return ($nm);
	}

	function getTeamIDbyName($nm) {
		$nm=scrubTeamName($nm);
		$sql="SELECT DISTINCT team_uid FROM tmsl_team_season WHERE REPLACE(REPLACE(LOWER(tname),'.',''),' ','') LIKE '$nm' ORDER BY season_uid DESC";
//print $sql;
		$arr=dbSelectSQL($sql);
		$cnt=count($arr);
		if (!$cnt) return 0;
		return $arr[0]['team_uid'];
	}

	function getSeasonIDbyName($div, $dt, $dtFormat="%c/%e/%Y") {
		$div=strtolower($div);
		$alias=getScalar('nickname', $div, 'translation', 'tmsl_alias');
		if ($alias) $div=$alias;
		$div=mysql_real_escape_string($div);
		$div_id=getScalar('LOWER(name)', $div, 'uid', 'tmsl_division');
		if (!$div_id) return 0;
		$sql="SELECT uid FROM tmsl_season WHERE division_uid=$div_id AND STR_TO_DATE('$dt','$dtFormat') >= start_date AND STR_TO_DATE('$dt','$dtFormat') <= stop_date ";
		$arr=dbSelectSQL($sql);
		if (!count($arr)) return 0;
		return $arr[0]['uid'];
	}

	function getRefIDbyName($nm) {
	  $nm_arr = explode(',', $nm);
		$ln = trim($nm_arr[0]);
		$fn = trim($nm_arr[1]);
		$fullname=strtolower($fn.$ln);
		$uname =  strtolower($fn[0].$ln);
		//$arr=dbSelectSQL("SELECT uid FROM tmsl_player WHERE LOWER(CONCAT(fname,lname)) LIKE '{$fullname}%'");
		$arr=dbSelectSQL("SELECT player_uid as uid FROM tmsl_user WHERE name LIKE '{$uname}%' AND isReferee=1");
		if (empty($arr)) return 0;
		if (count($arr) > 1) {
			//handle this later -- there could be two referees whose names match. check the player table
			return 0;
		}
		return $arr[0]['uid'];
	}

	function addRef($nm, $addLogOn=true, $email='') {
		//is there someone with this name in the player table?
    	$nm_arr = explode(',', $nm);
		$ln = trim($nm_arr[0]);
		$fn = trim($nm_arr[1]);
		$fullname=strtolower($fn.$ln);
		$arr=dbSelectSQL("SELECT uid FROM tmsl_player WHERE LOWER(CONCAT(fname,lname)) LIKE '{$fullname}%'");
		$pid=$arr[0]['uid'];
		if ($pid) $uid=getScalar('player_uid', $pid, 'uid', 'tmsl_user');
		$vals = array('isReferee'=>1);
		if ($email) $vals['email'] = $email;
		if ($uid) dbUpdate('tmsl_user', $vals, array('uid'=>$uid), 0);
		if (!$pid) {
			$vals = array('fname'=>$fn, 'lname'=>$ln, dob=>'1990-01-01');
			if ($email) $vals['email'] = $email;
			dbInsert('tmsl_player', $vals);$pid=mysql_insert_id();
		}
		if (!$uid && $addLogOn && $pid) {
			addPlayerToUserTbl($pid, array('isReferee'=>1));
		}
		return $pid;
	}

	function addPlayertoDB($data) {
    $email = $data['email'];
    unset($data['email']);
    $pid = dbInsert('tmsl_player', $data);
    if ($pid && $email) {
      addEmail($pid, $email);
      addPlayerToUserTbl($pid);
      notifyPlayerAccount($pid, $email);
    }
    return $pid;
	}
 
  function invalidEmail($pid, $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
      return "$email is not a valid email address.";
    //see if it exists; email must be unique
    $exists = getScalar('email', $email, 'uid', 'tmsl_player');
    if ($exists && $exists != $pid)
      return "the email $email belongs to someone else.";
    return false;
  }

  function addEmail($pid, $email) {
    $invEml = invalidEmail($pid, $email);
    if (!$invEml)
      if (dbUpdate('tmsl_player', array('email'=>$email), array('uid'=>$pid)))
        return "email updated.";
    return $invEml;
  }
  
  function notifyPlayerAccount($pid, $email) {
    mail($email, 'test', 'hi', "$noreply");
  }
  
	function getUsernameFromName($nm, $format='') {
		if (!$format) {
			$comma_pos=strpos($nm, ',');
			$ln=substr($nm,0,$comma_pos);
			$fi=substr($nm,$comma_pos+2,1);
			$unm=strtolower($fi.$ln);
			$uid=getScalar('name', $unm, 'player_uid', 'tmsl_user');
			while ($uid) {
				$uname=$unm.++$j;
				$uid=getScalar('name', $uname, 'player_uid', 'tmsl_user');
			}
			if ($j) $unm.=$j;
		}
		return $unm;
	}

	function addPlayerToUserTbl($uid, $vals=array()) {
		$user_id=getScalar('player_uid', $uid, 'uid', 'tmsl_user');
		if ($user_id) return 0;
		$pwd = genPwd($uid);
		$to = getUserEmail($uid);
		$subj = 'TMSL Account Info';
		$body = "You can now logon to the TMSL Registration Site ($site_url). ";
		$body .= "Your username is $to and your password is $pwd. "; 
		$body .= "Log on to change your password, complete registration, and so forth.";
		mail($to, $subj, $body, "$noreply");
		$vals['pwd'] = sha1($pwd);
		$vals['player_uid']=$uid;
		return dbInsert('tmsl_user', $vals, 1, 1);
	}
	
	function sendNewUserEmail($e, $pwd) {
		$to = $e;
		$subj = 'TMSL Account Info';
		$body = "You can now log on to the TMSL Registration Site ($site_url). ";
		$body .= "Your username is $to and your password is $pwd. "; 
		$body .= "Log on to change your password, complete registration, and so forth.";
		mail($to, $subj, $body, "$noreply");
	}

	function genPwd($uid) {
    	$x = 3*$uid-7;
    	$y = $x % 26 + 65;
    	$z = (1317*$x + 23) % 1000000;
    	$letr = chr($y);
    	return $letr.$z;
	}

	function checkPlayerExistence($FirstName, $Middle, $LastName, $DOB) {
		$player_exists=getScalar('CONCAT(fname,mname,lname)', $FirstName.$Middle.$LastName, 'uid', 'tmsl_player');
		if ($player_exists) return array(true,'There is already a player by that name in the database', $player_exists);
		$player_exists=getScalar('CONCAT(fname,lname,DOB)', $FirstName.$LastName.$DOB, 'uid', 'tmsl_player');
		if ($player_exists) return array(true,'There is already a player by that name in the database', $player_exists);
		$sql = "SELECT fname, lname, uid FROM tmsl_player WHERE SOUNDEX(lname) = SOUNDEX('$LastName') AND SOUNDEX(fname) = SOUNDEX('$FirstName')";
		$arr = dbSelectSQL($sql);
		if (!empty($arr)) return array(true, 'Players with similar names exist in the database', $arr);
		return array(false, '', array());
	}

	function suspensionHistory($player_id, $season_id=0) {
		if (!$season_id) { //show current seasons
			$seasons=getCurrentSeason($player_id);
			foreach($seasons as $s);
				$arr[] = $s['season_uid'];
			$season_id = implode(',', $arr);
		}
		if (!$season_id) {	
			$sql = "SELECT uid, DATE_FORMAT(start_date,'%m/%d/%Y') as start_date, DATE_FORMAT(stop_date,'%m/%d/%Y') as stop_date, player_uid, reason
						FROM tmsl_suspended s
						WHERE DATE_ADD(s.stop_date, INTERVAL 720 DAY) > now() AND player_uid=$player_id";						
			return dbSelectSQL($sql);
		}	
		$sql = "SELECT MIN(start_date) as sd, MAX(stop_date) as ed FROM tmsl_season WHERE uid IN ($season_id)";
		$arr = dbSelectSQL($sql);
		$sd = $arr[0]['sd'];
		$ed = $arr[0]['ed'];		
		$sql = "SELECT uid, DATE_FORMAT(start_date,'%m/%d/%Y') as start_date, DATE_FORMAT(stop_date,'%m/%d/%Y') as stop_date, player_uid, reason
						FROM tmsl_suspended s
						WHERE s.start_date <= '$ed' AND s.stop_date >= '$sd' AND player_uid=$player_id";
		return dbSelectSQL($sql);
	}

	function getCurrentSeason($player_id) {
		$sql = "SELECT season_uid FROM tmsl_player_team pt INNER JOIN tmsl_season s ON s.uid=pt.season_uid WHERE current_date >= s.start_date AND current_date < s.stop_date AND registered = 2 AND player_uid=$player_id";
		return dbSelectSQL($sql);
	}

	function addTeamRep($player_id, $team_id, $season_id) {
		//must have an email
		$e = getUserEmail($player_id);
		addPlayerToUserTbl($player_id, array('name'=>$e));
		$arr=dbSelectSQL("SELECT count(*) as ct FROM tmsl_team_manager WHERE user_uid=$player_id AND team_uid=$team_id AND season_uid=$season_id");
		if (!$arr[0]['ct'])
			return dbInsert('tmsl_team_manager', array('user_uid'=>$player_id, 'team_uid'=>$team_id, 'season_uid'=>$season_id));
		return 0;
	}
        
    function getTmLnk ($tm, $szn) {
        return "roster.php?season_id=$szn&team_id=$tm";
    }
        
    function getSeasonDropdown ($dt, $season_id, $callback="'submit();'") {
      if (!$dt) { //pass in a zero date to do the calculations here 
        $dt = getScalar('uid', $season_id, 'start_date', 'tmsl_season');
      }
	    $sql=" SELECT DISTINCT s.uid, CONCAT( d.name, ' -- ', s.name ) AS nm
		FROM tmsl_season s
		INNER JOIN tmsl_division d ON s.division_uid = d.uid
		WHERE s.stop_date >= '$dt' 
		ORDER BY  s.start_date desc, d.rank";
		$arrSeasons=buildSimpleSQLArr("uid", "nm", $sql);
            
           return getSelect("season_id", $arrSeasons, array(0=>"--Select a Season--"), $season_id, "onchange=$callback");	    
    }

    function getTeamDropdown ($season_id, $team_id, $callback="'submit();'") {
      $sql = "SELECT tname, team_uid FROM tmsl_team_season ts WHERE season_uid = $season_id ORDER BY tname";
      $arrTeams = buildSimpleSQLArr("team_uid", "tname", $sql);
      return getSelect("team_id", $arrTeams, array(0=>"--Select a Team--"), $team_id, "onchange=$callback");
    }

    function getPlayerDropdown ($season_id, $team_id, $player_id, $callback="'submit();'") {
      $sql = "SELECT player_uid, CONCAT(lname, ', ', fname) AS p_name FROM tmsl_player_team pt INNER JOIN tmsl_player p ON pt.player_uid = p.uid 
         WHERE season_uid = $season_id AND team_uid=$team_id ORDER BY p_name";
      $arrPlayers = buildSimpleSQLArr("player_uid", "p_name", $sql);
      return getSelect("player_id", $arrPlayers, array(0=>"--Select a Player--"), $player_id, "onchange=$callback");
    }

  function getRegistrationStatus ($uid) {
    $sql="SELECT pay_pending, pt.registered, tname, pt.season_uid, balance, waiver_signed, pt.team_uid FROM tmsl_player_team pt 
      INNER JOIN tmsl_season s ON s.uid=pt.season_uid 
      INNER JOIN tmsl_team_season ts ON s.uid=ts.season_uid AND ts.team_uid=pt.team_uid
      WHERE player_uid=$uid and s.stop_date > now()";
    $res=mysql_query($sql);
    while ($rec=mysql_fetch_assoc($res))
      $arr[] = $rec;
    return $arr;      
  }

  function isCurrentlySuspended($uid) {
  	  $sql="select date_format(stop_date,'%m/%d/%y') as stop_date from tmsl_suspended where player_uid=$uid and stop_date > now()";
	  $arr=dbSelectSQL($sql);
	  if (count($arr))
	  return "Suspended Until " . $arr[0]['stop_date'];
		return "Not suspended";
	}

  function updateRegStatus($uid, $team, $season) {
    $susp_status = isCurrentlySuspended($uid);
    if ($susp_status != "Not suspended") return false;
    $sql = "SELECT balance, registered, waiver_signed FROM tmsl_player_team 
      WHERE player_uid = $uid AND team_uid = $team AND season_uid = $season";
    $res = mysql_query($sql);
    $rec = mysql_fetch_assoc($res);
    foreach ($rec as $k=>$v) $$k=$v;
    if ($registered) {
      if ($waiver_signed and !$balance) $uarr = array('registered'=>2, 'pay_pending'=>0);
      else $uarr = array('registered'=>1);
      dbUpdate('tmsl_player_team', $uarr, array('player_uid' => $uid, 'team_uid' => $team, 'season_uid' => $season));
    }
    return true;
  }
		 	
?>
