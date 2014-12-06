<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<?
$cron = explode(' ', $var['shareMoverSchedule']);
$move = $cron[2]!='*' ? 4 : ($cron[4]!='*' ? 3 : ($cron[1]!='*' ? 2 : 1));
$mode = array('Hourly','Daily','Weekly','Monthly');
$days = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
?>
<script>
$(function() {
  presetMover(document.mover_schedule);
});
// Fool unRAID by simulating the original input field
function prepareMover(form) {
  var hour = mode!=1 ? form.hour.value : '*';
  form.shareMoverSchedule.options[form.shareMoverSchedule.value-1].value = form.min.value+' '+hour+' '+form.dotm.value+' * '+form.day.value;
  form.min.disabled = true;
  form.hour.disabled = true;
  form.dotm.disabled = true;
  form.day.disabled = true;
}
function presetMover(form) {
  mode = form.shareMoverSchedule.value;
  form.min.disabled = false;
  form.hour.disabled = mode==1;
  form.dotm.disabled = mode!=4;
  form.day.disabled = mode!=3;
  form.day.value = form.day.disabled ? '*' : '0';
  form.dotm.value = form.dotm.disabled ? '*' : '1';
}
</script>
<form name="mover_schedule" method="POST" action="/update.htm" target="progressFrame" onsubmit="prepareMover(this)">
<table class="settings">
  <tr>
  <td>Mover schedule:</td>
  <td><select name="shareMoverSchedule" size="1" class="large" onchange="presetMover(this.form)">
<?for ($m=0; $m<count($mode); $m++):?>
<?=mk_option($move, strval($m+1), $mode[$m])?>
<?endfor;?>
  </select></td>
  </tr>
  <tr>
  <td>Day of the week:</td>
  <td><select name="day" size="1" class="large">
<?for ($d=0; $d<count($days); $d++):?>
<?=mk_option($cron[4], strval($d), $days[$d])?>
<?endfor;?>
<?=mk_option($cron[4], "*", "--------", "disabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Day of the month:</td>
  <td><select name="dotm" size="1" class="large">
<?for ($d=1; $d<=31; $d++):?>
<?=mk_option($cron[2], strval($d), sprintf("%02d", $d))?>
<?endfor;?>
<?=mk_option($cron[2], "*", "--------", "disabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Time of the day:</td>
  <td><select name="hour" size="1" class="narrow">
<?for ($d=1; $d<=23; $d++):?>
<?=mk_option($cron[1], strval($d), sprintf("%02d", $d))?>
<?endfor;?>
  </select>
  <select name="min" size="1" class="narrow">
<?for ($d=0; $d<=55; $d+=5):?>
<?=mk_option($cron[0], strval($d), sprintf("%02d", $d))?>
<?endfor;?>
  </select>&nbsp;&nbsp;HH:MM</td>
  </tr>
  <tr>
  <td>Mover logging:</td>
  <td><select name="shareMoverLogging" size="1" class="large">
<?=mk_option($var['shareMoverLogging'], "yes", "Enabled")?>
<?=mk_option($var['shareMoverLogging'], "no", "Disabled")?>
  </select></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeMover" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
  <tr>
  <td></td>
<?if (file_exists("/var/run/mover.pid")):?>
  <td><input type="submit" name="cmdStartMover" value="Move now" disabled> Mover is running.</td>
<?else:?>
  <td><input type="submit" name="cmdStartMover" value="Move now"> Click to invoke the Mover.</td>
<?endif;?>
  </tr>
</table>
</form>