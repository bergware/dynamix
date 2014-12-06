<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<script>
function open_status() {
  $.ajax({url:'/plugins/webGui/include/DeviceList.php',data:{path:'<?=$path?>',device:'open',timer:timer},success:function(data) {
    if (data) $('#open_devices').html(data);
<?if (($display['refresh']>0 || ($display['refresh']<0 && $var['mdResync']==0)) && $var['fsState']=='Started'):?>
    var x = typeof(cache_status)=='function' ? 4 : 3;
    if ($('#tab'+x).is(':checked')) timer = setTimeout(open_status,<?=abs($display['refresh'])?>);
<?endif;?>
  }});
}
open_status();
<?if (($display['refresh']>0 || ($display['refresh']<0 && $var['mdResync']==0)) && $var['fsState']=='Started'):?>
var x = typeof(cache_status)=='function' ? 4 : 3;
$('#tab'+x).bind({click:function() {clearTimeout(timer); open_status();}});
<?endif;?>
</script>
<table class="disk_status <?=($var['fsState']=='Stopped'?'small':$display['view']).' '.$display['align']?>">
<thead><tr><td>Device</td><td>Identification</td><td>Temp.</td><td>Size</td><td>Used</td><td>Free</td><td>Reads</td><td>Writes</td><td>Errors</td><td>View</td></tr></thead>
<tbody id="open_devices"></tbody>
</table>