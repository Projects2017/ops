<?php
require("database.php");
require("secure.php");

$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body>
<?php require('menu.php'); ?>
<table width="70%" border="0" align="center" cellpadding="5" cellspacing="0">
<?php
$sql = "SELECT forms.minimum, vendors.name, vendors.ID, vendors.fax FROM forms
 LEFT JOIN vendors ON vendors.ID=forms.vendor WHERE forms.ID=$ID";
$query = mysql_query($sql);
checkDBError();
if ($result = mysql_fetch_Array($query)) {
	$vendorid = $result['ID'];
	$vendorname = $result['name'];
}
?>

    <tr><td colspan="5" height="30" class="fat_black"><?php echo $vendorname; ?> Orders</td></tr>

<?php
if (file_exists($basedir."logos/".$vendorid.".jpg")) {
	echo "<tr><td colspan=\"5\"><img src=\"logos/".$vendorid.".jpg\"></td></tr>";
}

$sql = "SELECT order_forms.ordered, order_forms.processed, order_forms.ID, snapshot_forms.name FROM order_forms INNER JOIN snapshot_forms ON order_forms.snapshot_form = snapshot_forms.id WHERE order_forms.form=$ID AND order_forms.user=$userid AND order_forms.deleted=0";
//echo "<tr><td colspan=\"6\" class=\"text_12\"><b>$sql</b></td></tr>";
$query = mysql_query($sql);
checkDBError($sql);
$bo_sql = "SELECT backorder.date, backorder.id, forms.name FROM backorder INNER JOIN forms ON backorder.form_id = forms.ID WHERE backorder.form_id=$ID AND backorder.user_id=$userid AND backorder.completed=0 AND backorder.canceled=0";
$bo_query = mysql_query($bo_sql);
checkDBerror($bo_sql);
if ((mysql_num_rows($query) + mysql_num_rows($bo_query)) == 0)
	echo "<tr><td colspan=\"6\" class=\"text_12\" bgcolor=\"#FFFFFF\">There are no orders for this vendor.</td></tr>";
else {
?>

  <tr> 
    <td class="fat_black_12" bgcolor="#CCCC99">Form</td>
	<td class="fat_black_12" bgcolor="#CCCC99">Date</td>
	<td bgcolor="#CCCC99" class="fat_black_12">Proc'd</td>
	<td bgcolor="#CCCC99" class="fat_black_12">PO #</td>
	<td class="fat_black_12" bgcolor="#CCCC99">&nbsp;</td>
  </tr>
<?php
        // Backorders
        while ($result = mysql_fetch_array($bo_query)) {
         ?>
  <tr>
    <td bgcolor="#EEEEEE" class="text_12"><?php echo $result['name']; ?></td>
	<td bgcolor="#EEEEEE" class="text_12"><?php echo date('m/d/Y', strtotime($result['date'])); ?></td>
	<td bgcolor="#EEEEEE" class="text_12">B</td>
	<td bgcolor="#EEEEEE" class="text_12">BO#
	<?php
	$bo = ($result['id'] + 1000);
	echo $bo;
	?>
    </td>
	<td bgcolor="#EEEEEE" class="text_12"> <a href="backorder_view.php?bo=<?php echo $bo; ?>">Details</a>
    </td>
  </tr>
  <?php
        }
        // Actual Orders
	while ($result = mysql_fetch_array($query)) {
?>
  <tr> 
    <td bgcolor="#FFFFFF" class="text_12"><?php echo $result['name']; ?></td>
	<td bgcolor="#FFFFFF" class="text_12"><?php echo date('m/d/Y', strtotime($result['ordered'])); ?></td>
	<td bgcolor="#FFFFFF" class="text_12"><?php echo $result['processed']; ?></td>
	<td bgcolor="#FFFFFF" class="text_12"> 
	<?php
	$po = ($result['ID'] + 1000);
	echo $po;
	?>
    </td>
	<td bgcolor="#FFFFFF" class="text_12"> <a href="orders-details.php?po=<?php echo $po; ?>">Details</a>
    </td>
  </tr>
  <?php
	}
}
mysql_close($link);
?>
  <tr>
    <td colspan="5" class="text_12">[<a href="javascript:history.back();">Back 
      to Vendor List</a>]</td>
  </tr>
</table>
</body>
</html>
