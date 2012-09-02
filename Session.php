<?
session_start();
include_once("sql.inc");
include_once("Season.php");
include_once("Team.php");
include_once("Person.php");
include_once("functions.php");
if (time() - $_SESSION['lastAction'] > 36000) {
  //print "time passed -- ".(time() - $_SESSION['lastAction']);
  $_SESSION['logged_in']=0;
}
$_SESSION['lastAction']=time();
foreach ($_POST as $key=>$val) $$key=$val;
foreach ($_GET as $key=>$val) $$key=$val;
$loginname=mysql_real_escape_string($loginname);
if (strlen($loginname) > 0)	{
	$sql="SELECT u.player_uid, mask, name, last_season_uid, isReferee, last_team_uid from tmsl_user u inner join tmsl_player p on u.player_uid=p.uid where (u.name='$loginname' OR p.email='$loginname') AND pwd=SHA1('$pwd')";
	$res=mysql_query($sql);
	$rec=mysql_fetch_array($res);
	if ($rec['mask']) {
		$_SESSION['logged_in']=1;
		$_SESSION['logon_name']=$loginname;
		$_SESSION['mask']=$rec['mask'];
		$_SESSION['isRef']=$rec['isReferee'];
		$_SESSION['logon_uid']=$rec['player_uid'];
		if ($rec['last_team_uid']) $_SESSION['team_uid']=$rec['last_team_uid'];
		if ($rec['last_season_uid']) $_SESSION['season_uid']=$rec['last_season_uid'];
		if (!$_SESSION['season_uid']) {
			$sql2="SELECT uid FROM tmsl_season WHERE active=1";
			$res2=mysql_query($sql2);
			$rec2=mysql_fetch_array($res2);
			$_SESSION['season_uid']=$rec2['uid'];
		}
		if ((int)$_SESSION['mask'] & 4) $_SESSION['editTeams']=true;
		if ($_SESSION['mask'] & 2) $addTmPriv=true;else $addTmPriv=false;
		if (sha1($rec['name']) == sha1($pwd)) {
			$cp_url="Location:chgPwd.php?m=1&uid=".$_SESSION['logon_uid'];
			$pw_redir = true;
		}
		//mail('futiaz@gmail.com', 'TMSL login', "{$rec['name']} has logged in. ref: {$rec['isReferee']} sql:$sql");
	}else{
		$_SESSION['logged_in']=0;
	}
}
if ($_SESSION['mask'] & 4) $adm=true;
if ($_SESSION['isRef']) $isRef=true;
if ($pw_redir) header($cp_url);
include_once("tmsl_include.php");
?>
