<?
  include_once("session.php");
  foreach($_POST as $key=>$dta) $$key=$dta;
  //uid, pwd
  if (getScalar('player_uid', $uid, 'uid', 'tmsl_user')) {
    dbUpdate('tmsl_user', array('pwd'=>sha1($pwd)), array('player_uid'=>$uid));
    print "updated";
  } else {
    $email = getScalar('uid', $uid, 'email', 'tmsl_player');
    dbInsert('tmsl_user', array('pwd'=>sha1($pwd), 'player_uid'=>$uid, 'name'=>$email));
	print "inserted"; 
  }   
?>
