<?PHP
/* Copyright 2012-2023, Bergware International.
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
$plugin = 'dynamix.active.streams';
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// add translations
$_SERVER['REQUEST_URI'] = 'activestreams';
require_once "$docroot/webGui/include/Translations.php";
require_once "$docroot/webGui/include/Helpers.php";

$plex   = $_POST['plex']??'';
$ppid   = $_POST['pid']??'';
$ipv4   = isset($_POST['ipv4']) ? "-i@{$_POST['ipv4']}" : "";
$ipv6   = isset($_POST['ipv6']) ? "-i@[{$_POST['ipv6']}]" : "";
$filter = $plex ? "^(smbd|$plex)" : "^smbd";
$mounts = explode('|',urldecode($_POST['mounts']??''));
$cfg    = array_map('trim',parse_plugin_cfg($plugin));

extract(parse_plugin_cfg("dynamix",true));

function strip_port($host) {
  if ($host[0]=='[') {
    return substr($host,1,strpos($host,']')-1);
  } else {
    return substr($host,0,strpos($host,':'));
  }
}
function playtime($time) {
  $days = floor($time/86400);
  $time -= $days*86400;
  $hour = floor($time/3600);
  $mins = floor($time/60)%60;
  $secs = $time%60;
  return ($days ? $days.'-':'').$hour.':'.($mins>9 ? '':'0').$mins.':'.($secs>9 ? '':'0').$secs;
}
function remap($cmd,&$file) {
  global $plex,$mounts;
  if (!$plex || strpos($cmd,$plex)!==0) return;
  foreach ($mounts as $mount) {
    [$source,$container] = explode(':',$mount);
    if (!$container) continue;
    if (strpos($file,$container)===0) {$file = substr_replace($file,$source,0,strlen($container)); return;}
  }
}
unset($data,$name,$lsof);
exec("LANG='en_US.UTF8' lsof -Owl /mnt/user 2>/dev/null|awk 'NR>1 && \$1~/$filter/ && \$5==\"REG\" && \$0!~/(\/mnt\/user\/appdata\/|\.AppleD(B|ouble))/{print \$2,\$1,\$7,substr(\$0,index(\$0,\"/\"))}'",$data);
exec("LANG='en_US.UTF8' smbstatus -f 2>/dev/null|awk 'NR>4 && \$1~/^[0-9]+$/{if(\$2!~/^[0-9]+$/) print \$1,\$2,\$4; else if(\$6!=\"NONE\") print \$1,substr(\$0,index(\$0,\"/\"))}'",$smb);

$now = time();
$streams = $network = $asset = $hosts = []; 
foreach ($smb as $row) {
  if (strpos($row,'/')===false) {
    [$pid,$user,$host] = explode(' ',$row);
    $network[] = ['pid'=>$pid, 'user'=>$user, 'host'=>$host];
  } else {
    [$pid,$file] = explode(' ',$row,2);
    $asset[] = ['pid'=>$pid, 'file'=>str_replace('   ','/',substr($file,0,-27)), 'date'=>substr($file,-20)];
  }
}
if ($plex && $ppid) {
  exec("nsenter -t $ppid -n lsof -OwlPn $ipv4 $ipv6 -sTCP:ESTABLISHED|awk 'NR>1 && \$9~/:443$/{print \$2,substr(\$9,index(\$9,\"->\")+2)}'",$lsof);
  $lsof = array_unique(array_map('strip_port',$lsof));
  foreach ($lsof as $row) {
    [$pid,$ip] = explode(' ',$row);
    $hosts[] = ['pid'=>$pid, 'ip'=>$ip];
  }
}
foreach ($data as $row) {
  [$pid,$cmd,$size,$file] = explode(' ',$row,4);
  remap($cmd,$file);
  $stream = "$pid $file";
  if (!in_array($stream,$streams,true)) {
    $streams[] = $stream;
    [$d1,$d2,$d3,$share,$title] = explode('/',$file,5);
    $key = array_search($pid,array_column($network,'pid'));
    if ($plex && strpos($cmd,$plex)===0) {
      $key = array_search($pid,array_column($hosts,'pid'));
      $user = $plex;
      $host = $hosts[$key]['ip'] ?: _('unknown');
    } elseif ($key!==false) {
      $user = $network[$key]['user'];
      $host = $network[$key]['host'];
      $user = $cfg[str_replace('.','_',$host)] ?: $user;
    } else {
      $user = $host = 'unknown';
    }
    $key = array_search($file,array_column($asset,'file'));
    if ($key!==false) {
      $date = preg_split('/ +/',$asset[$key]['date']); $c = count($date);
      $time = playtime($now-strtotime("{$date[$c-3]} {$date[$c-4]} {$date[$c-1]} {$date[$c-2]}"));
    } else $time = 'unavailable';
    $file = pathinfo($title);
    echo "<tr><td>$host</td><td>$user</td><td>$share</td>";
    echo "<td><div class='icon-file icon-".strtolower($file['extension'])."' style='margin-left:6px;'></div></td>";
    echo "<td>".str_replace('/',' &bullet; ',$file['dirname'])." &bullet; {$file['filename']}</td><td>$time</td><td>".my_scale($size,$unit,null,-1)." $unit</td>";
    echo "<td style='text-align:center'><a href='/plugins/dynamix.active.streams/include/StreamKill.php?pid=$pid' target='progressFrame'><i class='fa fa-square' title='"._('Stop stream')."'></i></a></td></tr>";
  }
}
if (!$streams) echo "<tr><td colspan='8' style='text-align:center;padding-top:12px'><em>"._('No active streams')."</em></td></tr>";
?>
