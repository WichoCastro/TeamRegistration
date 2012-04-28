<?
  include_once("session.php");
  $lines = array();
  $parsedLines = array();
  $arrTokens = array();
  $uf_file     = 'u.csv';
  $season_id=2;
  $division_id=2;
  $handle = fopen($uf_file, 'r');
  $contents = fread($handle, 1000000);
  $lines = preg_split("/(\r\n|\r|\n)/", $contents);
  foreach ($lines as $curline) {
		$parsedLines[]=$curline;
	}
  //now weve read the file; time to process the data
  //first, get the token array, where col X, row Y of the CSV
  //is represented by arrTokens[Y][X]
  if (is_array($parsedLines)) {
    foreach ($parsedLines as $csv) {
      $rw=explode(',', trim($csv));
      $arrTokens=$rw;

			$lname=trim($arrTokens[0]);
			$fname=trim($arrTokens[1]);
			$mname=trim($arrTokens[2]);
			$addr=trim($arrTokens[3]);
			$city=trim($arrTokens[4]);
			$state=trim($arrTokens[5]);
			$zip=trim($arrTokens[6]);
			$phone=trim($arrTokens[7]);
			$dob=trim($arrTokens[8]);
			$dob=date('Y-m-d',strtotime($dob));
			$doe=trim($arrTokens[9]);
			$doe=date('Y-m-d',strtotime($doe));
			$email=trim($arrTokens[10]);
			/*
			print "team is $team";
			if ($team != $last_team) {
				$sql="INSERT INTO tmsl_team (name) VALUES ('$team')";
				mysql_query($sql);
				print "$sql\n";
				$team_id=mysql_insert_id();
				$sql="INSERT INTO tmsl_team_season (team_uid, division_uid, season_uid) VALUES ($team_id, $division_id, $season_id)";
				mysql_query($sql);
				print "$sql\n";
			}
			$last_team=$team;
			*/
			//print_r($arrTokens);

			if ($lname) {
				$sql="INSERT INTO tmsl_player_orig (fname, mname, lname, addr, city, state, zip, phone, dob, dateJoinedTMSL, email) VALUES ('$fname',' $mname',' $lname',' $addr',' $city',' $state',' $zip',' $phone', '$dob', '$doe', '$email')";
				mysql_query($sql);
				if (mysql_error) {
					print "<pre>";
					print_r($arrTokens);
					print "</pre>";
				}
				//print "$sql\n";
				/*
				//$player_id=mysql_insert_id();
				//$sql="INSERT INTO tmsl_player_team (player_uid, team_uid, season_uid) VALUES ($player_id, $team_id, $season_id)";
				//mysql_query($sql);
				//print "$sql\n";
				//$sql = "SELECT * FROM tmsl_player where fname like '$fname' and lname like '$lname' and addr like '$addr'";
				$sql = "SELECT * FROM tmsl_player where phone like '$phone' and addr like '$addr'";
				$res=mysql_query($sql);
				if (!mysql_num_rows($res)) {
					print "No luck $phone $addr<br/>";
					$sql2 = "SELECT * FROM tmsl_player where fname like '$fname' and lname like '$lname' and zip like '$zip'";
					$res2=mysql_query($sql2);
					if (!mysql_num_rows($res2)) {
						print "No match $phone $addr<br/>";
					} else {
						if (mysql_num_rows($res2)<2) {
							$rec=mysql_fetch_assoc($res2);
							$upd="UPDATE tmsl_player SET DOB='$dob', dateJoinedTMSL='$doe' WHERE uid = ".$rec['uid'];
							mysql_query($upd);
							print "$upd<br/>";
						} else print "multiple found for $lname $fname<br/>";
					}
				} else {
					if (mysql_num_rows($res)<2) {
						$rec=mysql_fetch_assoc($res);
						$upd="UPDATE tmsl_player SET DOB='$dob', dateJoinedTMSL='$doe' WHERE uid = ".$rec['uid'];
						mysql_query($upd);
						print "$upd<br/>";
					}else print "multiple found for $phone $addr<br/>";
				}
				*/
			}
		}
  }
?>
