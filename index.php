<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
                print "<div id=container>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Home</div>";
		$str = "Welcome to the TMSL Registration Website. Here we hope to provide everything you need to register online.<br/><br/>";
		print "<div id='mainPar'>$str";

		print "<div id='myTeams' style='text-align:center'>";
		if ($adm){
			print "<span id='updateMsg'>You're an administrator.</span><br/>";
		}
		if ($isRef) {
			$myGames = gameList($_SESSION['logon_uid'], 'ref');
			if ($myGames) {
				print "<br/><span class='instrTtl'>Upcoming Games You Referee:</span><br/>
					<span class='instr'>Click on the
					<img src='images/report.png' alt='print roster'> icon
					for the game report</span><br/>";
				print "$myGames";
			}
			$myGames = gameList($_SESSION['logon_uid'], 'ref_past');
			if ($myGames) {
				print "<br/><span class='instrTtl'>Games You Recently Centered:</span><br/>
					<span class='instr'>Click on the
					<img src='images/report.png' alt='print roster'> icon
					for the game report</span><br/>";
				print "$myGames<br/><br/>";
			}
		}
		$myGames = gameList($_SESSION['logon_uid'], 'mgr');
		if ($myGames) {
			print "<br/><br/><span class='instrTtl'>Upcoming Games</span><br/>
				<span class='instr'>You can print the game roster from here when you see the
				<img src='images/printer.png' alt='print roster'> icon<br/>
				The icon will appear $days_before days before game date</i></span><br/>";
			print "$myGames";
		}
		print teamList($_SESSION['logon_uid']);
		
		

    	print "<span class='instrTtl'>Manage Your Registration and Data</span><br/>";
		print "<table id='basicLinks' class='rtbl' align='center'>";
    	print "<tr><td height='50px' width='300px' align='center'><a href='basicInfo.php'>Basic Info</a></td></tr>";
    	print "<tr><td height='50px' width='300px' align='center'><a href='regStatus.php'>Registration Status</a></td></tr>";
    	print "<tr><td height='50px' width='300px' align='center'><a href='payBalance.php'>Payments</a></td></tr>";
    	print "<tr><td height='50px' width='300px' align='center'><a href='playerCard.php'>Player Card</a></td></tr>";
		print "</table>";

		print "</div >"; //end myTeams
		if ($myGames || $adm) print "<br/><a href='addTeam.php'>add a new team</a>";
		print "</div >"; //end mainPar
		
    print "<div id='footer-spacer'></div>";
		print "</div >"; //end container
    print $footer;
		print "</body>";
		print "</html>";
	}else include("login.php");

	function teamList($uid) {
		$sql="SELECT CONCAT(s.name,' (',l.name,')') as s_name,  tl.tname as t_name, t.uid as team_id, s.uid as season_id,
			case registered WHEN 2 THEN 'registered' WHEN 1 THEN 'registration submitted' ELSE 'not registered' END AS reg_status
			FROM tmsl_team_manager tm
			INNER JOIN tmsl_team_season tl ON tm.team_uid=tl.team_uid
			INNER JOIN tmsl_team t ON tm.team_uid=t.uid
			INNER JOIN tmsl_season s ON tl.season_uid=s.uid and tm.season_uid=s.uid
			INNER JOIN tmsl_division l ON tl.division_uid=l.uid
			WHERE tm.user_uid=$uid AND s.stop_date > now()
			ORDER BY s.start_date desc, s_name, t_name";
		$res=mysql_query($sql);
		if (mysql_num_rows($res)) {
		  print "<br/><br/><span class='instrTtl'>Teams You Manage</span><br/>";
		  $str = "<table class='rtbl' align='center'>";
		  while ($rec=mysql_fetch_assoc($res)) {
			  $s_name=$rec['s_name'];
			  if ($s_name <> $prev_s_name) $str.= "<tr><th>$s_name</th></tr>";
			  $prev_s_name = $s_name;
			  $str.="<tr><td align='center'><a href='roster.php?team_id={$rec['team_id']}&season_id={$rec['season_id']}'>{$rec['t_name']} ({$rec['reg_status']})</a></td></tr>";
		  }
		  $str .= "</table><br/><br/>";
		}
		return $str;
	}

		function gameList($uid, $fcn) {
                        global $days_before;
			if ($fcn == 'ref')
				$sql="SELECT g.uid, team_h, team_v, game_loc, DATE_FORMAT(game_dt, '%Y-%m-%d') as game_dt, season_uid,
					DATE_FORMAT(game_tm, '%H:%i') as game_tm, edit_right,
					0 as dif, g.season_uid, -1 as grs
					FROM tmsl_game_assign ga INNER JOIN
					tmsl_game g ON ga.game_uid=g.uid
					WHERE user_uid = $uid AND game_dt >= current_date() AND edit_right=2
					ORDER BY game_dt, game_tm";
			elseif ($fcn == 'ref_past')
				$sql="SELECT g.uid, team_h, team_v, game_loc, DATE_FORMAT(game_dt, '%Y-%m-%d') as game_dt, season_uid,
					DATE_FORMAT(game_tm, '%H:%i') as game_tm, edit_right,
					0 as dif, g.season_uid, game_report_submitted as grs
					FROM tmsl_game_assign ga INNER JOIN
					tmsl_game g ON ga.game_uid=g.uid
					WHERE user_uid = $uid AND edit_right=2 AND 
					DATEDIFF(now(), game_dt) >= 0 
					AND (DATEDIFF(now(), game_dt) <= 28)
					ORDER BY game_dt, game_tm";
			elseif($fcn == 'mgr')
				$sql="SELECT g.uid, team_h, team_v, game_loc, DATE_FORMAT(game_dt, '%Y-%m-%d') as game_dt,
					DATE_FORMAT(game_tm, '%H:%i') as game_tm, 0 as edit_right,
					DATEDIFF(game_dt, now()) as dif, g.season_uid, -1 as grs
					FROM tmsl_team_manager mgr, tmsl_game g
					WHERE user_uid = $uid AND game_dt >= current_date() AND (team_h=mgr.team_uid OR team_v=mgr.team_uid)
					AND mgr.season_uid=g.season_uid
					ORDER BY game_dt, game_tm";
			$arr=dbSelectSQL($sql);
			if (empty($arr)) return "";
			$str = "<table class='rtbl' align='center'>";
			$str .= "<tr><th>Field</th><th>Date</th><th>Time</th><th>Home</th><th>Away</th><th>&nbsp</th></tr>";
			foreach ($arr as $rec) {
				if ($rec['grs'] == 0) $styl=" style='background-color:orange';"; else $styl='';
				$str .= "<tr>";
				$str .= "<td>{$rec['game_loc']}</td>";
				$str .= "<td>{$rec['game_dt']}</td>";
				$str .= "<td>{$rec['game_tm']}</td>";
				$str .= "<td>".getTeamName($rec['team_h'], $rec['season_uid'])."</td>";
				$str .= "<td>".getTeamName($rec['team_v'], $rec['season_uid'])."</td>";
				if ($fcn == 'mgr' && $rec['dif'] >= 0 && $rec['dif'] < $days_before) {
					if (hasPermission(1, $rec['team_h'], $rec['season_uid']))
						$str .= "<td><img alt='x' src='images/printer.png' style='cursor:pointer'
											onclick='window.open(\"roster_card.php?team_id={$rec['team_h']}&game_uid={$rec['uid']}\",
											 \"roster_win\",
											 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'
											title='print game card' alt='print game card' border='0'></td>";
					if (hasPermission(1, $rec['team_v'], $rec['season_uid']))
						$str .= "<td><img alt='print' src='images/printer.png' style='cursor:pointer'
											onclick='window.open(\"roster_card.php?team_id={$rec['team_v']}&game_uid={$rec['uid']}\",
											 \"roster_win\",
											 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'
											title='print game card' alt='print game card' border='0'></td>";
				} elseif ($rec['edit_right'] > 1) {
					$str .= "<td $styl><img src='images/report.png' title='fill out game report' alt='fill out game report' border='0'
										style='cursor:pointer'
										onclick='window.open(\"game_report.php?uid={$rec['uid']}\",
										 \"game_report_win\",
										 \"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\")'></td>";
				} elseif ($rec['edit_right'] == 1) $str .= "<td>Assistant</td>";
				$str .= "</tr>";
			}
			$str .= "</table>";
			return $str;
	}
?>
