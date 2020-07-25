<?PHP
/* Copyright 2012-2020, Bergware International.
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
$plugin = 'dynamix.file.integrity';
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$translations = file_exists("$docroot/webGui/include/Translations.php");

if ($translations) {
  // add translations
  $_SERVER['REQUEST_URI'] = 'integrity';
  require_once "$docroot/webGui/include/Translations.php";
} else {
  // legacy support (without javascript)
  $noscript = true;
  require_once "$docroot/plugins/$plugin/include/Legacy.php";
}

function regex($text) {
  return strtr($text,['.'=>'\.','['=>'\[',']'=>'\]','('=>'\(',')'=>'\)','{'=>'\{','}'=>'\}','/'=>'\/','+'=>'\+','-'=>'\-','*'=>'.*','&'=>'\&','?'=>'\?']);
}

$bunker = "/usr/local/emhttp/plugins/$plugin/scripts/bunker";
$path   = "/boot/config/plugins/$plugin/export";
$apple  = [regex('.AppleDB'),regex('.DS_Store')];
$disks  = [];

foreach ($_POST as $key => $value) {
  if (substr($key,0,4)=='disk') {
    $disks[] = $key;
    unset($_POST[$key]);
  }
}
$m = $_POST['#method'];
$n = $_POST['#notify'];
$e = $_POST['#exclude'] ? "-E \"".regex($_POST['#exclude'])."\"" : "";
$f = $_POST['#folders'] ? "-F \"".regex($_POST['#folders']).($_POST['#apple'] ? ",{$apple[0]}" : "")."\"" : "";
$l = strpos($_POST['#log'],'-L')!==false ? "-L" : "";

if ($_POST['#priority']) {
  list($nice,$ionice) = explode(',',$_POST['#priority']);
  $bunker = "nice $nice ionice $ionice $bunker";
}

switch ($_POST['cmd']) {
  case 'Build':
    $key = $_POST['#files'] ? array_map('trim', explode(',', $_POST['#files'])) : [];
    if ($_POST['#apple']) $key[] = $apple[1];
    $key = $key ? '! "'.implode(',', $key).'"' : '';
    foreach ($disks as $disk) {
      exec("$bunker -aqx $m $l $e $f -f $path/$disk.export.hash /mnt/$disk $key >/dev/null &");
    }
  break;
  case 'Export':
    foreach ($disks as $disk) {
      exec("$bunker -eqx $m $l $e $f -f $path/$disk.export.hash /mnt/$disk >/dev/null &");
    }
  break;
  case 'Check':
    foreach ($disks as $disk) {
      if (file_exists("$path/$disk.export.hash")) {
        exec("$bunker -Cqx $m $l $n -f $path/$disk.export.hash >/dev/null &");
      } else {
        file_put_contents("/var/tmp/$disk.tmp.end","100%#<span class='orange-text orange-button'>"._('Check')."</span> "._('Aborted - export file not found')."!#");
      }
    }
  break;
  case 'Import':
    foreach ($disks as $disk) {
      if (file_exists("$path/$disk.export.hash")) {
        exec("$bunker -iqx $m $l -f $path/$disk.export.hash >/dev/null &");
      } else {
        file_put_contents("/var/tmp/$disk.tmp.end","100%#<span class='orange-text orange-button'>"._('Import')."</span> "._('Aborted - export file not found')."!#");
      }
    }
  break;
  case 'Remove':
    foreach ($disks as $disk) {
      exec("$bunker -Rqx $m $l $e $f /mnt/$disk >/dev/null &");
    }
  break;
  case 'Clear':
    $key = $_POST['#files'] ? array_map('trim', explode(',', $_POST['#files'])) : [];
    if ($_POST['#apple']) $key[] = $apple[1];
    $key = $key ? '"'.implode(',', $key).'"' : '';
    foreach ($disks as $disk) {
      exec("$bunker -Rqxz $m $l $e $f /mnt/$disk $key >/dev/null &");
    }
  break;
}
$save = false;
?>
