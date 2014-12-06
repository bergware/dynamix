<?PHP
/* Copyright 2013, Andrew Hamer-Adams, http://www.pixeleyes.co.nz.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<?
function array2json($arr) {
  if (function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
  $parts = array();
  $is_list = false;
  //Find out if the given array is a numerical array
  $keys = array_keys($arr);
  $max_length = count($arr)-1;
  if (($keys[0] == 0) and ($keys[$max_length] == $max_length)) { //See if the first key is 0 and last key is length - 1
    $is_list = true;
    for ($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
      if ($i != $keys[$i]) { //A key fails at position check.
        $is_list = false; //It is an associative array.
        break;
      }
    }
  }
  foreach ($arr as $key=>$value) {
    if (is_array($value)) { //Custom handling for arrays
      if ($is_list) $parts[] = array2json($value); /* :RECURSION: */
      else $parts[] = '"'.$key.'":'.array2json($value); /* :RECURSION: */
    } else {
      $str = '';
      if (!$is_list) $str = '"'.$key.'":';
      //Custom handling for multiple data types
      if (is_numeric($value)) $str .= $value; //Numbers
      elseif ($value === false) $str .= 'false'; //The booleans
      elseif ($value === true) $str .= 'true';
      else $str .= '"'.addslashes($value).'"'; //All other things
      // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
      $parts[] = $str;
    }
  }
  $json = implode(',',$parts);
  if ($is_list) return '['.$json.']'; //Return numerical JSON
  return '{'.$json.'}'; //Return associative JSON
}

parse_str($argv[1], $_GET);

$dynamix = parse_ini_file("boot/config/plugins/dynamix/dynamix.webGui.cfg",true);
$notify = &$dynamix['notify'];

$unread_path = $notify['path'].'/unread';
$archive_path = $notify['path'].'/archive';

switch ($_GET['cmd']) {
case 'add':
  if (!is_dir($unread_path)) mkdir($unread_path,0,true);
  $unread_file = $unread_path.'/'.$_GET['plugin'].'-'.time().'.notify';
  $handle = fopen($unread_file, 'w') or die('Cannot open file:  '.$unread_file);
  $data = "[notification]\n\n";
  fwrite($handle, $data);
  $handle = fopen($unread_file, 'a') or die('Cannot open file:  '.$unread_file);
  unset($_GET['cmd']);
  $data = "timestamp = ".time()."\n";
  fwrite($handle, $data);
  foreach ($_GET as $setting => $value) {
    $data = $setting." = ".$value."\n";
    fwrite($handle, $data);
  }
  fclose($handle);
  break;
case 'get':
  if (!is_dir($unread_path)) mkdir($unread_path,0,true);
  if (isset($notify['date']) && isset($notify['time'])) {
    $datetime = $notify['date'].' '.$notify['time'];
  }
  $i = 0;
  $files = glob("$unread_path/*.notify");
  if (!empty($files)) {
    foreach ($files as $file) {
      $notify_array[$i] = parse_ini_file($file);
      $notify_array[$i]['timestamp'] = date($datetime, $notify_array[$i]['timestamp']);
      $path_parts = pathinfo($file);
      $notify_array[$i]['file'] = $path_parts['filename'].'.'.$path_parts['extension'];
      $notify_array[$i] = array2json($notify_array[$i]);
      $i++;
    }
    echo array2json($notify_array);
  }
  break;
case 'archive':
  if (!is_dir($archive_path)) mkdir($archive_path,0,true);
  $filepath_old = $unread_path.'/'.$_GET['file'];
  $filepath_new = $archive_path.'/'.$_GET['file'];
  if (copy($filepath_old,$filepath_new)) unlink($filepath_old);
  break;
}
?>