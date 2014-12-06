<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$plugin = 'dynamix.dns.server';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");
$sName = "dnsmasq";
$uName = ucfirst($sName);
$fName = "/usr/sbin/$sName";
$sConfig = "/etc/dnsmasq.conf";
$sHosts = "/etc/hosts.dnsmasq";
?>
<script>
$(function() {
  $.ajax({url:'/plugins/webGui/include/ProcessStatus.php',data:'name=<?=$sName?>',success:function(status) {$('.tabs').append(status);}});
  presetDNS(document.dns_settings);
});
function presetDNS(form) {
  var disabled = form.service.value==0;
  form.domain.disabled = disabled;
  form.local.disabled = disabled;
  form.server.disabled = disabled;
  form.host.disabled = disabled;
}
function resetDNS(form) {
  var service = form.service.selectedIndex;
  form.innerHTML = form.innerHTML.replace(/user-set/g,'default');
  form.service.selectedIndex = service;
  form.domain.value = "";
  form.local.selectedIndex = 1;
  form.server.value = "";
  form.host.value = "";
}
function checkDomain(service, domain) {
  if (service==1 && !domain) {
    alert("Please enter a domain name");
    return false;
  }
  return true;
}
</script>
<form name="dns_settings" method="POST" action="/update.php" target="progressFrame" onsubmit="return checkDomain(this.service.value, this.domain.value)">
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#include" value="update.dns.php">
<input type="hidden" name="#config"  value="<?=$sConfig?>">
<input type="hidden" name="#hosts"   value="<?=$sHosts?>">
<table class="settings">
  <tr>
  <td>DNS server function:</td>
  <td><select name="service" size="1" onchange="presetDNS(this.form);">
<?=mk_option($cfg['service'], "0", "Disabled")?>
<?=mk_option($cfg['service'], "1", "Enabled")?>
  </select></td>
  </tr>
  <tr>
  <td>Domain name:</td>
  <td><input type="text" name="domain" maxlength="200" value="<?=$cfg['domain']?>">(e.g. mydomain.com)</td>
  </tr>
  <tr>
  <td>Set domain as local-only:</td>
  <td><select name="local" size="1">
<?=mk_option($cfg['local'], "0", "No")?>
<?=mk_option($cfg['local'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>External DNS servers:</td>
  <td><b><u>IP Address</u></b><br><textarea name="server" rows="2" columns="120" wrap="off"><?=urldecode($cfg['server'])?></textarea></td>
  </tr>
  <tr>
  <td>Local hosts:</td>
  <td><b><u><span style="margin-right:50px">IP Address</span>Host Name</u></b><br><textarea name="host" rows="12" columns="120" wrap="off"><?=urldecode($cfg['host'])?></textarea></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetDNS(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
  <tr><td style="font-weight:normal;font-style:italic;font-size:smaller"><?=exec("$fName -v|awk '/^$uName/ {print \"$sName version:\",$3;exit}'")?></td><td></td></tr>
</table>
</form>