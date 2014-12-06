<?PHP
/* Copyright 2013, LimeTech & Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1],$_GET);
$path    = $_GET['path'];
$var     = parse_ini_file("state/var.ini");
$devs    = parse_ini_file("state/devs.ini",true);
$disks   = parse_ini_file("state/disks.ini",true);
$dynamix = parse_ini_file("boot/config/plugins/dynamix/dynamix.webGui.cfg",true);
$display = &$dynamix['display'];
$screen  = '/tmp/screen_buffer';

$temps=0; $counts=0; $fsSize=0; $fsUsed=0; $fsFree=0; $reads=0; $writes=0; $errors=0; $row=0;

include "plugins/webGui/include/Helpers.php";

function device_info($disk) {
  global $path, $var, $display, $screen;
  $href = $disk['name'];
  if ($href != 'preclear') {
    $name = my_disk($href);
    $type = strpos($href,'disk')===false ? $name : "Data";
  } else {
    $name = $disk['device'];
    $type = 'Preclear';
    $href = "{$disk['device']}&file=$screen";
  }
  $action = strpos($disk['color'],'blink')===false ? "down" : "up";
  $a = "<a href='#' class='info nohand' onClick='return false'>";
  $spin_disk = "";
  $title = "";
  if ($display['spin'] && $var['fsState']=="Started") {
    if ($href != 'cache' && isset($disk['idx'])) {
      $cmd = "/root/mdcmd&arg1=spin{$action}&arg2={$disk['idx']}";
    } else {
      $cmd = "hdparm&arg1=".($action=='up' ? "S0" : "-y")."&arg2=/dev/{$disk['device']}";
    }
    $a = "<a href='update.htm?cmd={$cmd}&runCmd=Apply' class='info' target='progressFrame'>";
    $title = "Spin ".ucfirst($action);
    $spin_disk = "<img src='/plugins/webGui/images/$action.png' class='iconwide'>Spin $action disk<br>";
  }
  $ball = "/plugins/webGui/images/{$disk['color']}.png";
  if ($type != 'Flash') {
    $status = "{$a}
    <img src='$ball' title='$title' class='icon' onclick=\"$.removeCookie('one',{path:'/'});\"><span>
    <img src='/plugins/webGui/images/green-on.png' class='icon'>Normal operation<br>
    <img src='/plugins/webGui/images/yellow-on.png' class='icon'>Invalid data content<br>
    <img src='/plugins/webGui/images/red-on.png' class='icon'>Disabled disk<br>
    <img src='/plugins/webGui/images/blue-on.png' class='icon'>New disk, not in array<br>
    <img src='/plugins/webGui/images/grey-off.png' class='icon'>No disk present<br>
    <img src='/plugins/webGui/images/green-blink.png' class='icon'>Disk spun-down<br>
    {$spin_disk}</span></a>";
  } else {
    $status = "<img src='$ball' class='icon'>";
  }
  $link = strpos($disk['status'], '_NP')===false ? "<a href='$path/$type?name=$href'>$name</a>" : $name;
  return $status.$link;
}
function device_browse($disk) {
  global $path;
  if ($disk['fsStatus']=='Mounted'):
    $dir = $disk['name']=="flash" ? "/boot" : "/mnt/{$disk['name']}";
    return "<a href='$path/Browse?dir=$dir'><img src='/plugins/webGui/images/explore.png' title='Browse $dir'></a>";
  endif;
}
function device_desc($disk) {
  return "{$disk['id']} ({$disk['device']})";
}
function assignment($disk) {
  global $devs, $screen;
  $out = "<form method='POST' name=\"{$disk['name']}Form\" action='/update.htm' target='progressFrame'><input type='hidden' name='changeDevice' value='Apply'>";
  $out .= "<select name=\"slotId.{$disk['idx']}\" onChange=\"{$disk['name']}Form.submit()\">";
  $empty = ($disk['idSb']!="" ? "no device" : "unassigned");
  if ($disk['id']!=""):
    $out .= "<option value=\"{$disk['id']}\" selected>".device_desc($disk)."</option>";
    $out .= "<option value=''>$empty</option>";
  else:
    $out .= "<option value='' selected>$empty</option>";
  endif;
  foreach ($devs as $dev):
    if (!file_exists("{$screen}_{$dev['device']}")) $out .= "<option value=\"{$dev['id']}\">".device_desc($dev)."</option>";
  endforeach;
  $out .= "</select></form>";
  return $out;
}
function array_offline($disk) {
  global $row;
  echo "<tr class='tr_row".($row^=1)."'>";
  switch ($disk['status']) {
  case "DISK_NP":
    echo "<td>".device_info($disk)."</td>";
    echo "<td colspan='9'>".assignment($disk)."</td>";
  break;
  case "DISK_OK":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $unit)." $unit</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  case "DISK_INVALID":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $unit)." $unit</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  case "DISK_DSBL":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $unit)." $unit</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  case "DISK_DSBL_NP":
  if ($disk['name']=="parity") {
    echo "<td>".device_info($disk)."</td>";
    echo "<td colspan='9'>".assignment($disk)."</td>";
  } else {
    echo "<td>".device_info($disk)."<span class='diskinfo'><em>Not installed</em></span></td>";
    echo "<td>".assignment($disk)."<em>{$disk['idSb']}</em></td>";
    echo "<td>-</td>";
    echo "<td><em>".my_scale($disk['sizeSb']*1024, $unit)." $unit</em></td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  }
  break;
  case "DISK_DSBL_NEW":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $unit)." $unit</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  case "DISK_NP_MISSING":
    echo "<td>".device_info($disk)."<span class='diskinfo'><em>Missing</em></span></td>";
    echo "<td>".assignment($disk)."<em>{$disk['idSb']}</em></td>";
    echo "<td>-</td>";
    echo "<td><em>".my_scale($disk['sizeSb']*1024, $unit)." $unit</em></td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  case "DISK_WRONG":
    echo "<td>".device_info($disk)."<span class='diskinfo'><em>Wrong</em></span></td>";
    echo "<td>".assignment($disk)."<em>{$disk['idSb']}</em></td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['size']*1024, $unit)." $unit<br><em>".my_scale($disk['sizeSb']*1024, $unit)." $unit</em></td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  case "DISK_NEW":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $unit)." $unit</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
  break;
  }
  echo "</tr>";
}
function array_online($disk) {
  global $display, $temps, $counts, $fsSize, $fsUsed, $fsFree, $reads, $writes, $errors, $row;
  if (is_numeric($disk['temp'])) {
    $temps += $disk['temp'];
    $counts++;
  }
  $reads += $disk['numReads'];
  $writes += $disk['numWrites'];
  $errors += $disk['numErrors'];
  if (isset($disk['fsFree']) && $disk['name']!='parity') {
    $disk['fsUsed'] = $disk['sizeSb'] - $disk['fsFree'];
    $fsSize += $disk['sizeSb'];
    $fsFree += $disk['fsFree'];
    $fsUsed += $disk['fsUsed'];
  }
  echo "<tr class='tr_row".($row^=1)."'>";
  switch ($disk['status']) {
  case "DISK_NP":
// Suppress empty slots to keep device list short
//    echo "<td>".device_info($disk)."</td>";
//    echo "<td colspan='9'>Not installed</td>";
  break;
  case "DISK_DSBL_NP":
    echo "<td>".device_info($disk)."</td>";
  if ($disk['name']=="parity") {
    echo "<td colspan='9'>Not installed</td>";
  } else {
    echo "<td><em>Not installed</em></td>";
    echo "<td>-</td>";
    echo "<td><em>".my_scale($disk['sizeSb']*1024, $unit)." $unit</em></td>";
    if ($disk['fsStatus']=='Mounted') {
      if ($display['text']) {
        echo "<td><em>".my_scale($disk['fsUsed']*1024, $unit)." $unit</em></td>";
        echo "<td><em>".my_scale($disk['fsFree']*1024, $unit)." $unit</em></td>";
      } else {
        $free = round(100*$disk['fsFree']/$disk['sizeSb']);
        $used = 100-$free;
        echo "<td><div class='usage-disk'><span style='margin:0;width:{$used}%'><span>".my_scale($disk['fsUsed']*1024, $unit)." $unit</span></span></div></td>";
        echo "<td><div class='usage-disk'><span style='margin:0;width:{$free}%'><span>".my_scale($disk['fsFree']*1024, $unit)." $unit</span></span></div></td>";
      }
    } else {
      if ($display['text']) {
        echo "<td></td>";
        echo "<td>{$disk['fsStatus']}</td>";
      } else {
        echo "<td><div class='usage-disk'><span style='margin:0;width:0'></span></div></td>";
        echo "<td><div class='usage-disk'><span style='margin:0;width:0'><span>{$disk['fsStatus']}</span></span></div></td>";
      }
    }
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>".device_browse($disk)."</td>";
  }
  break;
  default:
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".device_desc($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $unit)." $unit</td>";
    if ($disk['fsStatus']=='Mounted') {
      if ($display['text']) {
        echo "<td>".my_scale($disk['fsUsed']*1024, $unit)." $unit</td>";
        echo "<td>".my_scale($disk['fsFree']*1024, $unit)." $unit</td>";
      } else {
        $free = round(100*$disk['fsFree']/$disk['sizeSb']);
        $used = 100-$free;
        echo "<td><div class='usage-disk'><span style='margin:0;width:{$used}%'><span>".my_scale($disk['fsUsed']*1024, $unit)." $unit</span></span></div></td>";
        echo "<td><div class='usage-disk'><span style='margin:0;width:{$free}%'><span>".my_scale($disk['fsFree']*1024, $unit)." $unit</span></span></div></td>";
      }
    } else {
      if ($display['text']) {
        echo $disk['name']=="parity" ? "<td>-</td><td>-</td>" : "<td></td><td>{$disk['fsStatus']}</td>";
      } else {
        echo "<td><div class='usage-disk'><span style='margin:0;width:0'></span></div></td>";
		if ($disk['name']=="parity") $disk['fsStatus'] = '';
        echo "<td><div class='usage-disk'><span style='margin:0;width:0'><span>{$disk['fsStatus']}</span></span></div></td>";
      }
    }
    echo "<td>".my_number($disk['numReads'])."</td>";
    echo "<td>".my_number($disk['numWrites'])."</td>";
    echo "<td>".my_number($disk['numErrors'])."</td>";
    echo "<td>".device_browse($disk)."</td>";
  break;
  }
  echo "</tr>";
}
function my_clock($time) {
  if (!$time) return 'less than a minute';
  $days = floor($time/1440);
  $hour = $time/60%24;
  $mins = $time%60;
  return plus($days,'day',($hour|$mins)==0).plus($hour,'hour',$mins==0).plus($mins,'minute',true);
}
switch ($_GET['device']):
case 'array':
  switch ($var['fsState']):
  case 'Started':
    foreach ($disks as $disk) {if ($disk['name']!='flash' && $disk['name']!='cache') array_online($disk);}
    if ($display['total']) {
      echo "<tr class='tr_last'>";
      echo "<td><img src='/plugins/webGui/images/sum.png' class='icon'>Total</td>";
      echo "<td>Array of ".my_word($var['mdNumProtected'])." disks (including parity disk)</td>";
      echo "<td>".($counts>0?my_temp(round($temps/$counts, 1)):'*')."</td>";
      echo "<td>".my_scale($fsSize*1024, $unit)." $unit</td>";
      if ($display['text']) {
        echo "<td>".my_scale($fsUsed*1024, $unit)." $unit</td>";
        echo "<td>".my_scale($fsFree*1024, $unit)." $unit</td>";
      } else {
        $free = round(100*$fsFree/$fsSize);
        $used = 100-$free;
        echo "<td><div class='usage-disk'><span style='margin:0;width:{$used}%'><span>".my_scale($fsUsed*1024, $unit)." $unit</span></span></div></td>";
        echo "<td><div class='usage-disk'><span style='margin:0;width:{$free}%'><span>".my_scale($fsFree*1024, $unit)." $unit</span></span></div></td>";
      }
      echo "<td>".my_number($reads)."</td>";
      echo "<td>".my_number($writes)."</td>";
      echo "<td>".my_number($errors)."</td>";
      echo "<td></td>";
      echo "</tr>";
    }
  break;
  case 'Stopped':
    foreach ($disks as $disk) {if ($disk['name']!='flash' && $disk['name']!='cache') array_offline($disk);}
  break;
  endswitch;
break;
case 'flash':
  $disk = &$disks['flash'];
  $disk['fsUsed'] = $disk['size'] - $disk['fsFree'];
  echo "<tr class='tr_row1'>";
  echo "<td>".device_info($disk)."</td>";
  echo "<td>".device_desc($disk)."</td>";
  echo "<td>*</td>";
  echo "<td>".my_scale($disk['size']*1024, $unit)." $unit</td>";
  if ($display['text']) {
    echo "<td>".my_scale($disk['fsUsed']*1024, $unit)." $unit</td>";
    echo "<td>".my_scale($disk['fsFree']*1024, $unit)." $unit</td>";
  } else {
    $free = round(100*$disk['fsFree']/$disk['size']);
    $used = 100-$free;
    echo "<td><div class='usage-disk'><span style='margin:0;width:{$used}%'><span>".my_scale($disk['fsUsed']*1024, $unit)." $unit</span></span></div></td>";
    echo "<td><div class='usage-disk'><span style='margin:0;width:{$free}%'><span>".my_scale($disk['fsFree']*1024, $unit)." $unit</span></span></div></td>";
  }
  echo "<td>".$disk['numReads']."</td>";
  echo "<td>".$disk['numWrites']."</td>";
  echo "<td>".$disk['numErrors']."</td>";
  echo "<td>".device_browse($disk)."</td>";
  echo "</tr>";
break;
case 'cache':
  if ($var['fsState']=='Stopped') array_offline($disks['cache']); else array_online($disks['cache']);
break;
case 'open':
  $status = file_exists("/var/log/plugins/dynamix.disk.preclear") ? '' : '_NP';
  foreach ($devs as $dev) {
    $dev['name'] = 'preclear';
    $dev['color'] = 'blue-on';
    $dev['status'] = $status;
    echo "<tr class='tr_row".($row^=1)."'>";
    echo "<td>".device_info($dev)."</td>";
    echo "<td>".device_desc($dev)."</td>";
    echo "<td>*</td>";
    echo "<td>".my_scale($dev['size']*1024, $unit)." $unit</td>";
    if (file_exists("/tmp/preclear_stat_{$dev['device']}")) {
      $text = exec("cut -d'|' -f3 /tmp/preclear_stat_{$dev['device']} | sed 's:\^n:\<br\>:g'");
      if (strpos($text,'Total time')===false) $text = 'Preclear in progress... '.$text;
      echo "<td colspan='6' style='text-align:right'><em>$text</em></td>";
    } else
      echo "<td colspan='6'></td>";
    echo "</tr>";
  }
break;
case 'parity':
  $data = array();
  if ($var['mdResync']>0) {
    $data[] = my_scale($var['mdResync']*1024, $unit)." $unit";
    $data[] = my_clock(floor(($var['currTime']-$var['sbUpdated'])/60));
    $data[] = my_scale($var['mdResyncPos']*1024, $unit)." $unit (".number_format(($var['mdResyncPos']/($var['mdResync']/100+1)),1,substr($display['number'],0,1),'')." %)";
    $data[] = my_scale($var['mdResyncDb']/$var['mdResyncDt']*1024, $unit, 1)." $unit/sec";
    $data[] = my_clock(round(((($var['mdResyncDt']*(($var['mdResync']-$var['mdResyncPos'])/($var['mdResyncDb']/100+1)))/100)/60),0));
    $data[] = $var['sbSyncErrs'];
    echo implode(';',$data);
  }
break;
endswitch;
?>