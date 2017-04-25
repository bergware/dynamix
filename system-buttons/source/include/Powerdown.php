<?PHP
/* Copyright 2012-2017, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
if (file_exists("/usr/local/sbin/powerdown")) {
  switch ($_POST['cmd']) {
    case 'shutdown': $option = ''; break;
    case 'reboot'  : $option = '-r'; break;
    default        : $option = ''; break;
  }
  exec("/usr/local/sbin/powerdown $option 1>/dev/null 2>&1 &");
  $timer = 15;
  while ($timer>10) {
    sleep(1);
    $timer--;
  }
  while (file_exists("/var/run/powerdown.pid") && $timer) {
    sleep(1);
    $timer--;
  }
}
echo $_POST['cmd'];
?>
