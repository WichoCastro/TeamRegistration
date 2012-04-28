<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
	if ($fld=='comments') $val=mysql_real_escape_string($val);
	$ret=0;
	if ($tbl) {
		if ($tbl=='lu_values') $key='idx';
		if ($tbl=='tmsl_player_team') $whr=array('player_uid'=>$uid, 'team_uid'=>$team_id);
		elseif ($tbl=='tmsl_team_manager')
			if ($all)
				$whr=array('season_uid'=>$season_id, 'team_uid'=>$team_id);
			else
				$whr=array('season_uid'=>$season_id, 'team_uid'=>$team_id, 'user_uid'=>$player_id);
		else $whr=array('uid'=>$uid);
		$ret=dbUpdate($tbl, array($fld=>"$val"), $whr);
	}
	print $ret;
	if (!$ret) print mysql_error();
?>
