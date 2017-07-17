<?php
// showform.php
// displays a list of BOLs & credit requests for an order for the user to choose

require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
if(!isset($_GET)) {
	die('This page requires data to be sent via GET.');
}
if(!$_GET['id']) {
  die("PO ID must be sent via GET.");
} else {
  $po_id = $_GET['id'];
  $print_po_id = $po_id + 1000;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>RFE Shipping Bills of Lading for PO # <?php echo $print_po_id; ?></title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="WebDesign">
	<link type="text/css" href="css/styles.css" rel="stylesheet">
</head>
<body>
<?php include("../menu.php"); ?>
<p align="center" style="font-size: 24px; font-weight: bold;">Royal Furniture Express Shipping<br />Bills of Lading for PO # <?php echo $print_po_id; ?></p>
<?php if (secure_is_admin()) { ?>
	<form name="editfreight" action="editfreight.php" method="post"><input type="hidden" name="chosen" value=""><input type="hidden" name="po_id" value="<?php echo $po_id; ?>">
<?php } ?>
<table width="80%" align="center" cellpadding="2" cellspacing="2" border="0">
<?php if($_COOKIE['bol_msg']) {
  echo "<tr><td align=\"center\" colspan=\"8\" class=\"text_12\" style=\"font-size: 14px; color: red\">".$_COOKIE['bol_msg']."</td></tr>\n";
} ?>
<tr><td align="center" colspan="9" class="text_12" style="font-size: 14px"><a href="shipping.php">Back to Queue</a></td></tr>
<tr>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">ID</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Sets</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Matts</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Boxes</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Ship Date</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Weight (lbs.)</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Tracking Number</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">Freight</th>
  <th scope="col" class="text_12" style="font-weight: bold; font-size: 14px">View</th>
</tr>
<?php
// first get the actual PO # as stored in the db
$sq = "SELECT orig_po, po FROM BoL_queue WHERE COALESCE(orig_po, po) = $po_id";
$qu = mysql_query($sq);
$res = mysql_fetch_assoc($qu);
if(is_null($res['orig_po'])) { $ponum = $res['po']; } else { $ponum = $res['orig_po']; }
$i = 0;
$sq = "SELECT DISTINCT bol_id FROM BoL_items WHERE po = $ponum AND type = 'bol'";
$qu = mysql_query($sq);
while($res = mysql_fetch_row($qu)) {
	$sql = "SELECT ID, setamt, mattamt, boxamt, carrier, shipdate, freight, trackingnum, weight FROM BoL_forms WHERE ID = {$res[0]}";
	$query = mysql_query($sql);
	checkdberror($sql);
	while($result = mysql_fetch_assoc($query)) {
  		$i++;
  		?>
<tr>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $res[0]+1000; ?></td>
<?php if (secure_is_admin()) { ?>
  <input type="hidden" name="<?php echo $i; ?>" value="<?php echo $result['ID']; ?>">
<?php } ?>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['setamt']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['mattamt']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['boxamt']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['shipdate']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php echo $result['weight']; ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><?php if (secure_is_admin()) { ?><input type="text" size="25" name="trackingnum<?php echo $i; ?>" value="<?php }
  echo stripslashes(htmlentities($result['trackingnum']));
  if (secure_is_admin()) { echo '">'; }
?></td>
  <td align="center" class="text_12" style="font-size: 14px">$<?php if (secure_is_admin()) { ?><input type="text" size="8" name="freight<?php echo $i; ?>" value="<?php }
  echo number_format($result['freight'], 2);
  if (secure_is_admin()) { echo "\">&nbsp;<input type=\"submit\" name=\"apply\" value=\"Apply\" onclick=\"document.editfreight.chosen.value = '$i'\"";
  if($result['freight']!=0 && $result['trackingnum']!='') { echo ' disabled'; }
  echo ">";
  } ?></td>
  <td align="center" class="text_12" style="font-size: 14px"><a href="viewbol.php?id=<?php echo $res[0]; ?>">View BoL</a></td>
</tr>
<?php }
}
?>
</table>
<?php if (secure_is_admin()) { echo "</form>"; } ?>
</body>
</html>