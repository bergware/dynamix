<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<link type="text/css" rel="stylesheet" href="/plugins/webGui/fonts/dynamix-white.css">
<link type="text/css" rel="stylesheet" href="/plugins/webGui/styles/template-white.css">
<div style="margin:20px">
<?
parse_str($argv[1],$_GET);
if (is_file($_GET['file'])) {
  $contents = explode("\n",file_get_contents($_GET['file']));
  foreach ($contents as $line) echo "$line<br>";
  exec("rm -f {$_GET['file']}");
} else {
  echo "No release notes available<br>";
}
?>
<small>Release notes of <strong>ALL</strong> plugins can be found on <a href="https://github.com/bergware/dynamix/tree/master/changes" target="_blank"><u>GitHub</u></a>.</small>
</div>