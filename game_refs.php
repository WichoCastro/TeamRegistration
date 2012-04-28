<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($sbm) {
			dbDelete('tmsl_game_assign', array('game_uid'=>$uid));
			if ($center_ref) dbInsert('tmsl_game_assign', array('user_uid'=>$center_ref, 'game_uid'=>$uid, 'edit_right'=>2));
			if ($asst1) dbInsert('tmsl_game_assign', array('user_uid'=>$asst1, 'game_uid'=>$uid, 'edit_right'=>1));
			if ($asst2) dbInsert('tmsl_game_assign', array('user_uid'=>$asst2, 'game_uid'=>$uid, 'edit_right'=>1));
		}
		print "<html>";
		print "<head>";
		print "<script>function upd_fld() {
		  			document.forms['frm'].sbm.disabled=false;
		  			document.forms['frm'].sbm.style.color='red';
		  			document.forms['frm'].sbm.title='click to save changes';
					}</script>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<style>td {font-size:0.7em;}th {font-size:0.7em;} table {border-collapse:collapse;} body {margin-top:0;}</style>";
		print "</head>";
		print "<body>";
    print "<div id='ttlBar'>TUCSON METRO SOCCER LEAGUE REFEREE ASSIGNMENTS</div>";
		print "<div ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></div>";
		print "<div id='mainPar'>";
		print "<div style='font-size:14pt'>REFEREE ASSIGNMENTS FOR GAME #$uid</div>";
		$sql="SELECT DATE_FORMAT(game_dt, '%M %e, %Y') as game_date,
						DATE_FORMAT(game_tm, '%h:%i') as game_time, game_loc as field,
						th.tname as home_team, g.team_h_score as home_goals,
						tv.tname as visitor, g.team_v_score as visitor_goals,
						s.name as season, d.name as division
						FROM tmsl_game g JOIN tmsl_team_season th ON g.season_uid=th.season_uid AND g.team_h=th.team_uid
						JOIN tmsl_team_season tv ON g.season_uid=tv.season_uid AND g.team_v=tv.team_uid
						JOIN tmsl_season s ON g.season_uid=s.uid
						JOIN tmsl_division d ON d.uid=s.division_uid
						WHERE g.uid=$uid";
		print printTable($sql);

		$sql="SELECT * FROM tmsl_game_assign where game_uid=$uid";
		$arr=dbSelectSQL($sql);
		foreach($arr as $rec) {
			if ($rec['edit_right']==2) $center_ref=$rec['user_uid'];
			elseif (!$a1) $a1=$rec['user_uid'];
			else $a2=$rec['user_uid'];
		}
		$refList=buildSimpleSQLArr('player_uid', 'name', "SELECT player_uid, name FROM tmsl_user WHERE isReferee=1 ORDER BY name");
		print "<form id='frm'>";
		print "<input type='hidden' name='uid' value='$uid'>";
		print "<table align='center'>";
		print "<tr>";
		print "<td>Center Referee:</td>";
		print "<td>".getSelect('center_ref', $refList, $arrFirstOpts=array(0=>"--Select--"), $center_ref, "onchange='upd_fld()'", $adm)."</td>";
		print "</tr>";
		print "<tr>";
		print "<td>Assistant:</td>";
		print "<td>".getSelect('asst1', $refList, $arrFirstOpts=array(0=>"--Select--"), $a1, "onchange='upd_fld()'", $adm)."</td>";
		print "</tr>";
		print "<tr>";
		print "<td>Assistant:</td>";
		print "<td>".getSelect('asst2', $refList, $arrFirstOpts=array(0=>"--Select--"), $a2, "onchange='upd_fld()'", $adm)."</td>";
		print "</tr>";
		print "<tr>";
		print "<td colspan='2' align='center'><input type='submit' name='sbm' id='sbm' disabled='true' style='color:#ccc; background-color:#ddd' title='no changes to save' value='save changes'>
			<input type='button' value='close' onclick='window.close();'></td>";
		print "</tr>";
		print "</table>";
		print "</form>";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
