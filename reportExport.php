<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		$include_csv_hdrs = false;
		if (!$start_date) $start_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')-14,date('Y')));
		if (!$stop_date) $stop_date=date('m/d/Y', mktime(0,0,0,date('m'),date('d')+1,date('Y')));
		$start_date_sql=date('Y-m-d', strtotime($start_date));
		$stop_date_sql=date('Y-m-d', strtotime($stop_date));
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "</head>";
		print "<body>";
		echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Report: Export</div>";

		print "<div id='mainPar'>";
		print "<form name='frm' id='frm' method='post'>";
		print "<table border='1' align='center'>";
		print "<tr><td>Players Added Starting:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		print "<tr><td>Through:</td><td><input type='text' value='$stop_date' name='stop_date' id='stop_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].stop_date,\"anchor2\",\"MM/dd/yyyy\"); return false;' NAME='anchor2' ID='anchor2'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a></td></tr>";
		$arrTeams=buildSimpleSQLArr("uid", "name", "SELECT t.uid, tl.tname as name FROM tmsl_team t INNER JOIN tmsl_team_season tl ON t.uid=tl.team_uid WHERE stop_date = '0000-00-00'");
		print "<tr><td>Team:</td><td>";
		print getSelect("team_id", $arrTeams, array(0=>"Any"), $team_id, "");
		print "</td></tr>";
		if ($all_players) $ck1="checked='true'";
		else $ck0="checked='true'";
		print "<tr><td colspan='2' align='center'><input type ='radio' name='all_players' value='0' $ck0>New Players Only  ";
		print "<input type ='radio' name='all_players' value='1' $ck1>All Players</td></tr>";
		print "<tr><td colspan='2' align='center'><input type='submit' name='srch' value='go' class='pointer'></td></tr>";
		print "</table>";
		print "</form>";
		if ($srch) {
			$arrCSVFields=array(
				"p.lname"=> "LastName",
				"p.fname" => "FirstName",
				"p.mname" => "Middle",
				"p.addr" => "Address",
				"p.city" => "City",
				"p.state" => "State",
				"p.zip" => "Zip",
				"p.phone" => "Phone",
				"p.email" => "Email",
				"DATE_FORMAT(p.DOB, '%m/%d/%Y')" => DOB,
				"DATE_FORMAT(p.dateJoinedTMSL, '%m/%d/%Y')" => dateJoinedTMSL);
			foreach($arrCSVFields as $key=>$val) {
				$tbl_hdrs.="<th>$val</th>";
				$csv_hdrs_arr[]=$val;
				$colArr[]="$key as $val";
			}
			$sql="SELECT DISTINCT ".implode(",", $colArr).", pic_on_file as pic, p.uid as player_id
				FROM tmsl_player p LEFT JOIN tmsl_player_team pt ON pt.player_uid=p.uid
				LEFT JOIN tmsl_team t ON pt.team_uid=t.uid WHERE p.dateJoinedTMSL <= '$stop_date_sql' AND p.dateJoinedTMSL >= '$start_date_sql'";
			if ($all_players) $sql="SELECT DISTINCT ".implode(",", $colArr).", pic_on_file as pic, p.uid as player_id
				FROM tmsl_player p LEFT JOIN tmsl_player_team pt ON pt.player_uid=p.uid
				LEFT JOIN tmsl_team t ON pt.team_uid=t.uid WHERE pt.start_date<= '$stop_date_sql' AND pt.start_date >= '$start_date_sql'";
			if ($team_id) $sql .= " AND pt.team_uid=$team_id";
			$sql.=" ORDER BY t.name, lname";
			$res=mysql_query($sql);
			$fname="scratch/tmsl_requested_data.csv";
			//if (file_exists($fname)) unlink($fname);
			$f=fopen($fname,'w');
			if ($include_csv_hdrs) fwrite($f, implode(',', $csv_hdrs_arr)."\n");
			print "<a href='getFile.php?nm=$fname' target='_blank'>csv file</a><br/>";
			print "<table border='1' style='border-collapse:collapse' cellspacing='0' cellpadding='5' align='center'>";
			print "<tr><th>&nbsp</th>$tbl_hdrs</tr>";
			while ($rec=mysql_fetch_Array($res)) {
				$haveData=true;
				$valArr=array();
				print "<tr>";
				print "<td>";
				if ($rec['pic']) {
					$phot="main/".str_pad($rec['player_id'], 5, "0", STR_PAD_LEFT).".jpg";
					print "<a href='$phot'><img src='images/camera.png' alt='camera' title='View Photo' border='0'></a>";
				}
				print "</td>";
				foreach($arrCSVFields as $key=>$val) {
					if ($val == "Phone") {$v=preg_replace("/[^0-9]/","",$rec[$val]);if (strlen($v)==7) $v="520".$v;}
					else $v=$rec[$val];
					 print "<td>$v</td>";
					 $valArr[]=$v;
				}
				print "</tr>";
				fwrite($f, "\"".implode('","', $valArr)."\"\n");
			}
			if (!$haveData) print "<tr><td colspan='".count($arrCSVFields)."'>No data.</td></tr>";
			print "</table>";
		}
		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
