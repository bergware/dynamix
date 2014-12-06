<table class="share_status small">
<thead><tr><td>Name</td><td width='15%'>Size</td><td width='15%'>Last Modified</td><td width='5%'>Delete</td></tr></thead>
<tbody>
<?
$logs = glob("/var/log/lighttpd/*.log");
if (empty($logs)) {
  echo "<tr><td colspan='4' style='text-align:center'><em>No LOG files available.</em></td></tr>";
} else {
  foreach ($logs as $log) {
    echo "<tr><td><a href='/update.htm?cmd=/usr/bin/tail%20-n%201000%20-f%20{$log}&forkCmd=Start' id='openlog' rel='shadowbox;height=600;width=800' title='$log' class='sb-enable'>$log</a></td>";
    echo "<td>".filesize($log)."</td><td>".my_time(filemtime($log),"%F {$display['time']}")."</td>";
    echo "<td><a href='/plugins/dynamix/include/DeleteLogFile.php?log=$log' target='progressFrame'><img src='/plugins/dynamix/images/delete.png' title='Delete file'></a></td></tr>";
  }
}
?>
</tbody>
</table>
<br>
<button type="button" onclick="done();">Done</button>