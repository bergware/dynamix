<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
parse_str($argv[1], $_GET);
$log = $_GET['log'];
exec("rm -f $log");
?>
<html>
<head><script>var goback=parent.location;</script></head>
<body onLoad="parent.location=goback;"></body>
</html>