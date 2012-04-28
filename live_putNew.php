<?
$secs=$GLOBALS['argv'][1];
if (!$secs) $secs=3600;
print "Uploading shit created less than $secs secs ago\n";
$h = opendir(".");
$f=ftp_connect("ftp.tmslregistration.com");
if (ftp_login($f, 'tmsladmin', 'tmsl4066')) print "OK";
ftp_chdir($f, 'httpdocs');
while (false !== ($fil = readdir($h))) {
  if (filectime($fil) > time() - $secs)
    if (substr($fil, -3) == "php" || substr($fil, -3) == "css") {
      print "Uploading $fil ";
      echo filectime($fil)." ";
      echo "now= ".time()."\n";
      ftp_put($f, $fil, $fil, FTP_ASCII);
    }
}
ftp_close($f);
?>
