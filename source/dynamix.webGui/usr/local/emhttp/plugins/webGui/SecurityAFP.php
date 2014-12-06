<?PHP
/* Copyright 2010, Lime Technology LLC.
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2, or (at your option)
* any later version.
*/
/* Adapted by Bergware International (December 2013) */
?>
<script type="text/javascript">
function checkShareSettingsAFP(form) {
  if (form.shareExportAFP.value=="et") {
    form.shareVolsizelimitAFP.disabled=false;
  } else {
    form.shareVolsizelimitAFP.disabled=true;
  }
}
$(function() {
  checkShareSettingsAFP(document.share_settings_afp);
});
</script>

<form name="share_settings_afp" method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="settings">
  <tr>
  <td>Export:</td>
  <td><select name="shareExportAFP" size="1" onchange="checkShareSettingsAFP(this.form);">
<?=mk_option($sec_afp[$name]['export'], "-", "No");?>
<?=mk_option($sec_afp[$name]['export'], "e", "Yes");?>
<?=mk_option($sec_afp[$name]['export'], "et", "Yes (TimeMachine)");?>
  </select></td>
  </tr>
  <tr>
  <td>Security:</td>
  <td><select name="shareSecurityAFP" size="1">
<?=mk_option($sec_afp[$name]['security'], "public", "Public");?>
<?=mk_option($sec_afp[$name]['security'], "secure", "Secure");?>
<?=mk_option($sec_afp[$name]['security'], "private", "Private");?>
  </select></td>
  </tr>
  <tr>
  <td>TimeMachine volume size limit:</td>
  <td><input type="text" name="shareVolsizelimitAFP" maxlen="20" value="<?=$sec_afp[$name]['volsizelimit'];?>"> MB</td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShareSecurityAFP" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>

<?if ($sec_afp[$name]['security']=="secure"):?>
<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="access_list">
  <tr>
  <td>User</td>
  <td>Access</td>
  </tr>
<?$write_list = explode(",", $sec_afp[$name]['writeList']);
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
  <td><input type="submit" name="changeShareAccessAFP" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?endif;?>

<?if ($sec_afp[$name]['security']=="private"):?>
<form method="POST" action="/update.htm" target="progressFrame">
<input type="hidden" name="shareName" value="<?=$name;?>">
<table class="access_list">
  <tr>
  <td>User</td>
  <td>Access</td>
  </tr>
<?$read_list = explode(",", $sec_afp[$name]['readList']);
  $write_list = explode(",", $sec_afp[$name]['writeList']);
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
  <td><input type="submit" name="changeShareAccessAFP" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?endif;?>