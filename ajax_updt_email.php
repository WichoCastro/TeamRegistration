<?
	include_once("session.php");
	foreach($_POST as $key=>$dta) $$key=$dta;
        $i = invalidEmail($uid, $email);
        if ($i)
          print $i;
        else {
	  $p_code=sha1($uid);
	  $encoded_email = urlencode($email);
	  $link="$site_url/pwdInit.php?uid=$uid&p=$p_code&e=$encoded_email";
	  $body = "TMSL has received a request to change your email address to $email. ";
	  $body .= "Click this link to create your TMSL account: $link";
	  mail($email, 'TMSL account', $body, "FROM:$noreply");
          print "An email has been sent to $email with instructions on how to update your email address.";
         }
?>
