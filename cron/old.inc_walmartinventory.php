<?php
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
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */

/**
 * Walmart Inventory Flat File
 *
 * @package    Operations
 * @subpackage Walmart.com
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */
class WalmartInventory
{
    /**
     * File Type
     * Max Length: 2
     *  Possible Values:
     *    FII - Item Inventory File
     *    FFC - Confirmation File
     *    FFE - Error File
     * @var string
     */
    public $mType = 'FII';
    
    /**
     * File ID
     * Length: 25-35 (999999.20020213.032246.784879)
     *  Unique ID for this file. See Naming convention in spec.
     * @var string
     */
    public $mFileId = '';
    
    /**
     * File Name Suffix
     * Length: 25-35 (999999_20020213_032246_784879)
     *  Unique ID for this file. See Naming convention in spec.
     * @var string
     */
    public $mFileName = '';

    /**
     * File Version
     *  Length 5
     * @var string
     */
    private $mFileVersion = '4.0.0';

    /**
     * Recipient ID
     *  Length 4-9
     * @var int
     */
    private $mRecipientID = 2677;
        
    /**
     * Recipient Name
     *  Length 1-30
     * @var string
     */
    private $mRecipientName = 'Walmart.com';
    
    /**
     * Sender ID
     *  Length 4-9
     * @var int
     */
    public $mSenderID = 45750;
    
    /**
     * Sender Name
     *  Length 1-30
     * @var string
     */
    public $mSenderName = 'Soflex Furniture';
    
    /**
     * Detail Objects
     * @var Array<InventoryLine>
     */
    private $mDetails = array();
    
    /**
     * File Contents
     * @var string
     */
    public $mFile = '';
    
    const LINEEND = "\n";
    
    public function GenFileId() {
        $timestamp = time();
        $parts = array();
        $parts[] = $this->mSenderID;
        $parts[] = gmdate('Ymd', $timestamp);
        $parts[] = gmdate('His', $timestamp);
        $parts[] = str_pad(rand(0,999999),6, STR_PAD_LEFT);
        $this->mFileID = implode('.',$parts);
        $this->mFileName = implode('_',$parts);
    }

    /**
     * Parse Inventory File
     * @param string $string The String to Parse
     */
    public function Import($string) {
        $string = explode("\n",$string);
        foreach ($string as $line) {
            $line = trim($line);
            $parts = explode(WalmartInventoryDetail::DELIM, $line);
            switch ($parts[0]) {
                case 'FH':
                    // Order Header
                    $this->ParseFileHeader($parts);
                    break;
                case 'II':
                    $x = new WalmartInventoryItem($this);
                    $x->Import($line);
                    $this->mDetails[] = $x;
                    unset($x);
                    break;
                case 'FC':
                    $x = new WalmartInventoryConfirm($this);
                    $x->Import($line);
                    $this->mDetails[] = $x;
                    unset($x);
                    break;
                case 'FT':
                    // Order Trailer/Finish
                    $this->ParseFileFooter($parts);
                    break 2; // Drop out of the foreach, we're completely done.
            }
        }
    }
    
        /**
     * Parse Inventory File
     * @param string $string The String to Parse
     */
    public function Export() {
        $str = '';
        $str .= $this->DisplayFileHeader() . self::LINEEND;
        foreach ($this->mDetails as $line) {
            $str .= $line->Export() . self::LINEEND;
        }
        $str .= $this->DisplayFileFooter() . self::LINEEND;
        $this->mFile = $str;
        return $str;
    }
    
    /**
     * Parse Header of File
     * @param Array<string> Header Parts
     */
    protected function ParseFileHeader($parts) {
        if (count($parts) != 8)
            throw new WalmartInventoryException("ParseFileHeader: Incorrect Number of Elements", $this->mFile);
        $this->mFileId = $parts[1];
        $this->mType = $parts[2];
        $this->mFileVersion = $parts[3];
        $this->mRecipientID = $parts[4];
        $this->mRecipientName = $parts[5];
        $this->mSenderID = $parts[6];
        $this->mSenderName = $parts[7];
    }
    
    /**
     * Display Header of File
     */
    protected function DisplayFileHeader() {
        $parts = array();
        $parts[] = 'FH';
        $parts[] = WalmartInventoryDetail::DisplayString($this->mFileId);
        $parts[] = WalmartInventoryDetail::DisplayString($this->mType);
        $parts[] = WalmartInventoryDetail::DisplayString($this->mFileVersion);
        $parts[] = WalmartInventoryDetail::DisplayInt($this->mRecipientID);
        $parts[] = WalmartInventoryDetail::DisplayString($this->mRecipientName);
        $parts[] = WalmartInventoryDetail::DisplayInt($this->mSenderID);
        $parts[] = WalmartInventoryDetail::DisplayString($this->mSenderName);
        return implode(WalmartInventoryDetail::DELIM, $parts);
    }
    
     /**
     * Parse Footer of File
     * @param Array<string> Header Parts
     */
    protected function ParseFileFooter($parts) {
        if (count($parts) != 3)
            throw new WalmartInventoryException("ParseFileFooter: Incorrect Number of Elements",$this->mFile);
        if ($parts[1] != $this->mFileId)
            throw new WalmartInventoryException("ParseFileFooter: File ID in Footer does not match File ID in header.",$this->mFile);
        if ($this->CountDetails() != $parts[2])
            throw new WalmartInventoryException("ParseFileFooter: Number of Deatails does not match parsed file.",$this->mFile);
    }
    
    /**
     * Display Footer of File
     */
    protected function DisplayFileFooter() {
        $parts = array();
        $parts[] = 'FT';
        $parts[] = WalmartInventoryDetail::DisplayString($this->mFileId);
        $parts[] = $this->CountDetails();
        return implode(WalmartInventoryDetail::DELIM, $parts);
    }
    
    protected function AddDetail(WalmartInventoryDetail $detail) {
        $this->mDetails[] = $detail;
    }
    
    protected function CountDetails() {
        $i = 0;
        foreach ($this->mDetails as $detail) {
            if ($detail->mType == 'II' && $detail->mAvailability == WalmartInventoryItem::DELETED)
                continue;
            ++$i;
        }
        return $i;
    }
}

/**
 * Walmart Inventory Detail Abstract
 *
 * @package    Operations
 * @subpackage Walmart.com
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */
abstract class WalmartInventoryDetail
{
    /**
     * File Delimiter
     */
    const DELIM = '|';
    
    /**
     * Detail Type
     *  Length is 2
     */
    public $mType;
    
    /**
     * Detail Line
     *  For Debugging
     */
    public $mLine = '';
    
    /**
     * Parent
     * @var WalmartInventory
     */
    public $mParent;
    
    public function __construct(WalmartInventory $parent) {
        $this->mParent = $parent;
    }
    
    public function Import($string) {
        $this->mLine = $string;
    }
    
    public function Export() {
    }
    
    static public function ParseInt($int) {
        if (!is_numeric($int))
            return '';
        if ($int == '') {
            return -1;
        }
        return $int;
    }
    
    static public function DisplayInt($int) {
        if (!is_numeric($int))
            return '';
        if ($int == '')
            return -1;
        return $int;
    }
    
    static public function ParseString($string) {
        return $string;
    }
    
    static public function DisplayString($string) {
        $str = '';
        foreach (str_split($string) as $char) {
            if ($char == '|')
                continue;
            $str .= $char;
        }
        return $str;
    }
}

/**
 * Walmart Inventory Detail Item Information
 *
 * @package    Operations
 * @subpackage Walmart.com
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */
class WalmartInventoryItem extends WalmartInventoryDetail {
    const ACTIVE = 'AC';
    const ALWAYSAVAILABLE = 'AA';
    const PREORDER = 'PO';
    const JUSTINTIME = 'JT';
    const BUILDTOORDER = 'BO';
    const SEASONAL = 'SE';
    const RUNOUT = 'RO';
    const NOTACTIVE = 'NA';
    const DELETED = 'DT';
    
    /**
     * Walmart item number
     *  Length 1-13
     * @var int
     */
    public $mItemNumber = -1;
    
    /**
     * Wal-Mart UPC (13 digits with no check digit)
     * @var int
     */
    public $mUPC = -1;
    
    /**
     * Supplier SKU
     *  Length 1-20
     * @var int
     */
    public $mSKU = -1;
    
    /**
     * Supplier SKU
     *  Length 1-20
     * @var str
     */
    public $mAvailability = DELETED;
    
    /**
     * On Hand Quantity
     *  Length 0 to 9
     * @var int
     */
    public $mQuantity = -1;
    
    /**
     * Minimum Days to Ship
     *  Length 0 to 2
     * @var int
     */
    public $mMinShipDays = -1;
    
    /**
     * Maximum Days to Ship
     *  Length 0 to 2
     * @var int
     */
    public $mMaxShipDays = -1;
    
    /**
     * Availability Start Date
     *  Date (0 or 8)
     * @var date
     */
    public $mStartDate = -1;
    
    /**
     * Availability End Date
     *  Date (0 or 8)
     * @var int
     */
    public $mEndDate = -1;
    
    /**
     * MSRP
     *  Decimal (8,2)
     * @var int
     */
    public $mMSRP = -1;
    
    /**
     * Item Retail
     *  Decimal (8,2)
     * @var int
     */
    public $mRetail = -1;
    
    /**
     * Item Cost
     *  Suppliers Cost to Walmart.com
     * @var int
     */
    public $mCost = -1;
    
    /**
     * Facility ID
     *  Facility ID provided by Vendor
     *  Length 20
     * @var String
     */
    public $mFacilityId = '';
    
    public function __construct($parent) {
        $this->mType = 'II';
    }
    
    public function Import($line) {
        parent::Import($line);
        // TODO: Parse File
        $parts = explode(WalmartInventoryDetail::DELIM,  $line);
        if (count($parts) != 14)
            throw new WalmartInventoryDetailException("Import: Incorrect Number of Parameters (Expected 14, Got " . count($parts) . ")", $line, $this->mParent->mFile);
        $this->mItemNumber = self::ParseInt($parts[1]);
        $this->mUPC = self::ParseInt($parts[2]);
        $this->mSKU = self::ParseString($parts[3]);
        $this->mAvailability = self::ParseString($parts[4]);
        $this->mQuantity = self::ParseInt($parts[5]);
        $this->mMinShipDays = self::ParseInt($parts[6]);
        $this->mMaxShipDays = self::ParseInt($parts[7]);
        $this->mStartDate = self::ParseInt($parts[8]);
        $this->mEndDate = self::ParseInt($parts[9]);
        $this->mMSRP = self::ParseInt($parts[10]);
        $this->mRetail = self::ParseInt($parts[11]);
        $this->mCost = self::ParseInt($parts[12]);
        $this->mFacilityId = self::ParseString($parts[13]);
    }
    
    public function Export() {
        $item = array();
        $item[] = self::DisplayString($this->mType);
        $item[] = self::DisplayInt($this->mItemNumber);
        $item[] = self::DisplayInt($this->mUPC);
        $item[] = self::DisplayString($this->mSKU);
        $item[] = self::DisplayString($this->mAvailability);
        $item[] = self::DisplayInt($this->mQuantity);
        $item[] = self::DisplayInt($this->mMinShipDays);
        $item[] = self::DisplayInt($this->mMaxShipDays);
        $item[] = self::DisplayInt($this->mStartDate);
        $item[] = self::DisplayInt($this->mEndDate);
        $item[] = self::DisplayInt($this->mMSRP);
        $item[] = self::DisplayInt($this->mRetail);
        $item[] = self::DisplayInt($this->mCost);
        $item[] = self::DisplayString($this->mFacilityId);
        $this->mLine = implode(WalmartInventoryDetail::DELIM, $item);
        return $this->mLine;
    }
}

/**
 * Walmart Inventory Detail Item Information
 *
 * @package    Operations
 * @subpackage Walmart.com
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */
class WalmartInventoryConfirm extends WalmartInventoryDetail {

    /**
     * File ID
     *  Length 25-35
     * @var String
     */
    public $mFileId = '';
    
    /**
     * File Type
     *  Length 3
     * @var String
     */
    public $mFileType = '';
    
    public function __construct($parent) {
        $this->mType = 'FC';
    }
    
    public function Import($line) {
        parent::Import($line);
        // TODO: Parse File
        $parts = explode(WalmartInventoryDetail::DELIM,  $line);
        if (count($parts) != 3)
            throw new WalmartInventoryDetailException("Import: Incorrect Number of Parameters (Expected 14, Got " . count($parts) . ")", $line, $this->mParent->mFile);
        $this->mFileId = self::ParseString($parts[1]);
        $this->mUPC = self::ParseInt($parts[2]);
    }
    
    public function Export() {
        $item = array();
        $item[] = self::DisplayString($this->mFileId);
        $item[] = self::DisplayString($this->mFileType);
        $this->mLine = implode(WalmartInventoryDetail::DELIM, $item);
        return $this->mLine;
    }
}

/**
 * Walmart Inventory Exception
 *
 * @package    Operations
 * @subpackage Walmart.com
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */
class WalmartInventoryException extends Exception {
    public $invFile;
    
    public function __construct($why, $file) {
        $this->invFile = $file;
        parent::__construct($why);
    }
}

/**
 * Walmart Inventory Detail Exception
 *
 * @package    Operations
 * @subpackage Walmart.com
 * @copyright  Copyright (c) 2004-2009 Power Marketting Direct, Inc. (http://www.pmdfurniture.com)
 */
class WalmartInventoryDetailException extends WalmartInventoryException {
    public $invLine;
    
    public function __construct($why, $line, $file) {
        $this->invLine = $line;
        parent::__construct($why, $file);
    }
}