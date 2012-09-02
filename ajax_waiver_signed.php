<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
	
		$ret=dbUpdate($tbl, array($fld=>"$val"), $whr);
	
	print $ret;
	if (!$ret) print mysql_error();
?>
