//javascript functions for this site

function seasonChanged() {
  //assume the id of the season dropdown is season_id
  var newSeason = $F('season_id');
  //and the team dropdown is team_id
  $('team_id').value = 0;
  //and the form is frmSznTm
  $('frmSznTm').submit();
}  

function upd_email(id) {
  var val=$F('e_' + id);
  var url='ajax_updt_email.php';
  var params='email=' + val + '&uid=' + id;
  var myAjax=new Ajax.Updater('msg', url, {method: 'post', parameters: params});
}

function payAtOffice(u, t, s) {
  var url = 'ajax_updt.php';
  var params = 'tbl=tmsl_player_team&uid=' + u + '&team_id=' + t + '&season_id=' + s + '&fld=pay_pending&val=1';
  var myAjax = new Ajax.Request(url, {method: 'post', parameters: params});
  $('btnPayPromise').style.background='#8f8';
  alert('The office is located near First and River, at 4651 N. First Ave., Suite 204. Materials can be dropped off any time during regular business hours through the mail slot.  League office hours are Wednesdays  5-7.');
}
