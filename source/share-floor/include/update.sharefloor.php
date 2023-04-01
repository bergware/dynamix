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
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "$docroot/webGui/include/Wrappers.php";

if (($_POST['mode']??0)>0) {
  $hour = isset($_POST['hour']) ? $_POST['hour'] : '*';
  $min  = isset($_POST['min'])  ? $_POST['min']  : '*';
  $dotm = isset($_POST['dotm']) ? $_POST['dotm'] : '*';
  $day  = isset($_POST['day'])  ? $_POST['day']  : '*';
  $cron = "# Generated share floor schedule:\n$min $hour $dotm * $day $docroot/plugins/dynamix.share.floor/scripts/share_floor &> /dev/null\n\n";
} else {
  $cron = "";
}
parse_cron_cfg('dynamix.share.floor', 'share-floor', $cron);
?>
