<?PHP
/* Copyright 2015, Bergware International.
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
$bunker  = '/usr/local/emhttp/plugins/dynamix.file.integrity/scripts/bunker';
$path    = '/boot/config/plugins/dynamix.file.integrity';
$include = [];

foreach ($_POST as $key => $value) {
  if (substr($key,0,4)=='disk') {
    $include[] = $key;
    unset($_POST[$key]);
  }
}
$m = $_POST['#method'];
$n = $_POST['#notify'];
$e = $_POST['#exclude'] ? "-E \"{$_POST['#exclude']}\"" : "";
$l = strpos($_POST['#log'],'-L')!==false ? "-L" : "";

switch ($_POST['cmd']) {
  case 'Build':
    foreach ($include as $disk) {
      exec("$bunker -aqx $m $l $e -f $path/$disk.export.hash /mnt/$disk >/dev/null &");
    }
  break;
  case 'Export':
    foreach ($include as $disk) {
      exec("$bunker -eqx $m $l $e -f $path/$disk.export.hash /mnt/$disk >/dev/null &");
    }
  break;
  case 'Check':
    foreach ($include as $disk) {
      if (file_exists("$path/$disk.export.hash")) {
        exec("$bunker -Cqx $m $l $n $e -f $path/$disk.export.hash >/dev/null &");
      } else {
        file_put_contents("/var/tmp/$disk.tmp.end","100%#<span class='orange-text'>Check</span> Aborted - export file not found!#");
      }
    }
  break;
  case 'Import':
    foreach ($include as $disk) {
      if (file_exists("$path/$disk.export.hash")) {
        exec("$bunker -iqx $m $l -f $path/$disk.export.hash >/dev/null &");
      } else {
        file_put_contents("/var/tmp/$disk.tmp.end","100%#<span class='orange-text'>Import</span> Aborted - export file not found!#");
      }
    }
  break;
  case 'Remove':
    foreach ($include as $disk) {
      exec("$bunker -Rqx $m $l $e /mnt/$disk >/dev/null &");
    }
  break;
}
$save = false;
?>
