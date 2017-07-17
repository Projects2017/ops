#!/usr/bin/php -q
<?php
/* Create Inventory file to WM spec,
   FTP up every 20 minutes to a set location,
   we run as a cron script. */

//connect to database (libraries here)
include(dirname(__FILE__)."/../database.php");//imparts $link as the database handle

$sql = "SELECT `relation_id` FROM `login` WHERE `username` = 'walmart' AND `type` = 'D'";
$result = mysql_query($sql);
checkDBerror($sql);
$result = mysql_fetch_assoc($result);
$user_id = $result['relation_id'];


$sql = "SELECT `form` FROM `form_access` WHERE `user` = '".$user_id."'";
$result = mysql_query($sql);
checkDBerror($sql);
$result = mysql_fetch_assoc($result);
$form_id = $result['form'];

$sql = "SELECT `ID` FROM `form_headers` WHERE `form` = '".$form_id."'";
$resultHead = mysql_query($sql);
checkDBerror($sql);
while ($rowHead = mysql_fetch_assoc($resultHead)) {
	$sql = "SELECT * FROM `form_items` WHERE `header` = '".$rowHead['ID']."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if ($row['avail'] == -1) {
			$code = 'AA';
		} elseif ($row['avail'] > 0) {
			$code = 'AC';
		} else {
			$code = 'NA';
		}
		$parts = explode(":",$row['sku']);
		if (stock_block($row['stock'])) {
			$code = 'NA';
		}
		if (count($parts) == 1) {
			$upc = $parts[0];
			$sku = ''; // Walmart Item ID
		} elseif (count($parts) == 2) {
			$upc = $parts[0];
			$sku = $parts[1]; // Walmart Item ID
		}
		$sql = "REPLACE INTO `walmart_inventory` (`formitems_id`, `item_id`, `upc`, `sku`,`avail_code`,`numavail`,`mindaytoship`,`maxdaytoship`,`msrp`,`retail`,`cost`,`facility`,`deletionssent`,`updated`)
		VALUES ('".$row['ID']."','".$sku."','".$upc."','".$row['partno']."','".$code."','".$row['avail']."',1,1,NULL,NULL,'".$row['price']."','TX',0,1)";
		mysql_query($sql);
		checkDBerror($sql);

		//$sql = "INSERT INTO `walmart_inventory` (`formitems_id`, `item_id`, `upc`, `sku`,`avail_code`,`numavail`,`mindaytoship`,`maxdaytoship`,`msrp`,`retail`,`cost`,`facility`)
		// VALUES ('".$row['ID']."','".$sku."','".$upc."','".$row['partno']."','".$code."','".$row['avail']."',1,2,NULL,NULL,'".$row['price']."','TX')";
	}
}

// Remove any deletions that are old
$sql = "DELETE FROM `walmart_inventory` WHERE `deletionssent` > 3 AND `updated` = 0";
mysql_query($sql);
checkDBerror($sql);

// Incriment Discontinued Items Deletions Sent Counter
$sql = "UPDATE `walmart_inventory` SET `deletionssent` = `deletionssent` + 1, `updated` = 1, `formitems_id` = NULL  WHERE `avail_code` = 'DT'";
mysql_query($sql);
checkDBerror($sql);

// Make all other items not updated (i.e. not present) set to NA
$sql = "UPDATE `walmart_inventory` SET `formitems_id` = NULL, `avail_code` = 'NA', `deletionssent` = `deletionssent` + 1, `updated` = 1, `numavail` = NULL, `maxdaytoship` = NULL,`mindaytoship` = NULL, `availstart` = NULL, `availend` = NULL WHERE `updated` = 0";
mysql_query($sql);
checkDBerror($sql);

$sql = "UPDATE `walmart_inventory` SET `updated` = 0";
mysql_query($sql);
checkDBerror($sql);

//grab data from database.
$sql = "SELECT * FROM `walmart_inventory`";
$res=mysql_query($sql);
checkDBerror($sql);

//File Header:
$wmname="Walmart.com";
$wmnumb=2677;
$pmname="Soflex Furniture";
$pmnumb=45750;

//VVVVVVVVV_YYYYMMDD_HHMMSS_NNNNNN
$fileid=$pmnumb.date(".Ymd.His.").rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
$fname="WMI_Inventory_".str_replace(".","_",$fileid).".wff";
$datfile=implode("|",array("FH",$fileid,"FII","4.0.0",$wmnumb,$wmname,$pmnumb,$pmname))."\n";

//parse it to needed format.
$rowcount=0;
while($row=mysql_fetch_assoc($res))
{
	#	Field Name	Opt	Data	Length	Description
	#1	Record Type	R	STR	2	Fixed string "II"
	$vals=array("II");
	#2	Item Number	R	NUM	1 to 13	Walmart.com item number
	$vals[]=$row['item_id']?$row['item_id']:'';
	#3	UPC	R	NUM	13	Wal-Mart UPC (13 digits with no check digit)
	$row['upc'] = str_pad($row['upc'], 13, "0", STR_PAD_LEFT);
	if (substr($row['upc'],0,8) != '00811443') {
		echo "Invalid UPC!!! (".$row['upc'].") must have 00811443 prefix\n";
		print_r($row);
		echo "\n\n";
		continue;
	}
	$vals[]=$row['upc'];
	#4	Vendor SKU	R	STR	1 to 20	Supplier SKU or model number
	$vals[]=$row['sku'];
	#5	Availability Code	R	STR	2	See table below for values and usage.
	$vals[]=$row['avail_code'];
	#6	On Hand Quantity	O	NUM	0 to 9	Quantity of items available to ship.
	if ($row['numavail'] == -1) {
		$row['numavail'] = '';
	}
	$vals[]=$row['numavail'];
	#7	Minimum Days to Ship	O	NUM	0 to 2	The minimum number of business days to fulfill an order for this item.
	$vals[]=$row['mindaytoship'];
	#8	Maximum Days to Ship	O	NUM	0 to 2	The maximum number of business days to fulfill an order for this item.
	$vals[]=$row['maxdaytoship'];
	#9	Availability Start Date	O	DATE	0 or 8	The first date the item will be available to sell (as CCYYMMDD)
	$vals[]=$row['availstart'];
	#10	Availability End Date	O	DATE	0 or 8	The last date the item will be available to sell (as CCYYMMDD)
	$vals[]=$row['availend'];
	#11	Item MSRP	O	DEC	8.2	Suggested retail price per item
	#	Note: Pricing feeds are not supported for all suppliers.
	$vals[]=$row['msrp']?$row['msrp']:'';
	#12	Item Retail	O	DEC	8.2	Actual retail price per item.
	#	Note: Pricing feeds are not supported for all suppliers.
	$vals[]=$row['retail']?$row['retail']:'';
	#13	Item Cost	O	DEC	8.2	Supplier cost per item to Walmart.com.
	#	Note: Pricing feeds are not supported for all suppliers.
	$vals[]=$row['cost']?$row['cost']:'';
	#14	Facility ID	O	STR	20	Facility ID provided by the vendor.
	#	Note: Most vendors don�t provide updates on the facility-level.  In most cases this field is not used.
	#$vals[]=$row['facility'];
	
	$datfile.=implode("|",$vals)."\n";
	$rowcount++;
}

$datfile.="FT|".$fileid."|".$rowcount."\n";

//print($datfile);

/* Tmp crap */
$temp2=fopen("/tmp/wm_inv/".$fname,"w");
fwrite($temp2,$datfile);
fclose($temp2);

$ftp=ftp_connect($ftpcreds['addy']);
if(!ftp_login($ftp,$ftpcreds['name'],$ftpcreds['pass'])) exit(__FILE__." ".$wmname." FTP Error: Login failed on ".date('r').".\n");
if(!ftp_chdir($ftp,"inbound")) exit(__FILE__." ".$wmname." FTP Error: Inbound directory is missing on ".date('r').".\n");
$temp=fopen("php://temp","r+");
fwrite($temp,$datfile);
rewind($temp);
if(!ftp_fput($ftp,$fname,$temp,FTP_BINARY)) exit(__FILE__." ".$wmname." FTP Error: File write has failed on ".date("r").".\n");
fclose($temp);
ftp_close($ftp);

?>
