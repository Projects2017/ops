<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * TESTING ONLY
 *
   CREATE TABLE IF NOT EXISTS `sl_edi` (
     `purchase_order_number` int(11) NOT NULL,
     `transaction_set_number` int(11) NOT NULL DEFAULT '0',
     `interchange_number` int(11) NOT NULL DEFAULT '0'
   ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
   CREATE TABLE IF NOT EXISTS `sl_edi_groups` (
      `ID` int(11) NOT NULL AUTO_INCREMENT,
      `group` int(11) NOT NULL,
      `file_id` int(11) NOT NULL,
      `status` int(11) NOT NULL COMMENT '1 = accepted; -1 = rejected',
      PRIMARY KEY (`ID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Sealy EDI Group Tracker Table' ;
   CREATE TABLE IF NOT EXISTS `sl_edi_transactions` (
      `ID` int(11) NOT NULL AUTO_INCREMENT,
      `transaction` int(11) NOT NULL,
      `group_id` int(11) NOT NULL,
      `status` int(11) NOT NULL COMMENT '1 = accepted; -1 = rejected',
      PRIMARY KEY (`ID`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Sealy EDI Transaction Tracker Table' ;
   INSERT INTO `sl_edi` (`purchase_order_number`, `transaction_set_number`, `interchange_number`) VALUES (31, 113, 127);
   DELETE FROM `edi_vendor` WHERE `vendor` = 'pmdtest';
   UPDATE `edi_vendor` SET edi_qualifier = 'ZZ', edi_id = '829014849T' WHERE `vendor` = 'pmd';
   UPDATE `edi_vendor` SET `testing` = '1', `path` = '../../doc/as2/sealy/' WHERE `vendor` = 'sealy';

 */

// Let's generate a Order EDI
require('bo_edi.php');

class EdiPOBuilder extends EdiBuilder
{
    private $mEdiVendorBackup;
	function __construct($vendor)
	{
                $this->mEdiVendorBackup = new EdiVendor();
                $this->mEdiVendor = $this->mEdiVendorBackup;
                if ($this->mEdiVendor->LoadFromName($vendor))
                $GLOBALS['EdiVendor'] = $this->mEdiVendor;
		parent::__construct();
	}

    /**
     * Make
     * Constructs Purchase Order Response EDI File
     * @param PurchaseOrderStruct PO
     * @return EdiPOBuilder
     */
	public function Make(PurchaseOrderStruct $po)
	{
                // Setup Vendor
                $this->mEdiVendor = $this->mEdiVendorBackup;
                $GLOBALS['EdiVendor'] = $this->mEdiVendorBackup;
                $this->mTransactionControlNumber = $po->mPO; // PMD PO#

                // Setup EDI Header
                parent::StartAll('PO');
                $return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;

                // ST: Transaction Set Header
                $seg = $this->newSegment('ST');
                $seg[1] = '850'; // Purchase Order
                $seg[2] = str_pad($this->GetTransControl(),4); // Must be at least 4 characters

                // BEG: Begginning Segment for Purchase Order
                $seg = $this->newSegment('BEG');
                $seg[1] = '00'; // Original Order
                $seg[2] = 'SA'; // SA = Stand-Alone Order, FL = Floor Sample Order
                $seg[3] = $po->mPO; // PO #
                $seg[4] = ''; // Unspecified
                $seg[5] = date('Ymd',$po->mDate); // Date of PO

                // REF: Reference Identification
                //   Technically optional, may have more than one.
                //$seg = $this->NewSegment('REF');
                //$seg[1] = 'IT'; // Type: Internal Customer Number
                //$seg[2] = $po->mDealerId; // Dealer Id (PMD's Internal Customer Number)

                // DTM: Date/Time Reference
                //   Can specify Cancel after, Delivery Requested, Requested Ship, etc here.
                //   Optional, May be used up to 10 times.

                // TODO: Make this date do something
                $seg = $this->NewSegment('DTM');
                $seg[1] = '010'; // 010 - Requested Ship
                $seg[2] = gmdate('Ymd');

                $seg = $this->NewSegment('TD4');
                $seg[1] = 'NC'; // Notify Consignee Before Delivery

                // =LOOP= N9: Message
                $this->createN9Loop('L1', 'Comments for carrier', $po->mCarrierComments);
                $this->createN9Loop('L1', 'Comments to follow', $po->mComments);

                // =LOOP= N1: Address Information
                // N1: Name
                $seg = $this->NewSegment('N1');
                $seg[1] = 'ST'; // Ship To Party
                $name = $po->mDealerAddress->mName;
                if (strlen($name) > 60)
                    $name = substr($name,0,60);
                $seg[2] = str_pad($name,1); // Name up to 60 chars
                $seg[3] = '92';
                $seg[4] = 'D'.$po->mDealerId.'W'.($po->mDealerAddress->mSecondLocation?'2':'1'); // D{DealerId}W{1,2} Dealer Warehouse unique identifier
                // N2: Additional Name Information
                //   Optional Additional Name Information
                if ($po->mDealerAddress->mAdditionalName) {
                    $seg = $this->NewSegment('N2');
                    $name = $po->mDealerAddress->mAdditionalName;
                    if (strlen($name) > 60)
                        $name = substr($name,0,60);
                    $seg[1] = $name;
                }
                // N3: Address Information
                //   Optional
                if ($po->mDealerAddress->mAddress) {
                    $seg = $this->NewSegment('N3');
                    $seg[1] = $po->mDealerAddress->mAddress;
                    if ($po->mDealerAddress->mAddress2) {
                        $seg[2] = $po->mDealerAddress->mAddress2;
                    }
                    // N4: Geographic Location
                    $seg = $this->NewSegment('N4');
                    $seg[1] = $po->mDealerAddress->mCity;
                    $seg[2] = $po->mDealerAddress->mState;
                    $seg[3] = $po->mDealerAddress->mPostalCode;
                    $seg[4] = 'USA';
                }

                // =LOOP= PO1: Line Item
                $line_id = 0;
                foreach($po->mLines as $line) {
                    $line_id += 1; // Starts at 1 and goes up
                    // PO1: Baseline Item Data
                    $seg = $this->NewSegment('PO1');
                    $seg[1] = $line_id;
                    $seg[2] = $line->mQty;
                    $seg[3] = $line->mUnit;
                    $seg[4] = $line->mUnitPrice;
                    $seg[5] = $line->mPriceBasis;
                    $seg[6] = $line->mSellerIdQualifier;
                    $seg[7] = $line->mSellerId;
                    // PID: Product/Item Description
                    $seg = $this->NewSegment('PID');
                    $seg[1] = 'F'; // Item Description Type (F = Freeform)
                    $seg[2] = '';
                    $seg[3] = '';
                    $seg[4] = '';
                    $seg[5] = $line->mDescription; // Description
                }
                // CTT: Transaction Totals
                $seg = $this->NewSegment('CTT');
                $seg[1] = count($po->mLines);
                $seg = $this->NewSegment('SE');
                $seg[1] = count($this->mSegments) - 1;
                $seg[2] = str_pad($this->GetTransControl(),4); // Must be at least 4 character

                // Finishing off Footers
                $this->mTransactionSegmentEnd = count($this->mSegments);
                $this->MakeTransactionSegments();
		$this->mEdiTypeId = '850PO';
		parent::EndAll();
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;
		$return .= $this->mInterchangeFooter;
		if($obj->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}

        function createN9Loop($code,$name,$arr) {
            // =LOOP= N9: Message
            // N9: Reference Identification Qualifier
            $seg = $this->NewSegment('N9');
            $seg[1] = 'L1'; // L1 = Letters or Notes, ZZ = Mutually Defined
            $seg[2] = '';
            $seg[3] = $name;
            $firstseg = true;
            foreach ($arr as $comment) {
                $lineadvance = 0;
                $comment = trim(strtr($comment, "\r","")); // Strip out extraneous \r's
                foreach (explode("\n", $comment) as $comm) {
                    ++$lineadvance;
                    // Strip out anything but visible characters.
                    $comm = preg_replace('/[^\x20-\x7E]/', '', $comm);
                    $comm = trim($comm);
                    if (!$comm) continue;
                    foreach (explode("\n",wordwrap($comm,200,"\n",true)) as $commen) {
                        $commen = trim($commen);
                        $seg = $this->NewSegment('MSG');
                        // TODO: Limit to 254 characters per line.
                        $seg[1] = $commen;
                        if ($firstseg) {
                            // Do nothing.. no other elements are used
                        } elseif($lineadvance > 1) {
                            $seg[2] = 'AA';
                            $seg[3] = $lineadvance;
                            $lineadvance = 0;
                        } elseif ($lineadvance == 1) {
                            $seg[2] = 'SS';
                            $lineadvance = 0;
                        } else {
                            $seg[2] = 'LC'; // Continue last line.
                        }
                        $firstseg = false;
                    }
                }
            }
        }
}

class PurchaseOrderStruct {
    /**
     * Purchase Order #
     * @var int
     */
    public $mPO = 0;

    /**
     * Comments for transit carrier of goods.
     * @var string[]
     */
    public $mCarrierComments = array();

    /**
     * Purchase Order Comments
     * @var string[]
     */
    public $mComments = array();

    /**
     * Date Purchase Order Submitted
     * @var date
     */
    public $mDate = 0;

    /**
     * Dealer ID #
     * @var int
     */
    public $mDealerId = 0;

    /**
     * Dealer Address Struct
     * @var AddressStruct
     */
    public $mDealerAddress;

    /**
     * Purchase Order Lines
     * @var PurchaseOrderLineStruct[]
     */
    public $mLines = array();

    public function Load($po) {
        if (!is_numeric($po)) {
            // If not numeric just return.
            return;
        }
        $this->mPO = $po;
        $po = $po - 1000;

        $sql = "SELECT *, UNIX_TIMESTAMP(`ordered`) AS `ordered_ts` FROM `order_forms` WHERE `ID` = '".$po."'";
        $query = mysql_query($sql);
        checkDBerror($sql);
        $porow = mysql_fetch_assoc($query);
        $this->mDate = (int) $porow['ordered_ts'];
        $this->mDealerId = $porow['user']; // Dealer ID

        if (is_null($porow['shipto'])) {
          $snapshot_id = $porow['snapshot_user'];
        } else {
          $snapshot_id = $porow['shipto'];
        }
        $sql = "SELECT * FROM `snapshot_users` WHERE `id` = '". $snapshot_id."'";

        $query = mysql_query($sql);
        checkDBerror($sql);
        $dealer = mysql_fetch_assoc($query);
        // Comment on EVERY order
        $this->mCarrierComments[] = 'Call '.$dealer['phone'].' two hours before arrival of delivery'; // 45 Character limit
        // Dynamic Dealer Submitted Comments
        $this->mComments[] = $porow['comments'];
        $addr = new AddressStruct();
        $addr->mName = $dealer['last_name'];
        $addr->mAdditionalName = $dealer['first_name'];
        $addr->mAddress = $dealer['address'];
        $addr->mCity = $dealer['city'];
        $addr->mState = $dealer['state'];
        $addr->mPostalCode = $dealer['zip'];
        if ($dealer['secondary'] == 'Y') {
            $addr->mSecondLocation = true;
        }
        $this->mDealerAddress = $addr;

        $sql = "SELECT * FROM orders WHERE `po_id` = '".$po."'";
        $query = mysql_query($sql);
        checkDBerror($sql);
        while ($row = mysql_fetch_assoc($query)) {
            $sql = "SELECT * FROM `snapshot_items` WHERE `id` = '".$row['item']."'";
            $queryItem = mysql_query($sql);
            $rowItem = mysql_fetch_assoc($queryItem);
            $sku = explode(":", $rowItem['sku']);
            // Validate parameters of SKU just so we don't error out
            if (!(is_array($sku) && count($sku) == 3)) {
                if (!is_array($sky)) $sky = array();
                if (!isset($sku[0])) $sku[0] = '';
                if (!isset($sku[1])) $sky[1] = '';
                if (!isset($sku[2])) $sky[2] = '';
            }

            if ($rowItem['box'] == "") {
                if (!$sku[1]) $sku[1] = $sku[0];
            }

            // Check for setqty amount
            if ($row['setqty'] >= 1) {
                if (!is_numeric($rowItem['setqty']))
                    $rowItem['setqty'] = 2;

                $row['mattqty'] += $row['setqty'];
                $row['qty'] += $row['setqty'] * ($rowItem['setqty'] - 1);

                /*
                $item = new PurchaseOrderLineStruct();
                $item->mDescription = $rowItem['description'].' Set';
                $item->mQty = $row['setqty'];
                $item->mSellerId = $sku[2]; // Seller SKU
                $this->mLines[] = $item;
                 *
                 */
            }

            // Check for box amount
            if ($row['qty'] >= 1) {
                $item = new PurchaseOrderLineStruct();
                $append = "";
                if ($rowItem['box'] != "") {
                    $append = " Box";
                }
                $item->mDescription = $rowItem['description'];
                $item->mQty = $row['qty'];
                if ($rowItem['box'] != "")
                    $rowItem['price'] = $rowItem['box'];
                if ($rowItem['box_cost'] != "")
                    $rowItem['cost'] = $rowItem['box_cost'];
                if (is_numeric($rowItem['cost'])) {
                    $item->mUnitPrice = $rowItem['cost'];
                    $item->mPriceBasis = 'EA';
                }
                $item->mSellerId = $sku[1]; // Seller SKU
                $this->mLines[] = $item;
            }
            // Check for matt amount
            if ($row['mattqty'] >= 1) {
                $item = new PurchaseOrderLineStruct();
                $item->mDescription = $rowItem['description']. ' Matt';
                $item->mQty = $row['mattqty'];
                if (is_numeric($rowItem['matt_cost'])) {
                    $item->mUnitPrice = $rowItem['matt_cost'];
                    $item->mPriceBasis = 'EA';
                }
                $item->mSellerId = $sku[0]; // Seller SKU
                $this->mLines[] = $item;
            }

        }
    }

    public function SendEDI($vendorName) {
        $po_edi = new EdiPOBuilder($vendorName);
        $output = $po_edi->Make($this)->GetOutput();

        $edi = new EdiData();
        $edi->LoadFromEdiObject($po_edi);
        //$edi->Archive();
        $edi->Send();
        //die($edi->mEdiVendor->mVendorPath.'archive/'.date('Ym').'/'.$edi->mFilename."\n");

        // TODO: Send it!
    }
}

class PurchaseOrderLineStruct {
    /**
     * Order Line Id
     * @var int
     */
    public $mId;

    /**
     * Freeform Description of Item
     * @var string
     */
    public $mDescription = '';

    /**
     * Quantity Ordered
     * @var int
     */
    public $mQty = 0;

    /**
     * Unit or Basis for Measurement Code
     * @var string
     */
    public $mUnit = 'EA';

    /**
     * Unit Price (required if PriceBasis provided)
     * @var string
     */
    public $mUnitPrice = '';

    /**
     * Basis for Unit Price Code
     * @var sting
     */
    public $mPriceBasis = '';

    /**
     * Seller Identifier for Item
     * @var string
     */
    public $mSellerId = '';

    /**
     * Seller Identifier Qualifier for Item
     *
     * Defaults to 'UP' for U.P.C. Consumer Pacakge Code (1-5-5-1)
     *
     * @var string
     */
    public $mSellerIdQualifier = 'VN';
}

class AddressStruct {
    /**
     * Address Name
     * @var string
     */
    public $mName = '';

    /**
     * Additional Name
     * @var string
     */
    public $mAdditionalName = '';

    /**
     * Is this dealers second warehouse?
     * @var boolean
     */
    public $mSecondLocation = false;

    /**
     * Dealer Address (line 1)
     * @var string
     */
    public $mAddress = '';

    /**
     * Dealer Address (line 2)
     * @var string
     */
    public $mAddress2 = '';

    /**
     * Dealer City
     * @var string
     */
    public $mCity = '';

    /**
     * Dealer State
     * @var string
     */
    public $mState = '';

    /**
     * Dealer Postal Code
     * @var string
     */
    public $mPostalCode = '';
}

/* TEST INFORMATION
$po = new PurchaseOrderStruct();
$po->mPO = '200234';
$po->mDate = strtotime('2009-12-18 10:29:00');
$po->mDealerId = 106; // Will's Dealer ID
// Comment on EVERY order
$po->mComments[] = 'Call 503-367-2759 two hours before arrival of delivery'; // 45 Character limit
// Dynamic Dealer Submitted Comments
$po->mComments[] = 'TEST ORDER DO NOT SHIP';
$addr = new AddressStruct();
$addr->mName = 'William Lightning (IGNORE)';
$addr->mAdditionalName = 'Web Staff';
$addr->mAddress = '5426 SE Boise St.';
$addr->mCity = 'Portland';
$addr->mState = 'OR';
$addr->mPostalCode = '97206';
$po->mDealerAddress = $addr;
$item = new PurchaseOrderLineStruct();
$item->mDescription = 'Twin Matt';
$item->mQty = 2;
$item->mSellerId = '50589530'; // Seller SKU
$po->mLines[] = $item;
$item = new PurchaseOrderLineStruct();
$item->mDescription = 'Twin Box';
$item->mQty = 2;
$item->mSellerId = '60400030'; // Seller SKU
$po->mLines[] = $item;
$item = new PurchaseOrderLineStruct();
$item->mDescription = 'Queen Matt';
$item->mQty = 1;
$item->mSellerId = '50589550'; // Seller SKU
$po->mLines[] = $item;
$item = new PurchaseOrderLineStruct();
$item->mQty = 1;
$item->mDescription = 'Queen Box';
$item->mSellerId = '60400050'; // Seller SKU
$po->mLines[] = $item;
$item = new PurchaseOrderLineStruct();
$item->mDescription = 'Calif King Matt';
$item->mQty = 1;
$item->mSellerId = '50589561'; // Seller SKU
$po->mLines[] = $item;
$item = new PurchaseOrderLineStruct();
$item->mDescription = 'Calif King Box (each)';
$item->mQty = 2;
$item->mSellerId = '60400061'; // Seller SKU
$po->mLines[] = $item;


$po_edi = new EdiPOBuilder();
$output = $po_edi->Make($po)->GetOutput();

$edi = new EdiData();
$edi->LoadFromEdiObject($po_edi);
$edi->Archive();
echo $edi->mEdiVendor->mVendorPath.'archive/'.date('Ym').'/'.$edi->mFilename."\n";

$output = implode("\n",explode(EdiSeparators::NEWSEGMENT, $output));
echo "\n";
echo $output;
echo "\n";
*/
?>
