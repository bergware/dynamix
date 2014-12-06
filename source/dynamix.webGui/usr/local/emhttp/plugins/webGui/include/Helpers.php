<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
// Helper functions
function my_scale($value, &$unit, $precision = NULL) {
  global $display;
  $scale = $display['scale'];
  $number = $display['number'];
  $dot = substr($number,0,1);
  $comma = substr($number,1,1);
  $units = array('B','KB','MB','GB','TB','PB');
  if ($scale==0 && !$precision) {
    $unit = '';
    return number_format($value, 0, $dot, ($value>=10000 ? $comma : ''));
  } else {
    $base = $value ? floor(log($value, 1000)) : 0;
    if ($scale>0 && $base>$scale) $base = $scale;
    $unit = $units[$base];
    $value = round($value/pow(1000, $base), $precision ? $precision : 2);
    return number_format($value, $precision ? $precision : (($value-intval($value)==0 || $value>=100) ? 0 : ($value>=10 ? 1 : 2)), $dot, ($value>=10000 ? $comma : ''));
  }
}
function my_number($value) {
  global $display;
  $number = $display['number'];
  $dot = substr($number,0,1);
  $comma = substr($number,1,1);
  return number_format($value, 0, $dot, ($value>=10000 ? $comma : ''));
}
function my_time($time, $fmt = NULL) {
  global $display;
  if (!$fmt) $fmt = $display['date'].($display['date']!='%c' ? ", {$display['time']}" : " %Z");
  return $time ? strftime($fmt, $time) : "unset";
}
function my_temp($value) {
  global $display;
  $unit = $display['unit'];
  $dot = substr($display['number'],0,1);
  return is_numeric($value) ? (($unit=='C' ? str_replace('.', $dot, $value) : round(9/5*$value+32))." &deg;$unit") : $value;
}
function my_disk($name) {
  return ucfirst(str_replace('disk','disk ',$name));
}
function my_word($num) {
  $words = array('zero','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen','twenty');
  return $num<count($words) ? $words[$num] : $num;
}
function plus($val, $word, $last) {
  return $val>0 ? (($val || $last) ? ($val.' '.$word.($val!=1?'s':'').($last ?'':', ')) : '') : '';
}
?>