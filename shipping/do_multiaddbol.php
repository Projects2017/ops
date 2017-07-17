<?php
// multiadd_bol.php
// script to add BOLs with multiple POs in them
if(!isset($_POST)) {
	die('This page requires data to be sent via POST.');
}
require('../database.php');
$duallogin = 1;
include("../vendorsecure.php");
if (!$vendorid)
   include("../secure.php");
require('inc_shipping.php');
require('inc_postbol.php');
// Checking input
//print_r($_POST);
if (!is_numeric($_POST['totalrows'])) die("Non-Numeric argument: $_POST[totalrows]");
// first, lets throw the $_POST'd vars into variables
$carrier_name = stripslashes($_POST['carrier_name']);
$tracking_num = stripslashes($_POST['tracking_num']);
$ship_date = date('Y-m-d', strtotime($_POST['ship_date']));
$comment = addslashes($_POST['po_comment']);
$rows = $_POST['totalrows'];
// debugging code here

for ($i=1; $i<=$rows; $i++) {
  if(strlen($i)==2) { $i_compare = $i; } else { $i_compare = "0".$i; }
  foreach($_POST as $k => $v) {
    if (substr($k, 0, 3)=='_po') {
      if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
        $itempo[$i] = $v;
      }
    }
    if (substr($k, 0, 6)=='_srcpo') {
      if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
        $itemposrc[$i] = $v;
      }
    }
    if (substr($k, 0, 4)=='item') {
      if (is_numeric(substr($k, -2)) && substr($k, -2) == $i_compare) {
        $itemnum[$i] = $v;
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
}
/* print_r($itempo);
print_r($itemposrc);
print_r($itemnum);
print_r($itemclass);
print_r($setqty);
print_r($mattqty);
print_r($boxqty);
die();
*/
$ponums = array_unique($itempo); // remove the duplicates to get the PO #s
foreach($ponums as $k => $pos) {
  if(substr($pos, -2, 1)==".") { // if there's a suffix, split it out
    $pos_suff_array[] = substr($pos, -1); // suffix only to an array
    $pos_array[] = substr($pos, 0, strlen($pos)-2) - 1000; // main PO# to a separate array; minus 1000 for DB value
  } else {
    $pos_suff_array[] = "";
    $pos_array[] = $pos - 1000;
  } 
}
for($i=1; $i<=count($itempo); $i++) { // pull the suffixes out of the PO#s
  if(substr($itempo[$i], -2, 1)==".") { // if there's a suffix, split it out
    $item_pos_array[$i] = substr($itempo[$i], 0, strlen($itempo[$i])-2) - 1000;
    $item_pos_suff_array[$i] = substr($itempo[$i], -1);
  } else {
    $item_pos_array[$i] = $itempo[$i] - 1000;
    $item_pos_suff_array[$i] = "";
  }
}
$po = implode(',',$pos_array);
$ship_class = stripslashes($_POST['classtext']);
if($_POST['weight']=='' || !is_numeric($_POST['weight'])) {
  $ship_weight = 0;
  } else {
  $ship_weight = $_POST['weight'];
}

// check to see if the po # is coming from more than one place (i.e. "po" and "orig_po")
$srcs = array_unique($itemposrc);
sort($srcs);
if(count($srcs)>1) { $sources = implode(" OR ", $srcs); } else { $sources = $srcs[0]; }
foreach($pos_array as $k => $check_po) {
  preInsertCheck($check_po, $check_po);
}
// Create the BoL_forms db entry
// first, get the queue id
  $sql = "SELECT ID from BoL_queue WHERE $sources IN ($po)";
  if(checkdberror($sql)) {
    sendError("processing the Bill of Lading", "Multi BOL Creation - Queue ID select (multiadd_bol.php line 88)", checkdberror($sql), 'shipping.php');
  }
  $query = mysql_query($sql);
  $result = mysql_fetch_assoc($query);
  $queue_id = $result['ID'];
  // start the creation
  $sql = "INSERT INTO BoL_forms (multi_po, user_id, setamt, mattamt, boxamt, carrier, trackingnum, shipdate, weight, comment, createdate) VALUES (1, ";
  $sql .= $_POST['shipto'] ? $_POST['shipto'] : $_POST['dbuser'];
  $sql .= ", $setqtytot, $mattqtytot, $boxqtytot, '".mysql_escape_string($carrier_name)."', '".mysql_escape_string($tracking_num)."', '".$ship_date."', $ship_weight, '$comment', NOW())";
  if(checkdberror($sql)) {
    sendError("processing the Bill of Lading", "Multi BOL Creation - BOL Insert (multiadd_bol.php line 96-97)", checkdberror($sql), 'shipping.php');
  }
  $query = mysql_query($sql);
  $bol_id = mysql_insert_id();
  // Insert the BoL_items db records
  for ($i=1; $i<=count($itemnum); $i++) {
    if($setqty[$i]+$mattqty[$i]+$boxqty[$i]>0) {
      $sql = "INSERT INTO BoL_items (bol_id, po, po_suffix, item, setamt, mattamt, boxamt, class) VALUES ($bol_id, ".$item_pos_array[$i].", '".$item_pos_suff_array[$i]."', ".$itemnum[$i].", ".$setqty[$i].", ".$mattqty[$i].", ".$boxqty[$i].", '".$itemclass[$i]."')";
      if(checkdberror($sql)) {
        sendError("processing the Bill of Lading", "Multi BOL Creation - BOL Item Insert (multiadd_bol.php line 115)", checkdberror($sql), 'shipping.php');
      }
      $query = mysql_query($sql);
    }
  }

  // Now, we'll see if the order is complete
foreach($pos_array as $pochecking) {
	if(!isPOClosed($pochecking, true)) { // if the order's open, check to see if a suffix has been added...if not (i.e. the first BOL), do so
		$sql = "SELECT po_suffix FROM BoL_items WHERE bol_id = $bol_id AND po = $pochecking";
		//echo "$sql<br />\n";
		$que = mysql_query($sql);
		$addsuffix = false;
		checkdberror($sql);
		while($res = mysql_fetch_assoc($que))
		{
			if(is_null($res['po_suffix']) || $res['po_suffix']=="") $addsuffix = true;
			//print_r($res);
		}
		if($addsuffix)
		{ // if the po_suffix is null or "", add an 'A' suffix to the record...otherwise, it would have had one placed
			$sql = "UPDATE BoL_items SET po_suffix = '".ord('A')."' WHERE bol_id = $bol_id AND po = $pochecking";
			$que = mysql_query($sql);
			//echo "$sql<br />\n";
			checkdberror($sql);
		}
	}
	// close the order if necessary now
	isPOClosed($pochecking);
}
// take a look and possibly print
addedMultiBol($bol_id);
header("Location: viewbol.php?id=".$bol_id);
?>
