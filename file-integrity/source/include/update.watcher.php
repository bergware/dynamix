<?PHP
/* Copyright 2015-2016, Bergware International.
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
function expand_folders($text) {
  $trim = trim($text);
  return ($trim[0]=='*' ? '' : '*').$trim.'/';
}
function expand_files($text) {
  $trim = trim($text);
  return ($trim[0]=='*' ? '' : '*').$trim.'$';
}

$cfg = @parse_ini_file("/boot/config/docker.cfg");
$img = 'DOCKER_IMAGE_FILE';
$new = isset($default) ? array_replace_recursive($_POST, $default) : $_POST;
$bunker = "/usr/local/emhttp/plugins/dynamix.file.integrity/scripts/bunker";
$path = "/boot/config/plugins/dynamix.file.integrity";
$conf = "/etc/inotifywait.conf";
$cron = "$path/integrity-check.cron";
$run  = "$path/integrity-check.sh";
$chars = ['.','[',']','(',')','+','-','*'];
$escape = ['\.','\[','\]','\(','\)','\+','\-','.*'];
$tasks = [];
$record = [];

if (isset($cfg[$img]) && strpos(dirname($cfg[$img]),'/mnt/disk')!==false) $record[] = basename($cfg[$img])."$";
if ($new['folders']) $record = array_merge($record,array_map('expand_folders',explode(',',$new['folders'])));
if ($new['files']) $record = array_merge($record,array_map('expand_files',explode(',',$new['files'])));
if ($new['exclude']) $record = array_merge($record,explode(',',$new['exclude']));
if ($new['apple']) $record = array_merge($record,['*.AppleDB','*.DS_Store$']);

$more = count($record) > 1;
$exclude = $record ? ($more ? '(':'').str_replace($chars,$escape,implode('|',$record)).($more ? ')':'') : '';
$include = str_replace(['disk',','],['/mnt/disk',' '],$new['disks']);

file_put_contents($conf, "cmd=\"{$new['cmd']}\"\nmethod=\"{$new['method']}\"\nexclude=\"$exclude\"\ndisks=\"$include\"\n");
exec("/usr/local/emhttp/plugins/dynamix.file.integrity/scripts/rc.watcher ".($new['service'] ? 'restart' : 'stop')." &>/dev/null");

foreach ($keys as $key => $value) if ($key[0]!='#' && !array_key_exists($key,$new)) unset($keys[$key]);

foreach ($new as $key => $value) {
  if (preg_match('/^disk[0-9]/',$key)) {
    $do = explode('-',$key);
    $tasks[$do[1]][] = $do[0];
  }
}
$x = count($tasks);
if ($new['schedule']>0 && $x>0) {
  $n = $new['notify'];
  $l = $new['log'];
  $m = $new['method'];
  $p = $new['parity'] ? "&& $(grep -Po '^mdResync=\K\S+' /proc/mdcmd) -eq 0 " : "";
  switch ($new['schedule']) {
  case 1: //daily
    $time = "{$new['min']} {$new['hour']} * * *";
    $term = "\$((10#\$(date +%d)%$x))";
    break;
  case 2: //weekly
    $time = "{$new['min']} {$new['hour']} * * {$new['day']}";
    $term = "\$((10#$(date +%W)%$x))";
    break;
  case 3: //monthly
    $time = "{$new['min']} {$new['hour']} {$new['dotm']} * *";
    $term = "\$((10#\$(date +%m)%$x))";
    break;
  }
  if ($new['priority']) {
    list($nice,$ionice) = explode(',',$new['priority']);
    $bunker = "nice $nice ionice $ionice $bunker";
  }
  $i = 0;
  $text = [];
  $text[] = "#!/bin/bash";
  $text[] = "# This is an auto-generated file, do not change manually!";
  $text[] = "#";
  foreach ($tasks as $task) {
    if (empty($task)) continue;
    foreach ($task as $disk) {
      $log = strpos($l, '-f')!==false ? "$l $path/$disk.export.\$(date +%Y%m%d).bad.hash" : $l;
      $text[] = "[[ $term -eq $i $p]] && $bunker -Vq $m $n $log /mnt/$disk >/dev/null &";
    }
    $i++;
  }
  $text[] = "exit 0";
  file_put_contents($run, implode("\n",$text));
  file_put_contents($cron, "# Generated file integrity check schedule:\n$time $run &> /dev/null\n\n");
} else {
  @unlink($run);
  @unlink($cron);
}
exec("/usr/local/sbin/update_cron");
?>
