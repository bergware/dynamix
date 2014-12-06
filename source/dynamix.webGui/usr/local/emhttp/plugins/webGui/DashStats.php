<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
//Helper functions
include "plugins/webGui/include/Custom.php";

$cores   = exec('nproc');
$group   = $var['shareSMBEnabled']=='yes' | $var['shareAFPEnabled']=='yes' | $var['shareNFSEnabled']=='yes';
$scale   = $display['scale'];
$virtual = exec("cat /proc/meminfo|awk '/^MemTotal/ {print $2/1048576}'")*1000000;

exec("dmidecode -q -t memory|awk '/Maximum Capacity:/{print $3,$4};/Size:/{total+=$2;unit=$3} END{print total,unit}'",$physical);
exec("ifconfig -s|awk '$1~/[0-9]$/{print $1}'",$ports);

function parity_status() {
  global $var;
  if ($var['mdNumInvalid']==0) {
    echo "<tr><td colspan='2'><span class='green p0'><strong>Parity is valid</strong></span></td></tr>";
    if ($var['sbSynced']==0) {
      echo "<tr><td><em>Parity has not been checked yet.<em></td><td id='parity'></td></tr>";
    } else {
      unset($time);
      exec("awk '/sync completion/ {gsub(\"(time=|sec)\",\"\",x);print x;print \$NF};{x=\$NF}' /var/log/syslog|tail -2", $time);
      if (!count($time)) $time = array_fill(0,2,0);
      if ($time[1]==0) {
        echo "<tr><td style='width:50%'>Last checked on <strong>".my_time($var['sbSynced']).day_count($var['sbSynced'])."</strong>, finding <strong>{$var['sbSyncErrs']}</strong> error".($var['sbSyncErrs']==1?'.':'s.');
        echo "<br><em>Duration: ".my_check($time[0])."</em></td><td id='parity'></td></tr>";
      } else {
        echo "<tr><td style='width:50%'>Last check incomplete on <strong>".my_time($var['sbSynced']).day_count($var['sbSynced'])."</strong>, finding <strong>{$var['sbSyncErrs']}</strong> error".($var['sbSyncErrs']==1?'.':'s.');
        echo "<br><em>Error code: ".my_error($time[1])."</em></td><td id='parity'></td></tr>";
      }
    }
  } else {
    if ($var['mdInvalidDisk']==0) {
      echo "<tr><td colspan='2'><span class='red p0'><strong>Parity is invalid</strong></span></td></tr>";
    } else {
      echo "<tr><td colspan='2'><span class='red p0'><strong>Data is invalid</strong></span></td></tr>";
    }
  }
}
function truncate($string) {
  return strlen($string) < 38 ? $string : substr($string,0,35).' ...';
}
?>
<script>
function change_mode(item) {
<?if ($var['shareSMBEnabled']=='yes'):?>
  if (item==0) $('.smb').show(); else $('.smb').hide();
<?endif;?>
<?if ($var['shareAFPEnabled']=='yes'):?>
  if (item==1) $('.afp').show(); else $('.afp').hide();
<?endif;?>
<?if ($var['shareNFSEnabled']=='yes'):?>
  if (item==2) $('.nfs').show(); else $('.nfs').hide();
<?endif;?>
}
function dashboard_status() {
  $.ajax({url:'/plugins/webGui/include/DashUpdate.php',data:{cmd:'disk',hot:'<?=$display['hot']?>',max:'<?=$display['max']?>',unit:'<?=$display['unit']?>'},success:function(data) {
    if (data) $.each(data.split('#'),function(k,v) {$('#dash'+k).html(v);});
<?if ($display['refresh']>0 || ($display['refresh']<0 && $var['mdResync']==0)):?>
    setTimeout(dashboard_status,20000);
<?endif;?>
  }});
}
function system_status() {
  $.ajax({url:'/plugins/webGui/include/DashUpdate.php',data:{cmd:'sys'},success:function(data) {
    if (data) $.each(data.split('#'),function(k,v) {$('#sys'+k).animate({width:v}).text(v);});
  }});
  $.ajax({url:'/plugins/webGui/include/DashUpdate.php',data:{cmd:'cpu'},success:function(data) {
    if (data) $.each(data.split('#'),function(k,v) {$('#cpu'+k).html(v);});
<?if ($display['refresh']>0 || ($display['refresh']<0 && $var['mdResync']==0)):?>
    setTimeout(system_status,5000);
<?endif;?>
  }});
}
function parity_status() {
<?if ($var['mdNumInvalid']==0 && $var['mdResync']>0):?>
  $.ajax({url:'/plugins/webGui/include/DashUpdate.php',data:{cmd:'parity'},success:function(data) {
    if (data) $('#parity').html(data);
<?if ($display['refresh']>0 || ($display['refresh']<0 && $var['mdResync']==0)):?>
    setTimeout(parity_status,60000);
<?endif;?>
  }});
<?endif;?>
}
setTimeout(dashboard_status,0);
setTimeout(system_status,0);
setTimeout(parity_status,0);
<?if ($display['refresh']==0 || ($display['refresh']<0 && $var['mdResync']>0)):?>
$('.tabs').append("<span class='status' style='margin-right:-14px'><input type='button' value='Refresh' onclick='refresh()'></span>");
<?endif;?>
</script>
<table class='share_status fixed'>
<thead id='dash0'>
<tr><td>Array Status</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
</thead>
<tbody id='dash1'>
<tr><td class='td_col0'>Active</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
<tr><td class='td_col0'>Inactive</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
<tr><td class='td_col0'>Unassigned</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
<tr><td class='td_col0'>Faulty</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
<tr><td class='td_col0'>Heat alarm</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
<tr><td class='td_col0'>SMART status</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
<tr><td class='td_col0'>Utilization</td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td><td class='td_col0'></td><td class='td_col1'></td></tr>
</tbody>
</table>
<table class='share_status table'>
<thead><tr><td colspan='2'>Parity Status</td></tr></thead>
<tbody><?=parity_status()?></tbody>
</table>
<table class='share_status dash line'>
<thead><tr><td colspan='<?=$cores<2?3:4?>'>System Status</td></tr><tr><td colspan='<?=$cores<2?3:4?>'><center>Load Statistics</center></td></tr></thead>
<tbody>
<tr class='wide'><td>CPU utilization</td><td colspan='<?=$cores<2?2:3?>'><div class='usage-disk sys'><span id='sys0' style='width:0'></span></div></td></tr>
<?if ($cores>2):?><tr><td rowspan='<?=ceil($cores/2)?>'><?else:?><tr class='wide'><td><?endif;?>CPU speed</td>
<?
for ($c=0; $c<$cores; $c+=2):
  if ($c) echo "<tr>";
  if ($c+1<$cores)
    echo "<td>Core ".($c+1)." / ".($c+2)."</td><td class='blue' id='cpu{$c}'>"."</td><td class='blue' id='cpu".($c+1)."'></td>";
  else
    echo "<td>Core ".($c+1)."</td><td class='blue' id='cpu{$c}'>".($cores>1?"<td></td>":"");
  echo "</tr>";
endfor;
$display['scale'] = 2;
?>
<tr class='wide'><td>Memory usage</td><td colspan='<?=$cores<2?2:3?>'><div class='usage-disk sys'><span id='sys1' style='width:0'></span></div></td></tr>
<tr><td rowspan='2'>Memory size</td><td>virtual</td><td <?=$cores<2?"":"colspan='2' "?>class='blue'><?=my_scale($virtual*1024,$unit,0)." $unit"?></td></tr>
<tr><td>physical</td><td <?=$cores<2?"":"colspan='2' "?>class='blue'><?="{$physical[1]} (max. {$physical[0]})"?></td></tr>
<?if (count($ports)>1):?><tr><td rowspan='<?=count($ports)?>'><?else:?><tr class='wide'><td><?endif;?>Network</td>
<?
$display['scale'] = $scale;
$c = 0;
foreach ($ports as $port):
  if ($port=='bond0') {
    if ($c++) echo "<tr>";
    echo "<td>$port</td><td ".($cores<2?"":"colspan='2' ")."class='blue'>".exec("cat /proc/net/bonding/$port|grep 'Mode:'|cut -d: -f2")."</td></tr>";
  } else {
    unset($phy);
    exec("ethtool $port|awk -F: '/Speed:/{print $2};/Duplex:/{print $2}'",$phy);
    if ($c++) echo "<tr>";
    echo "<td>$port</td><td ".($cores<2?"":"colspan='2' ")."class='blue'>".preg_replace('/(\d+)/','$1 ',$phy[0])." - ".strtolower($phy[1])." duplex</td></tr>";
  }
endforeach;
?>
</tbody>
</table>
<table class='share_status dash'>
<thead><tr><td colspan='3'>Shares List</td><td>
<?if ($group):?>
<select name="dash_entry" size="1" onchange="change_mode(this.value);">
<?if ($var['shareSMBEnabled']=='yes'):?>
<?=mk_option("", "0", "SMB")?>
<?endif;?>
<?if ($var['shareAFPEnabled']=='yes'):?>
<?=mk_option("", "1", "AFP")?>
<?endif;?>
<?if ($var['shareNFSEnabled']=='yes'):?>
<?=mk_option("", "2", "NFS")?>
<?endif;?>
</select>
<?endif;?>
</td></tr><tr><td>Name</td><td>Description</td><td>Security</td><td>Export</td></tr></thead>
<?if ($var['shareSMBEnabled']=='yes'):?>
<tbody class='smb'>
<?
$i = 0;
foreach ($shares as $name => $share):
  $comment = truncate($share['comment']);
  $security = ucfirst($sec[$name]['security']);
  $visible = $sec[$name]['export']=='-' ? 'No' : ($sec[$name]['export']=='e' ? 'Yes' : 'Hidden');
  echo "<tr class='tr_row".($i++%2)."'><td>$name</td><td>$comment</td><td>$security</td><td>$visible</td></tr>";
endforeach;
if (!$i) echo "<tr class='tr_row0'><td colspan='4'><center>No shares present</center></td></tr>";
?>
</tbody>
<?endif;?>
<?if ($var['shareAFPEnabled']=='yes'):?>
<tbody class='afp'<?if ($var['shareSMBEnabled']=='yes'):?> style='display:none'<?endif;?>>
<?
$i = 0;
foreach ($shares as $name => $share):
  $comment = truncate($share['comment']);
  $security = ucfirst($sec_afp[$name]['security']);
  $visible = $sec_afp[$name]['export']=='-' ? 'No' : ($sec_afp[$name]['export']=='e' ? 'Yes' : 'Hidden');
  echo "<tr class='tr_row".($i++%2)."'><td>$name</td><td>$comment</td><td>$security</td><td>$visible</td></tr>";
endforeach;
if (!$i) echo "<tr class='tr_row0'><td colspan='4'><center>No shares present</center></td></tr>";
?>
</tbody>
<?endif;?>
<?if ($var['shareNFSEnabled']=='yes'):?>
<tbody class='nfs'<?if ($var['shareSMBEnabled']=='yes'||$var['shareAFPEnabled']=='yes'):?> style='display:none'<?endif;?>>
<?
$i = 0;
foreach ($shares as $name => $share):
  $comment = truncate($share['comment']);
  $security = ucfirst($sec_nfs[$name]['security']);
  $visible = $sec_nfs[$name]['export']=='-' ? 'No' : ($sec_nfs[$name]['export']=='e' ? 'Yes' : 'Hidden');
  echo "<tr class='tr_row".($i++%2)."'><td>$name</td><td>$comment</td><td>$security</td><td>$visible</td></tr>";
endforeach;
if (!$i) echo "<tr class='tr_row0'><td colspan='4'><center>No shares present</center></td></tr>";
?>
</tbody>
<?endif;?>
<?if (!$group):?>
<tbody>
<?
$i = 0;
foreach ($shares as $name => $share):
  $comment = truncate($share['comment']);
  echo "<tr class='tr_row".($i++%2)."'><td>$name</td><td>$comment</td><td>-</td><td>-</td></tr>";
endforeach;
if (!$i) echo "<tr class='tr_row0'><td colspan='4'><center>No shares present</center></td></tr>";
?>
</tbody>
<?endif;?>
</table>
<table class='share_status dash m0'>
<thead><tr><td colspan='4'>Users List</td></tr><tr><td>Name</td><td>Description</td><td>Write</td><td>Read</td></tr></thead>
<?if ($var['shareSMBEnabled']=='yes'):?>
<tbody class='smb'>
<?
$i = 0;
foreach ($users as $user):
  $tag = $user['name'];
  $desc = truncate($user['desc']);
  if ($tag=='root'):
    $write = '-'; $read = '-';
  else:
    $write = 0; $read = 0;
    foreach ($shares as $share):
      if (strpos($sec[$share['name']]['writeList'],$tag)!==false) $write++;
      if (strpos($sec[$share['name']]['readList'],$tag)!==false) $read++;
    endforeach;
  endif;
  echo "<tr class='tr_row".($i++%2)."'><td>$tag</td><td>$desc</td><td>$write</td><td>$read</td></tr>";
endforeach;
?>
</tbody>
<?endif;?>
<?if ($var['shareAFPEnabled']=='yes'):?>
<tbody class='afp'<?if ($var['shareSMBEnabled']=='yes'):?> style='display:none'<?endif;?>>
<?
$i = 0;
foreach ($users as $user):
  $tag = $user['name'];
  $desc = truncate($user['desc']);
  if ($tag=='root'):
    $write = '-'; $read = '-';
  else:
    $write = 0; $read = 0;
    foreach ($shares as $share):
      if (strpos($sec_afp[$share['name']]['writeList'],$tag)!==false) $write++;
      if (strpos($sec_afp[$share['name']]['readList'],$tag)!==false) $read++;
    endforeach;
  endif;
  echo "<tr class='tr_row".($i++%2)."'><td>$tag</td><td>$desc</td><td>$write</td><td>$read</td></tr>";
endforeach;
?>
</tbody>
<?endif;?>
<?if ($var['shareNFSEnabled']=='yes'):?>
<tbody class='nfs'<?if ($var['shareSMBEnabled']=='yes'||$var['shareAFPEnabled']=='yes'):?> style='display:none'<?endif;?>>
<?
$i = 0;
foreach ($users as $user):
  $tag = $user['name'];
  $desc = truncate($user['desc']);
  if ($tag=='root'):
    $write = '-'; $read = '-';
  else:
    $write = 0; $read = 0;
    foreach ($shares as $share):
      if (strpos($sec_nfs[$share['name']]['writeList'],$tag)!==false) $write++;
      if (strpos($sec_nfs[$share['name']]['readList'],$tag)!==false) $read++;
    endforeach;
  endif;
  echo "<tr class='tr_row".($i++%2)."'><td>$tag</td><td>$desc</td><td>$write</td><td>$read</td></tr>";
endforeach;
?>
</tbody>
<?endif;?>
<?if (!$group):?>
<tbody>
<?
$i = 0;
foreach ($users as $user):
  $tag = $user['name'];
  $desc = truncate($user['desc']);
  echo "<tr class='tr_row".($i++%2)."'><td>$tag</td><td>$desc</td><td>-</td><td>-</td></tr>";
endforeach;
?>
</tbody>
<?endif;?>
</table>