<?php
// printtargetpacking.php
require_once('../database.php');
$duallogin = 1;
include_once("../vendorsecure.php");
if (!$vendorid)
   include_once("../secure.php");
require_once('inc_shipping.php');
require_once(dirname(__FILE__).'/../include/edi/bo_shippingedi.php');
// this is an EDI order, let's grab some important info
// start w/ the objects
require_once(dirname(__FILE__).'/../include/edi/edi.php');
// this should return $bol & $po
parse_str($_SERVER['QUERY_STRING']);
// get what info we can from the dbs
$sql = "SELECT * FROM BoL_forms WHERE ID = $bol";
$query = mysql_query($sql);
checkdberror($sql);
$return = mysql_fetch_assoc($query);
$ediobj = new Edi();
$ediobj->LoadFromPO($po);
$order = $ediobj->Process();
makeShippingEdi($po, $bol)
/*
// getthe carrier name
$sql = "SELECT name FROM wm_shipcodes WHERE code = ".ltrim($order->mShipTo->mMarkedShippingCode, '0');
$query = mysql_query($sql);
checkdberror($sql);
$ret = mysql_fetch_assoc($query);
$carrierName = $ret['name'];
*/
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<script src="shipping.js" language="javascript" type="text/javascript"></script>
<script src="bol.js" language="javascript" type="text/javascript"></script>
</head>
<script language="javascript" type="text/javascript">
<?php for($i = 0; $i < count($order->mShipping->mPackages); $i++)
{
?>
	printTarget(
		"<?php= addslashes($order->mCustomerOrderNumber) ?>",
		"<?php= addslashes(date('m/d/Y', strtotime($return['shipdate']))) ?>",
		"<?php= addslashes($carrierName) ?>",
		"<?php= addslashes($order->mShipping->mPackages[$i]->mPackageIdentifier) ?>",
		"<?php= addslashes($order->mBillTo->mName) ?>",
		"<?php= addslashes($order->mShipTo->mName) ?>",
		"<?php= addslashes($order->mBillTo->mAddress1)."\\n".
(!is_null($order->mBillTo->mAddress2) ? (addslashes($order->mBillTo->mAddress2)."\\n") : '').
addslashes($order->mBillTo->mCity).', '.addslashes($order->mBillTo->mState).' '.addslashes($order->mBillTo->mPostal)."\\n".addslashes($order->mBillTo->mCountry) ?>",
		"<?php= addslashes($order->mShipTo->mAddress1)."\\n".
(!is_null($order->mShipTo->mAddress2) ? (addslashes($order->mShipTo->mAddress2)."\\n") : '').
addslashes($order->mShipTo->mCity).', '.addslashes($order->mShipTo->mState).' '.addslashes($order->mShipTo->mPostal)."\\n".addslashes($order->mShipTo->mCountry) ?>",
		"$<?php= is_null($order->mSubtotal) ? addslashes(number_format($order->mRetailTotal - $order->mShippingCost - $order->mTaxes, 2)) : addslashes(number_format($order->mSubtotal, 2)) ?>", 
		"$<?php= addslashes(number_format($order->mShippingCost, 2)) ?>", 
		"$<?php= is_null($order->mTaxes) ? '0.00' : addslashes(number_format($order->mTaxes, 2)) ?>", 
		"$<?php= addslashes(number_format($order->mRetailTotal, 2)) ?>",
		"<?php= addslashes($order->mShipTo->mReturnReferenceNumber) ?>",
		"<?php
if(!is_null($order->mStoreNumber))
{
	echo addslashes($order->mShipping->mPackages[$i]->mBarCode);
} ?>",
		"<?php= addslashes($order->mShipping->mRetailerPO) ?>",
		[<?php
// go through each item and add up dupes...should work
for($q = 0; $q < count($order->mShipping->mPackages[$i]->mItems) - 1; $q++)
{
	// check the next one to see if it's the same item, add quants together
	if($order->mShipping->mPackages[$i]->mItems[$q]->mUPC == $order->mShipping->mPackages[$i]->mItems[$q+1]->mUPC && $order->mShipping->mPackages[$i]->mItems[$q]->mDescription == $order->mShipping->mPackages[$i]->mItems[$q+1]->mDescription && $order->mShipping->mPackages[$i]->mItems[$q]->mPrice == $order->mShipping->mPackages[$i]->mItems[$q+1]->mPrice)
	{
		$order->mShipping->mPackages[$i]->mItems[$q]->mQtyShipped += $order->mShipping->mPackages[$i]->mItems[$q+1]->mQtyShipped;
		unset($order->mShipping->mPackages[$i]->mItems[$q+1]);
	}
}
$first = false;
foreach($order->mShipping->mPackages[$i]->mItems as $item)
{
	if($first) echo ", ";
	echo "\n\t\t\t['".addslashes($item->mQtyShipped)."', \"".addslashes($item->mUPC)."\", \"".addslashes($item->mDescription)."\", \"$".
	addslashes(number_format($item->mRetailPrice, 2))."\", \"$".addslashes(number_format(($item->mRetailPrice * $item->mQtyShipped), 2))."\"]";
	$first = true;
}
echo "\n\t\t]";
?>
);
<?php
}
?>

window.location = 'viewbol.php?id=<?php= $bol ?>';
</script>
<body>
</body>
</html>