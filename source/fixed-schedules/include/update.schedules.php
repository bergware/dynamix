<?PHP
/* Copyright 2015, Bergware International.
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
$tmp = "/tmp/cron.tmp";
exec("crontab -l > $tmp");
$cron = explode("\n", file_get_contents($tmp));
$new = isset($default) ? array_replace_recursive($_POST, $default) : $_POST;
$i = 0;
foreach ($cron as $line) {
  if (strpos($line, 'cron.hourly')) {
    $hourly = explode(' ', $line);
    $hourly[0] = $new['hourlyMM'];
    $cron[$i] = implode(' ', $hourly);
  } else if (strpos($line, 'cron.daily')) {
    $daily = explode(' ', $line);
    $daily[0] = $new['dailyMM'];
    $daily[1] = $new['dailyHH'];
    $cron[$i] = implode(' ', $daily);
  } else if (strpos($line, 'cron.weekly')) {
    $weekly = explode(' ', $line);
    $weekly[0] = $new['weeklyMM'];
    $weekly[1] = $new['weeklyHH'];
    $weekly[4] = $new['weeklyDD'];
    $cron[$i] = implode(' ', $weekly);
  } else if (strpos($line, 'cron.monthly')) {
    $monthly = explode(' ', $line);
    $monthly[0] = $new['monthlyMM'];
    $monthly[1] = $new['monthlyHH'];
    $monthly[2] = $new['monthlyDD'];
    $cron[$i] = implode(' ', $monthly);
  }
  $i++;
}
file_put_contents($tmp, implode("\n", $cron));
exec("crontab $tmp");
exec("rm -f $tmp");
?>
