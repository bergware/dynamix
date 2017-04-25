<?PHP
/* Copyright 2012-2017, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/webGui/include/Wrappers.php";

$plugin = $_GET['plugin'];
$cfg = parse_plugin_cfg($plugin);
?>

<script>
function done_plus(button) {
  try {button.click();}
  catch(e) {
    var event = document.createEvent('MouseEvents');
    event.initMouseEvent('click',true,true,window,0,0,0,0,0,false,false,false,false,0,null);
    button.dispatchEvent(event);
  }
}
$(function() {
  $('input[value="Apply"]').prop('disabled',true);
  $('form[name="host_names"]').find('input[type=text]').each(function(){$(this).on('input change',function() {
    var form = $(this).parentsUntil('form').parent();
    form.find('input[value="Apply"]').prop('disabled',false);
    form.find('input[value="Done"]').val('Reset').prop('onclick',null).click(function(){refresh(form.offset().top)});
  });});
});
</script>
<form name="host_names" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#file"  value="<?=$plugin.'/'.$plugin?>.cfg">
<input type="hidden" name="#cleanup" value="true">
<input type="hidden" name="csrf_token" value="<?=$_GET['csrf']?>">
<table class="settings">
<?
$online = array();
exec("lsof -OwlnPi 2>/dev/null|awk -F'>' '/^(smbd|afpd|Plex).*ESTABLISHED\)$/{print $2}'|cut -d':' -f1",$online);
foreach ($online as $host) {
  $ip = str_replace('.','_',$host);
  if (!isset($cfg[$ip])) $cfg[$ip] = "";
}
ksort($cfg);
foreach ($cfg as $ip => $name) {
  echo "<tr><td style='font-weight:normal'>".str_replace('_','.',$ip)."</td><td><input type='text' name='$ip' value=\"$name\"></td></tr>";
}
?>
<tr><td></td><td><input type="submit" name="#apply" value="Apply"><input type="button" value="Done" onclick="done_plus($('#tab1'))"></td></tr>
</table>
</form>
