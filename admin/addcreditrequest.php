<?php
// addcreditrequest.php
// script to add credit request data from the Shipping site
// info placed in via GET process
// requires: user ID, form ID, item IDs, set matt & box qtys
require('database.php');
require('../inc_content.php');

// echo everything for now to make sure stuff is getting over
foreach($_GET as $k => $v) {
  if(substr($k, 0, 4)=="item") $itemnum[substr($k, 4)] = $v;
  if(substr($k, 0, 6)=="setqty") $itemset[substr($k, 6)] = $v;
  if(substr($k, 0, 6)=="boxqty") $itembox[substr($k, 6)] = $v;
  if(substr($k, 0, 7)=="mattqty") $itemmatt[substr($k, 7)] = $v;
  if($k=="user") $user_id = $v;
  if($k=="form") $form_id = $v;
  if($k=="comment") $comment = $v;
}
$items = array();
for($i=0;$i<count($itemnum);$i++) {
  $item['item_id'] = $itemnum[$i];
	$item['setqty'] = $itemset[$i]-$itemset[$i]-$itemset[$i];
	$item['mattqty'] = $itemmatt[$i]-$itemmatt[$i]-$itemmatt[$i];
  $item['qty'] = $itembox[$i]-$itembox[$i]-$itembox[$i];
  $items[] = $item;
}
$credit = submitOrder($user_id, 1, $comment, $form_id, $items);
if(is_numeric($credit)) {
	echo "OK|".$credit;
} else {
	$output = "Parameters sent via CURL:\n";
	foreach($_GET as $k => $v) {
	    $output .= "$k => $v\n";
	}
	ini_set(sendmail_from, 'Shipping Queue Daemon <noreply@retailservicesystems.com>');
	sendmail('Web Administration <will@retailservicesystems.com>', 'Shipping Queue Production DB Error', $output);
	echo "ERROR|".$output;
}
?>
