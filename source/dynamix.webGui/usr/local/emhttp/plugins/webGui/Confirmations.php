<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<script>
function resetConfirm(form) {
  form.down.selectedIndex = 1;
  form.stop.selectedIndex = 1;
<?if (file_exists("/var/log/plugins/dynamix.s3.sleep")):?>
  form.sleep.selectedIndex = 1;
<?endif;?>
  form.warn.selectedIndex = 1;
}
</script>
<form name="confirm_settings" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#plugin" value="dynamix.webGui">
<input type="hidden" name="#section" value="confirm">
<table class="settings">
  <tr>
  <td>Confirm reboot & powerdown commands:</td>
  <td><select name="down" size="1">
<?=mk_option($confirm['down'], "0", "No")?>
<?=mk_option($confirm['down'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Confirm array stop command:</td>
  <td><select name="stop" size="1">
<?=mk_option($confirm['stop'], "0", "No")?>
<?=mk_option($confirm['stop'], "1", "Yes")?>
  </select></td>
  </tr>
<?if (file_exists("/var/log/plugins/dynamix.s3.sleep")):?>
  <tr>
  <td>Confirm sleep command:</td>
  <td><select name="sleep" size="1">
<?=mk_option($confirm['sleep'], "0", "No")?>
<?=mk_option($confirm['sleep'], "1", "Yes")?>
  </select></td>
  </tr>
<?endif;?>
<?if (file_exists("/var/log/plugins/dynamix.disk.preclear")):?>
  <tr>
  <td>Confirm preclear stop command:</td>
  <td><select name="preclear" size="1">
<?=mk_option($confirm['preclear'], "0", "No")?>
<?=mk_option($confirm['preclear'], "1", "Yes")?>
  </select></td>
  </tr>
<?endif;?>
  <tr>
  <td>Uncommitted changes warning:</td>
  <td><select name="warn" size="1">
<?=mk_option($confirm['warn'], "0", "No")?>
<?=mk_option($confirm['warn'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetConfirm(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>