<?PHP
/* Copyright 2012-2023, Bergware International.
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
if (isset($_POST['cmd'])) {
  switch ($_POST['cmd']) {
    case 'shutdown': $cmd = 'poweroff'; break;
    case 'reboot'  : $cmd = 'reboot'; break;
    default        : $cmd = ''; break;
  }
  if ($cmd) {
    exec("/sbin/$cmd 1>/dev/null 2>&1 &");
    echo $_POST['cmd'];
  }
}
?>
