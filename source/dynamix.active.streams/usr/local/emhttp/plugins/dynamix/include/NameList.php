<?PHP
/* Copyright 2013, Bergware International.
 * Styles modified by Andrew Hamer-Adams
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1],$_GET);
$plugin = $_GET['plugin'];
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");
$warn = $_GET['warn'];
?>
<script>
<?if ($warn):?>
$(function() {
  $('form').each(function(){$(this).change(function() {
    $.jGrowl("You have uncommitted form changes", {sticky: false, theme: "bottom", position: "bottom", life: 10000});});
  });
});
<?endif;?>

function done(button) {
  try {
    button.click();
  }
  catch (e) {
    var event = document.createEvent('MouseEvents');
    event.initMouseEvent('click',true,true,window,0,0,0,0,0,false,false,false,false,0,null);
    button.dispatchEvent(event);
  }
}
</script>
<form name="host_names" method="POST" action="/update.php" target="progressFrame" <?if ($warn):?>onsubmit="$('div.jGrowl-notification').trigger('jGrowl.close')"<?endif;?>>
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#cleanup" value="true">
<table class="settings">
  <tr><td style="font-size:12px;font-weight:bold">IP Address</td><td style="font-size:12px;font-weight:bold">User Name</td></tr>
<?
  $online = array();
  exec("lsof -i -n -P|awk -F'>' '/^(smbd|afpd|Plex).*ESTABLISHED\)$/ {print $2}'|cut -d':' -f1",$online);
  foreach ($online as $host) {
    $ip = str_replace('.','_',$host);
    if (!isset($cfg[$ip])) $cfg[$ip] = "";
  }
  ksort($cfg);
  foreach ($cfg as $ip => $name) {
    echo "<tr><td style='font-weight:normal'>".str_replace('_','.',$ip)."</td><td><input type='text' name='$ip' value='$name'></td></tr>";
  }
?>
  <tr><td></td><td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done($('#tab1'))">Done</button></td></tr>
</table>
</form>