<?php
// printwalmartpacking.php
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
$orderobj = $ediobj->Process();
//makeShippingEdi($po, $bol)
// getthe carrier name
switch($EdiVendor->mTypeCode)
{
	case 'WMI':
		$sql = "SELECT name FROM wm_shipcodes WHERE code = '".ltrim($orderobj->mShipCode, "0")."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		$carrierName = $return['name'];
		$vendor = 'walmart';
		break;
	case 'TVI':
		switch($orderobj->mShipCode)
		{
			case 'UPSGS':
				$set_shipcode = 'UPSET_CG';
				break;
			case 'UPS1S':
				$set_shipcode = 'UPSET_ND';
				break;
			case 'UPS2S':
				$set_shipcode = 'UPSET_SE';
				break;
			case 'UPSG':
				$set_shipcode = 'UPSN_CG';
				break;
			default:
				$set_shipcode = $orderobj->mShipCode;
		}
		// now get the shipcode info
		$sql = "SELECT ch_shipcodes.shipcode, ch_shipcodes.description, shipping_carriers.name, COALESCE(shipping_carriers.shortname, shipping_carriers.name)
		AS abbrev FROM shipping_carriers LEFT OUTER JOIN ch_shipcodes ON UCASE(ch_shipcodes.description) LIKE CONCAT('%', UCASE(COALESCE(shipping_carriers.shortname, shipping_carriers.name)), '%') AND 
		ch_shipcodes.selectable = '1'";
		$sql .= " WHERE ch_shipcodes.shipcode = '$set_shipcode'";
		$que = queryFailOnZero($sql, "Unfortunately there was an error while retrieving ship code information. Please let the system administrator know.");
		$carrierName = $res['name'];
		break;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<script src="../include/printing.js" language="javascript" type="text/javascript"></script>
</head>
<script language="javascript" type="text/javascript">
<?php

// now we call the proper JS functions...

switch($vendor)
{
	case 'walmart':
		foreach($orderobj->mShipping->mPackages as $thispack)
		{
			?>printWalmart("<?php= addslashes($orderobj->mCustomerOrderNumber) ?>","<?php= addslashes(date('m/d/Y', strtotime($orderobj->mShipping->mShipDateTime))) ?>","<?php= addslashes($carrierName) ?>","<?php= addslashes($thispack->mPackageIdentifier) ?>","<?php= addslashes($orderobj->mBillTo->mName) ?>","<?php= addslashes($orderobj->mShipTo->mName) ?>",	"<?php= addslashes($orderobj->mBillTo->mAddress1)."\\n".(!is_null($orderobj->mBillTo->mAddress2) ? (addslashes($orderobj->mBillTo->mAddress2)."\\n") : '').addslashes($orderobj->mBillTo->mCity).', '.addslashes($orderobj->mBillTo->mState).' '.addslashes($orderobj->mBillTo->mPostal)."\\n".addslashes($orderobj->mBillTo->mCountry) ?>","<?php= addslashes($orderobj->mShipTo->mAddress1)."\\n".(!is_null($orderobj->mShipTo->mAddress2) ? (addslashes($orderobj->mShipTo->mAddress2)."\\n") : '').addslashes($orderobj->mShipTo->mCity).', '.addslashes($orderobj->mShipTo->mState).' '.addslashes($orderobj->mShipTo->mPostal)."\\n".addslashes($orderobj->mShipTo->mCountry) ?>","$<?php= is_null($orderobj->mSubtotal) ? addslashes(number_format($orderobj->mRetailTotal - $orderobj->mShippingCost - $orderobj->mTaxes, 2)) : addslashes(number_format($orderobj->mSubtotal, 2)) ?>",		"$<?php= addslashes(number_format($orderobj->mShippingCost, 2)) ?>","$<?php= is_null($orderobj->mTaxes) ? '0.00' : addslashes(number_format($orderobj->mTaxes, 2)) ?>",	"$<?php= addslashes(number_format($orderobj->mRetailTotal, 2)) ?>","<?php= addslashes($orderobj->mShipTo->mReturnReferenceNumber) ?>",	"<?php
			if(!is_null($orderobj->mStoreNumber))
			{
				echo addslashes($thispack->mBarCode);
			} ?>","<?php= addslashes($orderobj->mRetailPONumber) ?>",[<?php
			// go through each item and add up dupes...should work
			$thispack->OptimizeItems();
			/*
			foreach($thispack->mItems as $thisitem)
			{
				
				// check the next one to see if it's the same item, add quants together
				if($orderobj->mShipping->mPackages[$i]->mItems[$q]->mUPC == $orderobj->mShipping->mPackages[$i]->mItems[$q+1]->mUPC && $orderobj->mShipping->mPackages[$i]->mItems[$q]->mDescription == $orderobj->mShipping->mPackages[$i]->mItems[$q+1]->mDescription && $orderobj->mShipping->mPackages[$i]->mItems[$q]->mPrice == $orderobj->mShipping->mPackages[$i]->mItems[$q+1]->mPrice)
				{
					$orderobj->mShipping->mPackages[$i]->mItems[$q]->mQtyShipped += 	$orderobj->mShipping->mPackages[$i]->mItems[$q+1]->mQtyShipped;
					unset($orderobj->mShipping->mPackages[$i]->mItems[$q+1]);
				}
			}
			*/
			$first = false;
			foreach($thispack->mItems as $item)
			{
				if($first) echo ", ";
				echo "\n\t\t\t['".addslashes($item->mQtyShipped)."', \"".addslashes($item->mUPC)."\", \"".addslashes($item->mDescription)."\", \"$".
				addslashes(number_format($item->mRetailPrice, 2))."\", \"$".addslashes(number_format(($item->mRetailPrice * $item->mQtyShipped), 2))."\"]";
				$first = true;
			}
		echo "\n\t\t]";?>
		);
	<?php}
		break;

	case 'targettest':
	case 'target':
	case 'targetprod':
		foreach($ediobj->mEdiObject->mTransactions as $trans)
		{
			if($trans->mPONumber == $orderobj->mRetailPONumber)
			{
				foreach($orderobj->mShipping->mPackages as $thispack)
				{
					?>
					printTarget("<?php= addslashes($orderobj->mRetailPONumber) ?>", <?php if($type=='target') { ?>false<?php } else { ?>true<?php } ?>, "<?php= addslashes($orderobj->mAlternateOrderNumber) ?>", "<?php= $trans->mWarehouseCode ?>",
					"<?php=	addslashes($orderobj->mBillTo->mName)."\\n".addslashes($orderobj->mBillTo->mAddress1)."\\n" ?><?php
					if($orderobj->mBillTo->mAddress2 != '') echo addslashes($orderobj->mBillTo->mAddress2)."\\n";
					echo  addslashes($orderobj->mBillTo->mCity)."\\n".addslashes($orderobj->mBillTo->mState)."\\n".
					addslashes($orderobj->mBillTo->mPostal)."\\n".addslashes($orderobj->mBillTo->mCountry) ?>",
					"<?php=					addslashes($orderobj->mShipTo->mName)."\\n".addslashes($orderobj->mShipTo->mAddress1)."\\n" ?><?php
					if($orderobj->mShipTo->mAddress2 != '') echo addslashes($orderobj->mShipTo->mAddress2)."\\n";
					echo addslashes($orderobj->mShipTo->mCity)."\\n".addslashes($orderobj->mShipTo->mState)."\\n".
					addslashes($orderobj->mShipTo->mPostal)."\\n".addslashes($orderobj->mShipTo->mCountry) ?>",
					"<?php= addslashes($trans->mMessages->PrintMessages()) ?>",
					"<?php= addslashes($trans->mOrderStatusMessage) ?>",
					[<?php
					$firstone = false;
					foreach($thispack->mItems as $thisitem)
					{
						if($firstone==true) {?>, <?php};
						?>["<?php= $orderobj->mShipCode ?>","<?php= $trans->mGiftWrapCode ?>","<?php= $thisitem->mQtyShipped ?>","<?php= $thisitem->mRetailerPartNumber ?>", "<?php= addslashes($thisitem->mDescription) ?>","<?php= $thisitem->mSKU ?>","<?php= $thisitem->mUPC ?>","<?php= $trans->mReturnMethod ?>","<?php= $thisitem->mItemMessage ?>","<?php= $thisitem->mASIN ?>"]<?php
						$firstone = true;
					}
					echo "]);\n";
				}
			}
		}
		break;
}
?>
//window.location = 'viewbol.php?id=<?php= $bol ?>';
</script>
<body>
</body>
</html>