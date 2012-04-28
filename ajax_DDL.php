<?
	include_once("session.php");
	include_once("functions.php");
	foreach($_POST as $key=>$dta) $$key=$dta;

	//mandatory $keyCol, $valCol, $tbl, $id,
	//optional $whr, $ordr, $firstOpts, $special, $editable, $selectedVal

	if (!$keyCol) {print "keyCol not found."; exit;}
	if (!$valCol) {print "valCol not found."; exit;}
	if (!$tbl) {print "tbl not found."; exit;}
	if (!$id) {print "id not found."; exit;}
	if (!isset($editable)) {$editable=1;}
	if (!isset($special)) {$special="";}
	if (!isset($selectedVal)) {$selectedVal="";}
	$arrFirstOpts=array();
	if ($firstOpts & 1) $arrFirstOpts[0]='--All--';

	$sql = "SELECT $keyCol, $valCol FROM $tbl $whr $ordr";

	//print $sql;

	$arrOpts = buildSimpleSQLArr($keyCol, $valCol, $sql);
	print getSelect($id, $arrOpts, $arrFirstOpts, $selectedVal, $special, $editable);
?>
