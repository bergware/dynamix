<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<table class="array_status" style="margin-top:0">
<tr>
<td></td>
<td><input type="button" id="sleep" value="Sleep"<?=$confirm['sleep']?' disabled':''?> onclick="disableInput();"></td>
<td><strong>Sleep</strong> will immediately put the server in sleep mode.<br>
Make sure your server supports S3 sleep. Check this <u><a href="http://lime-technology.com/wiki/index.php?title=Setup_Sleep_(S3)_and_Wake_on_Lan_(WOL)" target="_blank">wiki entry</a></u> for more information.<br>
<?if ($confirm['sleep']):?>
<input type="checkbox" id="confirmSleep" value="OFF" onclick="activateSleep()"><small>Yes I want to do this</small>
<?endif;?>
</td>
</tr>
</table>
<script>
$('#sleep').click(function() {
  $(this).val('Sleeping...').attr('disabled',false);
  $.ajax({url:"/plugins/dynamix/include/SleepMode.php",success:function(){window.location.reload(true);}});
});
<?if ($confirm['sleep']):?>
function activateSleep() {
  $('#sleep').attr('disabled',!$('#confirmSleep').prop('checked'));
}
<?endif;?>
</script>