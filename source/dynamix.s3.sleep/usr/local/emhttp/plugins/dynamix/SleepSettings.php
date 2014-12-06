<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 * 2013/12/29 SlrG added feature to include/exclude drives outside of array
 */
?>
<?
$plugin = 'dynamix.s3.sleep';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");
$sName = "s3_sleep";
$fName = "/usr/local/sbin/$sName";
$config = "/etc/s3_sleep.conf";
$folder = "/usr/local/bin";

unset($sPorts);
exec("ifconfig -s|awk '$1~/[0-9]$/ {print $1}'",$sPorts);

unset($sExcludeList);
exec("$fName -ED",$sExcludeList);

$days = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
?>
<script>
$(function() {
  $("#s1").dropdownchecklist({emptyText:'None', width:300, explicitClose:'...close'});
  $("#s2").dropdownchecklist({emptyText:'None', width:300, explicitClose:'...close'});
  $.ajax({url:'/plugins/webGui/include/ProcessStatus.php',data:'name=<?=$sName?>',success:function(status) {$('.tabs').append(status);}});
  presetSleep(document.sleep_settings);
});
<?if (array_filter($sExcludeList)):?>
function resetSelected() {
  names = document.getElementsByName (' selected');
  for (var x=0; x < names.length; x++)
    names[x].selected=true;
}

function countSelected(id){
  var options = document.getElementById(id).options;
  count = 0;

  for (var i=0; i < options.length; i++) {
    if (options[i].selected) count++;
  }
  return count;
}
<?endif;?>

function prepareSleep(form) {
<?if (array_filter($sExcludeList)):?>
  var sindex = form.exclude.selectedIndex;
  if ((sindex==1 || sindex==2) && countSelected('excludeList') == 0) {
    form.exclude.scrollIntoView();
    alert ("Please select at least one drive to exclude/include!");
    return false;
  }
<?endif;?>
  var days = '';
  for (var i=0,item; item=form.stopDay.options[i]; i++) {
    if (item.selected) {
      if (days.length) days += ',';
      days += item.value;
      item.selected = false;
    }
  }
  item = form.stopDay.options[0];
  item.value = days;
  item.selected = true;
  var hours = '';
  for (var i=0,item; item=form.stopHour.options[i]; i++) {
    if (item.selected) {
      if (hours.length) hours += ',';
      hours += item.value;
      item.selected = false;
    }
  }
  item = form.stopHour.options[0];
  item.value = hours;
  item.selected = true;
  form.pingIP.value = form.pingIP.value.replace(/,/g,' ').replace(/\s+/g,',');
}

function presetSleep(form) {
  var disabled = form.service.value==0;
  var onOff = disabled ? 'disable' : 'enable';
  var tags = ['select','input','textarea'];
  for (var n=0,tag; tag=tags[n]; n++) {
    for (var i=0,field; field=form.getElementsByTagName(tag)[i]; i++) field.disabled = (disabled && field.name.substr(0,1)!='#');
  }
  form.service.disabled = false;
  $("#s1").dropdownchecklist(onOff);
  $("#s2").dropdownchecklist(onOff);
  if (!disabled) {
<?if (array_filter($sExcludeList)):?>
    changeExclude(form);
<?endif;?>
    changeIdle(form); 
    changePort(form);
  }
  logNote(form);
  return true;
}
function resetSleep(form) {
  form.timeout.value = 30;
  form.checkTCP.selectedIndex = 0;
  form.idle.value = 0;
  form.login.selectedIndex = 0;
  form.pingIP.value = '';
  form.stopHour.value = '';
  form.stopDay.value = '';
  form.port.selectedIndex = 0;
  form.forceGb.selectedIndex = 0;
  form.dhcpRenew.selectedIndex = 0;
  form.setWol.value = '';
  form.preRun.value = '';
  form.postRun.value = '';
  form.debug.selectedIndex = 0;
  form.checkHDD.selectedIndex = 1;
<?if (array_filter($sExcludeList)):?>
  form.exclude.selectedIndex = 0;
  changeExclude(form);
<?endif;?>
  changeIdle(form);
  logNote(form);
}
<?if (array_filter($sExcludeList)):?>
function changeExclude(form) {
  var sindex = form.exclude.selectedIndex;
  var disabled = (sindex == 0 || sindex == 3);
  if (disabled)
    document.getElementById('excludeList').selectedIndex = -1;
  else
    resetSelected();
  document.getElementById('excludeList').disabled = disabled;
}
<?endif;?>
function changeIdle(form) {
  var disabled = form.checkTCP.value=='';
  form.idle.disabled = disabled;
}
function changePort(form) {
  var disabled = form.port.value.substr(0,3)!='eth';
  form.forceGb.disabled = disabled;
  form.dhcpRenew.disabled = disabled;
}
function logNote(form) {
  var note = form.debug.value;
  if (note==1 || note==3) {$("#note").show();} else {$("#note").hide();}
}
</script>
<form name="sleep_settings" method="POST" action="/update.php" target="progressFrame" onsubmit="return prepareSleep(this)">
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#include" value="update.sleep.php">
<input type="hidden" name="#config"  value="<?=$config?>">
<input type="hidden" name="#prefix"  value="timeout=m&port=e&idle=N&pingIP=i&stopHour=h&stopDay=d&setWol=w&debug=D">
<input type="hidden" name="#folder"  value="<?=$folder?>">
<table class="settings">
  <tr><td>Sleep or shutdown function:</td>
  <td><select name="service" size="1" onchange="presetSleep(this.form)">
<?=mk_option($cfg['service'], "0", "Disabled")?>
<?=mk_option($cfg['service'], "1", "Sleep")?>
<?=mk_option($cfg['service'], "2", "Shutdown")?>
  </select></td>
  </tr>
  <tr><td>Excluded days:</td>
  <td><select id="s2" name="stopDay" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?for ($d=0; $d<count($days); $d++):?>
<?=mk_option_check($cfg['stopDay'], strval($d), $days[$d])?>
<?endfor;?>
  </select></td>
  </tr>
  <tr><td>Excluded hours:</td>
  <td><select id="s1" name="stopHour" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?for ($h=0; $h<24; $h++):?>
<?=mk_option_check($cfg['stopHour'], sprintf("%02d", $h), sprintf("%02d:00", $h))?>
<?endfor;?>
  </select></td>
  </tr>
  <tr><td>Wait for array inactivity:</td>
  <td><select name="checkHDD" size="1">
<?=mk_option($cfg['checkHDD'], "", "No")?>
<?=mk_option($cfg['checkHDD'], "-a", "Yes")?>
<?if (is_dir("/mnt/cache")):?>
<?=mk_option($cfg['checkHDD'], "-a -c", "Yes, exclude Cache")?>
<?endif;?>
  </select></td>
  </tr>
<?if (array_filter($sExcludeList)):?>
  <tr><td>Exclude disks outside array:</td>
    <td><select name="exclude" size="1" onChange="changeExclude(this.form)">
    <?=mk_option($cfg['exclude'], "", "No")?>
    <?=mk_option($cfg['exclude'], "-A -E", "Only selected")?>
    <?=mk_option($cfg['exclude'], "-A -I", "All, but selected")?>
    <?=mk_option($cfg['exclude'], "-A", "All")?>
    </select></td>
  </tr>
  <tr><td></td><td><select id="excludeList" name="excludeList[]" width="320" style="width:320px;" multiple>
  <?
  $excludesMarked = $cfg['excludeList'];
  $excludesMarkedArray = explode (",", $excludesMarked);
  foreach ($sExcludeList as $excludeOption){
    $selected = "";
    foreach ($excludesMarkedArray as $string){
      if (strpos($excludeOption, $string) !== false) $selected = " selected";
    }
    $parts = explode ("|", $excludeOption);
    echo ("<option name=\"$selected\" value=\"$parts[1]\"$selected>$excludeOption</option>\n");
  }
  ?>
    </select>Multiselect with &lt;Ctrl&gt; or &lt;Shift&gt; key.</td>
  </tr>
<?else:?>
  <input type="hidden" name="exclude" value="">
  <input type="hidden" name="excludeList" value="">
<?endif;?>
  <tr><td>Extra delay after array inactivity (minutes):</td>
  <td><input type="text" name="timeout" maxlength="2" value="<?=$cfg['timeout']?>">default = 30 minutes</td>
  </tr>
  <tr><td>Wait for network inactivity:</td>
  <td><select name="checkTCP" size="1" onchange="changeIdle(this.form)">
<?=mk_option($cfg['checkTCP'], "", "No")?>
<?=mk_option($cfg['checkTCP'], "-n", "Yes")?>
  </select></td>
  </tr>
  <tr><td>Network idle threshold (kB/s):</td>
  <td><input type="text" name="idle" maxlength="4" value="<?=$cfg['idle']?>">default = 0 kB/s</td>
  </tr>
  <tr><td>Wait for device inactivity (address):</td>
  <td><input type="text" name="pingIP" maxlength="200" value="<?=$cfg['pingIP']?>">default = no devices</td>
  </tr>
  <tr><td>Wait for user login inactivity:</td>
  <td><select name="login" size="1">
<?=mk_option($cfg['login'], "", "No")?>
<?=mk_option($cfg['login'], "-l", "Local")?>
<?=mk_option($cfg['login'], "-L", "Remote")?>
<?=mk_option($cfg['login'], "-l -L", "Local & Remote")?>
  </select></td>
  </tr>
  <tr><td>Ethernet interface:</td>
  <td><select name="port" size="1" onchange="changePort(this.form)">
<?foreach ($sPorts as $port):?>
<?=mk_option_check($cfg['port'], $port, $port)?>
<?endforeach;?>
  </select></td>
  </tr>
  <tr><td>Force gigabit speed after wake-up:</td>
  <td><select name="forceGb" size="1">
<?=mk_option($cfg['forceGb'], "", "No")?>
<?=mk_option($cfg['forceGb'], "-F", "Yes")?>
  </select></td>
  </tr>
  <tr><td>DHCP renewal after wake-up:</td>
  <td><select name="dhcpRenew" size="1">
<?=mk_option($cfg['dhcpRenew'], "", "No")?>
<?=mk_option($cfg['dhcpRenew'], "-R", "Yes")?>
  </select></td>
  </tr>
  <tr><td>Set WOL options before sleep:</td>
<?unset($wakeon); exec("ethtool {$cfg['port']}|awk -F':' '/Wake-on/ {print $2}'",$wakeon)?>
  <td><input type="text" name="setWol" maxlength="8" value="<?=$cfg['setWol']?>"> <?if ($wakeon):?>current = <?=$wakeon[1]?> (available options: <strong><?=$wakeon[0]?></strong>)<?endif;?></td>
  </tr>
  <tr><td>Custom commands before sleep:</td>
  <td><textarea name="preRun" rows="3" columns="120" wrap="off"><?=urldecode($cfg['preRun'])?></textarea></td>
  </tr>
  <tr><td>Custom commands after wake-up:</td>
  <td><textarea name="postRun" rows="3" columns="120" wrap="off"><?=urldecode($cfg['postRun'])?></textarea></td>
  </tr>
  <tr><td>Enable DEBUG mode:</td>
  <td><select name="debug" size="1" onchange="logNote(this.form)">
<?=mk_option($cfg['debug'], "0", "No")?>
<?=mk_option($cfg['debug'], "1", "Syslog and flash")?>
<?=mk_option($cfg['debug'], "2", "Syslog")?>
<?=mk_option($cfg['debug'], "3", "Flash")?>
<?=mk_option($cfg['debug'], "4", "Console")?>
  </select><span id="note" style="color:red;display:none">Log will be stored in <b>"/boot/logs/<?=$sName?>.log"</b></span></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetSleep(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
  <tr><td style="font-weight:normal;font-style:italic;font-size:smaller"><?=exec("$fName -V")?></td><td></td></tr>
</table>
</form>