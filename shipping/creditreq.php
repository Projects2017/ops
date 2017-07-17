<?php
// creditreq.php
// script to actually approve the credit request

if(!$_POST) {
  die("This script requires info to be POST'd in.");
}
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");require('inc_shipping.php');
require('../inc_content.php');
if(!secure_is_admin()) {
  setcookie('BoL_msg', "You are not allowed to process credit requests.");
  header('Location: shipping.php');
}
foreach($_POST as $k => $v) {
  if($k=="approve") { $approval = true; }
  if($k=="deny") { $approval = false; }
  if(substr($k, 0, 5)=="item_") {
    $items[] = substr($k, 5);
    $items_yn[] = $v;
  }
}
$bol_id = $_POST[$_POST['chosen']];
$sql = "SELECT user_id, po FROM BoL_forms WHERE ID = ".$bol_id;
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the credit request", "Credit Request Processing (creditreq.php line 25)", checkdberror($sql), 'shipping.php');
}
$result = mysql_fetch_row($query);
$user_id = $result[0];
$po_id = $result[1];
// update the main form credit_approved field first
$sql = "UPDATE BoL_forms SET credit_approved = ";
if($approval) { $sql .= "1 WHERE id = $bol_id"; }
  else { $sql .= "2 WHERE id = $bol_id"; }
if(checkdberror($sql)) {
  sendError("updating the credit request status", "Credit Request Processing (creditreq.php line 24)", checkdberror($sql), 'shipping.php');
}
$que = mysql_query($sql);
if(!$approval) {
  isPOClosed($po_id);
  setcookie('credit_msg', "Credit Request for BOL # ".($bol_id+1000)." Denied", time() + 5);
  header('Location: showcredit.php?id='.$po_id);
  exit();
}
for($i=0; $i<count($items); $i++) {                                      // now we do approvals
  if($items_yn[$i] == "on") $goitems[] = $items[$i];
}
$itemlist = implode(", ", $goitems);
// set the credit_approval for the approved line items
$sql2 = "UPDATE BoL_items SET credit_approved = 1 WHERE bol_id = $bol_id AND item IN (".$itemlist.")";
if(checkdberror($sql2)) {
  sendError("updating the credit request status", "Credit Request Line Item Processing (creditreq.php line 50)", checkdberror($sql2), 'shipping.php');
}
$que2 = mysql_query($sql2);
// figure the $ to be credited
$sq = "SELECT item, setamt, boxamt, mattamt FROM BoL_items WHERE bol_id = $bol_id AND item in (".$itemlist.")";
$qu = mysql_query($sq);
if(checkdberror($sq)) {
  sendError("updating the credit request status", "Credit Request Line Item $ Counting (creditreq.php line 56)", checkdberror($sq), 'shipping.php');
}
while($res = mysql_fetch_assoc($qu)) {
  $sql = "SELECT price, setqty FROM snapshot_items WHERE id = ".$res['item'];
  $que = mysql_query($sql);
  if(checkdberror($sql)) {
    sendError("updating the credit request status", "Credit Request Line Item Info Query (creditreq.php line 62)", checkdberror($sql), 'shipping.php');
  }
  $answ = mysql_fetch_row($que);
  if($answ[0]=="") {                 // if the price field is empty, get the prices from the other fields
    $sql = "SELECT set_, matt, box FROM snapshot_items WHERE id = ".$res['item'];
    if(checkdberror($sql)) {
      sendError("updating the credit request status", "Credit Request Line Item Info Query (creditreq.php line 69)", checkdberror($sql), 'shipping.php');
    }
    $que = mysql_query($sql);
    $ans = mysql_fetch_row($que);
    $setprice = number_format((float) $ans[0], 2, '.', '');           // the set_, matt & box fields are the price per unit
    $mattprice = number_format((float) $ans[1], 2, '.', '');          // we cast the type to float (unfortunately, some fields are prefaced with '$' in their content
    $boxprice = number_format((float) $ans[2], 2, '.', '');           // this should work...if not, we'll find out soon
  } else {
    $baseprice = number_format((float) $answ[0], 2, '.', '');         // baseprice is what the price field is in the snapshot_items db
    $set_qty = (int) $answ[1];                               // x qty/set =
    $setprice = number_format($price*$set_qty, 2, '.', '');           // price/qty
    $mattprice = $baseprice;
    $boxprice = $baseprice;                                  // matt & box prices are the same as the base (?)
  }
  $totalcredit += ($setprice*$res['setamt']) + ($boxprice*$res['boxamt']) + ($mattprice*$res['mattamt']);
}                                                         // do the credit now...
$submit = submitCreditFee($user_id, 'c', 'Shipping Credit for Credit Request # '.($bol_id+1000).', PO # '.($po_id+1000).' Approved on '.date('n/j/Y'), number_format($totalcredit, 2, '.', ''));
if(!is_numeric($submit) && false) 
{
   sendError("applying a credit", "Applying Credit (creditreq.php line 87)", $submit, 'shipping.php');
}

/*
$curl_url_append = "?user=$user_id&type=c&comment=".urlencode("Shipping Credit for Credit Request # ".($bol_id+1000).", PO # ".($po_id+1000).", Approved on ".date('n/j/Y'))."&total=".number_format($totalcredit, 2, '.', '');
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
  $res = explode('|',$res,2);
  if ($res[0] != "OK") {
  	$res = implode('|',$res);
    sendError("adding the credit request approval to the production database.", "Credit CURL to Production DB (creditreq.php line 93)", $res, 'showcredit.php?id='.$po_id);
    $res = implode('|',$res);
    echo $res;
    exit();
  }
*/
isPOClosed($po_id);
setcookie('credit_msg', "Credit Request for BOL # ".($bol_id+1000)." Credit # ".($res[1])." Approved; Account Credited $".number_format($totalcredit, 2), time() + 5);
header('Location: showcredit.php?id='.$po_id);
?>