<?
  include_once("tmsl_config.php");

	/*--------bits-----------
	  1 = team manager
	  2 = edit all teams
	  4 = ?
	-----------------------*/

	$arrPlayerFields=array("p.uid"=>"ID", "CONCAT(p.lname, ', ', p.fname)"=>"Name", "case p.email when '' then 'N/A' else CONCAT('<a href=mailto:',p.email,'>',p.email,'</a>') end"=>"Email", "jersey_no"=>"Jersey", "pic_on_file"=>"Pic", "DOB_validated"=>"dob_val");
	//$arrPlayerFields["case when now() between ifnull(sus.start_date, '0000-00-00') and ifnull(sus.stop_date, '0000-00-00') then 1 else 0 end"]="Suspended";
	$arrPlayerFields["pt.registered"]="Registered";
	$arrPlayerFields["pt.notes"]="Notes";
	$arrPlayerFields["pt.waiver_signed"]="Waiver";
	$arrPlayerFields2=array("p.uid"=>"ID", "p.lname"=>"LastName", "p.fname"=>"FirstName", "p.mname"=>"Middle", "p.email"=>"Email", "p.addr"=>"Address", "p.city"=>"City", "p.state"=>"State", "p.zip"=>"Zip", "p.phone"=>"Phone");
	$arrPlayerFields2["DATE_FORMAT(p.dob,'%m/%d/%Y')"]="DOB";
	$arrPlayerFields2["DATE_FORMAT(p.dateJoinedTMSL,'%m/%d/%Y')"]="JoinedTMSL";
	$arrPlayerFields3=$arrPlayerFields2;
	$arrPlayerFields2["jersey_no"] = "Jersey";
	$arrPlayerFields2["DOB_validated"] = "dob_val";
	$hideField=array("ID"=>1, "Suspended"=>2, "Registered"=>3, "Pic"=>4, "JoinedTMSL"=>5, "Notes"=>6,"dob_val"=>7);
	$yesNo=array("Captain"=>1);
	$textArea=array();
	//These only appear to manager & admin:
	$sensitiveFields=array("Address"=>1, "City"=>1, "State"=>1, "Zip"=>1, "Phone"=>1, "DOB"=>1);
	$mandatoryFields=array("FirstName","LastName","DOB");
	$banner = "<div id='banner'>TMSL</div>";
	$footer = "<div id='footer'>site by <a href='http://3r3w.org'>3r3w</a></div>";

	$navBar = 	"<div id='navBar'>
					<table align='center'>
						<tr>
							<td><a href='index.php'>Home</a> | </td>";
	if (!$isRef || $adm) $navBar .=
							"<td><a href='roster.php'>Teams</a> | </td>";
	$navBar .=
							"<td><a href='contact.php'>Contact TMSL</a> | </td>
							 <td><a href='games.php'>Games</a> | </td>";



	if ($adm) $navBar .=
							"<td><a href='player.php'>Players</a> | </td>
							 <td><a href='manageSeasons.php'>Seasons</a> | </td>
							 <td><a href='report.php'>Reports</a> | </td>
							 <td><a href='admin.php'>Admin</a> | </td>";

	$navBar .=	"<td><a href='logout.php'>Log Out (".$_SESSION['logon_name'].")</a></td>
						</tr>
					</table>
				</div>";
	$days_before=3;
?>
