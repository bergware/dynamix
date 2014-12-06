<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$plugin = 'dynamix.system.stats';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");

unset($sPorts);
exec("ifconfig -s|awk '$1~/[0-9]$/ {print $1}'",$sPorts);
?>
<script>
function setDropdown() {
  $('#s1').dropdownchecklist({emptyText:'None', width:300, explicitClose:'...close'});
}
function prepareStats(form) {
  if (!form.critical.value | !form.warning.value) {
    alert('An empty percentage value is not allowed!');
    return false;
  }
  if (form.warning.value>form.critical.value) {
    alert('Warning percentage must be lower than Critical percentage!');
    return false;
  }
  var show = '';
  for (var i=0,item; item=form.show.options[i]; i++) {
    if (item.selected) {
      if (show.length) show += ',';
      show += item.value;
      item.selected = false;
    }
  }
  item = form.show.options[0];
  item.value = show;
  item.selected = true;
  return true;
}
function resetStats(form) {
  form.text.selectedIndex = 0;
  form.critical.value = 90;
  form.warning.value = 70;
  form.text.selectedIndex = 0;
  $('#s1').dropdownchecklist('destroy');
  for (var i=0,item; item=form.show.options[i]; i++) item.selected = true;
  setDropdown();
  form.cols.selectedIndex = 1;
  form.size.selectedIndex = 1;
  form.port.selectedIndex = 0;
  form.unit.selectedIndex = 0;
  form.graph.selectedIndex = 0;
  form.frame.selectedIndex = 3;
}
$(setDropdown);
</script>
<form name="stats_settings" method="POST" action="/update.php" target="progressFrame" onsubmit="return prepareStats(this)">
<input type="hidden" name="#plugin" value="<?=$plugin?>">
<table class="settings">
  <tr>
  <td>Critical percentage level (%): </td>
  <td><input type="text" name="critical" maxlength="2" value="<?=$cfg['critical']?>"></td>
  </tr>
  <tr>
  <td>Warning percentage level (%):</td>
  <td><input type="text" name="warning" maxlength="2" value="<?=$cfg['warning']?>"></td>
  </tr>
  <tr>
  <td>Position of disk usage percentage:</td>
  <td><select name="text" size="1">
<?=mk_option($cfg['text'], "left", "Left")?>
<?=mk_option($cfg['text'], "right", "Right")?>
  </select></td>
  </tr>
  <tr>
  <td>Show these system graphs:</td>
  <td><select id="s1" name="show" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?=mk_option_check($cfg['show'], "cpu", "Processor")?>
<?=mk_option_check($cfg['show'], "ram", "Memory")?>
<?=mk_option_check($cfg['show'], "com", "Network")?>
<?=mk_option_check($cfg['show'], "hdd", "Storage")?>
  </select></td>
  </tr>
  <tr>
  <td>Number of system graphs per row:</td>
  <td><select name="cols" size="1">
<?=mk_option($cfg['cols'], "0", "One")?>
<?=mk_option($cfg['cols'], "1", "Two")?>
<?=mk_option($cfg['cols'], "2", "Three")?>
<?=mk_option($cfg['cols'], "3", "Four")?>
  </select></td>
  </tr>
  <tr>
  <td>Show disk size:</td>
  <td><select name="size" size="1">
<?=mk_option($cfg['size'], "0", "No")?>
<?=mk_option($cfg['size'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Ethernet interface:</td>
  <td><select name="port" size="1">
<?foreach ($sPorts as $port):?>
<?=mk_option_check($cfg['port'], $port, $port)?>
<?endforeach;?>
  </select></td>
  </tr>
  <tr>
  <td>Network graph display unit:</td>
  <td><select name="unit" size="1">
<?=mk_option($cfg['unit'], "b", "Bits per second")?>
<?=mk_option($cfg['unit'], "B", "Bytes per second")?>
  </select></td>
  </tr>
  <tr>
  <td>Initial graphing mode:</td>
  <td><select name="graph" size="1">
<?=mk_option($cfg['graph'], "0", "Real-time")?>
<?=mk_option($cfg['graph'], "1", "Last day")?>
<?=mk_option($cfg['graph'], "2", "Last 2 days")?>
<?=mk_option($cfg['graph'], "3", "Last 3 days")?>
<?=mk_option($cfg['graph'], "7", "Last week")?>
<?=mk_option($cfg['graph'], "14", "Last 2 weeks")?>
<?=mk_option($cfg['graph'], "21", "Last 3 weeks")?>
<?=mk_option($cfg['graph'], "31", "Last month")?>
<?=mk_option($cfg['graph'], "3653", "Since start")?>
  </select></td>
  </tr>
  <tr>
  <td>Initial real-time sliding window:</td>
  <td><select name="frame" size="1">
<?=mk_option($cfg['frame'], "15", "30 seconds")?>
<?=mk_option($cfg['frame'], "30", "1 minute")?>
<?=mk_option($cfg['frame'], "60", "2 minutes")?>
<?=mk_option($cfg['frame'], "150", "5 minutes")?>
<?=mk_option($cfg['frame'], "300", "10 minutes")?>
<?=mk_option($cfg['frame'], "900", "30 minutes")?>
<?=mk_option($cfg['frame'], "1800", "1 hour")?>
<?=mk_option($cfg['frame'], "3600", "2 hours")?>
  </select></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetStats(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>