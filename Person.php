<?
include_once('session.php');

class Person {
  var $id;
  var $teamId;
  var $seasonId;
  var $firstName;
  var $lastName;
  var $middleName;
  var $fullName;
  var $email;
  var $addr;
  var $city;
  var $state;
  var $zip;
  var $phone;
  var $jersey;
  var $pic;
  var $dob;
  var $dobValidated;
  var $registered;
  var $notes;
  var $waiverSigned;
  var $balance;
  var $suspended;
  var $yellowCards;
  var $redCards;
  var $basicFields;

  function Person($id, $teamId = false, $seasonId = false) {
    $this->set('id', $id);
    $this->set('teamId', $teamId);
    $this->set('seasonId', $seasonId);
    $sql = "SELECT * FROM tmsl_player WHERE uid = $id";
    $res = mysql_query($sql);
    $rec = mysql_fetch_assoc($res);
	$this->basicFields = array(
				  		'firstName'=>'fname',
				  		'lastName'=>'lname',
				  		'middleName'=>'mname',
				  		'addr'=>'addr',
				  		'city'=>'city',
				  		'state'=>'state',
				  		'zip'=>'zip'
						);
	$this->seasonTeamFields = array(
						'jersey'=>'jersey_no',
						'registered'=>'registered',
						'notes'=>'notes',
						'waiverSigned'=>'waiver_signed',
						'balance'=>'balance'
						);
	foreach($this->basicFields as $attr=>$fld) {
		$this->$attr = $rec[$fld];
	}					
    //$this->firstName = $rec['fname'];
    //$this->lastName = $rec['lname'];
	//$this->middleName = $rec['mname'];
    $this->fullName = $rec['fname'] . ' ' . $rec['lname'];
    $this->email = $rec['email'];
    //$this->phone = $rec['phone'];
	//$this->addr = $rec['addr'];
	//$this->city = $rec['city'];
	//$this->state = $rec['state'];
	//$this->zip = $rec['zip'];
    $this->dob = $rec['DOB'];
    $this->dobValidated = $rec['DOB_validated'];
    $this->pic = $rec['pic_on_file'];
    $this->suspended = $this->isSuspended();
    $this->getCards();
    if ($teamId && $seasonId) {
      $sql = "SELECT * FROM tmsl_player_team WHERE player_uid = $id AND team_uid = $teamId AND season_uid = $seasonId";
      $res = mysql_query($sql);
      $rec = mysql_fetch_assoc($res);
	  foreach($this->seasonTeamFields as $attr=>$fld) {
		$this->$attr = $rec[$fld];
	  }
      //$this->jersey = $rec['jersey_no'];
      //$this->registered = $rec['registered'];
      //$this->notes = $rec['notes'];
      //$this->waiverSigned = $rec['waiver_signed'];
      //$this->balance = $rec['balance'];
    }
  }

  function getCards() {
    $sql = "SELECT uid FROM tmsl_card WHERE card_type = 'R' AND player_uid={$this->id} AND team_uid = {$this->teamId} AND season_uid = {$this->seasonId}";
    $res = mysql_query($sql);
    while ($rec = mysql_fetch_assoc($res)) 
      $this->redCards[] = $rec['uid'];
    $sql = "SELECT uid FROM tmsl_card WHERE card_type = 'Y' AND player_uid={$this->id} AND team_uid = {$this->teamId} AND season_uid = {$this->seasonId}";
    $res = mysql_query($sql);
    while ($rec = mysql_fetch_assoc($res)) 
      $this->yellowCards[] = $rec['uid'];
  }

  function isSuspended() {
    $sql = "SELECT uid from tmsl_suspended WHERE player_uid={$this->id} and now() >= start_date and now() <= stop_date"; 
    $res = mysql_query($sql);
    $rec = mysql_fetch_assoc($res);
    if (!empty($rec)) return $rec['uid'];
    return 0;
  }
  
  function writeBasicInfoToDB() {
  	$a = array();
  	$sql = "UPDATE tmsl_player SET ";
	foreach($this->basicFields as $attr=>$fld) {
		$a[] = $fld . " = '" . $this->$attr . "'";
	}
	if(!$this->dobValidated) 
		$a[] = "DOB = '" . $this->dob . "'"; 
	$sql .= implode(', ', $a);
  	$sql .= " WHERE uid = ". $this->id;
  	mysql_query($sql);
  }

  function writeSeasonTeamInfoToDB() {
    $a = array();
  	$sql = "UPDATE tmsl_player_team SET ";
	foreach($this->seasonTeamFields as $attr=>$fld) {
		$a[] = $fld . " = '" . $this->$attr . "'";
	}
	$sql .= implode(', ', $a);
  	$sql .= " WHERE player_uid = ". $this->id;
  	$sql .= " AND team_uid = " . $this->teamId;
  	$sql .= " AND season_uid = " . $this->seasonId;
  	mysql_query($sql);	
  }
  
  function addPlayerToUserTbl() {
	$pwd = genPwd($this->id);
	sendNewUserEmail($this->email, $pwd);
	$vals=array('pwd' => sha1($pwd), 'player_uid'=>$this->id, 'name' => $this->email);
	if (!dbInsert('tmsl_user', $vals, 1, 1))
		dbUpdate('tmsl_user', $vals, array('player_uid'=>$this->id));
  }
  function drop() {
  	$a = array('player_uid'=>$this->id, 'team_uid'=>$this->teamId, 'season_uid'=>$this->seasonId, 
  		'drop_date'=>date('Y-m-d'), 'registered'=>$this->registered);
	dbInsert('tmsl_dropped', $a, true, true);
	array_splice($a, 3);
	dbDelete('tmsl_player_team', $a, true);
  }
  function suspend($sd, $ed, $r) {
  	$r = mysql_escape_string($r);
  	$sql="INSERT INTO tmsl_suspended (player_uid, reason, start_date, stop_date) VALUES ($this->id, '$r', '$sd', '$ed')";
	mysql_query($sql);
  }
  
  function addEmail() {
  	$invEml = invalidEmail($this->id, $this->email);
    if (!$invEml)
      if (dbUpdate('tmsl_player', array('email'=>$this->email), array('uid'=>$this->id), 1))
        return "email updated.";
    return $invEml;
  }
  function set($varname,$value) {
    $this->$varname=$value;
  }

  function show($varname) {
    print $this->$varname;
  }
}
?>
