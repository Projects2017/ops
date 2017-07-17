<?php
require('../database.php');
require('../admin/XML.inc.php');

function secure_is_admin() { return false; }
function secure_is_vendor() { return true; }
function secure_is_dealer() { return false; }
function secure_is_manager() { return false; }
$userid = 0;

function validate_message($xml) {
	if (!$xml->message) return false;
	if (!$xml->message->greeting) return false;
	if (!$xml->message->greeting->vendorid) return false;
	if (!$xml->message->greeting->vendorkey) return false;
	if ($xml->message->stock) {
		if (!$xml->message->stock->item) return false;
		if (!is_array($xml->message->stock->item)) $xml->message->stock->item &= array(&$xml->message->stock->item);
		foreach ($xml->message->stock->item as $i => $item) {
			if (!$item->_param) return false;
			if (!$item->_param['sku']) return false;
			if (!$item->stockstatus) return false;
			if (!$item->stockstatus->_value) return false;
			switch ($item->stockstatus->_value) {
				case 'In Stock': // This till the break is considered valid
				case 'Out of Stock':
				case 'Discontinued':
				case 'Due in 1 week':
				case 'Due in Jan.':
				case 'Due in Feb.':
				case 'Due in Mar.':
				case 'Due in Apr.':
				case 'Due in May.':
				case 'Due in Jun.':
				case 'Due in Jul.':
				case 'Due in Aug.':
				case 'Due in Sept.':
				case 'Due in Oct.':
				case 'Due in Nov.':
				case 'Due in Dec.': break;
				default: return false;
			}
			if ($item->stockday) {
				if (!$item->stockday->_value) return false;
				if ($item->stockday->_value < 1) return false;
				if ($item->stockday->_value > 31) return false;
			}
			if ($item->allocation) {
				if (!$item->allocation->_value) return false;
				if (!is_numeric($item->allocation->_value)) return false;
				if ($item->allocation->_value < 1) return false;
			}
		}
	}
	if ($xml->message->orderupdates) {
		if (!$xml->message->orderupdates->order) return false;
		if (!is_array($xml->message->orderupdates->order)) $xml->message->orderupdates->order &= array(&$xml->message->orderupdates->order);
		foreach ($xml->message->orderupdates->order as $i => $order) {
			if (!$order->_param) return false;
			if (!$order->_param['po']) return false;
			if (!is_numeric($order->_param['po'])) return false;
			if ($order->shipdate) {
				if ($order->shipdate->_value) {
					$regs = array();
					if (!ereg("^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$", $order->shipdate->_value, $regs)) return false;
					if ($regs[1] < 2000) return false; // Check that it is after the year 2000
					if ($regs[2] > 12) return false; // Check that the month is 12 or less
					if ($regs[3] > 31) return false; // Check that the day is 31 or less
				}
			}
			if ($order->cuft) {
				if ($order->cuft->_value) {
					if (!is_numeric($order->cuft->_value)) return false;
					if ($order->cuft->_value < 0) return false;
				}
			}
		}
	}
	return true; // Made it!
}

function sendError($headers, $error) {
	$to = $headers->reply_toaddress;
	$from = "\"RSS E-Mail XML Processor\" <".$GLOBALS['email_addr'].">";
	$subject = "[ERROR] Re: ".$headers->subject;
	$refers = "References: ".$headers->message_id;
	sendmail($to,$subject,$error,'From: '.$from."\r\n".
		'Reply-To: gary@retailservicesystems.com, will@retailservicesystems.com'."\r\n".
		'Bcc: will@retailservicesystems.com'."\r\n".
		$refers."\r\n".
		'X-Mailer: PHP/'.phpversion());
}

function sendComplete($headers, $skus, $sku404, $order, $order404) {
	$to = $headers->reply_toaddress;
	$from = "\"RSS E-Mail XML Processor\" <".$GLOBALS['email_addr'].">";
	$subject = "Re: ".$headers->subject;
	$refers = "References: ".$headers->message_id;
	$body = 
	"XML Processing Completed\n".
	
	"Item Stock Updates: ".$skus."\n".
	"SKUs Not Found: ".$sku404."\n".
	"Order Status Updates: ".$order."\n".
	"Orders Not Found: ".$order404;
	sendmail($to,$subject,$body,'From: '.$from."\r\n".
		'Reply-To: gary@retailservicesystems.com, will@retailservicesystems.com'."\r\n".
		'Bcc: will@retailservicesystems.com'."\r\n".
		$refers."\r\n".
		'X-Mailer: PHP/'.phpversion());
}


$stockkey = stock_status(NULL);
$stockarray = array();
foreach ($stockkey as $stock) {
	$stockarray[$stock['name']] = $stock;
}
$stockkey = $stockarray;
unset($stockarray);
$mbox = imap_open("{".$email_host.":110/pop3/notls}".$email_mailbox, $email_login, $email_pass);

$queue = imap_headers($mbox);
$numEmails = count($queue);

for($i = 1; $i < $numEmails+1; $i++) 
{
	$mailHeader = imap_headerinfo($mbox, $i);
	$from = $mailHeader->fromaddress;
	$subject = strip_tags($mailHeader->subject);
	$date = $mailHeader->date;
	// Our front line guard against spam
	if ($subject != 'XML Stock Update'&&$subject != 'XML Update') {
		imap_delete($mbox, $i); // Delete the Offending Message
		continue;
	}
	unset($xml); // Prevents Object reuse (which is badddd!!!!)
	$xml = new XML();
	$body = imap_body($mbox, $i);
	$xml->parse($body);
	if (!validate_message($xml)) {
		sendError($mailHeader,"Error Validating XML. Message Ignored\n\nTry your XML at http://www.validome.org/xml/validate/ for more descriptive errors\n\nOriginal E-Mail was: \n".$body);
		imap_delete($mbox, $i); // Delete the Offending Message
		//echo "\t<tr>\n\t\t<td colspan=4>ERROR VALIDATING XML</td>\n\t</tr>\n"; 
		continue; 
	}
	$id = $xml->message->greeting->vendorid->_value;
	$key = $xml->message->greeting->vendorkey->_value;
	$sql = "SELECT `name` FROM `vendor` WHERE `id` = '".mysql_escape_string($id)."' AND `key` = '".mysql_escape_string($key)."';";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$row = mysql_fetch_assoc($query);
	if (!$row) {
		sendError($mailHeader,"Invalid Key in Greeting. Message Ignored\n\nOriginal E-Mail was: \n".$body);
		imap_delete($mbox, $i); // Delete the Offending Message
		continue;
	}
	$vname = $row['name'];
	$vendorid = $id;
	$sql = "SELECT `d`.`id` FROM `vlogin_access` AS `a` INNER JOIN `vendors` AS `b` ON `b`.`ID` = `a`.`vendor` INNER JOIN `forms` as `c` ON `c`.`vendor` = `b`.`ID` INNER JOIN `form_headers` AS `d` ON `d`.`form` = `c`.`ID` WHERE `a`.`user` = '".$id."';";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$headers = array();
	while ($row = mysql_fetch_assoc($query)) {
		$headers[] = $row['id'];
	}
	if (count($headers)) $headers = '('.implode(',',$headers).')';
	else $headers = '(0)';
	$skus = 0;
	$sku404 = 0;
	if ($xml->message->stock) {
		foreach ($xml->message->stock->item as $item) {
			$sql = "SELECT `ID`, `alloc` FROM `form_items` WHERE `sku` = '".mysql_escape_string($item->_param['sku'])."' AND `header` IN ".$headers.";";
			$query = mysql_query($sql);
			checkDBerror($sql);
			$itemFailed = true;
			while ($row = mysql_fetch_assoc($query)) {
				$itemFailed = false;
				if  ($item->stockday&&$stockkey[$item->stockstatus->_value]['zeroday'] == 'N') {
					$stockday = $item->stockday->_value;
				} else {
					$stockday = '0';
				}
				if  ($item->allocation&&$stockkey[$item->stockstatus->_value]['block_order'] == 'N') {
					$alloc = $item->allocation->_value;
					$avail = $alloc;
					if ($row['alloc'] >= $avail) {
						$alloc = $row['alloc'];
					}
				} else {
					$alloc = '-1';
					$avail = '-1';
				}
				$sql = "UPDATE form_items SET 
				`stock` = '".$stockkey[$item->stockstatus->_value]['id']."', 
				`stock_day` = '".$stockday."',
				`alloc` = '".$alloc."',
				`avail` = '".$avail."'
				WHERE `ID` = '".$row['ID']."'";
				mysql_query($sql);
				checkDBerror($sql);
			}

			if ($itemFailed) {
				++$sku404;
			} else {
				++$skus;
			}
		}
	}
	
	$orders = 0;
	$order404 = 0;
	if ($xml->message->orderupdates) {
		require_once("../form.inc.php");
		foreach ($xml->message->orderupdates->order as $order) {
			$filterdata = array('po' => $order->_param['po']);
			$oldorders = formdata ('order', 0, $filterdata);
			if ($oldorders) ++$orders;
			else {
				++$order404;
				continue;
			}
			
			$dataarray = array();
			$dataarray['factory_confirm'] = 'on'; // Simple Recieving of this update is a confirmation
			
			if ($order->carrier) {
				$dataarray['carrier'] = $order->carrier->_value;
			}
			if ($order->tracking) {
				$dataarray['tracking'] = $order->tracking->_value;
			}
			if ($order->shipdate) {
				if ($order->shipdate->_value)
					$dataarray['shipped'] = 'on';
				$dataarray['shipdate'] = $order->shipdate->_value;
			}
			if ($order->shippinginfo) {
				$dataarray['shipping_info'] = $order->shippinginfo->_value;
			}
			if ($order->cuft) {
				$dataarray['cubic_ft'] = $order->cuft->_value;
			}
			foreach ($oldorders as $oldorder) {
				formupdate('order', $oldorder['id'], $dataarray);
			}
		}
	}

	sendComplete($mailHeader, $skus, $sku404, $orders, $order404);
	imap_delete($mbox, $i);
}
imap_expunge($mbox);
imap_close($mbox);
?>
