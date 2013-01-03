<?
  //config file
  //SET ACCORDINGLY	
	//$environment = 'test';
	//$environment = 'development';
	$environment = 'production';
	if ($environment == 'production') {
  	$site_name = "TMSL";
  	$site_url = "http://tmslregistration.com/";
  	$sysAdmin = 'john.beckwith.farmer@gmail.com';
  	$noreply = 'noreply@tmslregistration.com';
	} else {
  	$site_name = "TMSL DEVELOPMENT";
  	$site_url = "http://3r3w.org/tmsl/";
  	$sysAdmin = 'john.beckwith.farmer@gmail.com';
  	$noreply = 'noreply@3r3w.org';
	}
?>
