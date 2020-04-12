<?PHP
/* Copyright 2012-2020, Bergware International.
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
$plugin = 'dynamix.s3.sleep';
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$translations = file_exists("$docroot/webGui/include/Translations.php");
require_once "$docroot/plugins/$plugin/include/Legacy.php";
?>
<?if (!$translations):?>
<?eval('?>'.parse_file("$docroot/plugins/$plugin/Sleep.php"))?>
<?else:?>
<script>
function sleepNow() {
  $('#sleepbutton').val('_(Sleeping)_...');
  if (typeof showNotice == 'function') showNotice('_(System in sleep mode)_');
  for (var i=0,element; element=document.querySelectorAll('input,button,select')[i]; i++) { element.disabled = true; }
  for (var i=0,link; link=document.getElementsByTagName('a')[i]; i++) { link.style.color = "gray"; } //fake disable
  $.get('/plugins/<?=$plugin?>/include/SleepMode.php',function(){location=location;});
}
function sleepS3() {
  if (<?=$confirm['sleep'] ? 'true' : 'false'?>) {
    swal({title:'_(Proceed)_?',text:'_(This will put the system in sleep mode)_',type:'warning',showCancelButton:true,confirmButtonText:'_(Proceed)_',cancelButtonText:'_(Cancel)_'},function(){sleepNow();});
  } else {
    sleepNow();
  }
}
</script>

<table class="array_status" style="margin-top:0">
<tr><td></td>
<td><input type="button" value="Sleep" onclick="sleepS3()"></td>
<td>_(<b>Sleep</b> will immediately put the server in sleep mode)_.<br>
_(Make sure your server supports S3 sleep)_. _(Check this)_ <u><a href="http://lime-technology.com/wiki/index.php?title=Setup Sleep (S3) and Wake on Lan (WOL)" target="_blank">_(wiki entry)_</a></u> _(for more information)_.<br>
</td></tr>
</table>
<?endif;?>
