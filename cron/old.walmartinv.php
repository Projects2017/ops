<?php

chdir(dirname(__FILE__));
require('inc_walmartinventory.php');

/**
 * FTP Connection
 * @global FTPResource $ftp_conn
 */
$ftp_conn = ftp_conn('140.174.10.195','soflex','welcome1', 21);

/**
 * Inventory Report Generation Object
 * @global WalmartInventory $inv
 */
$inv = new WalmartInventory();

/* Set Soflex Vendor */
$inv->mSenderID = '45750';
$inv->mSenderName = 'Soflex Furniture';

/* Set Walmart Merchant ID */
$inv->mRecipientID = '2677';
$inv->mRecipientName = 'Walmart.com';

// Generate File Name/ID
$inv->GenFileId();

/**
 * Target Filename
 * @global string $filename
 */
$filename = 'WMI_Inventory_'.$inv->mFileName.'.wff';

/*
II|487988|009843873409|AC-67800|AC|500|3|4|||6.00|5.00|3.00|
II|487989|009843478497|AA-67800|AA||5|10|||6.00|5.00|3.00|
II|487990|009843848999|PO-67800|PO|500|||20011201||6.00|5.00|3.00|
II|487991|009843842638|JT-67800|JT||5|7|||6.00|5.00|3.00|
II|487992|009843829781|BO-67800|BO||3|5|||6.00|5.00|3.00|
II|487993|009843958732|SE-67800|SE|300|||20011201|20020115|6.00|5.00|3.00|
II|487994|009843459727|RO-67800|RO|300||||20020101|6.00|5.00|3.00|
II|487995|009843878211|NA-67800|NA|||||||||
II|487996|009843878212|DT-67800|DT|||||||||
*/

/**
 * Inventory Detail
 * @global WalmartInventoryItem $detail
 */
$detail = new WalmartInventoryItem();


$detail->mItemNumber = '487988';
$detail->mUPC = '009843873409';
$detail->mSKU = 'AC-67800';
$detail->mAvailability = WalmartInventoryItem.ACTIVE;
$detail->mQuantity = 500;
$detail->mMinShipDays = 3;
$detail->mMaxShipDays = 4;
$detail->mMSRP = '6.00';
$detail->mRetail = '5.00';
$detail->mCost = '3.00';

$inv->AddDetail($detail);