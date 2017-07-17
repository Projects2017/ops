<?php
// Relavent Classes

class InventoryReport {
	private static $instance = null;
	private $simpleXML;
	private $hubFAs = array();
	private $batchId = null;
	private $partnerId;
	private $messageCount = 0;
	
	function __construct() {
		global $commercehub_tmp_dir;
		$xmlstr = <<<XML
<?phpxml version="1.0" encoding="UTF-8"?>
<advice_file>
	<vendor>soflex</vendor>
	<advice_file_control_number>batchNumber</advice_file_control_number>
	<vendorMerchId>costco</vendorMerchId>
</advice_file>
XML;
		$this->simpleXML = new SimpleXMLElement($xmlstr);
		$this->genBatchId();
	}
	
	static function instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	function destroy() {
		$this->instance = null;
		// We'll no longer get this instance when instance is called.
	}
	
	private function genBatchId() {
		$sql = "UPDATE `commercehub_counter` SET `int` = `int` + 1 WHERE `key` = 'fa'";
		mysql_query($sql);
		checkDBerror($sql);
		$sql = "SELECT `int` FROM `commercehub_counter` WHERE `key` = 'fa'";
		$res = mysql_query($sql);
		checkDBerror($sql);
		$res = mysql_fetch_assoc($res);
		$res = $res['int'];
		
		$this->batchId = $res;
		
		$this->simpleXML->advice_file_control_number = $res;
		//$this->documentElement->setAttribute('batchnumber', $res); // Set the batch number
	}
	
	public function getBatchId() {
		return $this->batchId;
	}
	
	public function setVendorId($parnerId) {
		// Only both if the partnerId is changing
		$this->partnerId = $partnerId;
		$this->simpleXML->partnerId = $partnerId; // We're of course assuming the landscape here
	}
	
	public function setMerchId($merchId) {
		$this->simpleXML->vendorMerchID = $merchId;
	}
	
	public function asXML() {
		$this->simpleXML->addChild('advice_file_count',(string)$this->messageCount);
		$out = $this->simpleXML->asXML();
		$tidy = new tidy;
		$config = array(
			'indent'		=> true,
			'input-xml'		=> true,
			'wrap'			=> 200);
		$tidy->parseString($out, $config, 'UTF8');
		$tidy->cleanRepair();
		return tidy_get_output($tidy);
	}
	
	public function save($filename) {
		return file_put_contents($filename, $this->asXML());
	}
	
	public function dump() {
		echo $this->asXML();
	}
}


class ConfirmMsg {
	private static $instance = null;
	private $simpleXML;
	private $hubConfims = array();
	private $batchId = null;
	private $partnerId;
	private $messageCount = 0;
	private $packageDetails = array();
	private $currentpo;
	
	function __construct() {
		global $commercehub_tmp_dir;
		$xmlstr = <<<XML
<?phpxml version="1.0" encoding="UTF-8"?>
<ConfirmMessageBatch batchNumber="batchNumber">
	<partnerID>soflex</partnerID>
</ConfirmMessageBatch>
XML;
		$this->simpleXML = new SimpleXMLElement($xmlstr);
		$this->genBatchId();
	}
	
	static public function instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function destroy() {
		$this->instance = null;
		// We'll no longer get this instance when instance is called.
	}
	
	public function getMessageCount() {
		return $this->messageCount;
	}
	
	public function getBatchId() {
		return $this->batchId;
	}
	
	private function genBatchId() {
		$sql = "UPDATE `commercehub_counter` SET `int` = `int` + 1 WHERE `key` = 'confirm'";
		mysql_query($sql);
		checkDBerror($sql);
		$sql = "SELECT `int` FROM `commercehub_counter` WHERE `key` = 'confirm'";
		$res = mysql_query($sql);
		checkDBerror($sql);
		$res = mysql_fetch_assoc($res);
		$res = $res['int'];
		
		$this->batchId = $res;
		
		$this->simpleXML['batchNumber'] = $res;
		//$this->documentElement->setAttribute('batchnumber', $res); // Set the batch number
	}
	
	private function genPackageId() {
		$sql = "UPDATE `commercehub_counter` SET `int` = `int` + 1 WHERE `key` = 'package'";
		mysql_query($sql);
		checkDBerror($sql);
		$sql = "SELECT `int` FROM `commercehub_counter` WHERE `key` = 'package'";
		$res = mysql_query($sql);
		checkDBerror($sql);
		$res = mysql_fetch_assoc($res);
		$res = $res['int'];
		
		return $res;
	}
	
	public function setParnerId($parnerId) {
		// Only both if the partnerId is changing
		$this->partnerId = $partnerId;
		$this->simpleXML->partnerId = $partnerId; // We're of course assuming the landscape here
	}
	
	public function getBatchFromBol($bol_id) {
		if (!is_numeric($bol_id)) return false; // Boom!
		$sql = 'SELECT `batch`, `merchantpo` FROM `ch_order` WHERE `po` IN (SELECT `po` FROM `BoL_forms` WHERE `ID` = "'.($bol_id-1000).'")';
				$query = mysql_query($sql);
		checkDBerror($sql);
		if (!($row = mysql_fetch_assoc($query))) {
			echo "getBatchFromBol: Unable to find BoL ".($bol_id-1000).".\n";
			return false; // Unable to find BoL
		}
		return $row; // Return the array! yay!
	}
	
	/*
		Adds a hubConfirm Row for a Order
	*/
	public function addBoL($orderXML, $bol_id) {
		$sql = 'SELECT *, DATE_FORMAT(`shipdate`,"%Y%m%d") AS `ship_date` FROM `BoL_forms` WHERE `ID` = "'.($bol_id-1000).'"';
		$query = mysql_query($sql);
		checkDBerror($sql);
		if (!($bol = mysql_fetch_assoc($query))) {
			echo "addBoL: Unable to find BoL ".($bol_id-1000).".\n";
			return false; // Unable to find BoL
		}
		
		if ($bol['type'] != 'bol' && $bol['type'] != 'cred') {
			echo "addBoL : BoL type is not credit or BoL, addBoL(".($bol_id-1000).") is the wrong function for it.\n";
			return false; // We can't process it!
		}
		
		$newConf = $this->simpleXML->addChild('hubConfirm');
		//$this->hubConfims[(string) $orderXML->orderID] = $newConf;
		$this->messageCount = $this->messageCount + 1;
		
		
		// Build Internals of HubConfirm
		$partParty = $newConf->addChild('participatingParty',$orderXML->participatingParty);
		//$partParty['name'] = (string) $orderXML->participatingParty['name'];
		$partParty['name'] = 'costco';
		$partParty['roleType'] = (string) $orderXML->participatingParty['roleType'];
		$partParty['participationCode'] = "To:"; // Sending Confirm to party X
		if ($bol['type'] == 'bol') {
			$newConf->addChild('partnerTrxID', "BOL".$bol_id);
		} elseif ($bol['type'] == 'cred') {
			$newConf->addChild('partnerTrxID', "CRED".$bol_id);
		}
		$newConf->addChild('partnerTrxDate', $bol['ship_date']);
		$newConf->addChild('poNumber', (string) $orderXML->poNumber);
		$newConf->addChild('orderID', (string) $orderXML->orderId);
		$trxData = $newConf->addChild('trxData');
		$this->currentpo = $bol['po']+1000;
		$trxData->addChild('vendorsOrderNumber',$this->currentpo);
		
		if ($bol['type'] == 'bol') {
			$shipinfo = array();
			$shipinfo['servicelevel'] = $bol['servicelevel']; // TODO: Fix this to an actual Service Level field for BoLs!
			$shipinfo['trackingnumber'] = $bol['trackingnum'];
			$shipinfo['weight'] = $bol['weight'];
			$shipinfo['shipdate'] = $bol['ship_date'];
			
			$pdetail_id = $this->addPackage($shipinfo);
		}
		
		$sql = 'SELECT * FROM `BoL_items` WHERE `bol_id` = "'.($bol_id-1000).'"';
		$query = mysql_query($sql);
		checkDBerror($sql);
		while ($item = mysql_fetch_assoc($query)) {
			// Determine SKU
			$sql = 'SELECT `sku` FROM `snapshot_items` WHERE `ID` = "'.$item['item'].'"';
			$query2 = mysql_query($sql);
			checkDBerror($sql);
			$sku = mysql_fetch_assoc($query2);
			$sku = trim($sku['sku']);
			// Determine Merchant SKU and LineId
			$i = 0;
			foreach ($orderXML->lineItem as $orderItem) {
				if ($orderItem->vendorSKU == $sku) {
					$i++;
					$LineId = (string) $orderItem->merchantLineNumber;
					$msku = (string) $orderItem->merchantSKU;
				}
			}
			if ($i < 1) { // Diddn't find anything
				echo "addBoL: Unable to find Vendor SKU\n";
				return false;
			}
			$qty = $item['boxamt'];
			$newAction = $newConf->addChild('hubAction');
			if ($bol['type'] == 'cred') {
				$newAction->addChild('action','v_cancel');
				$newAction->addChild('actionCode',$item['credit_reason']);
			} elseif ($bol['type'] == 'bol') { // Default Type is BoL
				$newAction->addChild('action','v_ship');
			}
			$newAction->addChild('merchantLineNumber',$LineId);
			$newAction->addChild('trxVendorSKU', $sku);
			$newAction->addChild('trxMerchantSKU', $msku);
			$newAction->addChild('trxQty',$qty);
			if ($bol['type'] == 'bol') {
				$pdetail = $newAction->addChild('packageDetailLink');
				$pdetail['packageDetailID'] = $pdetail_id;
			}
		}
		
		$this->addXMLPackages($newConf);
		
		return true; //Success
	}
	
	
	/*
		Adds a package to the XML. Requires an array in the following format
		hubConfirm = SimpleXMLElement
		array(
			servicelevel = UNSP
			trackingnumber = UNSP
			weight = 0
			weightunit = LB
		)
	*/
	private function addXMLPackages($hubConfirm) {
		foreach ($this->packageDetails as $info) {
			$pkg = $hubConfirm->addChild('packageDetail');
			$pkg['packageDetailID'] = $info['packageDetailID'];
			$pkg->addChild('shipDate', $info['shipdate']);
			$pkg->addChild('serviceLevel1', $info['servicelevel']);
			$pkg->addChild('trackingNumber', $info['trackingnumber']);
			$ship = $pkg->addChild('shippingWeight',$info['weight']);
			$ship['weightUnit'] = $info['weightunit'];
		}
		$this->packageDetails = array();
		return;
	}
	
	private function addPackage($info, $pkg_id = null) {
		if (!is_array($info)) return false;
		if (!$info['servicelevel']) $info['servicelevel'] = 'UNSP';
		if (!$info['shipdate'])  $info['shipdate'] = '00000000';
		if (!$info['trackingnumber']) $info['trackingnumber'] = 'UNSP';
		if (!($info['weight']&&is_numeric($info['weight']))) $info['weight'] = '0';
		if (!$info['weightunit']) $info['weightunit'] = 'LB';
		if (!array_key_exists($pkg_id, $this->packageDetails)) {
			$pkg = array();
			$pkg['packageDetailID'] = 'PD'.$this->genPackageID();
		} else {
			return; // Package Already built..
		}
		$pkg['shipdate'] = $info['shipdate'];
		$pkg['servicelevel'] = $info['servicelevel'];
		$pkg['trackingnumber'] = $info['trackingnumber'];
		$pkg['weight'] = $info['weight'];
		$pkg['weightunit'] = $info['weightunit'];
		
		$this->packageDetails[$pkg['packageDetailID']] = $pkg;
		
		return $pkg['packageDetailID'];
	}
	
	public function asXML() {
		$this->simpleXML->addChild('messageCount',(string)$this->messageCount);
		$out = $this->simpleXML->asXML();
		$tidy = new tidy;
		$config = array(
			'indent'		=> true,
			'input-xml'		=> true,
			'wrap'			=> 200);
		$tidy->parseString($out, $config, 'UTF8');
		$tidy->cleanRepair();
		return tidy_get_output($tidy);
	}
	
	public function save($filename) {
		return file_put_contents($filename, $this->asXML());
	}
	
	public function dump() {
		echo $this->asXML();
	}
}


class FunctionalAwk {
	private static $instance = null;
	private $simpleXML;
	private $hubFAs = array();
	private $batchId = null;
	private $partnerId;
	private $messageCount = 0;
	
	function __construct() {
		global $commercehub_tmp_dir;
		$xmlstr = <<<XML
<?phpxml version="1.0" encoding="UTF-8"?>
<FAMessageBatch batchNumber="batchNumber">
<partnerID>soflex</partnerID>
</FAMessageBatch>
XML;
		$this->simpleXML = new SimpleXMLElement($xmlstr);
		$this->genBatchId();
	}
	
	static function instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	function destroy() {
		$this->instance = null;
		// We'll no longer get this instance when instance is called.
	}
	
	public function getMessageCount() {
		return $this->messageCount;
	}
	
	private function genBatchId() {
		$sql = "UPDATE `commercehub_counter` SET `int` = `int` + 1 WHERE `key` = 'fa'";
		mysql_query($sql);
		checkDBerror($sql);
		$sql = "SELECT `int` FROM `commercehub_counter` WHERE `key` = 'fa'";
		$res = mysql_query($sql);
		checkDBerror($sql);
		$res = mysql_fetch_assoc($res);
		$res = $res['int'];
		
		$this->batchId = $res;
		
		$this->simpleXML['batchNumber'] = $res;
		//$this->documentElement->setAttribute('batchnumber', $res); // Set the batch number
	}
	
	public function getBatchId() {
		return $this->batchId;
	}
	
	public function setParnerId($parnerId) {
		// Only both if the partnerId is changing
		$this->partnerId = $partnerId;
		$this->simpleXML->partnerId = $partnerId; // We're of course assuming the landscape here
	}
	
	private function addhubFA($trxSetID) {
		$newFA = $this->simpleXML->addChild('hubFA');
		$this->hubFAs[$trxSetID] = $newFA;
		++$this->messageCount;
		//$this->simpleXML->messageCount = (string) $this->messageCount;

		// Build Internals of HubFA
		$msgBatch = $newFA->addChild('messageBatchLink');
		$msgBatch->addChild('trxSetID',$trxSetID);
	}
	
	public function ackMessage($trxSetID, $trxID, $type, $accepted = true, $detail = array()) {
		if (!$this->hubFAs[$trxSetID]) {
			$this->addhubFA($trxSetID);
		}
		$hubFA = $this->hubFAs[$trxSetID];
		
		$hubFA->messageBatchLink->trxSetID = $trxSetID;
		$msgAck = $hubFA->addChild('messageAck');
		$msgAck['type'] = $type;
		$msgAck->addChild('trxID',$trxID);
		if ($detail) {
			foreach ($detail as $id => $val) {
				$exception = $msgAck->addChild('detailException');
				$exception->addChild('detailID', $id);
				$exception->addChild('exceptionDesc', $val);
			}
		}
		$msgDisp = $msgAck->addChild('messageDisposition');
		$msgDisp['status'] = $accepted ? 'A' : 'R' ;
	}
	
	private function finishHubFAs() {
		foreach ($this->hubFAs as $hubFA) {
			$total = 0;
			$success = 0;
			foreach ($hubFA->messageAck as $msg) {
				if ($msg->messageDisposition['status'] == 'A') {
					++$success;
				}
				++$total;
			}
			$msgDisp = $hubFA->addChild('messageBatchDisposition');
			if ($total == $success)
				$msgDisp['status'] = 'A';
			elseif ($success > 0)
				$msgDisp['status'] = 'P';
			else
				$msgDisp['status'] = 'R';
			$msgDisp->addChild('trxReceivedCount',$total);
			$msgDisp->addChild('trxAcceptedCount',$success);
		}
	}
	
	public function asXML() {
		$this->simpleXML->addChild('messageCount',(string)$this->messageCount);
		$this->finishHubFAs();
		$out = $this->simpleXML->asXML();
		$tidy = new tidy;
		$config = array(
			'indent'		=> true,
			'input-xml'		=> true,
			'wrap'			=> 200);
		$tidy->parseString($out, $config, 'UTF8');
		$tidy->cleanRepair();
		return tidy_get_output($tidy);
	}
	
	public function save($filename) {
		return file_put_contents($filename, $this->asXML());
	}
	
	public function dump() {
		echo $this->asXML();
	}
}

// Relavent Functions


function confirm_bols($orderdir) {
	$inst = ConfirmMsg::instance();
	$sql = 'SELECT `bol_id` FROM `ch_bolqueue` WHERE `processed` = 0 ORDER BY `bol_id`';
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($bol_id = mysql_fetch_assoc($query)) {
		$bol = $bol_id['bol_id'] + 1000; // Extract the value from the array
		$sql = 'UPDATE `ch_bolqueue` SET `processed` = 1 WHERE `bol_id` = "'.$bol_id['bol_id'].'"';
		mysql_query($sql);
		checkDBerror($sql);
		$batchId = $inst->getBatchFromBol($bol);
		if (file_exists($orderdir.'/'.$batchId['batch'].'.neworders')) {
			$xml = simplexml_load_file($orderdir.'/'.$batchId['batch'].'.neworders');
			foreach ($xml->hubOrder as $order) {
				if ($order->poNumber == $batchId['merchantpo']) {
					if (!$inst->addBoL($order, $bol)) {
						$sql = 'UPDATE `ch_bolqueue` SET `processed` = 0 WHERE `bol_id` = "'.$bol_id['bol_id'].'"';
						mysql_query($sql);
						checkDBerror($sql);
						echo "Failed to addBoL(".$order->asXML().",".$bol.").\n";
					}
					break; // Done with the foreach
				}
			}
		} else {
			echo "Failed to locate order file ".$batchId['batch'].'.neworders for BoL #'.$bol."\n";
		}
	}
}

function mvdir($sdir, $tdir, $copy = false) {
	chdir($sdir);
	$d = dir($sdir);
	while (false !== ($entry = $d->read())) {
		if ($entry == '.' || $entry == '..') continue;
		$target = $entry;
		// If File Exists, We need to add a numeric after it
		if (file_exists($tdir.'/'.$target)) {
			$i = 1;
			while (file_exists($tdir.'/'.$target.'.'.$i)) {
				++$i;
			}
			$target = $target.'.'.$i;
		}
		if ($copy) {
			copy($sdir.'/'.$entry, $tdir.'/'.$target);
		} else {
			rename($sdir.'/'.$entry, $tdir.'/'.$target);
		}
	}
}

function procorder($source, $template) {
	global $commercehub_dealerid;
	$fa = FunctionalAwk::instance();
	$xml = simplexml_load_file($source);
	$batchnumber = (string) $xml['batchNumber']; // Get Batch Number, Type Cast it to a String
	
	$orders = $xml->hubOrder;
	// Process orders
	foreach ($orders as $order) {
		$failures = array(); // Failure Messages
		// Construct Order Comment
		$comments = '';
		if ($order->poTypeCode && $order->poTypeCode == 'R') {
			$comments .= "Reissued Costco Order";
		}
		$comments .= 'Costco PO #'.$order->poNumber."\n";
		$comments .= 'Costco Order #'.$order->custOrderNumber."\n";
		if ($order->shippingCode)
			$comments .= 'Shipping Code: '.$order->shippingCode."\n";
		if ($order->poHdrData && $order->poHdrData->reqShipDate) {
			// Downconvert Date to something we can handle!
		   	$year = substr($order->poHdrData->reqShipDate, 0, 4);
		   	$mo = substr($order->poHdrData->reqShipDate, 4, 2);
		   	$day = substr($order->poHdrData->reqShipDate, 6);
			$comments .= 'DO NOT SHIP BEFORE '.$mo.'-'.$day.'-'.$year."\n";
		}
		if ($order->giftMessage) {
			$comments .= 'Gift Message: '.$order->giftMessage."\n";
		}
		
		$lines = $order->lineItem;
			
		// Get initial SKU so we can determine the order form
		$i = 0;
		$f = 0;
		foreach ($lines as $line) {
			$sql = 'SELECT `ID`, `header` FROM `form_items` WHERE `sku` = "'.mysql_escape_string($line->vendorSKU).'"';
			$query = mysql_query($sql);
			checkDBerror($sql);
			$form = 0;
			while ($item = mysql_fetch_assoc($query)) {
				$sql2 = 'SELECT `form` FROM `form_headers` WHERE `ID` = '.$item['header'];
				$query2 = mysql_query($sql2);
				checkDBerror($sql2);
				if (!($form_temp = mysql_fetch_assoc($query2))) continue; // No Form Header with this item... argh
				$form_temp = $form_temp['form'];
				$sql2 = "select `form` from form_access where user = '$commercehub_dealerid' AND `form` = '".$form_temp."'";
				$query2 = mysql_query($sql2);
				checkDBerror($sql2);
				if (mysql_num_rows($query2)) {
					$form = $form_temp;
					break; // We found the form for this item... so let's break out
				} else {
					continue;
				}
			}
			if (!$form) {
				$failures[(string) $line->lineItemId] = 'Unable to match vendorSKU to an inventory item.';
			}
		}
		//print_r($failures);
		if ($failures||!$form) {
			// We were unable to determine the Form, so we're going to report this as a rejection, we were unable to insert the order into the database.
			$fa->ackMessage($batchnumber, $order['transactionID'],'order',false, $failures);
			continue; // Move on to next order
		}
		
		// Determine Headers Within the Form
		$sql = 'SELECT `ID` FROM `form_headers` WHERE `form` = "'.$form.'"';
		$query = mysql_query($sql);
		checkDBerror($sql);
		$headers = array();
		while ($header = mysql_fetch_assoc($query)) {
			$headers[] = $header['ID'];
		}
		
		if (!$headers) {
			// Form has no headers!
			$fa->ackMessage($batchnumber, $order['transactionID'],'order',false);
			continue; // Move on to next order
		}
		$head_str = implode(',',$headers);
		
		// Identify Items
		$items = array();
		foreach ($lines as $line) {
			$sql = 'SELECT `ID` FROM `form_items` WHERE `sku` = "'.mysql_escape_string($line->vendorSKU).'" AND `header` IN ('.$head_str.')';
			$query = mysql_query($sql);
			checkDBerror($sql);
			while ($item = mysql_fetch_assoc($query)) {
				$item_id = $item['ID'];
			}
			
			if (!$item_id) {
				// Unable to locate SKU!
				$failures[(string) $line->lineItemId] = 'Unable to match vendorSKU to an inventory item.';
				continue; // Next Item! (it will fully fail this order later)
			}

                        $existing = false;
                        foreach ($items as $k => $i) {
                            if ($item_id == $i['item_id']) {
                                $items[$k]['qty'] += $line->qtyOrdered;
                                $existing = true;
                                break;
                            }
                        }
			if (!$existing) {
                            $items[] = array(
                                    'item_id' => $item_id,
                                    'setqty' => 0,
                                    'mattqty' => 0,
                                    'qty' => $line->qtyOrdered
                                    );
                        }
		}
		
		if ($failures || !$items) {
			// Order behind the times!
			$fa->ackMessage($batchnumber, $order['transactionID'],'order',false,$failures);
			continue; // Next Order!
		}
		
		
		$tmpOrder = submitOrder($commercehub_dealerid,1,$comments,$form, $items, true);
		if ($tmpOrder['messages']) {
			foreach ($tmpOrder['messages'] as $id => $msg) {
				if ($msg['block'] != 'Y') continue; // Ignore it if it wouldn't normally block the order
				if ($id == 'nostock') {
					// This unfortunately isn't the correct place to report this, so we're processing it anyway for now.
				} elseif ($id == 'noitems') {
					// Generically Fail
					$fa->ackMessage($batchnumber, $order['transactionID'],'order',false);
					continue 2; // Move on to the next order
				}
			}
		}
		
		$orderId = submitOrder($commercehub_dealerid,1,$comments,$form, $items, false, false, true, false);
		
		foreach ($order->personPlace as $personPlace) {
			if ((string) $order->billTo['personPlaceID'] == (string) $personPlace['personPlaceID']) {
				$billTo = $personPlace;
				break; // We found our bill to
			}
		}
		
		$working = $billTo;
		$sql = 'SELECT * FROM `ch_personplace` WHERE `ch_id` = "'.mysql_escape_string($working['personPlaceID']).'" ORDER BY `id` DESC';
		$query = mysql_query($sql);
		checkDBerror($sql);
		$newRecord = true;
		if ($personPlace = mysql_fetch_assoc($query)) {
			$newRecord = false;
			if ($personPlace['name1'] != $working->name1) $newRecord = true;
			if ($personPlace['address1'] != $working->address1) $newRecord = true;
			if ($personPlace['address2'] != $working->address2) $newRecord = true;
			if ($personPlace['city'] != $working->city) $newRecord = true;
			if ($personPlace['state'] != $working->state) $newRecord = true;
			if ($personPlace['country'] != $working->country) $newRecord = true;
			if ($personPlace['postalCode'] != $working->postalCode) $newRecord = true;
			if ($personPlace['dayPhone'] != $working->dayPhone) $newRecord = true;
			if ($personPlace['email'] != $working->email) $newRecord = true;
			if ($personPlace['companyName'] != $working->companyName) $newRecord = true;
			if ($personPlace['partnerId'] != $working->partnerPersonPlaceId) $newRecord = true;
			
			if (!$newRecord) {
				$billTo = $personPlace['id'];
				$billTo_snap = $personPlace['snapshot'];
			}
		}
		
		if ($newRecord) {
			$sql = 'INSERT INTO `snapshot_users` (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `email`, `secondary`) VALUES (NULL, NULL, ';
			$sql .= '"'.mysql_escape_string($working->companyName).'", ';
			$sql .= '"'.mysql_escape_string($working->name1).'", ';
			$sql .= '"'.mysql_escape_string($working->address1).'", ';
			$sql .= '"'.mysql_escape_string($working->address2).'", ';
			$sql .= '"'.mysql_escape_string($working->city).'", ';
			$sql .= '"'.mysql_escape_string($working->state).'", ';
			$sql .= '"'.mysql_escape_string($working->postalCode).'", ';
			$sql .= '"'.mysql_escape_string($working->dayPhone).'", ';
			$sql .= '"'.mysql_escape_string($working->email).'", ';
			$sql .= '"", "N");';
			mysql_query($sql);
			checkDBerror($sql);
			$snap = mysql_insert_id();
		
			$sql = '
				INSERT INTO `ch_personplace` (ch_id,snapshot,name1,address1,address2,city,state,country,postalCode,dayPhone,email,partnerId,companyName)
					VALUES (
						"'.mysql_escape_string($working['personPlaceID']).'",
						"'.mysql_escape_string($snap).'",
						"'.mysql_escape_string($working->name1).'",
						"'.mysql_escape_string($working->address1).'",
						"'.mysql_escape_string($working->address2).'",
						"'.mysql_escape_string($working->city).'",
						"'.mysql_escape_string($working->state).'",
						"'.mysql_escape_string($working->country).'",
						"'.mysql_escape_string($working->postalCode).'",
						"'.mysql_escape_string($working->dayPhone).'",
						"'.mysql_escape_string($working->email).'",
						"'.mysql_escape_string($working->partnerPersonPlaceId).'",
						"'.mysql_escape_string($working->companyName).'")';
			mysql_query($sql);
			checkDBerror($sql);
			$billTo = mysql_insert_id();
			$billTo_snap = $snap;
		}
		
		foreach ($order->personPlace as $personPlace) {
			if ((string) $order->shipTo['personPlaceID'] == (string) $personPlace['personPlaceID']) {
				$shipTo = $personPlace;
				break; // We found our bill to
			}
		}
		
		$working = $shipTo;
		$sql = 'SELECT * FROM `ch_personplace` WHERE `ch_id` = "'.mysql_escape_string($working['personPlaceID']).'" ORDER BY `id` DESC';
		$query = mysql_query($sql);
		checkDBerror($sql);
		$newRecord = true;
		if ($personPlace = mysql_fetch_assoc($query)) {
			$newRecord = false;
			if ($personPlace['name1'] != $working->name1) $newRecord = true;
			if ($personPlace['address1'] != $working->address1) $newRecord = true;
			if ($personPlace['address2'] != $working->address2) $newRecord = true;
			if ($personPlace['city'] != $working->city) $newRecord = true;
			if ($personPlace['state'] != $working->state) $newRecord = true;
			if ($personPlace['country'] != $working->country) $newRecord = true;
			if ($personPlace['postalCode'] != $working->postalCode) $newRecord = true;
			if ($personPlace['dayPhone'] != $working->dayPhone) $newRecord = true;
			if ($personPlace['email'] != $working->email) $newRecord = true;
			if ($personPlace['companyName'] != $working->companyName) $newRecord = true;
			if ($personPlace['partnerId'] != $working->partnerPersonPlaceId) $newRecord = true;
			
			if (!$newRecord) {
				$shipTo = $personPlace['id'];
				$snap = $personPlace['snapshot'];
			}
		}
		
		if ($newRecord) {
			$sql = 'INSERT INTO `snapshot_users` (`id`, `orig_id`, `first_name`, `last_name`, `address`, `address2`, `city`, `state`, `zip`, `phone`, `fax`, `email`, `secondary`) VALUES (NULL, NULL, ';
			$sql .= '"'.mysql_escape_string($working->companyName).'", ';
			$sql .= '"'.mysql_escape_string($working->name1).'", ';
			$sql .= '"'.mysql_escape_string($working->address1).'", ';
			$sql .= '"'.mysql_escape_string($working->address2).'", ';
			$sql .= '"'.mysql_escape_string($working->city).'", ';
			$sql .= '"'.mysql_escape_string($working->state).'", ';
			$sql .= '"'.mysql_escape_string($working->postalCode).'", ';
			$sql .= '"'.mysql_escape_string($working->dayPhone).'", ';
			$sql .= '"'.mysql_escape_string($working->email).'", ';
			$sql .= '"", "N");';
			mysql_query($sql);
			checkDBerror($sql);
			$snap = mysql_insert_id();
		
			$sql = '
				INSERT INTO `ch_personplace` (ch_id,snapshot,name1,address1,address2,city,state,country,postalCode,dayPhone,email,partnerId,companyName)
					VALUES (
						"'.mysql_escape_string($working['personPlaceID']).'",
						"'.mysql_escape_string($snap).'",
						"'.mysql_escape_string($working->name1).'",
						"'.mysql_escape_string($working->address1).'",
						"'.mysql_escape_string($working->address2).'",
						"'.mysql_escape_string($working->city).'",
						"'.mysql_escape_string($working->state).'",
						"'.mysql_escape_string($working->country).'",
						"'.mysql_escape_string($working->postalCode).'",
						"'.mysql_escape_string($working->dayPhone).'",
						"'.mysql_escape_string($working->email).'",
						"'.mysql_escape_string($working->partnerPersonPlaceId).'",
						"'.mysql_escape_string($working->companyName).'")';
			mysql_query($sql);
			checkDBerror($sql);
			$shipTo = mysql_insert_id();
			$shipTo_snap = $snap;
		}
		
		$sql = 'INSERT INTO `ch_order` (`po`,`billto`,`shipto`,`batch`,`merchantpo`,`orderid`,`servicelevel`) VALUES (
			"'.mysql_escape_string($orderId - 1000).'",
			"'.mysql_escape_string($billTo).'",
			"'.mysql_escape_string($shipTo).'",
			"'.mysql_escape_string($batchnumber).'",
			"'.mysql_escape_string($order->poNumber).'",
			"'.mysql_escape_string($order->custOrderNumber).'",
			"'.mysql_escape_string($order->shippingCode).'")';
		mysql_query($sql);
		checkDBerror($sql);
		
		$sql = 'UPDATE `order_forms` SET `customer` = "'.$billTo_snap.'", `shipto` = "'.$shipTo_snap.'", `nobolmerge` = "1" WHERE `ID` = "'.($orderId - 1000).'"';
		mysql_query($sql);
		checkDBerror($sql);
		
		// echo("PO #".$orderId." a success!\n");
		
		// Add Awknowledgement to our FA
		$fa->ackMessage($batchnumber, $order['transactionID'],'order',true);
	}
}



function verify_dir($dir, $faildir, $dtdname, $dtdfile) {
	$imp = new DOMImplementation;
	chdir($dir);
	$d = dir($dir);
	$failed = array();
	$success = array();
	while (false !== ($entry = $d->read())) {
		if ($entry == '.' || $entry == '..') continue;
		if (is_file($entry)) {
			$dom = new DomDocument();
			$dom->load($entry);
			// Convert it to a XML DOMDocument w/DTD
			$dtd = $imp->createDocumentType($dtdname,'',$dtdfile);
			$dom2 = $imp->createDocument("", "", $dtd);
			$dom2->encoding = 'UTF-8';
			$dom2->standalone = false;
			$dom2->appendChild($dom2->importNode($dom->documentElement, true));
			$dom = $dom2;
			
			if ($dom->validate()) {
				// It's a valid pull... leave it for the next step!
				$success[] = $entry;
			} else {
				$failed[] = $entry;
				commercehub_mkdir($faildir);
				$target = $entry;
				// If File Exists, We need to add a numeric after it
				if (file_exists($faildir.'/'.$target)) {
					$i = 1;
					while (file_exists($faildir.'/'.$target.'.'.$i)) {
						++$i;
					}
					$target = $target.'.'.$i;
				}
				rename($entry,$faildir.'/'.$target); // Move file to failure dir
				$subject = 'CommerceHub Verify Error';
				$body = 'We found non-file '.$entry.' when verifying files in '.$dir.' moving file.\r\n';
				$body = 'Moved File to '.$faildir.'/'.$target.' for later review.';
				commercehub_senderr($subject, $body);
			}
		} // Else is not a file... we've already sent an error message about it with decrypt
	}
	
	return array('success' => $success, 'failed' => $failed);
}

function decrypt_dir($gnupg, $dir) {
	chdir($dir);
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry == '.' || $entry == '..') continue;
		if (!is_file($entry)) {
			// This entry isn't a file!
			$subject = 'CommerceHub Decrypt Error';
			$body = 'We found non-file '.$entry.' when decrypting files in '.$dir.'.';
			commercehub_senderr($subject, $body);
		} else {
			// This is a file! Let's try to decrypt it!
			file_put_contents($entry,$gnupg->decrypt(file_get_contents($entry))); //Overwrite file with unencrypted contents
		}
	}
}

function encrypt_dir($gnupg, $dir) {
	chdir($dir);
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry == '.' || $entry == '..') continue;
		if (!is_file($entry)) {
			// This entry isn't a file!
			$subject = 'CommerceHub Encrypt Error';
			$body = 'We found non-file '.$entry.' when encrypting files in '.$dir.'.';
			commercehub_senderr($subject, $body);
		} else {
			$entry_info = pathinfo($entry);
			if ($entry_info['extension'] == 'pgp') continue; // Skip it as we've already done it
			// This is a file! Let's try to decrypt it!
			file_put_contents($entry.".pgp",$gnupg->encrypt(file_get_contents($entry))); //Overwrite file with unencrypted contents
			unlink($entry); // Remove Old file
		}
	}
}

// Get list of files and download them
function ftp_getdir($conn, $rdir, $ldir) {
	$files = ftp_nlist($conn, $rdir);
	if (is_array($files)) {
		foreach ($files as $file) {
			if ($entry == '.' || $entry == '..') continue;
			if (!ftp_get($conn, $ldir."/".$file, $rdir."/".$file, FTP_BINARY)) {
				$subject = 'CommerceHub Download Error';
				$body = 'We were unable to download file '.$file.' in '.$rdir.'.';
				commercehub_senderr($subject, $body);
			}
		}
	} else {
		echo "Unable to get dir listing of ".$rdir.".\n";
	}
}

// Get list of files and download them
function ftp_putdir($conn, $ldir, $rdir) {
	$files = dir($ldir);
	while (false !== ($file = $files->read())) {
		if ($file == '.' || $file == '..') continue;
		if (!is_file($ldir."/".$file)) continue;
		if (!ftp_put($conn, $rdir."/".$file, $ldir.'/'.$file, FTP_BINARY)) {
			$subject = 'CommerceHub Upload Error';
			$body = 'We were unable to upload file '.$file.' in '.$ldir.'.';
			commercehub_senderr($subject, $body);
		}
	}
	$files->close();
}

// Functions for a seperate file later =)
function ftp_conn($host,$user,$pass, $port = null) {
	global $commercehub_email_err;
	if ($port)
		$ftp_conn = ftp_connect($host, $port);
	else
		$ftp_conn = ftp_connect($host);
		
	if (!$ftp_conn) die ("Diddn't even connect");
	$login_result = ftp_login($ftp_conn, $user, $pass);
	// ftp_pasv($ftp_conn, true);
	if (!($ftp_conn && $login_result)) {
		// Either Connect or Login Failed
		$subject = 'CommerceHub Connect Error';
		$body = 'We were unable to connect and login to the CommerceHub Server, please check logs for more information.';
		commercehub_senderr($subject, $body);
		die();
	}
	
	return $ftp_conn;
}

function commercehub_senderr($subject, $body) {
	$subject = '[RSS] '.$subject;
	sendmail($commercehub_email_err,$subject,$body,'From: xml@retailservicesystems.com');
}

// Make the dir if it doesn't already exist
function commercehub_mkdir($dir) {
	if (!is_dir($dir))
		mkdir($dir);
}

// Takes two-d array and makes prints it to a pretty fixed width text table
function print_pretty_table($arr, $tblname = null) {
	// Determine Columns and Size of Columns
	$columns = array();
	foreach ($arr as $row) {
		foreach ($row as $name => $value) {
			if (isset($columns[$name])) {
				if ($columns[$name] < strlen($value)) {
					$columns[$name] = strlen($value);
				}
			} else {
				$columns[$name] = strlen($value);
			}
		}
	}
	
	// Make sure that column names aren't bigger than the columns
	foreach ($columns as $name => $size) {
		if (strlen($name) > $size) {
			$columns[$name] = strlen($name);
		}	
	}
	
	// Determine size of table
	$i = 0;
	$width = 0;
	foreach ($columns as $col) {
		++$i;
		if ($i != 1) // Not First Iteration
			++$width;
		$width += $col;
	}
	unset($i);
	$hr = str_pad("", $width + 2, "=")."\n";
	
	// Build header of table
	if ($tblname) {
		echo str_pad(" ".$tblname." ", $width + 2, "=", STR_PAD_BOTH)."\n";
	} else {
		echo $hr;
	}
	echo " ";
	foreach ($columns as $name => $garbage) {
		echo str_pad($name, $columns[$name]," ",STR_PAD_BOTH)." ";
	}
	echo "\n";
	echo $hr;
	
	foreach ($arr as $row) {
		echo " ";
		foreach ($row as $name => $value) {
			echo str_pad($value, $columns[$name])." ";
		}
		echo "\n";
	}
	echo $hr;
}
