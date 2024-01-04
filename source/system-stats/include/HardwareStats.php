<?PHP
/* Copyright 2012-2023, Bergware International.
 * Copyright 2012, Andrew Hamer-Adams, http://www.pixeleyes.co.nz.
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
$plugin = 'dynamix.system.stats';
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// add translations
$_SERVER['REQUEST_URI'] = 'stats';
require_once "$docroot/webGui/include/Translations.php";
require_once "$docroot/webGui/include/Helpers.php";

function bar_color($val) {
  global $display;
  $critical = $display['critical']??0;
  $warning = $display['warning']??0;
  if ($val>=$critical && $critical>0) return "redbar";
  if ($val>=$warning && $warning>0) return "orangebar";
  return "greenbar";
}

switch ($_POST['cmd']??'') {
case 'sum':
  $plugin = $_POST['plugin']??'';
  $normal = ($_POST['startMode']??'')=='Normal';
  $disks = (array)parse_ini_file("state/disks.ini",true);
  extract(parse_plugin_cfg('dynamix',true));
  $arraysize = $arrayfree = 0;
  foreach ($disks as $disk) {
    if ($disk['type']!='Data') continue;
    $arraysize += $disk['size']*1024;
    $arrayfree += $disk['fsFree']*1024;
  }
  $arrayused = $arraysize-$arrayfree;
  $freepercent = $normal ? round(100*$arrayfree/$arraysize) : 100;
  $arraypercent = 100-$freepercent;
  $data = [];
  $data[] = "mybar ".bar_color($arraypercent)." align-left";
  $data[] = "$arraypercent%";
  $data[] = "mybar ".bar_color($arraypercent)." inside";
  $data[] = "<strong>".my_scale($arrayused,$unit,null,-1)." $unit <img src='/plugins/$plugin/images/arrow.png' style='margin-top:-3px'> $arraypercent%</strong><br><small>"._('Total Space Used')."</small>";
  $data[] = "<strong>".my_scale($arrayfree,$unit,null,-1)." $unit <img src='/plugins/$plugin/images/arrow.png' style='margin-top:-3px'> $freepercent%</strong><br><small>"._('Available for Data')."</small>";
  echo implode(';',$data);
  exit;
case 'sys':
  $normal = ($_POST['startMode']??'')=='Normal';
  $pools = explode(',',$_POST['pools']??'');
  $series = $normal ? ['Critical','Warning','Normal'] : ['Critical','Warning','Normal','Maintenance'];
  $disks = (array)parse_ini_file("state/disks.ini", true); $var = [];
  require_once 'webGui/include/CustomMerge.php';
  extract(parse_plugin_cfg("dynamix",true));
  $output = [];
  $json = [];
  foreach ($disks as $disk) {
    $size = 0;
    if (($disk['fsStatus']??'')!='Mounted' && $disk['type']!='Parity') continue;
    switch ($disk['type']) {
    case 'Data':
    case 'Flash':
      $size = $disk['size'];
    break;
    case 'Cache':
      if (in_array($disk['name'],$pools)) $size = isset($disk['fsSize']) ? $disk['fsSize'] : $disk['size'];
    break;}
    if ($size>0) {
      if ($normal) {
        $free = $disk['fsFree'];
        $percent = 100-round(100*$free/$size);
        $critical = !empty($disk['critical']) ? $disk['critical'] : $display['critical'] ?? 0;
        $warning = !empty($disk['warning']) ? $disk['warning'] : $display['warning'] ?? 0;
        $point[0] = $critical>0 ? $percent-$critical : 0;
        $point[1] = $warning>0 ? $percent-$warning : 0;
        if ($point[0]>0) {$point[1] = $warning>0 ? $critical-$warning : 0;} else {$point[0] = 0;}
        if ($point[1]>0) {$point[2] = $warning;} else {$point[2] = $warning>0 ? $percent : $percent-$point[0]; $point[1] = 0;}
        if ($warning>=$critical && $critical>0 && $point[2]>$warning) $point[2] = $critical;
      } else {
        $point[0] = 0;
        $point[1] = 0;
        $point[2] = 0;
        $point[3] = 100;
      }
      $i = 0;
      foreach ($series as $label) $output[$label][] = $point[$i++];
    }
  }
  foreach ($series as $label) $json[] = '"'.$label.'":['.implode(',', $output[$label]).']';
  echo '{'.implode(',', $json).'}';
  exit;
case 'rts':
  $nl  = '"\n"';
  $cpu = '$2=="all"';
  $hdd = '$2=="tps"';
  $ram = '$2=="kbmemfree"';
  $com = '$2=="'.($_POST['port']??'').'"';
  exec("sar 1 1 -u -b -r -n DEV|grep -a '^A'|tr -d '\\0'|awk '$cpu {u=$3;n=$4;s=$5;}; $hdd {getline;r=$6;w=$7;}; $ram {getline;f=$2;c=$6+$7;d=$4;}; $com {x=$5;y=$6;} END{print u,n,s{$nl}r,w{$nl}f,c,d{$nl}x,y}'",$data);
  echo implode(' ', $data);
  exit;
case 'cpu':
  $series = ['User','Nice','System'];
  $data = '$5,$6,$7';
  $case = '';
  $mask = ' && $5<=100 && $6<=100 && $7<=100';
  break;
case 'ram':
  $series = ['Free','Cached','Used'];
  $data = '$4,$8+$9,$6';
  $case = '-- -r';
  $mask = ' && $4<100000000000 && $6<100000000000 && $8<100000000000 && $9<100000000000';
  break;
case 'com':
  $series = ['Receive','Transmit'];
  $data = '$7,$8';
  $case = '-- -n DEV';
  $mask = ' && $4=="'.($_POST['port']??'').'" && $7<100000000000 && $8<100000000000';
  break;
case 'hdd':
  $series = ['Read','Write'];
  $data = '$8,$9';
  $case = '-- -b';
  $mask = ' && $8<100000000000 && $9<100000000000';
  break;
}
$input = [];
$output = [];
$json = [];
$select = [1=>60, 2=>120, 3=>300, 7=>600, 14=>1200, 21=>1800, 31=>3600, 3653=>7200];
$logs = glob('/var/sa/sa*',GLOB_NOSORT);
$days = count($logs);
$graph = $_POST['graph']??0;
if ($graph>0) {
  $interval = $select[$graph];
  if ($days<=28) {
    foreach ($select as $index => $period) {
      if ($index>$days) break;
      $interval = $period;
      if ($index==$graph) break;
    }
  }
  $valid = '$2~/^[0-9]/ && $3>='.((floor(time()/86400)-$graph)*86400).$mask;
  usort($logs, function($a,$b){return filemtime($a)-filemtime($b);});
  foreach ($logs as $log) {
    if ($days<=$graph) exec("sadf -d -U $interval $log $case|awk -F';' '$valid {print $3,$data}'",$input);
    $days--;
  }
  sort($input);
  foreach ($input as $row) {
    $field = explode(' ', $row);
    $timestamp = $field[0]*1000;
    $i = 1;
    foreach ($series as $label) $output[$label][] = "[$timestamp,{$field[$i++]}]";
  }
}
if (empty($output)) foreach ($series as $label) $output[$label][] = "";
foreach ($series as $label) $json[] = '"'.$label.'":['.implode(',', $output[$label]).']';
echo '{'.implode(',', $json).'}';
?>
