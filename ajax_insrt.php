<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
	if ($tbl=='tmsl_card') {
		mysql_query("DELETE FROM tmsl_card WHERE player_uid=$player_id AND game_uid=$game_id");
		$sql="INSERT INTO tmsl_card (player_uid, team_uid, season_uid, game_uid, card_type) VALUES ($player_id, $team_id, $season_id, $game_id, '$card_type')";
		$ret=mysql_query($sql);
	}
	if ($ret) print mysql_error();
	print $ret;
?>
