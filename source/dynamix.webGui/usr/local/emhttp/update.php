<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1], $_GET);
$plugin = isset($_GET['#plugin']) ? $_GET['#plugin'] : "plugins";
$folder = isset($_GET['#plugin']) ? strtok($plugin,'.') : "dynamix";
$section = isset($_GET['#section']) ? $_GET['#section'] : "";
$cleanup = isset($_GET['#cleanup']);

$cfg = "boot/config/plugins/$folder/$plugin.cfg";

$keys = parse_ini_file($cfg,$section);
$save = true;

if (isset($_GET['#include'])) include $_GET['#include'];
if ($save) {
  $rom = "";
  if ($section) {
    foreach ($_GET as $key => $value) if (substr($key,0,1)!='#') $keys[$section][$key] = $value;
    foreach ($keys as $section => $block) {
      $rom .= "[$section]\n";
      foreach ($block as $key => $value) if (strlen($value) || !$cleanup) $rom .= "$key=\"$value\"\n";
    }
  } else {
    foreach ($_GET as $key => $value) if (substr($key,0,1)!='#') $keys[$key] = $value;
    foreach ($keys as $key => $value) if (strlen($value) || !$cleanup) $rom .= "$key=\"$value\"\n";
  }
  file_put_contents($cfg,$rom);
}
?>
<html>
<head><script>var goback=parent.location;</script></head>
<body onLoad="parent.location=goback;"></body>
</html>