<?php
require("database.php");
require("secure.php");
require("menu.php");

$sql = "SELECT * FROM `login_session` ORDER BY `lastaccess` DESC";
$query = mysql_query($sql);
checkDBerror($sql);
$sessions = array();
while ($session = mysql_fetch_assoc($query)) {
    $row = array();
    $row['session'] = $session;
    $sql = "SELECT * FROM `login` WHERE `id` = '".$session['login_id']."'";
    $query2 = mysql_query($sql);
    checkDBerror($sql);
    if ($login = mysql_fetch_array($query2)) {
        $row['login'] = $login;
    } else {
        continue;
    }
    if ($login['type'] == 'V') {
        $sql = "SELECT * FROM `vendor` WHERE `id` = '".$login['relation_id']."'";
        $query2 = mysql_query($sql);
        checkDBerror($sql);
        if ($vendor = mysql_fetch_assoc($query2)) {
            $dealer = array();
            $dealer['last_name'] = 'Vendor ('.$vendor['type'].')';
            $dealer['first_name'] = $vendor['name'];
        } else {
            continue;
        }
    } else {
        $sql = "SELECT `last_name`, `first_name` FROM `users` WHERE `ID` = '".$login['relation_id']."'";
        $query2 = mysql_query($sql);
        checkDBerror($sql);
        $dealer = mysql_fetch_assoc($query2);
        if (!$dealer) continue;
    }
    $row['dealer'] = $dealer;
    $sessions[] = $row;
}
?>
<table id="loginsessions" border="0" cellspacing="0" cellpadding="5" width="90%" align="center"<?php
if ($nopages || $allpages||$totalpages == 1) { ?> class="sortable"<?php }
?>>
  <tr class="skiptop">
    <td colspan="8" align="center"><h3>Current Login Sessions</h3></td>
  </tr>
  <tr bgcolor="#fcfcfc">
    <td bgcolor="#CCCC99" class="fat_black_12">Dealer Name</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Dealer Location</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Username</td>
    <td bgcolor="#CCCC99" class="fat_black_12">IP Address</td>
    <td bgcolor="#CCCC99" class="fat_black_12">Last Access</td>
  </tr>
<?php foreach ($sessions as $session): ?>
  <tr valign="top">
      <td bgcolor="#FFFFFF" class="text_12"><?php echo htmlentities($session['dealer']['last_name']); ?></td>
      <td bgcolor="#FFFFFF" class="text_12"><?php echo htmlentities($session['dealer']['first_name']); ?></td>
      <td bgcolor="#FFFFFF" class="text_12"><?php echo htmlentities($session['login']['username']); ?></td>
      <td bgcolor="#FFFFFF" class="text_12"><?php echo htmlentities($session['session']['ip']); ?></td>
      <td bgcolor="#FFFFFF" class="text_12"><?php echo htmlentities($session['session']['lastaccess']); ?></td>
  </tr>
<?php endforeach; ?>
</table>
