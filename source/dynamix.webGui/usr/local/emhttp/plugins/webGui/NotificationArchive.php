<?PHP
/* Copyright 2013, Andrew Hamer-Adams, http://www.pixeleyes.co.nz.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<table class="share_status small">
<thead><tr><td>Importance</td><td>Time</td><td>Plugin</td><td>Title</td><td>Description</td></tr></thead>
<tbody>
<?
$path = $notify['path'];
$files = glob("$path/archive/*.notify");
$datetime = $notify['date'].' '.$notify['time'];

if (empty($files)) {
  echo "<tr><td colspan='5' style='text-align:center'><em>No notifications available</em></td></tr>";
} else {
  foreach($files as $file) {
    $ini_array[$i] = parse_ini_file($file);
    $ini_array[$i]['timestamp'] = date($datetime, $ini_array[$i]['timestamp']);
    echo "<tr>";
    echo "<td>{$ini_array[$i]['importance']}</td>";
    echo "<td>{$ini_array[$i]['timestamp']}</td>";
    echo "<td>{$ini_array[$i]['plugin']}</td>";
    echo "<td>{$ini_array[$i]['subject']}</td>";
    echo "<td>{$ini_array[$i]['description']}</td>";
    echo "</tr>";
    $i++;
  }
}
?>
</tbody>
</table>
<br>
<button type="button" onclick="done();">Done</button>