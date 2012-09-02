<?
  /******Page takes team_id or not -- diff is no jersey id, and caption is diff if no team_id******/
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($_SESSION['mask'] & 4) $adm=true;else $adm=false;
		error_reporting(0);
		if ($_GET['uid']) $uid=$_GET['uid'];
		if ($_GET['edit']) $edit=$_GET['edit'];
		if ($_GET['add']) {$uid=0;}
		//if (!$team_id) $team_id=$_SESSION['team_uid'];
		if (!$season_id) $season_id=$_SESSION['season_uid'];
		$redir="Location: $url";
		if (!is_numeric($Jersey)) $Jersey=0;
		if ($upd || $ins) {
			foreach ($arrPlayerFields2 as $colName=>$display) {
				if (!strcmp(substr($colName,0,5), 'DATE_')) {
					$colName=substr($colName, 12, strpos($colName, ',')-12);
					$$display=strtotime($$display);
					if (!strcmp('DOB', $display)) {
						if (!$DOB) $msg.="DOB=$DOB.Please enter a birthdate.";
						//if less than 17, error:
						if ((time()-$DOB)/(365.22*24*3600) < 17) $msg.="Players must be at least 17 to be entered into the database. Ese guey tiene ".((time()-$DOB)/(365.22*24*3600) < 17)."DOB=".date('Y-m-d',$DOB);
					}
					if ($$display) $$display=date('Y-m-d', $$display);
					else $$display='';
				}
				//if (strcmp($colName, 'jersey_no'))
				if (strcmp($colName, 'DOB_validated') && strcmp($colName, 'jersey_no') && strcmp($colName, 'p.uid') && strcmp($colName, 'p.dateJoinedTMSL')) {
					$fldArr[]=substr($colName,2);
					$valArr[]=$$display;
					$setArr[]="$colName='".trim($$display)."'";
					$col = substr($colName,2);
					$data[$col] = trim($$display);
				}
			}
			if (!$LastName) $msg .= "<br/>Please enter a last name";
			if (!$FirstName) $msg .= "<br/>Please enter a first name";
			//Is there already a player by that name in the db?  If so, error:
			//$player_exists=getScalar('CONCAT(fname,mname,lname)', $FirstName.$Middle.$LastName, 'uid', 'tmsl_player');
			if ($ins) $player_exists=checkPlayerExistence($FirstName,$Middle,$LastName, $DOB);
			//if ($player_exists && $player_exists != $ID) $msg.="There is already a player by that name in the database.";
			if (!$confirm && $player_exists[0]) {
				if ($player_exists[1] == 'There is already a player by that name in the database') $msg = $player_exists[1];
				else {
					$msg = $player_exists[1];
					foreach ($player_exists[2] as $potential_player) {
						$msg .= "<br/><a href='editPlayer.php?view=1&team_id=$team_id&uid={$potential_player['uid']}'>{$potential_player['fname']} {$potential_player['lname']}</a>";
					}
					//print_r($_POST);
					foreach($_POST as $k=>$v) if ($v) $postvars.="&$k=$v";
					//print "$postvars";
					$msg .= "<br/><input type='button' onclick='window.location=\"{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}{$postvars}&confirm=1\"' value='This really is a new player -- add to the database'";
				}
			}
		}
		if ($upd) {
			if (!$msg) {
				$sql="UPDATE tmsl_player p SET ".implode(", ",$setArr)." WHERE uid=$ID";
				mysql_query($sql) or die("ERROR: $sql");
				if ($Jersey) {
					$sql="UPDATE tmsl_player_team SET jersey_no = $Jersey WHERE player_uid=$ID AND team_uid=$team_id AND season_uid=$season_id";
					mysql_query($sql) or die("ERROR: $sql");
				}
				$saved=1;
			}
		}
		if ($ins) {
			if (!$msg) {
				//$sql="INSERT tmsl_player (".implode(", ",$fldArr).") VALUES ('".implode("', '",$valArr)."')";
				//mysql_query($sql) or die("ERROR: " . mysql_error());
				//$player_id = mysql_insert_id();
        //print $sql;exit;
				$player_id = addPlayertoDB($data);
				if ($team_id) {
					$msg=addPlayerToTeam($player_id, $team_id, $season_id);
				}
				if (strlen($msg)<=1) {
					if (!$url) $redir="Location: roster.php";else $redir="Location:$url";
					header($redir);
				}
			}

		}
		if ($really_del && $_SESSION['mask'] & 4) {
			dbDelete('tmsl_player', array('uid'=>$ID), true);
			header("Location:player.php");
		}
		if ($really_drop) {
			$arr=dbSelectSQL("SELECT registered FROM tmsl_player_team WHERE player_uid=$ID AND team_uid=$team_id AND season_uid=$season_id");
			$reg=$arr[0]['registered'];
			dbInsert('tmsl_dropped', array('player_uid'=>$ID, 'team_uid'=>$team_id, 'season_uid'=>$season_id, 'drop_date'=>date('Y-m-d'), 'registered'=>$reg), true, true);
			dbDelete('tmsl_player_team', array('player_uid'=>$ID, 'team_uid'=>$team_id, 'season_uid'=>$season_id), true);
			header("Location:roster.php");
		}
		if ($really_suspend) {
			$start_date=date('Y-m-d', strtotime($start_date));
			$stop_date=date('Y-m-d', strtotime($stop_date));
			//$sql="INSERT INTO tmsl_suspended (player_uid, team_uid, reason, start_date, stop_date) VALUES ($ID, $team_id, '$reason', '$start_date', '$stop_date')";
			$sql="INSERT INTO tmsl_suspended (player_uid, reason, start_date, stop_date) VALUES ($ID, '$reason', '$start_date', '$stop_date')";
			mysql_query($sql) or die("ERROR: $sql");
			header($redir);
		}
		$fieldArr=$arrPlayerFields2;
		if (!$team_id) $fieldArr=$arrPlayerFields3;
		if ($view) {$hideField["Email"]=8;$hideField["Address"]=8;$hideField["City"]=8;$hideField["State"]=8;$hideField["Zip"]=8;$hideField["Phone"]=8;}
		if ($team_id) {$team_nm=getTeamName($team_id, $season_id);}
		if ($uid) {
			if (!$team_id) {
				foreach ($fieldArr as $colName=>$display)
					$flds[]="$colName AS '$display'";
				$fldStr=implode(", ",$flds);
				$sql="SELECT $fldStr
					FROM tmsl_player p
					WHERE p.uid=$uid";
			} else {
				foreach ($fieldArr as $colName=>$display)
					$flds[]="$colName AS '$display'";
				$fldStr=implode(", ",$flds);
				$sql="SELECT $fldStr
					FROM tmsl_player p LEFT OUTER JOIN tmsl_player_team pt ON p.uid=pt.player_uid
					LEFT OUTER JOIN tmsl_suspended sus ON p.uid=sus.player_uid AND pt.team_uid=sus.team_uid
					WHERE p.uid=$uid "; //AND pt.team_uid=$team_id";
			}
			$res=mysql_query($sql) or die("err in $sql");
			$rec=mysql_fetch_array($res);
			foreach ($fieldArr as $colName=>$display)
				$player[$display]=$rec[$display];
		}
		print "<html>";
		print "<head>";
		echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Edit Player</div>";
		print "<div id='mainPar'>";
		print "<div id='idPar'>";
		if ($saved) print "<span id='updateMsg'>Saved</span><br/>";
		if ($msg) print "<span id='updateMsg'>$msg</span><br/>";
		$p_uid=str_pad($uid, 5, "0", STR_PAD_LEFT);
		if (file_exists("main/$p_uid.jpg")) $img="$p_uid.jpg";
		if (!$img) if (file_exists("main/$p_uid.png")) $img="$p_uid.png";
		if (!$img) $img='00000.jpg';
		print "<a href='main/$img' target='_blank'><img src='main/$img' alt='photo' width='100' border='0'></a><br/>";
		if ($player['LastName']) print $player['FirstName']." ".$player['Middle']." ".$player['LastName'];
		else print "New Player";
		if ($team_id) print " -- $team_name";
		print "</div>";
		if ($edit || $add || $view) {
			print "<form name='frm' id='frm' method='post'>";
			print "<table align='center'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			foreach ($fieldArr as $colName=>$display) {
				if ($view) $dis="disabled='disabled'"; else $dis="";
				if (!strcmp(substr($colName,0,5), 'DATE_'))
					if ($player["DOB_validated"]) $dis="disabled='disabled'";
				if (in_array($display, $mandatoryFields)) $mand="<span style='color:red'>*</span>";
				else $mand="";
				if (empty($player[$display])) $player[$display]=$$display;
				if ($textArea[$display])
					print "<tr><td colspan='2'>$display:<br/><textarea name='$display' id='$display' rows='2' cols='40'>".$player[$display]."</textarea></td></tr>";
				else {
					if ($hideField[$display]) {$type="hidden";}
					else {$type="text";print "<tr><td>$display$mand:</td><td>";}
					print "<input type='$type' name='$display' id='$display' onfocus='select();' size='50' $dis value=\"".$player[$display]."\">";
					if ($dis) print "<input type='hidden' name='$display' id='$display' value=\"".$player[$display]."\">";
					if ($type="text") print "</td></tr>";
				}
			}
			print "</table>";
			if ($edit) $action="upd"; else $action="ins";
		}
		if ($del && $_SESSION['mask'] & 4) {
			print "<form name='frm' id='frm' method='post'>";
			print "<input type='hidden' name='ID' value='".$player['ID']."'>";
			print "<input type='submit' value='REALLY DELETE THIS PLAYER?' name='really_del' style='color:red'>";
		}elseif ($drop) {
			print "<form name='frm' id='frm' method='post'>";
			print "<input type='hidden' name='ID' value='".$player['ID']."'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "<input type='hidden' name='season_uid' value='$season_uid'>";
			print "<input type='submit' value='REALLY DROP THIS PLAYER?' name='really_drop' style='color:red'>";
		}elseif ($suspend) {
			print "<form name='frm' id='frm' method='post'>";
			print "<input type='hidden' name='ID' value='".$player['ID']."'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "Suspend ".$player['First_Name']." ".$player['Last_Name']." from: <input type='text' value='mm/dd/yyyy' name='start_date' id='start_date'>";
			print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a> ";
			print " until: <input type='text' value='mm/dd/yyyy' name='stop_date' id='stop_date'>";
			print "<a href='#' onClick='cal.select(document.forms[\"frm\"].stop_date,\"anchor2\",\"MM/dd/yyyy\"); return false;' NAME='anchor2' ID='anchor2'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a><br/>";
			print "Reason for suspension: <textarea name='reason' rows='4' cols='60' valign='top'></textarea><br/>";
			print "<input type='submit' value='SUSPEND' name='really_suspend' style='color:red'>";
		}else {
			if (!$view) print "<input type='submit' value='Save' name='$action' class='pointer'>";
			if ($team_id) print "<input type='button' value='Back to Roster' onClick='window.location=\"roster.php?team_id=$team_id&season_id=$season_id\"' class='pointer'>";
			if ($adm && !$view) print "<input type='submit' value='Suspend' name='suspend' class='pointer'>";
		}
		if ($uid) print "<input type='hidden' name='url' value='editPlayer.php?uid=$uid&edit=1&team_id=$team_id'>";
		if ($edit) print "<br/><a href='uploadPhoto.php?player_id=$uid'>Upload New Photo</a>";
		if (!$add) {
			print "<br><br><a href='playerHistory.php?uid=$uid'><image src='images/history.png' alt='(icon)' border='0' title='Click for Full History'></a><span style='text-decoration:underline'>History (last 2 years)</span>
				<br>".getLatestTeams($uid);
			if ($adm) {
				print "<br/><span style='text-decoration:underline'>Recent Suspension History:</span><br/>";
				$s_hist = suspensionHistory($uid);
				if (empty($s_hist)) print "-none-";
				else foreach($s_hist as $fld=>$val) {
					if ($adm) print "<a href='reportSuspended.php?edit=1&s_uid={$val['uid']}''><img alt='Suspension Details' src='images/pencil.png' title='Suspension Details' border='0'></a>";
					print "{$val['start_date']} - {$val['stop_date']} {$val['reason']}<br/>";
				}
			}
		}
		print "</form>";
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
