<?
/* Copyright 2012-2018, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * Plugin development contribution by gfjardim
 */
?>
<?
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

$plugin  = 'dynamix.system.temp';
$autofan = "dynamix.system.autofan";
$script  = "$docroot/plugins/$autofan/scripts/rc.autofan";

function my_temp($val, $unit, $dot) {
  return ($val ? ($unit=='F' ? round(9/5*$val+32) : str_replace('.',$dot,$val)) : '##')."&thinsp;".$unit;
}
function my_rpm($val){
  return ($val ?: '##')."&thinsp;rpm";
}
function get_autofan() {
  global $script;
  if (is_executable($script)) {
    $p = trim(shell_exec("$script speed"));
    return is_numeric($p) ? " (${p}%)" : "";
  }
  return "";
}

$set = [];
$val = explode(' ',exec("sensors -A|awk 'BEGIN{cpu=\"-\";mb=\"-\";fan=\"-\"}{if (/^CPU Temp/) cpu=$3*1; if (/^MB Temp/) mb=$3*1; if (/^Array Fan/) fan=$3*1} END{print cpu,mb,fan}'"));

if ($val[0]!='-') $set[] = "<img src='/plugins/$plugin/icons/cpu.png' title='Processor' style='margin:0 6px 0 12px'>".my_temp($val[0], $_POST['unit'], $_POST['dot']);
if ($val[1]!='-') $set[] = "<img src='/plugins/$plugin/icons/mb.png' title='Mainboard' style='margin:0 6px 0 12px'>".my_temp($val[1], $_POST['unit'], $_POST['dot']);
if ($val[2]!='-') $set[] = "<img src='/plugins/$plugin/icons/fan.png' title='Array fan' style='margin:0 6px 0 12px'>".my_rpm($val[2]).get_autofan();
if ($set) echo "<span id='temp' style='margin-right:24px'>".implode($set)."</span>";
?>