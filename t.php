<?
$email_addresses = array("me@example.com", "john3r3w.commmm", "john-f@123.net");
foreach($email_addresses as $e) {
if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
  // The email address is valid
  print "yes";
} else {
  // The email address is not valid
  print "no";
}
}
?>
