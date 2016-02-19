<?PHP
/* Copyright 2015-2016, Bergware International.
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
function status($cmd, $disk) {
  global $list;
  return ($list && strpos($list[$disk],$cmd)!==false) ? "check green-text" : "circle-o orange-text";
}

if ($_POST['disk']>0) {
  $tmp = "/var/tmp/disk{$_POST['disk']}.tmp";
  $end = "$tmp.end";
  if (file_exists($tmp)) {
    echo file_get_contents($tmp);
  } else {
    echo file_exists($end) ? file_get_contents($end) : "100%#Operation aborted#";
    @unlink($end);
  }
} else {
  $path = "/boot/config/plugins/dynamix.file.integrity";
  $list = @parse_ini_file("$path/disks.ini");
  $disks = parse_ini_file('state/disks.ini',true);
  $row1 = $row2 = [];
  foreach ($disks as $disk) {
    if ($disk['type']=='Data') {
      $row1[] = "<td style='text-align:center'><i class='fa fa-".(status("build", $disk['name']))."'></i></td>";
      $row2[] = "<td style='text-align:center'><i class='fa fa-".(status("export", $disk['name']))."'></i></td>";
    }
  }
  echo "<tr><td style='font-style:italic'>Build up-to-date</td>";
  echo implode('',$row1);
  echo "</tr><tr><td style='font-style:italic'>Export up-to-date</td>";
  echo implode('',$row2);
  echo "</tr>";
}
?>
