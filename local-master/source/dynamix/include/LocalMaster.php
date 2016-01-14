<?PHP
/* Copyright 2015, Bergware International.
 * Copyright 2015, Lime Technology
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
require_once('Wrappers.php');

if (isset($_GET['monitor'])) {
  $file = '/boot/config/plugins/dynamix/dynamix.cfg';
  $cfg = parse_plugin_cfg('dynamix', true);
  $cfg['display']['monitor'] = $_GET['monitor'];
  $text = "";
  foreach ($cfg as $section => $keys) {
    $text .= "[$section]\n";
    foreach ($keys as $key => $value) $text .= "$key=\"$value\"\n";
  }
  @mkdir(dirname($file));
  file_put_contents($file, $text);
  $cron = $_GET['monitor'] ? "# Generated local master browser check:\n*/1 * * * * /usr/local/emhttp/plugins/dynamix/scripts/localmaster &> /dev/null\n\n" : "";
  parse_cron_cfg('dynamix', 'localmaster', $cron);
  exit;
}
if (isset($_GET['smb'])) {
  $lmb = exec("nmblookup -M -- - 2>/dev/null|grep -Pom1 '^\S+'");
  $tag = exec("nmblookup -A $lmb 2>/dev/null|grep -Pom1 '^\s+\K\S+'");
  echo "<img src='/webGui/icons/localmaster.png' class='icon'>$tag is current local master browser";
} else
  @readfile("/var/local/emhttp/localmaster.htm");
?>