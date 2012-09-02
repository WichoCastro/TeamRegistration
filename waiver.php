<?
  include_once("session.php");
  if ($_SESSION['logged_in']) {
  print "<html>";
  print "<head>";
  print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
  print "<script language='JavaScript' type='text/javascript' src='prototype.js'></script>";
  print "</head>";
  print "<body>";
  print "<div id=container>"; 
  print $banner;
  print $navBar;
  print "<div id='ttlBar'>Sign the Waiver</div>";
  print "<div id='mainPar'>";

  $uid = $_SESSION['logon_uid'];
  

  $waiver_text = "I, " . getUserName($uid) . ", agree to abide by the laws and rules of the game of soccer as promulgated by the International Football Federation (FIFA), the United States Soccer Federation (USSF), the Arizona State Soccer Association (ASSA), and any properly affiliated league or team which may sanction a competition in which I choose to participate. I further agree that failure to abide by these laws and rules may result in the revocation of the right to play granted by the acceptance of this registration.<br/><br/>

In consideration for being allowed to participate in any US Amateur Soccer Association athletic/sports program, and related
events and activities, I:<br/><br/>

Agree that prior to participating, I will inspect the facilities and equipment used, and if they believe anything to be unsafe,
will immediately advise their coach or supervisor of such conditions and refuse to participate:<br/>

1. Acknowledge and fully understand that each participant will be engaging in activities that involve risk of serious injury,
including permanent disability and death, and severe social and economic losses which might result not only from their own
actions, inaction or negligence of others, the rules of play, or the condition of the premises or of any equipment used. Further,
that there may be other risks not known to us or not reasonable foreseeable at this time.<br/><br/>
2. Assume all the foregoing risks and accept personal responsibility for the damages following such an injury, permanent
disability or death.<br/><br/>
3. Release, waive, discharge and covenant not to sue the US Amateur Soccer Associations, its affiliated clubs, their respective
administrators, directors, agents, coaches, and other employees of the organization, other participants, sponsoring agencies,
sponsors, advertisers, and if applicable owners and leasers of premises used to conduct the event, all of which are herein after
referred to as the “releases”, from demands, losses or damages on account of the injury, including death or damage to property,
caused or alleged to be caused in whole or in part by the negligence of the release or otherwise.<br/><br/>

I HAVE READ THE ABOVE WAIVER AND RELEASE, UNDERSTAND THAT I HAVE GIVEN
UP SUBSTANTIAL RIGHTS BY SIGNING IT, AND SIGN IT VOLUNTARILY. <br/><br/>";
  print "<div style='text-align:left'>$waiver_text</div>";
//  print "<form>";
  print "<input type='checkbox' id='waiverAgree' onclick='activateSubmit()'/>By checking this box I agree to the above<br/>";
  print "<input type='submit' id='waiverSubmit' onclick='signWaiver()' disabled=true value='Check the box if you agree'><br/>";
  print "<input type='button' id='waiverRefuse' onclick='window.location=\"basicInfo.php\"' value=\"I don't want to sign at this time\">";
//  print "<button id='waiverSubmit' onclick='signWaiver()' disabled=true>Check the box if you agree</button>";
//  print "</form>";
    
  print "</div >"; //end mainPar
  print "<div id='footer-spacer'></div>";
  print "</div >"; //end container
  print $footer;
?>
  <script>
    function activateSubmit() {
      if ($('waiverSubmit').disabled) {
      	$('waiverSubmit').enable();
        $('waiverSubmit').value='I hereby sign the waiver';
      }	else {
      	$('waiverSubmit').disable();
        $('waiverSubmit').value='Check the box if you agree';
      }
      
    }

    function signWaiver() {
    	  var url='ajax_sign_waiver.php';
		  var params="uid=<?=$uid?>&team_id=<?=$team_uid?>&season_id=<?=$season_uid?>";
		  var myAjax=new Ajax.Request(url, {method: 'post', parameters: params, onComplete:function() {document.location.href='regStatus.php'}});
    }
  </script>
<?
  print "</body>";
  print "</html>";
  }else include("login.php");
?>
