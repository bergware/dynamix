<?PHP
/* Copyright 2015-2016, Bergware International.
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
$output = array();
switch ($_POST['cmd']) {
case 'overview':
  echo "<tr><td style='font-weight:bold'>System Overview</td><td></td></tr>";
  echo "<tr><td>unRAID system:</td><td>unRAID server ".$_POST['regTy'].", version ".$_POST['version']."</td></tr>";
  echo "<tr><td>Model:</td><td>".$_POST['model']."</td></tr>";
  echo "<tr><td>Motherboard:</td><td>".exec("dmidecode -q -t 2|awk -F: '/^\tManufacturer:/{m=$2;}; /^\tProduct Name:/{p=$2;} END{print m\" -\"p}'")."</td></tr>";
  echo "<tr><td>Processor:</td><td>";
  $cpu = explode('#',exec("dmidecode -q -t 4|awk -F: '/^\tVersion:/{v=$2;}; /^\tCurrent Speed:/{s=$2;} END{print v\"#\"s}'"));
  $cpumodel = str_replace(array("Processor","(C)","(R)","(TM)"),array("","&#169;","&#174;","&trade;"),$cpu[0]);
  if (strpos($cpumodel,'@')===false) {
    $cpuspeed = explode(' ',trim($cpu[1]));
    if ($cpuspeed[0]>=1000 && $cpuspeed[1]=='MHz') {
      $cpuspeed[0] /= 1000;
      $cpuspeed[1] = 'GHz';
    }
    echo "$cpumodel @ {$cpuspeed[0]} {$cpuspeed[1]}";
  } else {
    echo $cpumodel;
  }
  echo "</td></tr>";
  echo "<tr><td>HVM:</td><td>";
  exec('modprobe -a kvm_intel kvm_amd 2>/dev/null');
  $strLoadedModules = shell_exec("lsmod | grep '^kvm_\(amd\|intel\)'");
  $strCPUInfo = file_get_contents('/proc/cpuinfo');
  if (!empty($strLoadedModules)) {
    echo 'Enabled';
  } else {
    echo '<a href="http://lime-technology.com/wiki/index.php/UnRAID_Manual_6#Determining_HVM.2FIOMMU_Hardware_Support" target="_blank">';
    echo (strpos($strCPUInfo, 'vmx')===false && strpos($strCPUInfo, 'svm')===false) ? 'Not Available' : 'Disabled';
    echo '</a>';
  }
  echo "</td></tr>";
  echo "<tr><td>IOMMU:</td><td>";
  $iommu_groups = shell_exec("find /sys/kernel/iommu_groups/ -type l");
  if (!empty($iommu_groups)) {
    echo 'Enabled';
  } else {
    echo '<a href="http://lime-technology.com/wiki/index.php/UnRAID_Manual_6#Determining_HVM.2FIOMMU_Hardware_Support" target="_blank">';
    echo (strpos($strCPUInfo, 'vmx') === false && strpos($strCPUInfo, 'svm') === false) ? 'Not Available' : 'Disabled';
    echo '</a>';
  }
  echo "</td></tr>";
  echo "<tr><td>Cache:</td>";
  $empty = true;
  $cache = explode('#',exec("dmidecode -q -t 7|awk -F: '/^\tSocket Designation:/{c=c$2\";\";}; /^\tInstalled Size:/{s=s$2\";\";}; /^\tMaximum Size:/{m=m$2\";\";} END{print c\"#\"s\"#\"m}'"));
  $socket = array_map('trim',explode(';',$cache[0]));
  $volume = array_map('trim',explode(';',$cache[1]));
  $limit  = array_map('trim',explode(';',$cache[2]));
  $name = array();
  for ($i=0; $i<count($socket); $i++) {
    if ($volume[$i] && $volume[$i]!='0 kB' && !in_array($socket[$i],$name)) {
      if ($i>0) echo "<tr><td></td>";
      echo "<td>{$socket[$i]} = {$volume[$i]} (max. capacity {$limit[$i]})</td></tr>";
      $name[] = $socket[$i];
      $empty = false;
    }
  }
  if ($empty) echo "</tr>";
  echo "<tr><td>Memory:</td>";
  $memory = explode('#',exec("dmidecode -q -t 17|awk -F: '/^\tBank Locator:/{b=b$2\";\";}; /^\tSize:/{split($2,s,\" \");t+=s[1];c=c$2\";\";}; /^\tSpeed:/{v=v$2\";\";} END{print t\"#\"b\"#\"c\"#\"v}'"));
  $maximum = exec("dmidecode -t 16 | awk -F: '/^\tMaximum Capacity: [0-9]+ GB$/{t+=$2} END{print t}'");
  $memory[0] /= 1024;
  if ($maximum < $memory[0]) {$maximum = pow(2,ceil(log($memory[0])/log(2))); $star = "*";} else $star = "";
  echo "<td>{$memory[0]} GB (max. installable capacity $maximum GB)$star</td></tr>";
  $bank = array_map('trim',explode(';', $memory[1]));
  $size = array_map('trim',explode(';', $memory[2]));
  $speed = array_map('trim',explode(';', $memory[3]));
  for ($i=0; $i<count($bank); $i++) if ($bank[$i] && strpos($size[$i],'No')===false) echo "<tr><td></td><td>{$bank[$i]} = {$size[$i]}, {$speed[$i]}</td></tr>";
  echo "<tr><td>Network:</td>";
  exec("ifconfig -s|grep -Po '^(bond|eth)\d+'",$sPorts);
  $i = 0;
  foreach ($sPorts as $port) {
    $mtu = file_get_contents("/sys/class/net/$port/mtu");
    if ($i++) echo "<tr><td></td>";
    if ($port=='bond0') {
      $mode = exec("grep -Pom1 '^Bonding Mode: \K.+' /proc/net/bonding/bond0").", mtu $mtu";
      echo "<td>$port: $mode</td></tr>";
    } else if ($port=='lo') {
      echo str_replace('yes','loopback',exec("ethtool lo|grep -Pom1 '^\s+Link detected: \K.+'"));
    } else {
      unset($info);
      exec("ethtool $port|grep -Po '^\s+(Speed|Duplex|Link\sdetected): \K[^U\\n]+'",$info);
      echo (array_pop($info)=='yes' && $info[0]) ? "<td>$port: {$info[0]}, ".strtolower($info[1])." duplex, mtu $mtu</td></tr>" : "<td>$port: not connected</td></tr>";
    }
  }
  if ($i==0) echo "Not available";
  echo "</td></tr>";
  echo "<tr><td>Kernel:</td><td>".exec("uname -srm")."</td></tr>";
  echo "<tr><td>OpenSSL:</td><td>".exec("openssl version|cut -d' ' -f2")."</td></tr>";
  echo "<tr><td>Uptime:</td><td>";
  $time = strtok(exec("cat /proc/uptime"), ".");
  $days = sprintf("%2d", $time/86400);
  $hours = sprintf("%2d", $time/3600%24);
  $min = sprintf("%2d", $time/60%60);
  $sec = sprintf("%2d", $time%60);
  echo "$days days, $hours hours, $min minutes, $sec seconds";
  echo "</td></tr>";
  return;
case 'bios':
  exec('dmidecode -q -t 0',$output);
  break;
case 'mb':
  exec('dmidecode -q -t 2',$output);
  break;
case 'cpu':
  exec('dmidecode -q -t 4',$output);
  break;
case 'cache':
  exec('dmidecode -q -t 7',$output);
  break;
case 'memory':
  exec('dmidecode -q -t 16',$output);
  break;
case 'device':
  exec('dmidecode -q -t 17',$output);
  break;
case 'ethernet':
  exec("ifconfig -s|grep -Po '^(bond|eth)\d+'",$ports);
  foreach ($ports as $port) {
    if (substr($port,0,4)=='bond')
      exec("sed 's/Ethernet Channel Bonding.*/Port $port Information/' /proc/net/bonding/$port",$output);
    else
      exec("ethtool $port|sed 's/^Settings for $port:/Port $port Information/'",$output);
    $output[] = "MTU size: ".file_get_contents("/sys/class/net/$port/mtu")." bytes";
  }
  break;
}
$join = false;
$header = false;
foreach ($output as $line) {
  if (!$line) continue;
  if (!$header || $line==$header || preg_match('/^Port (bond|eth)/',$line)) {
    if ($header) echo "<tr><td colspan='2'>&nbsp;</td></tr>"; else $header = $line;
    echo "<tr><td style='font-weight:bold'>$line</td><td></td></tr>";
    $join = false;
    continue;
  }
  if (strpos($line, ':')) {
    if ($join) {echo "<td></td></tr>"; $join = false;}
    list($title,$info) = array_map('trim', explode(':', $line, 2));
    echo "<tr><td>$title:</td>";
    if ($info)
      echo "<td>".str_replace(array("Processor","(C)","(R)","(TM)"),array("","&#169;","&#174;","&#8482;"),$info)."</td></tr>";
    else
      $join = true;
  } else {
    if (preg_match('/Information|Memory Device/',$line)) {
      echo "<tr><td>$line</td><td></td></tr>";
    } else {
      if (!$join) echo "<tr><td></td>";
      echo "<td>$line</td></tr>";
      $join = false;
    }
  }
}
if (!$header) echo "<tr><td colspan='2'><center><em>No information available</em></center></td></tr>";
?>
