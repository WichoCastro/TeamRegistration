<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
	if ($actn == 'unsign') $val=0;
	else $val = 1;
	$ret=dbUpdate('tmsl_player_team', array('waiver_signed'=>$val), array('player_uid'=>$uid, 'team_uid'=>$team_id, 'season_uid'=>$season_id));
	updateRegStatus($uid, $team_id, $season_id);
	print $ret;
	if (!$ret) print mysql_error();
?>
