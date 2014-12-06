<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$memory = '/tmp/memory.tmp';
$crontab = '/tmp/crontab.tmp';
$cron = '';
if (isset($_GET['#apply'])) {
  exec("crontab -l | grep -v 'Scheduled Parity Check' | grep -v '/root/mdcmd check' >$crontab");
  if ($_GET['mode']>0) {
    $hour = isset($_GET['hour']) ? $_GET['hour'] : '* *';
    $dotm = isset($_GET['dotm']) ? $_GET['dotm'] : '*';
    switch ($dotm) {
    case '28-31':
      $term = '[ $(date +%d -d tomorrow) -eq 1 ] && ';
      break;
    case 'W1':
      $dotm = '*';
    $term = '[ $(date +%d) -le 7 ] && ';
    break;
    case 'W2':
      $dotm = '*';
      $term = '[ $(date +%d) -ge 8 -a $(date +%d) -le 14 ] && ';
      break;
    case 'W3':
      $dotm = '*';
      $term = '[ $(date +%d) -ge 15 -a $(date +%d) -le 21 ] && ';
      break;
    case 'W4':
      $dotm = '*';
      $term = '[ $(date +%d) -ge 22 -a $(date +%d) -le 28 ] && ';
      break;
    case 'WL':
      $dotm = '*';
      $term = '[ $(date +%d -d +7days) -le 7 ] && ';
      break;
    default:
      $term = '';
    }
    $month = isset($_GET['month']) ? $_GET['month'] : '*';
    $day = isset($_GET['day']) ? $_GET['day'] : '*';
    $write = isset($_GET['write']) ? $_GET['write'] : '';
    $cron = "'$hour $dotm $month $day $term/root/mdcmd check $write 1>/dev/null 2>&1'";
    exec("echo '# Scheduled Parity Check' >>$crontab");
    exec("echo $cron >>$crontab");
  }
  $keys[$section]['cron'] = $cron;
  exec("crontab $crontab");
  exec("rm -f $crontab $memory");
} else {
  file_put_contents($memory, $argv[1]);
  $save = false;
}
?>