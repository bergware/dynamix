<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1], $_GET);

switch ($_GET['type']) {
case 'sum':
  $rom = "boot/config/plugins/dynamix";
  $images = "plugins/dynamix/images";
  $disks = parse_ini_file("state/disks.ini",true);
  $cfg = parse_ini_file("$rom/{$_GET['plugin']}.cfg");
  $dynamix = parse_ini_file("$rom/dynamix.webGui.cfg",true);
  $display = &$dynamix['display'];
  $data = array();
  $arraysize=0; $arrayfree=0;
  foreach ($disks as $disk) {
    if (strpos($disk['name'],'disk')!==false) {
      $arraysize += $disk['sizeSb']*1024;
      $arrayfree += $disk['fsFree']*1024;
    }
  }
  $arrayused = $arraysize-$arrayfree;
  $freepercent = round(100*$arrayfree/$arraysize);
  $arraypercent = 100-$freepercent;
  $data[] = "mybar ".bar_color($arraypercent)." align-left";
  $data[] = "$arraypercent%";
  $data[] = "mybar ".bar_color($arraypercent)." inside";
  $data[] = "<strong>".my_scale($arrayused, $unit)." $unit <img src='/$images/arrow.png' style='margin-top:-3px'> $arraypercent%</strong><br><small>Total Space Used</small>";
  $data[] = "<strong>".my_scale($arrayfree, $unit)." $unit <img src='/$images/arrow.png' style='margin-top:-3px'> $freepercent%</strong><br><small>Available for Data</small>";
  echo implode(';',$data);
  exit;
case 'sys':
  $series = array('Critical','Warning','Normal');
  $disks = parse_ini_file("state/disks.ini", true);
  $warning = $_GET['warning'];
  $critical = $_GET['critical'];
  $output = array();
  $json = array();
  foreach ($disks as $disk) {
    if ($disk['name']!='parity' && $disk['status']=='DISK_OK') {
      $size = ($disk['name']=='flash'?$disk['size']:$disk['sizeSb'])*1024;
      $free = $disk['fsFree']*1024;
      $percent = 100-round(100*$free/$size);
      $point[0] = $percent-$critical;
      $point[1] = $percent-$warning;
      if ($point[0]>0) {$point[1] = $critical-$warning;} else {$point[0] = 0;}
      if ($point[1]>0) {$point[2] = $warning;} else {$point[2] = $percent; $point[1] = 0;}
      if ($warning>=$critical && $point[2]>$warning) $point[2] = $critical;
      $i = 0;
      foreach ($series as $label) $output[$label][] = $point[$i++];
    }
  }
  foreach ($series as $label) $json[] = '"'.$label.'":['.implode(',', $output[$label]).']';
  echo '{'.implode(',', $json).'}';
  exit;
case 'rts':
  $cpu = '$2=="all"';
  $hdd = '$2=="tps"';
  $ram = '$2=="kbmemfree"';
  $com = '$2=="'.$_GET['port'].'"';
  $read = "sar 1 1 -u -b -r -n DEV|grep '^Average'|awk '$cpu {print $3+$4,$5}; $hdd {getline;print $5,$6}; $ram {getline;print $2,$5+$6,$3-$5-$6}; $com {print $5,$6}'";
  $data = array();
  exec($read,$data);
  echo implode(' ', $data);
  exit;
case 'cpu':
  $series = array('User','System');
  $data = '$5+$6,$7';
  $sadf = '';
  $mask = ' && $5<=100 && $6<=100 && $7<=100';
  break;
case 'ram':
  $series = array('Free','Cached','Used');
  $data = '$4,$7+$8,$5-$7-$8';
  $sadf = '-- -r';
  $mask = ' && $4<100000000000 && $5<100000000000 && $7<100000000000 && $8<100000000000';
  break;
case 'com':
  $series = array('Receive','Transmit');
  $data = '$7,$8';
  $sadf = '-- -n DEV';
  $mask = ' && $4=="'.$_GET['port'].'" && $7<100000000000 && $8<100000000000';
  break;
case 'hdd':
  $series = array('Read','Write');
  $data = '$7,$8';
  $sadf = '-- -b';
  $mask = ' && $7<100000000000 && $8<100000000000';
  break;
}
$input = array();
$output = array();
$json = array();
$select = array(1=>60, 2=>120, 3=>300, 7=>600, 14=>1200, 21=>1800, 31=>3600, 3653=>7200);
$logs = glob("/var/log/sa/sa*");
$days = count($logs);
$graph = $_GET['graph'];
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
  usort($logs, create_function('$a,$b', 'return filemtime($a)-filemtime($b);'));
  foreach ($logs as $log) {
    if ($days<=$graph) exec("sadf -d -U $interval $log $sadf|awk -F';' '$valid {print $3,$data}'",$input);
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

function my_scale($value, &$unit, $precision = NULL) {
  global $display;
  $scale = $display['scale'];
  $dot = substr($display['number'],0,1);
  $comma = substr($display['number'],1,1);
  $units = array('B','KB','MB','GB','TB','PB');
  if ($scale==0 && !$precision) {
    $unit = '';
    return number_format($value,0,$dot,($value>=10000?$comma:''));
  } else {
    $base = $value ? floor(log($value, 1000)):0;
    if ($scale>0 && $base>$scale) $base = $scale;
    $unit = $units[$base];
    $value = round($value/pow(1000, $base), $precision ? $precision : 2);
    return number_format($value,$precision?$precision:(($value-intval($value)==0||$value>=100)?0:($value>=10?1:2)),$dot,($value>=10000?$comma:''));
  }
}
function bar_color($val) {
  global $cfg;
  switch (true) {
  case ($val>=$cfg['critical']):
    return "redbar";
  case ($val>=$cfg['warning']):
    return "orangebar";
  default:
    return "greenbar";}
}
?>