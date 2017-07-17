<?php
// inc_viewpo.php
// script of functions which display OOR & shipping information
// in the viewpo.php form
require_once('inc_content.php');
if (!$MoS_enabled) {
	require_once('form.inc.php');
}

function OpenOrderDisplay($po_id) {
	global $MoS_enabled;
	  if ($MoS_enabled) return ''; // Skip over this if we're in MoS
	// collates and displays OOR data re: the current PO
	  global $claims_status;
	  $order_type = Array('bedding', 'furniture', 'order');
	  $po_id = $po_id + 1000;
   $titles_on = false;
   $claims_header = false;
   foreach($order_type as $type) {
     $type_header[$type] = false;
     $result = forminfo($type);
	    foreach ($result as $k => $v) {
       if (!$v['on_po']) unset($result[$k]);
     }
  	  foreach($result as $k => $v) {
  	 	  $fields[$type][] = $v['id'];
  	 	  $field_lbl[$type][] = $v['nicename']; 
  	  }
     if($type=='furniture' || $type=='order') { $field = "po"; } else { $field = "`PO#`"; }
  	  if($fields[$type]) $field_list = "`".implode("`, `", $fields[$type])."`";
  	  $sql = "SELECT $field_list FROM claim_$type WHERE $field = $po_id";
  	  $query = mysql_query($sql);
  	  checkdberror($sql);
  	  while($return = mysql_fetch_assoc($query)) {
       if(!$claims_header) {
     	 $ret .= "</table><table class=\"noprint\" width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\"><tr><td class=\"orderTH\"><b>Claims</b></td></tr></table>\n";
     	 $claims_header = true; 
     }
				 if(!$type_header[$type] && $type == 'order') {
				 	 $type = ucfirst($type);
				 	 $ret .= "<table class=\"noprint\" width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\"><tr><th align=\"center\" colspan=\"12\" scope=\"row\"><p style=\"font-size: 16px; font-weight: bold\">$type Status</th></tr>";
				 	 $type = strtolower($type);
				 	 $type_header[$type] = true;
				 } else if(!$type_header[$type]) {
				 	 $type = ucfirst($type);
				 	 $ret .= "<table class=\"noprint\" width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\"><tr><td align=\"center\" class=\"orderTD\"><span class=\"orderTD\" style=\"font-size: 16px; font-weight: bold\">$type Claims: </span>"; 
				 	 $type = strtolower($type);
				 	 $type_header[$type] = true;
				 }
  	 	if(!$titles_on && $type == 'order') {
  	 		foreach($field_lbl as $k => $v) {
  	 			if($k == $type) {
  	 				foreach($v as $kk => $vv) {
  	 					if($type == 'order')
    	 			$ret .= "<td class=\"orderTD\">$vv</td>"; 
  	 				}
  	 			}
  	 		}
  	 		$ret .= '</tr>'; 
  	 	 $titles_on = true; 
  	  }
  	 	if($type == 'order') $ret .= '<tr>';
  	 	static $add_commas = false;
  	 	foreach($return as $k => $v) {
  	 		if($k=="status") $v = $claims_status[$v];
  	 		if(($k=="factory_confirm" || $k=="shipped") && $v=="on") $v = "X";
  	 		if($type == 'order') {
  	 			 if($k=="cr_link") { $ret .= "<td class=\"orderTD\"><a href=\"/shipping/showcredit.php?claim=1&id=".($po_id-1000)."\">$v</a></td>";
  	 			 
  	 			 } elseif($k=="id") { $ret .= "<td class=\"orderTD\"><a href=\"/form.php?action=view&form=$type&viewid=".$return['id']."\">$v</a></td>"; 
  	 			 } else {
  	 			 	$ret .= "<td class=\"orderTD\">$v</td>"; 
  	 			 } 
  	 		}
  	 		if($type != 'order' && $k == 'id') {
  	 		 if($add_commas) { $ret .= ", "; }
  	 		 $ret .= "<a href=\"/form.php?action=view&form=$type&viewid=".$return['id']."\">$v</a>";
  	 		 $add_commas = true;
  	 		}
  	 	}
  	 	if($type=='order') $ret .= '</tr>';
  	 }
  	 $titles_on = false;
  	 $ret .= "</table>"; 
  	}
  return $ret;
}

function ShippingDisplay($po_id) {
	// collate & display shipping system information re: the current PO
	$sql = "SELECT ID, setamt, mattamt, boxamt, carrier, shipdate, comment FROM BoL_forms WHERE type = 'bol' AND po = $po_id";
	$query = mysql_query($sql);
	checkdberror($sql);
 $bol_header = false;
 $shipping_header = false;
 $titles_on = false;
	while($result = mysql_fetch_assoc($query)) {
		if(!$shipping_header) {
			$ret .= "</table><table class=\"noprint\" width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\"><tr><td colspan=\"7\" class=\"orderTH\"><b>Shipping</b></td></tr>\n";
			$shipping_header = true; 
		}
		if(!$bol_header) {
			$ret .= "<tr><th align=\"center\" colspan=\"7\" scope=\"row\"><p style=\"font-size: 16px; font-weight: bold\">Bills of Lading</th></tr>";
			$bol_header = true; 
		}
  if(!$titles_on) {
  	$ret .= "<tr><td class=\"orderTD\">BOL #</td><td class=\"orderTD\">Ship Date</td><td class=\"orderTD\">Set</td><td class=\"orderTD\">Matt</td><td class=\"orderTD\">Box</td><td class=\"orderTD\">Carrier</td><td class=\"orderTD\">Comment</td></tr>";
  	$titles_on = true; 
  }
  	$ret .= "<tr><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">".($result['ID']+1000)."</a></td><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">{$result['shipdate']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">{$result['setamt']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">{$result['mattamt']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">{$result['boxamt']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">{$result['carrier']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewbol.php?id={$result['ID']}\">{$result['comment']}</a></td></tr>\n";
 $titles_on = false; 
	}
	$sql = "SELECT ID, bol_id, item, setamt, mattamt, boxamt, credit_reason FROM BoL_items WHERE type = 'cred' AND credit_approved = 1 AND po = $po_id";
	$query = mysql_query($sql);
	checkdberror($sql);
 $credit_header = false;
 $titles_on = false;
	while($result = mysql_fetch_assoc($query)) {
		if(!$shipping_header) {
			$ret .= "</table><table class=\"noprint\" width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\"><tr><td colspan=\"7\" class=\"orderTH\">Shipping</td></tr>\n";
			$shipping_header = true; 
		}	
		if(!$credit_header) {
			$ret .= "<tr><th align=\"center\" colspan=\"7\" scope=\"row\"><p style=\"font-size: 16px; font-weight: bold\">Credits</th></tr>";
			$credit_header = true; 
		}
  if(!$titles_on) {
   $ret .= "<tr><td class=\"orderTD\" colspan=\"2\">Item</td><td class=\"orderTD\">Set</td><td class=\"orderTD\">Matt</td><td class=\"orderTD\">Box</td><td class=\"orderTD\">Credit Req. #</td><td class=\"orderTD\">Credit Reason</td></tr>";
  	$titles_on = true; 
  }
  $item_sql = "SELECT partno, description FROM snapshot_items WHERE id = {$result['item']}";
  $item_qry = mysql_query($item_sql);
  checkdberror($item_sql);
  $item_res = mysql_fetch_assoc($item_qry);
  $ret .= "<td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">{$item_res['partno']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">{$item_res['description']}</a></td>";
  $ret .= "<td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">{$result['setamt']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">{$result['mattamt']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">{$result['boxamt']}</a></td><td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">".($result['bol_id']+1000)."</td><td class=\"orderTD\"><a href=\"/shipping/viewcredit.php?id={$result['bol_id']}\">{$result['credit_reason']}</a></td></tr>";
  }
 $titles_on = false;
 $ret .= "</table><table width=\"85%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\">";
 return $ret; 
}