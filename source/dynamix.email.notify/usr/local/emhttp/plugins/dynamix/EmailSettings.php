<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$plugin = 'dynamix.email.notify';
$dynamix = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg",true);
extract($dynamix);
unset($dynamix);

$sName = "unraid_notify";
$fName = "/usr/bin/$sName";

$mail = "/etc/unraid_notify.conf";
$smtp = "/etc/ssmtp_config.conf";
$image = "/plugins/dynamix/images";
?>
<link type="text/css" rel="stylesheet" href="/plugins/dynamix/styles/tipsy.css">
<script type="text/javascript" src="/plugins/dynamix/scripts/jquery.tipsy.js"></script>

<script>
var counter,pid;
$(function() {
  $('.tooltip').tipsy({fade:true, gravity:'sw', html:true});
  $('#test').click(function(){
    $('#testresult').html('Test Result:<span class="orange">Obtaining <span id="counter"></span>...</span>');
    $(this).val('Sending').attr('disabled',true);
    counter = 20;
    countdown();
    $.ajax({
      url: '/plugins/dynamix/include/SMTPtest.php',
      success: function(data) {clearTimeout(pid); $('#test').attr('disabled',false).val('Test'); $('#testresult').html(data)}
    });
  });
  $.ajax({url:'/plugins/webGui/include/ProcessStatus.php', data:'name=<?=$sName?>', success:function(status) {$('.tabs').append(status);}});
  presetMail(document.mail_setup);
});

function presetMail(form) {
  var disabled = form.service.value==0;
  var tags = ['radio','input'];
  for (var n=0,tag; tag=tags[n]; n++) {
    for (var i=0,field; field=form.getElementsByTagName(tag)[i]; i++) field.disabled = (disabled && field.name.substr(0,1)!='#');
  }
  form.service.disabled = false;
}

function prepareSMTP(form) {
  form.mailhub.value = document.getElementById('server').value+':'+document.getElementById('port').value;
}

function countdown() {
  document.getElementById('counter').innerHTML = '('+counter+' sec)';
  counter--;
  if (counter>0) pid=setTimeout(countdown,1000);
}
</script>
<form name="mail_setup" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#section" value="email">
<input type="hidden" name="#include" value="update.notify.php">
<input type="hidden" name="#config"  value="<?=$mail?>">
<table class="settings">
  <tr>
  <td>Mail notifications function:</td>
  <td><select name="service" size="1" onchange="presetMail(this.form);">
<?=mk_option($email['service'], "0", "Disabled")?>
<?=mk_option($email['service'], "1", "Enabled")?>
  </select></td>
  </tr>
<?
$data = file($mail);
foreach ($data as $line) {
  if (!$line) continue;
  switch (substr($line,0,1)) {
  case '[':
    break;
  case '#':
    $text = preg_replace('/^#[A-Z]*: /', '', $line);
    switch (substr($line,1,1)) {
    case 'T':
      echo "<tr><td>$text";
      break;
    case 'C':
      echo "<img src='$image/information.png' class='tooltip' title='$text'>";
      break;
    }
    break;
  default:
    echo "</td><td>";
    $key = explode('=', $line);
    $key[1] = trim(str_replace('"', '', $key[1]));
    switch ($key[1]) {
    case "False":
      echo "<input type='radio' name='{$key[0]}' value='True'>Yes";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='False' CHECKED>No";
      break;
    case "True":
      echo "<input type='radio' name='{$key[0]}' value='True' CHECKED>Yes";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='False'>No";
      break;
    default:
      echo "<input type='text' name='{$key[0]}' value='{$key[1]}'>";
    }
    echo "</td></tr>";
  }
}
?>
<tr><td></td><td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td></tr>
</table>
</form>