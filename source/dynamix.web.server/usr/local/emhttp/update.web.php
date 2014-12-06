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
    $setup = '';
    break;
  case 'service':
    $enable = $value;
    break;
  case 'path':
    $setup .= "server.document-root = \"$value\"\n";
    break;
  case 'port':
    $setup .= "server.port = \"$value\"\n";
    break;
  case 'phpError':
    exec("sed -i \"s:^log_errors = .*:log_errors = $value:\" /boot/config/dynamix/php.ini");
    break;
  case 'error':
    if ($value) $setup .= "server.errorlog = \"/var/log/lighttpd/error.log\"\n";
    break;
  case 'access':
    if ($value) $setup .= "accesslog.filename = \"/var/log/lighttpd/portal.log\"\n";
    break;
  }
}
exec("/etc/rc.d/rc.lighttpd stop >/dev/null");
file_put_contents($config, $setup);
if ($enable) exec("/etc/rc.d/rc.lighttpd start >/dev/null");
?>