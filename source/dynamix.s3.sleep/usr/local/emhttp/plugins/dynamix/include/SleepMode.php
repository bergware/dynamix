<?PHP
/* Copyright 2013, Bergware International
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
// Go to sleep
exec("echo 'Enter sleep mode'|logger");
exec("echo -n mem >/sys/power/state");
// Now sleeping...
exec("echo 'Wake-up from sleep mode'|logger");
?>