<?PHP
/* Copyright 2013, Bergware International
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1], $_GET);
$name = $_GET['name'];
switch ($name) {
case 'crontab':
  $pid = exec("crontab -l | grep 'mdcmd'");
  break;
case 'preclear_disk':
  $pid = exec("ps -o pid,command --ppid 1 | awk -F/ '/$name .*{$_GET['device']}$/ {print $1}'");
  break;
case '21':
  $pid = exec("lsof -i:21 -n -P | awk '/\(LISTEN\)/ {print $2}'");
  break;
default:
  $pid = exec("pidof -s -x $name");
  break;
}
if (isset($_GET['update'])) {$span = ""; $_span = "";}
else {$span = "<span id='progress' class='status'>"; $_span = "</span>";}

echo $pid ? "{$span}Status:<span class='green'>Running</span>{$_span}" : "{$span}Status:<span class='orange'>Stopped</span>{$_span}";
?>