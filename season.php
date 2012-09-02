<?
include('session.php');
class Season {

  var $id;
  var $divisionId;
  var $seasonName;
  var $divisionName;
  var $startDate;
  var $stopDate;
  var $lastDateTeam;
  var $lastDatePlayer;
  var $minPlayers;
  var $maxPlayers;
  var $cost;
  var $active;
  var $halfwayDate;
  var $costAfter;
  var $seasonOver;

  function Season($id, $divisionId = false) {
    $this->set('id', $id);
    $sql = "SELECT *, (now() > stop_date) AS season_over FROM tmsl_season WHERE uid=$id";
    $res = mysql_query($sql);
    $rec = mysql_fetch_assoc($res);
    $this->divisionId = $rec['division_uid'];
    $this->seasonName = $rec['name'];
    $this->startDate = $rec['start_date'];
    $this->stopDate = $rec['stop_date'];
    $this->lastDateTeam = $rec['last_day_team'];
    $this->lastDatePlayer = $rec['last_day_player'];
    $this->minPlayers = $rec['min_players'];
    $this->maxPlayers = $rec['max_players'];
    $this->cost = $rec['cost_per_player'];
    $this->active = $rec['active'];
    $this->halfwayDate = $rec['halfway_date'];
    $this->costAfter = $this->computeCostAfter($this->cost);
    $this->seasonOver = $rec['season_over'];
  }

  function set($varname,$value) {
    $this->$varname = $value;
  }

  function show($varname) {
    print $this->$varname;
  }

  function computeCostAfter($c) {
    return $c / 2 + 5;
  }
}
?>
