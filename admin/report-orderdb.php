<?php
require("database.php");
$user = $_REQUEST['user'];
require("secure.php");
require("../form.inc.php");

$po_id = $po-1000;

if ($action == 'fakeautolog') {
	$sql = "UPDATE order_forms SET email_vendor='".date("Y-m-d")."' WHERE ID='".$po_id."'";
	mysql_query($sql);
	checkDBError();
	echo "<html><body onLoad=\"opener.location.reload(true); window.close();\">Order AutoLogged</body></html>";
	die();
}

// $po_id = $po-1000;

if (!$vendors_id) {
    $sql = "SELECT vendors.ID, vendors.name FROM vendors INNER JOIN forms ON vendors.ID=forms.vendor WHERE forms.ID='".$form."'";
    $query = mysql_query($sql);
    checkDBError();
	if ($result = mysql_fetch_array($query)) {
		$vendors_name = $result['name'];
		$vendors_id = $result['ID'];
	}
} else {
	$sql = "SELECT vendors.name FROM vendors WHERE vendors.id = '".mysql_escape_string($vendors_id)."'";
    $query = mysql_query($sql);
    checkDBError();
    if ($result = mysql_fetch_array($query)) {
		$vendors_name = $result['name'];
    }
}

$sql = "SELECT oorvendor FROM `forms` WHERE `ID`='".$form."'";
$query = mysql_query($sql);
$form_array = mysql_fetch_assoc($query);

checkDBError();
// http://www.pmddealer.com/admin/report-orderdb.php?po=36864&form=32&user=220&vendor=35864&date=2004-08-11
if ($vendor_id == 0) {
?>
<html>
<head>
<title> RSS Order AutoLog </title>
<link rel="stylesheet" href="../styles.css" type="text/css">
</head>
<body>
</td>
</tr>
</table>
<form id="fakeautolog" name="fakeautolog">
<input type="hidden" name="po" value="<?php echo $po; ?>">
<input type="hidden" name="action" value="fakeautolog">
<input type="hidden" name="vendor_id" value="1">
</form>
<form>
<table>
<tr>
<td class="fat_black_12">
 PO#
</td>
<td class="text_12">
 <?php echo $po; ?><input type="hidden" name="po" value="<?php echo $po; ?>">
</td>
</tr>
<tr>
<td class="fat_black_12">
 Dealer:
</td>
<td class="text_12">
 <?php
  $userinfo = db_user_getuserinfo($username2);
  echo $userinfo["first_name"]." ".$userinfo["last_name"]; 
 ?><input type="hidden" name="username2" value="<?php echo $username2; ?>">
</td>
</tr>
<tr>
<td class="fat_black_12">
 Date:
</td>
<td class="text_12">
 <?php echo $date; ?><input type="hidden" name="date" value="<?php echo $date; ?>">
</td>
</tr>
<tr>
<td class="fat_black_12">
 Form Vendor:
</td>
<td class="text_12">
 <?php echo $vendors_name; ?><input type="hidden" name="vendors_id" value="<?php echo $vendors_id; ?>">
</td>
</tr>
<tr>
<td class="fat_black_12">
 DB Vendor:
</td>
<td class="text_12">
 <SELECT name="vendor_id" class="text_12"><?php
	$vendorlist = db_vendor_getlist();
    ?><OPTION SELECTED>Select Matching Vendor</OPTION>
	<?php
	foreach ($vendorlist as $value) {
		echo "<OPTION VALUE=\"".$value[id]."\"";
		if ($form_array['oorvendor'] == $value[id]) echo " SELECTED";
		echo ">".$value[name]."</OPTION>\n";
	}
	echo "</SELECT>";
	?>
</td>
</tr>
<tr>
<td class="fat_black_12">
 Confirmed:
</td>
<td class="text_12">
 <input type="checkbox" name="confirmed" class="text_12" <?php if($confirmed) echo "CHECKED"; else echo ""; ?>>
</td>
</tr>
<tr>
<td class="fat_black_12">
 Tracking #:
</td>
<td class="text_12">
 <input type="text" name="track" value="<?php echo $track; ?>" class="text_12">
</td>
</tr>
<tr>
<td class="fat_black_12">
 Shipping Info:
</td>
<td class="text_12">
 <input type="text" name="shipinfo" value="<?php echo $shipinfo; ?>" class="text_12">
</td>
</tr>
<tr>

<td class="fat_black_12">
 Cubic feet:
</td>
<td class="text_12">
 <input type="hidden" name="cubic_ft" value="<?php
	if ($cubic_ft == "")
		$cubic_ft = db_order_get_volume($po_id);
	echo $cubic_ft;
?>" class="text_12"><?php echo $cubic_ft; ?>
</td>
</tr>

</table>
<INPUT type="submit" value="Submit Order">
<INPUT type="button" value="Cancel" onClick="window.close()">
<INPUT type="button" value="Fake Autolog" onClick="if (confirm('Are you sure you want to fake logging?')) document.fakeautolog.submit()">
</form>
<?php
} elseif ($vendor_id != 0) {
	$data = array(
		"user_id" => $username2,
		"vendor_id" => $vendor_id,
		"po" => $po,
		"date" => $date,
		"factory_confirm" => $confirmed,
		"tracking" => $track,
		"shipping_info" => $shipinfo,
		"cubic_ft" => $cubic_ft
	);
	$success = forminsert("order", $data);
	//$success = 0;
	if (!$success) {
		$sql = "UPDATE order_forms SET email_vendor='".date("Y-m-d")."' WHERE ID='".$po_id."'";
	    mysql_query($sql);
	    checkDBError();
		echo "<html><body onLoad=\"opener.location.reload(true); window.close();\">Order AutoLogged</body></html>";
	} else {
		echo "Autologger Error\n";
		print_r($success);
	}
}
?>
