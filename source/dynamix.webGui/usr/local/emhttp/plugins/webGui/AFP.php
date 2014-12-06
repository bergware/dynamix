<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<form method="POST" action="/update.htm" target="progressFrame">
<table class="settings">
  <tr>
  <td>Enable AFP:</td>
  <td><select name="shareAFPEnabled" size="1">
  <?=mk_option($var['shareAFPEnabled'], "no", "No");?>
  <?=mk_option($var['shareAFPEnabled'], "yes", "Yes");?>
  </select></td>
  </tr>
  <tr>
  <td>Connected users:</td>
  <td>
<?if ($var['shareAFPEnabled']=="yes"):
    $AFPUsers = exec("ps anucx | grep -c 'afpd'");
    if ($AFPUsers>0) $AFPUsers--;
    echo $AFPUsers;
  endif;
?></td>
  </tr>
  <tr>
  <td></td>
  <td><input type="submit" name="changeShare" value="Apply"><button type="button" onclick="done();">Done</button></td>
  </tr>
</table>
</form>