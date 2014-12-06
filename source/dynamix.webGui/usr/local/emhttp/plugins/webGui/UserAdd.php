<?PHP
/* Copyright 2010, Lime Technology LLC.
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2, or (at your option)
* any later version.
*/
/* Adapted by Bergware International (June 2014) */
?>
<?
$help = <<<EOF
<p>Usernames must start with a lower case letter or an underscore, followed by lower case
letters, digits, underscores, or dashes. They can end with a dollar sign. In regular
expression terms: [a-z_][a-z0-9_-]*[$]?

<p>Usernames may only be up to 32 characters long.
EOF
?>
<script>
function checkUsername(userName) {
  if (!userName) {
    alert('Please enter a user name');
    return false;
  }
  if (userName.match('[A-Z]| ')) {
    alert('Invalid user name specified\nDo not use uppercase or space characters.');
    return false;
  }
  if (userName.match('^disk[0-9]+$')) {
    alert('Invalid user name specified\nDo not use reserved names.');
    return false;
  }
  return true;
}
</script>

<form method="POST" action="/update.htm" target="progressFrame" onsubmit="return checkUsername(this.userName.value)">
<table class="settings">
  <tr>
  <td>User name:</td>
  <td><input type="text" name="userName" maxlength="40"></td>
  </tr>
  <tr>
  <td>Description:</td>
  <td><input type="text" name="userDesc" maxlength="64"></td>
  </tr>
  <tr>
  <td>Password:</td>
  <td><input type="password" name="userPassword" maxlength="40" onKeyUp="this.form.cmdUserEdit.disabled = (this.form.userPassword.value != this.form.userPasswordConf.value);">
  </td>
  </tr>
  <tr>
  <td>Retype password:</td>
  <td><input type="password" name="userPasswordConf" maxlength="40" onKeyUp="this.form.cmdUserEdit.disabled = (this.form.userPassword.value != this.form.userPasswordConf.value);">
  </td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="cmdUserEdit" value="Add"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>