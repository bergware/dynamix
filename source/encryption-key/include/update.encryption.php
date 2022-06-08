<?PHP
/* Copyright 2005-2022, Lime Technology
 * Copyright 2012-2022, Bergware International.
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
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
// add translations
$_SERVER['REQUEST_URI'] = 'settings';
require_once "$docroot/webGui/include/Translations.php";

$save   = false;
$disks  = parse_ini_file('state/disks.ini',true);
$newkey = parse_ini_file('state/var.ini')['luksKeyfile'];
$oldkey = str_replace('keyfile','oldfile',$newkey);
$delkey = !is_file($newkey);
$crypto = [];
foreach (glob('/dev/disk/by-id/*CRYPT-LUKS*',GLOB_NOSORT) as $disk) $crypto[] = array_pop(explode('-',$disk));
if (count($crypto)==0) die();

function delete_file(...$file) {
  array_map('unlink',array_filter($file,'is_file'));
}
function removeKey($key,$disk) {
  $match = $slots = 0;
  $dump = popen("cryptsetup luksDump /dev/$disk",'r');
  while (($row = fgets($dump))!==false) {
    if (strncmp($row,'Version:',8)==0) {
      switch (trim(explode(':',$row)[1])) {
        case 1: $match = '/^Key Slot \d+: ENABLED$/'; break;
        case 2: $match = '/^\s+\d+: luks2$/'; break;
      }
    }
    if ($match && preg_match($match,$row)) $slots++;
  }
  pclose($dump);
  if ($slots > 1) exec("cryptsetup luksRemoveKey /dev/$disk $key 1>/dev/null 2>&1");
}
function diskname($name) {
  global $disks;
  foreach ($disks as $disk) if (strncmp($name,$disk['device'],strlen(disk['device']))==0) return $disk['name'];
  return $name;
}
function reply($text,$type) {
  global $oldkey,$newkey,$delkey;
  $reply = $_POST['#reply'];
  if (realpath(dirname($reply))=='/var/tmp') file_put_contents($reply,$text."\0".$type);
  delete_file($oldkey);
  if ($_POST['newinput']=='text' || $delkey) delete_file($newkey);
  die();
}

if (isset($_POST['oldinput'])) {
  switch ($_POST['oldinput']) {
  case 'text':
    file_put_contents($oldkey,base64_decode($_POST['oldluks']));
    break;
  case 'file':
    file_put_contents($oldkey,base64_decode(explode(';base64,',$_POST['olddata'])[1]));
    break;
  }
} else {
  if (is_file($newkey)) copy($newkey,$oldkey);
}

if (is_file($oldkey)) {
  $disk = $crypto[0]; // check first disk only (key is the same for all disks)
  exec("cryptsetup luksOpen --test-passphrase --key-file $oldkey /dev/$disk 1>/dev/null 2>&1",$none,$error);
} else $error = 1;

if ($error > 0) reply(_('Incorrect existing key'),'warning');

if (isset($_POST['newinput'])) {
  switch ($_POST['newinput']) {
  case 'text':
    file_put_contents($newkey,base64_decode($_POST['newluks']));
    break;
  case 'file':
    file_put_contents($newkey,base64_decode(explode(';base64,',$_POST['newdata'])[1]));
    break;
  }
  $good = $bad = [];
  foreach ($crypto as $disk) {
    exec("cryptsetup luksAddKey --key-file $oldkey /dev/$disk $newkey 1>/dev/null 2>&1",$none,$error);
    if ($error==0) $good[] = $disk; else $bad[] = diskname($disk);
  }
  if (count($bad)==0) {
    // all okay, remove the old key
    foreach ($good as $disk) removeKey($oldkey,$disk);
    reply(_('Key successfully changed'),'success');
  } else {
    // something went wrong, restore key
    foreach ($good as $disk) removeKey($newkey,$disk);
    reply(_('Changing key failed for disks').': '.implode(' ',$bad),'error');
  }
}
reply(_('Missing new key'),'warning');
?>
