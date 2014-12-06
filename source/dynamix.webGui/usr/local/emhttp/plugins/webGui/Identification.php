<?PHP
/* Copyright 2010, Lime Technology LLC.
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2, or (at your option)
* any later version.
*/
/* Adapted by Bergware International (December 2013) */
?>
<form name="NameSettings" method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>Server name: </td>
  <td><input type="text" name="NAME" maxlength="40" value="<?=$var['NAME'];?>"></td>
  </tr>
  <tr>
  <td>Comments:</td>
  <td><input type="text" name="COMMENT" maxlength="40" value="<?=$var['COMMENT'];?>"></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeNames" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>