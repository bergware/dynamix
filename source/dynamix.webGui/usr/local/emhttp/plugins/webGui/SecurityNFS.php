<?PHP
/* Copyright 2010, Lime Technology LLC.
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2, or (at your option)
* any later version.
*/
/* Adapted by Bergware International (December 2013) */
?>
<?if ($var['shareNFSEnabled'] != "yes"):?>
<p class="notice">NFS is not enabled</p>
<?return;?>
<?endif;?>

<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="settings">
  <tr>
  <td>Export:</td>
  <td><select name="shareExportNFS" size="1">
<?=mk_option($sec_nfs[$name]['export'], "-", "No");?>
<?=mk_option($sec_nfs[$name]['export'], "e", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Security:</td>
  <td><select name="shareSecurityNFS" size="1">
<?=mk_option($sec_nfs[$name]['security'], "public", "Public");?>
<?=mk_option($sec_nfs[$name]['security'], "secure", "Secure");?>
<?=mk_option($sec_nfs[$name]['security'], "private", "Private");?>
  </select></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareSecurityNFS" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>

<?if ($sec_nfs[$name]['security']=="private"):?>
<form method="POST" name="otherForm" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="access_list">
  <tr>
  <td></td>
  <td></td>
  </tr>
  <tr>
  <td>Rule:</td>
  <td><input type="text" name="shareHostListNFS" maxlength="256" value="<?=$sec_nfs[$name]['hostList'];?>"</td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareAccessNFS" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?endif;?>