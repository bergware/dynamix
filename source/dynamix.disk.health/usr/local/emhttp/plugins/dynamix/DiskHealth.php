<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<link type="text/css" rel="stylesheet" href="/plugins/dynamix/styles/disk.health.css">
<link type="text/css" rel="stylesheet" href="/plugins/dynamix/styles/jquery.stylesidebar.css">
<script type="text/javascript" src="/plugins/dynamix/scripts/jquery.stylesidebar.js"></script>
<?
$plugin = 'dynamix.disk.health';
$cfg = parse_ini_file("boot/config/plugins/dynamix/$plugin.cfg");

foreach ($disks as $disk) {
  $name = $disk['name'];
  $dev = $disk['device'];
  if ($name!='flash' && $dev) {
    if (!isset($port)) $port = $dev;
    $spin = exec("hdparm -C /dev/$dev | grep 'active'") ? "on" : "off";
    echo "<div class='".($port==$dev ? "disk_sel" : "disk_add")."'>";
    echo "<center><a href='$path?port=$dev'><img src='/plugins/dynamix/images/disk.png' id='$dev' class='$spin'><br>".my_disk($name)."</a></center></div>";
  }
}
?>
<br>
<div id="menuDiv" style="overflow:hidden">
<ul id="SystemProfilerStyleSidebar">
<li class="ajaxlink"><a href="/plugins/dynamix/include/SmartInfo.php?cmd=IDENTITY&port=<?=$port?>">Disk identity</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SmartInfo.php?cmd=ATTRIBUTES&port=<?=$port?>">Disk attributes</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SmartInfo.php?cmd=CAPABILITIES&port=<?=$port?>">Disk capabilities</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SmartInfo.php?cmd=TESTLOG&port=<?=$port?>">Disk self-test log</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SmartInfo.php?cmd=ERRORLOG&port=<?=$port?>">Disk error log</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SmartInfo.php?cmd=SELFTEST&port=<?=$port?>">Run disk self-test</a></li>
</ul>
<div id="mainContent"><!-- content  --></div>
</div>
<script>
function updater(){
<?foreach ($disks as $disk):
  $name = $disk['name'];
  $dev = $disk['device'];
  if ($name!='flash' && $dev):
?>$.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=health&port=<?=$dev?>&poll=<?=$cfg['poll']?>',success:function(data){$('#<?=$dev?>').removeClass().addClass(data);}});
<?endif;
  endforeach;
?>
<?if ($display['refresh']>0 || ($display['refresh']<0 && $var['mdResync']==0)):?>
  setTimeout(updater,10000);
<?endif;?>
}
$(function(){
  $(function(){$('li').StyleSidebar({displayDivId:'mainContent'});});
  setTimeout(updater,1000);
});
</script>