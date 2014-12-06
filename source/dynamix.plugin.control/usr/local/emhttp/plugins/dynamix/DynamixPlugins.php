<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<script>
function pluginInstall(plugin) {
  var part = plugin.split('-');
  document.getElementById(plugin).value = 'Installing...';
  $.ajax({url:'/plugins/dynamix/include/PluginControl.php',data:{cmd:'install',plugin:part[0],version:part[1],load:part[2]},success:function(data) {
    if (data) $('#pluginList').html(data);
    if (part[3]=='y') setTimeout(refresh,0); else if ($('a.sb-refresh').length) Shadowbox.setup('a.sb-refresh', {onClose:function() {pluginInit();}});
  }});
}

function pluginUpdate(plugin) {
  var part = plugin.split('-');
  document.getElementById(plugin).value = 'Updating...';
  $.ajax({url:'/plugins/dynamix/include/PluginControl.php',data:{cmd:'update',plugin:part[0],version:part[1],github:part[2]},success:function(data) {
    if (data) $('#pluginList').html(data);
    if (part[3]=='y') setTimeout(refresh,0); else if ($('a.sb-refresh').length) Shadowbox.setup('a.sb-refresh', {onClose:function() {pluginInit();}});
  }});
}

function pluginRemove(plugin) {
  var part = plugin.split('-');
  if (!window.confirm("This will remove plugin: "+part[0]+".\n\nAre you sure?")) exit;
  document.getElementById(plugin).value = 'Removing...';
  $.ajax({url:'/plugins/dynamix/include/PluginControl.php',data:{cmd:'remove',plugin:part[0],version:part[1]},success:function(data) {
    if (data) $('#pluginList').html(data);
    if (part[2]=='y') setTimeout(refresh,0);
  }});
}

function pluginUninstall(plugin) {
  var part = plugin.split('-');
  if (!window.confirm("This will uninstall plugin: "+part[0]+".\n\nAre you sure?")) exit;
  document.getElementById(plugin).value = 'Uninstalling...';
  $.ajax({url:'/plugins/dynamix/include/PluginControl.php',data:{cmd:'uninstall',plugin:part[0],version:part[1],reboot:part[2]},success:function(data) {
    if (data) $('#pluginList').html(data);
    if (part[3]=='y') setTimeout(refresh,0);
  }});
}

function pluginInit() {
  $.ajax({url:'/plugins/dynamix/include/PluginControl.php',data:{cmd:'init'},success:function(data) {
    if (data) $('#pluginList').html(data);
    if ($('a.sb-refresh').length) Shadowbox.setup('a.sb-refresh', {onClose:function() {pluginInit();}});
  }});
}
pluginInit();
</script>

<table class='share_status small'>
<thead><tr><td width='64px' style='padding-left:28px'><img src='/plugins/dynamix/images/update.png'></td><td width='12%'>Plugin</td><td>Description</td><td width='7%'>Local<br>Version</td><td width='7%'>Github<br>Version</td><td width='9%'>Author</td><td width='8%'>Status</td><td width='14%'>Action</td><td width='5%'>System<br>Reboot*</td></tr></thead>
<tbody id="pluginList"></tbody>
</table>