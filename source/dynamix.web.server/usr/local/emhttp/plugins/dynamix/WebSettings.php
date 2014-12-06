<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$plugin = 'dynamix.web.server';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");

$sName = "lighttpd";
$fName = "/usr/sbin/$sName";
$config = "/etc/lighttpd.include.conf";
$emhttp = $var['emhttpPort'];
?>
<script>
$(function() {
  $.ajax({url:'/plugins/webGui/include/ProcessStatus.php',data:'name=<?=$sName?>',success:function(status){$('.tabs').append(status);}});
  presetWEB(document.web_settings);
});
function presetWEB(form) {
  var disabled = form.service.value==0;
  form.path.disabled = disabled;
  form.port.disabled = disabled;
  form.phpError.disabled = disabled;
  form.error.disabled = disabled;
  form.access.disabled = disabled;
}
function resetWEB(form) {
  form.path.value = "/tmp/web";
  form.port.value = "81";
  form.phpError.selectedIndex = 1;
  form.error.selectedIndex = 0;
  form.access.selectedIndex = 0;
}
function checkPort(port) {
  if (port == <?=$emhttp?>) {
    alert("Port number already in use by EMHTTP");
    return false;
  }
  return true;
}
</script>
<form name="web_settings" method="POST" action="/update.php" target="progressFrame" onsubmit="return checkPort(this.port.value)">
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#include" value="update.web.php">
<input type="hidden" name="#config"  value="<?=$config?>">
<table class="settings">
  <tr>
  <td>Web server function:</td>
  <td><select name="service" size="1" onChange="presetWEB(this.form)">
<?=mk_option($cfg['service'], "0", "Disabled")?>
<?=mk_option($cfg['service'], "1", "Enabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Web root directory:</td>
  <td><input type="text" name="path" value="<?=$cfg['path']?>"></td>
  </tr>
  <tr>
  <td>Listening port:</td>
  <td><input type="text" name="port" value="<?=$cfg['port']?>">Don't use port <?=$emhttp?></td>
  </tr>
  <tr>
  <td>PHP error logging:</td>
  <td><select name="phpError" size="1">
<?=mk_option($cfg['phpError'], "Off", "Disabled")?>
<?=mk_option($cfg['phpError'], "On", "Enabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Error logging:</td>
  <td><select name="error" size="1">
<?=mk_option($cfg['error'], "0", "Disabled")?>
<?=mk_option($cfg['error'], "1", "Enabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Access logging:</td>
  <td><select name="access" size="1">
<?=mk_option($cfg['access'], "0", "Disabled")?>
<?=mk_option($cfg['access'], "1", "Enabled")?>
  </select></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetWEB(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
  <tr><td style="font-weight:normal;font-style:italic;font-size:smaller"><?=exec("$fName -v|awk '/^$sName/ {print $1}'|sed 's/\// version: /'")?> &bullet; <?=exec("php -v|awk '/^PHP/ {print $1,\"version:\",$2}'")?></td><td></td></tr>
</table>
</form>