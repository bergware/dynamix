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
$help = <<<EOF
<p>This is a one-time action to be taken after upgrading from a pre-5.0 unRAID server
release to version 5.0. It is also useful for restoring default ownership/permissions on files and
directories when transitioning back from Active Directory to non-Active Directory integration.
<p>This utility starts a background process that goes to each of your data disks and cache disk
and changes file and directory ownership to nobody/users (i.e., uid/gid to 99/100), and sets permissions
as follows:
<pre>
For directories:
  drwxrwxrwx

For read/write files:
  -rw-rw-rw-

For readonly files:
  -r--r--r--
</pre>
  <p>Clicking Start will open another window and start the background process. Closing the window before
  completion will terminate the background process. This process can take a long time if you have many files.
EOF
?>

<script type="text/javascript">
function run_newperms() {
  var title="<?=$var['NAME'];?> newperms";
  var url="/logging.htm?title=" + title + "&cmd=/usr/local/sbin/newperms&forkCmd=Start";
  openWindow(url, title.replace(/ /g, "_"));
}
</script>

<p><?=$help;?></p>

<form method="POST" action="/update.htm" target="progressFrame" onsubmit="run_newperms()">
<?exec("pgrep newperms", $pids);?>
<?if (!empty($pids)):?>
  <p><input type="submit" value="Start" disabled> Already running!</p>
<?else:?>
<?if ($var['fsState']!="Started"):?>
  <p><input type="submit" value="Start" disabled> Array must be <strong><big>started</big></strong> to change permissions.</p>
<?else:?>
  <p><input type="submit" name="submit_button" value="Start" disabled><input type="checkbox" onClick="submit_button.disabled=!this.checked"><small>Yes I want to do this</small></p>
<?endif;?>
<?endif;?>
</form>