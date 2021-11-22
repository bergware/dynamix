<?PHP
/* Copyright 2005-2021, Lime Technology
 * Copyright 2012-2021, Bergware International.
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
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
// add translations
$_SERVER['REQUEST_URI'] = '';
require_once "$docroot/webGui/include/Translations.php";
require_once "$docroot/webGui/include/Helpers.php";

function unslash($dir) {
  return $dir[-1]!='/' ? $dir : substr($dir,0,-1);
}
function parent_link() {
  global $dir,$path,$block;
  return in_array(dirname($dir),$block)||$dir==dirname($dir) ? "" : "<a href=\"/$path?dir=".htmlspecialchars(urlencode_path(dirname($dir)))."\">Parent Directory</a>";
}
function my_devs(&$devs) {
  global $disks;
  $text = []; $i = 0;
  foreach ($devs as $dev) {
    switch ($disks[$dev]['luksState']) {
      case 0: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-unlock-alt grey-text'></i><span>"._('Not encrypted')."</span></a>"; break;
      case 1: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-unlock-alt green-text'></i><span>"._('Encrypted and unlocked')."</span></a>"; break;
      case 2: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-lock red-text'></i><span>"._('Locked: missing encryption key')."</span></a>"; break;
      case 3: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-lock red-text'></i><span>"._('Locked: wrong encryption key')."</span></a>"; break;
     default: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-lock red-text'></i><span>"._('Locked: unknown error')."</span></a>"; break;
    }
    $text[$i++] .= '&nbsp;'.compress($dev,8,0);
  }
  return implode(', ',$text);
}
extract(parse_plugin_cfg('dynamix',true));
$disks = parse_ini_file('state/disks.ini',true);
$dir   = preg_replace('://+:','/',urldecode($_GET['dir']??''));
$path  = unscript($_GET['path']??'');
$block = empty($_GET['block']) ? ['/','/mnt','/mnt/user'] : [];
$all   = $docroot.preg_replace('/([\'" &()[\]\\\\])/','\\\\$1',$dir).'/*';
$fmt   = "%F {$display['time']}";
$dirs  = $files = [];
$total = $i = 0; $n = 1;
$edit  = $dir[0]=='/' && $dir!='/' && is_dir($dir);
[$root,$main,$rest] = my_explode('/',substr($dir,1),3);
$fix   = $root=='mnt' ? ($main ?: '---') : _('flash');

echo "<thead><tr><th>".($edit?"<i id='check_0' class='fa fa-fw fa-square-o' onclick='selectAll()'></i>":"")."</th><th>"._('Type')."</th><th class='sorter-text'>"._('Name')."</th><th>"._('Size')."</th><th>"._('Last Modified')."</th><th style='width:200px'>"._('Location')."</th><th>"._('Action')."</th></tr></thead>";
if (!$dir||!is_dir($dir)||$dir[0]!='/') {
  echo "<tbody><tr><td></td><td></td><td colspan='5'>"._('Invalid path')."</td></tr></tbody>";
  exit;
}
if ($link = parent_link()) echo "<tbody class='tablesorter-infoOnly'><tr><td></td><td><div><img src='/webGui/icons/folderup.png'></div></td><td>$link</td><td colspan='4'></td></tr></tbody>";

$user = $root=='mnt' && in_array($main,['user','user0']);
if ($user) {
  $tag = implode('|',array_merge(['disk'],pools_filter($disks)));
  $set = explode(';',str_replace(',;',',',preg_replace("/($tag)/",';$1',exec("shopt -s dotglob; getfattr --no-dereference --absolute-names --only-values -n system.LOCATIONS $all 2>/dev/null"))));
}
$stat = popen("shopt -s dotglob; stat -L -c'%F|%n|%s|%Y' $all 2>/dev/null",'r');
while (($row = fgets($stat))!==false) {
  $row .= $user ? "|{$set[++$i]}" : "|$fix";
  if (substr($row,0,3)=='dir') $dirs[] = $row; else $files[] = $row;
}
pclose($stat);

echo "<tbody>";
foreach ($dirs as $row) {
  [$type,$name,$size,$time,$set] = my_explode('|',$row,5);
  $file = pathinfo($name);
  $name = $file['basename'];
  $devs = explode(',',$set);
  echo "<tr>";
  echo "<td>".($edit?"<i id='check_$n' class='fa fa-fw fa-square-o' onclick='selectOne(this.id)'></i>":"")."</td>";
  echo "<td data=''><div class='icon-dir'></div></td>";
  echo "<td><a href=\"/$path?dir=".htmlspecialchars(urlencode_path(unslash($dir)."/$name"))."\">".htmlspecialchars($name)."</a></td>";
  echo "<td data='0'>&lt;"._('FOLDER')."&gt;</td>";
  echo "<td data='$time'>".my_time($time,$fmt)."</td>";
  echo "<td class='loc'>".my_devs($devs)."</td>";
  echo "<td>".($edit?"<i id='row_$n' data=\"".unslash($dir)."/$name\" type='d' class='fa fa-plus-square-o' onclick='addFolderContext(this.id)'>...</i>":"")."</td>";
  echo "</tr>";
  $n++;
}
if (count($dirs)) echo "</tbody><tbody>";
foreach ($files as $row) {
  [$type,$name,$size,$time,$set] = my_explode('|',$row,5);
  $file = pathinfo($name);
  $name = $file['basename'];
  $ext  = strtolower($file['extension']);
  $devs = explode(',',$set);
  $tag  = strpos($set,',')===false ? '' : 'warning';
  echo "<tr>";
  echo "<td>".($edit?"<i id='check_$n' class='fa fa-fw fa-square-o' onclick='selectOne(this.id)'></i>":"")."</td>";
  echo "<td data='$ext'><div class='icon-file icon-$ext'></div></td>";
  echo "<td>".htmlspecialchars($name)."</td>";
  echo "<td data='$size' class='$tag'>".my_scale($size,$unit)." $unit</td>";
  echo "<td data='$time' class='$tag'>".my_time($time,$fmt)."</td>";
  echo "<td class='loc $tag'>".my_devs($devs)."</td>";
  echo "<td>".($edit?"<i id='row_$n' data=\"".unslash($dir)."/$name\" type='f' class='fa fa-plus-square-o' onclick='addFileContext(this.id)'>...</i>":"")."</td>";
  echo "</tr>";
  $total += $size;
  $n++;
}
echo "</tbody>";

$dirs  = count($dirs);
$files = count($files);
$objs  = $dirs + $files;
if ($objs==0 && !exec("find \"$dir\" -maxdepth 0 -empty -exec echo 1 \;")) {
  echo "<tbody><tr><td></td><td></td><td colspan='5'>"._('No listing: Too many files')."</td></tr></tbody>";
} else {
  $total = ' ('.my_scale($total,$unit).' '.$unit.' '._('total').')';
  echo "<tfoot><tr><td></td><td></td><td colspan='5'>$objs "._('object'.($objs==1?'':'s')).": $dirs "._('director'.($dirs==1?'y':'ies')).", $files "._('file'.($files==1?'':'s'))."$total</td></tr></tfoot>";
}
?>