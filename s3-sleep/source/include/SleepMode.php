<?PHP
/* Copyright 2016, Bergware International.
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
require_once("/usr/local/emhttp/webGui/include/Wrappers.php");
$cfg = parse_plugin_cfg("dynamix.s3.sleep");

$debug = $cfg['debug'] ? "-D {$cfg['debug']}" : "";
$preRun = $cfg['preRun'] ? "-b /usr/local/emhttp/plugins/dynamix.s3.sleep/scripts/preRun" : "";
$postRun = $cfg['postRun'] ? "-p /usr/local/emhttp/plugins/dynamix.s3.sleep/scripts/postRun" : "";

// Go to sleep
exec("echo 'Enter sleep mode'|logger --tag s3_sleep");
exec("/usr/local/emhttp/plugins/dynamix.s3.sleep/scripts/s3_sleep -S $preRun $postRun $debug");
// Now sleeping...
exec("echo 'Wake-up from sleep mode'|logger --tag s3_sleep");
?>
