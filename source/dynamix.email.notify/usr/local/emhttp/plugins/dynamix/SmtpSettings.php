<form name="smtp_setup" method="POST" action="/update.php" target="progressFrame" onsubmit="prepareSMTP(this)">
<input type="hidden" name="#plugin"  value="<?=$plugin?>">
<input type="hidden" name="#section" value="ssmtp">
<input type="hidden" name="#include" value="update.notify.php">
<input type="hidden" name="#config"  value="<?=$smtp?>">
<input type="hidden" name="mailhub"  value="">
<table class="settings">
<?
$data = file($smtp);
foreach ($data as $line) {
  if (!$line) continue;
  switch (substr($line,0,1)) {
  case '[':
    break;
  case '#':
    $text = preg_replace('/^#[A-Z]*: /', '', $line);
    switch (substr($line,1,1)) {
    case 'T':
      echo "<tr><td>$text";
      break;
    case 'C':
      echo "<img src='$image/information.png' class='tooltip' title='$text'></td><td>";
      break;
    }
    break;
  default:
    $key = explode('=', $line);
    $key[1] = trim($key[1]);
    switch ($key[1]) {
    case "NO":
      echo "<input type='radio' name='{$key[0]}' value='YES'>Yes";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='NO' CHECKED>No";
      break;
    case "YES":
      echo "<input type='radio' name='{$key[0]}' value='YES' CHECKED>Yes";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='NO'>No";
      break;
    case "none":
      echo "<input type='radio' name='{$key[0]}' value='none' CHECKED>None";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='cram-md5'>CRAM-MD5";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='login'>Login";
      break;
    case "cram-md5":
      echo "<input type='radio' name='{$key[0]}' value='none'>None";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='cram-md5' CHECKED>CRAM-MD5";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='login'>Login";
      break;
    case "login":
      echo "<input type='radio' name='{$key[0]}' value='none'>None";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='cram-md5'>CRAM-MD5";
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='{$key[0]}' value='login' CHECKED>Login";
      break;
    default:
      if ($key[0]=="mailhub") {
        $hub = explode(':', $key[1]);
        echo "<input type='text' name='#server' id='server' value='{$hub[0]}'></td></tr>";
        echo "<tr><td>Mail server port: ";
        echo "<img src='$image/information.png' class='tooltip' title='Port for mail server.<br>Usually ports 25, 465 or 587'></td><td>";
        echo "<input type='text' name='#port' id='port' value='{$hub[1]}'>";
      } else {
        echo "<input type='".($key[0]=="AuthPass" ? "password" : "text")."' name='{$key[0]}' value='{$key[1]}'>";
      }
    }
    echo "</td></tr>";
  }
}
?>
<tr><td></td><td><input type="submit" name="#apply" value="Apply"><button type="button" onclick="done();">Done</button></td>
<td id="testresult" style="font-size:13px;text-align:right"><input type="button" id="test" value="Test">Test Result:<span class="orange">Unknown</span></td></tr>
<tr><td style="font-weight:normal;font-style:italic;font-size:smaller"><?=exec("/usr/sbin/ssmtp -V 2>&1|awk '{print $1\" version: \"$2}'")?></td><td></td></tr>
</table>
</form>