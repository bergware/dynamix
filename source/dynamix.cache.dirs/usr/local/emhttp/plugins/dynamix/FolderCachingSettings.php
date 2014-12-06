<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$plugin = 'dynamix.cache.dirs';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");

$sName = "cache_dirs";
$fName = "/usr/local/sbin/$sName";
$config = "/etc/cache_dirs.conf";
?>
<script>
$(function() {
  $.ajax({url:'/plugins/webGui/include/ProcessStatus.php',data:'name=<?=$sName?>',success:function(status){$('.tabs').append(status);}});
  presetCache(document.cache_settings);
});
function presetCache(form) {
  var disabled = form.service.value==0;
  var tags = ['select','input'];
  for (var n=0,tag; tag=tags[n]; n++) {
    for (var i=0,field; field=form.getElementsByTagName(tag)[i]; i++) field.disabled = (disabled && field.name.substr(0,1)!='#');
  }
  form.service.disabled = false;
}
function resetCache(form) {
  form.wait.selectedIndex = 1;
  form.busy.selectedIndex = 0;
  form.suspend.selectedIndex = 1;
  form.shares.selectedIndex = 0;
  form.minimum.value = '';
  form.maximum.value = '';
  form.depth.value = '';
  form.exclude.value = '';
  form.include.value = '';
  form.other.value = '';
}
</script>
<form name="cache_settings" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#include" value="update.cache.php">
<input type="hidden" name="#config"  value="<?=$config?>">
<input type="hidden" name="#prefix"  value="minimum=m&maximum=M&depth=d&exclude=e&include=i">
<table class="settings">
  <tr>
  <td>Folder caching function:</td>
  <td><select name="service" size="1" onchange="presetCache(this.form);">
<?=mk_option($cfg['service'], "0", "Disabled")?>
<?=mk_option($cfg['service'], "1", "Enabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Wait until array is online:</td>
  <td><select name="wait" size="1">
<?=mk_option($cfg['wait'], "", "No")?>
<?=mk_option($cfg['wait'], "-w", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Force disks busy:</td>
  <td><select name="busy" size="1">
<?=mk_option($cfg['busy'], "-B", "No")?>
<?=mk_option($cfg['busy'], "", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Suspend during 'Mover' process:</td>
  <td><select name="suspend" size="1">
<?=mk_option($cfg['suspend'], "-S", "No")?>
<?=mk_option($cfg['suspend'], "", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Scan user shares:</td>
  <td><select name="shares" size="1">
<?=mk_option($cfg['shares'], "", "No")?>
<?=mk_option($cfg['shares'], "-u", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Minimum interval between folder scans (sec):</td>
  <td><input type="text" name="minimum" maxlength="3" value="<?=$cfg['minimum']?>">default = 1 second</td>
  </tr>
  <tr>
  <td>Maximum interval between folder scans (sec):</td>
  <td><input type="text" name="maximum" maxlength="3" value="<?=$cfg['maximum']?>">default = 10 seconds</td>
  </tr>
  <tr>
  <td>Maximum scan level depth:</td>
  <td><input type="text" name="depth" maxlength="4" value="<?=$cfg['depth']?>">default = 9999</td>
  </tr>
  <tr>
  <td>Excluded folders (separated by comma):</td>
  <td><input type="text" name="exclude" maxlength="200" value="<?=$cfg['exclude']?>">default = none</td>
  </tr>
  <tr>
  <td>Included folders (separated by comma):</td>
  <td><input type="text" name="include" maxlength="200" value="<?=$cfg['include']?>">default = all</td>
  </tr>
  <tr>
  <td>User defined options:</td>
  <td><input type="text" name="other" maxlength="200" value="<?=$cfg['other']?>">see <u><a href="http://lime-technology.com/forum/index.php?topic=4500.0" target="_blank">unRAID forum</a></u></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetCache(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
  <tr><td style="font-weight:normal;font-style:italic;font-size:smaller"><?=exec("$fName -V")?></td><td></td></tr>
</table>
</form>