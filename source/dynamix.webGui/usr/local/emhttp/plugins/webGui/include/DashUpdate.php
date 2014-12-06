<?PHP
/* Copyright 2014, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1],$_GET);
$path  = "/plugins/webGui/images";

function alternate(&$td,$index) {
  $td = "$td".($index%2)."'></td>";
}
function my_replace(&$source,$string) {
  $source = str_replace('-',$string,$source);
}
function my_insert(&$source,$string) {
  $source = substr_replace($source,$string,20,0);
}
function my_smart(&$source,$device) {
  global $path;
  passthru("smartctl -n standby -q silent -H /dev/$device",$smart);
  my_insert($source,($smart&8)==0 ? "<img src=$path/good.png>" : "<img src=$path/bad.png>");
}
function my_usage(&$source,$text) {
  global $path;
  $used = $text ? $text : 0;
  my_insert($source,"<div class='usage-disk all'><span style='width:$used'>$text</span></div>");
}
function my_temp($value,$unit) {
  return ($unit=='C' ? $value : round(9/5*$value+32))." &deg;$unit";
}
function my_clock($time) {
  if (!$time) return 'less than a minute';
  $days = floor($time/1440);
  $hour = $time/60%24;
  $mins = $time%60;
  return plus($days,'day',($hour|$mins)==0).plus($hour,'hour',$mins==0).plus($mins,'minute',true);
}
function plus($val,$word,$last) {
  return $val>0?(($val||$last)?($val.' '.$word.($val!=1?'s':'').($last ?'':', ')):''):'';
}
switch ($_GET['cmd']) {
case 'disk':
  $i = 3;
  $disks = parse_ini_file("state/disks.ini",true);
  $devs  = parse_ini_file("state/devs.ini",true);
  $row0 = array_fill(0,26,"<td>-</td>"); my_replace($row0[0],"Array Status");
  $row1 = array_fill(0,26,"<td class='td_col"); array_walk($row1,'alternate'); my_insert($row1[0],"Active");
  $row2 = array_fill(0,26,"<td class='td_col"); array_walk($row2,'alternate'); my_insert($row2[0],"Inactive");
  $row3 = array_fill(0,26,"<td class='td_col"); array_walk($row3,'alternate'); my_insert($row3[0],"Unassigned");
  $row4 = array_fill(0,26,"<td class='td_col"); array_walk($row4,'alternate'); my_insert($row4[0],"Faulty");
  $row5 = array_fill(0,26,"<td class='td_col"); array_walk($row5,'alternate'); my_insert($row5[0],"Heat alarm");
  $row6 = array_fill(0,26,"<td class='td_col"); array_walk($row6,'alternate'); my_insert($row6[0],"SMART status");
  $row7 = array_fill(0,26,"<td class='td_col"); array_walk($row7,'alternate'); my_insert($row7[0],"Utilization");
  foreach ($disks as $disk) {
    $state = $disk['color'];
    $n = 0;
    switch ($disk['name']) {
    case 'flash':
    break; // do nothing
    case 'parity':
      $n = 1; my_replace($row0[1],"Parity");
    break;
    case 'cache':
      $n = 2; my_replace($row0[2],"Cache");
    break;
    default:
      if ($disk['status']!='DISK_NP') {$n = $i++; my_replace($row0[$n],$disk['idx']);}
    break;}
    if ($n>0) {
      switch ($state) {
      case 'grey-off':
      break; //ignore
      case 'green-on':
        my_insert($row1[$n],"<img src=$path/$state.png>");
      break;
      case 'green-blink':
        my_insert($row2[$n],"<img src=$path/$state.png>");
      break;
      case 'blue-on':
      case 'blue-blink':
        my_insert($row3[$n],"<img src=$path/$state.png>");
      break;
      default:
        my_insert($row4[$n],"<img src=$path/$state.png>");
      break;}
      $temp = $disk['temp'];
      if ($temp>=$_GET['hot']) my_insert($row5[$n],"<img src='$path/hot".($temp>=$_GET['max']?'50':'40').".png' title='".my_temp($temp,$_GET['unit'])."'>");
      if ($disk['device'] && !strpos($state,'blink')) my_smart($row6[$n],$disk['device']);
      my_usage($row7[$n],($n>1 && $disk['fsStatus']=='Mounted')?(round((1-$disk['fsFree']/$disk['size'])*100).'%'):'');
    }
  }
  foreach ($devs as $dev) {my_replace($row0[$i],$dev['device']); my_insert($row3[$i++],"<img src=$path/blue-on.png>");}
  echo "<tr>".implode('',$row0)."</tr>#";
  echo "<tr>".implode('',$row1)."</tr>";
  echo "<tr>".implode('',$row2)."</tr>";
  echo "<tr>".implode('',$row3)."</tr>";
  echo "<tr>".implode('',$row4)."</tr>";
  echo "<tr>".implode('',$row5)."</tr>";
  echo "<tr>".implode('',$row6)."</tr>";
  echo "<tr>".implode('',$row7)."</tr>";
break;
case 'sys':
  $cpu = round(exec("cat /proc/loadavg|awk '{print $1*10}'"));
  if ($cpu>100) $cpu = 100;
  exec("cat /proc/meminfo|awk '/^Mem/ {print $2}'",$memory);
  $mem = round((1-$memory[1]/$memory[0])*100);
  if ($mem>100) $mem = 100;
  echo "{$cpu}%#{$mem}%";
break;
case 'cpu':
  exec("cat /proc/cpuinfo|awk '/^cpu MHz/ {print $4*1}'",$speeds);
  foreach ($speeds as $speed) echo "$speed MHz#";
break;
case 'parity':
  $var  = parse_ini_file("state/var.ini");
  echo "<span class='orange p0'><strong>".($var['mdNumInvalid']==0 ? 'Parity-Check' : ($var['mdInvalidDisk']==0 ? 'Parity-Sync' : 'Data-Rebuil'))." in progress... Completed: ".number_format(($var['mdResyncPos']/($var['mdResync']/100+1)),0)." %.</strong></span>".
    "<br><em>Elapsed time: ".my_clock(floor(($var['currTime']-$var['sbUpdated'])/60)).". Estimated finish: ".my_clock(round(((($var['mdResyncDt']*(($var['mdResync']-$var['mdResyncPos'])/($var['mdResyncDb']/100+1)))/100)/60),0))."</em>";
break;}