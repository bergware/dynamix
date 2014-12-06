<?PHP
/* Copyright 2013, Bergware International.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<script>
function nameList() {
  $('#names').load('/plugins/dynamix/include/NameList.php', "plugin=<?=$plugin?>&warn=<?=$confirm['warn']?>");
}
$(function() {
  nameList();
  $('#tab2').bind({click:function(){nameList()}});
});
</script>
<div id="names"></div>