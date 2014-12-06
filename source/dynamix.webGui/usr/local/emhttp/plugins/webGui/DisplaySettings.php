<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<script>
$(function() {
  presetTime(document.display_settings);
  presetSnow(document.display_settings);
});
function prepareDisplay(form) {
  if (!form.number.value) form.number.value='.,';
  if (form.unit.selectedIndex==1) {
    form.hot.value = Math.round(5/9*(form.hot.value-32));
    form.max.value = Math.round(5/9*(form.max.value-32));
  }
}
function presetTime(form) {
  form.time.disabled = form.date.value=='%c';
}
function presetSnow(form) {
  var plain = form.banner.value=='';
  if (plain) form.snow.value = 0;
  form.snow.disabled = plain;
}
function presetTemp(form) {
  var hot = document.getElementById('hot').innerHTML;
  var max = document.getElementById('max').innerHTML;
  if (form.unit.selectedIndex==0) {;
    form.hot.value = Math.round(5/9*(form.hot.value-32));
    form.max.value = Math.round(5/9*(form.max.value-32));
	document.getElementById('hot').innerHTML = hot.replace('F)','C)');
	document.getElementById('max').innerHTML = max.replace('F)','C)');
  } else {
    form.hot.value = Math.round((9/5*form.hot.value)+32);
    form.max.value = Math.round((9/5*form.max.value)+32);
	document.getElementById('hot').innerHTML = hot.replace('C)','F)');
	document.getElementById('max').innerHTML = max.replace('C)','F)');
  }
}
function resetDisplay(form) {
  form.tabs.selectedIndex = 0;
  form.date.selectedIndex = 0;
  form.time.selectedIndex = 1;
  form.time.disabled = true;
  form.number.selectedIndex = 0;
  form.unit.selectedIndex = 0;
  form.scale.selectedIndex = 0;
  form.align.selectedIndex = 2;
  form.view.selectedIndex = 0;
  form.blink.selectedIndex = 1;
  form.devices.selectedIndex = 1;
  form.total.selectedIndex = 1;
  form.spin.selectedIndex = 1;
  form.size.selectedIndex = 1;
  form.snow.selectedIndex = 0;
  form.snow.disabled = true;
<?if (file_exists("/var/log/plugins/dynamix.s3.sleep")):?>
  form.button.selectedIndex = 1;
<?endif;?>
  form.banner.selectedIndex = 0;
  form.icons.selectedIndex = 0;
  form.refresh.selectedIndex = 0;
}
</script>
<form name="display_settings" method="POST" action="/update.php" target="progressFrame" onsubmit="prepareDisplay(this)">
<input type="hidden" name="#plugin" value="dynamix.webGui">
<input type="hidden" name="#section" value="display">
<table class="settings">
  <tr>
  <td>Date format:</td>
  <td><select name="date" size="1" onchange="presetTime(this.form);">
<?=mk_option($display['date'], "%c", "System Setting")?>
<?=mk_option($display['date'], "%A, %Y %B %e", "Day, YYYY Month D")?>
<?=mk_option($display['date'], "%A, %e %B %Y", "Day, D Month YYYY")?>
<?=mk_option($display['date'], "%A, %B %e, %Y", "Day, Month D, YYYY")?>
<?=mk_option($display['date'], "%A, %m/%d/%Y", "Day, MM/DD/YYYY")?>
<?=mk_option($display['date'], "%A, %d-%m-%Y", "Day, DD-MM-YYYY")?>
<?=mk_option($display['date'], "%A, %d.%m.%Y", "Day, DD.MM.YYYY")?>
<?=mk_option($display['date'], "%A, %Y-%m-%d", "Day, YYYY-MM-DD")?>
  </select></td>
  </tr>
  <tr>
  <td>Time format:</td>
  <td><select name="time" size="1">
<?=mk_option($display['time'], "%I:%M %p", "12 hours")?>
<?=mk_option($display['time'], "%R", "24 hours")?>
  </select></td>
  </tr>
  <tr>
  <td>Number format:</td>
  <td><select name="number" size="1">
<?=mk_option($display['number'], ".,", "American")?>
<?=mk_option($display['number'], ",.", "European")?>
  </select></td>
  </tr>
  <tr>
  <td>Temperature unit:</td>
  <td><select name="unit" size="1" onchange="presetTemp(this.form);">
<?=mk_option($display['unit'], "C", "Celsius")?>
<?=mk_option($display['unit'], "F", "Fahrenheit")?>
  </select></td>
  </tr>
  <tr>
  <td>Number scaling:</td>
  <td><select name="scale" size="1">
<?=mk_option($display['scale'], "-1", "Automatic")?>
<?=mk_option($display['scale'], "0", "Disabled")?>
<?=mk_option($display['scale'], "1", "KB")?>
<?=mk_option($display['scale'], "2", "MB")?>
<?=mk_option($display['scale'], "3", "GB")?>
<?=mk_option($display['scale'], "4", "TB")?>
<?=mk_option($display['scale'], "5", "PB")?>
  </select></td>
  </tr>
  <tr>
  <td>Number alignment:</td>
  <td><select name="align" size="1">
<?=mk_option($display['align'], "left", "Left")?>
<?=mk_option($display['align'], "center", "Center")?>
<?=mk_option($display['align'], "right", "Right")?>
  </select></td>
  </tr>
  <tr>
  <td>Used / Free columns:</td>
  <td><select name="text" size="1">
<?=mk_option($display['text'], "1", "Text")?>
<?=mk_option($display['text'], "0", "Graphic")?>
  </select></td>
  </tr>
  <tr>
  <td>Table view spacing:</td>
  <td><select name="view" size="1">
<?=mk_option($display['view'], "", "Normal")?>
<?=mk_option($display['view'], "small", "Narrow")?>
<?=mk_option($display['view'], "wide", "Wide")?>
  </select></td>
  </tr>
  <tr>
  <td>Display array totals:</td>
  <td><select name="total" size="1">
<?=mk_option($display['total'], "0", "No")?>
<?=mk_option($display['total'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Allow individual disk spin up/down:</td>
  <td><select name="spin" size="1">
<?=mk_option($display['spin'], "0", "No")?>
<?=mk_option($display['spin'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Show settings page as tabbed view:</td>
  <td><select name="tabs" size="1">
<?=mk_option($display['tabs'], "0", "No")?>
<?=mk_option($display['tabs'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Show array utilization indicator:</td>
  <td><select name="usage" size="1">
<?=mk_option($display['usage'], "0", "No")?>
<?=mk_option($display['usage'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Show section icons:</td>
  <td><select name="icons" size="1">
<?=mk_option($display['icons'], "", "No")?>
<?=mk_option($display['icons'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Show banner image:</td>
  <td><select name="banner" size="1" onchange="presetSnow(this.form);">
<?=mk_option($display['banner'], "", "No")?>
<?=mk_option($display['banner'], "image", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Enable snow effect:</td>
  <td><select name="snow" size="1">
<?=mk_option($display['snow'], "0", "No")?>
<?=mk_option($display['snow'], "1", "Yes")?>
  </select></td>
  </tr>
  <tr>
  <td>Dynamix color theme:</td>
  <td><select name="theme" size="1">
<?=mk_option($display['theme'], "white", "White")?>
<?=mk_option($display['theme'], "black", "Black")?>
  </select></td>
  </tr>
  <tr>
  <td id="hot">Warning disk temperature threshold (&deg;<?=$display['unit']?>):</td>
  <td><input type="text" name="hot" class="narrow" maxlength="3" value="<?=$display['unit']=='C'?$display['hot']:round(9/5*$display['hot']+32)?>"></td>
  </tr>
  <tr>
  <td id="max">Critical disk temperature threshold (&deg;<?=$display['unit']?>):</td>
  <td><input type="text" name="max" class="narrow" maxlength="3" value="<?=$display['unit']=='C'?$display['max']:round(9/5*$display['max']+32)?>"></td>
  </tr>
  <tr>
  <td>Real-time page updates:</td>
  <td><select name="refresh" size="1">
<?=mk_option($display['refresh'], "0", "Disabled")?>
<?=mk_option($display['refresh'], "1000", "Active")?>
<?=mk_option($display['refresh'], "-1000", "Passive")?>
  </select></td>
  </tr>
  <tr>
  <td><button type="button" onclick="resetDisplay(this.form);">Default</button></td>
  <td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>