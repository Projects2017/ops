<?php
// setfreight.php
// script to apply a changed freight amt.
require('inc_shipping.php'); // get the shipping functions
if(!$_POST) // if vars aren't POST'd in, exit
    sendError("setting a BOL shipping amount", "BOL View - Set Freight (setfreight.php line 5)", "Unauthorized access to setfreight.php without POSTing variables", 'shipping.php');
// add the required scripts for db & user capabilities
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require('../inc_content.php');
require_once('inc_postbol.php');
// let's do this
$bol_id = $_POST['bol_id'];
$user_id = $_POST['user_id'];
$po_id = $_POST['po_id'];
$po_id_array = explode(',', $po_id);
if(count($po_id_array)>1) {
	$po_id = array_unique($po_id_array);
} else {
	$po_id = $po_id_array;
}
$pos = implode(',', $po_id);
$freight = number_format($_POST['newfreight'], 2, '.', '');
$sql = "UPDATE BoL_forms SET freight = '$freight' WHERE ID = $bol_id";
//echo "sql = $sql<br />\n";
mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the freight addition request", "Freight Amount Addition (setfreight.php line 17)", checkdberror($sql), 'shipping.php');
}
$sql = "SELECT ID FROM shipping_packages WHERE bol = $bol_id";
$query = mysql_query($sql);
checkdberror($sql);
$return = mysql_fetch_assoc($query);
if(mysql_num_rows($query)>0)
{
	// only do this if the shipping_package is set...
	$packageId = $return['ID'];
	$sql = "UPDATE shipping_packages SET freight = '$freight' WHERE ID = $packageId";
	mysql_query($sql);
	checkdberror($sql);
}
$sql = "SELECT shipdate FROM BoL_forms WHERE ID = $bol_id";
//echo "sql = $sql<br />\n";
$que = mysql_query($sql);
$res = mysql_fetch_array($que);
$ship_date = $res['shipdate'];
//echo "shipdate = $ship_date<br />\n";
/* now that we've applied the freight, we'll post it to the user account
	this code is copied straight from editfreight.php for now
	once it works and everything is looking good, I'll transform a lot of this code to functions
*/
// but we need to get the form names for the comments
$sql = "SELECT DISTINCT form FROM order_forms WHERE ID IN ($pos)";
//echo "sql = $sql<br />\n";
$query = mysql_query($sql);
checkdberror($sql);
while($result = mysql_fetch_array($query)) {
	$forms[] = $result['form'];
}
$form_id = implode(',', $forms);
//echo "form_id = $form_id<br />\n";
$sql = "SELECT DISTINCT name FROM forms WHERE ID IN ($form_id)";
//echo "sql = $sql<br />\n";
$query = mysql_query($sql);
checkdberror($sql);
while($result = mysql_fetch_array($query)) {
	$formnames[] = $result['name'];
}
$formname = implode(', ', $formnames);
//echo "formname = $formname<br />\n";
//begin lifted code:

// get total freight for shipments; if > prepaid amt, charge it to the account
//echo "getting total freight<br />\n";
// first we get the bol forms we need to look at via the bol_items table using the po_id
$sql = "SELECT bol_id FROM BoL_items WHERE po IN ($pos)";
//echo "sql = $sql<br />\n";
$query = mysql_query($sql);
checkdberror($sql);
while($res = mysql_fetch_assoc($query)) {
	$bol_ids[] = $res['bol_id'];
}
$bol_ids_exp = implode(', ',array_unique($bol_ids));
//echo "bol_ids_exp = $bol_ids_exp<br />\n";
$sql = "SELECT SUM(freight) as freightsum FROM BoL_forms WHERE ID IN ($bol_ids_exp)";
//echo "sql = $sql<br />\n";
$query = mysql_query($sql);
if(checkdberror($sql)) {
	sendError("processing the freight addition request", "Freight Processing - Total Freight Addition (setfreight.php line 40)", checkdberror($sql), 'shipping.php');
}
$res = mysql_fetch_assoc($query);
$totfreight = $res['freightsum'];
//echo "totfreight = $totfreight<br />\n";
//echo "getting prepaid freight<br />\n";
$sql = "SELECT prepaidfreight FROM BoL_queue WHERE po IN ($pos)";
//echo "sql = $sql<br />\n";
$query = mysql_query($sql);
if(checkdberror($sql)) {
	sendError("processing the freight addition request", "Freight Processing - Total Freight Addition (editfreight.php line 46)", checkdberror($sql), 'shipping.php');
}
$res = mysql_fetch_assoc($query);
$prepaidfreight = $res['prepaidfreight'];
//echo "prepaidfreight = $prepaidfreight<br />\n if $totfreight - $prepaidfreight > 0 , we go on<br />\n";
if($totfreight-$prepaidfreight>0) {  // if the total freight bill for the entire PO is greater than the prepaid amount, charge the remainder
	if($freight>($totfreight-$prepaidfreight)) { // so if the current BOL's freight is > the difference, use the difference
		$freightdiff = $totfreight - $prepaidfreight;
	} else {
		$freightdiff = $freight;  // otherwise, just add the current BOL's freight amount
	}
	//echo "freightdiff = $freightdiff<br />\n";
	$submitcomment = $formname.' - Freight Charge for PO # ';
	$notfirst = false;
	foreach($po_id as $ind_po) {
		if($notfirst) $submitcomment .= ", ";
		$submitcomment .= ($ind_po+1000);
		$notfirst = true;
	}
	$submitcomment .= ', BOL # '.($bol_id+1000).', Shipped on '.$ship_date;
	//echo "user_id = $user_id ; submitcomment = '$submitcomment' ; freight = $freightdiff";
	//die();
	//echo "submitcomment = $submitcomment<br />\n";
	$submit = submitCreditFee($user_id, 'f', $submitcomment, $freightdiff);
	if(!is_numeric($submit)) {
		sendError("processing the freight addition request", "Freight Processing - Freight Charging (editfreight.php line 59)", $submit, 'shipping.php');
	}
}
// check for PO status

//die();
foreach($po_id as $close_check) {
	isPOClosed($close_check, false);
}
setFreight($bol_id);
if(isset($_POST['edi']) && $_POST['edi'] && !(isset($_POST['nomakeedi']) && $_POST['nomakeedi'] == '1'))
{
	// now we see if the tracking number is also entered; if so, we kick off the EDI process (for those that need it)
	$sql = "SELECT trackingnum, freight FROM BoL_forms WHERE ID = $bol_id";
	$query = mysql_query($sql);
	checkdberror($sql);
	$return = mysql_fetch_assoc($query);
	if(!is_null($return['trackingnum']) && $return['trackingnum'] != '' && !is_null($return['freight']) && $return['freight'] >= 0)
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
			require_once(dirname(__FILE__).'/../include/edi/bo_shippingedi.php');
			// this is an EDI order, let's grab some important info
			// start w/ the objects
			require_once(dirname(__FILE__).'/../include/edi/edi.php');
			$edi = new Edi();
			$edi->LoadFromPO($po_id[0]);
			$orderobj = $edi->Process();
			makeShippingEdi($po_id[0], $bol_id, $orderobj->mPackingSlipType);
			exit();
		}
	}
}
header('Location: viewbol.php?id='.$bol_id);
exit();
?>
