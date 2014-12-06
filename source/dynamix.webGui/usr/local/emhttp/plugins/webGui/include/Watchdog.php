<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$var = parse_ini_file("state/var.ini");

switch ($var['fsState']) {
case 'Stopped':
  echo '<span class="red"><strong>Array Stopped</strong></span>'; break;
case 'Starting':
  echo '<span class="orange"><strong>Array Starting</strong></span>'; break;
default:
  echo '<span class="green"><strong>Array Started</strong></span>'; break;
}
if ($var['mdResync']) {
  parse_str($argv[1],$_GET);
  echo '&bullet;<span class="orange"><strong>'.($var['mdNumInvalid']==0 ? 'Parity-Check:' : ($var['mdInvalidDisk']==0 ? 'Parity-Sync:' : 'Data-Rebuild:')).' '.number_format(($var['mdResyncPos']/($var['mdResync']/100+1)),1,$_GET['dot'],'').' %</strong></span>';
  if ($_GET['mode']<0) echo '@stop';
}
?>