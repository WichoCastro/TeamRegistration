<?
print "use as follows: php {$_SERVER['PHP_SELF']} [live] files\n";
$live_site=0;
$arrfil=$GLOBALS['argv'];
if ($arrfil[1] == 'live') {
	$live_site=1;
	print "Uploading to live site\n";
	unset ($arrfil[1]);
}
unset ($arrfil[0]);
if ($live_site) {
 $f=ftp_connect("ftp.tmslregistration.com");
 if (ftp_login($f, 'tmsladmin', 'tmsl4066')) print "Login successful\n";
 else {print "Login Failed";exit;}
 ftp_chdir($f, 'httpdocs');
} else {
 $f=ftp_connect("ftp.3r3w.org");
 if (ftp_login($f, 'nr3wor5', 'j6Beck60')) print "Login successful\n";
 else {print "Login Failed";exit;}
 ftp_chdir($f, 'www/tmsl');
}

print "dir is ";
print ftp_pwd($f);
print "\n";
foreach($arrfil as $num=>$fil) {
	if (!$num || !$fil) continue;
	print "uploading $fil\n";
	if (substr($fil, -3) == "php" || substr($fil, -4) == "html" || substr($fil, -3) == "css" || substr($fil, -3) == "inc") {
		ftp_put($f, $fil, $fil, FTP_ASCII);
	} else ftp_put($f, $fil, $fil, FTP_BINARY);
}
ftp_close($f);
?>
