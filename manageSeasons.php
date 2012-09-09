<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($addSeasonSubmit) {
			if (!strtotime($start_date)) $errMsg="Please enter a valid start date ('$start_date' is not a valid date)";
			if (!strtotime($stop_date)) $errMsg="Please enter a valid start date ('$stop_date' is not a valid date)";
			if (!strtotime($last_day_team)) $errMsg="Please enter a valid date for 'Last Day to Register Team' ('$last_day_team' is not a valid date)";
			if (!strtotime($last_day_player)) $errMsg="Please enter a valid date for 'Last Day to Register a Player' ('$last_day_player' is not a valid date)";
			if (!strtotime($halfway_date)) $errMsg="Please enter a valid date for 'Season Midpoint' ('$halfway_date' is not a valid date)";
			if (!$season_name) $errMsg="Please enter a name";
			if (!$league_id) $errMsg="Please select a division";
			if ($errMsg) $addSeason=1;
			else {
				$start_date_sql=date('Y-m-d', strtotime($start_date));
				$stop_date_sql=date('Y-m-d', strtotime($stop_date));
				$last_day_team_sql=date('Y-m-d', strtotime($last_day_team));
				$last_day_player_sql=date('Y-m-d', strtotime($last_day_player));
				$halfway_date_sql=date('Y-m-d', strtotime($halfway_date));
				if ($season_id) $sql="UPDATE tmsl_season SET
					name = '$season_name', division_uid=$league_id, start_date='$start_date_sql', stop_date='$stop_date_sql',
					min_players = '$min_players', max_players=$max_players, last_day_team='$last_day_team_sql', last_day_player='$last_day_player_sql',
					halfway_date='$halfway_date_sql', cost_per_player='$cost_per_player'
					WHERE uid=$season_id";
				else $sql="INSERT INTO tmsl_season (name, division_uid, start_date, stop_date, last_day_team, last_day_player, halfway_date, min_players, max_players, cost_per_player)
					VALUES ('$season_name', $league_id, '$start_date_sql', '$stop_date_sql', '$last_day_team_sql', '$last_day_player_sql', '$halfway_date_sql', '$min_players',
					'$max_players', '$cost_per_player')";
				mysql_query($sql) or die("Error in $sql");
				if (!$season_id) $season_id=mysql_insert_id();
				$editSeason=1;
			}
		}
		if ($editSeason) {
			$sql="SELECT * FROM tmsl_season WHERE uid=$season_id";
			//print $sql;
			$res=mysql_query($sql);
			$rec=mysql_fetch_array($res);
			$season_name=$rec['name'];
			$league_id=$rec['division_uid'];
			$start_date=$rec['start_date'];
			$stop_date=$rec['stop_date'];
			$last_day_team=$rec['last_day_team'];
			$last_day_player=$rec['last_day_player'];
			$halfway_date=$rec['halfway_date'];
			$min_players=$rec['min_players'];
			$max_players=$rec['max_players'];
			$cost_per_player=$rec['cost_per_player'];
			$addSeason=1;
			$actn="Edit";
		}
		if ($addSeason) {
			if (!$actn) $actn="New";
			$addSeasonForm = "$actn Season Details:<br/><form method='post' name='frm'>";
			$arrLeagues=buildSimpleSQLArr("uid", "name", "SELECT l.uid, l.name FROM tmsl_division l");
			$addSeasonForm .= "<div class='error'>$errMsg</div>";
			$addSeasonForm .= "<table align='center'>";
			$addSeasonForm .= "<tr><td>Division:</td><td>";
			//<a href='addDivision.php'><img src='images/add.png' alt='Add' title='Add a new division' border='0'></a></td><td>";
			$addSeasonForm .= getSelect("league_id", $arrLeagues, array(0=>"--Select--"), "$league_id", "");
			//$addSeasonForm .= "</td></tr><tr><td>New League:</td><td><input type='text' name='new_league_name' style='width:200px' value='$new_league_name'></td></tr>";
			$addSeasonForm .= "</td></tr><tr><td>Name of Season:</td><td><input type='text' name='season_name' style='width:200px' value='$season_name'></td></tr>";
			$addSeasonForm .= "<tr><td>Starts:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
			$addSeasonForm .= "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='The first day of games'></a></td></tr> ";
			$addSeasonForm .= "<tr><td>Stops:</td><td><input type='text' value='$stop_date' name='stop_date' id='stop_date'>";
			$addSeasonForm .= "<a href='#' onClick='cal.select(document.forms[\"frm\"].stop_date,\"anchor2\",\"MM/dd/yyyy\"); return false;' NAME='anchor2' ID='anchor2'><img src='calendar.png' border='0' alt='cal' title='The last day of games'></a></td></tr> ";
			$addSeasonForm .= "<tr><td>Minimum # Players to Register:</td>";
			$addSeasonForm .= "<td><input type='text' size='5' name='min_players' value='$min_players'></td></tr>";
			$addSeasonForm .= "<tr><td>Maximum # Players per Team:</td>";
			$addSeasonForm .= "<td><input type='text' size='5' name='max_players' value='$max_players'></td></tr>";
			$addSeasonForm .= "<tr><td>Last Day to Register Team:</td><td><input type='text' value='$last_day_team' name='last_day_team' id='last_day_team'>";
			$addSeasonForm .= "<a href='#' onClick='cal.select(document.forms[\"frm\"].last_day_team,\"anchor3\",\"MM/dd/yyyy\"); return false;' NAME='anchor3' ID='anchor3'><img src='calendar.png' border='0' alt='cal' title='The last day a team may be added to the season'></a></td></tr> ";
			$addSeasonForm .= "<tr><td>Last Day to Register a Player:</td><td><input type='text' value='$last_day_player' name='last_day_player' id='last_day_player'>";
			$addSeasonForm .= "<a href='#' onClick='cal.select(document.forms[\"frm\"].last_day_player,\"anchor4\",\"MM/dd/yyyy\"); return false;' NAME='anchor4' ID='anchor4'><img src='calendar.png' border='0' alt='cal' title='The last day a team may add a player'></a></td></tr> ";
			$addSeasonForm .= "<tr><td>Season Midpoint:</td><td><input type='text' value='$halfway_date' name='halfway_date' id='halfway_date'>";
			$addSeasonForm .= "<a href='#' onClick='cal.select(document.forms[\"frm\"].halfway_date,\"anchor5\",\"MM/dd/yyyy\"); return false;' NAME='anchor5' ID='anchor5'><img src='calendar.png' border='0' alt='cal' title='After Season Midpoint, cost per player drops by half (plus ten)'></a></td></tr> ";

			$addSeasonForm .= "<tr><td>Cost Per Player:</td>";
			$addSeasonForm .= "<td><input type='text' size='5' name='cost_per_player' value='$cost_per_player'></td></tr>";
			$addSeasonForm .= "<tr><td align='center' colspan='2'><input type='submit' value='OK' name='addSeasonSubmit'></td></tr>";
			$addSeasonForm .= "</table>";
			if ($editSeason) $addSeasonForm .= "<input type='hidden' name='season_id' value='$season_id'>";
			$addSeasonForm .= "</form>";
		}elseif ($change_active && !$addSeasonSubmit) {
			$sql = "UPDATE tmsl_season SET active=0";
			mysql_query($sql);
			foreach ($_POST as $key=>$val) {
				if (!strcmp('make_active', substr($key, 0, 11))) {
					$sql = "UPDATE tmsl_season SET active=1 WHERE uid=$val";
					mysql_query($sql);
					//print $sql;
				}
			}
		}
		if (!$start_date) $start_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')-7,date('Y')));
		if (!$stop_date) $stop_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')+7,date('Y')));
		$start_date_sql=date('Y-m-d', strtotime($start_date));
		$stop_date_sql=date('Y-m-d', strtotime($stop_date));
		print $beginning;
		print "<div id='ttlBar'>Season Management</div>";
		print "<div id='mainPar'>";
		if ($addSeason) print $addSeasonForm;
		//print "Select an 'active season' for each division.";
		$sql = "SELECT s.name, s.uid, DATE_FORMAT(start_date,'%m/%d/%Y') as start_d, DATE_FORMAT(stop_date,'%m/%d/%Y') as stop_d, l.name as league_name, l.uid as division_uid,
			case when stop_date >= now() then 0 else 1 end as closed
			FROM tmsl_season s INNER JOIN tmsl_division l ON s.division_uid=l.uid
			ORDER BY start_date DESC, l.rank, s.name";
		$res=mysql_query($sql);
		print "<form method='post' action='manageSeasons.php'>";
		//print "<input type='hidden' name='change_active' value='1'>";
		print "<input type='hidden' name='season_id' value='$season_id'>";
		print "<table align='center' cellpadding='7' cellspacing='0'>";
		print "<tr><th>&nbsp;</th><th>Season</th><th>Division</th><th>From</th><th>Until</th></tr>";
		while ($rec=mysql_fetch_array($res)) {
			if ($prev_sd <> $rec['start_d']) print "<tr><td>&nbsp</td></tr>";
			//if ($rec['active']) {$col="bgcolor='yellow'";$ck="checked='checked'";} else {$col=""; $ck="";}
			//$activate="<input type='radio' name='make_active_".$rec['division_uid']."' value='".$rec['uid']."' onclick='submit();' $ck>";
			//if ($rec['closed']) $activate="&nbsp;";
			print "<tr $col>";
			//print "<td><input type='image' alt='Edit' src='images/pencil.png' name='editSeason' title='Edit Details' border='0'>";
			print "<td><a href='manageSeasons.php?editSeason=1&season_id=".$rec['uid']."'><img alt='Edit' src='images/pencil.png' title='Edit Details' border='0'></a>";
			//print "<input type='hidden' name='season_id' value='".$rec['uid']."'></td>";
			//print "<td>$activate</td>";
			print "<td><a href='teamSeason.php?season_id=".$rec['uid']."'>".$rec['name']."</a></td>";
			print "<td>".$rec['league_name']."</td>";
			print "<td>".$rec['start_d']."</td>";
			print "<td>".$rec['stop_d']."</td>";
			print "<tr>";
			$prev_sd = $rec['start_d'];
		}
		print "</table>";
		print "<input type='submit' value='New Season' name='addSeason'>";
		print "</form>";
		print "<input type='button' value='Add Division' onclick='window.location=\"addDivision.php\"'>";
		print "</div>";
		print $footer;
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
