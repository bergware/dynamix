<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$start = 'Start Test';
$stop = 'Cancel Test';
$check = 'Checking...';

$port = &$disks[$name]['device'];
$spin = exec("hdparm -C /dev/$port | grep 'active'");
?>
<script>
var start = '<?=$start?>';
var stop = '<?=$stop?>';
var check = '<?=$check?>';
var refresh = null;

$(function(){
  $('#status').click(function(){
    $(this).val(check);
    $.ajax({
      url:'/plugins/dynamix/include/Run.php', data:'cmd=status&port=<?=$port?>', success:function(data){
        $('#status').fadeOut('slow').replaceWith('<div id="statusdata" style="display:none"></div>');
        $('#statusdata').html(data).fadeIn('slow');
      }
    });
  });
  $('#identity').click(function(){
    $(this).val(check);
    $.ajax({
      url:'/plugins/dynamix/include/Run.php', data:'cmd=identity&port=<?=$port?>', success:function(data){
        $('#identity').fadeOut('slow').replaceWith('<div id="identitydata" style="display:none"></div>');
        $('#identitydata').html(data).fadeIn('slow');
      }
    });
  });
  $('#attributes').click(function(){
    $(this).val(check);
    $.ajax( {
      url:'/plugins/dynamix/include/Run.php', data:'cmd=attributes&port=<?=$port?>', success:function(data){
        $('#attributes').fadeOut('slow').replaceWith('<div id="attributesdata" style="display:none"></div>');
        $('#attributesdata').html(data).fadeIn('slow');
      }
    });
  });
  $('#capabilities').click(function(){
    $(this).val(check);
    $.ajax({
      url:'/plugins/dynamix/include/Run.php', data:'cmd=capabilities&port=<?=$port?>', success:function(data){
        $('#capabilities').fadeOut('slow').replaceWith('<div id="capabilitiesdata" style="display:none"></div>');
        $('#capabilitiesdata').html(data).fadeIn('slow');
      }
    });
  });
  $('#selftest').click(function(){
    $(this).val(check);
    $.ajax({
      url:'/plugins/dynamix/include/Run.php', data:'cmd=selftest&port=<?=$port?>', success:function(data){
        $('#selftest').fadeOut('slow').replaceWith('<div id="selftestdata" style="display:none"></div>');
        $('#selftestdata').html(data).fadeIn('slow');
      }
    });
  });
  $('#errorlog').click(function(){
    $(this).val(check);
    $.ajax({
      url:'/plugins/dynamix/include/Run.php', data:'cmd=errorlog&port=<?=$port?>', success:function(data){
        $('#errorlog').fadeOut('slow').replaceWith('<div id="errorlogdata" style="display:none"></div>');
        $('#errorlogdata').html(data).fadeIn('slow');
      }
    });
  });
  $('#short').click(function(){
    if ($(this).val()==start){
      $(this).val(stop).attr('disabled',true);
      $('#long').attr('disabled',true);
      $.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=short&port=<?=$port?>'});
      refresh = setInterval(function(){
        $.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=update&port=<?=$port?>',success:function(data){$('#update').html(data).trigger('smart');}});
      }, 4000);
    } else {
      $(this).attr('disabled',true);
      $.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=stop&port=<?=$port?>'});
    }
  });
  $('#long').click(function(){
    if ($(this).val()==start){
      $(this).val(stop).attr('disabled',true);
      $('#short').attr('disabled',true);
      $.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=long&port=<?=$port?>'});
      refresh = setInterval(function(){
        $.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=update&port=<?=$port?>',success:function(data){$('#update').html(data).trigger('smart');}});
      }, 4000);
    } else {
      $(this).attr('disabled',true);
      $.ajax({url:'/plugins/dynamix/include/Run.php',data:'cmd=stop&port=<?=$port?>'});
    }
  });
  $('#update').bind('smart', function(){
    if ($(this).html().search('%')<0){
      $('#short').val(start).attr('disabled',false);
      $('#long').val(start).attr('disabled',false);
      clearInterval(refresh);
    } else {
      if ($('#short').val()==stop){$('#short').attr('disabled',false)} else {if (!$('#short').attr('disabled')) $('#short').val(stop)};
      if ($('#long').val()==stop){$('#long').attr('disabled',false)} else {if (!$('#long').attr('disabled')) $('#long').val(stop)};
    }
  });
  $('#update').load('/plugins/dynamix/include/Run.php', 'cmd=update&port=<?=$port?>');
});
</script>
<table class="settings">
<tr id="update"></tr>
<tr>
<td>SMART status:</td>
<td><input type="button" value="Check" id="status"></td>
</tr>
<tr>
<td>SMART short test:</td>
<td><? echo $spin ? "<input type='button' value='$start' id='short'>" : "<big>Disk must be spun up before running test</big>"; ?></td>
</tr>
<tr>
<td>SMART extended test:</td>
<td><? echo $spin ? "<input type='button' value='$start' id='long'>" : "<big>Disk must be spun up before running test</big>"; ?></td>
</tr>
<tr>
<td>Disk identity:</td>
<td><input type="button" value="Collect" id="identity"></td>
</tr>
<tr>
<td>Disk attributes:</td>
<td><input type="button" value="Collect" id="attributes"></td>
</tr>
<tr>
<td>Disk capabilities:</td>
<td><input type="button" value="Collect" id="capabilities"></td>
</tr>
<tr>
<td>Disk self-test log:</td>
<td><input type="button" value="Collect" id="selftest"></td>
</tr>
<tr>
<td>Disk error log:</td>
<td><input type="button" value="Collect" id="errorlog"></td>
</tr>
<tr>
<td></td>
<td><button type="button" onclick="done();">Done</button></td>
</tr>
</table>