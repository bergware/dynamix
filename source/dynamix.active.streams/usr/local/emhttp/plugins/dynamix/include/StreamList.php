<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1],$_GET);
date_default_timezone_set($_GET['zone']);

$plugin = $_GET['plugin'];
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");

function autoscale($size) {
  $units = array('B','KB','MB','GB','TB');
  $base = $size?floor(log($size,1000)):0;
  $unit = $units[$base];
  $size = round($size/pow(1000,$base),2);
  return number_format($size,(($size-intval($size)==0 || $size>=100) ? 0 : ($size>=10 ? 1 : 2)))." $unit";
}
function select($array,$item,$pid) {
  foreach ($array as $entry) {if (strpos($entry,$item)!==false && ($pid?strpos($entry,$pid)!==false:true)) return $entry;}
  return "";
}
function host($com) {
  $x = strpos($com,'>')+1;
  return substr($com,$x,strrpos($com,':')-$x);
}
function span($time) {
  $days = floor($time/86400);
  $time -= $days*86400;
  $hour = floor($time/3600);
  $mins = $time/60%60;
  $secs = $time%60;
  return ($days ? $days.'-':'').$hour.':'.($mins>9 ? '':'0').$mins.':'.($secs>9 ? '':'0').$secs;
}
$data = array(); exec("LANG='en_US.UTF8' lsof /mnt/user /mnt/disk*|awk '/^(smbd|afpd|Plex)/ && $0!~/\.AppleD(B|ouble)/ && $5==\"REG\"'",$data);
$time = array(); exec("LANG='en_US.UTF8' smbstatus -L|awk 'NR>3 && $0!~/   \.   /'|cut -c1-12,76-",$time);
$stat = array(); exec("lsof -i -n -P|awk '/^(smbd|afpd|Plex).*ESTABLISHED\)$/ {print $2,$9}'",$stat);
$list = array(); $row = 0; $now = time();
foreach ($data as $entry) {
  if (!$entry) continue;
  $stream = explode('/',$entry,5);
  $info = preg_split('/ +/',$stream[0]);
  if (!in_array("{$info[1]} {$stream[4]}",$list,true)) {
    $list[] = "{$info[1]} {$stream[4]}";
    $host = host(select($stat,$info[1],''));
    $ip = str_replace('.','_',$host);
    $user = isset($cfg[$ip])?trim($cfg[$ip]):$host;
    $span = "unavailable";
    if ($date = substr(select($time,$stream[4],$info[1]),-20)) {
      $open = preg_split('/ +/',$date); $c = count($open);
      $span = span($now-strtotime("{$open[$c-3]} {$open[$c-4]} {$open[$c-1]} {$open[$c-2]}"));
      }
    $file = pathinfo($stream[4]);
    echo "<tr class='tr_row".($row^=1)."'><td>".($user?$user:$host)."</td><td>{$stream[3]}</td>";
    echo "<td><div class='icon-file icon-".strtolower($file['extension'])."' style='margin-left:6px;'></div></td>";
    echo "<td>".str_replace('/',' &bullet; ',$file['dirname'])." &bullet; {$file['filename']}</td><td>$span</td><td>".autoscale($info[6])."</td>";
    echo "<td style='text-align:center'><a href='/plugins/dynamix/include/StreamKill.php?pid={$info[1]}' target='progressFrame'><img src='/plugins/dynamix/images/halt.png' title='Stop stream'></a></td></tr>";
  }
}
if (!count($list)) echo "<tr class='tr_row1'><td colspan='7' style='text-align:center'><em>No active streams</em></td></tr>";
?>