<?PHP
/* Copyright 2010, Lime Technology LLC.
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2, or (at your option)
* any later version.
*/
/* Adapted by Bergware International (December 2013) */
?>
<?if ($var['shareSMBEnabled']=="no"):?>
<p class="notice">SMB is not enabled</p>
<?return;?>
<?endif;?>

<?if ($var['shareSMBEnabled']=="ads"):?>
<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="settings">
  <tr>
  <td>Export:</td>
  <td><select name="shareExport" size="1">
<?=mk_option($sec[$name]['export'], "-", "No");?>
<?=mk_option($sec[$name]['export'], "e", "Yes");?>
<?=mk_option($sec[$name]['export'], "eh", "Yes (hidden)");?>
  </select></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareSecurity" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?return;?>
<?endif;?>

<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="settings">
  <tr>
  <td>Export:</td>
  <td><select name="shareExport" size="1">
<?=mk_option($sec[$name]['export'], "-", "No");?>
<?=mk_option($sec[$name]['export'], "e", "Yes");?>
<?=mk_option($sec[$name]['export'], "eh", "Yes (hidden)");?>
  </select></td>
  </tr>
  <tr>
  <td>Security:</td>
  <td><select name="shareSecurity" size="1">
<?=mk_option($sec[$name]['security'], "public", "Public");?>
<?=mk_option($sec[$name]['security'], "secure", "Secure");?>
<?if ($var['featureSecurityUser']):?>
<?=mk_option($sec[$name]['security'], "private", "Private");?>
<?else:?>
<?=mk_option($sec[$name]['security'], "private", "Private", "disabled");?>
<?endif;?>
  </select></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareSecurity" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>

<?if ($sec[$name]['security']=="secure"):?>
<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="access_list">
  <tr>
  <td>User</td>
  <td>Access</td>
  </tr>
<?$write_list = explode(",", $sec[$name]['writeList']);
  foreach ($users as $user):
    if ($user['name'] == "root"):
?>  <input type="hidden" name="userAccess.<?=$user['idx'];?>" value="no-access">
<?  continue;
    endif;
    if (in_array( $user['name'], $write_list)):
      $userAccess = "read-write";
    else:
      $userAccess = "read-only";
    endif;
?><tr>
  <td><?=$user['name'];?></td>
  <td><select name="userAccess.<?=$user['idx'];?>" size="1">
<?=mk_option($userAccess, "read-write", "Read/Write");?>
<?=mk_option($userAccess, "read-only", "Read-only");?>
  </select></td>
  </tr>
<?endforeach;?>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareAccess" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?endif;?>

<?if ($sec[$name]['security']=="private"):?>
<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="access_list">
  <tr>
  <td>User</td>
  <td>Access</td>
  </tr>
<?$read_list = explode(",", $sec[$name]['readList']);
  $write_list = explode(",", $sec[$name]['writeList']);
  foreach ($users as $user):
    if ($user['name'] == "root"):
?>  <input type="hidden" name="userAccess.<?=$user['idx'];?>" value="no-access">
<?  continue;
    endif;
    if (in_array( $user['name'], $write_list)):
      $userAccess = "read-write";
    elseif (in_array( $user['name'], $read_list)):
      $userAccess = "read-only";
    else:
      $userAccess = "no-access";
    endif;
?><tr>
  <td><?=$user['name'];?></td>
  <td><select name="userAccess.<?=$user['idx'];?>" size="1">
<?=mk_option($userAccess, "read-write", "Read/Write");?>
<?=mk_option($userAccess, "read-only", "Read-only");?>
<?=mk_option($userAccess, "no-access", "No Access");?>
  </select></td>
  </tr>
<?endforeach;?>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareAccess" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?endif;?>