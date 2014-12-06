<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$plugin = 'dynamix.disk.health';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");
?>
<script>
function resetHealth(form) {
  form.poll.selectedIndex = 0;
}
</script>
<form name="health_settings" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#plugin" value="<?=$plugin?>">
<table class="settings">
  <tr>
  <td>Enable background polling for spun-down disks:</td>
  <td><select name="poll" size="1">
<?=mk_option($cfg['poll'], "0", "No")?>
<?=mk_option($cfg['poll'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetHealth(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>