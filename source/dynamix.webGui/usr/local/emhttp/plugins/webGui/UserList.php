<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<?foreach ($users as $user):?>
  <div class="user-list"><center><a href="<?=$path?>/UserEdit?name=<?=$user['name'];?>"><img src="/plugins/webGui/images/user.png" border="0"><br><?=$user['name']?></a><br><span style="font-size: 10px"><?=$user['desc']?></span></center></div>
<?endforeach;?>
<form method="GET" action="<?=$path?>/UserAdd">
<p class="centered"><input type="submit" value="Add User"></p>
</form>