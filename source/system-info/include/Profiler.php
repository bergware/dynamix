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
$plugin = 'dynamix.system.info';
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// add translations
$_SERVER['REQUEST_URI'] = 'systemprofiler';
require_once "$docroot/webGui/include/Translations.php";

function dmidecode($key,$n,$all=true) {
  $entries = array_filter(explode($key,shell_exec("dmidecode -qt$n")));
  $properties = [];
  foreach ($entries as $entry) {
    $property = [];
    foreach (explode("\n",$entry) as $line) if (strpos($line,': ')!==false) {
      [$key,$value] = array_pad(explode(': ',trim($line)),2,'');
      $property[$key] = $value;
    }
    $properties[] = $property;
  }
  return $all ? $properties : $properties[0]??null;
}
function grep($key, $speed){
  global $raid6;
  $match = '';
  foreach ($raid6 as $line) if (preg_match("/$key/",$line)) {$match = $line; break;}
  if (!$match) return;
  $line = preg_split('/ +/',substr($match,22));
  $size = count($line);
  return $speed ? $line[$size-2].' '.$line[$size-1] : $line[2].' '.str_replace(',','',$line[3]);
}
function si($size) {
  return str_replace(['kB','B'],['KB','iB'],$size);
}

$output = [];
switch ($_POST['cmd']??'') {
case 'overview':
  $board = dmidecode('Base Board Information',2,0);
  $cpu = dmidecode('Processor Information',4,0);
  $cpumodel = str_ireplace(["Processor","(C)","(R)","(TM)"],["","&#169;","&#174;","&#8482;"],$cpu['Version'] ?? exec("grep -Pom1 'model name\s+:\s*\K.+' /proc/cpuinfo"));
  echo "<tr><td style='font-weight:bold'>"._('System Overview')."</td><td></td></tr>";
  echo "<tr><td>"._('Unraid system').":</td><td>"._('Unraid server')." ".($_POST['regTy']??'').", version ".($_POST['version']??'')."</td></tr>";
  echo "<tr><td>Model:</td><td>".($_POST['model']??'')."</td></tr>";
  echo "<tr><td>"._('Motherboard').":</td><td>".($board['Manufacturer']??'')." ".($board['Product Name']??'').", "._('Version').": ".($board['Version']??_('unknown')).", "._('s/n').": ".($board['Serial Number']??_('unknown'))."</td></tr>";
  echo "<tr><td>"._('Processor').":</td><td>".$cpumodel.(strpos($cpumodel,'@')===false && !empty($cpu['Current Speed']) ? " @ {$cpu['Current Speed']}" : "")."</td></tr>";
  echo "<tr><td>"._('HVM').":</td><td>";
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
  echo "<tr><td>"._('IOMMU').":</td><td>";
  $iommu_groups = shell_exec("find /sys/kernel/iommu_groups/ -type l");
  if (!empty($iommu_groups)) {
    echo 'Enabled';
  } else {
    echo '<a href="http://lime-technology.com/wiki/index.php/UnRAID_Manual_6#Determining_HVM.2FIOMMU_Hardware_Support" target="_blank">';
    echo (strpos($strCPUInfo, 'vmx') === false && strpos($strCPUInfo, 'svm') === false) ? 'Not Available' : 'Disabled';
    echo '</a>';
  }
  echo "</td></tr>";
  echo "<tr><td>"._('Cache').":</td>";
  $cache_devices = dmidecode('Cache Information',7);
  $i = 0;
  foreach ($cache_devices as $device) {
   if ($i++) echo "<tr><td></td>";
    echo "<td>".$device['Socket Designation']." = ".si($device['Installed Size'])." (max. capacity ".si($device['Maximum Size']).")<td></tr>";
  }
  $sizes = ['MB','GB','TB'];
  $memory_type = $ecc = '';
  $memory_installed = $memory_maximum = 0;
  $memory_devices = dmidecode('Memory Device',17);
  $modules = 0;
  foreach ($memory_devices as $device) {
    if (empty($device['Type']) || $device['Type']=='Unknown') continue;
    [$size, $unit] = array_pad(explode(' ',$device['Size']),2,'');
    $base = array_search($unit,$sizes);
    if ($base!==false) $memory_installed += $size*pow(1024,$base);
    if (!$memory_type) $memory_type = $device['Type'];
    $modules++;
  }
  $memory_array = dmidecode('Physical Memory Array',16);
  foreach ($memory_array as $device) {
    [$size, $unit] = array_pad(explode(' ',$device['Maximum Capacity']),2,'');
    $base = array_search($unit,$sizes);
    if ($base>=1) $memory_maximum += $size*pow(1024,$base);
    if (!$ecc && isset($device['Error Correction Type']) && $device['Error Correction Type']!='None') $ecc = ($device['Error Correction Type']??'')." ";
  }
  if ($memory_installed >= 1024) {
    $memory_installed = round($memory_installed/1024);
    $memory_maximum = round($memory_maximum/1024);
    $unit = 'GiB';
  } else $unit = 'MiB';

  // If maximum < installed then roundup maximum to the next power of 2 size of installed. E.g. 6 -> 8 or 12 -> 16
  $low = $memory_maximum < $memory_installed;
  if ($low) $memory_maximum = pow(2,ceil(log($memory_installed)/log(2)));
  echo "<tr><td>"._('Memory').":</td><td>$memory_installed $unit $memory_type $ecc("._('max. installable capacity')." $memory_maximum $unit".($low?'*':'').")</td></tr>";
  foreach ($memory_devices as $device) {
    if (empty($device['Type']) || $device['Type']=='Unknown') continue;
    $size = si($device['Size']??'0');
    echo "<tr class='ram'><td></td><td>".$device['Locator'].": ".($device['Manufacturer']??'')." ".($device['Part Number']??'').", $size ".($device['Type']??'')." @ ".($device['Configured Memory Speed']??'')."</td></tr>";
  }
  $i = 0;
  echo "<tr><td>"._('Network').":</td>";
  exec("ls --indicator-style=none /sys/class/net|grep -Po '^(bond|eth)\d+$'",$sPorts);
  foreach ($sPorts as $port) {
    $int = "/sys/class/net/$port";
    $mtu = file_get_contents("$int/mtu");
    $link = @file_get_contents("$int/carrier")==1;
    if ($i++) echo "<tr><td></td>";
    if (substr($port,0,4)=='bond') {
      if ($link) {
        $mode = str_replace('Bonding Mode: ','',file("/proc/net/bonding/$port",FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)[1]);
        echo "<td>$port: $mode</td></tr>";
      } else {
        echo "<td>$port: "._("bond down")."</td></tr>";
      }
    } else {
      if ($link) {
        $speed = file_get_contents("$int/speed");
        $duplex = file_get_contents("$int/duplex");
        echo "<td>$port: $speed Mbps, $duplex duplex, mtu $mtu</td></tr>";
      } else {
        echo "<td>$port: "._("interface down")."</td></tr>";
      }
    }
  }
  if ($i==0) echo "<td>"._("Not available")."</td></tr>";
  echo "<tr><td>"._('Kernel').":</td><td>".exec("uname -srm")."</td></tr>";
  echo "<tr><td>"._('OpenSSL').":</td><td>".exec("openssl version|cut -d' ' -f2")."</td></tr>";
  echo "<tr><td>"._('P + Q algorithm').":</td>";
  exec("grep ' raid6: ' /var/log/dmesg", $raid6);
  $p = grep("\.\.\.\. xor()",false);
  $q = grep('using algorithm ',true);
  echo "<td>$p + $q</td></tr>";
  return;
case 'bios':
  exec("dmidecode -qt0|grep -v '^Invalid entry'",$output);
  break;
case 'mb':
  exec("dmidecode -qt2|grep -v '^Invalid entry'",$output);
  break;
case 'cpu':
  exec("dmidecode -qt4|grep -v '^Invalid entry'",$output);
  break;
case 'cache':
  exec("dmidecode -qt7|grep -v '^Invalid entry'",$output);
  break;
case 'memory':
  exec("dmidecode -qt16|grep -v '^Invalid entry'",$output);
  break;
case 'device':
  exec("dmidecode -qt17|grep -v '^Invalid entry'",$output);
  break;
case 'ethernet':
  exec("ifconfig -s -a|grep -Po '^(bond|eth)\d+ '",$ports);
  foreach ($ports as $port) {
    $port = trim($port);
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
    echo "<tr><td style='font-weight:bold'>"._($line)."</td><td></td></tr>";
    $join = false;
    continue;
  }
  if (strpos($line, ':')) {
    if ($join) {echo "<td></td></tr>"; $join = false;}
    [$title,$info] = array_map('trim', explode(':', $line, 2));
    echo "<tr><td>"._($title).":</td>";
    if ($info)
      echo "<td>"._(str_replace(["Processor","(C)","(R)","(TM)"],["","&#169;","&#174;","&#8482;"],$info))."</td></tr>";
    else
      $join = true;
  } else {
    if (preg_match('/Information|Memory Device/',$line)) {
      echo "<tr><td>"._($line)."</td><td></td></tr>";
    } else {
      if (!$join) echo "<tr><td></td>";
      echo "<td>$line</td></tr>";
      $join = false;
    }
  }
}
if (!$header) echo "<tr><td colspan='2'><center><em>"._('No information available')."</em></center></td></tr>";
?>
