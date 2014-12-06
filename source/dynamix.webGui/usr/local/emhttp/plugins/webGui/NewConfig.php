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
<p>This is a utility to reset the array disk configuration so that all disks appear as "New" disks, as
if it were a fresh new server.
<p>This is useful when you have added or removed multiple drives and wish to rebuild parity based on the new configuration.
<p><b>DO NOT USE THIS UTILITY THINKING IT WILL REBUILD A FAILED DRIVE</b> - it will have the opposite
effect of making it <b><em>impossible</em></b> to rebuild an existing failed drive - you have been warned!
EOF
?>
   
<p><?=$help;?></p>
<form name="newConfig" method="POST" action="/update.htm" target="progressFrame">
<?if ($var['fsState']=="Started"):?>
<p><input type="submit" name="cmdInit" value="Apply" disabled>Array must be <strong><big>stopped</big></strong></p>
<?else:?>
<p><input type="submit" name="cmdInit" value="Apply" disabled><input type="checkbox" onClick="cmdInit.disabled=!this.checked"><small>Yes I want to do this</small></p>
<?endif;?>
</form>