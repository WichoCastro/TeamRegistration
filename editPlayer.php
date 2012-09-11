<?
  /******Page takes team_id or not -- diff is no jersey id, and caption is diff if no team_id******/
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		//needed?
		if (!$season_id) $season_id=$_SESSION['season_uid'];
		
		$redir="Location: $url";
		
		if ($uid) 
			if (!hasPermissionEditPlayer($_SESSION['mask'], $uid)) header('Location:index.php');
		if ($_SESSION['mask'] & 128) $adm=true;else $adm=false;
		
		if ($team_id && $season_id) {
			$p = new Person($uid, $team_id, $season_id);
			$team = new Team($team_id, $season_id);
			$season = new Season($season_id);
		} elseif ($uid)
			$p = new Person($uid);
		else $uid =0;
		
		if ($DOB) {
			$DOB = date('Y-m-d', strtotime($DOB));
		}
		
		//does the user have addTmPriv?
		$player_mask = getScalar('player_uid', $uid, 'mask', 'tmsl_user');
		$usrAddTmPriv = $player_mask & 2;
		if (!$player_mask) $player_mask = 0;
		
		//must keep these 3 in sync:
		$form_fields = array("lname", "fname", "mname", "email", "addr", "city", "state", "zip", "phone", "DOB", "jersey_no");
		$person_fields = array("lastName", "firstName", "middleName", "email", "addr", "city", "state", "zip", "phone", "dob", "jersey");
		$form_labels = array("Last Name", "First Name", "Middle", "Email", "Address", "City", "State", "Zip", "Phone", "DOB", "Jersey");
		$required_fields = array("lname", "fname");
		if (!$p->dobValidated) $required_fields[] = "DOB"; 
		
		if ($upd || $ins) {
			
			//FIX -- place inside class
			if ($addTmPrivCk) {
				if(!$usrAddTmPriv){
					$player_mask +=  2;
					dbUpdate('tmsl_user', array('mask'=>$player_mask), array('player_uid'=>$uid), 0,0,true, true);
					$usrAddTmPriv = true;
				} 
			} else {
				if ($usrAddTmPriv) {
					$player_mask -= 2; 
					dbUpdate('tmsl_user', array('mask'=>$player_mask), array('player_uid'=>$uid),0,0,true, TRUE);
					$usrAddTmPriv = false;
				}	
			}
			
			foreach ($form_fields as $key=>$fld) {
				if (in_array($fld, $required_fields) && !$$fld) {
					$msg .= $form_labels[$key] . " is required.";
				}	
				$p->$person_fields[$key] = $$fld; 
			}
			
			//handle email here:
        	if ($email) $msg .= $p->addEmail($uid, $email);
			
			//Is there already a player by that name in the db?  If so, error:
			if ($ins) $player_exists=checkPlayerExistence($p->firstName,$p->middleName,$p->lastName, $p->dob);
			
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
				$p->writeBasicInfoToDB();
				$p->writeSeasonTeamInfoToDB();
				$saved=1;
			}
		}
		if ($ins) {
			if (!$msg) {
				$data = array('fname'=>$fname, 'lname'=>$lname, 'DOB'=>$DOB);
				$uid = addPlayertoDB($data);
				$p->id = $uid;
				$p->writeBasicInfoToDB();
				if ($team_id) {
					$msg=addPlayerToTeam($uid, $team_id, $season_id);
				}
				$p->writeSeasonTeamInfoToDB();
				if (strlen($msg)<=1) {
					if (!$url) $redir="Location: roster.php";else $redir="Location:$url";
					header($redir);
				}
			}
		}
		
		if ($really_del && $_SESSION['mask'] & 128) {
			dbDelete('tmsl_player', array('uid'=>$ID), true);
			header("Location:player.php");
		}
		if ($really_drop) {
			$p->drop();
			header("Location:roster.php");
		}
		if ($really_suspend) {
			$start_date=date('Y-m-d', strtotime($start_date));
			$stop_date=date('Y-m-d', strtotime($stop_date));
			$p->suspend($start_date, $stop_date, $reason);
			header($redir);
		}
		if ($addUser) $p->addPlayerToUserTbl();

		print $beginning;
		print "<div id='ttlBar'>Edit Player</div>";
		print "<div id='mainPar'>";
		print "<div id='idPar'>";
		if ($saved) print "<span id='updateMsg'>Saved</span><br/>";
		if ($msg) print "<span id='updateMsg'>$msg</span><br/>";
		$p_uid=str_pad($uid, 5, "0", STR_PAD_LEFT);
		if (file_exists("main/$p_uid.jpg")) $img="$p_uid.jpg";
		if (!$img) if (file_exists("main/$p_uid.png")) $img="$p_uid.png";
		if (!$img) $img='image_not_found.jpg';
		print "<a href='main/$img' target='_blank'><img src='main/$img' alt='photo' width='100' border='0'></a><br/>";
		if ($p->lastName) print $p->fullName;
		else print "New Player";
		if ($team_id) print " -- $team_name";
		print "</div>";
		if ($edit || $add || $view) {
			print "<form name='frm' id='frm' method='post'>";
			print "<table align='center'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			foreach ($form_labels as $key=>$display) {
				$fld = $form_fields[$key];
				$attr = $person_fields[$key];
				if ($view || ($p->dobValidated && $display == 'DOB')) $dis="disabled='disabled'"; else $dis="";
				if (in_array($fld, $required_fields)) $mand="<span style='color:red'>*</span>";
				else $mand="";
				print "<tr>";
				print "<td>";print $display . ":" . $mand;
				print "</td>";
				print "<td>";
				print "<input type='text' name='$fld' id='$fld' onfocus='select();' size='50' $dis value='";
				print $p->$attr;
				print "' />";
				print "</td>";
				print "</tr>";
			}if ($uid) {
	            $hasUsrAct = getScalar('player_uid', $uid, 'uid', 'tmsl_user');
				if ($adm) print "<tr><td>Has User account:</td><td>";
				if ($hasUsrAct) print "<a href='admin.php?uid=$uid'>Yes</a>"; 
				else {
					print "No ";
					if ($p->email) print "&nbsp;<a href='{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}&addUser=1'>create user acct</a>";
				} 
				print "</td></tr>";
	            $addTmPrivCk = $usrAddTmPriv ? 'checked' : '';
	            if ($adm && $hasUsrAct) print "<tr><td>Add Team Privilige</td><td><input type='checkbox' id='addTmPriv' name='addTmPrivCk' $addTmPrivCk></td></tr>";
			}
			print "</table>";
			if ($edit) $action="upd"; else $action="ins";
		}
		if ($del && $_SESSION['mask'] & 128) {
			print "<form name='frm' id='frm' method='post'>";
			print "<input type='hidden' name='ID' value='".$p->id."'>";
			print "<input type='submit' value='REALLY DELETE THIS PLAYER?' name='really_del' style='color:red'>";
		}elseif ($drop) {
			print "<form name='frm' id='frm' method='post'>";
			print "<input type='hidden' name='ID' value='".$p->id."'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "<input type='hidden' name='season_uid' value='$season_uid'>";
			$team = new Team($team_id, $season_id);
			print "<input type='submit' value='REALLY DROP {$p->fullName} from {$team->name}?' name='really_drop' style='color:red'>";
			print "<input type='button' value='Back to Roster' onClick='window.location=\"roster.php?team_id=$team_id&season_id=$season_id\"' class='pointer'>";
		}elseif ($suspend) {
			print "<form name='frm' id='frm' method='post'>";
			print "<input type='hidden' name='ID' value='".$p->id."'>";
			print "<input type='hidden' name='team_id' value='$team_id'>";
			print "Suspend ".$p->fullName." from: <input type='text' value='mm/dd/yyyy' name='start_date' id='start_date'>";
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
		//if ($uid) print "<input type='hidden' name='url' value='editPlayer.php?uid=$uid&edit=1&team_id=$team_id'>";
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
