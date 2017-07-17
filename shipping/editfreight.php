<?php
// editfreight.php
// script for the admin to enter the freight amount
if(!$_POST) { // data needs to be POST'd in
  die("Access error...denied.");
}
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
  { include("../secure.php"); }
require('../inc_content.php');
require('inc_shipping.php');
require_once('inc_postbol.php');
if(!secure_is_admin()) {
  setcookie('BoL_msg', "You do not have proper privileges to enter the freight amount.");
  header('Location: shipping.php');
}
$bol_id = $_POST[$_POST['chosen']];
$po_id = $_POST['po_id'];
$sql = "SELECT form FROM order_forms WHERE ID = $po_id";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_array($query);
$form_id = $result['form'];
$sql = "SELECT name FROM forms WHERE ID = $form_id";
$query = mysql_query($sql);
checkdberror($sql);
$result = mysql_fetch_array($query);
$formname = $result['name'];
$sq2 = "SELECT user FROM order_forms WHERE ID = $po_id";
$qu2 = mysql_query($sq2);
checkdberror($sq2);
$res2 = mysql_fetch_array($qu2);
$user_id = $res2['user'];
$sq3 = "SELECT last_name FROM users WHERE ID = $user_id";
$qu3 = mysql_query($sq3);
checkdberror($sq3);
$res3 = mysql_fetch_array($qu3);
$username = $res3['last_name'];
$freightchosen = 'freight'.$_POST['chosen'];
$trackingchosen = 'trackingnum'.$_POST['chosen'];
//foreach($_POST as $k => $v) {
//	echo "$k => $v<br />\n";
//}
//echo "form_id = $form_id ; formname = $formname<br />\n user_id = $user_id ; username = $username";
if($_POST[$freightchosen]!='' && !is_numeric($_POST[$freightchosen])) {
  setcookie('bol_msg', "Freight needs to be a number.", time()+4);
  header('Location: showbol.php?id='.$bol_id);
}
$sql = "SELECT po_suffix, shipdate FROM BoL_forms WHERE id = $bol_id";
$que = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the freight addition request", "Freight Processing (editfreight.php line 23)", checkdberror($sql), 'shipping.php');
}
$res = mysql_fetch_assoc($que);
$po_suffix = $res['po_suffix'];
$ship_date = date('n/j/Y', strtotime($res['shipdate']));
$freight = number_format($_POST[$freightchosen], 2, '.', '');
$tracking_num = stripslashes(htmlentities($_POST[$trackingchosen]));
$sql = "UPDATE BoL_forms SET freight = $freight, trackingnum = '$tracking_num' WHERE id = $bol_id";
//echo "Updating...<br />\n";
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the freight addition request", "Freight Amount Addition (editfreight.php line 32)", checkdberror($sql), 'shipping.php');
}
//echo "updated, setting cookie string<br />\n";
$cookiestring = 'Freight Information Updated for BOL # '.($bol_id+1000);
// get total freight for shipments; if > prepaid amt, charge it to the account
//echo "getting total freight<br />\n";
// first we get the bol forms we need to look at via the bol_items table using the po_id
$sql = "SELECT bol_id FROM BoL_items WHERE po = $po_id";
$query = mysql_query($sql);
checkdberror($sql);
while($res = mysql_fetch_assoc($query)) {
	$bol_ids[] = $res['bol_id'];
}
$bol_ids_exp = implode(', ',$bol_ids);
$sql = "SELECT SUM(freight) as freightsum FROM BoL_forms WHERE ID IN ($bol_ids_exp)";
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the freight addition request", "Freight Processing - Total Freight Addition (editfreight.php line 38)", checkdberror($sql), 'shipping.php');
}
$res = mysql_fetch_assoc($query);
$totfreight = $res['freightsum'];
//echo "getting prepaid freight<br />\n";
$sql = "SELECT prepaidfreight FROM BoL_queue WHERE po = ".$po_id;
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
$submitcomment = $formname.' - Freight Charge for PO # '.($po_id+1000);
if($po_suffix!="") $submitcomment .= ".$po_suffix";
$submitcomment .= ', BOL # '.($bol_id+1000).', Shipped on '.$ship_date;
//echo "user_id = $user_id ; submitcomment = '$submitcomment' ; freight = $freightdiff";
//die();
$submit = submitCreditFee($user_id, 'f', $submitcomment, $freightdiff);
if(!is_numeric($submit)) {
  sendError("processing the freight addition request", "Freight Processing - Freight Charging (editfreight.php line 59)", $submit, 'shipping.php');
}
/*$curl_url_append = "?user=$user_id&type=f&comment=".urlencode($submitcomment)."&total=$freightdiff";
// set up the curl info for the send
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $production_path."admin/addshipping.php".$curl_url_append);
$res = curl_exec($ch);
if (curl_errno($ch)) {
  echo curl_error($ch);
  } else {
  curl_close($ch);
  }
  if ($res != 1) {
  sendError("processing the freight addition request", "Freight Processing - CURL to Production DB (editfreight.php line 65)", $res, 'shipping.php');
  } else $cookiestring .= "; Account Charged for Amount Over Prepaid Freight ($$prepaidfreight) = $$freightdiff"; // add a little blurb with the amount charged
*/
}
// check for PO status

//die();
isPOClosed($po_id, false, $po_source);
setFreight($bol_id);
setcookie('bol_msg', $cookiestring, time() + 5);
header('Location: showbol.php?id='.$po_id);
?>