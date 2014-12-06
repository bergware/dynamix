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
  <td>Enable NFS:</td>
  <td><select name="shareNFSEnabled" size="1">
<?=mk_option($var['shareNFSEnabled'], "no", "No");?>
<?=mk_option($var['shareNFSEnabled'], "yes", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Tunable (fuse_remember):</td>
  <td><input type="text" name="fuse_remember" maxlength="10" value="<?=$var['fuse_remember'];?>"><?=$var['fuse_remember_status'];?></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>