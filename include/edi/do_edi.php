<?php
// do_edi.php
/**
 * PMD Dealer Ops
 *
 * LICENSE
 *
 * This file may contain information that is privilidged, confidential
 * and/or exempt from disclosure under applicatble law. If you are not the
 * explicitly authorized, you are hereby notified that any disclosure, copying,
 * distribution of this file or related files is STRICTLY PROHIBITED. If you
 * have access to this file when you are not explicitly authorized, please
 * notify jerrywilliams@pmdfurniture.com immediately.
 *
 * @category   PMD
 * @package    Operations
 * @copyright  Copyright (c) 2004-2009 Power Marketing Direct, Inc. (http://www.pmdfurniture.com)
 */
/*
require_once('database.php');
require_once('secure.php');
*/
/**
 * EDI Read/Write Database Library
 *
 * @package    Operations
 * @subpackage EDI
 * @copyright  Copyright (c) 2004-2009 Power Marketing Direct, Inc. (http://www.pmdfurniture.com)
 */
require_once('bo_edi.php');
require_once('edi.php'); 
 
class DoEdi
{
	/**
	 * The Instance var to keep track of the DoEdi class singleton instance
	 * @var Db
	 */
	private static $mInstance;
	private $mDb;
	public $mEdiVendor;
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return DoEdi An instance of this class.
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	function __construct()
	{
		global $EdiVendor;
		//$this->mDb = Db::getInstance();
		$this->mEdiVendor = &$EdiVendor;
	}

	/**
	 * Retrieves the next db increment of the given type for the given vendor code, formatted
	 * for an EDI file
	 * @param type: which type of number is being sought
	 * @param vendor: first two digits of the three-digit vendor code, defaults to 'wm' (Walmart)
	 * @return EDI-formatted number
	 */
	public static function GetNextDbNumber($type)
	{
		global $EdiVendor;
		$vendor = strtolower(substr($EdiVendor->mTypeCode, 0, 2));
		$sql = "SELECT $type AS thenum FROM {$vendor}_edi";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		$sql = "UPDATE {$vendor}_edi SET $type = $type + 1";
		mysql_query($sql);
		return str_pad($return['thenum'], 9, '0', STR_PAD_LEFT);
	}
	
	public static function AddToDb($edidata)
	{
		$sql = "SELECT * FROM edi_files WHERE filename = '{$edidata->mFilename}'";
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query) == 0)
		{
			$sql = "INSERT INTO edi_files (filename, confirmed, processed, rejected, po_id) VALUES ('{$edidata->mFilename}', 0, 0, 0, 0)";
			$query2 = mysql_query($sql);
			checkdberror($sql);
			$verpos = strpos($edidata->mData, $edidata->mSeparators->mElement.'00401'.$edidata->mSeparators->mElement);
			$icnpos = $verpos + 7;
			$icn = substr($edidata->mData, $icnpos, 9);
			$icn_actual = ltrim($icn, '0');
			DoEdi::UpdateDb('interchange', $edidata, $icn_actual);
			DoEdi::UpdateDb('sent', $edidata, date('Y-m-d H:i:s'));
			$fileId = DoEdi::GetDb('ID', $edidata);
			$searcher = new EdiParser($edidata);
			$temp = $searcher->GetSplitData();
			$groupset = array_keys($temp, 'GS');
			$transset = array_keys($temp, 'ST');
			for($i=0; $i<count($groupset); $i++)
			{
				$groupId = DoEdi::AddGroupId($fileId, ltrim($temp[$groupset[$i] + 6], '0'));
				foreach($transset as $thistrans)
				{
					// filter for the right kind of ST element...
					if($temp[$thistrans + 1] != '850' && $temp[$thistrans + 1] != '860' && 
					$temp[$thistrans + 1] != '824' && $temp[$thistrans + 1] != '997' && 
					$temp[$thistrans + 1] != '856' && $temp[$thistrans + 1] != '855' &&
					$temp[$thistrans + 1] != '832' && $temp[$thistrans + 1] != '865' &&
					$temp[$thistrans + 1] != '846') continue;
					if($thistrans > $groupset[$i] && (!isset($groupset[$i+1]) || $thistrans < 	$groupset[$i+i]))
					{
						DoEdi::AddTransId($groupId, ltrim($temp[$thistrans + 2], '0'));
					}
				}
			}
			return array('confirmed' => 0, 'processed' => 0, 'rejected' => 0, 'po_id' => 0);
		}
		else
		{
			$return = mysql_fetch_assoc($query);
			return $return;
		}
	}
	
	public function GetFilenameFromGroupId($group)
	{
		$vendor = strtolower(substr($this->mEdiVendor->mTypeCode, 0, 2));
		$sql = "SELECT file_id FROM {$vendor}_edi_groups WHERE `group` = $group";
		$query = mysql_query($sql);
		checkDBerror($sql);
		$ret = mysql_fetch_assoc($que);
		$sql = "SELECT filename FROM edi_files WHERE ID = ".$ret['file_id'];
		$que = mysql_query($sql);
		checkDBerror($sql);
		$ret2 = mysql_fetch_assoc($que);
		return $ret2['filename'];
	}
	
	public static function AddGroupId($fileId, $groupId)
	{
		global $EdiVendor;
		$vendor = strtolower(substr($EdiVendor->mTypeCode, 0, 2));
		$sql = "INSERT INTO {$vendor}_edi_groups (`group`, `file_id`) VALUES ('$groupId', '$fileId')";
		mysql_query($sql);
		checkdberror($sql);
		return mysql_insert_id();
	}
	
	public function AddTransId($groupId, $transId)
	{
		global $EdiVendor;
		$vendor = strtolower(substr($EdiVendor->mTypeCode, 0, 2));
		$sql = "INSERT INTO {$vendor}_edi_transactions (`transaction`, `group_id`) VALUES ('$transId', '$groupId')";
		mysql_query($sql);
		checkdberror($sql);
		return mysql_insert_id();
	}
	
	public static function SetArchiveFolder($filename, $folder = null)
	{
		if(is_null($folder)) $folder = date('Ym');
		$sql = "UPDATE edi_files SET archive_folder = '$folder' WHERE filename = '$filename'";
		$que = mysql_query($sql);
		checkdberror($sql);
	}
	
	public static function GetArchiveFolder($filename)
	{
		$sql = "SELECT archive_folder FROM edi_files WHERE filename = '$filename'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($que);
		return is_null($return['archive_folder']) || $return['archive_folder'] == '' ? null : $return['archive_folder'];
	}
	

	public static function GetDb($field, $edidata)
	{
		$sql = "SELECT $field FROM edi_files WHERE filename = '{$edidata->mFilename}'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($que);
		return $return[$field];
	}
	
	public static function UpdateDb($field, $edidata, $value = 1)
	{
		$sql = "UPDATE edi_files SET `$field` = '$value' WHERE filename = '{$edidata->mFilename}'";
		$que = mysql_query($sql);
		checkdberror($sql);
	}
	
	public static function AppendDb($field, $edidata, $value = 1)
	{
		$sql = "SELECT `$field` as fielddata FROM edi_files WHERE filename = '{$edidata->mFilename}'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		$newdata = ($ret['fielddata'] == '0' ? '' : $ret['fielddata']).";".$value;
		$sql = "UPDATE edi_files SET `$field` = '$newdata' WHERE filename = '{$edidata->mFilename}'";
		$que = mysql_query($sql);
		checkdberror($sql);
	}
	
	public static function GetEdiFromPO($po_id)
	{
		$sql = "SELECT filename FROM edi_files WHERE po_id LIKE '%$po_id%'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['filename'];
	}
	
	public static function GetRetailerFormId($retailer, $test)
	{
		$userId = DoEdi::GetRetailerUserId($retailer, $test);
		$sql = "SELECT form FROM form_access WHERE user = '$userId' LIMIT 1";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['form'];
	}
	
	public static function GetRetailerUserId($retailer, $test)
	{
		$sql = "SELECT relation_id FROM login WHERE username LIKE '%$retailer%'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['relation_id'];
	}
}


class DoEdiBuilder extends DoEdi
{
	function __construct()
	{
		parent::__construct();
	}

	public function GetInterchangeNumber()
	{
		return $this->GetNextDbNumber('interchange_number');
	}

	public function GetLineNumber()
	{
		return $this->GetNextDbNumber('line_number');
	}

	public function GetShipmentNumber()
	{
		return $this->GetNextDbNumber('shipment_number');
	}

	public function GetAcknowledgeNumber()
	{
		return $this->GetNextDbNumber('acknowledge_number');
	}
	
	public function GetInventoryNumber()
	{
		return $this->GetNextDbNumber('inventory_number');
	}
	
	public function GetTransactionNumber()
	{
		return $this->GetNextDbNumber('transaction_number');
	}
	
	public function GetErrorNumber()
	{
		return $this->GetNextDbNumber('error_number');
	}

        public function GetPurchaseOrderNumber()
	{
		return $this->GetNextDbNumber('purchase_order_number');
	}
	
	public function GetSHInformation($shipping)
	{
		// does an entry exist where filename like 856SH and retailer_po is set to the po and po_id is the bol id?
		$sql = "SELECT * FROM edi_files WHERE filename LIKE '%856SH%' AND retailer_po = '".$shipping->mRetailerPO."' AND po_id = '".$shipping->mBolId."'";
		$que = mysql_query($sql);
		checkDBerror($sql);
		while($return = mysql_fetch_assoc($que))
		{
			return $return;
		}
		return false; // doesnt exist
	}
	
	public function GetPMDId()
	{
		$sql = "SELECT edi_id FROM edi_vendor WHERE vendor = 'pmd'";
		$que = mysql_query($sql);
		checkDBerror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['edi_id'];
	}
	
	public function GetPMDQualifier()
	{
		$sql = "SELECT edi_qualifier FROM edi_vendor WHERE vendor = 'pmd'";
		$que = mysql_query($sql);
		checkDBerror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['edi_qualifier'];
	}
}


class DoEdiCABuilder
{
	/**
	 * The Instance var to keep track of the DoEdi class singleton instance
	 * @var Db
	 */
	private static $mInstance;
	private $mDb;
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return DoEdi An instance of this class.
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	function __construct()
	{
		//$this->mDb = Db::getInstance();
	}

	public function GetItemData($item)
	{
		global $EdiVendor;
		echo "in DoEdiCABuilder->GetItemData...";
		print_r($EdiVendor);
		// get the header for the vendor form
		$sql = "SELECT ID FROM form_headers WHERE form = '{$EdiVendor->mVendorFormID}'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		$sql = "SELECT cubic_ft, weight FROM form_items WHERE partno = '{$item->mSKU}' AND header = {$ret['ID']}";
		$qu = mysql_query($sql);
		checkDBError($sql);
		$return = mysql_fetch_assoc($qu);
		return $return;
	}
}


class DoEdiCancel
{
	/**
	 * The Instance var to keep track of the DoEdi class singleton instance
	 * @var Db
	 */
	private static $mInstance;
	private $mDb;
	public $mEdiVendor;
	
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return DoEdi An instance of this class.
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	function __construct()
	{
		global $EdiVendor;
		//$this->mDb = Db::getInstance();
		$this->mEdiVendor = &$EdiVendor;
	}

	public function GetPONumber($retpo)
	{
		$sql = "SELECT po_id FROM edi_files WHERE retailer_po LIKE '%$retpo%'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($que);
		$test = $return['po_id'];
		if($test == '' || is_null($test)) return 0;
		$test1 = explode(';', $test);
		if($test1[0]=='') array_shift($test1);
		$temp = implode(',', $test1);
		// get the specific PO now
		// yes, i know it sucks
		$sq2 = "SELECT * FROM order_forms WHERE ID IN ($temp) AND comments LIKE '%$retpo%'";
		$qu2 = mysql_query($sq2);
		checkdberror($sq2);
		if(mysql_num_rows($qu2)>0)
		{
			$res = mysql_fetch_assoc($qu2);
			return $res['ID'];
		}
		return 0;
	}
	
	public function GetFilenameFromOriginalPONumber($retpo)
	{
		$sql = "SELECT filename FROM edi_files WHERE retailer_po LIKE '%$retpo%'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$return = mysql_fetch_assoc($que);
		return $return['filename'];
	}
	
	public function SetArchiveFolder($filename, $folder = null)
	{
		if(is_null($folder)) $folder = date('Ym');
		$sql = "UPDATE edi_files SET archive_folder = '$folder' WHERE filename = '$filename'";
		$que = mysql_query($sql);
		checkdberror($sql);
	}
	
	public function GetArchiveFolder($filename)
	{
		$sql = "SELECT archive_folder FROM edi_files WHERE filename = '$filename'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($que);
		return is_null($return['archive_folder']) || $return['archive_folder'] == '' ? null : $return['archive_folder'];
	}
	
	public function GetOrigPOFilename($po)
	{
		$sql = "SELECT filename FROM edi_files WHERE retailer_po LIKE '%$po%'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($que);
		return $return['filename'];
	}
	
	public function GetItemIdFromOrigLineNumber($po, $line)
	{
		$sql = "SELECT ID FROM orders WHERE po_id IN ($po) AND po_lineid = '$line'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($que);
		return $return['ID'];
	}
	
	public function IsTotalCancel($po, $itemid)
	{
		// function checks if the entire PO is eliminated
		// searches the orders table by the po, removing the item ids passed in
		// if any returned, not a total elimination
		$sql = "SELECT COUNT(*) AS byebye FROM orders WHERE po_id IN ($po) AND ID NOT IN (".implode(',', $itemid).")";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return !$ret['byebye'] > 0;
	}

	public function RemoveFromShipping($po)
	{
		// if the order is in the shipping queue, remove it (i.e. turn complete on)
		$sql = "SELECT ID FROM BoL_queue WHERE po = $po";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		if(is_numeric($ret['ID']))
		{
			$sql = "UPDATE BoL_queue SET complete = 1 WHERE ID = ".$ret['ID'];
			$que = mysql_query($sql);
			checkdberror($sql);
		}
	}
	
	public function RemoveItemsFromShipping($po, $items, $cancel_po)
	{
		// first we see if the order is in shipping...
		$sql = "SELECT ID FROM BoL_queue WHERE po = $po";
		$que = mysql_query($sql);
		checkDBerror($sql);
		$ret = mysql_fetch_assoc($que);
		if(is_numeric($ret['ID']) && $ret['ID'] != 0)
		{
			$queueId = $ret['ID'];
			// itemIds = orders.ID
			// get item id from orders table, then make BoL_items entries
			// should reduce # available
			foreach($items as $lineitem)
			{
				$sql = "SELECT item, setqty, mattqty, qty AS boxqty FROM orders WHERE po_id = $po AND po_lineid = {$lineitem->mLineNumber}";
				$que = mysql_query($sql);
				checkDBerror($sql);
				$ret = mysql_fetch_assoc($que);
				// verify the line items have not already been credited before making new credits
				$sq2 = "SELECT setamt, mattamt, boxamt FROM BoL_items WHERE type = 'cred' AND item = '".$ret['item']."' AND lineid = '{$lineitem->mLineNumber}'";
				$que2 = mysql_query($sq2);
				checkDBerror($sq2);
				$ret2 = mysql_fetch_assoc($que2);
				if($ret2['boxamt'] != 0)
				{
					// already canceled, no need to do another
					continue;
				}
				$sql = "INSERT INTO BoL_forms (po, queue_id, user_id, type, createdate, boxamt, credit_approved, credit_po) VALUES ('$po', '$queueId', '{$this->mEdiVendor->mVendorUserID}', 'cred', NOW(), ".$lineitem->mQtyCanceled.", 1, '$cancel_po')";
				mysql_query($sql);
				checkDBerror($sql);
				$bolid = mysql_insert_id();
				$sql = "INSERT INTO BoL_items (bol_id, po, type, item, lineid, setamt, mattamt, boxamt, credit_reason, credit_approved) VALUES ('$bolid', '$po', 'cred', '".$ret['item']."', '{$lineitem->mLineNumber}', ".$ret['setqty'].", ".$ret['mattqty'].", ".$ret['boxqty'].", '".ucwords($this->mEdiVendor->mVendorName)." Cancellation', 1)";
				mysql_query($sql);
				checkDBerror($sql);
				// check to see if the order is complete; if so, set the complete Boolean to true
				$sql = "SELECT totalset, totalmatt, totalbox FROM BoL_queue WHERE ID = $queueId";
				$que = mysql_query($sql);
				checkdberror($sql);
				$order = mysql_fetch_assoc($que);
				$sql2 = "SELECT SUM(setamt) as totset, SUM(mattamt) as totmatt, SUM(boxamt) as totbox FROM BoL_items WHERE po = $po AND IF(type = 'cred', credit_approved = 1, TRUE)";
				$que2 = mysql_query($sql2);
				checkdberror($sql2);
				$res2 = mysql_fetch_assoc($que2);
				$totset = $res2['totset'];
				$totmatt = $res2['totmatt'];
				$totbox = $res2['totbox'];
				// check the #s
				if($order['totalset']==$totset && $order['totalmatt']==$totmatt && $order['totalbox']==$totbox)
				{
					$sq = "UPDATE BoL_queue SET complete = 1 WHERE po = $po"; // it is complete
					$qu = mysql_query($sq);
					checkdberror($sq,false);
				}
			}
		}
	}
	
	public function CancelOrder($linenums)
	{
		// ok, from the line #s we'll grab the item #s and quantities
		$podata = array();
		foreach($linenums as $thisline)
		{
			$sql = "SELECT item, setqty, mattqty, qty AS boxqty FROM orders WHERE ID = $thisline";
			$que = mysql_query($sql);
			checkdberror($sql);
			$podata[] = mysql_fetch_assoc($que);
		}
		
	}
	
	public function GetSnapshotUser($userid)
	{
		$sql = "SELECT snapshot FROM users WHERE ID = '$userid'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['snapshot'];
	}
	
	public function GetSnapshotItem($item)
	{
		$sql = "SELECT snapshot FROM form_items WHERE ID = '$item'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['snapshot'];
	}
	
	public function SetProcessComment($po, $cancel)
	{
		$cancel = $cancel + 1000;
		$sql = "SELECT comments FROM order_forms WHERE ID = $po";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		$comments = $ret['comments']."\nCancellation Placed on ".date('Y-m-d')."\n(Order #$cancel)";
		$sql = "UPDATE order_forms SET comments = '$comments' WHERE ID = '$po'";
		$que = mysql_query($sql);
		checkdberror($sql);
		// processPO is in inc_content.php
		processPO($po+1000);
	}
	
	public function Submit($vendorpo, $cancelpo, $origedi)
	{
		$po_insert = array();
		$po_insert['user'] = $this->mEdiVendor->mVendorUserID;
		$po_insert['snapshot_user'] = $this->GetSnapshotUser($this->mEdiVendor->mVendorUserID);
		$po_insert['form'] = $this->mEdiVendor->mVendorFormID;
		$po_insert['processed'] = 'Y';
		$po_insert['ordered'] = date('Y-m-d G:i');
		$po_insert['process_time'] = date('Y-m-d G:i:s');
		$po_insert['comments'] = ucfirst($this->mEdiVendor->mVendorName)." Order Cancellation\n";
		
		$po_insert['comments'] .= "Order Cancellation for Retailer Order #$vendorpo)\n";
		$po_insert['comments'] .= "(RSS Order #".($cancelpo->mPONumber+1000).")\n";
		$po_insert['deleted'] = 0;
		$po_insert['type'] = 'c';
		$po_insert['messages'] = array();
		
		// Get snapshot form
		$sql = "SELECT snapshot FROM forms WHERE ID = '".$this->mEdiVendor->mVendorFormID."'";
		$query = mysql_query($sql);
		checkDBError($sql);
		if ($result = mysql_fetch_array($query))
		{
			$snapshot_form = $result[0];
		}
		$po_insert['snapshot_form'] = $snapshot_form ? $snapshot_form : 0;
		
		$sql = "select name, address, city, state, zip, phone, fax, prepaidfreight from " . $table_prefix . "snapshot_forms where id='".$snapshot_form."'";
		$query = mysql_query($sql);
		checkDBError($sql);
		if ($result = mysql_fetch_array($query))
		{
			$po_insert['vendor_name'] = $result['name'];
			$po_insert['vendor_address'] = $result['address'];
			$po_insert['vendor_city'] = $result['city'];
			$po_insert['vendor_state'] = $result['state'];
			$po_insert['vendor_zip'] = $result['zip'];
			$po_insert['vendor_phone'] = $result['phone'];
			$po_insert['vendor_fax'] = $result['fax'];
			$po_insert['prepaidfreight'] = $result['prepaidfreight'];
		}
		$timesthru = 0;
		foreach($cancelpo->mLineItems as $thisline)
		{
			foreach($origedi->mEdiObject->mTransactions as $trans)
			{
				if($trans->mPONumber != $vendorpo) continue;
				foreach($trans->mItems as $checkitem)
				{				
					if($thisline->mLineNumber != $checkitem->mLineNumber) continue;
					$thisitem = array();
					$total = 0;
					$disc_total = 0;
					$checkitem->GetItemId();
					$thisitem['orig_itemid'] = $checkitem->ReturnOrigItemId();
					$sql = "SELECT a.id AS item_snap, b.id as header_snap, a.partno, a.description, a.price, a.set_, a.discount, a.matt, a.box, c.alloc, c.avail, c.stock FROM snapshot_items AS a INNER JOIN form_items AS c ON a.orig_id = c.ID INNER JOIN snapshot_headers AS b ON a.header = b.id WHERE a.id = '".$checkitem->mItemId."'";
					$query = mysql_query($sql);
					checkDBError($sql);
					if ($result = mysql_fetch_assoc($query))
					{
						if ($result['box'] != "")
						{
							$thisitem['price'] = $result['box'];
						}
						else
						{
							$thisitem['price'] = $result['price'];
							if ($result['price'] == "")
								$thisitem['price'] = 0;
						}
					}
					if($thisline->mQtyCanceled != -1 || $thisline->mQtyRemaining != -1)
					{
						// either quantity canceled or quantity remaining is defined
						$itemqty = $thisline->mQtyCanceled;	
					}
					else
					{
						$itemqty = $checkitem->mQuantity;
						//$itemqty = $trans['qty_ordered'][$target];
					}
					$itemcharge = ($checkitem->mChargeAmount != 0 && $checkitem->mChargeAmount != '') ? $checkitem->mChargeAmount : $checkitem->mRetailerUnitCost; // if a charge is defined, use that otherwise grab the retailer unit cost
					$itemshipping = isset($checkitem->mShippingAmount) ? $checkitem->mShippingAmount * $itemqty : 0;
					$itemtax = isset($checkitem->mTaxes) ? $checkitem->mTaxes * $itemqty : 0;
					$thisitem['qty'] = -$itemqty;
					$thisitem['item'] = $checkitem->mItemId;

					// Price Totaling! Yay!
					if (isset($itemqty) && $itemqty != 0)
					{
						$total += round($itemcharge * $itemqty, 2);
						$total += $itemshipping;
						$total += $itemtax;
						$totalqty += $itemqty;
						$qtyordered += $itemqty;
						$total_cubic_ft += round($thisitem['cubic_ft'] * $itemqty, 2);
					}
					$thisitem['total'] = -$total;
					$items[] = $thisitem;
					unset($thisitem);
					// Insert Per Item Discount Here...
					$subtotal += $total;
				}
			}
		}
		
		$po_insert['total'] = -$total;
		$po_insert['totalqty'] = $totalqty;
		$po_insert['total_cubic_ft'] = $total_cubic_ft;
		foreach ($po_insert['messages'] as $message)
		{
			if (checkbox2boolean($message['block']))
			{
				$po_insert['items'] = $items;
				return $po_insert;
			}
		}
		// From here on out, we're committing the order to database..
		// God Help Us All if this fails....
		$sql = buildInsertQuery("order_forms",$po_insert, true);
		mysql_query($sql);
		checkDBError($sql);
		$po_id = mysql_insert_id();
		foreach ($items as $thisitem)
		{
			$sql = "UPDATE form_items SET avail = avail + ".(-$thisitem['qty'])." WHERE ID=".$thisitem['orig_itemid'];
			mysql_query($sql);
			checkDBError($sql);
			$thisitem['ordered_time'] = date("H:i:s");
			$thisitem['form'] = $this->mEdiVendor->mVendorFormID;
			$thisitem['user'] = $this->mEdiVendor->mVendorUserID;
			$thisitem['po_id'] = $po_id;
			$thisitem['ordered'] = $po_insert['ordered'];
			$thisitem['snapshot_form'] = $po_insert['snapshot_form'];
			$thisitem['snapshot_user'] = $po_insert['snapshot_user'];
			$sql = buildInsertQuery("orders",$thisitem, true);
			mysql_query($sql);
			checkDBError($sql);
		}
		return $po_id;
	}
}


class DoEdiItem
{
	private $mDb;
	private static $mInstance;
	
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	public function GetItemIdFromRetailerPartNumber(&$item)
	{
		$sql = "SELECT formitems_id AS id FROM walmart_inventory WHERE item_id = '{$item->mRetailerPartNumber}'";
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query) == 0)
		{
			// if there are no returned rows, we're not dealing w/ Walmart
			// get the ID from another source...
			unset($query);
			$sql = "SELECT ID AS id from form_items WHERE sku LIKE '%{$item->mRetailerPartNumber}%'";
			$query = mysql_query($sql);
			checkDBError($sql);
		}
		$ret = mysql_fetch_assoc($query);
		// now we have the form_items.id, get the snapshot ID
		$sql = "SELECT snapshot FROM form_items WHERE ID = '".$ret['id']."'";
		$query = mysql_query($sql);
		checkDBerror($sql);
		$return = mysql_fetch_assoc($query);
		return $return['snapshot'];
	}
	
	public function GetItemId(&$item)
	{
		$idcheck = $this->GetItemIdFromRetailerPartNumber($item);
		if($idcheck == 0 || $idcheck == '')
		{
			$idcheck = $this->GetItemIdFromSKU($item->mSKU);
			if($idcheck == 0 || $idcheck == '' || $idcheck == -1)
			{
				$idcheck = $this->GetItemIdFromUPC($item);
				if($idcheck == 0 || $idcheck == '' || $idcheck == -1)
				{
					$item->mItemId = 0;
				}
			}
		}
		$item->mItemId = $idcheck;
	}
	
	public function ReturnOrigItemId(&$item)
	{
		$sql = "SELECT orig_id FROM snapshot_items WHERE id = '{$item->mItemId}'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['orig_id'];
	}
	
	public function GetItemStatus(&$item, $walmart = false)
	{
		// first import the $EdiVendor global object, then use the form ID to get the header ID
		global $EdiVendor;
		$sql = "SELECT ID FROM form_headers WHERE form = ".$EdiVendor->mVendorFormID;
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		$item->GetItemId();
		$sql = "SELECT * FROM form_items WHERE ID = ".$this->ReturnOrigItemId($item);
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query)==0)
		{
			return -1;
		}
		$ret = mysql_fetch_assoc($query);
		if($walmart)
		{
			$newupc = ltrim($item->mUPC, '0');
			if(strlen($newupc)==12)	$newupc = substr($newupc, 0, strlen($newupc) - 1); // should get all but the check digit
			$sql = "SELECT avail_code AS statuscode, numavail AS qty FROM walmart_inventory WHERE item_id = '{$item->mRetailerPartNumber}' AND upc LIKE '%$newupc%'";
			$query = mysql_query($sql);
			if(mysql_num_rows($query) == 0) return 0;
			$ret = mysql_fetch_assoc($query);
		}
		return $ret;
	}
	
	public function GetItemIdFromSKU($sku)
	{
		// first import the $EdiVendor global object, then use the form ID to get the header ID
		global $EdiVendor;
		$sql = "SELECT ID FROM form_headers WHERE form = ".$EdiVendor->mVendorFormID;
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		$sql = "SELECT ID FROM form_items WHERE (sku = '$sku' OR partno = '$sku') AND header = '".$ret['ID']."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query)==0)
		{
			return -1;
		}
		$ret = mysql_fetch_assoc($query);
		// now we have the form_items.id, get the snapshot ID
		$sql = "SELECT snapshot FROM form_items WHERE ID = '".$ret['ID']."'";
		$query = mysql_query($sql);
		checkDBerror($sql);
		$return = mysql_fetch_assoc($query);
		return $return['snapshot'];
	}
	
	public function GetItemIdFromUPC(&$item)
	{
		// first import the $EdiVendor global object, then use the form ID to get the header ID
		global $EdiVendor;
		$sql = "SELECT ID FROM form_headers WHERE form = ".$EdiVendor->mVendorFormID;
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		// now we massage the UPC to match
		// remove any prepended zeroes
		$newupc = ltrim($item->mUPC, '0');
		if(strlen($newupc)==12)	$newupc = substr($newupc, 0, strlen($newupc) - 1); // should get all but the check digit
		$sql = "SELECT ID FROM form_items WHERE upc LIKE '%$newupc%' AND header = '{$ret['ID']}'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		// now we have the form_items.id, get the snapshot ID
		$sql = "SELECT snapshot FROM form_items WHERE ID = '".$ret['ID']."'";
		$query = mysql_query($sql);
		checkDBerror($sql);
		$return = mysql_fetch_assoc($query);
		return $return['snapshot'];
		return $ret['ID'];	
	}
}


class DoEdiInventory
{
	private $mDb;
	private $mEdiVendor;
	private static $mInstance;
	
	function __construct()
	{
		global $EdiVendor;
		// $this->mDb = Db::getInstance();
		$this->mEdiVendor = &$EdiVendor;	
	}

	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	public function GetItems()
	{
		// first we get the headers that are in the form
		$sql = "SELECT ID FROM form_headers WHERE form = '".$this->mEdiVendor->mVendorFormID."'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$returns = array();
		while($ret = mysql_fetch_assoc($que))
		{
			$sq = "SELECT ID, partno, description, price, stock AS stockcode, stock_date, stock_day, weight, alloc, avail, sku, upc FROM form_items WHERE header = '".$ret['ID']."'";
			$qu = mysql_query($sq);
			checkDBError($sq);
			while($rets = mysql_fetch_assoc($qu))
			{
				$returns[] = $rets;
			}
		}
		return $returns;
	}
}


class DoEdiOrder
{
	private $mDb;
	protected $mEdiVendor;
	
	// class to add EDI-submitted orders to the system
	function __construct()
	{
		global $EdiVendor;
		// $this->mDb = Db::getInstance();
		$this->mEdiVendor = &$EdiVendor;
	}
	
	public function GetRetailerPO($pmdpo)
	{
		// retrieves the associated retailer po for the PMD po
		$sql = "SELECT comments FROM order_forms WHERE ID = $pmdpo";
		$que = mysql_query($sql);
		checkDBerror($sql);
		while($return = mysql_fetch_assoc($que))
		{
			if($return['comments'] != '')
			{
				$startnum = strpos($return['comments'], 'Order #') + 7;
				$endnum = strpos($return['comments'], "\n");
				return substr($return['comments'], $startnum, $endnum - $startnum);
			}
			else
			{
				return false;
			}
		}
	}
	
	public function GetCancellations($po_id)
	{
		// find the order #s for any cancellations re: this order
		$po = $po_id + 1000;
		$sql = "SELECT ID FROM order_forms WHERE comments LIKE '%Order #$po%' AND comments LIKE '%Cancellation%'";
		$que = mysql_query($sql);
		checkDBerror($sql);
		$returns = array();
		while($ret = mysql_fetch_assoc($que))
		{
			// get item ids of the cancelled items
			$sql = "SELECT item FROM orders WHERE po_id = ".$ret['ID'];
			$que = mysql_query($sql);
			checkDBerror($sql);
			while($data = mysql_fetch_assoc($que))
			{
				$returns[] = $data['item'];
			}
		}
		return $returns;
	}
	
	function Submit(&$order)
	{
		$preview = false;
		$modify_avail = true;
		$block_blocks = true;
		$po_insert = array();
		$po_insert['user'] = $this->mEdiVendor->mVendorUserID;
		$po_insert['form'] = $this->mEdiVendor->mVendorFormID;
		$po_insert['processed'] = 'N';
		$po_insert['ordered'] = date('Y-m-d G:i');
        $po_insert['retailer_orderdate'] = date('Y-m-d', strtotime($order->mRetailPODate));
		$po_insert['comments'] = ucfirst($this->mEdiVendor->mVendorName).' Order # '.$order->mRetailPONumber.(!is_null($order->mCustomerOrderNumber) && $order->mCustomerOrderNumber != '' ? "\nCustomer Order # ".$order->mCustomerOrderNumber : '').(!is_null($order->mAlternateOrderNumber) && $order->mAlternateOrderNumber != '' ? "\nAlternate Order # ".$order->mAlternateOrderNumber : '');
		$po_insert['deleted'] = 0;
		$po_insert['type'] = 'o';
		$po_insert['messages'] = array();
		
		// Get snapshot form
		$sql = "SELECT snapshot FROM forms WHERE ID = '".$this->mEdiVendor->mVendorFormID."'";
		$query = mysql_query($sql);
		checkDBError($sql);
		if ($result = mysql_fetch_array($query))
		{
			$snapshot_form = $result[0];
		}
		$po_insert['snapshot_form'] = isset($snapshot_form) ? $snapshot_form : 0;

		// Get snapshot user
		$sql = "SELECT snapshot FROM users WHERE ID = '".$this->mEdiVendor->mVendorUserID."'";
		$query = mysql_query($sql);
		checkDBerror($sql);
		if($result = mysql_fetch_assoc($query))
		{
			$snapshot_user = $result['snapshot'];
		}
		$po_insert['snapshot_user'] = isset($snapshot_user) ? $snapshot_user : 0;
		$sql = "select name, address, city, state, zip, phone, fax, prepaidfreight from  snapshot_forms where id='".$snapshot_form."'";
		$query = mysql_query($sql);
		checkDBError($sql);
		if ($result = mysql_fetch_array($query))
		{
			$po_insert['vendor_name'] = $result['name'];
			$po_insert['vendor_address'] = $result['address'];
			$po_insert['vendor_city'] = $result['city'];
			$po_insert['vendor_state'] = $result['state'];
			$po_insert['vendor_zip'] = $result['zip'];
			$po_insert['vendor_phone'] = $result['phone'];
			$po_insert['vendor_fax'] = $result['fax'];
			$po_insert['prepaidfreight'] = $result['prepaidfreight'];
		}

		// figure the freight percentage based on the shipping charge
		$freight_percentage = ($order->mTotal - $order->mShippingCost == 0 || $order->mShippingCost == 0) ? 0 : $order->mShippingCost / ($order->mTotal - $order->mShippingCost);
		//$discount_percentage = getDiscountPercentage($this->mEdiVendor->mVendorUserID, $this->mEdiVendor->mVendorFormID);

		$subtotal = 0;
		$totalqty = 0;
		//$itemdiscamount = 0;
		$total_cubic_ft = 0;
		$out_of_stock = 0;
		//$discount_total = 0; // Total twords % discount
		$qtyordered = 0;
		// Process Items Here

		$items = array();
		foreach ($order->mItems as $item)
		{
			$thisitem = array();
			$total = 0;
			$disc_total = 0;
			$sql = "SELECT * FROM form_items WHERE snapshot = ".$item->mItemId;
			$query = mysql_query($sql);
			checkDBError($sql);
			$result = mysql_fetch_array($query);
			$thisitem['orig_item_id'] = $result['ID'];
			if ($result['box'] != "")
			{
				$thisitem['price'] = $result['box'];
			} else {
				$thisitem['price'] = $result['price'] == '' ? 0 : $result['price'];
			}
			$thisitem['cubic_ft'] = $result['cubic_ft'];
			$thisitem['partno'] = $result['partno'];
			$thisitem['description'] = $result['description'];
			$thisitem['set'] = $result['set_'];
			$thisitem['qtyinset'] = $result['setqty'];
			$sql = "SELECT a.id AS item_snap, b.id as header_snap, a.partno, a.description, a.price, a.set_, a.matt, a.box, c.alloc, c.avail, c.stock FROM snapshot_items AS a INNER JOIN form_items AS c ON a.orig_id = c.ID INNER JOIN snapshot_headers AS b ON a.header = b.id WHERE a.id = '".$item->mItemId."'";
			$query = mysql_query($sql);
			checkDBError($sql);
			if ($result = mysql_fetch_assoc($query))
			{
				$thisitem['partno'] = $result['partno'];
				$thisitem['description'] = $result['description'];
				if ($result['box'] != "")
				{
					$thisitem['price'] = $result['box'];
				}
				else
				{
					$thisitem['price'] = $result['price'];
					if ($result['price'] == "")
						$thisitem['price'] = 0;
				}
				$thisitem['set'] = $result['set_'];
				$thisitem['matt'] = $result['matt'];
				$thisitem['item'] = $result['item_snap'];
				$thisitem['header'] = $result['header_snap'];
				$thisitem['alloc'] = $result['alloc'];
				$thisitem['avail'] = $result['avail'];
				$thisitem['stock'] = $result['stock'];
			}
			$thisitem['qty'] = $item->mQuantity;
			if ($thisitem['qty'] != 0 && $thisitem['avail'] >= 0 && $thisitem['qty'] > $thisitem['avail'])
			{
				++$out_of_stock;
				$thisitem['suff_stock'] = false;
			}
			elseif ($thisitem['qty'] == 0 && $thisitem['backorder'])
			{
				$thisitem['suff_stock'] = true;
			}
			elseif (stock_block($thisitem['stock']))
			{
				++$out_of_stock;
				$thisitem['avail'] = 0;
				$thisitem['suff_stock'] = false;
			}
			else
			{
				$thisitem['suff_stock'] = true;
			}

			// Price Totaling! Yay!
			if (isset($item->mQuantity) && $item->mQuantity != 0)
			{
				$total += round($thisitem['price'] * $item->mQuantity, 2);
				$totalqty += $item->mQuantity;
				$qtyordered += $item->mQuantity;
				$total_cubic_ft += round($thisitem['cubic_ft'] * $item->mQuantity, 2);
			}
			$thisitem['total'] = $total;
			$items[] = $thisitem;
			unset($thisitem);
			// Insert Per Item Discount Here...
			$subtotal += $total;
			$discount_total += $disc_total;
		}

		$freight = $subtotal * ($freight_percentage * .01);
		$grandtotal = $subtotal + $freight;

		if ($freight == "-0") $freight = 0;

		$po_insert['product_total'] = $producttotal;
		$po_insert['subtotal'] = $subtotal;
		$po_insert['freight'] = $freight;
		$po_insert['freight_percentage'] = number_format($freight_percentage * 100, 2);
		$po_insert['total'] = $order->mTotal;
		$po_insert['totalqty'] = $totalqty;
		$po_insert['total_cubic_ft'] = $total_cubic_ft;
		$po_insert['out_of_stock'] = $out_of_stock;
	
		if ($out_of_stock)
		{
			$message = array();
			$message['text'] = "One or more of the items in your order is insufficiently stocked. ".
		  "Please go back and reduce the quantities of those items.";
			$message['block'] = 'Y';
			$po_insert['messages']['nostock'] = $message;
		}
	
		foreach ($po_insert['messages'] as $message)
		{
			if (checkbox2boolean($message['block']))
			{
				$po_insert['items'] = $items;
				return $po_insert;
			}
		}

		$po_insert['customer'] = $order->mBillTo->mUserId;
		$po_insert['shipto'] = $order->mShipTo->mUserId;
		// From here on out, we're committing the order to database..
		// God Help Us All if this fails....
		$sql = buildInsertQuery("order_forms",$po_insert, true);
		mysql_query($sql);
		checkDBError($sql);
		$po_id = mysql_insert_id();
		$itemcount = 0;
		foreach ($order->mItems as $item)
		{
			$thisitem = array();
			$thisitem['qty'] = $item->mQuantity;
			$thisitem['item'] = $item->mItemId;
			$thisitem['ordered_time'] = date("H:i:s");
			$thisitem['form'] = $this->mEdiVendor->mVendorFormID;
			$thisitem['user'] = $this->mEdiVendor->mVendorUserID;
			$thisitem['po_id'] = $po_id;
			$thisitem['po_lineid'] = $item->mLineNumber;
			$thisitem['ordered'] = $po_insert['ordered'];
			$thisitem['snapshot_user'] = $order->mBillTo->mUserId;
			$thisitem['snapshot_form'] = $po_insert['snapshot_form'];
			if ($modify_avail)
			{
				if ($item->mQuantity > 0) { // If it's negative, we don't want it to affect alloc
					if ($items[$itemcount]['alloc'] != "" && $items[$itemcount]['alloc'] >= 0) {
						if ($items[$itemcount]['qty'] >= $items[$itemcount]['avail'] && $items[$itemcount]['stock'] == 1) {
							$temp = stock_status(2);
							$query2 = "";
							if ($temp['zeroday'] == 'Y') {
								$query2 .= " ,stock_day=0";
							}
							$query2 = " ,stock=2";
							unset($temp);
						} else {
							$query2 = "";
						}
						$sql = "UPDATE form_items SET avail=avail-".$items[$itemcount]['qty']."${query2} WHERE ID=".$items[$itemcount]['orig_item_id'];
						mysql_query($sql);
						checkDBError($sql);
					}
				}
			}
			$sql = buildInsertQuery("orders",$thisitem, true);
			mysql_query($sql);
			checkDBError($sql);
			// apply any msrp_applied mappings as needed
			// get the last ID
			$lastid = mysql_insert_id();
			if($items[$itemcount]['price'] != $item->mChargeAmount)
			{
				// the charged price is different than the PMD price
				// add to the db
				$sql = "INSERT INTO msrp_applied (orders_id, msrp) VALUES ('$lastid', '{$item->mChargeAmount}')";
				mysql_query($sql);
				checkDBerror($sql);
			}
		}
		return $po_id;
	}
	
	
	public function GetBolId($po_id)
	{
		$sql = "SELECT DISTINCT bol_id FROM BoL_items WHERE po = $po_id AND type = 'bol'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		return $return['bol_id'];
	}
}


class DoEdiParser
{
	/**
	 * The Instance var to keep track of the DoEdi class singleton instance
	 * @var Db
	 */
	private static $mInstance;
	private $mDb;
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return DoEdi An instance of this class.
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	function __construct()
	{
		//$this->mDb = Db::getInstance();
	}
	
	public function PriceCheck($upc, $cost)
	{
		// verifies if the passed-in cost is the same as in the db
		$sql = "SELECT price FROM form_items WHERE upc LIKE '%$upc%'";
		$que = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret['price'] == $cost ? 1 : $ret['price'];
	}
}





class DoEdiSh
{
	private $mDb;
	
	function __construct()
	{
		// $this->mDb = Db::getInstance();
	}
	
	public function CheckForEdi($bol)
	{
		// checks for the existence of an EDI file from the given bol ID
		// now see if there's an 856 EDI file in the db w/ the given po_id
		// 856s only: po_id = bol_id
		$sq = "SELECT filename FROM edi_files WHERE filename LIKE 'WMI_856SH_%' AND po_id = '$bol'";
		$que = mysql_query($sq);
		checkDBerror($sq);
		if(mysql_num_rows($que) > 0) return true;
		return false;
	}
}


class DoEdiUser
{
	private $mDb;
	public $mEdiVendor;
	
	function __construct()
	{
		global $EdiVendor;
		// $this->mDb = Db::getInstance();
		$this->mEdiVendor = &$EdiVendor;
	}
	
	public function AddEdiUser($userobj)
	{
		// grab the telephone numbers
		$phone = array();
		$emails = array();
		foreach($userobj->mContactTelephone as $thisphone)
		{
			$phone[] = $thisphone;
		}
		foreach($userobj->mContactEmail as $thisemail)
		{
			$emails[] = $thisemail;
		}
		// adds EDI user to system if needed; returns snapshot_user ID
		$sqlphone = '';
		$sqlemail = '';
		$insertsqlphone = 'phone';
		$valuesqlphone = '';
		$insertsqlemail = 'email';
		$numphone = 1;
		foreach($phone as $thisphone)
		{
			$sqlphone = "phone".($numphone > 1 ? $numphone : "")." = '$thisphone' AND ";
			$insertsqlphone .= $numphone > 1 ? ", phone".$numphone : '';
			$valuesqlphone .= $numphone > 1 ? "', '$thisphone" : "$thisphone";
			$numphone++;
		}
		$numemail = 1;
		foreach($emails as $thisemail)
		{
			$sqlemail = "email".($numemail > 1 ? $numemail : "")." = '$thisemail' AND ";
			$insertsqlemail .= $numemail > 1 ? ", email".$numemail : "";
			$valuesqlemail .= $numemail > 1 ? "', '$thisemail" : "$thisemail";
			$numemail++;
		}
		$sql = "SELECT id FROM snapshot_users WHERE last_name = '".$userobj->mName."' AND address = '".$userobj->mAddress1."' AND address2 = '".$userobj->mAddress2."' AND city = '".$userobj->mCity."' AND ".$sqlphone.$sqlemail."state = '".$userobj->mState."' AND zip = '".$userobj->mPostal."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query) == 0)
		{
			$sql = "INSERT INTO snapshot_users (orig_id, last_name, address, address2, city, state, zip, $insertsqlphone, $insertsqlemail) VALUES (".$this->mEdiVendor->mVendorUserID.", '".$userobj->mName."', '".$userobj->mAddress1."', '".$userobj->mAddress2."', '".$userobj->mCity."', '".$userobj->mState."', '".$userobj->mPostal."', '".$valuesqlphone."', '".$valuesqlemail."')";
			$query = mysql_query($sql);
			checkdberror($sql);
			return mysql_insert_id();
		}
		else
		{
			$ret = mysql_fetch_assoc($query);
			return $ret['id'];
		}
	}
	
	public function GetEdiUser($userid)
	{
		$details = array();
		$sql = "SELECT * FROM snapshot_users WHERE ID = $userid";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		return $return;
	}
}


class DoEdiVendor
{
	/**
	 * The Instance var to keep track of the DoEdi class singleton instance
	 * @var Db
	 */
	private static $mInstance;
	private $mDb;
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return DoEdi An instance of this class.
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	function __construct()
	{
		//$this->mDb = Db::getInstance();
	}
	
	public function GetFromEdiID($edi_id)
	{
		// retrieves vendor data from the db
		$sql = "SELECT * FROM edi_vendor WHERE edi_id = '$edi_id'";
		$que = mysql_query($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret;
	}
	
	public function GetFromVendorName($vendor)
	{
		// retrieves vendor data from the db from passed-in vendor name
		$sql = "SELECT * FROM edi_vendor WHERE vendor = '$vendor'";
		$que = mysql_query($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret;
	}
	
	public function GetAllVendors()
	{
		// retrieves all vendor data from the db
		$sql = "SELECT * FROM edi_vendor";
		$que = mysql_query($sql);
		while($ret = mysql_fetch_assoc($que))
		{
			$returns[] = $ret;
		}
		return $returns;
	}
	
	public function GetFromTypeCode($typecode)
	{
		// retrieves the vendor info from a passed in typecode
		$sql = "SELECT * FROM edi_vendor WHERE typecode = '$typecode'";
		$que = mysql_query($sql);
		$ret = mysql_fetch_assoc($que);
		return $ret;
	}
	
	public function GetFromFilename($filename)
	{
		// parse the filename for a vendor ID
		$names = split('_', $filename);
		// first 3 chars of the filename will be the typecode of the vendor in question
		return $this->GetFromTypeCode($names[0]);
	}
}

?>