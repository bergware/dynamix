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
$ftp_userlist_file = "/boot/config/vsftpd.user_list";
$ftp_userlist = "";
if (file_exists($ftp_userlist_file)) {
  $ftp_userlist = str_replace("\n", " ", trim(file_get_contents($ftp_userlist_file)));
  if ($ftp_userlist === false) {
    $ftp_userlist = "";
  }
}
?>
<script>
$(function() {
  $.ajax({url:'/plugins/webGui/include/ProcessStatus.php', data:'name=21', success:function(status) {$(".tabs").append(status);}});
});
</script>

<form method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <input type="hidden" name="cmd" value="echo">
  <input type="hidden" name="arg2" value="| tr ' ' '\n' > <?=$ftp_userlist_file?>">
  <tr>
  <td>FTP user(s): </td>
  <td><input type="text" name="arg1" size="40" maxlength="80" value="<?=$ftp_userlist?>"></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="runCmd" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>