<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<form name="SMBEnable" method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>Enable SMB:</td>
  <td><select name="shareSMBEnabled" size="1">
<?=mk_option($var['shareSMBEnabled'], "no", "No");?>
<?=mk_option($var['shareSMBEnabled'], "yes", "Yes (Workgroup)");?>
<?if ($var['featureSecurityAds']):?>
<?=mk_option($var['shareSMBEnabled'], "ads", "Yes (Active Directory)");?>
<?else:?>
<?=mk_option($var['shareSMBEnabled'], "ads", "Yes (Active Directory)", "disabled");?>
<?endif;?>
  </select></td>
  </tr>
<?if ($var['fsState']=="Started"):?>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply" disabled><button type="button" onClick="done();">Done</button></td>
  </tr>
  <tr>
  <td></td>
  <td>Array must be <strong>Stopped</strong> to change.</td>
  </tr>
<?else:?>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
<?endif;?>
<?if ($var['shareSMBEnabled']=="yes"):?>
  <tr>
  <td>Connected users:</td>
  <td><?exec("smbstatus -p | awk 'NR>4 {print $2}' | uniq", $list); echo implode(',',$list)?></td>
  </tr>
  <tr>
  <td>Workgroup:</td>
  <td><input type="text" name="WORKGROUP" maxlength="40" value="<?=$var['WORKGROUP'];?>"></td>
  </tr>
  <tr>
  <td>Local master:</td>
  <td><select name="localMaster">
<?=mk_option($var['localMaster'], "no", "No");?>
<?=mk_option($var['localMaster'], "yes", "Yes");?>
  </select></td>
  </tr>
<?endif;?>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>

<?if ($var['shareSMBEnabled']=="ads"):?>
<form name="JoinOps" method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>AD join status:</td>
  <td><?=$var['joinStatus'];?></td>
  </tr>
  <tr>
  <td>AD domain name (FQDN):</td>
  <td><input type="text" name="DOMAIN" maxlength="80" value="<?=$var['DOMAIN'];?>"></td>
  </tr>
  <tr>
  <td>AD short domain name:</td>
  <td><input type="text" name="DOMAIN_SHORT" maxlength="40" value="<?=$var['DOMAIN_SHORT'];?>"></td>
  </tr>
  <td>AD account login:</td>
  <td><input type="text" name="DOMAIN_LOGIN" maxlength="40" value="<?=$var['DOMAIN_LOGIN'];?>"></td>
  </tr>
  <tr>
  <td>AD account password:</td>
  <td><input type="password" name="DOMAIN_PASSWD" maxlength="40" value="<?=$var['DOMAIN_PASSWD'];?>"></td>
  </tr>
<?if ($var['joinStatus']=="Joined"):?>
  <tr>
  <td></td>
  <td><input type="submit" name="cmdJoinDomain" value="Join" disabled><input type="submit" name="cmdLeaveDomain" value="Leave"></td>
  </tr>
<?else:?>
  <tr>
  <td></td>
  <td><input type="submit" name="cmdJoinDomain" value="Join"><input type="submit" name="cmdLeaveDomain" value="Leave" disabled></td>
  </tr>
<?endif;?>
</table>
</form>

<form name="shareOwnership" method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>AD initial owner:</td>
  <td><input type="text" name="shareInitialOwner" maxlength="40" value="<?=$var['shareInitialOwner'];?>"></td>
  </tr>
  <tr>
  <tr>
  <td>AD initial group:</td>
  <td><input type="text" name="shareInitialGroup" maxlength="40" value="<?=$var['shareInitialGroup'];?>"></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>
<?endif;?>