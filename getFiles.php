<?
$arrfil=$GLOBALS['argv'];
if (!$arrfil[1]) {print "Plz specify file to download\n";exit;}
//$h = opendir(".");
$f=ftp_connect("ftp.tmslregistration.com");
if (ftp_login($f, 'tmsladmin', 'tmsl4066')) print "Login successful\n";
ftp_chdir($f, 'httpdocs');
foreach($arrfil as $ct=>$fil) {
	if (!$fil || !$ct) continue;
	print "downloading $fil\n";
	if (substr($fil, -3) == "php" || substr($fil, -4) == "html" || substr($fil, -3) == "css" || substr($fil, -3) == "inc") {
		ftp_get($f, $fil, $fil, FTP_ASCII);
	} else ftp_get($f, $fil, $fil, FTP_BINARY);
}
ftp_close($f);
?>

