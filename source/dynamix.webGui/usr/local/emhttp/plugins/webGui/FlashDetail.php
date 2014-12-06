<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
/* Adapted by Bergware International (December 2013) */
?>
<table class="settings">
  <tr>
  <td>Flash vendor:</td>
  <td><?=$var['flashVendor']?></td>
  </tr>
  <tr>
  <td>Flash product:</td>
  <td><?=$var['flashProduct']?></td>
  </tr>
  <tr>
  <td>Flash GUID:</td>
  <td><?=$var['flashGUID']?></td>
  </tr>
<?if ($var['regTy']=="Basic" && strlen($var['regGUID'])):?>
  <tr>
  <td>Registered GUID:</td>
  <td><?=$var['regGUID']?><strong>Wrong</strong></td>
  </tr>
<?endif;?>
  <tr>
  <td>Registered to:</td>
  <td><?=$var['regTo'] ? $var['regTo'] : 'unregistered'?></td>
  </tr>
  <tr>
  <td>Registration date:</td>
  <td><?=strlen(my_key()) ? my_key() : '---'?></td>
  </tr>
<?if ($var['regTy']=="Basic" && !strlen($var['regGUID'])):?>
  <tr>
  <td></td>
  <td><a href="http://lime-technology.com/registration-keys?BasicGUID=<?=$var['flashGUID'];?>" target="_blank">Upgrade to <em><strong>Plus</strong></em> or go <em><strong>Pro</strong></em></a></td>
  </tr>
<?endif;?>
<?if ($var['regTy']=="Plus"):?>
  <tr>
  <td></td>
  <td><a href="http://lime-technology.com/registration-keys?PlusGUID=<?=$var['flashGUID'];?>" target="_blank">Upgrade to <em><strong>Pro</strong></em></a></td>
  </tr>
<?endif;?>
</table>
<br>
<button type="button" onClick="done();">Done</button>