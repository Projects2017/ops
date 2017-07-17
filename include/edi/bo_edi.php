<?php
// bo_edi.php
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


/**
 * EDI Business Logic Library
 *
 * @package    Operations
 * @subpackage EDI
 * @copyright  Copyright (c) 2004-2009 Power Marketing Direct, Inc. (http://www.pmdfurniture.com)
 */
//require_once('./../lib_objects/db.php'); // for getting db; hard coded for now
require_once('edi.php');
require_once('do_edi.php');

class EdiAG extends EdiObject
{
	public function Load($data)
	{
		$this->mEdiType = 'AG';
		$this->mParser = new EdiAGParser($data);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactionData = array();
		do
		{
			$this->mTransactionData[] = $this->mParser->GetTransactionData();
		}
		while($this->mParser->Peek() != 'GE' && !$this->mParser->mEndOfFile);
		$en = &end($this->mGroups);
		$env = is_array($en) ? $en[0] : $en;
		$this->mParser->EndGroupEnvelope($env);
		if(!$env->ValidateControl || count($this->mTransactionData) != $env->mTransactions || $this->mParser->mReject == true)
		{
			$this->mReject = true;
		}
	}
	
	public function Build($edi = null)
	{
		$output = null;
		if(!is_null($edi))
		{
			$this->mOutEdi = new EdiAGBuilder();
			$output = $this->mOutEdi->Make($edi);
		}
		if($edi->mReject) $output['reject'] = true;
		$this->mOutputString = $output;
		return $output;
	}
}


// commenting out because it's not finished
/* TODO FINISH
// AG Builder (AG = Error Notification)
class EdiAGBuilder extends EdiBuilder
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function Make(&$obj = null, $acktype = null, $trans = null, $cancel_po = null)
	{
		parent::StartAll('AG');
		if(is_null($obj)) die('edi must not be null for AG');
		$this->mParentEdi = $obj;
		$this->NewTransControl();
		$transactionout = 'ST'.$this->NewElements().'824'.
		$this->NewElements().$this->GetTransControl().$this->NewSegment();
		$transactionout .= 'BGN'.$this->NewElements().'11'.
		$this->NewElements().$this->mParentEdi;
		// TODO Finish this code		
				
		parent::EndAll();
		$this->mEdiTypeId = '824AG';
		$return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;		
		$return .= $this->mInterchangeFooter;
		if(isset($this->mParentEdi) && $this->mParentEdi->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}
}
*/


class EdiAGParser extends EdiParser
{
	private $mTransactionNumber = 0;
	function __construct($data = null, $separators = null)
	{
		if(!is_null($data))
		{
			parent::__construct($data, $separators);
			if(!is_array($data))
			$this->mTransactionRawData = array();
			$searchfrom = 0;
			do
			{
				$transpos = strpos($this->mRawData, $this->mSegmentSeparator.'ST*824', $searchfrom);
				if(!$transpos) break;
				$partial = strpos($this->mRawData, $this->mSegmentSeparator.'SE', $transpos);
				$endspot = strpos($this->mRawData, $this->mSegmentSeparator, $partial);
				$this->mTransactionRawData[] = substr($this->mRawData, $transpos + 1, $endspot - $transpos);
				$searchfrom = $endspot;
			}
			while($searchfrom < strlen($this->mRawData));
		}
		else
		{
			parent::__construct();
			$this->mTransactionRawData = array();
		}
	}
	
	public function GetTransactionData()
	{
		// should start w/ the 'AG' code ('Error')
		if($this->GetNextData() != 'ST' || $this->GetNextData() != '824')
		{
			$return['reject'] = true;
			$return['reject_reason'] = 'AGParser: not an AG';
			return;
		}
		$return = array();
		$return['transaction_type_code'] = '824';
		$return['transaction_control_number'] = $this->GetNextData();
		$this->NextSegment();
		if($this->GetNextData() != 'BGN' || $this->GetNextData() != '11')
		{
			$return['reject'] = true;
			$return['reject_reason'] = 'BGN: invalid transaction data in AG';
			return;
		}
		$return['interchange_number'] = $this->GetNextData();
		$return['process_date'] = $this->GetNextData();
		$return['process_time'] = $this->GetNextData();
		if($this->GetNextData() != 'GM')
		{
			$return['reject'] = true;
			$return['reject_reason'] = 'BGN: missing GMT mark in AG';
			return;
		}
		$this->NextSegment();
		while($this->Peek() == 'OTI')
		{
			$this->Skip();
			if($this->GetNextData() != 'BP' || $this->GetNextData() != '1Q')
			{
				$return['reject'] = true;
				$return['reject_reason'] = 'OTI: segment data missing in AG';
				return;
			}
			$return['error_code'][] = $this->GetNextData();
			$this->Skip(4);
			$return['error_group_number'][] = $this->GetNextData();
			$this->NextSegment();
		}
		while($this->Peek() == 'TED')
		{
			$this->Skip();
			if($this->GetNextData() != '024')
			{
				$return['reject'] = true;
				$return['reject_reason'] = 'TED: data segment missing in AG';
				return;
			}
			$return['error_message'][] = $this->GetNextData();
			$this->NextSegment();
			while($this->Peek() == 'NTE')
			{
				$this->Skip();
				if($this->GetNextData() != 'ERN')
				{
					$return['reject'] = true;
					$return['reject_reason'] = 'NTE: segment data missing in AG';
				}
				$return['error_notes'][] = $this->GetNextData();
				$this->NextSegment();
			}
		}
		while($this->Peek() == 'SE')
		{
			$this->Skip();
			$return['total_number_segments'] = $this->GetNextData();
			$return['transaction_control_number2'] = $this->GetNextData();
			$this->NextSegment();
		}
		$return['total_transaction_segments'] = substr_count($this->mTransactionRawData[$this->mTransactionNumber], $this->mSegmentSeparator);
		$this->mTransactionNumber++;
		if($return['total_number_segments'] != $return['total_transaction_segments'])
		{
			$return['reject'] = true;
			$this->mReject = true;
		}
		return $return;
	}
}

class EdiCA extends EdiObject
{
	protected $mSourceEdi;
	
	function __construct()
	{
		parent::__construct();
	}
	
	public function Load(&$data)
	{
		$this->mParser = new EdiCAParser($data);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactionData = array();
		$this->mTransactionData[]  = $this->mParser->GetTransactionData();
	}

	public function Make(&$edi)
	{
		$this->mSourceEdi = $edi;
		$output = array();
		// doc type = cancel
		foreach($edi->mEdiPOs as $ponum => $thispo)
		{
			$this->mOutputEdi = new EdiCABuilder($edi->mSender);
			$output = $this->mOutputEdi->Make($edi, $ponum);
			$this->mOutputString = $output;
			$this->Send();
		}
	}
	
	
}

// CA Builder (CA = Cancel Acknowledgement)
class EdiCABuilder extends EdiBuilder
{
	private $mDoEdiCABuilder;
	
	function __construct()
	{
		parent::__construct();
		$this->mDoEdiCABuilder = DoEdiCABuilder::getInstance();
	}
	
	public function Make(&$obj = null, $cancel_po = null)
	{
		parent::StartAll('CA');
		if(is_null($obj) || is_null($cancel_po)) die('edi & cancel_po must not be null for CA');
		$this->NewTransControl();
		$newseg = $this->NewSegment('ST');
		$newseg[1] = '865';
		$newseg[2] = $this->GetTransControl();
		$newseg = $this->NewSegment('BCA');
		$newseg[1] = '01';
		$newseg[2] = 'AT';
		$newseg[3] = $cancel_po;
		$newseg[5] = $obj->mEdiPOs[$cancel_po]->mRevision;
		$newseg[6] = gmdate('Ymd', strtotime($obj->mEdiPOs[$cancel_po]->mOriginalPODate));
		$newseg = $this->NewSegment('REF');
		$newseg[1] = 'VR';
		$newseg[2] = $obj->mEdiPOs[$cancel_po]->mVendorId;
		if($this->mEdiPOs[$cancel_po]->mGuestOrderId != '')
		{
			$newseg = $this->NewSegment('REF');
			$newseg[1] = 'OQ';
			$newseg[2] = $this->mEdiPOs[$cancel_po]->mGuestOrderId;
		}
		$newseg = $this->NewSegment('REF');
		$newseg[1] = 'D7';
		$newseg[2] = 'RETAIL';
		$newseg = $this->NewSegment('DTM');
		$newseg[1] = '007';
		$newseg[2] = gmdate('Ymd');
		$totalweight = 0;
		$totalcubicft = 0;
		foreach($obj->mEdiPOs[$cancel_po]->mLineItems as $thisitem)
		//for($i=0; $i<count($acktype['type']); $i++)
		{
			//$thisitem = $obj->mEdiPOs[$cancel_po]->mLineItems[$i];
			$newseg = $this->NewSegment('POC');
			$newseg[1] = $thisitem->mLineNumber;
			$newseg[2] = 'DI';
			$newseg[3] = $thisitem->mQtyCanceled;
			$newseg[4] = $thisitem->mQtyRemaining;
			$newseg[5] = 'EA';
			$newseg[6] = $thisitem->mPrice;
			if($thisitem->mSKU != '')
			{
				$newseg[8] = 'SK';
				$newseg[9] = $thisitem->mSKU;
			}
			if($thisitem->mRetailerPartNumber != '')
			{
				$newseg[10] = 'IN';
				$newseg[11] = $thisitem->mRetailerPartNumber;
			}
			if($thisitem->mUPC != '')
			{
				$newseg[12] = 'UP';
				$newseg[13] = $thisitem->mUPC;
			}
			$newseg[14] = 'PR';
			$newseg[15] = $thisitem->mProcessNumber;
			$seg = $this->NewSegment('ACK');
			$seg[1] = 'ID';
			$seg[2] = $thisitem->mQtyCanceled;
			$seg[3] = 'EA';
			if($thisitem->mSKU != '')
			{
				$seg[7] = 'SK';
				$seg[8] = $thisitem->mSKU;
			}
			if($thisitem->mRetailerPartNumber != '')
			{
				$seg[9] = 'IN';
				$seg[10] = $thisitem->mRetailerPartNumber;
			}
			if($thisitem->mUPC != '')
			{
				$seg[11] = 'UP';
				$seg[12] = $thisitem->mUPC;
			}
			// get the item's weight & cubic_ft
			$data = $this->mDoEdiCABuilder->GetItemData($thisitem);
			$totalweight += $data['weight'];
			$totalcubicft += $data['cubic_ft'];
		}
		$seg = $this->NewSegment('CTT');
		$seg[1] = count($obj->mEdiPOs[$cancel_po]->mLineItems);
		$seg = $this->NewSegment('SE');
		$seg[1] = count($this->mSegments) - $this->mTransactionSegmentStart;
		$seg[2] = $this->GetTransControl();
		$this->mTransactionSegmentEnd = count($this->mSegments);
		$this->MakeTransactionSegments();
		$this->mEdiTypeId = '865CA';
		parent::EndAll();
		$return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;		
		$return .= $this->mInterchangeFooter;
		if($obj->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}
}


class EdiCancel
{
	private $mDoEdiCancel;
	public $mFilename;
	public $mEdiPOs;
	public $mOrigEdis;
	public $mArchiveFolder;
	public $mPMDPOs;

	public $mPOs;
	public $mPOLineNumbers;
	public $mEdiVendor;
	
	function __construct()
	{
		global $EdiVendor;
		$this->mEdiPOs = array();
		$this->mPMDPOs = array();
		$this->mOrigEdis = array();
		$this->mDoEdiCancel = DoEdiCancel::getInstance();
		$this->mEdiVendor = &$EdiVendor;
	}
	
	public function Load(&$edi)
	{
		foreach($edi->mEdiObject->mTransactions as $trans)
		{
			foreach($trans->mItems as $thisitem)
			{
				if(!isset($this->mEdiPOs[$trans->mOriginalPONumber])) $this->mEdiPOs[$trans->mOriginalPONumber] = new EdiCancelPO($trans->mOriginalPONumber);
				$ed = new Edi();

                $filename = $this->mDoEdiCancel->GetFilenameFromOriginalPONumber($trans->mOriginalPONumber);
				$ed->LoadFromFile($this->mDoEdiCancel->GetFilenameFromOriginalPONumber($trans->mOriginalPONumber));
				foreach($ed->mEdiObject->mTransactions as $tra)
				{
					if($tra->mPONumber == $trans->mOriginalPONumber) $this->mEdiPOs[$trans->mOriginalPONumber]->mOriginalPODate = $tra->mCreateDate;
				}
				$thisline = new EdiCancelLine();
				$thisline->mLineNumber = $thisitem->mOriginalPOLineNumber;
				if(isset($thisitem->mQuantity) || isset($thisitem->mQuantityRemaining))
				{
					$thisline->mQtyCanceled = $thisitem->mQuantity;
					$thisline->mQtyRemaining = $thisitem->mQuantityRemaining;
					$thisline->mPrice = $thisitem->mPrice;					
					$thisline->mSKU = $thisitem->mSKU;
					$thisline->mUPC = $thisitem->mUPC;
					$thisline->mRetailerPartNumber = $thisitem->mRetailerPartNumber;
					$thisline->mProcessNumber = $thisitem->mProcessNumber;
				}
				
				$this->mEdiPOs[$trans->mOriginalPONumber]->mLineItems[] = $thisline;
				$this->mEdiPOs[$trans->mOriginalPONumber]->mRevision = $trans->mOriginalPORevision;
				$this->mEdiPOs[$trans->mOriginalPONumber]->mGuestOrderId = $trans->mAlternatePONumber;
				$this->mEdiPOs[$trans->mOriginalPONumber]->mVendorId = $trans->mVendorId;
				$this->mEdiPOs[$trans->mOriginalPONumber]->mVendorName = $trans->mVendorName;
				if(!isset($this->mPMDPOs[$trans->mOriginalPONumber])) $this->mPMDPOs[$trans->mOriginalPONumber] = new EdiCancelPO($this->mDoEdiCancel->GetPONumber($trans->mOriginalPONumber));
				$filename = $this->mDoEdiCancel->GetOrigPOFilename($trans->mOriginalPONumber);
				$this->mEdiPOs[$trans->mOriginalPONumber]->mFilename = $filename;
				$this->mPMDPOs[$trans->mOriginalPONumber]->mFilename = $filename;
				$archive_folder = $this->mDoEdiCancel->GetArchiveFolder($filename);
				$this->mEdiPOs[$trans->mOriginalPONumber]->mArchiveFolder = $archive_folder;
				$this->mPMDPOs[$trans->mOriginalPONumber]->mArchiveFolder = $archive_folder;
				$this->mPMDPOs[$trans->mOriginalPONumber]->mLineItems[] = $thisline;
			}
		}
	}

	public function Process()
	{
		// need to load the order
		$edi = Edi::getInstance();
		foreach($this->mEdiPOs as $edicancelpo)
		{
			if($edicancelpo->mFilename == '') continue; // means no file was found
			$edi->LoadFromFile($edicancelpo->mFilename);
			$poids[] = $edi->mPOId;
			if(!in_array($edi->mPOId, $this->mOrigEdis)) $this->mOrigEdis[$edi->mPOId] = $edi;
		}
		$i = 0;
		foreach($this->mPMDPOs as $vendorpo => $cancelpo)
		{
			if($cancelpo->mFilename == '') continue; // means no file was found
			$cancel_id = $this->mDoEdiCancel->Submit($vendorpo, $cancelpo, $this->mOrigEdis[$poids[$i]]);
			$this->mDoEdiCancel->SetProcessComment($cancelpo->mPONumber, $cancel_id);
			$this->mDoEdiCancel->RemoveItemsFromShipping($cancelpo->mPONumber, $cancelpo->mLineItems, $cancel_id);
			$i++;
		}
		$acktype = array();
		foreach($this->mEdiPOs as $ponum => $edipo)
		{
			if($edipo->mFilename == '') continue;
			for($j = 0; $j < count($edipo->mLineNumbers); $j++)
			{
				$acktype[$ponum][$edipo->mLineNumbers[$j]] = 'ID';
			}
		}
		$output = array();
		// grab the default Cancel response type from the vendor object
		$ediclass = "Edi".$this->mEdiVendor->mPOCancelResponseID;
		$processed = new $ediclass;
		$processed->Make($this, $acktype);
	}
}

class EdiCancelLine
{
	public $mLineNumber;
	public $mPrice;
	public $mSKU;
	public $mUPC;
	public $mWeight;
	public $mCubicFeet;
	public $mRetailerPartNumber;
	public $mProcessNumber;
	public $mQtyCanceled;
	public $mQtyRemaining;
	
	function __construct()
	{
		$this->mQtyCanceled = -1; // default to -1, meaning entire order canceled
		$this->mQtyRemaining = -1; // default to -1, meaning none of the order remains
	}
}

class EdiCancelPO
{
	private $mDoEdiCancelPO;
	public $mPONumber;
	public $mGuestOrderId;
	public $mOriginalPODate;
	public $mVendorId;
	public $mVendorName;
	public $mRevision;
	public $mLineItems;
	public $mFilename;
	public $mArchiveFolder;
	
	function __construct($po = null)
	{
		if(!is_null($po)) $this->mPONumber = $po;
		$this->mLineItems = array();
	}
}



class EdiFA extends EdiObject
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function Load($data, $separators = null)
	{
		$this->mEdiType = 'FA';
		$this->mParser = new EdiFAParser($data, $separators);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactionData = array();
		do
		{
			$this->mTransactionData[] = $this->mParser->GetTransactionData();
		}
		while($this->mParser->Peek() != 'GE' && !$this->mParser->mEndOfFile);
		$en = &end($this->mGroups);
		$env = is_array($en) ? $en[0] : $en;
		$this->mParser->EndGroupEnvelope($env);
		if(!$env->ValidateControl() || count($this->mTransactionData) != $env->mTransactions || $this->mParser->mReject == true)
		{
			$this->mReject = true;
		}
		return $this;
	}
	
	public function Build(&$data = null)
	{
		$output = null;
		if(!is_null($data))
		{
			$this->mOutputEdi = new EdiFABuilder($this->mEdiVendor);
			$output = $this->mOutputEdi->Make($data);
			if($output == false) return false;
		}
		return true;
	}
}


class EdiFABuilder extends EdiBuilder
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function Make(&$obj = null)
	{
		$check = parent::StartAll('FA');
		if($check === false) return false; // do not build FA for those who don't want it
		if(is_null($obj)) die('edi must not be null for FA');
		$return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;
		foreach($obj->mEdiObject->mGroups as $group)
		{
			$this->mTransactionSegmentStart = count($this->mSegments);
			$this->NewTransControl();
			$seg = $this->NewSegment('ST');
			$seg[1] = '997';
			$seg[2] = $this->GetTransControl();
			$seg = $this->NewSegment('AK1');
			$seg[1] = $group->mType;
			$seg[2] = $group->mControlHeader;
			$setsrejected = 0;
			foreach($obj->mEdiObject->mTransactions as $trans)
			{
				if($trans->mReject) $setsrejected++;
				$seg = $this->NewSegment('AK2');
				$seg[1] = $trans->mTypeCode;
				$seg[2] = $trans->mTransactionControlHeader;
				$seg = $this->NewSegment('AK5');
				$seg[1] = $trans->mReject ? 'W' : 'A';
			}
			$seg = $this->NewSegment('AK9');
			$seg[1] = $setsrejected > 0 ? 'R' : 'A';
			$seg[2] = count($obj->mEdiObject->mTransactions);
			$seg[3] = count($obj->mEdiObject->mTransactions);
			$seg[4] = count($obj->mEdiObject->mTransactions) - $setsrejected;
			if($obj->mEdiObject->mReject == true) $this->mStatus = 0;
			$seg = $this->NewSegment('SE');
			$seg[1] = count($this->mSegments) - $this->mTransactionSegmentStart;
			$seg[2] = $this->GetTransControl();
			$this->mTransactionSegmentEnd = count($this->mSegments);
			$this->MakeTransactionSegments();
		}
		$this->mEdiTypeId = '997FA';
		parent::EndAll();
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;		
		$return .= $this->mInterchangeFooter;
		if($obj->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}
}


class EdiFAParser extends EdiParser
{
	private $mTransactionNumber = 0;
	function __construct($data = null, $separators = null)
	{
		if(!is_null($data)) // deconstructing a sent one
		{
			parent::__construct($data, $separators);
			if(!is_array($data))
			$this->mTransactionRawData = array();
			$searchfrom = 0;
			do
			{
				$transpos = strpos($this->mRawData, $this->mSeparators->mSegment.'ST'.$this->mSeparators->mElement.'997', $searchfrom);
				if(!$transpos) break;
				$partial = strpos($this->mRawData, $this->mSeparators->mSegment.'SE', $transpos);
				$endspot = strpos($this->mRawData, $this->mSeparators->mSegment, $partial + 1);
				$this->mTransactionRawData[] = substr($this->mRawData, $transpos + 1, $endspot - $transpos);
				$searchfrom = $endspot;
			}
			while($searchfrom < strlen($this->mRawData));
		}
		else // making a new one
		{
			parent::__construct();
			$this->mTransactionRawData = array();
		}
	}
	
	public function GetTransactionData()
	{
		// should start w/ the 'FA' code ('997')
		if($this->GetNextData() != 'ST' || $this->GetNextData() != '997')
		{
			$return['reject'] = true;
			$return['reject_reason'] = 'FAParser: not an FA';
			return;
		}
		$return = array();
		$return['transaction_type_code'] = '997';
		$return['transaction_control_number'] = $this->GetNextData();
		$this->NextSegment();
		if($this->Peek() != 'AK1')
		{
			$return['reject'] = true;
			$return['reject_reason'] = 'AK1: segment missing in FA';
			return;
		}
		$this->Skip();
		$return['function_code'] = $this->GetNextData();
		$return['group_control_number'] = $this->GetNextData();
		$this->NextSegment();
		while($this->Peek() == 'AK2')
		{
			$this->Skip();
			$return['transaction_set_code'][] = $this->GetNextData(); // should match for the function code
			$return['transaction_set_control_number'][] = $this->GetNextData();
			$this->NextSegment();
			if($this->GetNextData() != 'AK5')
			{
				$return['reject'] = true;
				$return['reject_reason'] = 'AK5: segment missing in FA';
				return;
			}
			$return['transaction_ack_code'][] = $this->GetNextData();
			$this->NextSegment();
		}
		if($this->GetNextData() != 'AK9')
		{
			$return['reject'] = true;
			$return['reject_reason'] = 'AK9: segment missing in FA';
			return;
		}
		$return['group_ack_code'] = $this->GetNextData();
		$return['total_transaction_sets'] = $this->GetNextData();
		$return['received_transaction_sets'] = $this->GetNextData();
		$return['accepted_transaction_sets'] = $this->GetNextData();
		$this->NextSegment();
		while($this->Peek() == 'SE')
		{
			$this->Skip();
			$return['total_number_segments'] = $this->GetNextData();
			$return['transaction_control_number2'] = $this->GetNextData();
			$this->NextSegment();
		}
		$return['total_transaction_segments'] = substr_count($this->mTransactionRawData[$this->mTransactionNumber], $this->mSeparators->mSegment);
		$this->mTransactionNumber++;
		if($return['total_number_segments'] != $return['total_transaction_segments'])
		{
			$return['reject'] = true;
			$this->mReject = true;
		}
		return $return;
	}
}


class EdiIB extends EdiObject
{
	public $mItems;
	private $mDoEdiInventory;

	function __construct()
	{
		$this->mDoEdiInventory = DoEdiInventory::getInstance();
	}
	
	public function LoadItems()
	{
		// TODO flesh out eventually after orders are done
		$allitems = $this->mDoEdiInventory->GetItems();
		foreach($allitems as $thisitem)
		{
			$newitem = new EdiInventoryItem();
			$newitem->mId = $thisitem['ID'];
			$newitem->mSKU = $thisitem['partno'];
			$newitem->mDescription = $thisitem['description'];
			$newitem->mPrice = $thisitem['price'];
			$newitem->mStockCode = $thisitem['stockcode'];
			$newitem->mStockDate = $thisitem['stock_date'];
			$newitem->mStockDay = $thisitem['stock_day'];
			$newitem->mWeight = $thisitem['weight'];
			$newitem->mAllocated = ($thisitem['alloc'] == -1 ? '0' : $thisitem['alloc']);
			$newitem->mAvailable = ($thisitem['avail'] == -1 ? '100' : $thisitem['avail']);
			// grab the retailer item ID from the sku field
            echo "here's the sku field...".print_r($thisitem['sku'], true)."\n";
			$data = split(':', $thisitem['sku']);
            echo "heres the data after splitting...".print_r($data, true)."\n";
            // $data[0] = ASIN; $data[1] = RetailerItemId; $data[2] = UPC ///// Target
            // $data[0] = RetailerItemId; $data[1] = UPC
            $newitem->mRetailerItemID = count($data) > 2 ? $data[1] : (count($data) > 1 ? $data[0] : '');
			$newitem->mUPC = count($data) > 2 ? $data[2] : (count($data) > 1 ? $data[1] : $thisitem['upc']);
            $newitem->mASIN = count($data) > 2 ? $data[0] : '';
			$this->mItems[] = $newitem;
			unset($newitem);
		}
	}
	
	public function Process()
	{
		// construct 846 (IB) Inventory EDI file
		// first we load up this->mItems with EdiInventoryItem's
		$this->LoadItems();
		$this->mOutputEdi = new EdiIBBuilder();
		$this->mOutputEdi->Make($this);
		$this->Send(); // should send it off
	}
}



// IB Builder (IB = Inventory Inquiry/Advice)
class EdiIBBuilder extends EdiBuilder
{
	function __construct()
	{
		parent::__construct();
	}

    /**
     * Make
     * Creates Inventory EDI file
     * @param EdiIB obj
     * @return EdiIBBuilder this
     */
	public function Make(&$obj = null)
	{
		parent::StartAll('IB');
		// TODO finish this builder
		if(is_null($obj)) die('edi must not be null for IB');
		$return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;
		$this->mTransactionSegmentStart = count($this->mSegments);
		$this->NewTransControl();
		$seg = $this->NewSegment('ST');
		$seg[1] = '846';
		$seg[2] = $this->GetTransControl();
		$seg = $this->NewSegment('BIA');
		$seg[1] = '00';
		$seg[2] = 'DD';
		$seg[3] = $this->GetTransControl();
		$seg[4] = gmdate('Ymd');
		$seg = $this->NewSegment('REF');
		$seg[1] = 'VR';
		$seg[2] = $this->mEdiVendor->mVendorId;
		$seg = $this->NewSegment('N1');
		$seg[1] = 'DU';
		$seg[2] = 'Soflex Leather';
		$itemcount = 1;
		foreach($obj->mItems as $thisitem)
		{
			$seg = $this->NewSegment('LIN');
			$seg[1] = $itemcount;
			if($thisitem->mSKU != '')
			{
				$seg[2] = 'SK';
				$seg[3] = $thisitem->mSKU;
			}
			if($thisitem->mRetailerItemID != '')
			{
				$seg[4] = 'IN';
				$seg[5] = $thisitem->mRetailerItemID;
			}
			if($thisitem->mUPC != '')
			{
				$seg[6] = 'UP';
				$seg[7] = $thisitem->mUPC;
			}

            if($thisitem->mAvailable < 0)
            {
                $seg = $this->NewSegment('DTM');
                $seg[1] = '128';
                $seg[2] = date('Ymd');
            }
			$seg = $this->NewSegment('SDQ');
			$seg[1] = 'EA';
			$seg[2] = '54';
			$seg[3] = 'AVAIL';
			$seg[4] = $thisitem->mAvailable;
			$seg[23] = $this->mEdiVendor->mWarehouseCode;
			$itemcount++;
		}
        $seg = $this->NewSegment('CTT');
        $seg[1] = $itemcount;
		$seg = $this->NewSegment('SE');
		$seg[1] = count($this->mSegments) - $this->mTransactionSegmentStart;
		$seg[2] = $this->GetTransControl();
		$this->mTransactionSegmentEnd = count($this->mSegments);
		$this->MakeTransactionSegments();
		$this->mEdiTypeId = '846IB';
		parent::EndAll();
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;		
		$return .= $this->mInterchangeFooter;
		if($obj->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}
}


class EdiOrder
{
	private $mDoEdiOrder;
	const FORMNUMBER = '1';
	public $mBillTo;
	public $mShipTo;
	public $mInternalPOId;
	public $mPackingSlipType;
	public static $mItems;
	public $mSubtotal;
	public $mShippingCost;
	public $mTaxes;
	public $mAllowance;
	public $mTotal;
	public $mRetailTotal;
	public $mRetailPONumber;
	public $mAltPONumber;
	public $mRetailPODate;
	public $mStoreNumber;
	public $mShipCode;
	public $mShipByDate;
	public $mShipping; // can be ShippingEdi object
	public $mCustomerOrderNumber;
	public $mAlternateOrderNumber;
	public $mMarketingMsg;
	public $mReturnsMsg;
	public $mDeliveryMsg;
	public $mShipRequirements;
	public $mEdiVendor;
	
	function __construct()
	{
		global $EdiVendor;
		$this->mDoEdiOrder = new DoEdiOrder();
		$this->mItems = array();
		$this->mTotal = 0.00;
		$this->mEdiVendor = &$EdiVendor;
	}
	
	public function Submit()
	{
		return $this->mDoEdiOrder->Submit($this);
	}
	
	public function LoadFromPO($poedi)
	{
		$pos = strstr($poedi->mPOId, ';') != false ? explode(';', $poedi->mPOId) : array(0 => $poedi->mPOId);
		foreach($pos as $thispo)
		{
			// $thispo is the PMD PO
			// we need to get the associated retailer PO
			$this->mInternalPOId = $thispo;
			// find this order number in any cancels that have taken place
			
			$retailerpo = $this->mDoEdiOrder->GetRetailerPO($thispo);
			if($retailerpo === false) return false;
			foreach($poedi->mEdiObject->mTransactions as $trans)
			{
				$retailerpo = trim($retailerpo);
				if($trans->mPONumber == $retailerpo)
				$this->Load($trans);
			}
			// returns item IDs of the canceled item
			$cancels = $this->mDoEdiOrder->GetCancellations($this->mInternalPOId);
			if(count($cancels) > 0)
			{
				foreach($cancels as $thiscancel)
				{
					for($j = 0; $j < count($this->mItems); $j++)
					{
						if($this->mItems[$j]->mItemId == $thiscancel)
						{
							$this->mShippingCost -= $this->mItems[$j]->mShippingCost;
							$this->mTaxes -= $this->mItems[$j]->mTaxes;
							$this->mAllowance -= $this->mItems[$j]->mAllowanceAmt;
							$this->mTotal -= ($this->mItems[$j]->mCharge + $this->mItems[$j]->mShippingCost + $this->mItems[$j]->mTaxes - $this->mItems[$j]->mAllowance);
							unset($this->mItems[$j]);
						}
					}
				}
			}
		}
	}
	
	public function Load(&$trans, &$acktype = null)
	{
		$this->mBillTo = isset($trans->mUsers['billto']) ? $trans->mUsers['billto'] : $trans->mUsers->Add('billto');
		$this->mShipTo = isset($trans->mUsers['shipto']) ? $trans->mUsers['shipto'] : $trans->mUsers->Add('shipto');
		$this->mRetailPONumber = $trans->mPONumber;
		$this->mRetailPODate = $trans->mCreateDate;
		$this->mCustomerOrderNumber = isset($trans->mPONumbers['customer']) ? $trans->mPONumbers['customer'] : '';
		$this->mAlternateOrderNumber = isset($trans->mPONumbers['alternate']) ? $trans->mPONumbers['alternate'] : '';
		$this->mShipByDate = isset($trans->mDates['deliver_by']) ? $trans->mDates['deliver_by'] : (isset($trans->mDates['cancel_date']) ? $trans->mDates['cancel_date'] : '');
		$this->mAltPONumber = isset($trans->mPONumbers['alternate']) ? $trans->mPONumbers['alternate'] : '';
		$this->mShipCode = $this->mShipTo->mMarkedShippingCode;
		$this->mStoreNumber = isset($trans->mStoreNumber) ? $trans->mStoreNumber : '';
		// TODO finish making changes to use objects
		if(isset($trans->mMessages['marketing']))
		{
			$this->mMarketingMsg = $trans->mMessages['marketing']->ToString();
		}
		if(isset($trans->mMessages['returns']))
		{
			$this->mReturnsMsg = $trans->mMessages['returns']->ToString();
		}
		if(isset($trans->mMessages['delivery']))
		{
			$this->mDeliveryMsg = $trans->mMessages['delivery']->ToString();
		}
		$this->mShipRequirements = $trans->mShipRequirements;
		switch($trans->mPackingSlipType)
		{
			case '36':
				$this->mPackingSlipType['vendor'] = $this->mEdiVendor->mVendorName;
				$this->mPackingSlipType['type'] = 'target';
				break;
			case '32':
				$this->mPackingSlipType['vendor'] = $this->mEdiVendor->mVendorName;
				$this->mPackingSlipType['type'] = 'amazon.com';
		}
		$this_ack = $acktype[$trans->mPONumber];
		foreach($trans->mItems as $itemcheck)
		//for($i=0; $i<count($trans['lineitemid']); $i++)
		{
			if(is_null($this_ack) || $this_ack[$itemcheck->mLineNumber]->mStatus == 'IA' || $this_ack[$itemcheck->mLineNumber]->mStatus == 'IH') // if in stock or set to be in 1 week, go ahead
			{
				$thisitem = $itemcheck;
				if(!is_numeric($thisitem->mItemId)) $thisitem->GetItemId();
				if(!isset($thisitem->mChargeAmount)) $thisitem->mChargeAmount = $thisitem->mRetailerUnitCost;
				$this->mShippingCost += $thisitem->mShippingAmount;
				$this->mTaxes += $thisitem->mTaxes;
				// figure allowances
				if(!is_null($thisitem->mAllowanceAmount) && number_format($thisitem->mAllowanceAmount, 2) != '0.00')
				{
					$this->mAllowance += $thisitem->mAllowanceAmount;
				}
				$this->mTotal += ($thisitem->mRetailerUnitCost * $thisitem->mQuantity) + $thisitem->mShippingAmount - $thisitem->mAllowanceAmount;
				$this->mRetailTotal += ($thisitem->mChargeAmount * $thisitem->mQuantity) + $thisitem->mShippingAmount + $thisitem->mTaxes - $thisitem->mAllowanceAmount;
				$this->mItems[] = $thisitem;
				unset($thisitem);
			}
		}
		$this->mBillTo->AddUser();
		$this->mShipTo->AddUser();
		if(isset($this->mInternalPOId))
		{
			// order has been placed, see if shipping has been done
			$bol_id = $this->mDoEdiOrder->GetBolId($this->mInternalPOId);
			$shippinginfo = new ShippingEdi();
			$shippinginfo->mBolId = $bol_id;
			$shippinginfo->Load();
			$shippinginfo->mRetailerPODate = $this->mRetailPODate;
            
			// set the UPCs to the order UPCs
            if(count($shippinginfo->mPackages) > 0)
            foreach($shippinginfo->mPackages as $shippack)
			{
				foreach($shippack->mItems as $shipitem)
				{
					foreach($this->mItems as $poitem)
					{
						if($poitem->mLineNumber == $shipitem->mPOLineNumber || $poitem->mSKU == $shipitem->mSKU)
						{
							$shipitem->mUPC = $poitem->mUPC;
							$shipitem->mSKU = $poitem->mSKU;
							$shipitem->mRetailerPartNumber = $poitem->mRetailerPartNumber;
						}
					}
				}
			}
			$this->mShipping = $shippinginfo;
		}
	}
	
	public function Insert(&$edi, &$acktype = null)
	{
		$this->Load($edi, $acktype);
		return $this->Submit();
	}
}


class EdiPC extends EdiObject
{
	function __construct($data = null)
	{
		if(!is_null($data))
		{
			parent::__construct($data);
		}
		else
		{
			parent::__construct();
		}
	}

	public function Load($data)
	{
		$this->mEdiType = 'PC';
		$this->mParser = new EdiPCParser($data);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactions = new EdiPCTransactions();
		do
		{
			$trans = $this->mTransactions->Add();
			$this->mParser->GetTransactionData($trans);
		}
		while($this->mParser->Peek() != 'GE' && !$trans->mReject == true);
		$env = $this->mGroups->GetLast();
		$this->mParser->EndGroupEnvelope($env);
		if(!$env->ValidateControl() || count($this->mTransactions) != $env->mTransactions || $this->mParser->mReject == true)
		{
			$this->mReject = true;
		}
		return $this;
	}
}


class EdiPCItem extends EdiItem
{

	public $mOriginalPOLineNumber;
	public $mQuantityRemaining;
	public $mUnitOfMeasure;
	public $mProcessNumber;
	public $mPrice;
	
	function __construct()
	{
		parent::__construct();
	}
}

class EdiPCItems implements Iterator
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
	
	public function Add($line = null)
	{
		$this->mItems[] = new EdiPCItem();
		$ret = &end($this->mItems);
		$ret->mLineNumber = $line == null ? $this->mPos : $line;
		reset($this->mItems);
		return $ret;
	}
}


class EdiPCParser extends EdiParser
{
	private $mTransactionRawData;
	private $mTransactionNumber = 0;
	
	function __construct(&$data)
	{
		if(!is_null($data))
		{
			parent::__construct($data);
			$searchfrom = 0;
			$this->mTransactionRawData = array();
			do
			{
				$transpos = strpos($this->mRawData, $this->mSeparators->mSegment.'ST'.$this->mSeparators->mElement.'860', $searchfrom);
				if(!$transpos) break;
				$partial = strpos($this->mRawData, $this->mSeparators->mSegment.'SE', $transpos);
				$endspot = strpos($this->mRawData, $this->mSeparators->mSegment, $partial + 1);
				$this->mTransactionRawData[] = substr($this->mRawData, $transpos + 1, $endspot - $transpos);
				$searchfrom = $endspot;
			}
			while($searchfrom < strlen($this->mRawData));
		}
		else
		{
			parent::__construct();
			$this->mTransactionRawData = array();
		}
	}
	

	public function GetTransactionData(&$trans)
	{
		// should start w/ the 'PC' code ('Cancel')
		if($this->GetNextData() != 'ST' || $this->GetNextData() != '860')
		{
			$trans->mReject = true;
			$trans->mRejectReason = 'PCParser: not a PC';
			return;
		}
		$trans->mTypeCode = '860';
		$trans->mTransactionControlHeader = $this->GetNextData();
		$this->NextSegment();
		while($this->Peek() == 'BCH')
		{
			$this->Skip(3);
			$trans->mOriginalPONumber = $this->GetNextData();
			$this->Skip();
			$trans->mOriginalPORevision = $this->GetNextData();
			$trans->mCancelDate = date('Y-m-d', strtotime($this->GetNextData()));
			$this->NextSegment();
		}
		while($this->Peek() == 'REF')
		{
			$this->Skip();
			switch($this->GetNextData())
			{
				case 'VR':
					$trans->mVendorId = $this->GetNextData();
					break;
				case 'OQ':
					$trans->mAlternatePONumber = $this->GetNextData();
					break;
			}
			$this->NextSegment();
		}
		while($this->Peek() == 'PER')
		{
			$this->Skip();
			if($this->GetNextData() != 'AN')
			{
				$trans->mReject = true;
				$trans->mRejectReason = 'PER: should be attention-to-party [AN] for cancel';
				return;
			}
			$trans->mAttentionTo = $this->GetNextData();
			$this->NextSegment();
		}
		while($this->Peek() == 'DTM')
		{
			$this->Skip();
			if($this->GetNextData() != '004')
			{
				$trans->mReject = true;
				$trans->mRejectReason = 'DTM: should be purchase order date for cancel';
				return;
			}
			$trans->mOriginalPOOrderDate = $this->GetNextData();
			$this->NextSegment();
		}
		while($this->Peek() == 'N1')
		{
			$this->Skip();
			if($this->GetNextData() != 'VN')
			{
				$trans->mReject = true;
				$trans->mRejectReason = 'N1: should be type vendor [VN] for cancel';
				return;
			}
			$trans->mVendorName = $this->GetNextData();
			$trans->mVendorIdQualifier = $this->GetNextData();
			$trans->mVendorIdCode = $this->GetNextData();
			$this->NextSegment();
		}
		while($this->Peek() == 'POC')
		{
			switch($this->mEdiVendor->mTypeCode)
			{
				case 'WMI':
					$this->Skip();
					$thisitem = $trans->mItems->Add($this->GetNextData());
					$this->Skip();
					$this->Skip(5);
					$this->Skip();
					$thisitem->mOriginalPOLineNumber = $this->GetNextData();
					$this->NextSegment();
					break;
				case 'TVI':
					$this->Skip();
					$thisitem = $trans->mItems->Add();
					$thisitem->mOriginalPOLineNumber = $this->GetNextData();
					if($this->GetNextData() != 'DI')
					{
						$trans->mReject = true;
						$trans->mRejectReason = 'POC2: should be deleting items [DI] for cancel';
						return;
					}
					$thisitem->mQuantity = $this->GetNextData();
					$thisitem->mQuantityRemaining = $this->GetNextData();
					$thisitem->mUnitOfMeasure = $this->GetNextData();
					$thisitem->mPrice = $this->GetNextData();
					$this->Skip();
					if($this->GetNextData() != 'SK')
					{
						$trans->mReject = true;
						$trans->mRejectReason = 'POC8: should be stock keeping units [SK] for cancel';
						return;
					}
					$thisitem->mSKU = $this->GetNextData();
					if($this->Peek() != 'UI' && $this->Peek() != 'UN' && $this->Peek() != 'UP')
					{
						$trans->mReject = true;
						$trans->mRejectReason = 'POC10: should be a valid UPC type for cancel';
						return;
					}
					$this->Skip();
					$thisitem->mUPC = $this->GetNextData();
					if($this->GetNextData() != 'PR')
					{
						$trans->mReject = true;
						$trans->mRejectReason = 'POC12: should be Process Number [PR] for cancel';
						return;
					}
					$thisitem->mProcessNumber = $this->GetNextData();
					$this->NextSegment();
					break;
			}
		}
		foreach($trans->mItems as $thisitem)
		{
			while($this->Peek() == 'PID')
			{
				$this->Skip();
				switch($this->GetNextData())
				{
					case 'F':
						$this->Skip(3);
						$thisitem->mDescription = $this->GetNextData();
						break;
					case 'S':
						$this->Skip(2);
						$thisitem->mDescription = $this->GetNextData();
						break;
					case 'X':
						$this->Skip(2);
						$thisitem->mDescription = $this->GetNextData()."\n".$this->GetNextData();
						break;
				}
				$this->NextSegment();
			}
		}
		while($this->Peek() == 'CTT')
		{
			$this->Skip();
			$trans->mTotalItems = $this->GetNextData();
			if(!is_null($this->Peek())) $trans->mTotalQuantityCanceled = $this->GetNextData();
			$this->NextSegment();
		}
		while($this->Peek() == 'SE')
		{
			$this->Skip();
			$trans->mSegmentTotal = $this->GetNextData();
			$trans->mTransactionControlFooter = $this->GetNextData();
			$this->NextSegment();
		}
		$trans->mTotalTransactionSegments = substr_count($this->mTransactionRawData[$this->mTransactionNumber], $this->mSeparators->mSegment);
		$this->mTransactionNumber++;
		if($trans->mSegmentTotal != $trans->mTotalTransactionSegments)
		{
			$this->mReject = true;
		}
	}
}


class EdiPCTransaction extends EdiTransaction
{
	public $mOriginalPONumber;
	public $mOriginalPORevision;
	public $mCancelDate;
	public $mVendorId;
	public $mAlternatePONumber;
	public $mAttentionTo;
	public $mOriginalPOOrderDate;
	public $mVendorName;
	public $mVendorIdQualifier;
	public $mVendorIdCode;
	public $mItems; // based on EdiItems class, include PO change line #, Qty canceled, QuantityRemaining, UOM, ProcessNumber
	public $mTotalItems;
	public $mTotalQuantityCanceled;
	
	function __construct()
	{
		parent::__construct();
		$this->mItems = new EdiPCItems();
	}
}


class EdiPCTransactions implements Iterator
{
	protected $mTransactions;
	public $mTransactionRawData;
	private $mPos;
	
	function __construct()
	{
		$this->mGroups = array();
		$this->mDates = new EdiDates();
		$this->mUsers = new EdiUsers();
		$this->mPos = 0;
	}
	
	function rewind()
	{
		$this->mPos = 0;
	}

	function current()
	{
		return $this->mTransactions[$this->mPos];
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
		return isset($this->mTransactions[$this->mPos]);
	}
	
	public function Add()
	{
		$this->mTransactions[] = new EdiPCTransaction();
		$ret = &end($this->mTransactions);
		reset($this->mTransactions);
		return $ret;
	}
}


class EdiPO extends EdiObject
{
	function __construct()
	{
		parent::__construct();
	}

	public function Load(&$data)
	{
		$this->mEdiType = 'PO';
		$this->mParser = new EdiPOParser($data);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactions = new EdiPOTransactions();
		do
		{
			$trans = $this->mTransactions->Add();
			$trans->mPackingSlipType = $this->mEdiVendor->mVendorName;
			$this->mParser->GetTransactionData($trans);
		}
		while($this->mParser->Peek() != 'GE' && !$trans->mReject == true);
		$env = $this->mGroups->GetLast();
		$this->mParser->EndGroupEnvelope($env);
		if(!$env->ValidateControl() || count($this->mTransactions) != $env->mTransactions || $this->mParser->mReject == true)
		{
			$this->mReject = true;
		}
		return $this;
	}
	
	public function GetRetailerPO()
	{
		// TODO need to make i think
	}
}


class EdiPODataElement
{
	public $mName;
	public $mSourceQualifier;
	public $mSourceId;
	public $mData;
	
	public function __toString()
	{
		return $this->mData;
	}
}


class EdiPODataElements implements arrayaccess
{
	private $mElements;
	
	function __construct()
	{
		$this->mElements = array();	
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mElements[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mElements[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mElements[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mElements[$offset]) ? $this->mElements[$offset] : null;
	}
	
	public function Add()
	{
		$this->mElements[] = new EdiPODataElement();
		$ret = &end($this->mElements);
		reset($this->mElements);
		return $ret;
	}
}


class EdiPOItemsByPOStatus implements arrayaccess
{
	private $mPOs;
	
	function __construct()
	{
		$this->mPOs = array();
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mPOs[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mPOs[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mPOs[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mPOs[$offset]) ? $this->mPOs[$offset] : null;
	}
	
	public function Add($po, $line)
	{
		if(!isset($this->mPOs[$po])) $this->mPOs[$po] = new EdiPOItemsStatus();
		$ret = &$this->mPOs[$po]->Add($line);
		return $ret;
	}
}


class EdiPOItemStatus
{
	public $mLineNumber;
	public $mStatus;
	public $mQuantity;
}


class EdiPOItemsStatus implements arrayaccess
{
	private $mItems;
	private $mPos;
	
	function __construct()
	{
		$this->mItems = array();
		$this->mPos = 0;
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mItems[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mItems[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mItems[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mItems[$offset]) ? $this->mItems[$offset] : null;
	}
	
	public function Add($line)
	{
		$this->mItems[$line] = new EdiPOItemStatus();
		$ret = &end($this->mItems);
		$ret->mLineNumber = $line;
		reset($this->mItems);
		return $ret;
	}
}


class EdiPONumber
{
	public $mType;
	public $mData;
	
	function __toString()
	{
		return $this->mData;
	}
}


class EdiPONumbers implements arrayaccess
{
	private $mNumbers;
	
	function __construct()
	{
		$this->mNumbers = array();	
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mNumbers[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mNumbers[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mNumbers[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mNumbers[$offset]) ? $this->mNumbers[$offset] : null;
	}
	
	public function Add($type, $number)
	{
		$this->mNumbers[$type] = new EdiPONumber();
		$ret = &end($this->mNumbers);
		$ret->mType = $type;
		$ret->mData = $number;
		reset($this->mNumbers);
		return $ret;
	}
}


class EdiPOParser extends EdiParser
{
	private $mTransactionNumber = 0;
	public $mTransactionRawData;
	private $mTransObject;
	
	function __construct(&$data)
	{
		if(!is_null($data))
		{
			parent::__construct($data);
			$searchfrom = 0;
			$this->mTransactionRawData = array();
			do
			{
				$transpos = strpos($this->mRawData, $this->mSeparators->mSegment.'ST'.$this->mSeparators->mElement.'850', $searchfrom);
				if(!$transpos) break;
				$partial = strpos($this->mRawData, $this->mSeparators->mSegment.'SE', $transpos);
				$endspot = strpos($this->mRawData, $this->mSeparators->mSegment, $partial + 1);
				$this->mTransactionRawData[] = substr($this->mRawData, $transpos + 1, $endspot - $transpos);
				$searchfrom = $endspot;
			}
			while($searchfrom < strlen($this->mRawData));
		}
		else
		{
			parent::__construct();
			$this->mTransactionRawData = array();
		}
	}
	
	public function GetTransactionData(&$trans)
	{
		// should start w/ the 'ST' code ('PO')
		if($this->GetNextData() != 'ST' || $this->GetNextData() != '850')
		{
			$trans->mReject = true;
			$trans->mRejectReason = 'PPOParser: not a PO';
			return;
		}
		$trans->mTypeCode = '850';
		$trans->mTransactionControlHeader = $this->GetNextData();
		$this->NextSegment();
		if($this->GetNextData() != 'BEG' || $this->GetNextData() != '00' || $this->GetNextData() != 'DS')
		{
			$trans->mReject = true;
			$trans->mRejectReason = 'BEG: invalid transaction data in PO';
			return;
		}
		$trans->mPONumber = $this->GetNextData();
		$this->Skip(); // usually blank
		$podate = $this->GetNextData();
		$trans->mCreateDate = date('Y-m-d', strtotime($podate));
		$this->NextSegment();
		$this->mTransObject = &$trans;
		while($this->Peek() != 'SE' && !(isset($this->mTransObject->mReject) && $this->mTransObject->mReject == true))
		{
			$this->RunNextSegment();
		}
		while($this->Peek() == 'SE')
		{
			$this->Skip();
			$trans->mSegmentTotal = $this->GetNextData();
			$trans->mTransactionControlFooter = $this->GetNextData();
			$this->NextSegment();
		}
		$trans->mTotalTransactionSegments = substr_count($this->mTransactionRawData[$this->mTransactionNumber], $this->mSeparators->mSegment);
		$trans->mPlaceOrder = true;
		$this->mTransactionNumber++;
		if($trans->mSegmentTotal != $trans->mTotalTransactionSegments)
		{
			$this->mReject = true;
		}
		if($this->mEndOfFile) $trans->mSendErrorNotice = true;
	}
	
	private function RunNextSegment()
	{
		switch($this->Peek())
		{
			case 'REF':
				$this->Skip();
				switch($this->GetNextData())
				{
					case 'OQ':
						$this->mTransObject->mPONumbers->Add('alternate', $this->GetNextData());
						break;
					case 'D7':
						$this->mTransObject->mPackingSlipType = $this->GetNextData();
						break;
					case 'VR':
						$this->mTransObject->mVendorId = $this->GetNextData();
						break;
					case 'WS':
						$this->mTransObject->mWarehouseCode = $this->GetNextData();
						break;
					case 'CO':
						$this->mTransObject->mPONumbers->Add('customer', $this->GetNextData());
						break;
					case 'ST':
						$this->mTransObject->mStoreNumber = $this->GetNextData();
						break;
					case 'IA':
						$this->mTransObject->mInternalVendorId = $this->GetNextData();
						break;
				}
				$this->NextSegment();
				break;
		
			case 'CTP':
				$this->Skip();
				if($this->GetNextData() != 'RS')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'CTP: invalid pricing info in PO';
					return;
				}
				$this->Skip();
				$this->mTransObject->mTotalPrice = $this->GetNextData();
				$this->mTransObject->mTotalQuantity = $this->GetNextData();
				if($this->mTransObject->mTotalQuantity != 1)
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'CTP: unit quantity should be 1 in PO';
					return;
				}
				$this->mTransObject->mCompositeUOM = $this->GetNextData();
				$this->NextSegment();
				break;
		
			case 'CSH':
				$this->Skip();
				$this->mTransObject->mShipRequirements = $this->GetNextData() == 'P2' ? 'asap' : 'complete';
				$this->NextSegment();
				break;

			case 'DTM':
				$this->Skip();
				switch($this->GetNextData())
				{
					case '806':
						$this->mTransObject->mDates->Add('order', date('Y-m-d', strtotime($this->GetNextData())));
						break;
					case '002':
						$this->mTransObject->mDates->Add('deliver_by', date('Y-m-d', strtotime($this->GetNextData())));
						break;
					case '001':
						$this->mTransObject->mDates->Add('cancel_date', date('Y-m-d', strtotime($this->GetNextData())));
						break;
					case '006':
						$this->mTransObject->mDates->Add('guest_order', date('Y-m-d', strtotime($this->GetNextData())));
						break;
				}
				$this->NextSegment();
				break;
		
			case 'N9':
				$this->Skip();
				if($this->Peek() != 'ZZ' && $this->Peek() != 'L1')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'N9: wrong reference ID qualifier in PO';
					return;
				}
				$this->Skip();
				$msgtype = $this->GetNextData();
				$msg1 = $this->GetNextData();
				if($this->mEdiVendor->mTypeCode == 'TVI' && !isset($this->mTransObject->mOrderStatusMessage))
				{
					// if this is the first message, is the shipment status msg
					$this->mTransObject->mOrderStatusMessage = $msg1;
				}
				else
				{
					switch(strtolower(substr($msgtype, 0, 9)))
					{
						case 'marketing':
							$msgtype = 'marketing';
							break;
						case 'returnsms':
							$msgtype = 'returns';
							break;
						case 'lastdeliv':
							$msgtype = 'delivery';
							break;
					}
					if($msg1 != '') $this->mTransObject->mMessages->Append($msgtype, $msg1);
				}
				$this->NextSegment();
				while($this->Peek() == 'MSG')
				{
					$this->Skip();
					$theline = $this->GetNextData();
					if($this->Peek() != 'AA' && $this->Peek() != '')
					{
						$this->mTransObject->mReject = true;
						$this->mTransObject->mRejectReason = 'MSG: wrong data in message text codes for PO';
						return;
					}
					$this->Skip();
					$lines = $this->GetNextData();
					// if Target, MSG is return method type on first one only, so check for set status before assigning
					if($this->mTransObject->mReturnMethod == '' || !isset($this->mTransObject->mReturnMethod))
					{
						$this->mTransObject->mReturnMethod = $theline;
					}
					else
					{
						if($theline != '' && $theline != '0') 			$this->mTransObject->mMessages->Append($msgtype, $theline); // only add if the data != '' or '0'
					}
					$this->NextSegment();
				}
				unset($msgtype);
				unset($theline);
				unset($lines);
				break;
		
			case 'N1':
				$this->Skip();
				switch($this->GetNextData())
				{
					case 'BT':
						$thisuser = $this->mTransObject->mUsers->Add('billto');
						break;
					case 'RT':
						$thisuser = $this->mTransObject->mUsers->Add('returnedto');
						break;
					case 'ST':
						$thisuser = $this->mTransObject->mUsers->Add('shipto');
						break;
					case 'SO':
						$thisuser = $this->mTransObject->mUsers->Add('soldto');
						break;
				}
				$thisuser->mName = $this->GetNextData();
				if($this->GetNextData() != '')
				{
					if($this->GetNextData() != $this->mTransObject->mStoreNumber)
					{
						$this->mTransObject->mReject = true;
						$this->mTransObject->mRejectReason = 'N1: store number mismatch in PO';
						return;
					}
				}
				$this->NextSegment();
				if($this->Peek() == 'N2')
				{
					$this->Skip();
					$thisuser->mUserInfo = $this->GetNextData();
					$this->NextSegment();
				}
				if($this->Peek() != 'N3')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = "N3: {$thisuser->mType} missing in PO";
					return;
				}
				$this->Skip();
				$thisuser->mAddress1 = $this->GetNextData();
				$test = $this->GetNextData();
				if($test != '') $thisuser->mAddress2 = $test;
				$this->NextSegment();
				if($this->GetNextData() != 'N4')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = "N4: {$thisuser->mType} missing in PO";
					return;
				}
				$thisuser->mCity = $this->GetNextData();
				$thisuser->mState = $this->GetNextData();
				$thisuser->mPostal = $this->GetNextData();
				$thisuser->mCountry = $this->GetNextData();
				$this->NextSegment();

				// grab any refs
				while($this->Peek() == 'REF')
				{
					$this->Skip();
					switch($this->GetNextData())
					{
						case "4N": // special payment reference number
							$thisuser->mPaymentRefNumber = $this->GetNextData();
							break;
						case "OD": // original return request ref number
							$thisuser->mReturnReferenceNumber = $this->GetNextData();
							break;
						case "PN": // permit number
							$this->Skip();
							if($this->GetNextData() != 'ZZ')
							{
								$this->mTransObject->mReject = true;
								$this->mTransObject->mRejectReason = 'REF: no ZZ code in the reference ID qualifier (PN City) for PO';
								return;
							}
							$thisuser->mPermitCity = $this->GetNextData();
							if($this->GetNextData() != 'ZZ')
							{
								$this->mTransObject->mReject = true;
								$this->mTransObject->mRejectReason = 'REF: no ZZ code in the reference ID qualifier (PN State) for PO';
								return;
							}
							$thisuser->mPermitState = $this->GetNextData();
							if($this->GetNextData() != 'ZZ')
							{
								$this->mTransObject->mReject = true;
								$this->mTransObject->mRejectReason = 'REF: no ZZ code in the reference ID qualifier (PN Postal) for PO';
								return;
							}
							$thisuser->mPermitPostal =  $this->GetNextData();
							break;
						case 'PHC':
							$thisuser->mMethodCode = $this->GetNextData();
							break;
					}
					$this->NextSegment();
				}
				$contacts = 0;
				if($this->Peek() == 'PER')
				{
					$this->Skip();
					$thisuser->mContactFunction[] = $this->GetNextData();
					$thisuser->mContactName[] = $this->GetNextData();
					while($this->Peek() == 'TE' || $this->Peek() == 'EM') // telephone or email
					{
						switch($this->GetNextData())
						{
							case 'TE':
								$thisuser->mContactTelephone[] = $this->GetNextData();
								break;
							case 'EM':
								$thisuser->mContactEmail[] = $this->GetNextData();
						}
					}
					$this->Skip(2);
					// check for the existence of another record...
					while($this->Peek() == 'TE' || $this->Peek() == 'EM') // telephone or email
					{
						switch($this->GetNextData())
						{
							case 'TE':
								$thisuser->mContactTelephone[] = $this->GetNextData();
								break;
							case 'EM':
								$thisuser->mContactEmail[] = $this->GetNextData();
						}
					}
					$contacts++;
					$this->NextSegment();
				}
				break;
			
			case 'TD5':
				$this->Skip();
				if($this->Peek() != 'O' && $this->Peek() != '')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'TD5: not origin carrier for PO';
					return;
				}
				$this->Skip();
				if($this->Peek() != '93' && $this->Peek() != '94')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'TD5: wrong originating code in transaction set for PO';
					return;
				}
				$this->Skip();
				switch($this->mEdiVendor->mTypeCode)
				{
					case 'WMI':
						$returnme = $this->mTransObject->mUsers->GetType('shipto');
						$returnme->mMarkedShippingCode = $this->GetNextData();
						$this->Skip(3);
						$returnme->mShipToStore = $this->GetNextData();
						$returnme->mStoreId = $this->GetNextData();
						$this->Skip(3);
						$returnme->mServiceLevel = $this->GetNextData();
						break;
					
					case 'TVI':
						// define the shipto user now
						$returnme = $this->mTransObject->mUsers->Add('shipto');
						$returnme->mMarkedShippingCode = $this->GetNextData();
						$this->Skip();
						$returnme->mCarrierName = $this->GetNextData();
						$this->Skip(6);
						$returnme->mServiceLevel = $this->GetNextData();
						break;
				}
				$this->NextSegment();
				break;

			case 'PO1':
				$this->Skip();
				$item = $this->mTransObject->mItems->Add($this->GetNextData()); // first data is lineitem_id
				$item->mQuantity = $this->GetNextData();
				if($this->GetNextData() != 'EA')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'PO1: units must be EA in PO';
					return;
				}
				// check to see if the walmart price matches the db price
				$item->mRetailerUnitCost = $this->GetNextData();
				if($this->Peek() != 'QE' && $this->Peek() != 'PE')
				{
					$this->mTransObject->mReject = true;
					$this->mTransObject->mRejectReason = 'PO1: basis of unit price code must be QE or PE in PO';
					return;
				}
				$this->Skip();
				switch($this->Peek())
				{
					case 'BP':
						$this->Skip();
						$item->mRetailerPartNumber = $this->GetNextData();
						if($this->GetNextData() != 'UP')
						{
							$this->mTransObject->mReject = true;
							$this->mTransObject->mRejectReason = 'PO1: next item ID should be UPC in PO';
							return;
						}
						$thisupc = $this->GetNextData();
						// now compare the db price w/ the passed-in unit price
						$pricecheck = $this->mDoEdiParser->PriceCheck($thisupc, $item->mRetailerUnitCost);
						// TODO set what happens if pricecheck fails
						$item->mUPC = $thisupc;
						if($this->GetNextData() != 'VP')
						{
							$this->mTransObject->mReject = true;
							$this->mTransObject->mRejectReason = 'PO1: next item ID should be the internal part number in PO';
							return;
						}
						$item->mPartNumber = $this->GetNextData();
						$this->NextSegment();
						break;
				
					case 'SK':
						$this->Skip();
						$item->mSKU = $this->GetNextData();
						if($this->GetNextData() != 'IN')
						{
							$this->mTransObject->mReject = true;
							$this->mTransObject->mRejectReason = 'PO1: next item ID should be vendor part number in PO';
							return;
						}
						$item->mRetailerPartNumber = $this->GetNextData();
						if($this->GetNextData() != 'UP' && $this->GetNextData() != 'IB')
						{
							$this->mTransObject->mReject = true;
							$this->mTransObject->mRejectReason = 'PO1: next item ID should be UPC in PO';
							return;
						}
						$thisupc = $this->GetNextData();
						// now compare the db price w/ the passed-in unit price
						$pricecheck = $this->mDoEdiParser->PriceCheck($thisupc, $item->mRetailerUnitCost);
						$item->mUPC = $thisupc;
						$this->NextSegment();
						break;
				
					default:
						$this->mTransObject->mReject = true;
						$this->mTransObject->mRejectReason = 'PO1: incorrect item ID section in PO';
						return;
				}
				while($this->Peek() == 'PID')
				{
					$this->Skip();
					if($this->GetNextData() != 'F' && $this->GetNextData() != 'X')
					{
						$this->mTransObject->mReject = true;
						$this->mTransObject->mRejectReason = 'PID: Item description must be free-form or semi-structured in PO';
						return;
					}
					$this->Skip(3);
					$item->mDescription = $this->GetNextData();
					$this->NextSegment();
				}
				while($this->Peek() == 'SAC')
				{
					$this->Skip();
					if($this->GetNextData() != 'N')
					{
						$this->mTransObject->mReject = true;
						$this->mTransObject->mRejectReason = 'SAC: no allowance or charge in PO';
						return;
					}
					switch($this->GetNextData())
					{
						case 'D340': // Goods & Services Charge
							$this->Skip(5); // always 5 skips
							$item->mChargeAmount = $this->GetNextData();
							$this->Skip(3);
							$item->mChargeMethod = $this->GetNextData();
							break;
						case 'G830': // Shipping & handling
							$this->Skip(5); // always 5 skips
							$item->mShippingAmount = $this->GetNextData();
							$this->Skip(3);
							$item->mShippingMethod = $this->GetNextData();
							break;
						case 'H000': // Special allowance
							$this->Skip(5); // always 5 skips
							$item->mAllowanceAmount = $this->GetNextData();
							$this->Skip(3);
							$item->mAllowanceMethod = $this->GetNextData();
							$this->Skip(2);
							$item->mAllowanceDescription = $this->GetNextData();
							break;
						case 'H151': // Special servicves
							$this->Skip(5); // always 5 skips
							$item->mServicesAmount = $this->GetNextData();
							$this->Skip(3);
							$item->mServicesMethod = $this->GetNextData();
							$this->Skip(2);
							$item->mServicesDescription = $this->GetNextData();
							break;
						case 'H850': // tax
							$this->Skip(5);
							$item->mTaxes = $this->GetNextData();
							$this->Skip(3);
							$item->mTaxesMethod = $this->GetNextData();
							break;
					}
					$this->NextSegment();
				}
				break;

			case 'PKG':
				switch($this->mEdiVendor->mTypeCode)
				{
					case 'WMI':
						$this->Skip(3);
						$this->mTransObject->mGiftWrapCode = $this->GetNextData();
						break;
					
					case 'TVI':
						$this->Skip();
						if($this->GetNextData() != 'F' && $this->GetNextData() != 'WM')
						{
							$this->mTransObject->mReject = true;
							$this->mTransObject->mRejectReason = 'PKG: Desc. must be specified in free-form manner, with wrapping material noted';
							return;
						}
						$this->Skip(3);
						$this->mTransObject->mGiftWrapCode = $this->GetNextData();
						break;
				}
				$this->NextSegment();
				break;

			case 'N9':
				$this->Skip();
				while($this->Peek() == 'QY')
				{
					$this->Skip();
					// QY = service performed code; VGM (Gift message, 4 lines), VGT (Gift Tag [ to/from]); VGW (Gift Wrapping)
					$svcs = $this->mTransObject->mServices->Add();
					$svcs->mCode = $this->GetNextData();
					$this->Skip(4);
					$svcs->mReqSequence = $this->GetNextData();
					$svcs->mIdQualifier = $this->GetNextData();
					$svcs->mId = $this->GetNextData();
					$this->NextSegment();
					$service_element = array();
					while($this->Peek() == 'MSG')
					{
						$this->Skip();
						$service_element[] = $this->GetNextData();
						$this->NextSegment();
					}
					$svcs->mData = $service_element;
					$this->NextSegment();
				}
				while($this->Peek() == 'S2')
				{
					$this->Skip(2);
					$ele = $this->mTransObject->mDataElements->Add();
					$ele->mName = $this->GetNextData();
					$this->Skip(4);
					$ele->mSourceQualifier = $this->GetNextData();
					$ele->mSourceId = $this->GetNextData();
					$this->NextSegment();
					$data_element = '';
					while($this->Peek() == 'MSG')
					{
						$this->Skip();
						$data_element .= $this->GetNextData();
						$this->NextSegment();
					}
					$ele->mData = $data_element;
					$this->NextSegment();
				}
				while($this->Peek() == 'L1')
				{
					$this->Skip(2); // next element will be Message
					$this->mTransObject->mOrderStatusMessage = $this->GetNextData();
					$this->NextSegment();
				}
				break;
				
			case 'CTT':
				$this->Skip();
				$this->mTransObject->mNumberOfLineItems = $this->GetNextData();
				$this->NextSegment();
				break;
				
			default:
				break;
		}
		if(isset($returnme)) return $returnme;
	}
}


class EdiPOService
{
	public $mCode;
	public $mReqSequence;
	public $mIdQualifier;
	public $mId;
	public $mData;
	
	public function __toString()
	{
		return $this->mData;
	}
}

class EdiPOServices implements arrayaccess
{
	private $mSvcs;
	
	function __construct()
	{
		$this->mSvcs = array();	
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mSvcs[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mSvcs[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mSvcs[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mSvcs[$offset]) ? $this->mSvcs[$offset] : null;
	}
	
	public function Add()
	{
		$this->mSvcs[] = new EdiPOService();
		$ret = &end($this->mSvcs);
		reset($this->mSvcs);
		return $ret;
	}
}


class EdiPOTransaction extends EdiTransaction
{
	public $mPONumber;
	public $mCreateDate;
	public $mPlaceOrder;
	public $mPONumbers;
	public $mVendorId;
	public $mInternalVendorId;
	public $mPackingSlipType;
	public $mWarehouseCode;
	public $mStoreNumber;
	public $mTotalPrice;
	public $mTotalQuantity;
	public $mCompositeUOM;
	public $mShipRequirements;
	public $mGiftWrapCode;
	public $mOrderStatusMessage;
	public $mReturnMethod;
	public $mNumberOfLineItems;
	public $mItems;
	public $mServices;
	public $mDataElements;
	
	function __construct()
	{
		parent::__construct();
		$this->mPONumbers = new EdiPONumbers();
		$this->mItems = new EdiItems();
		$this->mServices = new EdiPOServices();
		$this->mDataElements = new EdiPODataElements();
	}
}


class EdiPOTransactions implements Iterator
{
	protected $mTransactions;
	public $mType; // two-char code of the transaction type
	public $mTransactionRawData;
	private $mPos;
	
	function __construct()
	{
		$this->mTransactions = array();
		$this->mPos = 0;
	}
	
	function rewind()
	{
		$this->mPos = 0;
	}

	function current()
	{
		return $this->mTransactions[$this->mPos];
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
		return isset($this->mTransactions[$this->mPos]);
	}
	
	public function Add()
	{
		$this->mTransactions[] = new EdiPOTransaction();
		$ret = &end($this->mTransactions);
		reset($this->mTransactions);
		return $ret;
	}
}


class EdiPR extends EdiObject
{
	protected $mSourceEdi;
	
	function __construct()
	{
		parent::__construct();
	}
	
	public function Load(&$data)
	{
		$this->mParser = new EdiPRParser($data);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactionData = array();
		$this->mTransactionData = $this->mParser->GetTransactionData();
	}

	public function Make(&$edi, &$acktype = null)
	{
		$this->mSourceEdi = $edi;
		$output = array();
		if(isset($edi->mEdiObject) && $edi->mEdiObject->mEdiType == 'PO')
		{
			foreach($edi->mEdiObject->mTransactions as $trans)
			{
				$this->mOutputEdi = new EdiPRBuilder($edi->mEdiVendor);
				$output = $this->mOutputEdi->Make($edi, $acktype, $trans);
				$this->Send();
			}
			return $output;
		}
		else
		if(isset($edi->mEdiPOs))
		{
			// doc type = cancel
			foreach($edi->mEdiPOs as $ponum => $thispo)
			{
				for($j = 0; $j < count($thispo->mLineNumbers); $j++)
				{
					if(!isset($acktype[$ponum][$thispo->mLineNumbers[$j]])) continue;
					$this->mOutputEdi = new EdiPRBuilder($edi->mSender);
					$output = $this->mOutputEdi->Make($edi, $acktype, $j, $ponum);
					$this->mOutput = $output;
					$this->Send();
				}
			}
		}
	}
}

/**
 * EdiPRBuilder
 * Constructor for Purchase Order Response EDI files
 */
class EdiPRBuilder extends EdiBuilder
{
	function __construct()
	{
		parent::__construct();
	}

    /**
     * Make
     * Constructs Purchase Order Response EDI File
     * @param EdiPR obj
     * @param array acktype Acknowledgement codes to return
     * @param EdiPR Transactions trans
     * @param int cancel_po Cancellation PO Number
     * @return EdiPRBuilder
     */
	public function Make(&$obj = null, &$acktype = null, &$trans = null, $cancel_po = null)
	{
		parent::StartAll('PR');
		if(is_null($obj) || is_null($acktype) || is_null($trans)) die('edi & acktype & trans must not be null for PR');
		$return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;
		foreach($obj->mEdiObject->mGroups as $group)
		{
			foreach($obj->mEdiObject->mTransactions as $editrans)
			{
				$this_ack = &$acktype[$trans->mPONumber];
				$this->mTransactionSegmentStart = count($this->mSegments);
				$this->NewTransControl();
				$seg = $this->NewSegment('ST');
				$seg[1] = '855';
				$seg[2] = $this->GetTransControl();
				$origtrans = true;
				if(isset($obj->mEdiObject) && $obj->mEdiObject->mEdiType == 'PO' && $origtrans)
				{
					$thistype = 'PO';
					$thispo = $trans->mPONumber;
				}
				if(isset($obj->mEdiPOs) && $origtrans)
				{
					$thistype = 'PC';
					// trans is preset, but we get a lot of data from other locations
					// need to set that up here
					// cancel_po should be passed in now...
					$thispo = $cancel_po;
				}
				$seg = $this->NewSegment('BAK');
				$seg[1] = '00';
				switch($this->mEdiVendor->mTypeCode)
				{
					case "WMI":
						$seg[2] = 'NA';
						break;
					case "TVI":
						$seg[2] = 'AC';
						break;
				}
				$seg[3] = $thispo;
                switch($this->mEdiVendor->mTypeCode)
                {
                    case "TVI":
                        $seg[4] = gmdate('Ymd', strtotime($editrans->mCreateDate));
                        break;
                    default:
                        $seg[4] = gmdate('Ymd');
                        break;
                }
				if($this->mEdiVendor->mTypeCode == 'TVI')
				{
					$seg = $this->NewSegment('REF');
					$seg[1] = 'VR';
					$seg[2] = $this->mEdiVendor->mVendorId;
				}
				$backordered = 0;
				$transactions = 0;
				if($thistype == 'PO')
				{
					foreach($trans->mItems as $lineitem)
					{
						if(is_null($lineitem)) continue;
						$transactions++;
						$seg = $this->NewSegment('PO1');
						if($this->mEdiVendor->mTypeCode == 'WMI')
						{
							$seg[1] = str_pad($transactions, 6, '0', STR_PAD_LEFT);
							$seg[6] = 'PL';
							$seg[7] = $lineitem->mLineNumber;
							$seg = $this->NewSegment('ACK');
							$seg[1] = $this_ack[$lineitem->mLineNumber]->mStatus;
							if($this_ack[$lineitem->mLineNumber]->mStatus == 'R4')
							{
								$seg[2] = $lineitem->mQuantity;
								$seg[3] = 'EA';
							}
						}
						else if($this->mEdiVendor->mTypeCode == 'TVI')
						{
							$seg[1] = $lineitem->mLineNumber;
							$seg[2] = $lineitem->mQuantity;
							$seg[6] = 'SK';
							$seg[7] = $lineitem->mSKU;
							$seg[8] = 'IN';
							$seg[9] = $lineitem->mRetailerPartNumber;
							$seg[10] = 'UP';
							$seg[11] = $lineitem->mUPC;
							$seg = $this->NewSegment('ACK');
							$seg[1] = $this_ack[$lineitem->mLineNumber]->mStatus != 'IA' ? 'IR' : 'IA';
                            if($thisack[$lineitem->mLineNumber]->mStatus == 'IR')
                            {
                                $seg[2] = $lineitem->mQuantity;
                                $seg[3] = 'EA';
                                $seg[5] = date('Ymd');
                            }
						}
						if($this_ack[$lineitem->mLineNumber]->mStatus != 'IA')
						{
							// TODO : make the displayed rejection codes be those that apply to the particular vendor
							// order not accepted, send an email to Will & Amy w/ this info
							sendmail('Amy Bowen <amy@retailservicesystems.com>, William Lightning <will@retailservicesystems.com>',
							ucfirst($this->mEdiVendor->mVendorName)." EDI Order {$trans->mPONumber} Item {$lineitem->mSKU} Rejected",
							ucfirst($this->mEdiVendor->mVendorName)." EDI-based Order # {$trans->mPONumber} Item {$lineitem->mSKU} was rejected.\nPossible reject codes and their meanings are:\nR1 = Not a Contract Item\nR2 = Invalid Item Number (Unrecognized)\nR4 = Item Unavailable, i.e. Out of Stock\n\nThis item's reject code: ".$this_ack[$lineitem->mLineNumber]->mStatus."\n\nWeb Administration has been notified.");
						}
					}
				}
				else
				{
					// cancellation acknowledgment
					for($i=0; $i<count($acktype[$thispo]); $i++)
					{
						$transactions++;
						$seg = $this->NewSegment('PO1');
						$seg[1] = str_pad($transactions, 6, '0', STR_PAD_LEFT);
						$seg[6] = 'PL';
						$seg[7] = $obj->mEdiPOs[$thispo]->mLineNumbers[$trans].
						$seg = $this->NewSegment('ACK');
						$seg[1] = 'ID';
					}
				}
				$seg = $this->NewSegment('CTT');
				$seg[1] = $transactions;
				$seg = $this->NewSegment('SE');
				$seg[1] = count($this->mSegments) - $this->mTransactionSegmentStart;
				$seg[2] = $this->GetTransControl();
				$this->mTransactionSegmentEnd = count($this->mSegments);
				$this->MakeTransactionSegments();
			}
		}
		$this->mEdiTypeId = '855PR';
		parent::EndAll();
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;		
		$return .= $this->mInterchangeFooter;
		if($obj->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}
}


class EdiSH extends EdiObject
{
	protected $mShippingEdi;
	public $mDoEdiSh;
	
	public function __construct()
	{
		parent::__construct();
		$this->mDoEdiSh = new DoEdiSh();
	}
	
	public function Load($data)
	{
		$this->mParser = new EdiSHParser($data);
		$this->mParser->StartGroupEnvelope($this->mGroups);
		$this->mTransactionData = array();
		$this->mTransactionData = $this->mParser->GetTransactionData();
	}

	public function EdiExistsCheck($bol)
	{
		return $this->mDoEdiSh->CheckForEdi($bol);
	}
	
	public function Build($data = null)
	{
		if(!is_null($data))
		{
			$this->mShippingEdi = $data;
			$this->mOutputEdi = new EdiSHBuilder();
			$output = $this->mOutputEdi->Make($this->mShippingEdi);
			if($output == false) return false;
			return $output;
		}
	}
}


class EdiSHBuilder extends EdiBuilder
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function Make(&$obj = null, $acktype = null, $trans = null, $cancel_po = null)
	{
		// check if the shipping object has already been made
		// filename will have 856SH in it, along w/ retailer_po & bol_id
		$does_exist = $this->mDoEdiBuilder->GetSHInformation($obj);
		if(is_array($does_exist)) return false;
		// has not been made, time to make
		// first thing we do is set up for any differences between vendors
		switch($this->mEdiVendor->mTypeCode)
		{
			case 'TVI':
				$vendor = 'target';
                $tvi_runonce = true;
				break;
			case 'WMI':
				$vendor = 'walmart';
				break;
		}
		parent::StartAll('SH');
		if(get_class($obj) != "ShippingEdi")
		{
			// $obj must be a ShippingEdi() object
			die('shipping notice must be ShippingEdi() object');
		}
		$return = $this->mInterchangeHeader;
		$return .= $this->mGroupHeader;
		$this->mTransactionSegmentStart = count($this->mSegments);
		$this->NewTransControl();
		$seg = $this->NewSegment('ST');
		$seg[1] = '856';
		$seg[2] = $this->GetTransControl();
		$seg = $this->NewSegment('BSN');
		$seg[1] = '00';
		$seg[2] = $obj->mShipmentId;
		$seg[3] = gmdate('Ymd');
		$seg[4] = gmdate('Hi');
		if($this->mEdiVendor->mTypeCode == 'TVI') $seg[5] = '0004';
		if($this->mEdiVendor->mTypeCode == 'WMI')
		{
			// getting whether DST is on
			$tz = substr(date('T'), 0, 1);
			$tz .= substr(date('T'), 1, 1) == 'D' ? 'D' : 'T';
			$seg = $this->NewSegment('DTM');
			$seg[1] = '011';
			$seg[2] = gmdate('Ymd',strtotime($obj->mShipDateTime));
			$seg[3] = gmdate('Hi',strtotime($obj->mShipDateTime));
			$seg[4] = $tz;
			$seg = $this->NewSegment('HL');
			$seg[1] = '1';
			$seg[2] = '0';
			$seg[3] = 'O';
			$seg = $this->NewSegment('PRF');
			$seg[1] = $obj->mRetailerPO;
			$seg[7] = 'DS';
		}
		else if($this->mEdiVendor->mTypeCode == 'TVI')
		{
			$seg = $this->NewSegment('HL');
			$seg[1] = '1';
			$seg[3] = 'S';
		}
		$hl = 2;
		$lineitemId = 1;
		foreach($obj->mPackages as $pack)
		{
			if($this->mEdiVendor->mTypeCode == 'WMI')
			{
				$seg = $this->NewSegment('HL');
				$seg[1] = $hl;
				$seg[2] = '1';
				$seg[3] = 'P';
				$thispack = $hl;
				$hl++;
				$seg = $this->NewSegment('TD1');
				$seg[6] = 'G';
				$seg[7] = $pack->mWeight;
				$seg[8] = 'LB';
				$seg = $this->NewSegment('TD5');
				$seg[1] = 'O';
				$seg[2] = '94';
				$seg[3] = strlen($pack->mCarrierCode) < 2 ? str_pad($pack->mCarrierCode, 2, '0', STR_PAD_LEFT) : $pack->mCarrierCode;
				$seg = $this->NewSegment('MAN');
				$seg[1] = 'CP';
				$seg[2] = $pack->mCarrierCode == '20' || $pack->mCarrierCode == '67' ? 	substr(trim($pack->mPackageIdentifier), -15) : trim($pack->mPackageIdentifier);
				$seg = $this->NewSegment('MAN');
				$seg[1] = 'SM';
				$seg[2] = $pack->mCarrierCode == '20' || $pack->mCarrierCode == '67' ? 	substr(trim($pack->mPackageIdentifier), -15) : trim($pack->mPackageIdentifier);
				if($obj->mShipToStore == true)
				{
					$seg = $this->NewSegment('MAN');
					$seg[1] = 'GM';
					$seg[2] = $pack->mBarCode;
				}
				$seg = $this->NewSegment('SAC');
				$seg[1] = 'N';
				$seg[2] = 'G821';
				$seg[8] = $pack->mFreight;
				$seg[12] = $pack->mPMDPays ? '06' : '15';
			}
            else if($this->mEdiVendor->mTypeCode == 'TVI' && $tvi_runonce == true)
            {
                $seg = $this->NewSegment('TD5');
                $seg[2] = '94';
                $seg[3] = strlen($pack->mEdiCarrierCode) < 2 && is_numeric($pack->mEdiCarrierCode) ? str_pad($pack->mEdiCarrierCode, 2, '0', STR_PAD_LEFT) : $pack->mEdiCarrierCode;
                $seg = $this->NewSegment('REF');
                $seg[1] = 'VR';
                $seg[2] = $this->mEdiVendor->mVendorId;
                $seg = $this->NewSegment('DTM');
                $seg[1] = '011';
                $seg[2] = gmdate('Ymd',strtotime($obj->mShipDateTime));
                $seg = $this->NewSegment('N1');
                $seg[1] = 'ST';
                $seg[2] = $obj->mShipToUser->mName;
                $seg = $this->NewSegment('N3');
                $seg[1] = $obj->mShipToUser->mAddress1;
                $seg[2] = $obj->mShipToUser->mAddress2;
                $seg = $this->NewSegment('N4');
                $seg[1] = $obj->mShipToUser->mCity;
                $seg[2] = $obj->mShipToUser->mState;
                $seg[3] = $obj->mShipToUser->mPostal;
                $seg[4] = $obj->mShipToUser->mCountry;
                $seg = $this->NewSegment('HL');
                $seg[1] = '2';
                $seg[2] = '1';
                $seg[3] = 'O';
                $seg = $this->NewSegment('PRF');
                $seg[1] = $obj->mRetailerPO;
                $seg[3] = $obj->mRetailerPORevision;
                $seg[4] = date('Ymd', strtotime($obj->mRetailerPODate));
                $seg[5] = $obj->mPO + 1000;
                $seg[7] = 'DS';
                $hl = 3;
                $tvi_runonce = false;
            }
			// now output the items
			foreach($pack->mItems as $item)
			{
				if($this->mEdiVendor->mTypeCode == 'WMI')
				{
					$seg = $this->NewSegment('HL');
					$seg[1] = $hl;
					$seg[2] = $thispack;
					$seg[3] = 'I';
					$hl++;
					$seg = $this->NewSegment('LIN');
					$seg[1] = $lineitemId;
					$seg[2] = 'PL';
					$seg[3] = $item->mPOLineNumber;
					if(!is_null($item->mStoreNumber) && $item->mStoreNumber != 0)
					{
						$seg[4] = 'SN';
						$seg[5] = $item->mStoreNumber;
					}
					$seg = $this->NewSegment('SN1');
					$seg[1] = $lineitemId;
					$seg[2] = $item->mQtyShipped;
					$seg[3] = 'EA';
					if(!is_null($item->mHandlingCost) && $item->mHandlingCost != '0.00')
					{
						$seg = $this->NewSegment('SAC');
						$seg[1] = 'N';
						$seg[2] = 'D500';
						$seg[8] = $this->mHandlingCost;
						$seg[12] = '06';
					}
					if(!is_null($item->mGiftWrappingCost) && $item->mGiftWrappingCost != 	'0.00')
					{
						$seg = $this->NewSegment('SAC');
						$seg[1] = 'N';
						$seg[2] = 'H151';
						$seg[8] = $this->mGiftWrappingCost;
						$seg[12] = '06';
						$seg[15] = 'VGW';
					}
					if(!is_null($item->mGiftTagCost) && $item->mGiftTagCost != '0.00')
					{
						$seg = $this->NewSegment('SAC');
						$seg[1] = 'N';
						$seg[2] = 'H151';
						$seg[8] = $this->mGiftTagCost;
						$seg[12] = '06';
						$seg[15] = 'VGT';
					}
					if(!is_null($item->mGiftMessageCost) && $item->mGiftMessageCost != 	'0.00')
					{
						$seg = $this->NewSegment('SAC');
						$seg[1] = 'N';
						$seg[2] = 'H151';
						$seg[8] = $this->mGiftMessageCost;
						$seg[12] = '06';
						$seg[15] = 'VGM';
					}
				}
				else
				if($this->mEdiVendor->mTypeCode == 'TVI')
				{
					// TODO insert the order's defined line # here ish
					$qty_shipped_sum = 0;
                    $seg = $this->NewSegment('HL');
                    $seg[1] = $hl;
                    $seg[2] = '2';
                    $seg[3] = 'I';
					$seg = $this->NewSegment('LIN');
					$seg[1] = $item->mPOLineNumber;
					$seg[2] = 'SK';
					$seg[3] = $item->mSKU;
					$seg[4] = 'IN';
					$seg[5] = $item->mRetailerPartNumber;
					$seg[6] = 'UP';
					$seg[7] = $item->mUPC;
					$seg = $this->NewSegment('SN1');
					$seg[2] = $item->mQtyShipped;
					$seg[3] = 'EA';
					$seg[5] = $item->mQtyOrdered;
					$seg[6] = 'EA';
					$seg = $this->NewSegment('PID');
					$seg[1] = 'F';
					$seg[5] = $item->mDescription;
                    $hl++;
					$seg = $this->NewSegment('SAC');
					$seg[1] = 'C';
					$seg[2] = 'G830';
					$seg[5] = $pack->mFreight * 100; // to remove decimal
                    $seg = $this->NewSegment('MAN');
                    $seg[1] = 'ZZ';
                    $seg[2] = $pack->mPackageIdentifier;
				}
				$lineitemId++;
			}
			$lineitemId = 1;
		}
		$seg = $this->NewSegment('CTT');
		$seg[1] = $hl - 1;
		$seg = $this->NewSegment('SE');
		$seg[1] = count($this->mSegments) - $this->mTransactionSegmentStart;
		$seg[2] = $this->GetTransControl();
		$this->mTransactionSegmentEnd = count($this->mSegments);
		$this->MakeTransactionSegments();
		$this->mEdiTypeId = '856SH';
		parent::EndAll();
		$return .= $this->mTransaction;
		$return .= $this->mGroupFooter;		
		$return .= $this->mInterchangeFooter;
		if($obj->mReject == true) $this->mStatus = 0;
		$this->mEdiOutput = $return;
		return $this;
	}
}

$EdiVendor = new EdiVendor();
$EdiVendor->LoadDefault();

$PMDVendor = EdiVendor::GetPMD();
?>