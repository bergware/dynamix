<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 * 2013/12/29 SlrG added feature to include/exclude drives outside of array
 */
?>
<?
if (isset ($_GET['excludeList'])) {
  $excludeList = $_GET['excludeList'];
  $excludeString = implode (",",$excludeList);
}
foreach ($_GET as $key => $value) {
  if ("$key" == "excludeList") $value = $excludeString;
  if (!strlen($value)) continue;
  switch ($key) {
  case '#config':
    $config = $value;
    $options = '';
    break;
  case '#folder':
    $preRun = "$value/preRun";
    $postRun = "$value/postRun";
    exec("rm -f $preRun");
    exec("rm -f $postRun");
    break;
  case '#prefix':
    parse_str($value, $prefix);
    break;
  case 'service':
    $enable = ($value != '0');
    if ($enable) $options .= "-C $value ";
    break;
  case 'exclude':
    $options .= "$value ";
    break;
  case 'excludeList':
    $options .= "$value ";
    $_GET[$key] = $value;
    break;
  case 'preRun':
    file_put_contents($preRun, "#!/bin/bash\n".str_replace("\r","",$value));
    exec("chmod u+x $preRun");
    $_GET[$key] = urlencode($value);
    $options .= " -b $preRun";
    break;
  case 'postRun':
    file_put_contents($postRun, "#!/bin/bash\n".str_replace("\r","",$value));
    exec("chmod u+x $postRun");
    $_GET[$key] = urlencode($value);
    $options .= " -p $postRun";
    break;
  case 'stopDay':
  case 'stopHour':
  case 'pingIP':
    $list = explode(',', $value);
    foreach ($list as $insert) $options .= "-{$prefix[$key]} $insert ";
    break;
  default:
    if (substr($key,0,1)!='#') $options .= (isset($prefix[$key]) ? "-{$prefix[$key]} " : "")."$value ";
    break;
  }
}
exec("/etc/rc.d/rc.s3sleep stop >/dev/null");
$options = trim($options);
$keys['options'] = $options;
file_put_contents($config, $options);
if ($enable) exec("/etc/rc.d/rc.s3sleep start >/dev/null");
?>