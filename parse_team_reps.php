<?
  include_once("session.php");
  $lines = array();
  $parsedLines = array();
  $arrTokens = array();
  $uf_file     = 'team_reps.csv';
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

			//print "<pre>";
			//print_r($arrTokens);
			//print "</pre>";
			//exit;
			$team=trim($arrTokens[1]);
			$nm=trim($arrTokens[2]);
			$email=trim($arrTokens[3]);
			$sql="SELECT uid FROM tmsl_team WHERE name='$team'";
			$a=dbSelectSQL($sql);
			if (!$a[0]['uid']) print "Can't find $team ($sql)<br/>";
			else {$team_id=$a[0]['uid'];print "$team has id $team_id<br/>";}
			if ($team_id) {
				$sql="SELECT uid FROM tmsl_player WHERE CONCAT(fname,' ',lname)='$nm'";
				$b=dbSelectSQL($sql);
				$player_id=$b[0]['uid'];
				if ($player_id) print "$nm has id $player_id<br/>";
				else print "$sql<br/>";
				dbUpdate('tmsl_player', array('email'=>$email), array('uid'=>$player_id));
				$exists=getScalar('user_uid', $player_id, 'count(1) as ct', 'tmsl_team_manager');
				if (!$exists) {
					//print "not exists<br/>";

					$uexists=getScalar('player_uid', $player_id, 'player_uid', 'tmsl_user');
					if (!$uexists) {
						$arr=dbSelect('tmsl_player', array('fname', 'lname'), array('uid'=>$player_id));
						$fname=$arr[0]['fname'];
						$lname=$arr[0]['lname'];
						$username=strtolower(substr($fname, 0, 1).$lname);
						print "Add $username to tmsl_user<br/>";
						$user_arr=array('name'=>$username, 'pwd'=>sha1(strtolower($username)), 'player_uid'=>$player_id);
						dbInsert('tmsl_user', $user_arr, true, true);
					}
					print "Add $username to tmsl_mgr<br/>";
					dbInsert('tmsl_team_manager', array('user_uid'=>$player_id, 'team_uid'=>$team_id), true, true);

				}
			}
		}
  }
?>
