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
function checkNetworkSettings(form) {
  if (form.BONDING.value=="yes")
    form.BONDING_MODE.disabled=false;
  else
    form.BONDING_MODE.disabled=true;
  if (form.USE_DHCP.value=="yes") {
    form.IPADDR.disabled=true;
    form.NETMASK.disabled=true;
    form.GATEWAY.disabled=true;
    form.DHCP_KEEPRESOLV.disabled=false;
    if (form.DHCP_KEEPRESOLV.value == "yes") {
      form.DNS_SERVER1.disabled=false;
      form.DNS_SERVER2.disabled=false;
      form.DNS_SERVER3.disabled=false;
    } else {
      form.DNS_SERVER1.disabled=true;
      form.DNS_SERVER2.disabled=true;
      form.DNS_SERVER3.disabled=true;
    }
  } else {
    form.IPADDR.disabled=false;
    form.NETMASK.disabled=false;
    form.GATEWAY.disabled=false;
    form.DHCP_KEEPRESOLV.value = "yes";
    form.DHCP_KEEPRESOLV.disabled=true;
    form.DNS_SERVER1.disabled=false;
    form.DNS_SERVER2.disabled=false;
    form.DNS_SERVER3.disabled=false;
  }
}
function checkBondingSettings(form) {
  var mode=form.BONDING_MODE.value;
  if (mode==1 || mode>4 || form.BONDING.value=="no") {$('#attention').hide();} else {$('#attention').show();}
}
$(function() {
  checkNetworkSettings(document.network_settings);
  checkBondingSettings(document.network_settings);
});
</script>

<form name="network_settings" method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>MAC address:</td>
  <td><?=$var['HWADDR'];?></td>
  </tr>
  <tr>
  <td>Enable bonding: </td>
  <td><select name="BONDING" size="1" onchange="checkNetworkSettings(this.form);">
<?=mk_option($var['BONDING'], "no", "No");?>
<?=mk_option($var['BONDING'], "yes", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Bonding mode:</td>
  <td><select name="BONDING_MODE" size="1" onchange="checkBondingSettings(this.form);">
<?=mk_option($var['BONDING_MODE'], "0", "balance-rr (0)");?>
<?=mk_option($var['BONDING_MODE'], "1", "active-backup (1)");?>
<?=mk_option($var['BONDING_MODE'], "2", "balance-xor (2)");?>
<?=mk_option($var['BONDING_MODE'], "3", "broadcast (3)");?>
<?=mk_option($var['BONDING_MODE'], "4", "802.3ad (4)");?>
<?=mk_option($var['BONDING_MODE'], "5", "balance-tlb (5)");?>
<?=mk_option($var['BONDING_MODE'], "6", "balance-alb (6)");?>
  </select><span id="attention" style="display:none;color:red"><b>Attention:</b> this mode requires a switch with proper setup and support...</span></td>
  </tr>
  <tr>
  <td>Obtain IP address automatically: </td>
  <td><select name="USE_DHCP" size="1" onchange="checkNetworkSettings(this.form);">
<?=mk_option($var['USE_DHCP'], "yes", "Yes");?>
<?=mk_option($var['USE_DHCP'], "no", "No");?>
  </select></td>
  </tr>
  <tr>
  <td>IP address:</td>
  <td><input type="text" name="IPADDR" maxlength="40" value="<?=$var['IPADDR'];?>"></td>
  </tr>
  <tr>
  <td>Netmask:</td>
  <td><input type="text" name="NETMASK" maxlength="40" value="<?=$var['NETMASK'];?>"></td>
  </tr>
  <tr>
  <td>Gateway:</td>
  <td><input type="text" name="GATEWAY" maxlength="40" value="<?=$var['GATEWAY'];?>"></td>
  </tr>
  <tr>
  <td>Obtain DNS server address automatically: </td>
  <td><select name="DHCP_KEEPRESOLV" size="1" onchange="checkNetworkSettings(this.form);">
<?=mk_option($var['DHCP_KEEPRESOLV'], "yes", "No");?>
<?=mk_option($var['DHCP_KEEPRESOLV'], "no", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>DNS server 1:</td>
  <td><input type="text" name="DNS_SERVER1" maxlength="80" value="<?=$var['DNS_SERVER1'];?>"></td>
  </tr>
  <tr>
  <td>DNS server 2:</td>
  <td><input type="text" name="DNS_SERVER2" maxlength="80" value="<?=$var['DNS_SERVER2'];?>"></td>
  </tr>
  <tr>
  <td>DNS server 3:</td>
  <td><input type="text" name="DNS_SERVER3" maxlength="80" value="<?=$var['DNS_SERVER3'];?>"></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeNetwork" value="Apply"><button type="button" onClick="done();">Done</button></td>
  </tr>
</table>
</form>