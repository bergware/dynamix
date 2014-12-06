<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<?
// User share data exists only if array is Started
if ($var['fsState']!="Started") {
  echo "<p class='notice'>Array must be <strong><big>started</big></strong> to view User Shares.</p>";
  return;
}
// Share size per disk
$preserve = $path==$prev;
$ssz = array();
foreach (glob("state/plugins/webGui/*.ssz", GLOB_NOSORT) as $entry) {
  if ($preserve) {
    $ssz[basename($entry, ".ssz")] = parse_ini_file($entry);
  } else {
    unlink($entry);
  }
}
?>
<table class="share_status <?=$display['view']?>">
<thead><tr><td>Name</td><td>Comment</td><td>Size</td><td>Free</td><td>View</td></tr></thead>
<tbody>
<?
$row = 0;
foreach ($shares as $name => $share):
?><tr class="tr_row<?=$row^=1?>">
  <td><a href='#' class='info nohand' onClick='return false'>
  <img src='/plugins/webGui/images/<?=$share['color']?>.png' class='icon'><span>
  <img src='/plugins/webGui/images/green-on.png' class='icon'>All files on array<br>
  <img src='/plugins/webGui/images/yellow-on.png' class='icon'>Cache contains files<br>
  </span></a><a href="<?=$path?>/Share?name=<?=urlencode($name)?>"><?=$share['name']?></a></td>
  <td><?=$share['comment']?></td>
<?if (array_key_exists("{$share['name']}", $ssz)):?>
  <td><?=my_scale($ssz[$share['name']]['total']*1024, $unit).' '.$unit?></td>
  <td><?=my_scale($share['free']*1024, $unit).' '.$unit?></td>
  <td><a href="<?=$path?>/Browse?dir=/mnt/user/<?=urlencode($share['name'])?>"><img src="/plugins/webGui/images/explore.png" title="Browse /mnt/user/<?=urlencode($share['name'])?>"></a></td>
  </tr>
<?foreach ($ssz[$share['name']] as $disk_name => $disk_size):
    if ($disk_name!="total"):
?>  <tr class="share_status_size tr_row<?=$row^=1?>">
    <td><?=my_disk($disk_name)?>:</td>
    <td></td>
    <td><?=my_scale($disk_size*1024, $unit).' '.$unit?></td>
    <td><?=my_scale($disks[$disk_name]['fsFree']*1024, $unit).' '.$unit?></td>
    <td></td>
    </tr>
<?  endif;
  endforeach;
  else:
  $cmd="/usr/local/emhttp/plugins/webGui/scripts/share_size '$name' /var/local/emhttp/plugins/webGui/'$name.ssz'";
?><td><a href="/update.htm?cmd=<?=$cmd?>&runCmd=Start" target="progressFrame">Compute...</a></td>
  <td><?=my_scale($share['free']*1024, $unit).' '.$unit?></td>
  <td><a href="<?=$path?>/Browse?dir=/mnt/user/<?=urlencode($share['name'])?>"><img src="/plugins/webGui/images/explore.png" title="Browse /mnt/user/<?=urlencode($share['name'])?>"></a></td>
  </tr>
<?endif;
endforeach;
?>
</tbody>
</table>
<form method="GET" action="<?=$path?>/Share">
<input type="hidden" name="name" value="">
<p class="centered"><input type="submit" value="Add Share"<?=$var['shareUser']=='e'?'>':' disabled>User shares must be <strong>enabled</strong> to add shares.'?></p>
</form>