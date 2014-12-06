<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
foreach ($_GET as $key => $value) {
  if (!strlen($value)) continue;
  switch ($key) {
  case '#config':
    $config = $value;
    $options = '';
    break;
  case '#prefix':
    parse_str($value, $prefix);
    break;
  case 'service':
    $enable = $value;
    break;
  case 'exclude':
  case 'include':
    $list = explode(',', $value);
    foreach ($list as $insert) $options .= "-{$prefix[$key]} \"".str_replace(' ','\ ',trim($insert))."\" ";
    break;
  default:
    if (substr($key,0,1)!='#') $options .= (isset($prefix[$key]) ? "-{$prefix[$key]} " : "")."$value ";
    break;
  }
}
exec("/etc/rc.d/rc.cachedirs stop >/dev/null");
$options = trim($options);
$keys['options'] = $options;
file_put_contents($config, $options);
if ($enable) exec("/etc/rc.d/rc.cachedirs start >/dev/null");
?>