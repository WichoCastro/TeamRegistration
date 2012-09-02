<?
include('session.php');

class Person {
  var $id;
  var $teamId;
  var $seasonId;
  var $firstName;
  var $lastName;
  var $fullName;
  var $email;
  var $phone;
  var $jersey;
  var $pic;
  var $dobValidated;
  var $registered;
  var $notes;
  var $waiverSigned;
  var $balance;
  var $suspended;
  var $yellowCards;
  var $redCards;

  function Person($id, $teamId = false, $seasonId = false) {
    $this->set('id', $id);
    $this->set('teamId', $teamId);
    $this->set('seasonId', $seasonId);
    $sql = "SELECT * FROM tmsl_player WHERE uid = $id";
    $res = mysql_query($sql);
    $rec = mysql_fetch_assoc($res);
    $this->firstName = $rec['fname'];
    $this->lastName = $rec['lname'];
    $this->fullName = $rec['fname'] . ' ' . $rec['lname'];
    $this->email = $rec['email'];
    $this->phone = $rec['phone'];
    $this->dob = $rec['DOB'];
    $this->dobValidated = $rec['DOB_validated'];
    $this->pic = $rec['pic_on_file'];
    $this->suspended = $this->isSuspended();
    $this->getCards();
    if ($teamId && $seasonId) {
      $sql = "SELECT * FROM tmsl_player_team WHERE player_uid = $id AND team_uid = $teamId AND season_uid = $seasonId";
      $res = mysql_query($sql);
      $rec = mysql_fetch_assoc($res);
      $this->jersey = $rec['jersey_no'];
      $this->registered = $rec['registered'];
      $this->notes = $rec['notes'];
      $this->waiverSigned = $rec['waiver_signed'];
      $this->balance = $rec['balance'];
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

  function set($varname,$value) {
    $this->$varname=$value;
  }

  function show($varname) {
    print $this->$varname;
  }
}
?>
