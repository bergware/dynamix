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
$output = array();

function change_info($line) {
  return preg_replace('/Settings for eth([0-9]):/','Port eth${1} Information',$line);
}

switch ($_GET['cmd']):
case "SYSOVERVIEW":
  unset($product,$cpu,$cache,$memory,$sPorts);
?><div class='label'><span class='left'>System Overview</span></div>
  <table class='list'>
  <tr><td>unRAID Version:</td>
  <td>unRAID Server <?=$_GET['regTy']?>, Version <?=$_GET['version']?></td>
  </tr>
  <tr><td>Motherboard:</td><td>
<?exec("dmidecode -q -t 2 | awk -F: '/Manufacturer:/ {print $2}; /Product Name:/ {print $2}'",$product);
  echo "{$product[0]} - {$product[1]}";
?></td></tr>
  <tr><td>Processor:</td><td>
<?exec("dmidecode -q -t 4 | awk -F: '/Version:/ {print $2};/Current Speed:/ {print $2}'",$cpu);
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
?></td></tr>
  <tr><td>Cache:</td>
<?$empty = true;
  exec("dmidecode -q -t 7 | awk -F: '/Socket Designation:/ {print $2}; /Installed Size:/ {print $2}; /Maximum Size:/ {print $2}'",$cache);
  $name = array();
  for ($i=0; $i<count($cache); $i+=3):
    if ($cache[$i+1]!=' 0 kB' && !in_array($cache[$i],$name)) {
      if ($i>0) echo "<tr><td></td>";
      echo "<td>{$cache[$i]} = {$cache[$i+1]} (max. {$cache[$i+2]})</td></tr>";
      $name[] = $cache[$i];
      $empty = false;
    }
  endfor;
  if ($empty) echo "</tr>";
?><tr><td>Memory:</td><td>
<?exec("dmidecode -q -t memory | awk -F: '/Maximum Capacity:/ {print $2};/Bank Locator:/ {print $2}; /Size:/ {total+=$2;print $2}; /^\tSpeed:/ {print $2} END {print total}'",$memory);
  $total = array_pop($memory);
  echo "$total MB (max. {$memory[0]})</td></tr>";
  for ($i=1; $i<count($memory); $i+=3):
    if (strpos($memory[$i+1], 'No')===false) echo "<tr><td></td><td>{$memory[$i+1]} = {$memory[$i]}, {$memory[$i+2]}</td></tr>";
  endfor;
?><tr><td>Network:</td>
<?exec("ifconfig -s | awk '$1~/[0-9]$/ {print $1}'",$sPorts);
  $i = 0;
  foreach ($sPorts as $port):
    if ($i++>0) echo "<tr><td></td>";
    if ($port=='bond0') {
      $mode = exec("cat /proc/net/bonding/$port | grep 'Mode:' | cut -d: -f2");
      echo "<td>$port: $mode</td></tr>";
    } else {
      unset($phy);
      exec("ethtool $port | awk -F: '/Speed:/ {print $2}; /Duplex:/ {print $2}'",$phy);
      echo "<td>$port: {$phy[0]} - {$phy[1]} Duplex</td></tr>";
    }
  endforeach;
  if ($i==0) echo "Not available";
?><tr><td>Uptime:</td><td>
<?$time = strtok(exec("cat /proc/uptime"), ".");
  $days = sprintf("%2d", $time/86400);
  $hours = sprintf("%2d", $time/3600%24);
  $min = sprintf("%2d", $time/60%60);
  $sec = sprintf("%2d", $time%60);
  echo "$days days, $hours hours, $min minutes, $sec seconds";
?></td></tr>
  </table>
<?break;
case "BIOS":
  exec('dmidecode -q -t 0',$output);
  break;
case "MOBOINFO":
  exec('dmidecode -q -t 2',$output);
  break;
case "CPUINFO":
  exec('dmidecode -q -t 4',$output);
  break;
case "CACHEINFO":
  exec('dmidecode -q -t 7',$output);
  break;
case "PORTINFO":
  exec('dmidecode -q -t 8',$output);
  break;
case "MEMARRAYINFO":
  exec('dmidecode -q -t 16',$output);
  break;
case "MEMDEVICE":
  exec('dmidecode -q -t 17',$output);
  break;
case "BOOTINFO":
  exec('dmidecode -q -t 32',$output);
  break;
case "ETHINFO":
  unset($sPorts);
  exec("ifconfig -s | awk '$1~/[0-9]$/ {print $1}'",$sPorts);
  foreach ($sPorts as $port):
    if ($port=='bond0'):
      exec("cat /proc/net/bonding/$port | sed 's/Ethernet Channel Bonding.*/Port $port Information/'",$output);
    else:
      exec("ethtool $port",$output);
    endif;
  endforeach;
  break;
endswitch;
$join = false;
$header = false;
foreach ($output as $line):
  if (!strlen($line)) continue;
  if (!$header):
    echo "<div class='label'><span class='left'>".change_info($line)."</span></div>";
    echo "<table class='list'>";
    $header = $line;
    continue;
  endif;
  if (preg_match('/eth[0-9]:$/',$line) || $line==$header):
    echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
    echo "<tr><td class='port'>".change_info($line)."</td><td></td></tr>";
    echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
    $join = false;
    continue;
  endif;
  if (strpos($line, ':')):
    $info = explode(':',$line,2);
    echo "<tr><td>".trim($info[0]).":</td>";
    $info[1] = str_replace(array("Processor","(C)","(R)","(TM)"),array("","&#169;","&#174;","&#8482;"),trim($info[1]));
    if (strlen($info[1])):
      echo "<td>{$info[1]}</td></tr>";
    else:
      $join = true;
    endif;
  else:
    if (preg_match('/Information|Memory Device/',$line)):
      echo "<tr><td>$line</td><td></td></tr>";
    else:
      if (!$join) echo "<tr><td></td>";
      echo "<td>$line</td></tr>";
      $join = false;
    endif;
  endif;
endforeach;
if ($header) echo "</table>";
?>