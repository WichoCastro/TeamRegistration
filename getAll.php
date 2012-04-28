<?
$f=ftp_connect("ftp.tmslregistration.com");
if (ftp_login($f, 'tmsladmin', 'tmsl4066')) print "OK";
ftp_chdir($f, 'httpdocs');
$arr=ftp_nlist($f,'.');
foreach ($arr as $fil) {
  print $fil." ".substr($fil,-3).":\n";
  if (substr($fil, -3) == "php" || substr($fil, -3) == "css")
    ftp_get($f, $fil, $fil, FTP_ASCII);
}
ftp_close($f);
?>
