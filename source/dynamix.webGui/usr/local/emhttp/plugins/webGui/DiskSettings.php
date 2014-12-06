<?PHP
/* Copyright 2010, Lime Technology LLC.
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2, or (at your option)
* any later version.
*/
/* Adapted by Bergware International (December 2013) */
?>
<form method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>Enable auto start:</td>
  <td><select name="startArray" size="1">
<?=mk_option($var['startArray'], "no", "No");?>
<?=mk_option($var['startArray'], "yes", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Default spin down delay:</td>
  <td><select name="spindownDelay" size="1">
<?=mk_option($var['spindownDelay'], "0",  "Never");?>
<?=mk_option($var['spindownDelay'], "15", "15 minutes");?>
<?=mk_option($var['spindownDelay'], "30", "30 minutes");?>
<?=mk_option($var['spindownDelay'], "45", "45 minutes");?>
<?=mk_option($var['spindownDelay'], "1",  "1 hour");?>
<?=mk_option($var['spindownDelay'], "2",  "2 hours");?>
<?=mk_option($var['spindownDelay'], "3",  "3 hours");?>
<?=mk_option($var['spindownDelay'], "4",  "4 hours");?>
<?=mk_option($var['spindownDelay'], "5",  "5 hours");?>
<?=mk_option($var['spindownDelay'], "6",  "6 hours");?>
<?=mk_option($var['spindownDelay'], "7",  "7 hours");?>
<?=mk_option($var['spindownDelay'], "8",  "8 hours");?>
<?=mk_option($var['spindownDelay'], "9",  "9 hours");?>
  </select></td>
  </tr>
  <tr>
  <td>Force NCQ disabled:</td>
  <td><select name="queueDepth" size="1">
<?=mk_option($var['queueDepth'], "0", "No");?>
<?=mk_option($var['queueDepth'], "1", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Enable spinup groups:</td>
  <td><select name="spinupGroups" size="1">
<?=mk_option($var['spinupGroups'], "no", "No");?>
<?=mk_option($var['spinupGroups'], "yes", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Default partition format:</td>
  <td><select name="defaultFormat" size="1">
<?=mk_option($var['defaultFormat'], "1", "MBR: unaligned");?>
<?=mk_option($var['defaultFormat'], "2", "MBR: 4K-aligned");?>
  </select></td>
  </tr>
  <tr>
  <td>Tunable (md_num_stripes):</td>
  <td><input type="text" name="md_num_stripes" maxlength="10" value="<?=$var['md_num_stripes'];?>"><?=$var['md_num_stripes_status'];?></td>
  </tr>
  <tr>
  <td>Tunable (md_write_limit):</td>
  <td><input type="text" name="md_write_limit" maxlength="10" value="<?=$var['md_write_limit'];?>"><?=$var['md_write_limit_status'];?></td>
  </tr>
  <tr>
  <td>Tunable (md_sync_window):</td>
  <td><input type="text" name="md_sync_window" maxlength="10" value="<?=$var['md_sync_window'];?>"><?=$var['md_sync_window_status'];?></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeDisk" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
