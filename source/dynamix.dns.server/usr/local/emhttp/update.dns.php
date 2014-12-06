<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
foreach ($_GET as $key => $value) {
  if (!strlen($value)) continue;
  switch ($key) {
  case '#config':
    $config = $value;
    $data1 = "domain-needed\nbogus-priv\nexpand-hosts\nno-hosts\n";
    $data2 = "";
    break;
  case '#hosts':
    $hosts = $value;
    $data1 .= "addn-hosts=$hosts\n";
    break;
  case 'service':
    $enable = $value;
    break;
  case 'domain':
    $domain = $value;
    $data1 .= "domain=$domain\n";
    break;
  case 'local':
    if (isset($domain)) $data1 .= "local=/$domain/\n";
    break;
  case 'server':
    $servers = explode("\n",str_replace("\r","",$value));
    foreach ($servers as $server) $data1 .= "server=$server\n";
    $_GET[$key] = urlencode($value);
    break;
  case 'host':
    $data2 .= str_replace("\r","",$value);
    $_GET[$key] = urlencode($value);
    break;
  }
}
exec("/etc/rc.d/rc.dnsmasq stop >/dev/null");
file_put_contents($config, $data1);
file_put_contents($hosts, $data2);
if ($enable) exec("/etc/rc.d/rc.dnsmasq start >/dev/null");
?>