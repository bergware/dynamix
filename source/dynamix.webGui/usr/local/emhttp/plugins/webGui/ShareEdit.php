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
/* default values when adding new share */
$share = array("nameOrig"   => "",
               "name"       => "",
               "comment"    => "",
               "allocator"  => "highwater",
               "floor"      => "0",
               "splitLevel" => "",
               "include"    => "",
               "exclude"    => "",
               "useCache"   => "no");

/* check for empty share */
function shareEmpty($name) {
  return (($files = @scandir('/mnt/user/'.$name)) && (count($files) <= 2));
}

$split = array(""    => "Not set - split function not used",
               "0"   => "Level 0 - combine files to the disk where share '$name' originates.",
               "1"   => "Level 1 - combine files to top level subfolders within share '$name'.",
               "2"   => "Level 2 - combine files to second level subfolders within share '$name'.",
               "3"   => "Level 3 - combine files to third level subfolders within share '$name'.",
               "4"   => "Level 4 - combine files to fourth level subfolders within share '$name'.",
               "9"   => "Disabled - combine files to share '$name' using the allocation method.");

if ($name != ""):
  if (array_key_exists( $name, $shares)):
    $share = $shares[$name];
  else:
?>  <p class="notice">Share <?=$name?> has been deleted.</p><br>
    <button type="button" onClick="done();">OK</button>
<?  return;
  endif;
endif;
?>
<script>
$(function() {
  presetSpace(document.share_edit.shareFloor);
  $("#s0").dropdownchecklist({emptyText:'', width:300, explicitClose:'...close'});
  $("#s1").dropdownchecklist({emptyText:'All', width:300, explicitClose:'...close'});
  $("#s2").dropdownchecklist({emptyText:'None', width:300, explicitClose:'...close'});
});

function presetImage(on) {
  var off = [0,1,2,3,4,9];
  for (var i=0; i<off.length; i++) document.getElementById('split'+off[i]).style.display = "none";
  document.getElementById('split'+on).style.display = "";
}

function presetSpace(shareFloor) {
  var unit = ['KB','MB','GB','TB','PB'];
  var scale = shareFloor.value;
  if (scale.replace(/[0-9.,\s]/g,'').length>0) return;
  var base = scale>0 ? Math.floor(Math.log(scale)/Math.log(1000)) : 0;
  if (base>=unit.length) base = unit.length-1;
  shareFloor.value = (scale/Math.pow(1000, base))+unit[base];
}

// Fool unRAID by simulating the original input fields
function prepareEdit(form) {
// Test share name validity
  var share = form.shareName.value;
  if (!share) {
    alert('Please enter a share name');
    return false;
  }
  if (share.match('^disk[0-9]+$')) {
    alert('Invalid share name specified\nDo not use reserved names.');
    return false;
  }
  if (share.match(' ')) {
    alert('Warning: using spaces in the share name may give unpredictable results.');
  }
// Adjust minimum free space value to selected unit
  var unit = 'KB,MB,GB,TB,PB';
  var scale = form.shareFloor.value;
  var index = unit.indexOf(scale.replace(/[0-9.,\s]/g,'').toUpperCase());
  form.shareFloor.value = scale.replace(/[A-Z\s]/gi,'') * Math.pow(1000, (index>0 ? index/3 : 0))
// Return splitlevel as single value
  for (var i=0,item; item=form.shareSplitLevel.options[i]; i++) {
    if (item.selected) {
      var split = item.value;
      item.selected = false;
      break;
    }
  }
  item = form.shareSplitLevel.options[0];
  item.value = split;
  item.selected = true;
// Return include as single line input
  var include = '';
  for (var i=0,item; item=form.shareInclude.options[i]; i++) {
    if (item.selected) {
      if (include.length) include += ',';
      include += item.value;
      item.selected = false;
    }
  }
  item = form.shareInclude.options[0];
  item.value = include;
  item.selected = true;
// Return exclude as single line input
  var exclude = '';
  for (var i=0,item; item=form.shareExclude.options[i]; i++) {
    if (item.selected) {
      if (exclude.length) exclude += ',';
      exclude += item.value;
      item.selected = false;
    }
  }
  item = form.shareExclude.options[0];
  item.value = exclude;
  item.selected = true;

  return true;
}
</script>

<form name="share_edit" method="POST" action="/update.htm" target="progressFrame" onsubmit="return prepareEdit(this)">
<input type="hidden" name="shareNameOrig" value="<?=$share['nameOrig']?>">
<table class="settings">
  <tr>
  <td>Name:</td>
  <td><input type="text" name="shareName" maxlength="40" value="<?=$share['name']?>"></td>
  </tr>
  <tr>
  <td>Comments:</td>
  <td><input type="text" name="shareComment" maxlength="256" value="<?=$share['comment']?>"></td>
  </tr>
  <tr>
  <td>Allocation method:</td>
  <td><select name="shareAllocator" size="1">
<?=mk_option($share['allocator'], "highwater", "High-water")?>
<?=mk_option($share['allocator'], "mostfree", "Most-free")?>
<?=mk_option($share['allocator'], "fillup", "Fill-up")?>
  </select></td>
  </tr>
  <tr>
  <td>Minimum free space:</td>
  <td><input type="text" name="shareFloor" maxlength="16" value="<?=$share['floor']?>"></td>
  </tr>
  <tr>
  <td>Split level:</td>
  <td><select id="s0" name="shareSplitLevel" size="1" style="display:none" onchange="presetImage(this.form.shareSplitLevel.value)">
<?foreach ($split as $level => $text):?>
<?=mk_option($share['splitLevel'], strval($level), $text)?>
<?endforeach;?>
  </select></td>
  </tr>
  <tr>
  <td>Included disk(s):</td>
  <td><select id="s1" name="shareInclude" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?foreach ($disks as $disk):?>
<?=mk_option_check($disk['name'], $share['include'])?>
<?endforeach;?>
  </select></td>
  </tr>
  <tr>
  <td>Excluded disk(s):</td>
  <td><select id="s2" name="shareExclude" size="1" multiple="multiple" style="display:none">
  <option value='' selected></option>
<?foreach ($disks as $disk):?>
<?=mk_option_check($disk['name'], $share['exclude'])?>
<?endforeach;?>
  </select></td>
  </tr>
<?if ($var['cacheActive']=="yes"):?>
  <tr>
  <td>Use cache disk:</td>
  <td><select name="shareUseCache" size="1">
<?=mk_option($share['useCache'], "no", "No")?>
<?=mk_option($share['useCache'], "yes", "Yes")?>
<?=mk_option($share['useCache'], "only", "Only")?>
  </select></td>
  </tr>
<?endif;
  if ($share['name'] == ""):
?><tr>
  <td></td>
  <td><input type="submit" name="cmdEditShare" value="Add Share"><button type="button" onclick="done();">Cancel</button>
  </td>
  </tr>
<?elseif (shareEmpty($share['name'])):?>
  <tr>
  <td>Share empty?</td>
  <td>Yes</td>
  </tr>
  <tr>
  <td>Delete<input type="checkbox" name="confirmDelete" onchange="chkDelete(this.form, this.form.cmdEditShare);"></td>
  <td><input type="submit" name="cmdEditShare" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
<?else:?>
  <tr>
  <td>Share empty?</td>
  <td>No</td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="cmdEditShare" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
<?endif;?>
</table>
</form>
<div style="float:right">
<div id="split0" class="split0" style="display:none"></div>
<div id="split1" class="split1" style="display:none"></div>
<div id="split2" class="split2" style="display:none"></div>
<div id="split3" class="split3" style="display:none"></div>
<div id="split4" class="split4" style="display:none"></div>
<div id="split9" class="split9" style="display:none"></div>
</div>