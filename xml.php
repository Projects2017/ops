<?php

// xml.php

// A repository of XML-related functions


// Contains the class for parsing XML data into an object
require('XML.inc.php');



/* OrderToXML ($po, $header)

	This function creates the XML code for the individual POs.
	
	Attributes:
	
		po (int)			Order number
		header (boolean)	Determines whether the XML file header is added with each order number reading
							This should only be TRUE for the first iteration	
*/

function OrderToXML($po, $header) {

	/* Adapted from OrderForWeb ($po, $section), only changed to output to an XML file using the Purchase Order XML schema as
	defined in the po.dtd Document Type Definition file. */
	
	if (!is_numeric($po)) {
		die("Invalid PO#".$po);
	}

	/* Start the XML file
	We begin with the initial XML version line,
	followed by the DTD declaration.
	Make sure the DTD gets to the path, or we change the path to be the correct one. */
	
	$xmloutput = "";
	
	if($header) {
		$xmloutput = '<?phpxml version="1.0" ?>'."\n";
		$xmloutput .= '<!DOCTYPE po SYSTEM "http://www.pmddealer.com/xml/po.dtd">'."\n";
	}
	
	/* get basic order information and variables */
	$sql = "SELECT ordered, snapshot_user, comments, freight_percentage, discount_percentage, type, total, processed, process_time, deleted, user, snapshot_form, user_address, form FROM order_forms WHERE ID='".$po."'";
	$query = mysql_query($sql);
	if (!mysql_num_rows($query)) {
		die($env."\nPO#".$po." Not Found");
	}
	$result = mysql_fetch_array($query);
	//ADD ERROR CHECK FOR NO ROWS RETURNED HERE
	$form = $result['snapshot_form'];
	$orig_form = $result['form'];
	$comments = $result['comments'];
	$freight_percentage = $result['freight_percentage'];
	$discount_percentage = $result['discount_percentage'];
	$type = $result['type'];
	$total = $result['total'];
	$processed = $result['processed'];
	$process_datetime = $result['process_time'];
	if ($process_datetime == "0000-00-00 00:00:00") {
		$process_datetime = "";
	} else {
		$process_datetime = date("Y-m-d\TH:i:s", strtotime($process_time));
	}
	$user2 = $result['snapshot_user'];
	$user3 = $result['user'];
	$deleted = $result['deleted'];
	$user_address = $result['user_address'];

	/* get order time */
	$order_datetime =  date("Y-m-d\TH:i:s", strtotime($result['ordered']));
	if ($order_datetime == "0000-00-00T00:00:00") $order_datetime = "";

	/* Self Heal from bad code (Dealer) */
	if ($user2 == NULL && $user3) {
		$sql = "SELECT `snapshot`, `snapshot2` FROM `users` WHERE ID = '".$user3."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$row = mysql_fetch_assoc($query);
		if ($user_address == 2) {
			$row['snapshot'] = $row['snapshot2'];
		}
		$sql = "UPDATE `orders` SET `snapshot_user` = '".$row['snapshot']."' WHERE po_id = '".$po."'";
		$user2 = $row['snapshot'];
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `order_forms` SET `snapshot_user` = '".$row['snapshot']."' WHERE ID = '".$po."'";
		mysql_query($sql);
		checkdberror($sql);
	}
	/* Self Heal from bad code (Vendor) */
	if ($form == NULL && $orig_form) {
		$sql = "SELECT `snapshot` FROM `forms` WHERE ID = '".$orig_form."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$row = mysql_fetch_assoc($query);
		$sql = "UPDATE `orders` SET `snapshot_form` = '".$row['snapshot']."' WHERE po_id = '".$po."'";
		$form = $row['snapshot'];
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `order_forms` SET `snapshot_form` = '".$row['snapshot']."' WHERE ID = '".$po."'";
		mysql_query($sql);
		checkdberror($sql);
	}
	/* get dealer name and address */
	if ($user_address == 2)
		$sql = "SELECT first_name, last_name, address, city, state, zip, phone, fax FROM snapshot_users WHERE ID='$user2' AND secondary='Y'";
	else
		$sql = "SELECT first_name, last_name, address, city, state, zip, phone, fax FROM snapshot_users WHERE ID='$user2' AND secondary='N'";
	$query = mysql_query($sql);
	checkDBError($sql);
	$user_phone = '';
	if ($result = mysql_fetch_Array($query)) {
		$dealer_lastname = $result['last_name'];
		$dealer_firstname = $result['first_name'];
		if($result[2] != "") {
			$dealer_address1 = $result[2];		
		// Placed for the future when a second address line is in effect	
			$dealer_address2 = "";
		// End placement
			$dealer_city = $result[3];		
			$dealer_st = $result[4];
			$dealer_zip = $result[5];
		} else {
			$dealer_address1 = "";
			$dealer_address2 = "";
			$dealer_city = "";
			$dealer_st = "";
			$dealer_zip = "";
		}
		
		if($result['phone'] != "") {
			$dealer_phone = $result['phone']; } else {
				$dealer_phone = "";
			}
		if($result['fax'] != "") {
			$dealer_fax = $result['fax']; } else {
				$dealer_fax = "";
			}
		$user_phone = $result['phone']; // For use later
	}
	
	/* get vendor address */
	$sql = "select name, address, city, state, zip, phone, fax, prepaidfreight from snapshot_forms where snapshot_forms.id='".$form."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if($result = mysql_fetch_Array($query)) {
		$vendor_name = $result['name'];
		if($result['address'] != "") {
			$vendor_address1 = $result['address'];		
			// Placed for the future when a second address line is in effect	
			$vendor_address2 = "";
			// End placement
			$vendor_city = $result['city'];
			$vendor_st = $result['state'];
			$vendor_zip = $result['zip'];
		} else {
			$vendor_address1 = "";
			$vendor_address2 = "";
			$vendor_city = "";
			$vendor_st = "";
			$vendor_zip = "";
		}
		
		if($result['phone'] != "") {
			$vendor_phone = $result['phone']; }  else {
				$vendor_phone = "";
			}
		if($result['fax'] != "") {
			$vendor_fax = $result['fax']; } else {
				$vendor_fax = "";
			}
		if ($result['prepaidfreight'] == "Y")
			$freightprepaid = "1";
		else
			$freightprepaid = "0";
	}

	/* Begin building the actual XML data elements now  */

	$xmloutput .= '<po number="'.$po.'" deleted="'.$deleted.'" type="'.$type.'">'."\n";
	$xmloutput .= '<freightprepaid>'.$freightprepaid.'</freightprepaid>'."\n";
	$xmloutput .= '<entrydate>'.$order_datetime.'</entrydate>'."\n";
	$xmloutput .= '<processdate>'.$process_datetime.'</processdate>'."\n";
	$xmloutput .= '<vendor>'."\n".'<sourceid>'.$orig_form."</sourceid>\n<vendorname>".$vendor_name.'</vendorname>'."\n";
	$xmloutput .= '<address1>'.$vendor_address1.'</address1>'."\n";
	$xmloutput .= '<address2>'.$vendor_address2.'</address2>'."\n";
	$xmloutput .= '<city>'.$vendor_city.'</city>'."\n";
	$xmloutput .= '<st>'.$vendor_st.'</st>'."\n";
	$xmloutput .= '<zip>'.$vendor_zip.'</zip>'."\n";
	$xmloutput .= '<phone>'.$vendor_phone.'</phone>'."\n";
	$xmloutput .= '<fax>'.$vendor_fax.'</fax>'."\n".'</vendor>'."\n";
	$xmloutput .= '<dealer>'."\n".'<sourceid>'.$user3."</sourceid>\n";
	$xmloutput .= '<addressnumber>'.$user_address.'</addressnumber>.'."\n";
	$xmloutput .= "<dealerfirstname>".$dealer_firstname.'</dealerfirstname>'."\n";
	$xmloutput .= '<dealerlastname>'.$dealer_lastname.'</dealerlastname>'."\n";
	$xmloutput .= '<address1>'.$dealer_address1.'</address1>'."\n";
	$xmloutput .= '<address2>'.$dealer_address2.'</address2>'."\n";
	$xmloutput .= '<city>'.$dealer_city.'</city>'."\n";
	$xmloutput .= '<st>'.$dealer_st.'</st>'."\n";
	$xmloutput .= '<zip>'.$dealer_zip.'</zip>'."\n";
	$xmloutput .= '<phone>'.$dealer_phone.'</phone>'."\n";
	$xmloutput .= '<fax>'.$dealer_fax.'</fax>'."\n".'</dealer>'."\n";
	
	/* It's time to get more data....we'll leave the XML for now. */

		$sql = "SELECT DISTINCT orders.setqty, orders.mattqty, orders.qty, orders.item, orders.ID AS orderID, orders.ordered AS orderdate, orders.ordered_time AS ordertime, snapshot_items.partno, snapshot_items.description, snapshot_items.price, snapshot_items.set_,	 snapshot_items.matt, snapshot_items.box, snapshot_items.header, snapshot_items.cubic_ft, snapshot_items.orig_id, snapshot_items.setqty as qtyinset FROM orders INNER JOIN snapshot_items ON snapshot_items.id=orders.item WHERE orders.po_id='".$po."' ORDER BY snapshot_items.header, snapshot_items.display_order";
		// "DISTINCT" added 3/16/04. Somehow, duplicate items are occasionally being added to the snapshots table.
		// Since the cause of this can't be found, we'll fix the problem here.
		$query = mysql_query($sql);

		$itemgroup = 0;
		$total_cubic_ft = 0;
		$totalpieces = 0;
		$producttotal = 0;

/* Iterate through all of the rows, building the XML format one line at a time. */

		while($result = mysql_fetch_assoc($query)) {
	
			if($itemgroup == 0) {
				$xmloutput .= '<itemgroup header="'.$result['header'].'">'."\n";
				}
			elseif($itemgroup != $result['header']) {
				$xmloutput .= '</itemgroup>'."\n".'<itemgroup header="'.$result['header'].'">'."\n";
				}
			$itemgroup = $result['header'];
			$xmloutput.= '<lineitem id="'.$result['orderID'].'">'."\n".'<itemnumber>'.$result['orig_id'].'</itemnumber>'."\n";
			$xmloutput .= '<datetime>'.$result['orderdate']."T".$result['ordertime'].'</datetime>'."\n";
			$xmloutput.= '<snapitem>'.$result['item']."</snapitem>\n";
			$xmloutput.= '<description>'.$result['description'].'</description>'."\n";
			if ($result['box'] != "") {
				$pricetemp = $result['box'];
			} else {
				$pricetemp = $result['price'];
				if ($pricetemp == "") $pricetemp = 0;
			}
			if($result['qty'] != 0) {
				$xmloutput.= '<qty type="box" amt="'.$result['qty'].'">'."\n";
				$xmloutput.= '<unitprice>'.$pricetemp.'</unitprice>'."\n".'</qty>'."\n";
				$totalpieces += $result['qty'];
				$producttotal += ($result['qty'] * $pricetemp);
				$total_cubic_ft += round($result['cubic_ft'] * $result['qty'], 2);
				}
				
			if($result['setqty'] != 0) {
				$xmloutput.= '<qty type="set" amt="'.$result['setqty'].'" setqty="'.$result['qtyinset'].'">'."\n";
				$xmloutput.= '<unitprice>'.$result['set_'].'</unitprice>'."\n".'</qty>'."\n";
				$totalpieces += ($result['qtyinset'] * $result['setqty']);
				$producttotal += ($result['setqty'] * $result['set_']);
				}
						
			if($result['mattqty'] != 0) {
				$xmloutput.= '<qty type="matt" amt="'.$result['mattqty'].'">'."\n";
				$xmloutput.= '<unitprice>'.$result['matt'].'</unitprice>'."\n".'</qty>'."\n";
				$totalpieces += $result['mattqty'];
				$producttotal += ($result['mattqty'] * $result['matt']);
				}
			
			$xmloutput .= '</lineitem>'."\n";
			
			/* We have the line item info now; loop back to the start for the next row */
			}
			
			$xmloutput .= '</itemgroup>'."\n";
			$xmloutput .= '<totalpieces>'.$totalpieces.'</totalpieces>'."\n";
			$xmloutput .= '<approxvolume>'.$total_cubic_ft.'</approxvolume>'."\n";
			$xmloutput .= '<producttotal>'.$producttotal.'</producttotal>'."\n";
						
			/* Do the last bit of calculating */
			
		$discount = $producttotal * ($discount_percentage * .01);
		if ($discount < 0)
			$discount = $discount-$discount-$discount;
		else
			$discount = "-".$discount;

		$subtotalshow = $producttotal + $discount;
		$freight = $subtotalshow * ($freight_percentage * .01);
		$grandtotal = $subtotalshow + $freight;
		
		if ($freight == "-0") $freight = 0;
		if ($discount == "-0") $discount = 0;
		
			/* Finish up */
			
			$xmloutput .= '<discount percentage="'.$discount_percentage.'">'.$discount.'</discount>'."\n";
			$xmloutput .= '<freight percentage="'.$freight_percentage.'">'.$freight.'</freight>'."\n";
			$xmloutput .= '<total>'.$grandtotal.'</total>'."\n";
			$xmloutput .= '<comments>'.$comments.'</comments>'."\n";
			$xmloutput .= '</po>'."\n";			
	
	return $xmloutput;
}
/* end OrderToXML */



function displayForm() {
	echo "<form enctype=\"multipart/form-data\" action=\"checkzip.php\" method=\"post\" name=\"sourceForm\" id=\"sourceForm\">
  <p>Import XML Files to RSS Database</p>
  <p>Choose ZIP'd XML file source:  </p>
  <p>
    <label>
    <input type=\"radio\" name=\"chooseType\" checked value=\"server\" onchange=\"checkSelect()\" />
Server file</label>
    <br />
    <label>
    <input type=\"radio\" name=\"chooseType\" value=\"upload\" onchange=\"checkSelect()\" />
Upload file</label>
    <br />
	<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"2000000\" />
    <input disabled type=\"file\" name=\"upload_file\" />
  </p>
  <p>
    <input type=\"submit\" name=\"submit\" value=\"Choose records...\" />&nbsp;&nbsp;&nbsp;&nbsp;<input name=\"reset\" type=\"reset\" value=\"Reset Form\" />
  </p>
</form>";

}

function displayHeader($type, $title) {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>RSS XML '.$type;
if($title != "") {
	echo ' - '.$title;
}
echo '</title>
<script language="javascript" src="xml.js" />
</head>
<body>';
}

function displayZIPs($files, $basedir) {
	global $xmldir;
	echo '<form enctype="multipart/form-data" action="do_importxml.php" method="post" name="chooseXML" id="chooseXML">
	<script language="javascript" src="xml.js" /><p>Select XML files to import</p>'; // First part of the form
	echo '<p><input type="submit" name="submit" value="Import XML records" />&nbsp;&nbsp;&nbsp;&nbsp;<input name="reset" type="reset" value="Reset Form" /></p>';
	$f = 0; // Counter for the ZIP files
	foreach($files as $file) {
		echo '<p><label><input type="checkbox" name="all_'.$f.'" value="'.$file.'" onchange="chooseAll(\''.$f.'\')"><input type="hidden" name="'.$f.'" value="'.$file.'" />All files from '.$file.'</label><br />'."\n";
		$zipfile = zip_open($xmldir.$file); // Open the zip file
		$k = 0; // Counter for the individual XML files
		echo "<div id=\"$f\">";
		while ($inner_file = zip_read($zipfile)) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input type="checkbox" name="'.$f.'_'.$k.'" value="'.basename(zip_entry_name($inner_file)).'">'.basename(zip_entry_name($inner_file)).'</label>'."\n".'<br />';
			$k++;
		}
		$f++;
		echo '</div></p>';
	}
	echo '<p><input type="submit" name="submit" value="Import XML records" />&nbsp;&nbsp;&nbsp;&nbsp;<input name="reset" type="reset" value="Reset Form" />
  </p></form>';
}


function makeXMLObject($xmlstring) {
	$XMLObject = new XML();
	$XMLObject->parse($xmlstring);
	return $XMLObject;
}


function getZipTarget($findme, $postvars) {
	if(substr($findme, 0, -1) != "_") {
		$findme = substr($findme, 0, strlen($findme)-1);
	}
	$findme = substr($findme, 0, strlen($findme)-1);
	foreach($postvars as $key => $value) {
		if($key == $findme) return $value;
	}
}


function getXMLFromZip($xml, $sourceZip) {
	global $xmldir;
	echo $xmldir.$sourceZip." - ".$xml;
	$zipfile = zip_open($xmldir.$sourceZip);
	echo $zipfile;
	while($zipFiles = zip_read($zipfile)) {
		echo 'zip_entry_name($zipFiles) == $xml is '.(zip_entry_name($zipFiles) == $xml)."\n<br />";
		if(zip_entry_name($zipFiles) == $xml) {
			echo 'Within the if() loop now...'."\n<br />";
			$go = zip_entry_open($zipfile, $zipFiles, "r");
			$export = zip_entry_read($zipFiles, zip_entry_filesize($zipFiles));
			echo $export;
		}
	}
	return $export;
}


function importXML($xmlObj) {
	// start assigning XML object data values to variables
	$xmlPO =&$xmlObj->po;
	$po = $xmlPO->_param['number'];
	$deleted = $xmlPO->_param['deleted'];
	$type = $xmlPO->_param['type'];
	$freightprepaid = $xmlPO->freightprepaid->_value;
	$entrydatetime = $xmlPO->entrydate->_value;
	$processdatetime = $xmlPO->processdate->_value;
	$xmlVendor =&$xmlPO->vendor;
	$vendorID = $xmlVendor->sourceid->_value;
	$vendorName = $xmlVendor->vendorname->_value;
	$vendorAdd1 = $xmlVendor->address1->_value;
	$vendorAdd2 = $xmlVendor->address2->_value;
	$vendorCity = $xmlVendor->city->_value;
	$vendorSt = $xmlVendor->st->_value;
	$vendorZip = $xmlVendor->zip->_value;
	$vendorPhone = $xmlVendor->phone->_value;
	$vendorFax = $xmlVendor->fax->_value;
	unset($xmlVendor);
	$xmlDealer =&$xmlPO->dealer;
	$dealerID = $xmlDealer->sourceid->_value;
	$dealerAddressNum = $xmlDealer->addressnumber->_value;
	$dealerFirstName = $xmlDealer->dealerfirstname->_value;
	$dealerLastName = $xmlDealer->dealerlastname->_value;
	$dealerAdd1 = $xmlDealer->address1->_value;
	$dealerAdd2 = $xmlDealer->address2->_value;
	$dealerCity = $xmlDealer->city->_value;
	$dealerSt = $xmlDealer->st->_value;
	$dealerZip = $xmlDealer->zip->_value;
	$dealerPhone = $xmlDealer->phone->_value;
	$dealerFax = $xmlDealer->fax->_value;
	unset($xmlDealer);
	$qtycount = 0;
	$xmlPO->debug();
	echo 'Itemgroups = '.count($xmlPO->itemgroup)."<br />\n";
	foreach($xmlPO->itemgroup as $linegroup) {
		echo 'Lineitems = '.count($linegroup->lineitem)."<br />\n";
		foreach($linegroup->lineitem as $lineitem) {
			$head[] = $linegroup->_param['header'];
			$orderid[] = $lineitem->_param['id'];
			$item[] = $lineitem->itemnumber->_value;
			$datetime[] = $lineitem->datetime->_value;
			$snapitem[] = $lineitem->snapitem->_value;
			$desc[] = $lineitem->description->_value;
			$qtystart[] = $qtycount;
			foreach($lineItem->qty as $qtygrp) {
				$qtyType[] = $qtygrp->_param['type'];
				$qtyAmt[] = $qtygrp->_param['amt'];
				if($qtygrp->_param['setqty']) {
					$qtySetQty[] = $qtygrp->_param['setqty'];
				} else {
					$qtySetQty[] = "n/a";
				}
				$qtyUnitPrice[] = $qtygrp->unitprice->_value;
				$qtycount++;
			}
		}
	}
	// This assignment is so the forthcoming while() loop doesn't try to evaluate an undefined variable.
	$qtystart[] = count($qtyType);
	
	$totalPieces = $xmlPO->totalpieces->_value;
	$approxVolume = $xmlPO->approxvolume->_value;
	$productTotal = $xmlPO->producttotal->_value;
	$discount_percentage = $xmlPO->discount->_param['percentage'];
	$discount = $xmlPO->discount->_value;
	$freight_percentage = $xmlPO->freight->_param['percentage'];
	$freight = $xmlPO->freight->_value;
	$total = $xmlPO->total->_value;
	$comments = $xmlPO->comments->_value;

	// Start building the SQL queries needed; display them for debugging as a start
	$locationT = strpos($entrydatetime, "T");
	$entrydate = substr($entrydatetime, 0, $locationT-1);
	$entrytime = substr($entrydatetime, $locationT+1, strlen($entrydatetime)-$locationT);
	if($processdatetime != "") {
		$processed = "Y";
		$locationT = strpos($processdatetime, "T");
		$processdate = substr($processdatetime, 0, $locationT-1);
		$processtime = substr($processdatetime, $locationT+1, strlen($processdatetime)-$locationT);
	} else {
	$processed = "N";
	$processdate = "0000-00-00";
	$processtime = "00:00:00";
	}
	
	// See if the dealer already exists in the db; if so, find the snapshot id for (possible) addition to the orders db
	
	$sql = "SELECT first_name, last_name, address, city, state, zip, phone, fax, snapshot, snapshot2 FROM users WHERE ID = '".$dealerID."'";
	echo $sql."<br />\n";
	$query = mysql_query($sql);
	checkdberror($sql);
	if($row = mysql_fetch_assoc($query)) {
		$sql2 = "SELECT * from snapshot_users WHERE orig_id = '".$dealerID."' and first_name = '".$dealerFirstName."' and last_name = '".$dealerLastName."' and address = '".$dealerAdd1."' and city = '".$dealerCity."' and state = '".$dealerSt."' and zip = '".$dealerZip."' and phone = '".$dealerPhone."' and fax = '".$dealerFax."' and secondary = '";
		if($dealerAddressNum == '1') {
			$sql2 .= "N'";
		} else {
			$sql2 .= "Y'";
		}
		echo "$sql2<br />\n";
		$query2 = mysql_query($sql2);
		checkdberror($sql2);
		if($row2 = mysql_fetch_assoc($query2)) {
			$snapuserid = $row2['id'];
			echo "Duplicate entry found in snapshots. Snapuserid = $snapuserid. No additions needed.<br />\n";
		} else {
			$sql3 = "SELECT MAX(ID) AS idmax FROM snapshot_users";
			$query3 = mysql_query($sql3);
			checkdberror($sql3);
			if($row = mysql_fetch_assoc($query3)) {
				$snapuserID = $row['idmax'] + 1;
			}
			$addsnapforuser = TRUE;
			echo "No duplicate entry found in snapshots. Need to add snapshot user # $snapuserID<br />\n";
		}
	} else {
		$sql3 = "SELECT MAX(ID) AS idmax FROM snapshot_users";
		$query3 = mysql_query($sql3);
		checkdberror($sql3);
		if($row = mysql_fetch_assoc($query3)) {
			$snapuserID = $row['idmax'] + 1;
		}
		$dealerID = 0;
		echo "Create snapshot user # $snapuserID with orig_id = 0<br />\n";
	}

	$sql = "SELECT * FROM forms WHERE ID = '".$vendorID."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	if($row = mysql_fetch_assoc($query)) {
		$sql2 = "SELECT id FROM snapshot_forms WHERE orig_id = '$vendorID' and name = '$vendorName' and address = '$vendorAdd1' and city = '$vendorCity' and state = '$vendorSt' and zip = '$vendorZip'";
		$query2 = mysql_query($sql2);
		checkdberror($sql2);
		$lastID = 0;
		while($row2 = mysql_fetch_assoc($query2)) {
			$snapformID = $row2['id'];
		}
		if($snapformID != 0) {
			echo 'Original form (id = '.$row['ID'].') and snapshot form (id = '.$snapformID.') found. No additions necessary.<br />'."\n";
		} else {
			$sql3 = 'SELECT MAX(id) AS idmax FROM snapshot_forms';
			$query3 = mysql_query($sql3);
			checkdberror($sql3);
			if($row = mysql_fetch_assoc($query3)) {
				$snapformID = $row['idmax'] + 1;
			}
			$addsnapforform = TRUE;
			echo 'Original form (id = '.$row['ID'].") found, but snapshot form not found. Add snapshot form (id $snapformID).<br />\n";
		}
	} else {
		$sql3 = 'SELECT MAX(id) AS idmax FROM snapshot_forms';
		$query3 = mysql_query($sql3);
		checkdberror($sql3);
		if($row = mysql_fetch_assoc($query3)) {
			$snapformID = $row['idmax'] + 1;
		}
		$addsnapformonly = TRUE;
		echo "Original form not found, nor was snapshot form found. Add snapshot form (id $snapformID).<br />\n";
	}	
	
			
//       Start for the actual INSERT statements

	if(!($addsnapforuser || $addsnapuseronly || $addsnapforform || $addsnapformonly)) {
		$sql = "INSERT INTO order_forms (ID, ordered, snapshot_user, comments, freight_percentage, discount_percentage, type, total, processed, process_time, deleted, user, snapshot_form, user_address, form) VALUES ('".$po."', '".$entrydate."', '".$snapuserid."', '".$comments."', ".$freight_percentage.", ".$discount_percentage.", '".$type."', ".$total.", '".$processed."', '".$processdate." ".$processtime."', '".$deleted."', '".$dealerID."', '".$formID."', '".$dealerAddressNum."', '".$vendorID."')";
		echo $sql.'<br />'."\n".count($orderid);

		$j = 0;
		while($j < count($orderid)) {
			for($k = $qtystart[$j]; $k < $qtystart[$j + 1]; $k++) {
				switch ($qtyType[$k]) {
					case 'set':
						$setqty = $qtyAmt[$k];
						break;
					case 'box':
						$qty = $qtyAmt[$k];
						break;
					default:
						$mattqty = $qtyAmt[$k];
						break;
				}
			}
			$sql2 = "INSERT INTO orders (ID, user, setqty, mattqty, qty, item, ordered, form, po_id, ordered_time, snapshot_user, snapshot_form) VALUES ('".$orderid[$j]."', '$dealerID', $setqty, $mattqty, $qty, ".$item[$j].", '$entrydate', $vendorid, '$po', '$entrytime', '$snapuserid', '$snapformID')";
			echo $sql2.'<br />'."\n";
			$j++;
		}
	}
		
		die();
	





/*		$order_datetime = date("Y-m-d\TH:i:s", strtotime($row['ordered']));
		if($row['process_time'] != "0000-00-00 00:00:00") {
			$process_datetime = date("Y-m-d\TH:i:s", strtotime($row['ordered']));
		} else {
			$process_datetime = "";
		}
		if(!$nextID) {
			$snapshot_user = $snapuserid;
		} else {
			$snapshot_user = $nextID;
		}
		
	}
	
	
	$sql = "SELECT `snapshot`, `snapshot2` FROM `users` WHERE ID = '".$user3."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	$row = mysql_fetch_assoc($query);
*/

// End parseXML($xmlObj)
}

?>