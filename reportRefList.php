<?
	include_once("session.php");
	if ($_SESSION['logged_in']) {
		if ($send_mail) {
			//print_r($_POST);
			//$bdy=mysql_real_escape_string($_POST['mailer_body']);
			//$sbj=mysql_real_escape_string($_POST['mailer_subj']);
			$bdy=$_POST['mailer_body'];
			$sbj=$_POST['mailer_subj'];
			$to=$_POST['mailer_to'];
			//$to = 'futiaz@gmail.com, futiaz@yahoo.com';
			$hdr="FROM:{$_POST['mailer_from']}";
			mail($to, $sbj, $bdy, $hdr);
		}
		if (!$start_date) {
			$sql="SELECT DATE_FORMAT(game_dt, '%m/%d/%Y') as game_dt FROM tmsl_game where game_dt >= current_date() order by game_dt limit 1";
			$arr=dbSelectSQL($sql);
			$start_date=$arr[0]['game_dt'];
			if (!$start_date) $start_date = date('m/d/Y');
		}
		print "<html>";
		print "<head>";
		print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
		print "<script language='JavaScript' type='text/javascript' src='calendar.js'></script>";
		print "<script>var cal = new CalendarPopup('testdiv1');</script>";
		print "<style> textarea {width:600px;} </style>";
		print "</head>";
		print "<body>";
		echo "<DIV ID='testdiv1' STYLE='position:absolute;visibility:hidden;background-color:white;layer-background-color:white;'></DIV>";
		print $banner;
		print $navBar;
		print "<div id='ttlBar'>Center Refs for $start_date</div>";
		print "<form name='frm' id='frm'>";print "<table>";
		print "<tr><td>Show for:</td><td><input type='text' value='$start_date' name='start_date' id='start_date'>";
		print "<a href='#' onClick='cal.select(document.forms[\"frm\"].start_date,\"anchor1\",\"MM/dd/yyyy\"); return false;' NAME='anchor1' ID='anchor1'><img src='calendar.png' border='0' alt='cal' title='Select Date'></a>";
		print "<input type='submit' value='ok'></td></tr>";
		print "</table>";print "</form>";

		print "<div id='mainPar'>";
		if ($start_date) {
			$mm=substr($start_date, 0, 2);
			$dd=substr($start_date, 3, 2);
			$yyyy=substr($start_date, 6, 4);
			$stop_date=date('m/d/Y',mktime(0,0,0,$mm,$dd+1,$yyyy));
			$sd="$yyyy-$mm-$dd";
			$sql="SELECT CONCAT('<a href=''editPlayer.php?edit=1&uid=', p.uid, '''>',lname, ', ', fname,'</a>') as name, email,
					CONCAT('<a href=''game_refs.php?uid=', g.uid, '''>',DATE_FORMAT(game_tm, '%l:%i %p'),'</a>') as game_time, game_loc as game_field,
					case game_report_submitted when 0 then CONCAT('<a href=''#'' onclick=''window.open(\"game_report.php?uid=',g.uid,'\",
						\"game_report_win\",
						\"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\");''>no</a>')
						else
					CONCAT('<a href=''#'' onclick=''window.open(\"game_report.php?uid=',g.uid,'\",
						\"game_report_win\",
						\"height=1000; width=1200, location=no, scrollbars=yes, resizeable=yes, menubar=yes\");''>yes</a>') end as report_filed
					FROM tmsl_game_assign ga INNER JOIN tmsl_game g ON ga.game_uid=g.uid
					LEFT OUTER JOIN tmsl_player p ON ga.user_uid=p.uid
					WHERE g.game_dt = '$sd' AND edit_right=2
					ORDER BY game_tm, lname, fname";
			//print $sql;
			print printTable($sql);
		}
		$arr = dbSelectSQL($sql);
		$email_list_arr = array();
		foreach ($arr as $rec) if (strlen($rec['email']) > 2) $email_list_arr[] = $rec['email'];
		$email_list = implode(',',$email_list_arr);
		print "<br/>Send a message to these people:<br/>";
		print "<form name='mailer' method='post'>";
		print "<table align='center' style='width:800px'>";
		print "<tr><td valign='top'>To:<td><textarea name='mailer_to' rows='5' cols='60'>$email_list</textarea><br/>";
		print "<tr><td>From:<td><textarea name='mailer_from' onclick='select();' rows='1' cols='60'>ref_assignor@tmslregistration.com</textarea><br/>";
		print "<tr><td>Subject:<td><textarea name='mailer_subj' onclick='select();' rows='1' cols='60'>(no subject)</textarea><br/>";
		print "<tr><td valign='top'>Message:<td><textarea name='mailer_body' onclick='select();' rows='12' cols='60'>(body)</textarea><br/>";
		print "<input type='submit' name='send_mail' value='send'>";
		print "</form>";

		print "</div>";
		print "</body>";
		print "</html>";
	}else include("login.php");
?>
