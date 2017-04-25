<?
/* Copyright 2015, Bergware International.
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
$plugin  = 'dynamix.system.temp';
$autofan = "dynamix.system.autofan";
$script  = "/usr/local/emhttp/plugins/${autofan}/scripts/rc.autofan";

function my_temp($val, $unit, $dot) {
  return ($val>0 ? ($unit=='F' ? round(9/5*$val+32) : str_replace('.',$dot,$val)) : '##')."&thinsp;$unit";
}
function my_rpm($val){
  return ($val>0 ? $val : '##')."&thinsp;rpm";
}
function get_autofan() {
  global $script;
  if (is_executable($script)) {
    $p = trim(shell_exec("$script speed"));
    return is_numeric($p) ? " (${p}%)" : "";
  }
  return "";
}

$set = array();
$val = explode(' ',exec("sensors -A|awk 'BEGIN{cpu=\"-\";mb=\"-\";fan=\"-\"}{if (/^CPU Temp/) cpu=$3*1; if (/^MB Temp/) mb=$3*1; if (/^Array Fan/) fan=$3*1} END{print cpu,mb,fan}'"));

if ($val[0]!='-') $set[] = "<img src='/plugins/$plugin/icons/cpu.png' title='Processor' class='icon'>".my_temp($val[0], $_GET['unit'], $_GET['dot']);
if ($val[1]!='-') $set[] = "<img src='/plugins/$plugin/icons/mb.png' title='Mainboard' class='icon'>".my_temp($val[1], $_GET['unit'], $_GET['dot']);
if ($val[2]!='-') $set[] = "<img src='/plugins/$plugin/icons/fan.png' title='Array fan' class='icon'>".my_rpm($val[2]).get_autofan();
if ($set) echo "<span id='temps' style='margin-right:16px'>".implode('&nbsp;', $set)."</span>";
?>