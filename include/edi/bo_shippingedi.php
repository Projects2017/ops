<?php
// bo_shippingedi.php


class ShippingEdi
{
	public $mBolId;
	public $mShipmentId; // [BOL]YYYYMMDD
	public $mShipDateTime; // can be Ymd Hi, but must also have time
	public $mPODate;
	public $mPO;
	public $mRetailerPO; // PO from the incoming retailer 850 (PO)
    public $mRetailerPODate;
	public $mRetailerPORevision;
	public $mShipToUser;
	public $mShipToStore;
	public $mEdiFilename;
	public $mPackages; // array of packages
	public $mEdiVendor;
	private $mDoShippingEdi;
	
	function __construct()
	{
		//global $EdiVendor;
		$this->mEdiVendor = new EdiVendor();
		$this->mEdiVendor->LoadDefault();
		$this->mShipDateTime = date('Ymd Hi');
		$this->mDoShippingEdi = DoShippingEdi::getInstance();
		$this->mShipToStore = false;
	}
	
	public function SetBOL($bol)
	{
		$this->mBolId = $bol;
	}
	
	public function Load($bol = null)
	{
		if(is_null($bol) && !is_null($this->mBolId))
		{
			$bol = $this->mBolId;
		}
		else if(is_null($bol) && is_null($this->mBolId))
		{
			return -1;
		}
		$this->mPackages = new ShippingPackages();
		$this->mPackages->GetAllPackagesForBol($bol);
		//$package = new ShippingPackage();
		foreach($this->mPackages as $pack)
		{
			if(!is_null($pack->mBarCode) && $pack->mBarCode != '') $this->mShipToStore = true;
			if($this->mShipmentId == '') $this->mShipmentId = substr($pack->mId, -1).substr($bol, 0, 4).date('ymd'); // sets the shipment id from the first package
		}
		$this->mEdiFilename = $this->mDoShippingEdi->GetFilename($bol);
		$this->mShipDateTime = $this->mDoShippingEdi->GetShipDate($bol);
		$this->mPO = $this->mDoShippingEdi->GetPO($bol);
		$this->mPODate = $this->mDoShippingEdi->GetPODate($bol);
		$this->mShipToUser = $this->mDoShippingEdi->GetShipToUser($this);
		$this->mRetailerPO = $this->mDoShippingEdi->GetRetailerPO($bol);
        $this->mRetailerPODate = $this->mDoShippingEdi->GetRetailerPODate($bol);
		$this->mRetailerPORevision = $this->mDoShippingEdi->GetRetailerPORevision($bol);
	}


		
	public function GetRetailerPOFromOrder($po)
	{
		$this->mRetailerPO = $this->mDoShippingEdi->GetRetailerPOFromOrder($po);
	}
}

class ShippingPackages implements Iterator
{
	private $mPacks;
	private $mPos;
	private $mDoShippingPackages;
	
	function __construct()
	{
		$this->mPacks = array();
		$this->mPos = 0;
		$this->mDoShippingPackages = DoShippingPackages::getInstance();
	}
	
	function rewind()
	{
		$this->mPos = 0;
	}

	function current()
	{
		return $this->mPacks[$this->mPos];
	}

	function key()
	{
		return $this->mPos;
	}

	function next()
	{
		++$this->mPos;
	}

	function valid()
	{
		return isset($this->mPacks[$this->mPos]);
	}
	
	public function Add()
	{
		$this->mPacks[] = new ShippingPackage();
		$ret = &end($this->mPacks);
		reset($this->mPacks);
		return $ret;
	}
	
	public function GetAllPackagesForBol($bol)
	{
		$packages = $this->mDoShippingPackages->GetAllPackageIds($bol);
		$return = array();
		foreach($packages as $packId)
		{
			$newpack = $this->Add();
			$newpack->mId = $packId;
			$newpack->Load();
		}
	}
}


class ShippingPackage
{
	public $mDoShippingPackage;
	public $mId;
	public $mBolId;
	public $mWeight; // weight of package in lbs.
	public $mSize;
	public $mCarrierName;
	public $mCarrierCode;
	public $mEdiCarrierCode; // carrier code sent in the orig EDI
	public $mPackageIdentifier;
	public $mStoreNumber;
	public $mBarCode;
	public $mFreight; // freight cost
	public $mPMDPays; // boolean to determine whether PMD pays shipping cost (all but UPS)
	public $mItems; // array of ShippingItems;
	
	function __construct()
	{
		$this->mPackageNumbers = array();
		$this->mItems = new ShippingItems();
		$this->mSize = new ShippingPackageSize();
		$this->mPMDPays = true; // preseting to true for now, only override if UPS
		$this->mDoShippingPackage = DoShippingPackage::getInstance();
	}
	
	public function GetPMDPays($shipcode)
	{
		return $this->mDoShippingPackage->GetPMDPaysBool($shipcode);
	}
	
	public function Load()
	{
		$details = array();
		$details = $this->mDoShippingPackage->GetPackage($this->mId);
		$this->mBolId = $details['bol'];
		$this->mEdiCarrierCode = $details['orig_carrier_code'];
		$this->mCarrierCode = $details['carrier_code'];
		$this->mPMDPays = $this->GetPMDPays($this->mCarrierCode);
		$this->mCarrierName = $this->mDoShippingPackage->GetCarrierName($this->mCarrierCode);
		$this->mPackageIdentifier = $details['package_identifier'];
		$this->mWeight = $details['weight'];
		$this->mBarCode = $details['bar_code'];
		$this->mFreight = $details['freight'];
		$this->mStoreNumber = $details['store_number'];
		$items = $this->mDoShippingPackage->GetItemIds($this->mId);
		foreach($items as $itemId)
		{
			$newitem = $this->mItems->Add();
			$newitem->mId = $itemId;
			$newitem->Load();
		}
		unset($items);	
	}
	
	public function OptimizeItems()
	{
		$itemdata = array(); // hack to store ids as key of an array, quants as value
		foreach($this->mItems as $thisitem)
		{
			$itemdata[$thisitem->mSKU] += $thisitem->mQtyShipped;
		}
		foreach($this->mItems as $thisitem)
		{
			if(array_key_exists($thisitem->mSKU, $itemdata) && $thisitem->mQtyShipped != $itemdata[$thisitem->mSKU])
			{
				$thisitem->mQtyShipped = $itemdata[$thisitem->mSKU];
				unset($itemdata[$thisitem->mSKU]);
			}
			else if(!array_key_exists($thisitem->mSKU, $itemdata))
			{
				unset($thisitem);
			}
		}
	}
}

class ShippingPackageSize
{
	public $mHeight;
	public $mWidth;
	public $mLength;
}

class ShippingItem
{
	public $mId;
	public $mPOLineNumber; // line number from the original EDI order
	public $mBOLLineNumber;
	public $mStoreNumber; // may be null if no store number included
	public $mQtyOrdered;
	public $mQtyShipped;
	public $mUPC;
	public $mSKU;
	public $mRetailerPartNumber;
	public $mASIN; // Amazon ID Number
	public $mDescription;
	public $mRetailPrice;
	public $mPrice;
	public $mHandlingCost;
	public $mGiftWrapping;
	public $mGiftWrappingCost;
	public $mGiftTag;
	public $mGiftTagCost;
	public $mGiftMessage; // array of up to four lines
	public $mItemMessage;
	public $mGiftMessageCost;
	private $mDoShippingItem;
	
	function __construct()
	{
		$this->mDoShippingItem = DoShippingItem::getInstance();
	}
	
	public function Load()
	{
		$details = array();
		$details = $this->mDoShippingItem->GetItemDetails($this->mId);
		$this->mPOLineNumber = $details['po_linenumber'];
		$this->mStoreNumber = is_null($details['store_number']) || $details['store_number'] == 0 ? null : $details['store_number'];
		$this->mQtyShipped = $details['qty'];
		$this->mQtyOrdered = $details['qty_ordered'];
		$this->mUPC = $details['upc'];
		$this->mSKU = $details['sku'];
		$this->mASIN = ''; // blank for now TODOASIN
		$this->mRetailerPartNumber = $details['retailer_partno'];
		$this->mDescription = $details['description'];
		$this->mRetailPrice = $details['retail_price'];
		$this->mPrice = $details['price'];
		$this->mHandlingCost = $details['handling_cost'];
		$this->mGiftWrapping = $details['giftwrap'];
		$this->mGiftWrappingCost = $details['giftwrap_cost'];
		$this->mGiftTag = $details['gifttag'];
		$this->mGiftTagCost = $details['gifttag_cost'];
		$this->mGiftMessage = $details['giftmessage'];
		$this->mGiftMessageCost = $details['giftmessage_cost'];
		$this->mItemMessage = $this->mGiftMessage;
	}
}

class ShippingItems implements Iterator
{
	private $mItems;
	private $mPos;
	
	function __construct()
	{
		$this->mItems = array();
		$this->mPos = 0;
	}
	
	function rewind()
	{
		$this->mPos = 0;
	}

	function current()
	{
		return $this->mItems[$this->mPos];
	}

	function key()
	{
		return $this->mPos;
	}

	function next()
	{
		++$this->mPos;
	}

	function valid()
	{
		return isset($this->mItems[$this->mPos]);
	}
	
	public function Add()
	{
		$this->mItems[] = new ShippingItem();
		$ret = &end($this->mItems);
		reset($this->mItems);
		return $ret;
	}
}


class DoShippingEdi
{
	private static $mInstance;
	
	public function __construct()
	{
		//$this->mDb = Db::getInstance();
	}

	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	public function GetFilename($bol)
	{
		$po_id = $this->GetPO($bol);
		$sql = "SELECT filename FROM edi_files WHERE po_id LIKE '%$po_id%'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($query);
		return $ret['filename'];
	}
	
	public function GetPO($bol)
	{
		$sql = "SELECT DISTINCT po FROM BoL_items WHERE bol_id = '$bol'";
		$query = mysql_query($sql);
		checkdberror($sql);
		while($ret = mysql_fetch_assoc($query))
		{
			$po_id = $ret['po'];
		}
		return $po_id;
	}
	
	public function GetShipDate($bol)
	{
		$sql = "SELECT createdate FROM BoL_forms WHERE ID = '$bol'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		return date('Y-m-d H:i:s', strtotime($return['createdate']));
	}
	
	public function GetPODate($bol)
	{
		// first we get the PO #
		$po_id = $this->GetPO($bol);
		$sql = "SELECT ordered FROM order_forms WHERE ID = '$po_id'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		return date('Ymd', strtotime($ret['ordered']));
	}
	
	public function GetRetailerPO($bol)
	{
		$filename = $this->GetFilename($bol);
		$thispo = $this->GetPO($bol);
		$sql = "SELECT retailer_po, po_id FROM edi_files WHERE filename = '$filename'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		extract($return);
		$pos = explode(';',$po_id);
		$retailerpos = explode(';',$retailer_po);
		for($i=0;$i<count($pos);$i++)
		{
			if($pos[$i]==$thispo) return $retailerpos[$i];
		}
		return -1;
	}

    public function GetRetailerPODate($bol)
    {
        $thispo = $this->GetPO($bol);
        $sql = "SELECT retailer_orderdate FROM order_forms WHERE ID = $thispo";
        $query = mysql_query($sql);
        checkDBError($sql);
        $ret = mysql_fetch_assoc($query);
        return $ret['retailer_orderdate'];
    }
    
	public function GetRetailerPOFromOrder($po)
	{
		$sql = "SELECT comments FROM order_forms WHERE ID = $po";
		$que = mysql_query($sql);
		checkDBError($sql);
		while($return = mysql_fetch_assoc($que))
		{
			if($return['comments'] != '')
			{
				$startnum = strpos($return['comments'], 'Order #') + 7;
				$endnum = strpos($return['comments'], "\n");
				return trim(substr($return['comments'], $startnum, $endnum - $startnum));
			}
			else
			{
				return false;
			}
		}
	}
	
	public function GetShipToUser($po_object)
	{
		// function returns the EdiUser of the person the item will be shipped to
		
		$sql = "SELECT shipto FROM order_forms WHERE ID = '{$po_object->mPO}'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		// now we have the ID, load 'em up
		$user = new EdiUser();
		$user->mUserId = $ret['shipto'];
		$user->Load();
		$user->SetVendor($po_object->mEdiVendor);
		return $user;
	}
	
	public function GetRetailerPORevision($bol)
	{
		// function will retrieve the revision # of a changed PO
		// return null for now
		return null;
	}
}

class DoShippingPackage
{
	private static $mInstance;
	public $mEdiVendor;
	
	public function __construct()
	{
		global $EdiVendor;
		$this->mEdiVendor = &$EdiVendor;
		//$this->mDb = Db::getInstance();
	}

	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	public function GetPMDPaysBool($shipid)
	{
		if($this->mEdiVendor->mTypeCode != 'WMI') return true;
		// all but UPS orders, PMD pays...so if the ship id is a UPS one, PMD doesn't pay
		$sql = "SELECT * FROM wm_shipcodes WHERE code = '$shipid' AND `name` LIKE '%UPS%'";
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query) > 0) return false;
		return true;
	}
	
	public function LoadFromBOL($bol)
	{
		$sql = "SELECT * FROM shipping_packages WHERE bol = $bol";
		$query = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($query);
		return $ret;
	}
	
	public function GetPackage($id)
	{
		$sql = "SELECT * FROM shipping_packages WHERE ID = '$id'";
		$query = mysql_query($sql);
		checkdberror($sql);
		return mysql_fetch_assoc($query);
	}
	
	public function GetCarrierName($code)
	{
		// retrieves carrier name based on code
		// checks EDI codes first, then shipping codes via CH
		$sql = "SELECT * FROM wm_shipcodes WHERE code = '".ltrim($code, "0")."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		if(mysql_num_rows($query) == 0)
		{
			// now check CH ship codes
			$sql = "SELECT * FROM ch_shipcodes WHERE shipcode = '".ltrim($code, "0")."'";
			$query = mysql_query($sql);
			checkdberror($sql);
			if(mysql_num_rows($query) == 0)
			{
				return "";
			}
			$ret = mysql_fetch_assoc($query);
			return $ret['description'];
		}
		else
		$return = mysql_fetch_assoc($query);
		return $return['name'];
	}
	
	public function GetItemIds($id)
	{
		$sql = "SELECT ID FROM shipping_items WHERE package_id = '$id'";
		$query = mysql_query($sql);
		checkdberror($sql);
		while($ret = mysql_fetch_assoc($query))
		{
			$returns[] = $ret['ID'];
		}
		return $returns;
	}
}

class DoShippingPackages
{
	private static $mInstance;
	
	public function __construct()
	{
		//$this->mDb = Db::getInstance();
	}

	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	public function GetAllPackageIds($bol)
	{
		$sql = "SELECT ID FROM shipping_packages WHERE bol = '$bol'";
		$query = mysql_query($sql);
		checkdberror($sql);
		while($return = mysql_fetch_assoc($query))
		{
			$data[] = $return['ID'];
		}
		return $data;
	}
}

class DoShippingItem
{
	private static $mInstance;
	
	public function __construct()
	{
		//$this->mDb = Db::getInstance();
	}

	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	public function GetItemDetails($id)
	{
		$sql = "SELECT * FROM shipping_items WHERE ID = '$id'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$return = mysql_fetch_assoc($query);
		$sql = "SELECT bol FROM shipping_packages WHERE ID = ".$return['package_id'];
		$query = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($query);
		$sql = "SELECT item, po FROM BoL_items WHERE bol_id = ".$ret['bol']." AND lineid = ".$return['po_linenumber'];
		$query = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($query);
		$itempo = $ret['po'];
		$thisitem = $ret['item'];
		$sql = "SELECT orig_id FROM snapshot_items WHERE id = ".$ret['item'];
		$query = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($query);
		$sql = "SELECT partno, price, upc, description, sku FROM form_items WHERE ID = ".$ret['orig_id'];
		$query = mysql_query($sql);
		checkdberror($sql);
		$ret = mysql_fetch_assoc($query);
		$return['price'] = $ret['price'];
		
		$return['upc'] = $ret['upc'];

		$return['description'] = $ret['description'];
		$return['sku'] = $ret['partno'];
		$sku_split = split(':',$ret['sku']);
		if(count($sku_split) == 2)
		$return['retailer_partno'] = $sku_split[1];
		if(count($sku_split) == 3)
		{
			// Target item, split
			$return['asin'] = $sku_split[0];
			$return['retailer_partno'] = $sku_split[1];
			$return['upc'] = $sku_split[2];
		}
		// blank retail_price
		$return['retail_price'] = 0.00;
		// get the orders ID & qty ordered for the item in question
		$sql = "SELECT ID, qty FROM orders WHERE po_id = '$itempo' AND item = '$thisitem'";
		$que = mysql_query($sql);
		checkDBError($sql);
		$ret = mysql_fetch_assoc($que);
		$return['qty_ordered'] = $ret['qty'];
		// see if the orders.ID is in msrp_applied; if so, retrieve and make the price
		$sql = "SELECT msrp FROM msrp_applied WHERE orders_id = '{$ret['ID']}'";
		$que = mysql_query($sql);
		checkDBError($sql);
		if(mysql_num_rows($que) > 0)
		{
			$ret = mysql_fetch_assoc($que);
			$return['retail_price'] = $ret['msrp'];
		}
		return $return;
	}
}
?>