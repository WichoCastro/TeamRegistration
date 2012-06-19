<?
    include_once("session.php");
    if ($_SESSION['logged_in']) {
	if (!$season_id) $season_id = $_SESSION['season_uid'];
	else $_SESSION['season_uid'] = $season_id;
	print "<html>";
	print "<head>";
	print "<link href='tmsl.css' rel='stylesheet' type='text/css'>";
	print "</head>";
	print "<body>";
        print "<div id=container style='text-align:center'>";
	print $banner;
	print $navBar;
	print "<div id='ttlBar'>Standings</div>";
	print "<form>";
        print getSeasonDropdown(date('Y-m-d', 1284267600), $season_id);
	print "</form>";
        if ($season_id) {

          //get the start date & length:
          $sql = "SELECT start_date, DATEDIFF(stop_date, start_date) as diff from tmsl_season WHERE uid = $season_id";
          $res=mysql_query($sql);
          $rec=mysql_fetch_assoc($res);
          $season_start_date = $rec['start_date'];
          $season_length = $rec['diff'];
          $season_name = getSeasonName($season_id);
          print "<h3 style='margin-top:5px'>$season_name</h3>";

          $sql="select UPPER(team) as team, team_id, W+L+Ta+Tb as GP, W, L, Ta+Tb as T, 3*W + Ta+Tb as Pts, GF1+GF2 as GF, GA1+GA2 as GA, GF1+GF2-GA1-GA2 AS '+/-'  from 

            ( select ifnull(W,0) as W, ifnull(L,0) as L, ifnull(Ta,0) as Ta, ifnull(Tb,0) as Tb, ts.tname as team, winner as team_id, GF1, GF2, GA1, GA2 FROM 

            (select winner, count(*) as W from (select  case 
            when team_h_score > team_v_score then team_h
             when team_h_score < team_v_score then team_v 
            else 'T'
            end as winner,
              case 
            when team_h_score > team_v_score then team_v
             when team_h_score < team_v_score then team_h 
            else 'T'
            end as loser
            from tmsl_game where season_uid=$season_id) a
            group by winner) wn
            
            left outer join
            
            (select loser, count(*) as L from (select  case 
            when team_h_score > team_v_score then team_h
             when team_h_score < team_v_score then team_v 
            else 'T'
            end as winner,
              case 
            when team_h_score > team_v_score then team_v
             when team_h_score < team_v_score then team_h 
            else 'T'
            end as loser
            from tmsl_game where season_uid=$season_id) a
            group by loser) lz
            
            on wn.winner=lz.loser
            
            left outer join
             
            (select t1, count(*) as Ta from (
            
            select  case 
            when team_h_score = team_v_score then team_h
            end as t1,
              case 
            when team_h_score = team_v_score then team_v
            end as t2
            from tmsl_game where season_uid=$season_id and team_h_pts > -1) a
            group by t1) t
            
            on wn.winner=t.t1
            
            left outer join
             
            (select t2, count(*) as Tb from (
            
            select  case 
            when team_h_score = team_v_score then team_h
            end as t1,
              case 
            when team_h_score = team_v_score then team_v
            end as t2
            from tmsl_game where season_uid=$season_id and team_h_pts > -1) a
            group by t2) u
            
            on wn.winner=u.t2
            
            inner join
            
            (select team_h, sum(team_h_score) as GF1, sum(team_v_score) as GA1 from tmsl_game where season_uid=$season_id group by team_h) ph
            
            on wn.winner=ph.team_h
            
            inner join
            
            (select team_v, sum(team_v_score) as GF2, sum(team_h_score) as GA2 from tmsl_game where season_uid=$season_id group by team_v) pv
            
            on wn.winner=pv.team_v
            
            inner join 
            
            tmsl_team_season ts
            
            on wn.winner=ts.team_uid
    
            where ts.season_uid=$season_id
            ) mn
    
            order by Pts desc, '+/-' DESC, GF DESC, GA";

          $res=mysql_query($sql) or die("$sql -- ".mysql_error());
          $str="<table border='1' align='center'>";
          while ($rec=mysql_fetch_assoc($res)) {
              if (!$hdr) {
              	$str.="<tr>";
              	foreach($rec as $key=>$val) {
                    if ($key != 'team_id') {
              		if (!empty($hdrStyl)) $x=array_shift($hdrStyl);
              		$str.="<th $x>".str_replace("_","<br>",$key)."</th>";
              		$numCols++;
                    }
              	}
              	$str.="</tr>";
              	$hdr=true;
              }
              $r=$rowStyl;
              foreach($rec as $key=>$val) {
                if ($key == 'team') $val = "<a href='games.php?dt=$season_start_date&season_id=$season_id&team_id={$rec['team_id']}&numDays=$season_length&srch=ok'>$val</a>";
                if ($key != 'team_id') 
              	  $str.="<td $y>$val</td>";
              }
              $str.="</tr>";
          }
          $str.="</table>";
          print $str;
          //print printTable($sql);
        }
        print "<div id='footer-spacer'></div>";
        print "</div >"; //end container
        print $footer;
	print "</body>";
	print "</html>";
    }else include("login.php");

?>
