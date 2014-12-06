<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
function PsExecute($command, $timeout = 20, $sleep = 2) {
  exec($command.'>/dev/null & echo $!',$op);
  $pid = (int)$op[0];
  $timer = 0;
  while ($timer<$timeout) {
    sleep($sleep);
    $timer += $sleep;
    if (PsEnded($pid)) return true;
  }
  PsKill($pid);
  return false;
}
function PsEnded($pid) {
  exec("ps -eo pid|grep $pid",$output);
  foreach ($output as $list) if (trim($list)==$pid) return false;
  return true;
}
function PsKill($pid) {
  exec("kill -9 $pid");
}
preg_match_all("/root=(.*)\n/", file_get_contents("/etc/ssmtp_config.conf"),$mail);
$smtp = '/usr/sbin/ssmtp';
$mail = trim($mail[1][0]);
$text = "From: $mail
To: $mail
Subject: unRAID SMTP Test

Dynamix SSMTP test message has arrived!
";
$success = PsExecute("echo ".escapeshellarg($text)."|$smtp $mail");
echo "Test Result:<span class=".($success ? "'green'>Mail sent</span>" : "'red'>Failed</span>")."<br>Click <strong>Log</strong> button to view test result information.";
?>