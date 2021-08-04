<?PHP
/* Copyright 2012-2020, Bergware International.
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
$disk = 'disk'.$_POST['disk'];
$filter = "bunker -{$_POST['cmd']}.*(\/mnt\/$disk ?|\/$disk\.export\.hash$)";
$pid = exec("ps -eo pid,comm,args|awk '$2==\"bunker\" && $0~/$filter/{print $1}'");
if ($_POST['kill']=='true') {
  exec("pgrep -P $pid 2>/dev/null", $cpids);
  exec("kill $pid 2>/dev/null");
  foreach ($cpids as $cpid) exec("kill $cpid 2>/dev/null");
  usleep(100000);
  unlink("/var/tmp/$disk.tmp.end");
} else {
  echo $pid;
}
?>
