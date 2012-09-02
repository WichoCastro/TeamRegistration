<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if (!$player_id) $player_id = $_SESSION['logon_uid'];
		if ($_SESSION['mask'] & 4) $adm=true;else $adm=false;
		$max_sz=2*1024*1024;

		//if (!hasPermissionEditPlayer($_SESSION['mask'], $player_id) || !$player_id) header("Location:roster.php");

		if ($_FILES['upload_file']) {
			$tmp_file =  $_FILES['upload_file'];
			$tmp_nm = $tmp_file['tmp_name'];
			$orig_nm = $tmp_file['name'];
			$ext=strtolower(substr($orig_nm,-3));
			if (strcasecmp($ext,'jpg') && strcasecmp($ext,'png')) {
				$msg = "This site only accepts images of type jpg or png.  Yours is '$ext'.<br/>";
			} else {
				$sz=filesize($tmp_nm);
				if ($sz > $max_sz) {
					$msg = "Filesize is $sz; please upload a file less than $max_sz<br/>";
				} else {
					$targ = "ids/" . str_pad($player_id, 5, "0", STR_PAD_LEFT) . "." . $ext;
					if (file_exists($targ))  {
						$re_name = substr($targ, 0, 9). "_" . date('Ymdhis', filectime($targ)) . ".$ext";
						rename($targ, $re_name);
					}
					$success=copy($tmp_nm, $targ);
				}

				if ($success) {
					dbUpdate('tmsl_player', array('DOB_validated'=>2), array('uid'=>$player_id));
					header("Location:regStatus.php?player_id=$player_id");
				} else $msg .= "Operation failed copying $tmp_nm to $targ";
			}
		}

		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Proof of Age</div>";
		print "<div id='mainPar'>";
		if ($msg) print "<span id='updateMsg'>$msg<br/></span><br/>";
		print "Use this page to upload a scanned copy of a document which proves " . getUserName($player_id) . "'s date of birth, such as a driver's license. The file must be a jpg or png and be less than 1MB.";

		print "<br/><br/><form enctype='multipart/form-data' method='post'>";
		print "File: <input type='file' name='upload_file' value='".$_POST['upload_file']."' size='80'>";
		print "<br/><br/><input type='submit' value='OK'>";
		print "<input type='button' value='Cancel' onclick='window.location=\"regStatus.php?player_id=$player_id\"'>";
		print "</form>";

		print "<div>";
			$p_uid=str_pad($player_id, 5, "0", STR_PAD_LEFT);
			if (file_exists("ids/$p_uid.jpg")) $img="$p_uid.jpg";
			if (!$img) if (file_exists("ids/$p_uid.png")) $img="$p_uid.png";
			if (!$img) $img='image_not_found.jpg';
			print "<a href='ids/$img'><img src='ids/$img' alt='doc' width='200' border='0'></a>";
		print "</div>";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
