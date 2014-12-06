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
function show_map($menu, $level) {
  $pages = find_pages( $menu, TRUE);
  if (empty($pages))
    return;
  echo "<ul>";
  foreach ($pages as $page) {
    $link="<a href='/{$page['Name']}'>{$page['Name']}</a>";
    if ($page['Type'] == "menu") {
      echo "{$level} ({$link}) - {$page['Title']}<br>";
    } else if ($page['Type'] == "xmenu") {
      echo "{$level} [{$link}] - {$page['Title']}<br>";
    } else {
      echo "{$level} {$link} - {$page['Title']}<br>";
    }
    show_map( $page['Name'], $level+1);
  }
  echo "</ul>";
}
?>
<div style="margin-top:-20px"><?show_map("Tasks",1)?></div>