<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
	if ($tbl=='tmsl_card') {
		$ret=dbDelete($tbl, array('uid'=>$uid));
	}
	if ($ret) print mysql_error();
	print $ret;
?>
