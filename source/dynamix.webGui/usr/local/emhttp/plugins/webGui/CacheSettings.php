<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<form name="cache_settings" method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
 <tr>
 <td>Use cache disk:</td>
 <td><select name="shareCacheEnabled" size="1" onchange="check_cache_settings();">
<?=mk_option($var['shareCacheEnabled'], "yes", "Yes");?>
<?=mk_option($var['shareCacheEnabled'], "no", "No");?>
 </select></td>
 </tr>
 <tr>
 <td>Min. free space:</td>
 <td><input type="text" name="shareCacheFloor" maxlength="16" value="<?=$var['shareCacheFloor'];?>"></td>
 </tr>
 <tr>
 <tr>
 <td></td>
 <td><input type="submit" name="changeShare" value="Apply"><button type="button" onClick="done();">Done</button></td>
 </tr>
</table>
</form>

<script type="text/javascript">
function check_cache_settings(){
  form = document.cache_settings;
  enabled = (form.shareCacheEnabled.value == "yes");
  form.shareCacheFloor.disabled = !enabled;
}
$(function(){
  check_cache_settings();
});
</script>
