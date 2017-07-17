<?php
require_once('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
require_once('../inc_content.php');
if (!$vendorid)
   include("../secure.php");
if(!$_POST['formid']) {
  die("This script requires a BoL ID to be passed via POST.");
}
if(!secure_is_admin()) die("Access Denied");
require_once('inc_shipping.php'); // include shipping functions
$bol_id = $_POST['formid'];
foreach($_POST as $key => $val)
{
	if((substr($key, 0, 3)=="del" || substr($key, 0, 3)=="rsn") && $val)
	{
		if(substr($key,0,1)=="d") // set the amt to delete
		{
			switch(substr($key, 3, 1))
			{
				case "b":
					$badlines[substr($key, 6)]['box']['amt'] = (int)$val;
					break;
				case "m":
					$badlines[substr($key, 6)]['matt']['amt'] = (int)$val;
					break;
				case "s":
					$badlines[substr($key, 6)]['set']['amt'] = (int)$val;
					break;
			}
		}
		else
		{
			switch(substr($key, 3, 1))
			{
				case "b":
					$badlines[substr($key, 6)]['box']['rsn'] = $val;
					break;
				case "m":
					$badlines[substr($key, 6)]['matt']['rsn'] = $val;
					break;
				case "s":
					$badlines[substr($key, 6)]['set']['rsn'] = $val;
					break;
			}
		}
	}
}
// run through each line to cancel, using the item id from the order to ID the item itself
// so we can properly remove from the order
$cancels = Array();
$cancels['bol'] = $bol_id;
foreach($badlines as $item => $itemval)
{
	// get the current amount(s)
	$diffs = Array();
	$sql = "SELECT po, MAX(po_suffix) as po_suff FROM BoL_items WHERE bol_id = '$bol_id' AND item = '".$item."' GROUP BY item";
	$que = mysql_query($sql);
	checkdberror($sql);
	$res = mysql_fetch_assoc($que);
	$sql2 = "SELECT ID FROM orders WHERE po_id = '{$res['po']}' AND item = '$item'";
	//die($sql2);
	checkdberror($sql2);
	$que2 = mysql_query($sql2);
	$orderid = mysql_fetch_assoc($que2);

	foreach($itemval as $valtype => $vals)
	{
		$cancel = Array('item' => $item, 'orderid' => $orderid['ID'], 'type' => $valtype, 'amt' => $vals['amt'], 'rsn' => $vals['rsn']);
		$cancels[] = $cancel;
		$diffs[$valtype] += $vals['amt'];
	}
	$diffs['set'] = $diffs['set'] ? 0 : $diffs['set'];
	$diffs['matt'] = $diffs['matt'] ? 0 : $diffs['matt'];
	$diffs['set'] = $diffs['set'] ? 0 : $diffs['set'];
	
	$sql = "INSERT INTO BoL_items (bol_id, po, po_suffix, type, item, setamt, mattamt, boxamt, class, credit_reason, credit_approved) VALUES ('$bol_id', '{$res['po']}', '{$res['po_suff']}', 'bol', '$item', '".($diffs['set'] ? "-{$diffs['set']}" : "0")."', '".($diffs['matt'] ? "-{$diffs['matt']}" : "0")."', '".($diffs['box'] ? "-{$diffs['box']}" : "0")."', '', 'BOL Cancel', '1')";
	checkdberror($sql);
	$que = mysql_query($sql);
}
$cancels['comments'] = stripslashes($_POST['cancelcomments']);
doBolCancels($cancels);
header('Location: viewbol.php?id='.$bol_id);
?>