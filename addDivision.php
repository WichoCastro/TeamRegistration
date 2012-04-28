<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($division_name) {
			$sql="INSERT tmsl_division (name) VALUES ('".mysql_real_escape_string($division_name)."')";
			mysql_query($sql) or die("That name already exists");
			if (!$url) $url='roster.php';
			$str="Location:$url";
			header($str);
		}else{
			print "<html>";
			print "<head>";
			print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
			print "</head>";
			print "<body>";
			print $banner;
			print $navBar;
			print "<div id='ttlBar'>Divisions</div>";
			print "<div id='mainPar'>";
			$arr=dbSelect('tmsl_division', array('name'), '', array('name'));
			foreach ($arr as $rec)
				print $rec['name']."<br/>";
			print "<form method='post'>";
			print "<input type='hidden' name='url' value='".$_SERVER['HTTP_REFERER']."'>";
			print "<table align='center'>";
			print "<tr><td>New Division Name:</td><td><input type='text' name='division_name' style='width:200px'></td></tr>";
			print "<tr><td colspan='2' align='center'><input type='submit' value='ok'></td></tr>";
			print "</table>";
			print "</form>";
			print "</div>";
			print "</body>";
			print "</html>";
		}
	}else include("login.php");
?>
