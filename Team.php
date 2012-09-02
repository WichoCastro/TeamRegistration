<?
include('session.php');
class Team {

  var $id;
  var $seasonId;
  var $name;
  var $seasonName;
  var $numPlayers;
  var $registered;
  var $notes;
  var $active;
  var $bondOwed;
  var $colors;
  var $seasonHistory;
  var $players;
  var $reps;

  function Team($id, $seasonId = false) {
    $this->set('id', $id);
    $sql = "SELECT * FROM tmsl_team_season WHERE team_uid = $id AND season_uid = $seasonId";
    $res = mysql_query($sql);
    $rec = mysql_fetch_assoc($res);
    $this->seasonId = $seasonId;
    $this->name = $rec['tname'];
    $this->getPlayers();
    $this->numPlayers = count($this->players);
    $this->registered = $rec['registered'];
    $this->notes = $rec['notes'];
    $this->active = $rec['active'];
    $this->colors = $rec['colors'] ? $rec['colors'] : 'not specified';
    $this->bondOwed = $rec['bond_owed'];
    $this->getSeasonHistory();
    $this->getReps();
  }

  function getSeasonHistory() {
    $sql = "SELECT season_uid FROM tmsl_team_season ts INNER JOIN tmsl_season s 
      ON ts.season_uid = s.uid WHERE team_uid = " . $this->id .
      " ORDER BY s.start_date";
    $res=mysql_query($sql);
    while ($rec=mysql_fetch_assoc($res)) {
      $this->seasonHistory[] = $rec['season_uid'];
    }
  }

  function getNextSeason($dir=1) {
    $pos = array_search($this->seasonId, $this->seasonHistory);
    if (!$pos) return 0;
    return $this->seasonHistory[$pos+$dir];
  }

  function getPlayers() {
    $sql = "SELECT player_uid FROM tmsl_player_team pt INNER JOIN tmsl_player p 
      ON p.uid = pt.player_uid WHERE team_uid = ". $this->id .
      " AND season_uid = " . $this->seasonId .
      " ORDER BY lname, fname, mname";
    $res=mysql_query($sql);
    while ($rec = mysql_fetch_assoc($res)) 
      $this->players[] = $rec['player_uid'];
  }

  function getReps() {
    $sql = "SELECT user_uid FROM tmsl_team_manager WHERE team_uid = ". $this->id .
      " AND season_uid = " . $this->seasonId;
    $res=mysql_query($sql);
    while ($rec = mysql_fetch_assoc($res)) 
      $this->reps[] = $rec['user_uid'];
  }

  function set($varname,$value) {
    $this->$varname=$value;
  }

  function show($varname) {
    print $this->$varname;
  }
}
?>
