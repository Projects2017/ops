<?php
// add_credit.php
// script to add a credit request

if(!isset($_POST)) {
	die('This page requires data to be sent via POST.');
}
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require('inc_shipping.php');
require_once('inc_postbol.php');
// now it's time to add the credit request
// first, lets throw the $_POST'd vars into variables
$makeEdi = false;
extract($_POST);
if(isset($sendedifile)) $makeEdi = true;
$po = $po_id;
for ($i=1; $i<=$rows; $i++) {
  if(strlen($i)==2) { $i_compare = $i; } else { $i_compare = "0".$i; }
  foreach($_POST as $k => $v) {
    if (substr($k, 0, 4)=='item') {
      if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
        $itemnum[$i] = $v;
      }
    }
    if (substr($k, 0, 6)=='reason') {
      if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
        $itemreason[$i] = $v;
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
}
if($setqtytot + $mattqtytot + $boxqtytot == 0) {
  setcookie('BoL_msg', "Credit Request for Zero Quantities Are Not Allowed", time()+5);
  header("Location: shipping.php");
  exit();
}
// Create the BoL_forms db entry
// first, get the queue id
$sq = "SELECT po, orig_po FROM BoL_queue WHERE $po_source = $po";
$qu = mysql_query($sq);
$res = mysql_fetch_assoc($qu);
$orig_po = $res[$po_source];
$sql = "SELECT ID from BoL_queue WHERE $po_source = $po";
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the credit request", "Credit Request Processing (add_credit.php line 64)", checkdberror($sql), 'shipping.php');
}
$result = mysql_fetch_assoc($query);
$queue_id = $result['ID'];
preInsertCheck($orig_po, $po);
// start the creation
$cr_comment = mysql_escape_string($_POST['cr_comment']);
$sql = "INSERT INTO BoL_forms (po, queue_id, user_id, type, setamt, mattamt, boxamt, credit_approved, comment, createdate) VALUES (";
$sql .= "$po, $queue_id, ";
$sql .= secure_is_vendor() ? $vendorid : $userid;
$sql .= ", 'cred', $setqtytot, $mattqtytot, $boxqtytot, 0, '$cr_comment', NOW())";
$query = mysql_query($sql);
if(checkdberror($sql)) {
  sendError("processing the credit request", "Credit Request Processing - Insert BoL_forms query (add_credit.php line 72)", checkdberror($sql), 'shipping.php');
}
$bol_id = mysql_insert_id();
// Insert the BoL_items db records
for ($i=1; $i<=$rows; $i++) {
  if($setqty[$i]+$mattqty[$i]+$boxqty[$i]>0) {
    $sql = "INSERT INTO BoL_items (bol_id, type, po, item, setamt, mattamt, boxamt, credit_reason) VALUES ($bol_id, 'cred', $po, ".$itemnum[$i].", ".$setqty[$i].", ".$mattqty[$i].", ".$boxqty[$i].", '".mysql_escape_string($itemreason[$i])."')";
    $query = mysql_query($sql);
    if(checkdberror($sql)) {
      sendError("processing the credit request", "Credit Request Processing - Insert BoL_items query (add_credit.php line 82)", checkdberror($sql), 'shipping.php');
    }
  }
}
addedCr($bol_id);
if($makeEdi)
{
	// we send the PR Edi file
	// how we do this? generate a cancellation Edi file & proc it!
	require_once(dirname(__FILE__).'/../include/edi/bo_shippingedi.php');
	// this is an EDI order, let's grab some important info
	// start w/ the objects
	require_once(dirname(__FILE__).'/../include/edi/edi.php');
	// gather all the data we need into an object
	
	$cancelEdi = new EdiBuilder();
	$cancelEdi->Make('PC',$obj, $acktype, $trans);
	
}
setcookie('BoL_msg', 'Credit Request for PO # '.($po + 1000).' Submitted', time()+5);
header("Location: shipping.php");
?>