<?PHP
/* Copyright 2005-2023, Lime Technology
 * Copyright 2012-2023, Bergware International.
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
$data[] = 'action="'.($_POST['action']??'').'"';
$data[] = 'title="'.rawurldecode($_POST['title']??'').'"';
$data[] = 'source="'.htmlspecialchars_decode(rawurldecode($_POST['source']??'')).'"';
$data[] = 'target="'.rawurldecode($_POST['target']??'').'"';
$data[] = 'H="'.(empty($_POST['hdlink']) ? '' : 'H').'"';
$data[] = 'sparse="'.(empty($_POST['sparse']) ? '' : '--sparse').'"';
$data[] = 'exist="'.(empty($_POST['exist']) ? '--ignore-existing' : '').'"';
$data[] = 'zfs="'.rawurldecode($_POST['zfs']??'').'"';
file_put_contents('/var/tmp/file.manager.active',implode("\n",$data));
?>
