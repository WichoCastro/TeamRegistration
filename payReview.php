<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
  


/*==================================================================
 PayPal Express Checkout Call
 ===================================================================
*/
// Check to see if the Request object contains a variable named 'token'	
$token = "";
if (isset($_REQUEST['token']))
{
	$token = $_REQUEST['token'];
	
}

// If the Request object contains the variable 'token' then it means that the user is coming from PayPal site.	
if ( $token != "" )
{

	require_once ("paypalfunctions.php");

	/*
	'------------------------------------
	' Calls the GetExpressCheckoutDetails API call
	'
	' The GetShippingDetails function is defined in PayPalFunctions.jsp
	' included at the top of this file.
	'-------------------------------------------------
	*/
	

	$resArray = GetShippingDetails( $token );
	$ack = strtoupper($resArray["ACK"]);
	if( $ack == "SUCCESS" || $ack == "SUCESSWITHWARNING") 
	{
		/*
		' The information that is returned by the GetExpressCheckoutDetails call should be integrated by the partner into his Order Review 
		' page		
		*/
		$email 				= $resArray["EMAIL"]; // ' Email address of payer.
		$payerId 			= $resArray["PAYERID"]; // ' Unique PayPal customer account identification number.
		$payerStatus		= $resArray["PAYERSTATUS"]; // ' Status of payer. Character length and limitations: 10 single-byte alphabetic characters.
		$salutation			= $resArray["SALUTATION"]; // ' Payer's salutation.
		$firstName			= $resArray["FIRSTNAME"]; // ' Payer's first name.
		$middleName			= $resArray["MIDDLENAME"]; // ' Payer's middle name.
		$lastName			= $resArray["LASTNAME"]; // ' Payer's last name.
		$suffix				= $resArray["SUFFIX"]; // ' Payer's suffix.
		$cntryCode			= $resArray["COUNTRYCODE"]; // ' Payer's country of residence in the form of ISO standard 3166 two-character country codes.
		$business			= $resArray["BUSINESS"]; // ' Payer's business name.
		$shipToName			= $resArray["PAYMENTREQUEST_0_SHIPTONAME"]; // ' Person's name associated with this address.
		$shipToStreet		= $resArray["PAYMENTREQUEST_0_SHIPTOSTREET"]; // ' First street address.
		$shipToStreet2		= $resArray["PAYMENTREQUEST_0_SHIPTOSTREET2"]; // ' Second street address.
		$shipToCity			= $resArray["PAYMENTREQUEST_0_SHIPTOCITY"]; // ' Name of city.
		$shipToState		= $resArray["PAYMENTREQUEST_0_SHIPTOSTATE"]; // ' State or province
		$shipToCntryCode	= $resArray["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"]; // ' Country code. 
		$shipToZip			= $resArray["PAYMENTREQUEST_0_SHIPTOZIP"]; // ' U.S. Zip code or other country-specific postal code.
		$addressStatus 		= $resArray["ADDRESSSTATUS"]; // ' Status of street address on file with PayPal   
		$invoiceNumber		= $resArray["INVNUM"]; // ' Your own invoice or tracking number, as set by you in the element of the same name in SetExpressCheckout request .
		$phonNumber			= $resArray["PHONENUM"]; // ' Payer's contact telephone number. Note:  PayPal returns a contact telephone number only if your Merchant account profile settings require that the buyer enter one. 
		$amtPaid			= $resArray["AMT"];
		
		$paymentInfo = ConfirmPayment($amtPaid);
		
		if ($paymentInfo['ACK'] == 'Success') {
			dbUpdate('tmsl_player_team', array('balance'=>0, 'pay_pending'=>0, 'notes'=>'paid via PayPal'), array('player_uid'=>$_SESSION['logon_uid'], 'team_uid'=>$_SESSION['team_uid'], 'season_uid'=>$_SESSION['season_uid']));
	    	updateRegStatus($_SESSION['logon_uid'], $_SESSION['team_uid'], $_SESSION['season_uid']);
	    	$_SESSION['Payment_Amount'] = 0;
			dbInsert('tmsl_pp', array('user_id'=>$_SESSION['logon_uid'], 'amt'=>$amtPaid));	
			mail($sysAdmin, 'PP Success', 'Success', "FROM:$noreply");
		} else {
			print "";
			print_r($paymentInfo);
			dbInsert('tmsl_pp', array('user_id'=>$_SESSION['logon_uid'], 'amt'=>'fail'));
			mail($sysAdmin, 'PP Fail', 'PP Fail -- check log -- Problem with ConfirmPayment Call', "FROM:$noreply");
			$amtPaid = 0;
		}
	} 
	else  
	{
		//Display a user friendly Error on the page using any of the following error information returned by PayPal
		$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
		$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
		$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
		$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
		
		echo "GetExpressCheckoutDetails API call failed. ";
		echo "Detailed Error Message: " . $ErrorLongMsg;
		echo "Short Error Message: " . $ErrorShortMsg;
		echo "Error Code: " . $ErrorCode;
		echo "Error Severity Code: " . $ErrorSeverityCode;
	}
}

  print "<html>";
  print "<head>";
  print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
  print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
  print "</head>";
  print "<body>";
  print "<div id=container>"; 
  print $banner;
  print $navBar;
  print "<div id='ttlBar'>Payment Summary</div>";
  print "<div id='mainPar'>";

  print "Thank you for your payment. Here are the details of the transaction:<br/><br/>";
  print "<table class='rtbl' align='center'>";

    print "<tr>";
    print "<th>";
    print "Amount Paid:";
    print "</th>";
    print "<td>";
    print "$" . $amtPaid;
    print "</td>";
    print "</tr>";

    print "<tr>";
    print "<th>";
    print "Email:";
    print "</th>";
    print "<td>";
    print $email;
    print "</td>";
    print "</tr>";

    print "<tr>";
    print "<th>";
    print "Paypal Payer ID:";
    print "</th>";
    print "<td>";
    print $payerId;
    print "</td>";
    print "</tr>";

    print "<tr>";
    print "<th>";
    print "Name:";
    print "</th>";
    print "<td>";
    print "$firstName $middleName $lastName";
    print "</td>";
    print "</tr>";

    print "<tr><td colspan='2' style='background:white'>&nbsp;</td></tr>";
    print "<tr><td colspan='1' style='text-align:left'><a href='regStatus.php'><- Registration Status</a></td>";
    print "<td colspan='1' style='text-align:right'><span>Player Card -></span></td></tr>";
    
  print "</table>";
  
  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
  print "</body>";
  print "</html>";
  }else include("login.php");
?>
