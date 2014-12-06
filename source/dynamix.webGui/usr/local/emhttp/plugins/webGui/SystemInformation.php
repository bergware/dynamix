<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$var = parse_ini_file('state/var.ini');
?>
<link type="text/css" rel="stylesheet" href="/plugins/webGui/fonts/dynamix-white.css">
<link type="text/css" rel="stylesheet" href="/plugins/webGui/styles/template-white.css">

<script>
// server uptime & update period
var uptime = <?=strtok(exec("cat /proc/uptime"),' ')?>;
var period = 1; //seconds

function add(value, label, last) {
  return parseInt(value)+' '+label+(parseInt(value)!=1?'s':'')+(!last?', ':'');
}
function two(value, last) {
  return (parseInt(value)>9?'':'0')+parseInt(value)+(!last?':':'');
}
function updateTime() {
  document.getElementById('uptime').innerHTML = add(uptime/86400,'day')+two(uptime/3600%24)+two(uptime/60%60)+two(uptime%60,true);
  uptime += period;
  setTimeout(updateTime, period*1000);
}
</script>

<body onLoad="updateTime()" style="margin-top:20px">
<center>
<img src="/plugins/webGui/images/logo-white.png" alt="unRAID" width="169" height="28" border="0" /><br>
<span style="font-size:18px;color:#6FA239;font-weight:bold">unRAID Server <?=$var['regTy']?></span><br>
</center>
<div style="margin-top:14px;font-size:12px;line-height:30px;color:#303030;margin-left:40px;">
<div style="margin-top:20px;"><span style="width:90px;display:inline-block"><strong>System:</strong></span>
<?
exec("dmidecode -q -t 2 | awk -F: '/Manufacturer:/ {print $2}; /Product Name:/ {print $2}'", $product);
echo "{$product[0]} - {$product[1]}";
?>
</div>
<div><span style="width:90px; display:inline-block"><strong>CPU:</strong></span>
<?
function write($number) {
  $words = array('zero','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen','twenty');
  return $number<=count($words) ? $words[$number] : $number;
}
exec("dmidecode -q -t 4 | awk -F: '/Version:/ {print $2};/Current Speed:/ {print $2}'",$cpu);
$cpumodel = str_replace(array("Processor","(C)","(R)","(TM)"),array("","&#169;","&#174;","&#8482;"),$cpu[0]);
if (strpos($cpumodel,'@')===false):
  $cpuspeed = explode(' ',trim($cpu[1]));
  if ($cpuspeed[0]>=1000 && $cpuspeed[1]=='MHz'):
    $cpuspeed[0] /= 1000;
    $cpuspeed[1] = 'GHz';
  endif;
  echo "$cpumodel @ {$cpuspeed[0]} {$cpuspeed[1]}";
else:
  echo $cpumodel;
endif;
?>
</div>
<div><span style="width:90px; display:inline-block"><strong>Cache:</strong></span>
<?
exec("dmidecode -q -t 7 | awk -F: '/Socket Designation:/ {print $2}; /Installed Size:/ {print $2}'",$cache);
$name = array();
$size = "";
for ($i=0; $i<count($cache); $i+=2):
  if ($cache[$i+1]!=' 0 kB' && !in_array($cache[$i],$name)):
    if ($size) $size .= ', ';
    $size .= $cache[$i+1];
    $name[] = $cache[$i];
  endif;
endfor;
echo $size;
?>
</div>
<div><span style="width:90px; display:inline-block"><strong>Memory:</strong></span>
<?
exec("dmidecode -q -t memory | awk -F: '/Maximum Capacity:/ {print $2}; /Size:/ {total+=$2} END {print total}'",$memory);
echo "{$memory[1]} MB (max. {$memory[0]})";
?>
</div>
<div><span style="width:90px; display:inline-block"><strong>Network:</strong></span>
<?
exec("ifconfig -s | awk '$1~/[0-9]$/ {print $1}'", $sPorts);
$i = 0;
foreach ($sPorts as $port):
  if ($i++>0) echo "<br><span style='width:94px; display:inline-block'>&nbsp;</span>";
  if ($port=='bond0'):
    $mode = exec("cat /proc/net/bonding/$port | grep 'Mode:' | cut -d: -f2");
    echo "$port: $mode";
  else:
    unset($phy);
    exec("ethtool $port | awk -F: '/Speed:/ {print $2}; /Duplex:/ {print $2}'", $phy);
    echo "$port: {$phy[0]} - {$phy[1]} Duplex";
  endif;
endforeach;
?>
</div>
<div><span style="width:90px; display:inline-block"><strong>Connections:</strong></span>
<?
$AFPUsers = 0;
$SMBUsers = 0;
if ($var['shareAFPEnabled']=="yes") {
  $AFPUsers = exec("ps anucx | grep -c 'afpd'");
  if ($AFPUsers > 0) $AFPUsers--;
}
if ($var['shareSMBEnabled']=="yes") {
  $SMBUsers = exec("smbstatus -p | awk 'NR>4' | wc -l");
}
echo ucfirst(write($AFPUsers+$SMBUsers));
?>
</div>
<div><span style="width:94px; display:inline-block"><strong>Uptime:</strong></span><span id="uptime"></span></div>
<br>
</div>
<center>
<?if (file_exists("/var/log/plugins/dynamix.system.info")):?>
<a href="/Utils/SystemProfiler" class="button" target="_parent">More Info</a>
<?endif;?>
</center>
</body>