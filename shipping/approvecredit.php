<?php
// approvecredit.php
// script to actually approve the credit request

if(!$_POST) {
  die("This script requires info to be POST'd in.");
}
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require('inc_shipping.php');
require('../inc_content.php');
require_once('inc_postbol.php');
if(!secure_is_admin()) {
  setcookie('BoL_msg', "You are not allowed to process credit requests.");
  header('Location: shipping.php');
}
foreach($_POST as $k => $v) {
  if($k=="approve") { $approval = true; }
  if($k=="deny") { $approval = false; }
  if(substr($k, 0, 4)=="item") {
    $items[substr($k, 4)] = $v;
  }
  if(substr($k, 0, 5)=="yesno") {
    $itemstatus[substr($k, 5)] = $v;
  }
}
$cr_id = $_POST['cr_id'];
$sql = "SELECT po, comment FROM BoL_forms WHERE ID = ".$cr_id;
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the credit request", "Credit Request Processing (approvecredit.php line 25)", checkdberror($sql), 'shipping.php');
}
$result = mysql_fetch_assoc($query);
$po_id = $result['po'];
$cr_comment = $result['comment'];
$sql = "SELECT user, form FROM order_forms WHERE ID = $po_id";
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the credit request", "Credit Request Processing (approvecredit.php line 25)", checkdberror($sql), 'shipping.php');
}
$result = mysql_fetch_assoc($query);
$form_id = $result['form'];
$user_id = $result['user'];
// update the main form credit_approved field first
$sql = "UPDATE BoL_forms SET credit_approved = ";
if($approval) { $sql .= "1 WHERE id = $cr_id"; }
  else { $sql .= "2 WHERE id = $cr_id"; }
if(checkdberror($sql)) {
  sendError("updating the credit request status", "Credit Request Processing (approvecredit.php line 24)", checkdberror($sql), 'shipping.php');
}
$que = mysql_query($sql);
if(!$approval) {
  deniedCredit($cr_id); // run function done after credit denied
                                     // if denied, set all line items to denied
  $itemlist = implode(", ", $items);
  $sql2 = "UPDATE BoL_items SET credit_approved = 2 WHERE bol_id = $cr_id AND item IN (".$itemlist.")";
  if(checkdberror($sql2)) {
    sendError("updating the credit request status", "Credit Request Line Item Processing (approvecredit.php line 44)", checkdberror($sql2), 'shipping.php');
  }
  $que2 = mysql_query($sql2);
  isPOClosed($po_id);
  setcookie('credit_msg', "Credit Request for BOL # ".($cr_id+1000)." Denied", time() + 5);
  header('Location: showcredit.php?id='.$cr_id);
  exit();
}
approvedCredit($cr_id);
for($i=0; $i<count($items); $i++) {                                      // now we do approvals
  if($itemstatus[$i] == "on") $goitems[] = $items[$i];
}
$itemlist = implode(", ", $goitems);
// set the credit_approval for the approved line items
$sql2 = "UPDATE BoL_items SET credit_approved = 1 WHERE bol_id = $cr_id AND item IN (".$itemlist.")";
if(checkdberror($sql2)) {
  sendError("updating the credit request status", "Credit Request Line Item Processing (creditreq.php line 50)", checkdberror($sql2), 'shipping.php');
}
$que2 = mysql_query($sql2);
// figure the $ to be credited and build the CURL GET string
// $i is a counter for the items
$i = 0;
$items_info = array();
$sq = "SELECT item, setamt, boxamt, mattamt, credit_reason FROM BoL_items WHERE bol_id = $cr_id AND item in (".$itemlist.")";
$qu = mysql_query($sq);
if(checkdberror($sq)) {
  sendError("updating the credit request status", "Credit Request Line Item $ Counting (creditreq.php line 56)", checkdberror($sq), 'shipping.php');
}
while($res = mysql_fetch_assoc($qu)) {
  $sql = "SELECT orig_id FROM snapshot_items WHERE id = ".$res['item'];
  $que = mysql_query($sql);
  if(checkdberror($sql)) {
    sendError("updating the credit request status", "Credit Request Line Item Info Query (creditreq.php line 82)", checkdberror($sql), 'shipping.php');
  }
  $answer = mysql_fetch_assoc($que);
  $item_info['item_id'] = $answer['orig_id'];
  $sql_1 = "SELECT partno FROM form_items WHERE ID = {$answer['orig_id']
  }";
  $que_1 = mysql_query($sql_1);
  $res_1 = mysql_fetch_assoc($que_1);
  if($res['credit_reason']!="") $desc_item_info .= "{$res_1['partno']}: {$res['credit_reason']}\n";
  $item_info['snapshot_id'] = $res['item'];
  $item_info['setqty'] = $res['setamt']-$res['setamt']-$res['setamt'];
  $item_info['mattqty'] = $res['mattamt']-$res['mattamt']-$res['mattamt'];
  $item_info['qty'] = $res['boxamt']-$res['boxamt']-$res['boxamt'];
  $items_info[] = $item_info;
}                                                         // do the credit now...
if($cr_comment!="") { $comment_info = "\n--Comment--\n$cr_comment"; 
} else { $comment_info = ""; 
}
$temporary_str = $desc_item_info;
if($temporary_str!="") $desc_item_info = "\n--Credit Items & Reasons--\n$temporary_str";
$credit = submitOrder($user_id, 1, 'Approved Credit Request for PO # '.$po_id.$comment_info.$desc_item_info, $form_id, $items_info, false, true, false, false);
if(!is_numeric($credit)) {
  sendError("adding the credit request approval to the production database.", "Credit Approval to Production DB (approvecredit.php line 97)", $credit, 'showcredit.php?id='.$po_id);
}
$sql_update = "UPDATE BoL_forms SET credit_po = ".($credit)." WHERE ID = $cr_id";
$query_update = mysql_query($sql_update);
isPOClosed($po_id);
approvedCreditNum($cr_id, $credit);
setcookie('printcredit', 'yes', time()+20);
setcookie('credit_msg', "Credit Request for BOL # ".($cr_id+1000)." Approved");
header('Location: showcredit.php?id='.$po_id);
?>
