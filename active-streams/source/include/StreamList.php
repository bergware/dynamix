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
require_once 'webGui/include/Helpers.php';

$plugin = $_GET['plugin'];
$plex = explode('|',$_GET['plex']);
$cfg = parse_plugin_cfg($plugin);
extract(parse_plugin_cfg("dynamix",true));

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

function segment($process,$stream) {
  global $plex;
  if ($process=='Plex') foreach ($plex as $ref) if (strpos($stream,$ref)===0) return substr_count($ref,'/')+1;
  return 3;
}

$data = array(); exec("LANG='en_US.UTF8' lsof -Owl /mnt/user /mnt/disk* 2>/dev/null|awk '/^(smbd|afpd|Plex)/ && $5==\"REG\" && $0!~/\.AppleD(B|ouble)/{print $1,$2,$7,substr($0,index($0,\"/\"))}'",$data);
$time = array(); exec("LANG='en_US.UTF8' smbstatus -L 2>/dev/null|awk 'NR>3 && $0!~/   \.   /{print $1,substr($0,index($0,\"/\"))}'",$time);
$stat = array(); exec("lsof -OwlnPi 2>/dev/null|awk '/^(smbd|afpd).*ESTABLISHED/ || ($1==\"Plex\" && $9~/:443$/){print $2,$9}'",$stat);
$list = array();
$now = time();

foreach ($data as $entry) {
  if (!$entry) continue;
  $stream = explode('/',$entry,2);
  $info = preg_split('/ +/',$stream[0]);
  $max = segment($info[0], "/{$stream[1]}");
  $stream = explode('/',$stream[1],$max+1);
  $title = $stream[$max];
  $pid = $info[1];
  if (!in_array("$pid $title",$list,true)) {
    $list[] = "$pid $title";
    $host = host(select($stat,$pid,''));
    $ip = str_replace('.','_',$host);
    $user = isset($cfg[$ip])?trim($cfg[$ip]):$host;
    $span = "unavailable";
    if ($date = substr(select($time,$title,$pid),-20)) {
      $open = preg_split('/ +/',$date); $c = count($open);
      $span = span($now-strtotime("{$open[$c-3]} {$open[$c-4]} {$open[$c-1]} {$open[$c-2]}"));
    }
    $file = pathinfo($title);
    echo "<tr><td>".($user?$user:$host)."</td><td>{$stream[$max-1]}</td>";
    echo "<td><div class='icon-file icon-".strtolower($file['extension'])."' style='margin-left:6px;'></div></td>";
    echo "<td>".str_replace('/',' &bullet; ',$file['dirname'])." &bullet; {$file['filename']}</td><td>$span</td><td>".my_scale($info[2], $unit)." $unit</td>";
    echo "<td style='text-align:center'><a href='/plugins/$plugin/include/StreamKill.php?pid=$pid' target='progressFrame'><i class='fa fa-square' title='Stop stream'></i></a></td></tr>";
  }
}
if (!count($list)) echo "<tr><td colspan='7' style='text-align:center'><em>No active streams</em></td></tr>";
?>
