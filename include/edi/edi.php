<?php
// edi.php
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
 * EDI Read/Write Library
 *
 * @package    Operations
 * @subpackage EDI
 * @copyright  Copyright (c) 2004-2009 Power Marketing Direct, Inc. (http://www.pmdfurniture.com)
 */
//require_once('./../lib_objects/db.php'); // for getting db; hard coded for now
if (!function_exists('getconfig'))
    require_once(dirname(__FILE__).'/../../database.php');
require_once('bo_edi.php');
require_once('do_edi.php');
if (!function_exists('submitOrder'))
    require_once(dirname(__FILE__).'/../../inc_content.php');


class Edi
{
	private $mDoEdi;
	public $mEdiObject;
	public $mParser;
	public $mSender;
	public $mReceiver;
	public $mDateTime;
	public $mSent;
	public $mReceived;
	public $mEdiVersion;
	public $mEdiStandard;
	public $mInterchangeControlNumber;
	public $mSendAcknowledgement;
	public $mIsTest;
	public $mEdiData;
	public $mReject;
	public $mConfirmed;
	public $mProcessed;
	public $mRejected;
	public $mPOId;
	public $mOrderStatus;

	//const PMD_ID = '829014849';
	//const PMD_QUALIFIER = '01';

	public $mEdiVendor;

	/**
	 * The Instance var to keep track of the Edi class singleton instance
	 * @var mInstance
	 */
	private static $mInstance;
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return Edi An instance of this class.
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}


	/**
	 * Parses an Edi file just enough to find out what type it is, then sends the data
	 * to the proper class
	 * @return An instance of the right Edi class.
	 */
	function __construct($data = null)
	{
		global $EdiVendor;
		$this->mEdiVendor = &$EdiVendor;
		$this->mDoEdi = DoEdi::getInstance();
		// get the walmart form id & user id
		// figure out which vendor we're dealing with first...
		if(!is_null($data))
		{
			$newedi = new EdiData();
			$newedi->Load($data);
			$newedi->Archive();
			$this->mEdiData = $newedi;
			$this->Load($newedi);
		}
		$this->mOrderStatus = array();
	}

	public function LoadFromFile($filename, $ponum = null)
	{
		$archive_folder = DoEdi::GetArchiveFolder($filename);
		$this->mEdiVendor->LoadFromFilename($filename);
		if(is_null($archive_folder))
		{
			$vendorpos = strpos($filename, $this->mEdiVendor->mVendorId);
			$vendorlen = strlen($this->mEdiVendor->mVendorId);
			$dateloc = $vendorpos + $vendorlen + 1;
			$archive_folder = substr($filename, $dateloc, 6);
		}
                if (!file_exists($this->mEdiVendor->mVendorPath."archive/$archive_folder/".$filename)) {
                    throw new Exception("Cannot find EDI file: archive/".$archive_folder."/".$filename);
                }
                $getdata = file_get_contents($this->mEdiVendor->mVendorPath."archive/$archive_folder/".$filename);
		$newdata = new EdiData();
		$readdata = array('filename' => $filename, 'data' => $getdata);
		$newdata->Load($readdata);
		$newdata->mSeparators->GetFromData($newdata);
		$this->Load($newdata, $ponum);
	}
	
	public function LoadFromPO($po)
	{
		$ediname = $this->mDoEdi->GetEdiFromPO($po);
		$this->LoadFromFile($ediname, $po);
	}
	
	public function Load(&$data, $ponum = null)
	{
		$data->mSeparators->GetFromData($data);
		$this->mEdiData = $data;
		$status = $this->mDoEdi->AddToDb($data);
		$this->mConfirmed = $status['confirmed'];
		$this->mProcessed = $status['processed'];
		$this->mRejected = $status['rejected'];
		$pos = split(';', $status['po_id']);
		if($pos[0]=='' || $pos[0]=='0') array_shift($pos);
		if(count($pos)==1) $ponum = $pos[0];
		$this->mPOId = !is_null($ponum) ? $ponum : $status['po_id'];
		$this->mSent = $status['sent'];
		$this->mReceived = $status['received'];
		$this->mParser = new EdiParser($data);
		$temp = $this->mParser->GetSplitData();
		$header = $this->mParser->GetInterchangeHeaderData();
		$this->mSender = $header['sender'];
		$this->mReceiver = $header['receiver'];
		// now we load the EdiVendor
		$this->mEdiVendor->LoadFromID($this->mSender);
		if($this->mEdiVendor->mVendorName == 'pmd')
		{
			// PMD sent, so the vendor in question is the receiver
			$this->mEdiVendor->LoadFromID($this->mReceiver);
		}
		$this->mDateTime = $header['datetime'];
		$this->mEdiStandard = $header['standard'];
		$this->mEdiVersion = $header['version'];
		$this->mInterchangeControlNumber = $header['interchangeno'];
		$this->mSendAcknowledgement = ($header['ackreq'] != '' ? true : false);
		$this->mIsTest = ($header['testing'] == 'T' ? true : false);
		unset($header);
		DoEdi::UpdateDb('interchange', $this->mEdiData, $this->mInterchangeControlNumber);
		$docs = array_keys($temp, 'GS');
		foreach($docs as $hey) // should only be one time through the loop
		{
			switch($temp[$hey + 1])
			{
				case 'PO': // Purchase Orders
					$po = new EdiPO();
					$returns = $po->Load($data);
					break;
				case 'PR': // Line Acceptance & Line Cancel Acceptance
					$pr = new EdiPR();
					$returns = $pr->Load($data);
					break;
				case 'PC': // Cancel Order
					$pc = new EdiPC();
					$returns = $pc->Load($data);
					break;
				case 'FA': // Confirmation
					$fa = new EdiFA();
					$returns = $fa->Load($data);
					break;
				case 'AG': // Error File
					$ag = new EdiAG();
					$returns = $ag->Load($data);
					break;
				case 'CA': // PO Cancellation Acknowledgment
					$ca = new EdiCA();
					$returns = $ca->Load($data);
					break;
				case 'IB': // Inventory Inquiry
					$ib = new EdiIB();
					$returns = $ib->Load($data);
					break;
			}
		}
		$this->mEdiObject = $returns;
		$footer = $this->mEdiObject->mParser->GetInterchangeFooterData();
		if($footer['total_groups'] != count($this->mEdiObject) || $footer['interchange_control_number2'] != $this->mInterchangeControlNumber)
		{
			$this->mReject = true;
		}
		if($this->mParser->mReject == true)
		{
			$this->mReject = true;
		}
	}
	
	public function Confirm()
	{
		if(!$this->mConfirmed)
		{
			DoEdi::UpdateDb('received', $this->mEdiData, date('Y-m-d H:i:s'));
			$edi = new EdiFA();
			$confirm = $edi->Build($this);
			// confirm should be the output EDI file
			DoEdi::UpdateDb('confirmed', $this->mEdiData);
			if($confirm != false)
			{
				if(is_array($confirm) && isset($confirm['reject']))
				{
					DoEdi::UpdateDb('rejected', $this->mEdiData);
				}
				else if($this->mEdiVendor->mSendAck == true)
				{
					$edi->Send();
				}
			}
			return $confirm;
		}
		else
		{
			return $this->mConfirmed;
		}
	}
	
	public function Process()
	{
		if(!$this->mProcessed)
		{
			$output = '';
			switch($this->mEdiObject->mEdiType)
			{
				case 'PO': // order
					// check the items for stock
					$acktype = new EdiPOItemsByPOStatus();
					foreach($this->mEdiObject->mTransactions as $trans)
					{
						foreach($trans->mItems as $thisitem)
						{
							// determine if we're in a Walmart one
							$is_walmart = false; // default to no
							if(substr($this->mEdiVendor->mVendorName, 0, 7) == 'walmart') $is_walmart = true;
							$gotstatus = $thisitem->GetItemStatus($is_walmart);
							if($is_walmart)
							{
								$status = $gotstatus;
								unset($gotstatus);
							}
							else
							{
								// get the status from the object data
								if(is_array($gotstatus))
								{
									$status['statuscode'] = ($gotstatus['avail'] >= $thisitem->mQuantity || $gotstatus['avail'] == -1) ? 'AC' : 'NA';
									$status['numavail'] = $gotstatus['avail'];
									unset($gotstatus);
								}
								else
								{
									// if non-array (i.e. bad data), go ahead & pass the digit
									$status = $gotstatus;
									unset($gotstatus);
								}
							}
							$pricecheck = $this->mParser->PriceCheck($thisitem->mUPC, $thisitem->mRetailerUnitCost);
							if($pricecheck != 1 && $pricecheck != $thisitem->mRetailerUnitCost)
							{
								// pricecheck failed, do something
								// reset the unit cost to the actual one (which probably should be passed in as a return option)
								// send email to folks w/ that info
								$oldprice = $thisitem->mRetailerUnitCost;
								$thisitem->mRetailerUnitCost = $pricecheck != '' && $pricecheck != 0 ? $pricecheck : $thisitem->mRetailerUnitCost;
								sendmail('William Lightning <will@retailservicesystems.com>, Amy Bowen <amy@retailservicesystems.com>, Gary Davis <gary@retailservicesystems.com>',
								ucfirst($this->mEdiVendor->mVendorName)." EDI Order ".$trans->mPONumber." SKU ".$thisitem->mSKU." Mispriced",
								"The unit cost to the retailer for item ".$thisitem->mSKU." (UPC ".$thisitem->mSKU.") was incorrect.\n
								The price sent was $".$oldprice.".\nThe price in the database is $".$pricecheck.".\nPlease inform the vendor (".ucfirst($this->mEdiVendor->mVendorName).").");
							}
							if(!is_array($status))
							{
								if($status == -1)
								// unrecognized item
								{
									$ack = $acktype->Add($trans->mPONumber, $thisitem->mLineNumber);
									$ack->mStatus = 'R2';
									$this->mOrderStatus[$trans->mPONumber][$thisitem->mLineNumber] = false;
								}
								else if($status == 0)
								{
									$ack = $acktype->Add($trans->mPONumber, $thisitem->mLineNumber);
									$ack->mStatus = 'R1';
									$this->mOrderStatus[$trans-mPONumber][$thisitem->mLineNumber] = false;
								}
							
							}
							else
							{
								switch($status['statuscode'])
								{
									case 'AC':
									case 'AA':
									case 'JT':
									case 'RO':
										$ack = $acktype->Add($trans->mPONumber, $thisitem->mLineNumber);
										$ack->mStatus = 'IA';
										$this->mOrderStatus[$trans->mPONumber][$thisitem->mLineNumber] = true;
										break;
									case 'NA':
										$ack = $acktype->Add($trans->mPONumber, $thisitem->mLineNumber);
										$ack->mStatus = 'R4';
										$ack->mQuantity = $status['numavail'];
										$this->mOrderStatus[$trans->mPONumber][$thisitem->mLineNumber] = false;
										break;
									case 'DT':
										$ack = $acktype->Add($trans->mPONumber, $thisitem->mLineNumber);
										$ack->mStatus = 'R1';
										$this->mOrderStatus[$trans->mPONumber][$thisitem->mLineNumber] = false;
								}
							}
							// reset status
							unset($status);
						}
					}
					$processed = new EdiPR();
					$output['data'] = $processed->Make($this, $acktype);
					if(!is_object($output['data']))
					{
						if($output['data'] == -1 || $output['data']['sendAG'] == true)
						{
							unset($processed);
							$processed= new EdiAG();
							$output['data'] = $processed->Make($this);
						}
					}
					else
					{
						foreach($this->mEdiObject->mTransactions as $trans)
						{
							$te = array_unique($this->mOrderStatus[$trans->mPONumber]);
							$test = array_values($te);
							if($this->mEdiVendor->mTypeCode == 'WMI' && (count($test) != 1 || $test[0] == false)) continue; // if the vendor is Walmart and one or more items are kicked, don't place the order at all
							if(count($test) == 1 && $test[0] == false) continue; // don't place order if all items have been kicked
							$neworder = new EdiOrder();
							$poid = $neworder->Insert($trans, $acktype);
							if(is_array($poid))
							{
								// prep things first
								if(is_array($poid['items']))
								{
									foreach($poid['items'] as $thisitem)
									{
										$itemids[] = $thisitem['orig_item_id'];
										$partnos[] = $thisitem['partno'];
										$stocks[] = $thisitem['stock'];
									}
									foreach($stocks as $thisstock)
									{
										$sql = "SELECT name FROM stock_status WHERE id = '$thisstock'";
										$que = mysql_query($sql);
										checkDBerror($sql);
										$ret = mysql_fetch_assoc($que);
										$stocknames[] = $ret['name'];
									}
								}
								$outtable = "Item\tPart\tCode\tStatus\n";
								for($i=0; $i<count($itemids); $i++)
								{
									$outtable .= $itemids[$i]."\t".$partnos[$i]."\t".$stocks[$i]."\t".$stocknames[$i]."\n";
								}
								sendmail('William Lightning <will@retailservicesystems.com>, Amy Bowen <amy@retailservicesystems.com>, Gary Davis <gary@retailservicesystems.com>', ucfirst($this->mEdiVendor->mVendorName)." EDI Order ".$neworder->mRetailPONumber." Not Placed Due to Out Of Stock Status", ucfirst($this->mEdiVendor->mVendorName)." EDI Order ".$neworder->mRetailPONumber." did not get placed correctly because of an out-of-stock status.\n\nBelow is a listing of the item(s) in question and their status(es):\n\n\n".$outtable);
							}
							else
							{
								$disppo = $poid + 1000;
								sendmail('Amy Bowen <amy@retailservicesystems.com>, William Lightning <will@retailservicesystems.com>, Gary Davis <gary@retailservicesystems.com>', ucfirst($this->mEdiVendor->mVendorName)." EDI Order {$neworder->mRetailPONumber} Accepted", ucfirst($this->mEdiVendor->mVendorName)." EDI-based Order {$neworder->mRetailPONumber} has been accepted.\n\nThe internal order number is $disppo.\n\n<a href='https://login.retailservicesystems.com/admin/report-orders-details.php?po=$disppo'>Click here to begin processing.</a>");
								DoEdi::AppendDb('po_id', $this->mEdiData, $poid);
								DoEdi::AppendDb('retailer_po', $this->mEdiData, $neworder->mRetailPONumber);
								$output['process_object'][] = $neworder;
								unset($neworder);
							}
						}
						$this->mOrderStatus = array();
					}
					break;
				case 'FA': // acknowledgement
					echo "FA process";
					//print_r($this->mEdiObject);
					// TODO finish building this
					die();
					// first we need to ID which item is being acknowledged
					foreach($this->mEdiObject->mTransactionData as $trans)
					{
						// get the group number that we're talking about, from which we get the file ID of the acknowledged file
						$target_filename = $this->mDoEdi->GetFilenameFromGroupId($trans['group_control_number']);
						
					}
					break;
				case 'PC': // cancel order
					$newcancel = new EdiCancel();
					$newcancel->Load($this);
					$newcancel->Process();
					unset($newcancel);
					break;
			}
			DoEdi::UpdateDb('processed', $this->mEdiData);
			if(isset($output)) return $output;
		}
		else
		{
			switch($this->mEdiObject->mEdiType)
			{
				case 'PO': // order
					$neworder = new EdiOrder();
					$neworder->LoadFromPO($this);
					return $neworder;
					break;
				case 'FA': // acknowledgement
				
					break;
				case 'PC': // cancel order
					
					break;
			}
			return !$this->mRejected;	
		}
	}
}


class EdiBuilder
{
	protected $mSender;
	protected $mSenderQualId;
	protected $mReceiver;
	protected $mReceiverQualId;
	protected $mInterchangeHeader;
	protected $mGroupHeader;
	protected $mTransaction;
	protected $mGroupFooter;
	protected $mInterchangeFooter;
	protected $mSegments;
	protected $mTransactionSegmentStart;
	protected $mTransactionSegmentEnd;
	protected $mAckNumber;
	protected $mStatus = 1; // default to accept
	protected $mInterchangeNumber;
	protected $mTransactionControlNumber;
	protected $mSegmentCount;
	protected $mEdiTypeId;
	protected $mEdiVendor;
	protected $mEdiOutput;
	protected $mDoEdiBuilder;

	function __construct()
	{
		global $EdiVendor;
		$this->mDoEdiBuilder = new DoEdiBuilder();
		$this->mEdiVendor = &$EdiVendor;
		$this->mInterchangeHeader = $this->MakeInterchangeHeader();
		$this->mGroupHeader = array();
		$this->mTransaction = array();
		$this->mGroupFooter = array();
		$this->mSegmentCount = 0;
		$this->mSegments = array();
	}
	
	protected function NewElements($count = 1)
	{
		$return = '';
		for($i=1; $i<=$count; $i++)
		{
			$return .= EdiSeparators::NEWELEMENT;
		}
		return $return;
	}
	
	protected function NewSegment($name = null)
	{
		if(!is_null($name)) $this->mSegments[] = new EdiSegment($name);
		$this->mSegmentCount++;
		return $this->mSegments[$this->mSegmentCount - 1];
	}
	
	protected function GetTransControl()
	{
		return $this->mTransactionControlNumber;
	}
	
	protected function NewTransControl()
	{
		$this->mTransactionControlNumber = $this->mDoEdiBuilder->GetTransactionNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
	}
	
	protected function StartAll($type = 'FA')
	{
		if($type == 'FA' && !$this->mEdiVendor->mSendAck) return false; // some vendors do not want an FA sent
		switch($type)
		{
			case 'FA':
				$this->mAckNumber = $this->mDoEdiBuilder->GetAcknowledgeNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
				break;
			case 'PR':
			case 'CA':
				$this->mAckNumber = $this->mDoEdiBuilder->GetLineNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
				break;
			case 'SH':
				$this->mAckNumber = $this->mDoEdiBuilder->GetShipmentNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
				break;
			case 'IB':
				$this->mAckNumber = $this->mDoEdiBuilder->GetInventoryNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
				break;
			case 'AG':
				$this->mAckNumber = $this->mDoEdiBuilder->GetErrorNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
				break;
                        case 'PO':
                                $this->mAckNumber = $this->mDoEdiBuilder->GetPurchaseOrderNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
                                break;
		}
		$newseg = $this->NewSegment('GS');
		$newseg[1] = $type;
		$newseg[2] = $this->mSender;
		$newseg[3] = $this->mReceiver;
		$newseg[4] = gmdate('Ymd');
		$newseg[5] = gmdate('Hi');
		$newseg[6] = $this->mAckNumber;
		$newseg[7] = 'X';
		$newseg[8] = '004010';
		$this->mGroupHeader = $newseg->Make();
		$this->mTransactionSegmentStart = count($this->mSegments);
	}
	
	protected function MakeTransactionSegments()
	{
		$output = '';
		for($i = $this->mTransactionSegmentStart; $i < $this->mTransactionSegmentEnd; $i++)
		{
			$output .= $this->mSegments[$i]->Make();
		}
		$this->mTransaction = $output;
	}
	
	protected function EndAll()
	{
		$newseg = $this->NewSegment('GE');
		$newseg[1] = count($this->mTransaction);
		$newseg[2] = $this->mAckNumber;
		$this->mGroupFooter = $newseg->Make();
		$newseg = $this->NewSegment('IEA');
		$newseg[1] = count($this->mGroupHeader);
		$newseg[2] = $this->mInterchangeNumber;
		$this->mInterchangeFooter = $newseg->Make();
	}

	protected function MakeInterchangeHeader()
	{
		$this->mInterchangeNumber = $this->mDoEdiBuilder->GetInterchangeNumber(strtolower(substr($this->mEdiVendor->mTypeCode, 2)));
		$this->mSender = ''; // these values are determined by who the sender or receiver are
		$this->mSenderQualId = '';
		$this->mSender = $this->mDoEdiBuilder->GetPMDId();
		$this->mSenderQualId = $this->mDoEdiBuilder->GetPMDQualifier();
		$this->mReceiver = $this->mEdiVendor->mEdiId;
		$this->mReceiverQualId = $this->mEdiVendor->mEdiQualifier;
		$newseg = $this->NewSegment('ISA');
		$newseg[1] = '00';
		$newseg[2] = '          ';
		$newseg[3] = '00';
		$newseg[4] = '          ';
		$newseg[5] = $this->mSenderQualId;
		$newseg[6] = str_pad($this->mSender, 15);
		$newseg[7] = $this->mReceiverQualId;
		$newseg[8] = str_pad($this->mReceiver, 15);
		$newseg[9] = gmdate('ymd');
		$newseg[10] = gmdate('Hi');
		$newseg[11] = 'U';
		$newseg[12] = '00401';
		$newseg[13] = $this->mInterchangeNumber;
		$newseg[14] = '0';
		$newseg[15] = $this->mEdiVendor->mTesting == true ? 'T' : 'P';
		$newseg[16] = ''; // for final element ender
		$newseg->AddSubSegment();
		return $newseg->Make();
	}
	
	public function GetOutput()
	{
		return $this->mEdiOutput;
	}
}


class EdiData
{
	public $mData;
	public $mSeparators;
	public $mFilename;
	public $mTypeCode;
	private static $mIsFixed;
	public $mEdiVendor;
	
	function __construct()
	{
		global $EdiVendor;
		$this->mIsFixed = false;
		$this->mEdiVendor = &$EdiVendor;
	}
	
	public function Load($data)
	{
		$this->mData = $data['data'];
		$this->mFilename = $data['filename'];
		$this->mTypeCode = 'WMI'; // default to walmart typecode
		$this->SetTypeCode();
		$this->mSeparators = new EdiSeparators();
		$test = $this->mSeparators->GetFromData($this);
		if(!$test) return 0;
	}
	
	public function LoadFromEdiObject($obj)
	{
		$this->mData = $obj->GetOutput();
		$this->SetTypeCode();
		$this->FixFilename();
		$this->mSeparators = new EdiSeparators();
		$test = $this->mSeparators->GetFromData($this);
		if(!$test) return 0;
	}
	
	/**
	 * Creates a filename for an EdiData object
	 * @return (string)Filename
	 */
	public function MakeFilename($type)
	{
		$filename_stub = $this->mTypeCode."_".$type.'_'.$this->mEdiVendor->mVendorId.
		"_".gmdate('Ymd').'_'.gmdate('His').'_'.str_pad(mt_rand(1, 999999), 6, "0", STR_PAD_LEFT).".edi";
		return $filename_stub;
	}
	
	/**
	 * Set type code based upon sender/receiver
	 * @return (string)Typecode
	 */
	public function SetTypeCode($data = null)
	{
		if(is_null($data)) $data = $this;
		$sender_qualifier = substr($data->mData, 32, 2);
		$sender_id = trim(substr($data->mData, 35, 15));
		$receiver_qualifier = substr($data->mData, 51, 2);
		$receiver_id = trim(substr($data->mData, 54, 15));
		// ID which vendor we're talking about/to
		// try sender first (most likely)
		$found = $this->mEdiVendor->LoadFromId($sender_id);
		if($found == 1) // vendor located
		{
			$this->mTypeCode = $this->mEdiVendor->mTypeCode;
		}
		if($this->mTypeCode != '') return; // bail from the function if the non-PMD typecode has been found
		$found = $this->mEdiVendor->LoadFromId($receiver_id);
		if($found == 1) // vendor located
		{
			$this->mTypeCode = $this->mEdiVendor->mTypeCode;
		}
	}

	public function FixFilename()
	{
		// this function edits the filename to match the naming convention
		// read temporarily into the file....i so hope this doesn't break things
		// only do this once, hence check for IsFixed
		if(!$this->mIsFixed)
		{
			$parse = new EdiParser($this);
			$temp = $parse->GetSplitData();
			$docs = array_keys($temp, 'GS');
			foreach($docs as $hey) // should only be one time through the loop
			{
				switch($temp[$hey + 1])
				{
					case 'PO': // 850 Purchase Orders
						if(substr($this->mFilename, 4, 3) != '850' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('850PO');
						}
						break;
					case 'PR': // 855 Line Acceptance & Line Cancel Acceptance
						if(substr($this->mFilename, 4, 3) != '855' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('855PR');
						}
						break;
					case 'CA': // 865 PO Cancel Acknowledgement
						if(substr($this->mFilename, 4, 3) != '865' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('865CA');
						}
						break;
					case 'IB': // Inventory Inquiry
						if(substr($this->mFilename, 4, 3) != '846' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('846IB');
						}
						break;
					case 'PC': // 860 Cancel Order
						if(substr($this->mFilename, 4, 3) != '860' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('860PC');
						}
						break;
					case 'FA': // 997 Confirmation
						if(substr($this->mFilename, 4, 3) != '997' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('997FA');
						}
						break;
					case 'AG': // 824 Error File
						if(substr($this->mFilename, 4, 3) != '824' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('824AG');
						}
						break;
					case 'SH': // 856 Shipment Notification
						if(substr($this->mFilename, 4, 3) != '856' || substr($this->mFilename, -3) != 'edi')
						{
							$this->mFilename = $this->MakeFilename('856SH');
						}
						break;
				}
			}
		}
		$this->mIsFixed = true;
	}
	
	public function Send()
	{
		global $as2_testing, $config_as2copy_cmd, $config_as2test_cmd;
		if($as2_testing)
		{
			global $as2_testing_partnername;
			$as2name = $as2_testing_partnername;
		}
		else
		{
			$as2name = $this->mEdiVendor->mAS2SenderName;
		}
		$this->FixFilename();
		$file = fopen($this->mEdiVendor->mVendorPath.'msg_tosend/'.$this->mFilename, 'w');
		$go = fwrite($file, $this->mData);
		chmod($this->mEdiVendor->mVendorPath.'msg_tosend/'.$this->mFilename, 0666);
		fclose($file);
		$waserror = false;
		// define proc_open array
		$procarray = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("file", $this->mEdiVendor->mVendorPath.'msg_tosend/error.log', 'a'));
		$cwd = $this->mEdiVendor->mVendorPath;
		$getinfo = proc_open($config_as2test_cmd, $procarray, $pipes, $cwd);
		if(is_resource($getinfo))
		{
			$info = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			proc_close($getinfo);
                        // TODO: Turn this into an actual logging process
			// mail('Will Lightning <kassah@gmail.com>', 'PMD POSIX User Info', "$info");
		} else proc_close($getinfo);
		$thecommand = $config_as2copy_cmd.' '.$as2name.' '.$this->mEdiVendor->mVendorPath.'msg_tosend/'.$this->mFilename;
		$success = proc_open($thecommand, $procarray, $pipes, $cwd);
		// read from the stdout pipe, should just be an echo of the command above
		if(is_resource($success))
		{
			$command = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			$command = rtrim($command);
			// $command should be the command passed in (i.e. /var/www/vhosts/pmddealer.com/webbin/as2copy as2_partnername edipath/msg_tosend/filename
			if($command !== $thecommand)
			{
				// set waserror = true, doesn't remove msg_tosend
				$waserror = true;
			}
                        // TODO: Turn this into an actual logging process
			// mail('Will Lightning <kassah@gmail.com>', 'PMD POSIX Data Set', "Name of the file to send: ".$this->mFilename."\nHere is the set of posix_get* functions...\nposix_getegid() = ".posix_getegid()."\nposix_getgid() = ".posix_getgid()."\nposix_geteuid = ".posix_geteuid()."\nposix_getuid() = ".posix_getuid()."\nposix_getgroups() = ".print_r(posix_getgroups(), true));

		}
		else
		{
			// there was a problem
			// copy the "resource" so we can output it to the error email
			$success_copy = $success;
			proc_close($success); // close the command "resource" so we can write to the error.log
			$waserror = true;
			$errorwrite = fopen($this->mEdiVendor->mVendorPath.'msg_tosend/error.log', 'a');
			$go = fwrite($errorwrite, "\nThere was an error in sending an EDI file, here is the passed-in command:\n\n".$thecommand."\n\nHere is the resource output:".print_r($success_copy, true));
			fclose($errorwrite);
			// sending an email to a personal account because I want to keep in the loop
			mail('Will Lightning <kassah@gmail.com>, William Lightning <will@retailservicesystems.com>', 'RSS EDI Sending Error', "\nThere was an error in sending an EDI file, here is the passed-in command:\n\n".$thecommand."\n\nHere is the set of variables...\nprocarray:".print_r($procarray, true)."\npipes:".print_r($pipes, true)."\ncwd:".print_r($cwd, true)."\n\nHere is the resource output:".print_r($success_copy, true));
		}
		
		if(is_resource($success)) $check2 = proc_close($success);
		
		if(!$waserror)
		{
			$success2 = popen('rm '.$this->mEdiVendor->mVendorPath.'msg_tosend/'.$this->mFilename.' -rf', 'r');
			pclose($success2);
		}
		//$check .= fread($success, 8192);
		//if($check2 != 0) die("Problem in sending file: check = $check; check2 = $check2");
		$this->Archive();
	}
	
	public function Archive()
	{
		$this->FixFilename();
		if(!isset($this->mFilename) || $this->mFilename == '') return;
		if(!file_exists($this->mEdiVendor->mVendorPath.'archive/'.date('Ym')))
		{
			mkdir($this->mEdiVendor->mVendorPath.'archive/'.date('Ym'), 0777, true);
		}
		$file = fopen($this->mEdiVendor->mVendorPath.'archive/'.date('Ym').'/'.$this->mFilename, 'w');
		$go = fwrite($file, $this->mData);
		fclose($file);
		chmod($this->mEdiVendor->mVendorPath.'archive/'.date('Ym').'/'.$this->mFilename, 0666);
		DoEdi::AddToDb($this);
		DoEdi::UpdateDb('archive_folder', $this, date('Ym'));
	}
}


class EdiDate
{
	public $mType;
	public $mData;
	
	function __toString()
	{
		return $this->mData;
	}
}


class EdiDates implements arrayaccess
{
	private $mDates;
	
	function __construct()
	{
		$this->mDates = array();	
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mDates[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mDates[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mDates[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mDates[$offset]) ? $this->mDates[$offset] : null;
	}
	
	public function Add($type, $number)
	{
		$this->mDates[$type] = new EdiDate();
		$ret = &end($this->mDates);
		$ret->mType = $type;
		$ret->mData = $number;
		reset($this->mDates);
		return $ret;
	}
}



class EdiGroup
{
	public $mType; // PO, PC, AG, etc.
	public $mDateTime;
	public $mControlHeader;
	public $mControlFooter;
	public $mAgency;
	public $mVersion;
	public $mTransactions;
	
	public function ValidateControl()
	{
		return $this->mControlHeader === $this->mControlFooter;
	}
}


class EdiGroups implements Iterator
{
	private $mGroups;
	private $mPos;
	
	function __construct()
	{
		$this->mGroups = array();
		$this->mPos = 0;
	}
	
	function rewind()
	{
		$this->mPos = 0;
	}

	function current()
	{
		return $this->mGroups[$this->mPos];
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
		return isset($this->mGroups[$this->mPos]);
	}

	public function Add()
	{
		$this->mGroups[] = new EdiGroup();
		$ret = &end($this->mGroups);
		reset($this->mGroups);
		return $ret;
	}
	
	public function GetLast()
	{
		$ret = &end($this->mGroups);
		reset($this->mGroups);
		return $ret;
	}
}


class EdiInventoryItem
{
	public $mId;
	public $mSKU; // known as partno internally
	public $mDescription;
	public $mPrice;
	public $mStockCode;
	public $mStockDate;
	public $mStockDay;
	public $mWeight;
	public $mAllocated;
	public $mAvailable;
	public $mRetailerItemID; // grabbed from sku field
    public $mASIN; // Amazon ID
	public $mUPC;
}

class EdiItem
{
	private $mDoEdiItem;
	public $mLineNumber;
	public $mItemId;
	public $mQuantity;
	public $mRetailerUnitCost;
	public $mRetailerPartNumber;
	public $mUPC;
	public $mSKU;
	public $mPartNumber;
	public $mDescription;
	public $mChargeAmount;
	public $mChargeMethod;
	public $mShippingAmount;
	public $mShippingMethod;
	public $mAllowanceAmount;
	public $mAllowanceMethod;
	public $mAllowanceDescription;
	public $mServicesAmount;
	public $mServicesMethod;
	public $mServicesDescription;
	public $mShipping;
	public $mTaxes;
	public $mTaxesMethod;
	
	function __construct()
	{
		$this->mDoEdiItem = DoEdiItem::getInstance();
	}
	
	public function GetItemIdFromRetailerPartNumber()
	{
		$this->mItemId = $this->mDoEdiItem->GetItemIdFromRetailerPartNumber($this);
	}
	
	public function GetItemId()
	{
		$this->mDoEdiItem->GetItemId($this);
	}
	
	public function ReturnOrigItemId()
	{
		return $this->mDoEdiItem->ReturnOrigItemId($this);
	}
	
	public function GetItemStatus($walmart)
	{
		return $this->mDoEdiItem->GetItemStatus($this, $walmart);
	}
	
	public function GetItemIdFromSKU()
	{
		return $this->mDoEdiItem->GetItemIdFromSKU($this->mSKU);
	}
}


class EdiItems implements Iterator
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
		$this->mItems[] = new EdiItem();
		$ret = &end($this->mItems);
		$ret->mLineNumber = $line;
		reset($this->mItems);
		return $ret;
	}
}


class EdiMessage
{
	public $mType;
	public $mData;
	
	public function ToString()
	{
		return $this->mData;
	}
}


class EdiMessages implements arrayaccess
{
	private $mMsgs;
	private $mTypes;
	private $mPos;
	
	function __construct()
	{
		$this->mMsgs = array();
		$this->mTypes = array();
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mMsgs[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mMsgs[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mMsgs[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mMsgs[$offset]) ? $this->mMsgs[$offset] : null;
	}
	
	public function Add($type, $msg)
	{
		$this->mMsgs[$type] = new EdiMessage();
		$ret = &end($this->mMsgs);
		$ret->mType = $type;
		$ret->mData = $msg;
		if(!in_array($type, $this->mTypes)) $this->mTypes[] = $type;
		reset($this->mMsgs);
		return $ret;
	}
	
	public function Append($type, $msg)
	{
		if(isset($this->mMsgs[$type]))
		{
			$this->mMsgs[$type]->mData .= $msg;
			$ret = &$this->mMsgs[$type];
			return $ret;
		}
		else
		{
			return $this->Add($type, $msg);
		}
	}
	
	public function GetCount()
	{
		return count($this->mMsgs);
	}
	
	public function PrintMessages()
	{
		for($j = 0; $j<count($this->mTypes); $j++)
		{
			$outputstr .= $this->mMsgs[$this->mTypes[$j]]->mData;
		}
		return $outputstr;
	}
}



class EdiObject
{
	protected $mDoEDI;
	public $mEdiType;
	public $mGroups;
	public $mRawData;
	public $mTransactionData;
	public $mParser;
	public $mReject;
	public $mOutputString;
	protected $mOutputEdi;
	public $mEdiVendor;
	
	function __construct($data = null)
	{
		global $EdiVendor;
		$this->mRawData = $data;
		$this->mDoEdi = DoEdi::getInstance();
		$this->mEdiVendor = &$EdiVendor;
		$this->mGroups = new EdiGroups();
	}
		
	/* data = array('data' => EDI data, 'filename' => output EDI file name, with extension */
	public function Send()
	{
		if(isset($this->mOutputEdi) && get_parent_class($this->mOutputEdi) == 'EdiBuilder') // only run if the OutputEdi is a sub-class of EdiBuilder
		{
			$data = new EdiData();
			$thisdata = array();
			$thisdata['data'] = $this->mOutputEdi->GetOutput();
			$thisdata['filename'] = ''; // set to blank, will be reset before sending to the proper value
			$data->Load($thisdata);
			$data->Send();
			$data->Archive();
			DoEdi::AddToDb($data);
		}
	}
	
	public function GetOutput()
	{
		return $this->mOutputEdi->GetOutput();
	}
}




class EdiParser
{
	/**
	 * The split data sent in to this instance
	 * @var Instance
	 */
	public static $mReadLocation;
	public static $mRawData;
	public static $mEdiData;
	public static $mAllSplitData;
	public $mReject;
	public static $mSegmentNumber;
	public static $mSegmentData;
	public static $mSegmentRawData;
	public static $mSeparators;
	public static $mInterchangeHeaderData;
	public static $mTransactionData;
	public $mReturn;
	public $mDoEdiParser;
	public $mEdiVendor;
	public $mEndOfFile;
	public $mHasBeenSplit;
	
	function __construct(&$data)
	{
		global $EdiVendor;
		$this->mHasBeenSplit = false;
		$this->mSeparators = $data->mSeparators == null ? new EdiSeparators() : $data->mSeparators;
		$this->mSegmentData = array();
		if(!is_null($data))
		{
			$this->mRawData = $data->mData;
			if(!is_array($this->mRawData))
			{
				$this->InnerSplit($this->mRawData);
				$this->mHasBeenSplit = true;
			}
			else
			{
				$this->mAllSplitData = $this->mRawData; // already split
				$this->mHasBeenSplit = true;
			}
			$this->mReadLocation = 0;
			$this->mSegmentNumber = 0;
			$this->mTransactionData = array();
		}
		$this->mDoEdiParser = DoEdiParser::getInstance();
		$this->mEdiVendor = &$EdiVendor;
	}
	
	public function PriceCheck($upc, $price)
	{
		return $this->mDoEdiParser->PriceCheck($upc, $price);
	}
	
	public function SetReadHeadTo($location)
	{
		$this->mReadLocation = $location;
	}
	
	private function Split($data, $internal = null)
	{
		// roll through an Edi stream and separate to the constituent parts
		if(is_null($internal))
		{
			$array = split("[".EdiSeparators::NEWELEMENT.EdiSeparators::NEWSEGMENT.EdiSeparators::NEWSUBSEGMENT."]", $data);
		}
		else
		{
			$array = spliti("[".sql_regcase($this->mSeparators->mElement.$this->mSeparators->mSegment.$this->mSeparators->mSubSegment)."]", $data);
		}
		foreach($array as $k => $v)
		{
			$array[$k] = trim($v);
		}
		return $array;
	}
	
	public function GetSplitData()
	{
		return $this->Split($this->mRawData, false);
	}
	
	private function SplitSegments($data, $internal = null)
	{
		// roll through an Edi stream and separate to the constituent parts
		if(is_null($internal))
		{
			$array = split("[".EdiSeparators::NEWSEGMENT."]", $data);
		}
		else
		{
			$array = spliti("[".sql_regcase($this->mSeparators->mSegment)."]", $data);
		}
		foreach($array as $k => $v)
		{
			$array[$k] = trim($v);
		}
		return $array;
	}
	
	public function GetAllSplitData()
	{
		return $this->mAllSplitData;
	}
	
	public function GetNextData()
	{
		$return = isset($this->mSegmentData[$this->mSegmentNumber][$this->mReadLocation]) ? $this->mSegmentData[$this->mSegmentNumber][$this->mReadLocation] : '';
		$this->mReadLocation++;
		return $return;
	}
	
	public function NextSegment()
	{
		$this->mSegmentNumber++;
		if($this->mSegmentNumber == count($this->mSegmentData)) $this->mEndOfFile = true;
		$this->mReadLocation = 0;
	}
	
	public function Peek()
	{
		return isset($this->mSegmentData[$this->mSegmentNumber]) && isset($this->mSegmentData[$this->mSegmentNumber][$this->mReadLocation]) ? $this->mSegmentData[$this->mSegmentNumber][$this->mReadLocation] : '';
	}
	
	public function Skip($num = 1)
	{
		$this->mReadLocation = $this->mReadLocation + $num;
	}
	
	private function InnerSplit($data = null)
	{
		if($this->mHasBeenSplit == true) return;
		if(is_null($data)) $data = $this->mRawData;
		$segments = $this->SplitSegments($data, true);
		$this->mSegmentRawData = $segments;
		$segnum = 0;
		foreach($segments as $seg)
		{
			$array = $this->Split($seg, true);
			$this->mSegmentData[$segnum] = $array;
			$segnum++;
		}
	}
	
	public function GetInterchangeHeaderData()
	{
		$return = array();
		$this->Skip(5);
		$return['sender_qualifier'] = $this->GetNextData();
		$return['sender'] = $this->GetNextData();
		$return['receiver_qualifier'] = $this->GetNextData();
		$return['receiver'] = $this->GetNextData();
		$dateinfo = $this->GetNextData();
		$timeinfo = $this->GetNextData();
		$return['datetime'] = '20'.substr($dateinfo, 0, 2).'-'.substr($dateinfo, 2, 2).'-'.substr($dateinfo, -2).' '.substr($timeinfo, 0, 2).':'.substr($timeinfo, -2).':00';
		$return['standard'] = $this->GetNextData();
		$return['version'] = $this->GetNextData();
		$return['interchangeno'] = $this->GetNextData();
		$return['ackreq'] = $this->GetNextData();
		$return['testing'] = $this->GetNextData();
		$this->NextSegment();
		$this->NextSegment();
		return $return;
	}
	
	public function GetInterchangeFooterData()
	{
		$return = array();
		if($this->GetNextData() != 'IEA')
		{
			die('not in end of interchange; '.$this->Peek());
		}
		$return['total_groups'] = $this->GetNextData();
		$return['interchange_control_number2'] = $this->GetNextData();
		return $return;
	}
	
	public function StartGroupEnvelope(&$group)
	{
		$return = array();
		$this->mSegmentNumber = 1;
		$this->Skip(); // GS
		$env = $group->Add();
		$env->mType = $this->GetNextData();
		$env->mSender = $this->GetNextData();
		$env->mReceiver = $this->GetNextData();
		$dateinfo = $this->GetNextData();
		$timeinfo = $this->GetNextData();
		$env->mDateTime = date('Y-m-d H:i:s', strtotime("$dateinfo $timeinfo"));
		$env->mControlHeader = $this->GetNextData();
		$env->mAgency = $this->GetNextData();
		$env->mVersion = $this->GetNextData();
		$this->NextSegment();
	}
	
	public function EndGroupEnvelope(&$env)
	{
		if($this->GetNextData() != 'GE')
		{
			die('not in envelope footer, here is the next data element: '.$this->GetNextData());
		}
		$env->mTransactions = $this->GetNextData();
		$env->mControlFooter = $this->GetNextData();
		$this->NextSegment();
	}
}


class EdiSegment implements arrayaccess
{
	public $mSubSegments;
	private $mElements; // just a simple array

	function __construct($segtype)
	{
		$this->mElements = array();
		$this->mElements[0] = $segtype;
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
	
	public function AddSubSegment($name = null)
	{
		$this->mSubSegments = array();
		$this->mSubSegments[] = array();
		$this->mSubSegments[0] = $name;
		return $this->mSubSegments[count($this->mSubSegments)-1];
	}
	
	public function Make()
	{
		// output this segment
		$output = "";
		for($i = 0; $i < count($this->mElements); $i++)
		{
			$thiskey = key($this->mElements);
			$check = current($this->mElements);
			$next = next($this->mElements);
			$nextkey = key($this->mElements);
			if($check !== false)
			{
				if($nextkey === false || $next === false)
				{
					$nextkey = $thiskey + 1; // this gets the last element
				}
				for($j = $thiskey; $j < $nextkey; $j++)
				{
					if($j != 0) $output .= EdiSeparators::NEWELEMENT;
					if($j == $thiskey) {
                                                $check = str_replace(EdiSeparators::NEWSEGMENT," ",$check);
                                                $check = str_replace(EdiSeparators::NEWSUBSEGMENT," ",$check);
                                                $check = str_replace(EdiSeparators::NEWELEMENT," ",$check);
                                                $output .= $check;
                                        }
				}
			}
		}
		if(isset($this->mSubSegments))
		{
			foreach($this->mSubSegments as $subseg)
			{
				$output .= EdiSeparators::NEWSUBSEGMENT;
				for($i = 0; $i < count($subseg); $i++)
				{
					$output .= $subseg[$i];
					if($i == count($subseg) - 1) continue;
					$output .= EdiSeparators::NEWELEMENT;
				}
			}
		}
		$output .= EdiSeparators::NEWSEGMENT;
		return $output;
	}
}


class EdiSeparators
{
	public $mSegment;
	public $mElement;
	public $mSubSegment;
	const NEWELEMENT = "*";
	const NEWSEGMENT = "~";
	const NEWSUBSEGMENT = ">"; // only seen at the end of exchange

	private static $mInstance;
	/**
	 * Returns an instance of the class if the class does not already exist.
	 * @return An instance of EdiSeparators
	 */
	public static function getInstance() {
		if (self::$mInstance == null) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}
	
	function __construct()
	{
		$this->mSegment = self::NEWSEGMENT;
		$this->mElement = self::NEWELEMENT;
		$this->mSubSegment =self::NEWSUBSEGMENT;
	}

	public function GetFromData(&$fulldata = null)
	{
		if(!is_null($fulldata))
		{
			$elementsep = substr($fulldata->mData, 3, 1);
			$subsegmentsep = substr($fulldata->mData, 104, 1);
			$segmentsep = substr($fulldata->mData, 105, 1);
			$this->mElement = $elementsep;
			$this->mSubSegment = $subsegmentsep;
			$this->mSegment = $segmentsep;
		}
		if($this->mElement != $this->mSubSegment && $this->mElement != $this->mSegment && $this->mSubSegment != $this->mSegment)
		{
			// they are unique...now if one is alphanumeric, we fail
			if(eregi('a-z0-9', $this->mElement) || eregi('a-z0-9', $this->mSubSegment) || eregi('a-z0-9', $this->mSegment))
			{
				return 0;
			}
			else
			{
				return 1;
			}
		}
	}
}



class EdiTransaction
{
	public $mTypeCode;
	public $mTransactionControlHeader;
	public $mSegmentTotal;
	public $mTransactionControlFooter;
	public $mTotalTransactionSegments;
	public $mReject;
	public $mRejectReason;
	public $mSendErrorNotice;
	public $mDates;
	public $mMessages;
	public $mUsers;

	function __construct()
	{
		$this->mDates = new EdiDates();
		$this->mMessages = new EdiMessages();
		$this->mUsers = new EdiUsers();
	}
}


class EdiUser
{
	protected $mDoEdiUser;
	public $mUserId;
	public $mName;
	public $mAddress1;
	public $mAddress2;
	public $mCity;
	public $mState;
	public $mPostal;
	public $mCountry;
	public $mPaymentRefNumber;
	public $mReturnReferenceNumber;
	public $mPermitCity;
	public $mPermitState;
	public $mPermitPostal;
	public $mMethodCode;
	public $mContactFunction;
	public $mContactName;
	public $mContactTelephone;
	public $mContactEmail;
	public $mMarkedShippingCode;
	public $mShipToStore;
	public $mStoreId;
	public $mServiceLevel;
	public $mType;
	
	function __construct($userid = null)
	{
		$this->mDoEdiUser = new DoEdiUser();
		$this->mContactFunction = array();
		$this->mContactName = array();
		$this->mContactTelephone = array();
		$this->mContactEmail = array();
		$this->mCountry = 'USA'; // defaults to US
		if(!is_null($userid))
		{
			$this->mUserId = $userid;
			$this->Load();
		}
	}
	
	public function AddUser()
	{
		// checks the db for this user, if not found adds the info we have
		if(!isset($this->mName)) // if we don't have even the name set, return -1
		{
			return -1;
		}
		else
		{
			$this->mUserId = $this->mDoEdiUser->AddEdiUser($this);
			return $this->mUserId;
		}
	}
	
	public function Load()
	{
		// grabs the user data from the db
		$details = array();
		$details = $this->mDoEdiUser->GetEdiUser($this->mUserId);
		$this->mName = $details['last_name'];
		$this->mAddress1 = $details['address'];
		$this->mAddress2 = $details['address2'];
		$this->mCity = $details['city'];
		$this->mState = $details['state'];
		$this->mPostal = $details['zip'];
		unset($details);
	}
	
	public function SetVendor(&$vendor)
	{
		$this->mDoEdiUser->mEdiVendor = $vendor;
	}
}


class EdiUsers implements arrayaccess
{
	protected $mUsers;
	
	function __construct()
	{
		$this->mUsers = array();
	}
	
	public function offsetExists($offset)
	{
		return isset($this->mUsers[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->mUsers[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->mUsers[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->mUsers[$offset]) ? $this->mUsers[$offset] : null;
	}
	
	public function Add($type = null)
	{
		if($type !== null)
		{
			if($this->TypeExists($type))
			{
				return $this->GetType($type);
			}
			else
			{
				$this->mUsers[$type] = new EdiUser();
				$ret = &end($this->mUsers);
				$ret->mType = $type;
				reset($this->mUsers);
				return $ret;
			}
		}
		if(count($this->mUsers) > 0 && $this->mUsers[count($this->mUsers) - 1]->mType == null)  // need to make sure there's at least one user in the array as well, was returning blanks
		{
			$ret = &end($this->mUsers);
			return $ret; // if the last new user's type wasn't set, it wasn't used...try again
		}
		$this->mUsers[] = new EdiUser();
		$ret = &end($this->mUsers);
		reset($this->mUsers);
		return $ret;
	}
	
	public function TypeExists($type)
	{
		return $this->offsetExists($type);
	}
	
	public function GetType($type)
	{
		$ret = &$this->mUsers[$type];
		return $ret;
	}
}


class EdiVendor
{
	// this class holds all vendor-specific data
	// EDI ID & Qualifier can differ based upon testing status or not testing status
	public $mVendorName; // text name of the vendor
	public $mEdiId;
	public $mEdiQualifier;
	public $mAS2SenderName;
	public $mTypeCode; // used for filename creation
	public $mVendorId; // also for filename creation
	public $mVendorPath; // path for storage of files
	public $mTesting; // boolean value, whether this vendor is still in testing mode
	public $mVendorUserID; // grabbed from the db
	public $mVendorFormID; // also grabbed from the db
	public $mSendAck; // whether this vendor sends a 997 for all received files
	public $mPOCancelResponseID; // EDI file type to send when an order/item is canceled
	public $mWarehouseCode; // warehouse code, typically used for 846 (Inventory)
	private $mDoEdiVendor;
	
	function __construct()
	{
		$this->mDoEdiVendor = DoEdiVendor::getInstance();
	}
	
	private function Assign(&$vendor)
	{
		$this->mVendorName = $vendor['vendor'];
		$this->mEdiId = $vendor['edi_id'];
		$this->mEdiQualifier = $vendor['edi_qualifier'];
		$this->mAS2SenderName = $vendor['as2_sendername'];
		$this->mTypeCode = $vendor['typecode'];
		$this->mVendorId = $vendor['vendor_id'];
		$this->mTesting = $vendor['testing'];
		$this->mSendAck = $vendor['send_997'] == 1 ? true : false;
		$this->mPOCancelResponseID = $vendor['cancel_edi_type'];
		$this->mWarehouseCode = $vendor['warehouse_code'];
		if($vendor['path'] != '')
		$this->mVendorPath = realpath(dirname(__FILE__).'/'.$vendor['path']).'/';
		$this->mVendorUserID = DoEdi::GetRetailerUserId($this->mVendorName, $this->mTesting);
		$this->mVendorFormID = DoEdi::GetRetailerFormId($this->mVendorName, $this->mTesting);
	}
	
	public function LoadDefault()
	{
		$this->LoadFromName('walmart');
	}
	
	public static function GetPMD()
	{
		$pmdvend = new EdiVendor();
		$pmdvend->LoadFromName('pmd');
		$go = &$pmdvend;
		return $go;
	}
	
	public function LoadFromId($vendor_id)
	{
		// loads vendor data from db based upon passed-in vendor ID
		$vendor = $this->mDoEdiVendor->GetFromEdiID($vendor_id);
		if(is_array($vendor) && count($vendor) > 0)
		{
			$this->Assign($vendor);
			return 1;
		}
		return 0;
	}
	
	public function SetVendor($vendor)
	{
		// sets the vendor to the passed-in vendor name
		$this->LoadFromName(strtolower($vendor));
	}
	
	public function LoadFromName($vendor)
	{
		// loads vendor data from db  based on passed-in vendor name
		$vendor = $this->mDoEdiVendor->GetFromVendorName($vendor);
		if(is_array($vendor) && count($vendor) > 0)
		{
			$this->Assign($vendor);
			return 1;
		}
		return 0;
	}
	
	
	public function LoadFromFilename($filename)
	{
		// loads vendor data from db, using the passed-in filename to get vendor id
		$vendor = $this->mDoEdiVendor->GetFromFilename($filename);
		if(is_array($vendor) && count($vendor) > 0)
		{
			$this->Assign($vendor);
			return 1;
		}
		return 0;
	}
	
	public function GetAllVendors()
	{
		$sendvendors = array();
		// retrieves all vendors from the db and returns as array of EdiVendors
		$vendors = $this->mDoEdiVendor->GetAllVendors();
		foreach($vendors as $thisvendor)
		{
			$newvendor = new EdiVendor();
			$newvendor->Assign($thisvendor);
			$sendvendors[] = $newvendor;
			unset($newvendor);
		}
		return $sendvendors;
	}
}
?>