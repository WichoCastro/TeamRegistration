<?
	include_once("sql.inc");
	include_once("functions.php");
	$u=$_GET['u'];
	$u1=$_GET['u1'];
	$s=$_GET['s'];
	$u=sha1($u1);
	if ($u1 && $s) {
		$sql="UPDATE tmsl_user set pwd='$u' WHERE name='$u1' and pwd='$s'";
		$res=mysql_query($sql);
	}
	print "ok";print "<script>";
	print "alert('Your password has been reset to $u1');";
	print "window.location='index.php';";
	print "</script>";
?>
