<?PHP
/* Copyright 2012-2023, Bergware International.
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
$plugin = 'dynamix.local.master';
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// add translations
$_SERVER['REQUEST_URI'] = 'smb';
require_once "$docroot/webGui/include/Translations.php";
require_once "$docroot/webGui/include/Wrappers.php";

if (isset($_GET['monitor'])) {
  $monitor = $_GET['monitor'];
  $file = '/boot/config/plugins/dynamix/dynamix.cfg';
  $cfg = parse_plugin_cfg('dynamix', true);
  $cfg['display']['monitor'] = $monitor;
  $text = "";
  foreach ($cfg as $section => $keys) {
    $text .= "[$section]\n";
    foreach ($keys as $key => $value) $text .= "$key=\"$value\"\n";
  }
  @mkdir(dirname($file));
  file_put_contents($file, $text);
  $cron = $monitor==1 ? "# Generated local master browser check:\n*/1 * * * * $docroot/plugins/dynamix.local.master/scripts/localmaster &> /dev/null\n\n" : "";
  parse_cron_cfg('dynamix.local.master', 'localmaster', $cron);
  exit;
}
if (isset($_GET['smb'])) {
  if ($master = exec("nmblookup -M -- - 2>/dev/null|grep -Pom1 '^(?:[0-9]{1,3}\.){3}[0-9]{1,3}'")) {
    $tag = exec("nmblookup -A $master 2>/dev/null|grep -Pom1 '^\s+\K\S+'") ?: $master;
    echo "<img src='/plugins/dynamix.local.master/icons/localmaster.png' class='icon'>$tag "._('is current local master browser');
  }
} else
  @readfile("/var/local/emhttp/localmaster.htm");
?>