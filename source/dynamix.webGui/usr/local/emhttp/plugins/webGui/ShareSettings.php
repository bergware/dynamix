<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<script>
$(function() {
  $("#s1").dropdownchecklist({emptyText:'All', width:300, explicitClose:'...close'});
  $("#s2").dropdownchecklist({emptyText:'None', width:300, explicitClose:'...close'});
  presetShare(document.share_settings);
});
// Fool unRAID by simulating the original input field
function prepareShare(form) {
  var include = '';
  for (var i=0,item; item=form.shareUserInclude.options[i]; i++) {
    if (item.selected) {
      if (include.length) include += ',';
      include += item.value;
      item.selected = false;
    }
  }
  item = form.shareUserInclude.options[0];
  item.value = include;
  item.selected = true;
  var exclude = '';
  for (var i=0,item; item=form.shareUserExclude.options[i]; i++) {
    if (item.selected) {
      if (exclude.length) exclude += ',';
      exclude += item.value;
      item.selected = false;
    }
  }
  item = form.shareUserExclude.options[0];
  item.value = exclude;
  item.selected = true;
}
function presetShare(form) {
  var disabled = form.shareUser.value!="e";
  var onOff = disabled ? 'disable' : 'enable';
  form.shareUserInclude.disabled = disabled;
  form.shareUserExclude.disabled = disabled;
  $("#s1").dropdownchecklist(onOff);
  $("#s2").dropdownchecklist(onOff);
}
</script>
<form name="share_settings" method="POST" action="/update.htm" target="progressFrame" onsubmit="prepareShare(this)">
<table class="settings">
  <tr>
  <td>Enable user shares:</td>
  <td><select name="shareUser" size="1" onchange="presetShare(this.form)">
<?=mk_option($var['shareUser'], "e", "Yes")?>
<?=mk_option($var['shareUser'], "-", "No")?>
  </select></td>
  </tr>
  <tr>
  <td>Included disk(s):</td>
  <td><select id="s1" name="shareUserInclude" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?foreach ($disks as $disk):?>
<?=mk_option_check($disk['name'], $var['shareUserInclude'])?>
<?endforeach;?>
  </select></td>
  </tr>
  <tr>
  <td>Excluded disk(s):</td>
  <td><select id="s2" name="shareUserExclude" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?foreach ($disks as $disk):?>
<?=mk_option_check($disk['name'], $var['shareUserExclude'])?>
<?endforeach;?>
</select></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>