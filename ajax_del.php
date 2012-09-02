<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
	if ($tbl=='tmsl_card') {
		$ret=dbDelete($tbl, array('uid'=>$uid));
	}
	if ($tbl=='tmsl_team_manager') {
		$ret=dbDelete($tbl, array('user_uid'=>$player_id, 'team_uid'=>$team_id, 'season_uid'=>$season_id));
	}
	if ($ret) print mysql_error();
	print $ret;
?>
