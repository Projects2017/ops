<?php
require("database.php");
require("vendorsecure.php");
require("form.inc.php");
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<BR><BR>
<FONT FACE=ARIAL><B>SHIPPING SYSTEM</B><BR>
<a href="/shipping/shipping.php">Bills of Lading & Credit Requests</a>
<BR><BR>
<BLOCKQUOTE class="float">
<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Claims Type</td>
    <td class="fat_black_12">Waiting</td>
    <td class="fat_black_12">Open</td>
  </tr>
  <?php
  	$claims = formsummaries();
  	foreach($claims as $y => $x) {
  		$forminfo = forminfo($y,1);
  ?>
  <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php echo $forminfo['nicename']; ?></td>
    <td class="text_12"><?php echo $x['own']; ?></td>
    <td class="text_12"><?php echo $x['open']; ?></td>
  </tr>
  <?php } ?>
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">&nbsp;</td>
    <td class="fat_black_12">Pending</td>
    <td class="fat_black_12">Completed</td>
  </tr>
  <?php
  $sql = "SELECT forms.id FROM vlogin_access
 LEFT JOIN forms ON forms.vendor=vlogin_access.vendor WHERE vlogin_access.user=$vendorid";
 $query = mysql_query($sql);
 checkDBerror($sql);
 $accessforms = array();
 while ($row = mysql_fetch_assoc($query)) {
 	$accessforms[] = $row['id'];
 }
 
 if (count($accessforms) > 1) {
 	$where = "IN (".implode(",",$accessforms).")";
 } elseif ($accessforms) {
 	$where = "= '".$accessforms[0]."'";
 } else {
 	$where = "= 0";
 }
 
 $sql = "SELECT COUNT(id) AS id FROM `backorder` WHERE form_id ".$where." AND completed = 0 AND canceled = 0";
 $result = mysql_query($sql);
 checkDBerror($sql);
 if ($row = mysql_fetch_assoc($result)) {
 	$pending = $row['id'];
 } else {
 	$pending = 0;
 }
 
  $sql = "SELECT COUNT(id) AS id FROM `backorder` WHERE form_id ".$where." AND completed = 1";
 $result = mysql_query($sql);
 checkDBerror($sql);
 if ($row = mysql_fetch_assoc($result)) {
 	$completed = $row['id'];
 } else {
 	$completed = 0;
 }
  ?>
  <tr bgcolor="#FFFFFF">
    <td class="text_12">Backorders</td>
    <td class="text_12"><?php echo $pending; ?></td>
    <td class="text_12"><?php echo $completed; ?></td>
  </tr>
</table>
</BLOCKQUOTE>
<?php
$formlist = formlistforms();
?>
<FONT FACE=ARIAL><B>CLAIMS DATABASE</B><BR>
<?php
$i = 0;
foreach ($formlist as $eachform) {
		$i++;
		$forminfotemp = forminfo($eachform,1);
		if ($i != 1) {
		  ?> | <?php 
		}
		?><a href="form.php?form=<?php echo $eachform; ?>&action=display"><?php echo $forminfotemp['nicename']; ?></a><?php
	}
?><BR><BR>
<?php
$sql = "select * from users where ID=".$vendorid;
$query = mysql_query($sql);
checkDBError($sql);

$sql = "SELECT vlogin_access.*, vendors.name FROM vlogin_access
 LEFT JOIN vendors ON vendors.ID=vlogin_access.vendor WHERE vlogin_access.user=$vendorid ORDER BY vendors.name";
$query = mysql_query($sql);
checkDBError($sql);
if (mysql_num_rows($query) == 0)
	echo "<p align=\"center\">You do not have access to any vendors!</p>";
else {
?>
<B>SELECT AN ORDER FORM TO CHANGE STOCK STATUS</B><BR>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Vendor</td>
    <td class="fat_black_12">Form</td>
    <td class="fat_black_12">Backorders</td>
  </tr>
  <?php
	while($result = mysql_fetch_array($query)) {
		$sql = "select * from forms where vendor='".$result['vendor']."' AND alloworder = 'Y'";
		$query2 = mysql_query($sql);
		checkDBError($sql);
		while ($result2 = mysql_fetch_array($query2)) {
			$sql = "SELECT COUNT(`id`) as `count` FROM `backorder` WHERE `form_id` = '".$result2['ID']."' AND `completed` = 0 AND `canceled` = 0";
			$query3 = mysql_query($sql);
			checkDBerror($sql);
			if ($query3 = mysql_fetch_assoc($query3)) {
				if ($query3['count'] >= 1)
					$query3 = " (".$query3['count'].")";
				else
					$query3 = "";
			} else {
				$query3 = "";
			}
	?>
  <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php echo $result['name'] ?></td>
    <td class="text_12"><a href="stock-form.php?ID=<?php echo $result2['ID'] ?>"><?php echo $result2['name'] ?></a></td>
    <td class="text_12"><a href="backorder-list.php?form=<?php echo $result2['ID'] ?>">view backorders<?php echo $query3; ?></a></td>
  </tr> 
  <?php
		}
	}
}
mysql_close($link);
?>
<tr>
    <td><img src="images/furniture1.jpg" width="238" height="150"></td>
    <td align="center"><img src="images/furniture2.jpg" width="238" height="150"></td>
    <td><img src="images/furniture3.jpg" width="238" height="150"></td>
  </tr>
</table>
</body>
</html>
