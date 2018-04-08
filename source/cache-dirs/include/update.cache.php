<?PHP
/* Copyright 2015, Bergware International.
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
$new = isset($default) ? array_replace_recursive($_POST, $default) : $_POST;
foreach ($new as $key => $value) {
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
  case 'adaptive':
    $adaptive = $value;
    break;
  case 'depth':
    $depth = $value;
    break;
  case 'exclude':
  case 'include':
    $list = explode(',', $value);
    foreach ($list as $insert) $options .= "-{$prefix[$key]} \"".str_replace(' ','\ ',trim($insert))."\" ";
    break;
  default:
    if ($key[0]!='#') $options .= (isset($prefix[$key]) ? "-{$prefix[$key]} " : "")."$value ";
    break;
  }
}
$cachedirs = "/usr/local/emhttp/plugins/dynamix.cache.dirs/scripts/rc.cachedirs";
exec("$cachedirs stop >/dev/null");
if ($adaptive == 1) {
  if ($depth > 0) $options .= "-d ".$depth;
} else {
  # Fixed specified so must set a value
  if ($depth == 0) {
    $depth=9999;
  }
  $options .= "-D ".$depth;
} 
$options = trim($options);
$keys['options'] = $options;
file_put_contents($config, $options);
if ($enable) exec("$cachedirs start >/dev/null");
?>
