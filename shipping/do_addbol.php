<?php
if(!isset($_POST)) {
	die('This page requires data to be sent via POST.');
}
require_once('../database.php');
$duallogin = 1;
include_once("../vendorsecure.php");
if (!isset($vendorid)) include("../secure.php");
require_once('inc_shipping.php');
require_once('inc_postbol.php');
require_once('../include/edi/edi.php');
require_once('../include/edi/bo_shippingedi.php'); // adding the EDI generation object
// we need to find out if a shipping agent has been chosen; if so, redirect back to addbol & display with the agent's info
$makeEdi = isset($_POST['edi']) && $_POST['edi'] == 1 ? true : false;

if(is_numeric($_POST['shipping_agent']) && !$_POST['doit'])
{
	foreach($_POST as $key => $value)
	{
		setcookie($key, $value);
	}
	header('Location: addbol.php');
	exit();
}

//print_r($_POST);

// check to see if the carrier has been chosen; if not, go back to the page and give the carrier field focus
if($_POST['carrier_name']=='n/a') // n/a = none selected
{
	foreach($_POST as $key => $value)
	{
		setcookie($key, $value);
	}
	setcookie('nocarrier',1);
	header('Location: addbol.php?id='.($po_id+1000));
	exit();
}
// now it's time to add the BoL itself before we print
// first, lets throw the $_POST'd vars into variables
if($_POST['weight'] && !is_numeric($_POST['weight'])) {
	setcookie('BoL_msg', "Weight must be only a number", time()+5);
	header("Location: shipping.php");
	exit();
}
$carrier_name = stripslashes($_POST['carrier_name']);
$orig_carrier_code = isset($_POST['orig_carrier_code']) ? stripslashes($_POST['orig_carrier_code']) : '';
$tracking_num = stripslashes($_POST['tracking_num']);
$set_shipcode = isset($_POST['shipcode']) ? stripslashes($_POST['shipcode']) : '';
$ship_date = date('Y-m-d', strtotime($_POST['ship_date']));
$rows = $_POST['rows'];
$service_level = $_POST['service_level'];
if($_POST['snapshotuser'])
{
	$snapshotuser = $_POST['snapshotuser'];
	$chorder = true;
}
else
{
	$chorder = false;
}
$po_comment = addslashes($_POST['po_comment']);
$multipo = false;
for ($i=1; $i<=$rows; $i++) {
	if(strlen($i)==2) { $i_compare = $i; } else { $i_compare = "0".$i; }
	foreach($_POST as $k => $v) {
		if (substr($k, 0, 3)=='_po') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$itempo[$i] = $v;
				$multipo = true;
			}
		}
		if (substr($k, 0, 7)=='_suffpo') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$itempo_suff[$i] = $v;
			}
		}
		if (substr($k, 0, 4)=='item') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$itemnum[$i] = $v;
			}
		}
		if (substr($k, 0, 6)=='weight') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$itemweight[$i] = $v;
			}
		}
		if (substr($k, 0, 6)=='lineid') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$lineid[$i] = $v;
			}
		}
		if (substr($k, 0, 5)=='class') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$itemclass[$i] = $v;
			}
		}
		if (substr($k, 0, 6)=='setqty') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$set = $v;
			}
		}
		if (substr($k, 0, 7)=='mattqty') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$matt = $v;
			}
		}
		if (substr($k, 0, 6)=='boxqty') {
			if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
				$box = $v;
			}
		}
	}
	$setqtytot += $set;
	$setqty[$i] = $set;
	$mattqtytot += $matt;
	$mattqty[$i] = $matt;
	$boxqtytot += $box;
	$boxqty[$i] = $box;
	unset($box);
}
$po_id = $_POST['po_id'];
$po_num_source = $_POST['po_source'];
$po_suffix = $_POST['po_suffix'];
$queue_id = $_POST['queue_id'];
if($makeEdi)
{
	// this is an EDI order, let's grab some important info
	// start w/ the objects
	// get what info we can from the dbs
	$ediobj = new Edi();
	$ediobj->LoadFromPO($po_id);
	if(!$ediobj->mRejected)
	{
		$orderobj = $ediobj->Process();
	}
}
preInsertCheck($po_id);
$ship_class = stripslashes($_POST['classtext']);
if($_POST['weight']=='') {
	$ship_weight = 0;
} else {
	$ship_weight = $_POST['weight'];
}
// Create the BoL_forms db entry
// start the creation
$sql = "INSERT INTO BoL_forms (po, po_suffix, queue_id, user_id, setamt, mattamt, boxamt, carrier, trackingnum, shipdate, weight, createdate, servicelevel, comment) VALUES (";
if(!$multipo) { $sql .= "$po_id, "; } else { $sql .="0, "; }
if($po_suffix=="") { $sql .= "NULL, "; } else { $sql .= "'$po_suffix', "; }
$sql .= $queue_id.", ";
if($chorder)
{
	$insertid = $snapshotuser;
}
else
{
	$insertid = $_POST['dbuser'];
}
$sql .= $insertid.", $setqtytot, $mattqtytot, $boxqtytot, '".mysql_escape_string($carrier_name)."', '".mysql_escape_string($tracking_num)."', '".$ship_date."', $ship_weight, NOW(), '$service_level', '$po_comment')";
$query = mysql_query($sql);
if(checkdberror($sql)) {
	sendError("processing the Bill of Lading", "BOL Creation - BOL Insert (add_bol.php line 119-120)", checkdberror($sql), 'shipping.php');
}
$bol_id = mysql_insert_id();
if($multipo) {
	$sql = "UPDATE BoL_forms SET multi_po = 1 WHERE ID = $bol_id";
	$que = mysql_query($sql);  
}

// create the shipping_packages entry/entries
// we need to see what the package identifier is for walmart shipping only
// for those shippers id'd as "freight", we use the BOL ID
// for those shippers not id'd as "freight", we use the shipper tracking number
// we default to FedEx/UPS mode for all orders
$fedexups = true;
if(is_numeric($carrier_name))
{
	$fedexups = false;
	$freightshipper = false;
	// we need to see if the carrier is a UPS/FedEx type first
	$sql = "SELECT * FROM wm_shipcodes WHERE name LIKE '%UPS%' OR name LIKE '%FedEx%' AND code = '$carrier_name'";
	// should return 0 rows if not UPS/Fedex
	$que = mysql_query($sql);
	if(mysql_num_rows($que)!=0) $fedexups = true;
	if(!$fedexups)
	{
		// not fedex or ups, see if it's freight...
		$sql = "SELECT freight FROM wm_shipcodes WHERE code = $carrier_name";
		$que = mysql_query($sql);
		checkDBError($sql);
		while($res = mysql_fetch_assoc($que))
		{
			if($res['freight']==0)
			{
				// not freight, shipper tracking number
				// already set, so make no changes
			}
			else
			{
				// freight, use BOL ID
				$tracking_num = $bol_id;
				$freightshipper = true;
			}
		}
	}
}
if(isset($fedexups) && $fedexups)
{
	$sql = "INSERT INTO shipping_packages (bol, box_number, orig_carrier_code, carrier_code, store_number, weight, package_identifier) VALUES ('$bol_id', '1', '$orig_carrier_code', '".(is_numeric($carrier_name) ? $carrier_name : ($set_shipcode != '' ? $set_shipcode : substr($carrier_name, 0, 8)))."', '".($_POST['store_number'] == '' ? '0' : $_POST['store_number'])."', $ship_weight, '".mysql_escape_string($tracking_num)."')";
	$query = mysql_query($sql);
	checkdberror($sql);
	$packageId = mysql_insert_id();
	$thispack = new BolPackage();
	$thispack->mBolId = $bol_id;
	$thispack->mPackageId = $packageId;
}
else
if(isset($freightshipper) && $freightshipper)
{
	$pack = 1;
	for($q = 0; $q < count($itemnum); $q++)
	{
		// for each item shipped, create a new package, should only be boxamts for now
		for($z = 1; $z < $boxqty[$q]; $z++)
		{
			$sql = "INSERT INTO shipping_packages (bol, box_number, orig_carrier_code, carrier_code, store_number, weight, package_identifier) VALUES ('$bol_id', '$pack', '$orig_carrier_code', '".(is_numeric($carrier_name) ? $carrier_name : ($set_shipcode != '' ? $set_shipcode : substr($carrier_name, 0, 8)))."', '".($_POST['store_number'] == '' ? '0' : $_POST['store_number'])."', ".($itemweight[$q]/$boxqty[$q]).", '".mysql_escape_string($tracking_num)."')";
			$query = mysql_query($sql);
			checkdberror($sql);
			$packageId = mysql_insert_id();
			$thispack = new BolPackage();
			$thispack->mBolId = $bol_id;
			$thispack->mBoxNumber = $pack;
			$thispack->mPackageId = $packageId;
			$newitem = new BolPackageItem();
			$newitem->mPOLineNumber = $lineid[$q];
			$newitem->mBOLLineNumber = $q;
			$newitem->mBolItemNumber = $itemnum[$q];
			$thispack->mItems[] = $newitem;
			$packages[] = $thispack;
			unset($thispack);
			$pack++;
		}
	}
}
if(!isset($packages))
{
	for ($i=1; $i<=$rows; $i++)
	{
		$newitem = new BolPackageItem();
		$newitem->mPOLineNumber = $lineid[$i];
		$newitem->mBOLLineNumber = $i;
		$newitem->mBolItemNumber = $itemnum[$i];
		$thispack->mItems[] = $newitem;
	}
}



if(!is_null($orderobj->mStoreNumber))
{
	if(isset($packages))
	{
		foreach($packages as $runme) $runme->GenerateASN();
	}
	else
	{
		$thispack->GenerateASN();
	}
}

// Insert the BoL_items db records
for ($i=1; $i<=count($itemnum); $i++)
{
	if($setqty[$i]+$mattqty[$i]+$boxqty[$i]>0)
	{
		if(!$multipo)
		{
			$item_po = $po_id;
			$item_suff = $po_suffix;
		}
		else
		{
			$item_po = $itempo[$i];
			$item_suff = $itemsuff[$i];
		}
		$sql = "INSERT INTO BoL_items (bol_id, po, po_suffix, type, lineid, item, setamt, mattamt, boxamt, class) VALUES ($bol_id, $item_po, '$item_suff', 'bol', {$lineid[$i]}, {$itemnum[$i]}, {$setqty[$i]}, {$mattqty[$i]}, {$boxqty[$i]},  '{$itemclass[$i]}')";
		if(checkdberror($sql))
		{
			sendError("processing the Bill of Lading", "BOL Creation - BOL Item Insert (add_bol.php line 129)", checkdberror($sql), 'shipping.php');
		}
		$query = mysql_query($sql);
		// create the shipping_items entry
		$sql = "SELECT weight, setqty AS qtyperset FROM snapshot_items WHERE id = {$itemnum[$i]}";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		$itemweight = $return['weight'];
		$qtyperset = $return['qtyperset'];
		if(isset($packages))
		{
			foreach($packages as $thispack)
			{
				for($k = 0; $k < count($thispack->mItems); $k++)
				{
					// is this package for this item?
					if($thispack->mItems[$k]->mPOLineNumber == $lineid[$i])
					{
						$qtyShipped = 1; // assume 1 ... i know, dangerous choice
						$totalweight = $itemweight;
						$sql = "INSERT INTO shipping_items (package_id, po_linenumber, store_number, qty, weight) VALUES (".$thispack->mPackageId.", {$lineid[$i]}, '".(isset($_POST['store_number']) && $_POST['store_number'] != '' ? $_POST['store_number'] : '0')."', $qtyShipped, $totalweight)";
						mysql_query($sql);
						checkdberror($sql);
						$thispack->mWeight += $totalweight;
						$thispack->mItems[$k]->mWeight = $totalweight;
					}
				}
				// update the shipping package weight
				$sql = "UPDATE shipping_packages SET weight = ".$thispack->mWeight." WHERE ID = ".$thispack->mPackageId;
				$que = mysql_query($sql);
				checkdberror($sql);
			}
		}
		else
		{
			for($k = 0; $k < count($thispack->mItems); $k++)
			{
				// is this package for this item?
				if($thispack->mItems[$k]->mPOLineNumber == $lineid[$i])
				{
					$qtyShipped = $setqty[$i] + $mattqty[$i] + $boxqty[$i];
					$totalweight = ($setqty[$i] * $qtyperset * $itemweight) + 	($mattqty[$i] * $itemweight) + ($boxqty[$i] * $itemweight);
					$sql = "INSERT INTO shipping_items (package_id, po_linenumber, store_number, qty, weight) VALUES (".$thispack->mPackageId.", {$lineid[$i]}, '".(isset($_POST['store_number']) && $_POST['store_number'] != '' ? $_POST['store_number'] : '0')."', $qtyShipped, $totalweight)";
					mysql_query($sql);
					checkdberror($sql);
					$thispack->mWeight += $totalweight;
					$thispack->mItems[$k]->mWeight = $totalweight;
				}
			}
			// update the shipping package weight
			$sql = "UPDATE shipping_packages SET weight = ".$thispack->mWeight." WHERE ID = ".$thispack->mPackageId;
			$que = mysql_query($sql);
			checkdberror($sql);
		}
	}
}


// Now, we'll see if the order is complete
if($multipo) {
	$queue_po = array_unique($itempo);
} else {
	$queue_po = array();
	$queue_po[] = $po_id;

}
foreach($queue_po as $pochecking) {
	if(!isPOClosed($pochecking, true)) { // if the order's open, check to see if a suffix has been added...if not (i.e. the first BOL), do so
		$sql = "SELECT po_suffix FROM BoL_forms WHERE ID = $bol_id";
		$que = mysql_query($sql);
		checkdberror($sql);
		$res = mysql_fetch_assoc($que);
		if(is_null($res['po_suffix']) || $res['po_suffix']=="") { // if the po_suffix is null or "", add a '0' suffix to the record...otherwise, it would have had one placed
			$sql = "UPDATE BoL_forms SET po_suffix = '".ord('A')."' WHERE ID = $bol_id AND po = '$pochecking'";
			$que = mysql_query($sql);
			checkdberror($sql);
			$sql = "UPDATE BoL_items SET po_suffix = '".ord('A')."' WHERE bol_id = '$bol_id' AND po = '$pochecking'";
			$que = mysql_query($sql);
			checkdberror($sql);
		}
	}
	// close the order if necessary now
	isPOClosed($pochecking);
}
addedBol($bol_id);
// take a look and possibly print
header("Location: viewbol.php?id=".$bol_id);
?>