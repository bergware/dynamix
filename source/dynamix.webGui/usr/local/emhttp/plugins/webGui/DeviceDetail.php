<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<?$disk = &$disks[$name]?>

<form method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>Partition size:</td>
  <td><?=my_number($disk['size'])?> KB (K=1024)</td>
  </tr>
  <tr>
  <td>Partition format:</td>
  <td><?=$disk['format']?></td>
  </tr>
<?if ($disk['name']!="parity"): ?>
  <tr>
  <td>File system type:</td>
  <td><?=$disk['fsType']?></td>
  </tr>
<?endif; ?>
  <tr>
  <td>Spin down delay:</td>
  <td><select name="diskSpindownDelay.<?=$disk['idx']?>" size="1">
<?=mk_option($disk['spindownDelay'], "-1", "Use default")?>
<?=mk_option($disk['spindownDelay'], "0",  "Never")?>
<?=mk_option($disk['spindownDelay'], "15", "15 minutes")?>
<?=mk_option($disk['spindownDelay'], "30", "30 minutes")?>
<?=mk_option($disk['spindownDelay'], "45", "45 minutes")?>
<?=mk_option($disk['spindownDelay'], "1",  "1 hour")?>
<?=mk_option($disk['spindownDelay'], "2",  "2 hours")?>
<?=mk_option($disk['spindownDelay'], "3",  "3 hours")?>
<?=mk_option($disk['spindownDelay'], "4",  "4 hours")?>
<?=mk_option($disk['spindownDelay'], "5",  "5 hours")?>
<?=mk_option($disk['spindownDelay'], "6",  "6 hours")?>
<?=mk_option($disk['spindownDelay'], "7",  "7 hours")?>
<?=mk_option($disk['spindownDelay'], "8",  "8 hours")?>
<?=mk_option($disk['spindownDelay'], "9",  "9 hours")?>
  </select></td>
  </tr>
<?if (($var['spinupGroups']=="yes")&&($disk['name']!="cache")):?>
  <tr>
  <td>Spinup group(s):</td>
  <td><input type="text" name="diskSpinupGroup.<?=$disk['idx'];?>" maxlength="256" value="<?=$disk['spinupGroup'];?>"></td>
  </tr>
<?endif;?>
  <tr>
  <td></td>
  <td><input type="submit" name="changeDisk" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>