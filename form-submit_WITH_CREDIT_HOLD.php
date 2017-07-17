<?php
require("database.php");
require("inc_content.php");
require("secure.php");

$po_obtained = "N";
$user = $userid;

/*
$query = mysql_query("SELECT credit_limit FROM users WHERE ID=$user");
checkDBError();
if ($result = mysql_fetch_array($query))
	$credit_limit =  $result['credit_limit'];

$user_balance = 0; //CALCULATE THIS VALUE

if ($user_balance > $credit_limit)
	$credit_hold = "Y";
*/

$sql = "select credit_hold from users where ID=$user";
$query = mysql_query($sql);
checkDBError();
if ($result = mysql_fetch_array($query))
	$credit_hold =  $result['credit_hold'];

if ($credit_hold <> "Y") {

	for($c = 0; $c < $num_of_items; $c++)
	{
		if (${"item".$c} == "")
			break;

		if (${"setqty".$c} != 0 || ${"mattqty".$c} != 0 || ${"qty".$c} != 0 )
		{
			$setqty = ${"setqty".$c};
			$mattqty = ${"mattqty".$c};
			$qty = ${"qty".$c};
			$user = $userid;
			$item = ${"item".$c};
			$ordered = date("Ymd");
			$ordered_time = date("H:i:s"); // "00:00:00"

			if ($po_obtained == "N") {
				$processed = "N";
				$sql = buildInsertQuery("order_forms");
				mysql_query($sql);
				checkDBError();
				$po_id = mysql_insert_id();
				$po_obtained = "Y";
			}

			$sql = buildInsertQuery("orders");
			mysql_query($sql);
			checkDBError();

			/* begin snapshot addition */
			$orders_id = mysql_insert_id();
			$sql = "SELECT form_items.partno, form_items.description, form_items.price,
			 form_items.set_, form_items.matt, form_items.box, form_items.size, form_items.color, form_headers.header
			 FROM form_items INNER JOIN form_headers ON form_items.header = form_headers.ID
			 WHERE form_items.ID=$item";
			$snap_query = mysql_query($sql);
			checkDBError();
			if ($result = mysql_fetch_array($snap_query)) {
				$partno = addslashes($result[0]);
				$description = addslashes($result[1]);
				$price = $result[2];
				$set_ = $result[3];
				$matt = $result[4];
				$box = $result[5];
				$size = $result[6];
				$color = $result[7];
				$header = addslashes($result[8]);
			}
			$snapshot_sql = buildInsertQuery("order_snapshot");
			mysql_query($snapshot_sql);
			checkDBError();
			/* end snapshot addition */
		}
	}

}
?>
<html>
<head>
	<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="EDECDA" leftmargin="10" topmargin="10" marginwidth="10" marginheight="10">
<?php
if ($credit_hold == "Y") {
	echo "<table border=\"0\" width=\"50%\" align=\"center\"><tr><td><p>I'm sorry, but your order cannot be processed at this time. You are on Credit Hold
	for non-payment of past orders. Please wire/pay immediately so that your orders are not further
	delayed. If you feel that this message was given in error, please contact Amy Bowen
	immediately at (614) 538-0675.</p></td></tr></table>";
}
else {
?>

<p align="center" class="fat_black">Your Order Has Been Submitted!</p>

<?php
$section = "web";

$po = ($po_id+1000);
echo OrderForWeb($po,$section);
?>

<div align="center"><form name="form2" method="post" action="javascript:window.print();">
<input type="submit" name="Submit" value="Print Order">
</form></div>

<?php
}
mysql_close($link);
?>

<p align="center"><a href="selectvendor.php">Select Another Vendor</a></p>
</body>
</html>
