<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1],$_GET);
$hub = "https://raw.github.com/bergware/dynamix/master";
$tmp = "/tmp/plugins.tmp";

function get($version) {
  $numeral = 0; $weight = 1;
  $ident = explode('.',$version);
  foreach ($ident as $nibble) {
    $numeral += $nibble/$weight;
    $weight *= 100;
  }
  return $numeral;
}

function plugin_info($file,&$name,&$version,&$author) {
  $parts = explode('-',$file);
  $name = $parts[0];
  $version = $parts[1];
  $author = ucfirst($parts[3]);
}

function plugin_search($plugin,$local) {
  foreach ($local as $name) if (strpos($name,$plugin)!==false) return $name;
  return null;
}

function plugin_list() {
  global $hub,$tmp;
  $row = 0;
  exec("rm -f $tmp");
  exec("wget --no-check-certificate -q -O $tmp $hub/plugins/plugins.txt");
  $online = is_file($tmp) ? parse_ini_file($tmp,true) : array();
  $local = array();
  foreach (glob("/boot/plugins/dynamix.*.plg",GLOB_NOSORT) as $file) $local[] = basename($file,'.plg');
  foreach (glob("/boot/config/plugins/dynamix.*.plg",GLOB_NOSORT) as $file) $local[] = basename($file,'.plg');
  if (!$online) {
    foreach ($local as $file) {
      plugin_info($file,$name,$version,$author);
      $online[$name]['version'] = "*";
      $online[$name]['description'] = "Github information unavailable";
      $online[$name]['author'] = $author;
      $online[$name]['reboot'] = "n";
      $online[$name]['refresh'] = "n";
      $online[$name]['url'] = "";
    }
  }
  foreach ($online as $plugin => $info) {
    if ($file = plugin_search($plugin,$local)) {
      plugin_info($file,$name,$version,$author);
      if ("v$version"==exec("cut -d' ' -f2 /var/log/plugins/$name 2>/dev/null")) {
        $status = "Installed";
        $uninstall = "<input type='button' id='$plugin-$version-{$info['reboot']}-{$info['refresh']}' value='Uninstall' onclick='pluginUninstall(this.id)'>";
        $update = "<input type='button' id='$plugin-$version-{$info['version']}-{$info['refresh']}' value='Update' onclick='pluginUpdate(this.id)'>";
        $notes = is_file("/tmp/{$plugin}.txt") ? "<a href='/plugins/dynamix/include/ReleaseNotes.php?file=/tmp/{$plugin}.txt' rel='shadowbox;height=460;width=430' title='Release Notes' class='sb-refresh'><input type='button' value='Changes'></a>" : "";
        $button = $uninstall.(get($version)<get($info['version']) ? $update : $notes);
        $boot = $info['reboot']=="y" ? "Yes" : "No";
      } else {
        $status = "Downloaded";
        $remove = "<input type='button' id='$plugin-$version-{$info['refresh']}' value='Remove' onclick='pluginRemove(this.id)'>";
        $update = "<input type='button' id='$plugin-$version-{$info['version']}-{$info['refresh']}' value='Update' onclick='pluginUpdate(this.id)'>";
        $install = "<input type='button' id='$plugin-$version-l-{$info['refresh']}' value='Install' onclick='pluginInstall(this.id)'>";
        $button = $remove.(get($version)<get($info['version']) ? $update : $install);
        $boot = "";
      }
    } else {
      $name = $plugin; $version = '*'; $author = $info['author'];
      $status = "Available";
      $install = "<input type='button' id='$plugin-{$info['version']}-r-{$info['refresh']}' value='Install' onclick='pluginInstall(this.id)'>";
      $reboot = "<a href='/update.htm?reboot=apply' target='progressFrame'><input type='button' value='Reboot'></a>";
      $button = is_file("/tmp/$plugin.reboot") ? $reboot : $install;
      $boot = "";
    }
    $icon = "/plugins/dynamix/images/".($plugin=='dynamix.webGui' ? "lime-gui" : $name).".png";
    if (!is_file("/usr/local/emhttp{$icon}")) $icon = "/plugins/dynamix/images/blank.png";
    $name = ucwords(str_replace(array('dynamix.','.')," ",$name));
    $href = ($status=='Installed' && $info['url']) ? "href='{$info['url']}'" : "href='#' style='cursor:default'";
    echo "<tr class='tr_row".($row^=1)."'><td><img src='$icon'></td><td><a $href>$name</a></td><td>{$info['description']}</td><td>$version</td><td>{$info['version']}</td><td>$author</td><td>$status</td><td>$button</td><td>$boot</td></tr>";
  }
  foreach ($local as $file) {
    plugin_info($file,$name,$version,$author);
    if (!isset($online[$name])) {
      if (is_file("/var/log/plugins/$name")) {
        $status = "Installed";
        $uninstall = "<input type='button' id='$name-$version-{$info['reboot']}-{$info['refresh']}' value='Uninstall' onclick='pluginUninstall(this.id)'>";
        $button = $uninstall;
        $boot = $info['reboot']=="y" ? "Yes" : "No";
      } else {
        $status = "Downloaded";
        $remove = "<input type='button' id='$name-$version-{$info['refresh']}' value='Remove' onclick='pluginRemove(this.id)'>";
        $install = "<input type='button' id='$name-$version-l-{$info['refresh']}' value='Install' onclick='pluginInstall(this.id)'>";
        $button = $remove.$install;
        $boot = "";
      }
      $icon = "/plugins/dynamix/images/$name.png";
      if (!is_file("/usr/local/emhttp{$icon}")) $icon = "/plugins/dynamix/images/blank.png";
      $name = ucwords(str_replace(array('dynamix.','.')," ",$name));
      echo "<tr class='tr_row".($row^=1)."'><td><img src='$icon'></td><td><a href='#' style='cursor:default'>$name</a></td><td>Unsupported plugin</td><td>$version</td><td>*</td><td>$author</td><td>$status</td><td>$button</td><td>$boot</td></tr>";
    }
  }
  echo "<tr><td colspan='9' style='text-align:right;font-size:x-small;font-type:italic;'>*Reboot is only required after a <b>UNINSTALL</b> action.</td></tr>";
}

switch ($_GET['cmd']) {
case 'init':
  plugin_list();
break;
case 'install':
  $plugin = $_GET['plugin'];
  $version = $_GET['version'];
  $folder = $plugin=='dynamix.webGui' ? "/boot/plugins" : "/boot/config/plugins";
  $file = "$plugin-$version-noarch-bergware.plg";
  if ($_GET['load']=='r') exec("wget --no-check-certificate -q -O $folder/$file $hub/plugins/$file");
  if (is_file("$folder/$file")) {
    exec("/usr/local/sbin/installplg $folder/$file");
    exec("wget --no-check-certificate -q -O /tmp/$plugin.txt $hub/changes/$plugin.txt");
  }
  plugin_list();
break;
case 'update':
  $plugin = $_GET['plugin'];
  $version = $_GET['version'];
  $folder = $plugin=='dynamix.webGui' ? "/boot/plugins" : "/boot/config/plugins";
  $old = "$plugin-$version-noarch-bergware.plg";
  $new = "$plugin-{$_GET['github']}-noarch-bergware.plg";
  exec("wget --no-check-certificate -q -O $folder/$new $hub/plugins/$new");
  if (is_file("$folder/$new")) {
    exec("rm -f $folder/$old");
    exec("/usr/local/sbin/installplg $folder/$new");
    exec("wget --no-check-certificate -q -O /tmp/$plugin.txt $hub/changes/$plugin.txt");
  }
  plugin_list();
break;
case 'remove':
  $plugin = $_GET['plugin'];
  $version = $_GET['version'];
  $folder = $plugin=='dynamix.webGui' ? "/boot/plugins" : "/boot/config/plugins";
  $file = "$plugin-$version-noarch-bergware.plg";
  exec("rm -f $folder/$file");
  plugin_list();
break;
case 'uninstall':
  $plugin = $_GET['plugin'];
  $version = $_GET['version'];
  $folder = $plugin=='dynamix.webGui' ? "/boot/plugins" : "/boot/config/plugins";
  $file = "$plugin-$version-noarch-bergware.plg";
  if ($_GET['reboot']=='n') exec("/usr/local/sbin/removeplg $folder/$file");
  exec("rm -f $folder/$file");
  exec("rm -f $folder/dynamix/$plugin-$version-i486-1.txz");
  exec("rm -f /boot/config/plugins/dynamix/$plugin.cfg");
  exec("rm -f /boot/config/plugins/images/$plugin.png");
  if ($_GET['reboot']=='y') exec("touch /tmp/$plugin.reboot");
  plugin_list();
break;
}
?>