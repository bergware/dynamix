<?PHP
/* Copyright 2015, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * 2013/12/29 SlrG added feature to include/exclude drives outside of array
 */
?>

<?
if (isset ($_POST['excludeList'])) {
  $excludeList = $_POST['excludeList'];
  $excludeString = implode (",",$excludeList);
}
$new = isset($default) ? array_replace_recursive($_POST, $default) : $_POST;
foreach ($new as $key => $value) {
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
    $_POST[$key] = $value;
  break;
  case 'preRun':
    file_put_contents($preRun, "#!/bin/bash\n".str_replace("\r","",$value));
    exec("chmod +x $preRun");
    $_POST[$key] = urlencode($value);
    $options .= "-b $preRun ";
  break;
  case 'postRun':
    file_put_contents($postRun, "#!/bin/bash\n".str_replace("\r","",$value));
    exec("chmod +x $postRun");
    $_POST[$key] = urlencode($value);
    $options .= "-p $postRun ";
  break;
  case 'stopDay':
  case 'stopHour':
  case 'pingIP':
  case 'outside':
    $list = explode(',', $value);
    foreach ($list as $insert) $options .= "-{$prefix[$key]} $insert ";
  break;
  default:
    if ($key[0]!='#') $options .= (isset($prefix[$key]) ? "-{$prefix[$key]} " : "")."$value ";
  break;}
}
$s3sleep = "/usr/local/emhttp/plugins/dynamix.s3.sleep/scripts/rc.s3sleep";
exec("$s3sleep stop >/dev/null");
$options = trim($options);
$keys['options'] = $options;
file_put_contents($config, $options);
if ($enable) exec("$s3sleep start >/dev/null");
?>
