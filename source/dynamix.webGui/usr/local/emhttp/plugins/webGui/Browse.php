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
// Create list of objects in directory given by global $dir variable.  The list
// is sorted by $field (type, name, size, time or disk), either SORT_ASC or SORT_DESC
// as given by $opt.

function sort_by($field, $opt, $show_disk) {
// read directory contents into 'list' array
  global $dir;
  $path = '/usr/local/emhttp'.preg_replace('/([\'" &()[\]\\\\])/','\\\\$1',$dir).'/*';
  $file = array(); $disk = array(); $list = array();
  $i = 0;
  exec("stat -L -c'%F|%n|%s|%Y' $path", $file);
  if ($show_disk)
    exec("getfattr --absolute-names -n user.LOCATION $path | awk -F'\"' '/^user.LOCATION/ {print $2}'", $disk);
  else
    $disk = array_fill(0, (count($file)>0?count($file):1), '');
  foreach ($file as $entry) {
    $attr = explode('|', $entry);
    $list[] = array(
    'type' => $attr[0],
    'name' => basename($attr[1]),
    'fext' => strtolower(pathinfo($attr[1],PATHINFO_EXTENSION)),
    'size' => $attr[2],
    'time' => $attr[3],
    'disk' => $disk[$i++]);
  }
// sort by input 'field'
  if ($field=='name') {
    $type = array();
    $name = array();
    foreach ($list as $row) {
      $type[] = $row['type'];
      $name[] = strtolower($row['name']);
    }
    array_multisort($type,$opt, $name,$opt, $list);
  } else {
    $type = array();
    $indx = array();
    $name = array();
    foreach ($list as $row) {
      $type[] = $row['type'];
      $indx[] = $row[$field];
      $name[] = strtolower($row['name']);
    }
    if ($field=='size'||$field=='time')
      array_multisort($type,$opt, $indx,$opt,SORT_NUMERIC, $name,$opt, $list);
    else
     array_multisort($type,$opt, $indx,$opt, $name,$opt, $list);
  }
// return sorted list
  return $list;
}

function safe_dirname($path) {
  $dirname = urlencode_path(dirname($path));
  return $dirname == '/' ? '' : $dirname;
}

// here we go..
$show_disk = (substr_compare("/mnt/user",$dir,0,9)==0);
clearstatcache();
if (!isset($column)) $column='name';
if (!isset($order)) $order='A';
$list = sort_by($column, $order=='A'?SORT_ASC:SORT_DESC, $show_disk);

$order=($order=='A'?'D':'A');
$fext_order=($column=='fext'?$order:'A');
$name_order=($column=='name'?$order:'A');
$size_order=($column=='size'?$order:'A');
$time_order=($column=='time'?$order:'A');
$disk_order=($column=='disk'?$order:'A');
$row=1;
?>
<table id="indexer">
  <tr>
  <td><a href="<?='/'.$path.'?dir='.$dir.'&column=fext&order='.$fext_order?>">Type</a></td>
  <td><a href="<?='/'.$path.'?dir='.$dir.'&column=name&order='.$name_order?>">Name</a></td>
  <td><a href="<?='/'.$path.'?dir='.$dir.'&column=size&order='.$size_order?>">Size</a></td>
<?if ($show_disk):?>
  <td><a href="<?='/'.$path.'?dir='.$dir.'&column=disk&order='.$disk_order;?>">Location</a></td>
<?endif;?>
  <td><a href="<?='/'.$path.'?dir='.$dir.'&column=time&order='.$time_order?>">Last Modified</a></td>
  </tr>
<?if ($dir):?>
  <tr class="tr_row<?=$row^=1?>">
  <td><div class="icon-dirup"></div></td>
  <td><a href="<?='/'.$path.'?dir='.safe_dirname($dir)?>">Parent Directory</a></td>
  <td></td>
  <td></td>
<?if ($show_disk):?>
  <td></td>
<?endif;?>
  </tr>
<?endif;?>
<?$dirs=0; $files=0; $total=0;
  foreach ($list as $entry):
?>  <tr class="tr_row<?=$row^=1?>">
<?  if ($entry['type']=='directory'):
      $dirs++;
      $warn = "";
?>    <td><div class="icon-folder"></div></td>
      <td><a href="<?='/'.$path.'?dir='.urlencode_path($dir.'/'.$entry['name'])?>"><?=$entry['name']?></a></td>
      <td></td>
<?  else:
      $files++;
      $total+=$entry['size'];
      $warn = substr($entry['disk'],-1)=="*" ? " style='color:orange'" : "";
?>    <td><div class="icon-file icon-<?=strtolower($entry['fext'])?>"></div></td>
      <td<?=$warn?>><?=$entry['name']?></td>
      <td<?=$warn?>><?=my_scale($entry['size'],$unit).' '.$unit?></td>
<?  endif;?>
<?if ($show_disk):?>
    <td<?=$warn?>><?=$entry['disk'];?></td>
<?endif;?>
    <td<?=$warn?>><?=my_time($entry['time'],"%F {$display['time']}")?></td>
    </tr>
<?endforeach;
  $objs = $dirs + $files;
  $objtext = ($objs == 1)? "1 object" : "{$objs} objects";
  $dirtext = ($dirs == 1)? "1 directory" : "{$dirs} directories";
  $filetext = ($files == 1)? "1 file" : "{$files} files";
  $totaltext = ($files == 0)? "" : '('.my_scale($total,$unit).' '.$unit.' total)';
?><tr>
  <td></td>
  <td colspan=<?=$show_disk?4:3?>><span><?=$objtext?>: <?=$dirtext?>, <?=$filetext?> <?=$totaltext?></span></td>
  </tr>
</table>