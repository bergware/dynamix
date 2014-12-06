<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
function day_count($time) {
  global $var;
  $days = floor($var['currTime']/86400)-floor($time/86400);
  switch (true) {
  case ($days<0):
    return "";
  case ($days==0):
    return " (today)";
  case ($days==1):
    return " (yesterday)";
  case ($days<=31):
    return " (".my_word($days)." days ago)";
  case ($days<=61):
    return " <span class='orange-text'>($days days ago)</span>";
  case ($days>61):
    return " <span class='red-text'>($days days ago)</span>";
  }
}
function my_check($time) {
  global $disks;
  if (!$time) return "unavailable (system reboot or log rotation)";
  $days = floor($time/86400);
  $hmss = $time-$days*86400;
  $hour = floor($hmss/3600);
  $mins = $hmss/60%60;
  $secs = $hmss%60;
  return plus($days,'day',($hour|$mins|$secs)==0).plus($hour,'hour',($mins|$secs)==0).plus($mins,'minute',$secs==0).plus($secs,'second',true).". Average speed: ".(isset($disks['parity']['sizeSb'])?my_scale($disks['parity']['sizeSb']*1024/$time,$unit,1)." $unit/sec":"unknown");
}
function my_error($code) {
  switch ($code) {
  case -4:
    return "<em>user abort</em>";
  default:
    return "<strong>$code</strong>";
  }
}
?>