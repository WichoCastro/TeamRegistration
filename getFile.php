<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		header("Content-type: aplication/octet-stream");
		$str="Content-disposition: attachment; filename=$nm";
		header($str);
		if (!readfile($nm)) die ("Unable to open $nm");
	}else include("login.php");
?>
