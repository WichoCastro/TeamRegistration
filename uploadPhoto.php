<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($_SESSION['mask'] & 4) $adm=true;else $adm=false;
		$max_sz=1000000;

		if (!$player_id) $player_id = $_SESSION['logon_uid'];
		//if (!hasPermissionEditPlayer($_SESSION['mask'], $player_id) || !$player_id) header("Location:roster.php");
		if ($noPhotoNeeded) {
			dbUpdate('tmsl_player', array('pic_on_file'=>1), array('uid'=>$player_id));
			header("Location:roster.php");
		}
		if ($useImg) {
			$targ="main/".str_pad($player_id, 5, "0", STR_PAD_LEFT).".jpg";
			$re_name=substr($targ, 0, 10)."_".date('Ymdhis', filectime($targ)).".jpg";
			copy("main/".$useImg, $targ);
			header("Location:editPlayer.php?uid=$player_id&edit=1");
		}

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
					$targ="main/".str_pad($player_id, 5, "0", STR_PAD_LEFT).".$ext";
					if (file_exists($targ))  {
						$re_name=substr($targ, 0, 10)."_".date('Ymdhis', filectime($targ)).".$ext";
						//print "att rename to $re_name";
						rename($targ, $re_name);
					}
					$success=copy($tmp_nm, $targ);
				}

				if ($success) {
					dbUpdate('tmsl_player', array('pic_on_file'=>1), array('uid'=>$player_id));
					header("Location:basicInfo.php");
				} else $msg .= "Operation failed";
			}
		}

		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "</head>";
		print "<body>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Photo</div>";
		print "<div id='mainPar'>";
		if ($msg) print "<span id='updateMsg'>$msg<br/></span><br/>";
		print "Use this page to upload a photo, which must be a jpg or png less than 1MB.<br/>";

		print "<br/><form enctype='multipart/form-data' method='post'>";
		print "File: <input type='file' name='upload_file' value='".$_POST['upload_file']."' size='50'>";
		print "<br/><br/><input type='submit' value='OK'>";
		print "<input type='button' value='Cancel' onclick='window.location=basicInfo.php'>";
		if ($adm) print "<br/><input type='submit' name='noPhotoNeeded' value='Photo already on file'>";
		print "</form>";


		if ($h=opendir('main/')) {
			while (false !== ($file = readdir($h))) {
				if (substr($file,0,5) == str_pad($player_id, 5, "0", STR_PAD_LEFT) && strpos($file,'.') > 5)
					$str .=  "<a href='uploadPhoto.php?player_id=$player_id&useImg=$file'><img src='main/$file' ></a>";
			}
			if ($str) print "...or select an old image to use:<br/>$str";
		}else print "Unable to open dir";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
