<?PHP
/* Copyright 2013, Andrew Hamer-Adams, http://www.pixeleyes.co.nz.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<style>
.dataTables_filter {margin-top:-32px;}
.bottom {padding:10px;}
</style>
<script>
$(function() {
  $('#notify').dataTable( {
  "bPaginate": true,
  "sPaginationType": "full_numbers",
  "bProcessing": true,
  "bFilter": true,
  "bSort": true,
  "aaSorting": [[1,'desc']],
  "bInfo": true,
  "iDisplayLength": 10,
  "sDom": '<"top"<"clear">f<"clear">>rt<"bottom"lp>'
  });
});

function resetNotify(form) {
  form.date.selectedIndex = 0;
  form.time.selectedIndex = 0;
  form.path.value = "/tmp/notifications";
  form.position.selectedIndex = 1;
}
</script>
<form name="notify_settings" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#plugin" value="dynamix.webGui">
<input type="hidden" name="#section" value="notify">
<table class="settings">
  <tr>
  <td>Date format:</td>
  <td><select name="date" size="1">
  <?=mk_option($notify['date'], "d/m/Y", "DD-MM-YYYY")?>
  <?=mk_option($notify['date'], "m/d/Y", "MM-DD-YYYY")?>
  </select></td>
  </tr>
  <tr>
  <td>Time format:</td>
  <td><select name="time" size="1">
  <?=mk_option($notify['time'], "h:i A", "12 hours")?>
  <?=mk_option($notify['time'], "H:i", "24 hours")?>
  </select></td>
  </tr>
  <tr>
  <td>Storage path:</td>
  <td><input type="text" name="path" value="<?=$notify['path']?>"><?=(exec("echo {$notify['path']} | grep '^/mnt'") ? 'Permanent storage' : 'Temporary storage')?></td>
  </tr>
  <tr>
  <td>Display position:</td>
  <td><select name="position" size="1">
  <?=mk_option($notify['position'], "top-left", "top-left")?>
  <?=mk_option($notify['position'], "top-right", "top-right")?>
  <?=mk_option($notify['position'], "bottom-left", "bottom-left")?>
  <?=mk_option($notify['position'], "bottom-right", "bottom-right")?>
  <?=mk_option($notify['position'], "center", "center")?>
  </select></td>
  </tr>
  <td><button type="button" onclick="resetNotify(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>