<?php
// settracking.php
// script to apply a changed tracking #
require('../database.php');
require('inc_shipping.php'); // get the shipping functions
require_once('../form.inc.php'); // get the form functions to add records to OOR view
if(!$_POST) // if vars aren't POST'd in, exit
    sendError("setting a BOL's tracking number", "BOL View - Set Tracking (settracking.php line 5)", "Unauthorized access to settracking.php without POSTing variables", 'shipping.php');
// add the required scripts for db & user capabilities
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require_once('inc_postbol.php');
// let's do this
$bol_id = $_POST['bol_id'];
$newTrackingNumber = $_POST['newtrackingnum'];
$sql = "UPDATE BoL_forms SET trackingnum = '$newTrackingNumber' WHERE ID = $bol_id";
mysql_query($sql);
checkdberror($sql);
// get carrier to ID if this is a Walmart order...
$sql = "SELECT carrier FROM BoL_forms WHERE ID = '$bol_id'";
$que = mysql_query($sql);
checkDBerror($sql);
$ret = mysql_fetch_assoc($que);
if(is_numeric($ret['carrier']))
{
	// if this order is shipped via FedEx/UPS, set the tracking number otherwise leave be
	$fedexups = false;
	// we need to see if the carrier is a UPS/FedEx type first
	$sql = "SELECT * FROM wm_shipcodes WHERE name LIKE '%UPS%' OR name LIKE '%FedEx%' AND code = '$carrier_name'";
	// should return 0 rows if not UPS/Fedex
	$que = mysql_query($sql);
	if(mysql_num_rows($que)!=0)
	{
		$sql = "UPDATE shipping_packages SET package_identifier = '$newTrackingNumber' WHERE bol = $bol_id";
		mysql_query($sql);
		checkdberror($sql);
	}
}
else
{
	$sql = "UPDATE shipping_packages SET package_identifier = '$newTrackingNumber' WHERE bol = $bol_id";
	mysql_query($sql);
	checkdberror($sql);
}
setTracking($bol_id);
if(isset($_POST['edi']) && $_POST['edi'])
{
	// now we see if the freight amount is also entered; if so, we kick off the EDI process (for those that need it)
	$sql = "SELECT trackingnum, freight FROM BoL_forms WHERE ID = $bol_id";
	$query = mysql_query($sql);
	checkdberror($sql);
	$return = mysql_fetch_assoc($query);
	if(!is_null($return['trackingnum']) && $return['trackingnum'] != '' && !is_null($return['freight']) && $return['freight'] != 0)
	{

		unset($pos);
		$sql = "SELECT DISTINCT po FROM BoL_items WHERE bol_id = $bol_id";
		$query = mysql_query($sql);
		checkdberror($sql);
		while($ret = mysql_fetch_assoc($query))
		{
			$pos[] = $ret['po'];
		}
		$po_id = array_unique($pos);
		if(count($po_id) == 1)
		{
			makeShippingEdi($po_id[0], $bol_id);
		}
	}
}
header('Location: viewbol.php?id='.$bol_id);
exit();
?>