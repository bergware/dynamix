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
  switch ($key) {
  case '#section':
    $script = ($value == 'email');
    break;
  case '#config':
    $file = $value;
    $data = file_get_contents($file);
    break;
  case 'service':
    $enable = $value;
    break;
  default:
    if (substr($key,0,1)!='#') $data = preg_replace("/$key=.*\n/", "$key=$value\n", $data, 1);
  }
}
file_put_contents($file, $data);
if ($script) {
  exec("/etc/rc.d/rc.emailnotify stop >/dev/null");
  if ($enable) exec("/etc/rc.d/rc.emailnotify start >/dev/null");
}
?>