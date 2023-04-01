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
$plugin = 'dynamix.active.streams';
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// add translations
$_SERVER['REQUEST_URI'] = '';
require_once "$docroot/webGui/include/Translations.php";
require_once "$docroot/webGui/include/Wrappers.php";

$plex   = $_GET['plex']??'';
$filter = $plex ? "^(smbd|$plex)" : "^smbd";
$cfg    = parse_plugin_cfg('dynamix.active.streams');
$online = [];

exec("lsof -OwlnPi -sTCP:ESTABLISHED 2>/dev/null|awk 'NR>1 && \$1~/$filter/{print substr(\$9,index(\$9,\"->\")+2)}'",$online);

foreach ($online as $host) {
  if ($host[0]=='[') {
    $ip = substr($host,1,strpos($host,']')-1);
  } else {
    $ip = str_replace('.','_',substr($host,0,strpos($host,':')));
  }
  if (!isset($cfg[$ip])) $cfg[$ip] = "";
}
ksort($cfg);
foreach ($cfg as $ip => $name) {
  echo "<tr><td style='font-weight:normal'>".str_replace('_','.',$ip)."</td><td><input type='text' name='$ip' value=\"$name\"></td></tr>";
}
echo '<tr><td></td><td><input type="submit" name="#apply" value="'._('Apply').'"><input type="button" value="'._('Done').'" onclick="done_plus($(\'#tab1\'))"></td></tr>';
?>
