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

if (isset($_POST['mode'])) {
  switch ($_POST['mode']) {
  case 'upload':
    $size = 'stop';
    $file = htmlspecialchars_decode(rawurldecode($_POST['file']),ENT_QUOTES);
    if (preg_match('@^/mnt/(.+)?/.+|^/boot/?@',$file)) {
      if (($_POST['start']==0 || $_POST['cancel']==1) && file_exists($file)) unlink($file);
      if ($_POST['cancel']==0) $size = file_put_contents($file,base64_decode(explode(';base64,',$_POST['data'])[1]),FILE_APPEND);
    }
    die($size);
  case 'calc':
    extract(parse_plugin_cfg('dynamix',true));
    $source = htmlspecialchars_decode(rawurldecode($_POST['source']),ENT_QUOTES);
    $awk    = "awk 'BEGIN{ORS=\" \"}/Number of files|Total file size/{if(\$5==\"(reg:\")print \$4,\$8;if(\$5==\"(dir:\")print \$4,\$6;if(\$3==\"size:\")print \$4}'"; 
    [$files,$dirs,$size] = explode(' ',str_replace([',',')'],'',exec("rsync --stats -naI ".escapeshellarg($source)." /var/tmp 2>/dev/null|$awk")));
    $files -= $dirs;
    $text   = [];
    $text[] = _('Name').": ".basename($source);
    $text[] = _('Last modified').': '.my_age(filemtime($source));
    $text[] = _('Total occupied space').": ".my_scale($size,$unit)." $unit";
    $text[] = sprintf(_("in %s folder".($dirs==1?'':'s')." and %s file".($files==1?'':'s')),my_number($dirs),my_number($files));
    die('<div style="text-align:left;margin-left:60px">'.implode('<br>',$text).'</div>');
  }
}
function escapeQuote($data) {
  return str_replace('"','&#34;',$data);
}
function age($number,$word) {
  return sprintf(_('%s '.($number==1 ? $word : $word.'s').' ago'),$number);
}
function my_age($time) {
  $age = new DateTime('@'.$time);
  $age = date_create('now')->diff($age);
  if ($age->y > 0) return age($age->y,'year');
  if ($age->m > 0) return age($age->m,'month');
  if ($age->d > 0) return age($age->d,'day');
  if ($age->h > 0) return age($age->h,'hour');
  if ($age->i > 0) return age($age->i,'minute');
  return age($age->s,'second');
}
function is_slash($dir,$p) {
  return mb_substr($dir,$p,1)=='/';
}
function parent_link() {
  global $dir,$path,$block;
  return in_array(dirname($dir),$block)||dirname($dir)==$dir ? "" : "<a href=\"/$path?dir=".rawurlencode(dirname($dir))."\">"._('Parent Directory')."</a>";
}
function my_devs(&$devs) {
  global $disks,$fix;
  $text = []; $i = 0;
  foreach ($devs as $dev) {
    if ($fix!='---') switch ($disks[$dev]['luksState']) {
      case 0: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-unlock-alt grey-text'></i><span>"._('Not encrypted')."</span></a>"; break;
      case 1: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-unlock-alt green-text'></i><span>"._('Encrypted and unlocked')."</span></a>"; break;
      case 2: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-lock red-text'></i><span>"._('Locked: missing encryption key')."</span></a>"; break;
      case 3: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-lock red-text'></i><span>"._('Locked: wrong encryption key')."</span></a>"; break;
     default: $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-lock red-text'></i><span>"._('Locked: unknown error')."</span></a>"; break;
    } else    $text[$i] = "<a class='info' onclick='return false'><i class='lock fa fa-fw fa-hdd-o grey-text'></i></a>";
    $text[$i++] .= '&nbsp;'.compress($dev,8,0);
  }
  return implode(', ',$text);
}
extract(parse_plugin_cfg('dynamix',true));
$disks = parse_ini_file('state/disks.ini',true);
$dir   = preg_replace('://+:','/',htmlspecialchars_decode(rawurldecode($_GET['dir']??''),ENT_QUOTES));
$path  = unscript($_GET['path']??'');
$block = empty($_GET['block']) ? ['/','/mnt','/mnt/user'] : ['/'];
$shell = $docroot.preg_replace('/([\\\\\'" @^&=;:<>(){}[\]])/','\\\\$1',$dir).'/*';
$fmt   = "%F {$display['time']}";
$dirs  = $files = [];
$total = $n = 0;
$edit  = is_slash($dir,0) && $dir!='/' && is_dir($dir);
[$root,$main,$rest] = my_explode('/',mb_substr($dir,1),3);
$fix   = $root=='mnt' ? ($main ?: '---') : ($root=='boot' ? _('flash') : '---');
$isshare = $root=='mnt' && (!$main || !$rest);

echo "<thead><tr><th>".($edit?"<i id='check_0' class='fa fa-fw fa-square-o' onclick='selectAll()'></i>":"")."</th><th>"._('Type')."</th><th class='sorter-text'>"._('Name')."</th><th>"._('Owner')."</th><th>"._('Permission')."</th><th>"._('Size')."</th><th>"._('Last Modified')."</th><th style='width:200px'>"._('Location')."</th><th>"._('Action')."</th></tr></thead>";
if (!$dir||!is_dir($dir)||!is_slash($dir,0)) {
  echo "<tbody><tr><td></td><td></td><td colspan='7'>"._('Invalid path')."</td></tr></tbody>";
  exit;
}
if ($link = parent_link()) echo "<tbody class='tablesorter-infoOnly'><tr><td></td><td><div><img src='/webGui/icons/folderup.png'></div></td><td>$link</td><td colspan='6'></td></tr></tbody>";

$user = $root=='mnt' && in_array($main,['user','user0']);
if ($user) {
  $tag = implode('|',array_merge(['disk'],pools_filter($disks)));
  $set = explode(';',str_replace(',;',',',preg_replace("/($tag)/",';$1',exec("shopt -s dotglob; getfattr --no-dereference --absolute-names --only-values -n system.LOCATIONS $shell 2>/dev/null"))));
}
$stat = popen("shopt -s dotglob; stat -L -c'%F|%n|%U|%A|%s|%Y' $shell 2>/dev/null",'r');
while (($row = fgets($stat))!==false) {
  [$type,$full,$owner,$perm,$size,$time] = my_explode('|',$row,6);
  $n++; $loc = $user ? $set[$n] : $fix;
  $file = pathinfo($full);
  $full = str_replace($docroot,'',$full);
  $name = $file['basename'];
  $ext  = strtolower($file['extension']);
  $devs = explode(',',$loc);
  $tag  = count($devs)>1 ? 'warning' : '';
  $text = [];
  if ($row[0]=='d') {
    $text[] = "<tr><td>".($edit?"<i id='check_$n' class='fa fa-fw fa-square-o' onclick='selectOne(this.id)'></i>":"")."</td>";
    $text[] = "<td data=''><div class='icon-dir'></div></td>";
    $text[] = "<td><a id='name_$n' oncontextmenu='folderContextMenu(this.id,\"right\");return false' href=\"/$path?dir=".rawurlencode(htmlspecialchars($full,ENT_COMPAT))."\">".htmlspecialchars($name,ENT_COMPAT)."</a></td>";
    $text[] = "<td id='owner_$n'>"._($owner)."</td>";
    $text[] = "<td id='perm_$n'>$perm</td>";
    $text[] = "<td data='0'>&lt;".($fix=='---'&&$dir!='/'?_('DEVICE'):($isshare?_('SHARE'):_('FOLDER')))."&gt;</td>";
    $text[] = "<td data='$time'><span class='my_time'>".my_time($time,$fmt)."</span><span class='my_age' style='display:none'>".my_age($time)."</span></td>";
    $text[] = "<td class='loc'>".my_devs($devs)."</td>";
    $text[] = "<td>".($edit?"<i id='row_$n' data=\"".escapeQuote($full)."\" type='d' class='fa fa-plus-square-o' onclick='folderContextMenu(this.id,\"both\")' oncontextmenu='folderContextMenu(this.id,\"both\");return false'>...</i>":"")."</td></tr>";
    $dirs[] = implode($text);
  } else {
    $text[] = "<tr><td>".($edit?"<i id='check_$n' class='fa fa-fw fa-square-o' onclick='selectOne(this.id)'></i>":"")."</td>";
    $text[] = "<td class='ext' data='$ext'><div class='icon-file icon-$ext'></div></td>";
    $text[] = "<td id='name_$n' class='$tag' oncontextmenu='fileContextMenu(this.id,\"right\");return false'>".htmlspecialchars($name,ENT_COMPAT)."</td>";
    $text[] = "<td id='owner_$n' class='$tag'>$owner</td>";
    $text[] = "<td id='perm_$n' class='$tag'>$perm</td>";
    $text[] = "<td data='$size' class='$tag'>".my_scale($size,$unit)." $unit</td>";
    $text[] = "<td data='$time' class='$tag'><span class='my_time'>".my_time($time,$fmt)."</span><span class='my_age' style='display:none'>".my_age($time)."</span></td>";
    $text[] = "<td class='loc $tag'>".my_devs($devs)."</td>";
    $text[] = "<td>".($edit?"<i id='row_$n' data=\"".escapeQuote($full)."\" type='f' class='fa fa-plus-square-o' onclick='fileContextMenu(this.id,\"both\")' oncontextmenu='fileContextMenu(this.id,\"both\");return false'>...</i>":"")."</td></tr>";
    $files[] = implode($text);
    $total += $size;
  }
}
pclose($stat);

echo "<tbody>";
echo implode($dirs);
if (count($dirs)) echo "</tbody><tbody>";
echo implode($files);
echo "</tbody>";

$dirs  = count($dirs);
$files = count($files);
$objs  = $dirs + $files;
if ($objs==0 && !exec("find ".escapeshellarg($dir)." -maxdepth 0 -empty -exec echo 1 \;")) {
  echo "<tbody><tr><td></td><td></td><td colspan='7'>"._('No listing: Too many files')."</td></tr></tbody>";
} else {
  $total = ' ('.my_scale($total,$unit).' '.$unit.' '._('total').')';
  echo "<tfoot><tr><td></td><td></td><td colspan='7'>$objs "._('object'.($objs==1?'':'s')).": $dirs "._('director'.($dirs==1?'y':'ies')).", $files "._('file'.($files==1?'':'s'))."$total</td></tr></tfoot>";
}
?>