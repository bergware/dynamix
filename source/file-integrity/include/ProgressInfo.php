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
$plugin = 'dynamix.file.integrity';
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$translations = file_exists("$docroot/webGui/include/Translations.php");

if ($translations) {
  // add translations
  $_SERVER['REQUEST_URI'] = 'integrity';
  require_once "$docroot/webGui/include/Translations.php";
} else {
  // legacy support (without javascript)
  $noscript = true;
  require_once "$docroot/plugins/$plugin/include/Legacy.php";
}

function status($cmd,$name,$file) {
  global $list;
  if (!$file) return "close blue-text";
  return ($list && strpos($list[$name],$cmd)!==false) ? "check green-text" : "circle-o orange-text";
}

if ($_POST['disk']>0) {
  $tmp = "/var/tmp/disk{$_POST['disk']}.tmp";
  $end = "$tmp.end";
  if (file_exists($tmp)) {
    echo file_get_contents($tmp);
  } else {
    echo file_exists($end) ? file_get_contents($end) : "100%#<span class='red-text red-button'>"._('Error')."</span>"._('Operation aborted')."#";
    //don't delete end file because there could be a race condition if you submit forms or reload the page for any other reason
  }
} else {
  $ctrl = "/var/tmp/ctrl.tmp";
  if (!file_exists($ctrl) || (time()-filemtime($ctrl)>=$_POST['time'])) {
    exec("/etc/cron.daily/exportrotate -q 1>/dev/null 2>&1 &");
    touch($ctrl);
  }
  $path = "/boot/config/plugins/$plugin";
  $list = @parse_ini_file("$path/disks.ini");
  $disks = parse_ini_file("state/disks.ini",true);
  $row1 = $row2 = [];
  foreach ($disks as $disk) {
    if ($disk['type']=='Data' && strpos($disk['status'],'_NP')===false) {
      $name = $disk['name'];
      $row1[] = "<td style='text-align:center'><i class='fa fa-".status('build',$name,true)."'></i></td>";
      $row2[] = "<td style='text-align:center'><i class='fa fa-".status('export',$name,file_exists("$path/export/$name.export.hash"))."'></i></td>";
    }
  }
  $x = 28-count($row1);
  echo "<tr><td style='font-style:italic'>"._('Build up-to-date')."</td>";
  echo implode('',$row1);
  echo str_repeat("<td></td>", $x);
  echo "</tr><tr id='export-status'><td style='font-style:italic'>"._('Export up-to-date')."</td>";
  echo implode('',$row2);
  echo str_repeat("<td></td>", $x);
  echo "</tr>";
}
?>
