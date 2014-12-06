<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (February 2014) */
?>
<?if (!array_key_exists( $name, $users)):?>
  <p class="notice">User <?=$name?> has been deleted.</p><br>
  <button type="button" onClick="done()">OK</button>
<?return;?>
<?endif;?>

<?$user = &$users[$name]?>

<form method="POST" action="/update.htm" target="progressFrame">
  <input type="hidden" name="userName" value="<?=$user['name']?>">
  <table class="settings">
  <tr>
  <td>Description:</td>
  <td><input type="text" name="userDesc" maxlength="64" value="<?=$user['desc']?>"></td>
  </tr>
  <tr>
<?if ($user['name']=="root"):?>
    <td></td>
<?else:?>
    <td>Delete<input type="checkbox" name="confirmDelete" onChange="chkDelete(this.form, this.form.cmdUserEdit)">
<?endif;?>
  </td>
  <td><input type="submit" name="cmdUserEdit" value="Apply"><button type="button" onClick="done()">Done</button></td>
  </tr>
  </table>
</form>
<br><br>
<form method="POST" action="/update.htm" target="progressFrame">
  <input type="hidden" name="userName" value="<?=$user['name']?>">
  <table class="settings">
  <tr>
  <td>Password:</td>
  <td><input type="password" name="userPassword" maxlength="40" onKeyUp="this.form.cmdUserEdit.disabled = (this.form.userPassword.value != this.form.userPasswordConf.value)">
  </td>
  </tr>
  <tr>
  <td>Retype password:</td>
  <td><input type="password" name="userPasswordConf" maxlength="40" onKeyUp="this.form.cmdUserEdit.disabled = (this.form.userPassword.value != this.form.userPasswordConf.value)">
  </td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="cmdUserEdit" value="Change"><button type="button" onClick="done()">Done</button></td>
  </tr>
  </table>
</form>