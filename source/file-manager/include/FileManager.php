<?PHP
/* Copyright 2005-2021, Lime Technology
 * Copyright 2012-2021, Bergware International.
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
$action  = $_POST['action'];
$source  = explode("\n",htmlspecialchars_decode(rawurldecode($_POST['source'])));
$target  = rawurldecode($_POST['target']);
$H       = empty($_POST['hdlink']) ? '' : 'H';
$protect = empty($_POST['protect']) ? '--ignore-existing' : '';
$status  = '/var/tmp/file.manager.status';
$moving  = '/var/tmp/file.manager.moving';
$procid  = '/var/tmp/file.manager.pid';
$idle    = !file_exists($moving);

function pgrep($pid) {
  global $procid;
  $pid = is_array($pid) ? $pid[0] : $pid;
  $pid = $pid && file_exists("/proc/$pid") ? $pid : '';
  $pid ? file_put_contents($procid,$pid) : @unlink($procid);
  return $pid;
}
function truepath($name) {
  $bits = array_filter(explode('/',$name),'mb_strlen');
  $path = [];
  foreach ($bits as $bit) {
    if ($bit == '.') continue;
    if ($bit == '..') array_pop($path); else $path[] = $bit;
  }
  return '/'.implode('/',$path);
}
function validname($name, $real=true) {
  $path = $real ? realpath(dirname($name)) : truepath(dirname($name));
  $root = explode('/',$path)[1] ?? '';
  return in_array($root,['mnt','boot']) ? $path.'/'.basename($name).(mb_substr($name,-1)=='/'?'/':'') : '';
}
function escape($name) {return escapeshellarg(validname($name));}
function quoted($name) {return is_array($name) ? implode(' ',array_map('escape',$name)) : escape($name);}

$reply['status'] = 'starting';
$reply['pid'] = file_exists($procid) ? file_get_contents($procid) : null;
switch ($action) {
case 0: // create folder
  if ($reply['pid']) {
    $reply['status'] = 'creating';
  } else {
    exec("mkdir -p ".quoted($source[0].'/'.$target)." >/dev/null 2>&1 & echo $!",$reply['pid']);
  }
  break;
case 1: // delete folder
case 5: // delete file
  if ($reply['pid']) {
    $reply['status'] = 'removing';
  } else {
    exec("rm -Rf ".quoted($source)." >/dev/null 2>&1 & echo $!",$reply['pid']);
  }
  break;
case 2: // rename folder
case 6: // rename file
  if ($reply['pid']) {
    $reply['status'] = 'renaming';
  } else {
    $path = dirname($source[0]);
    exec("mv -f ".quoted($source)." ".quoted("$path/$target")." >/dev/null 2>&1 & echo $!",$reply['pid']);
  }
  break;
case 3:  // copy folder
case 7:  // copy file
  if ($reply['pid']) {
    $reply['status'] = preg_replace('/\s\s+/',' ',rtrim(exec("tail -1 $status|grep -Pom1 '^.+ \K[0-9]+%[^(]+'"))) ?: 'copying';
  } else {
    $target = validname($target,false);
    if ($target) {
      exec("rsync -ahPIX$H $protect --mkpath --info=name0,progress2 ".quoted($source)." ".escapeshellarg($target)." >$status 2>/dev/null & echo $!",$reply['pid']);
    } else {
      $reply['error'] = 'Invalid target';
    }
  }
  break;
case 4: // move folder
case 8: // move file
  if ($reply['pid']) {
    $reply['status'] = $idle ? 'moving' : (preg_replace('/\s\s+/',' ',rtrim(exec("tail -1 $status|grep -Pom1 '^.+ \K[0-9]+%[^(]+'"))) ?: 'moving');
  } else {
    $target = validname($target,false);
    if ($target) {
      touch($moving);
      $idle = false;
      exec("rsync -ahPIX$H $protect --mkpath --info=name0,progress2 --remove-source-files ".quoted($source)." ".escapeshellarg($target)." >$status 2>/dev/null & echo $!",$reply['pid']);
    } else {
      $reply['error'] = 'Invalid target';
    }
  }
  break;
case 9: // change owner
  if ($reply['pid']) {
    $reply['status'] = 'updating';
  } else {
    exec("chown -Rf $target ".quoted($source)." >/dev/null 2>&1 & echo $!",$reply['pid']);
  }
  break;
case 10: // change permission
  if ($reply['pid']) {
    $reply['status'] = 'updating';
  } else {
    exec("chmod -Rf $target ".quoted($source)." >/dev/null 2>&1 & echo $!",$reply['pid']);
  }
  break;
case 99: // kill background processes
  exec("kill -9 ".$reply['pid']);
  break;
}
$reply['pid'] = pgrep($reply['pid']);
if (empty($reply['pid'])) {
  if ($idle) {
    if (file_exists($status)) unlink($status);
    $reply['status'] = 'done';
  } else {
    unlink($moving);
    exec("find ".quoted($source)." -type d -empty -delete >/dev/null 2>&1 & echo $!",$reply['pid']);
    $reply['pid'] = pgrep($reply['pid']);
  }
}
header('Content-Type: application/json');
die(json_encode($reply));
?>
