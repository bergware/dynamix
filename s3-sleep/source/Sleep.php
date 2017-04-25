<?PHP
/* Copyright 2016, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<script>
function sleepNow() {
  $('#sleepbutton').val('Sleeping...');
  if (typeof showNotice == 'function') showNotice('System in sleep mode');
  for (var i=0,element; element=document.querySelectorAll('input,button,select')[i]; i++) { element.disabled = true; }
  for (var i=0,link; link=document.getElementsByTagName('a')[i]; i++) { link.style.color = "gray"; } //fake disable
  $.get('/plugins/dynamix.s3.sleep/include/SleepMode.php',function(){location=location;});
}
function sleepS3() {
  if (<?=$confirm['sleep'] ? 'true' : 'false'?>) {
    swal({title:'Proceed?',text:'This will put the system in sleep mode',type:'warning',showCancelButton:true},function(){sleepNow();});
  } else {
    sleepNow();
  }
}
</script>

<table class="array_status" style="margin-top:0">
<tr><td></td>
<td><input type="button" value="Sleep" onclick="sleepS3()"></td>
<td><strong>Sleep</strong> will immediately put the server in sleep mode.<br>
Make sure your server supports S3 sleep. Check this <u><a href="http://lime-technology.com/wiki/index.php?title=Setup_Sleep_(S3)_and_Wake_on_Lan_(WOL)" target="_blank">wiki entry</a></u> for more information.<br>
</td></tr>
</table>