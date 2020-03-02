<?PHP
/* Copyright 2012-2017, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * Plugin development contribution by gfjardim
 * Version log:
 * Version 1.6   Modified by InfinityMod - added multifan support
 */
?>
<?
function scan_dir($dir, $type = "") {
  $out = array();
  foreach (array_diff(scandir($dir), array('.','..')) as $f){
    $out[] = realpath($dir).'/'.$f ;
  }
  return $out;
}
function list_fan() {
  $out = array();
  exec("find /sys/devices -type f -iname 'fan[0-9]_input' -exec dirname \"{}\" +|uniq", $chips);
  foreach ($chips as $chip) {
    $name = is_file("$chip/name") ? file_get_contents("$chip/name") : "";
    foreach (preg_grep("/fan\d+_input/", scan_dir($chip)) as $fan) {
      $out[] = array('chip'=>$name, 'name'=>end(explode('/',$fan)), 'sensor'=>$fan , 'rpm'=>file_get_contents($fan));
    }
  }
  return $out;
}

switch ($_GET['op']) {
case 'detect':
if (is_file( $_GET['pwm'])) {
  $pwm = $_GET['pwm'];
  $default_method = file_get_contents($pwm."_enable");
  $default_rpm    = file_get_contents($pwm);
  file_put_contents($pwm."_enable", "1");
  file_put_contents($pwm, "150");
  sleep(3);
  $init_fans = list_fan();
  file_put_contents($pwm, "255");
  sleep(3);
  $final_fans = list_fan();
  file_put_contents($pwm, $default_rpm);
  file_put_contents($pwm."_enable", $default_method);
  for ($i=0; $i < count($final_fans); $i++) {
    if (($final_fans[$i]['rpm'] - $init_fans[$i]['rpm'])>10) {
      echo $init_fans[$i]['sensor'];
      break;
    }
  }
}
break;
case 'pwm':
if (is_file( $_GET['pwm']) && is_file( $_GET['fan'])) {
  $docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
  $autofan = "$docroot/plugins/dynamix.system.autofan/scripts/rc.autofan";
  exec("$autofan stop >/dev/null");
  $pwm = $_GET['pwm'];
  $fan = $_GET['fan'];
  $fan_min = explode("_", $_GET['fan'])[0]."_min";
  $default_method = file_get_contents($pwm."_enable");
  $default_pwm = file_get_contents($pwm);
  $default_fan_min = file_get_contents($fan_min);
  file_put_contents($pwm."_enable", "1");
  file_put_contents($fan_min, "0");
  file_put_contents($pwm, "0");
  sleep(5);
  $min_rpm = file_get_contents($fan);
  foreach (range(0, 20) as $i) {
    $val=$i*5;
    file_put_contents($pwm, $val);
    sleep(2);
    if ((file_get_contents($fan) - $min_rpm) > 15) {
      # Debounce
      for ($i=0; $i <= 10; $i++) { 
        if (file_get_contents($fan) == 0) {$is_lowest = FALSE; break;} else {$is_lowest = TRUE; sleep(1);};
      }
      if ($is_lowest) {echo $val; break;}
    }
  }
  file_put_contents($pwm, $default_pwm);
  file_put_contents($fan_min, $default_fan_min);
  file_put_contents($pwm."_enable", $default_method);
  exec("$autofan start >/dev/null");
}
break;
case 'settings':
   $fan_id = $_GET['fan_id'];
   $plugin="dynamix.system.autofan";
   $settings_root = "/boot/config/plugins/$plugin";
   $docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
   $std_settings = "$docroot/plugins/$plugin/default.cfg";
   $settings = "$settings_root/$plugin.$fan_id.cfg";

   if(!file_exists ($settings)){
    $settings = $std_settings;
   }
   
   $file_handle = fopen($settings, "rb");
   $response = [];
   while (!feof($file_handle) ) {
    $line_of_text = fgets($file_handle);
    $parts = explode('=', $line_of_text);
    if ($parts[0] != ""){
        $response[$parts[0]] = substr(substr($parts[1], 1), 0, -2);
    }
   }
   fclose($file_handle);
   echo json_encode($response);   
break;}

?>
