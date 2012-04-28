<?
include_once('session.php');

$hdr = "From:support@tmslregistration.com\nBcc:futiaz@yahoo.com";

$sql="select email, tm.name as nm, pwd, u.name as uname from tmsl_player p, tmsl_user u, tmsl_team_manager m, tmsl_team tm WHERE p.uid=m.user_uid
  AND m.team_uid=tm.uid and u.player_uid=m.user_uid";

$arr=dbSelectSQL($sql);
foreach ($arr as $val) {
  $e=$val['email'];
  $tm=$val['nm'];
  $s=$val['pwd'];
  $u=$val['uname'];

  $subj="TMSL Online Registration";

  $body = "TMSL is asking teams to register online for the upcoming summer season.  You are receiving this message because ".
  	"you are listed as a team representative of $tm.".
  	"  If you plan on registering your team for the upcoming season, please go to http://www.tmslregistration.com and logon with username $u.  ";

  if (sha1($u) == $s) $body .= "Your password is also $u -- you will be prompted to change it the first time you log on.";

  $body .= "  If you have any questions or have difficulty using the site, reply to this email or contact one of the people listed below.\n\n".
  	"John Farmer (webmaster) support@tmslregistration.com\n".
  	"Martyn Tagg (board member) mtagg@sricrm.com\n".
  	"Brad Herbert (board member) bherbertrg@hotmail.com";

  print "sending mail to ".$e." about ".$tm." $u $s<br/>";

  mail($e, $subj, $body, $hdr);
}
?>
