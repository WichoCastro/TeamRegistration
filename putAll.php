<?

$f=ftp_connect("ftp.3r3w.org");
if (ftp_login($f, 'nr3w0r5', 'j6Beck60')) print "Login OK";
ftp_chdir($f, 'web/tmsl');

$arr=ftp_nlist($f,'.');
foreach ($arr as $fil) {
  print $fil." ".substr($fil,-3).":\n";
  if (substr($fil, -3) == "php" || substr($fil, -3) == "css")
    ftp_get($f, $fil, $fil, FTP_ASCII);
}
ftp_close($f);
?>
