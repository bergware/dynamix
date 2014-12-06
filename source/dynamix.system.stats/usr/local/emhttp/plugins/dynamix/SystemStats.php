<?PHP
/* Copyright 2013, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
if ($var['fsState']=="Stopped"):
  echo "<div class='notice'>Array must be <strong><big>started</big></strong> to view system stats.</div>";
  return;
endif;

function selector() {
  global $graph, $frame;
  echo "<span id='selector' class='status' style='display:none;margin-top:-4px;margin-right:-4px'><span><select size='1' onchange='modeller(this.value)'>";
  echo mk_option($graph, '0', 'Real-time');
  echo mk_option($graph, '1', 'Last day');
  echo mk_option($graph, '2', 'Last 2 days');
  echo mk_option($graph, '3', 'Last 3 days');
  echo mk_option($graph, '7', 'Last week');
  echo mk_option($graph, '14', 'Last 2 weeks');
  echo mk_option($graph, '21', 'Last 3 weeks');
  echo mk_option($graph, '31', 'Last month');
  echo mk_option($graph, '3653', 'Since start');
  echo "</select></span><span id='monitor' style='display:none'><select size='1' onchange='resizer(this.value)'>";
  echo mk_option($frame, '15', '30 seconds');
  echo mk_option($frame, '30', '1 minute');
  echo mk_option($frame, '60', '2 minutes');
  echo mk_option($frame, '150', '5 minutes');
  echo mk_option($frame, '300', '10 minutes');
  echo mk_option($frame, '900', '30 minutes');
  echo mk_option($frame, '1800', '1 hour');
  echo mk_option($frame, '3600', '2 hours');
  echo "</select><button type='button' onclick='modeller(0)'>Reset</button></span></span>";
}
?>
<script>
$('.tabs').append("<?=selector()?>");
</script>
<?if (strpos($show,'cpu')!==false):?>
<span id='cpu' class='graph<?=$cfg['cols']?3:2?>'></span>
<?endif;?>
<?if (strpos($show,'ram')!==false):?>
<span id='ram' class='graph<?=$cfg['cols']?3:2?>'></span>
<?endif;?>
<?if (strpos($show,'com')!==false):?>
<span id='com' class='graph<?=$cfg['cols']?3:2?>'></span>
<?endif;?>
<?if (strpos($show,'hdd')!==false):?>
<span id='hdd' class='graph<?=$cfg['cols']?3:2?>'></span>
<?endif;?>