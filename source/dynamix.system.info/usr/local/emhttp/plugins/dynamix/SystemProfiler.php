<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<link type="text/css" rel="stylesheet" href="/plugins/dynamix/styles/jquery.stylesidebar.css">
<script type="text/javascript" src="/plugins/dynamix/scripts/jquery.stylesidebar.js"></script>
<script>
$(function(){
  $(function(){$('li').StyleSidebar({displayDivId:'mainContent'});});
});
</script>
<div id="menuDiv" style="overflow:hidden">
<ul id="SystemProfilerStyleSidebar">
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=SYSOVERVIEW&regTy=<?=$var['regTy']?>&version=<?=$var['version']?>">System Overview</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=MOBOINFO">Motherboard Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=BIOS">BIOS Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=CPUINFO">Processor Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=CACHEINFO">Cache Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=ETHINFO">Ethernet Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=PORTINFO">Port Connector Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=MEMARRAYINFO">Memory Slot Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=MEMDEVICE">Memory Device Information</a></li>
<li class="ajaxlink"><a href="/plugins/dynamix/include/SystemInfo.php?cmd=BOOTINFO">System Boot Information</a></li>
</ul>
<div id="mainContent"><!-- content  --></div>
</div>