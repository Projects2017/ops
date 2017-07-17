<?php
//require(dirname(dirname(__FILE__)).'/database.php'); 
//require(dirname(dirname(__FILE__)).'/config.default.php');

//get the querystring, this will be a passed in value, possibly an array
//$qs = $_GET['id'];
//call the function
//but first check the site-level boolean to see if we're supposed to process!

// check to see if the global boolean flag is turned on


//call the function
//processCorsicanaXML($po);


//processCorsicanaXML is the function to loop 
//the passed in array of PO numbers
//It will, in turn call the createCorsicanaXML function to
//actually build XML files for shipment to Corsicana
// ---returns an array of XML file names

function processCorsicanaXML($po) {
	 if ($GLOBALS['corsicana_processXML'] == false) return;
// this function loops through (if applicable) an array of POs, and passes them
// to the createCorsicanaXML function that actually builds the XML
// it will return a list of XML file names to be sent to Corsicana via SFTP

 			//check to see it's an array or no
			if (is_array($po)) {
				//$arr = $po
				$tmparray = array();
				$counter = 1;
 				foreach ($po as $myval) {
 					//process everything now
 					//the createCorsicanaXML will return the XML, so we'll add it to the array to be returned
					$tmparray[$counter] = createCorsicanaXML($myval);
					$counter++;
 				}
 				return $tmparray;
 			} else {
				//just a single PO
 				return createCorsicanaXML($po);
 			} //ends the if on line 21
 			
 } //end function

//the createCorsicanaXML function actually builds XML files for shipment to Corsicana
//and saves them to /docs/XML
 			
function createCorsicanaXML($po) {
			// Checking to make sure we have valid input.
			 if (!is_numeric($po)) {
		 		die("Not a numeric PO#!");
		 	}
			
			$po_id = $po - 1000;
		
			//create query to get the ordered date
			$sql = "SELECT ordered FROM order_forms where ID=$po_id";
			$result = mysql_query($sql);
			checkDBError($sql, true, __FILE__, __LINE__);
		
			$dbresult = mysql_fetch_array($result);
			if (!$dbresult) {
				echo "Couldn't find PO#".$po;
				die();
			}
				// create a new XML document
			$doc = new DomDocument('1.0', 'UTF-8');	
			
			// create root node
			$root = $doc->createElement('furn_po:order');
			$root = $doc->appendChild($root);
			
			//append name spaces	
			$root->setAttribute('xmlns:furn_po','http://support.furnishnet.com/xml/schemas/FurnPO_v1.8');
			$root->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
			$root->setAttribute('xmlns:fnParty','http://support.furnishnet.com/xml/schemas/fnParty_v1.4');
			$root->setAttribute('xmlns:fnBase','http://support.furnishnet.com/xml/schemas/fnBase_v1.5');
			$root->setAttribute('xmlns:fnItem','http://support.furnishnet.com/xml/schemas/fnItem_v1.5');
			$root->setAttribute('xsi:schemaLocation','http://support.furnishnet.com/xml/schemas/FurnPO_v1.8 http://support.furnishnet.com/xml/schemas/FurnXMLPO_v1.8.xsd');
			//$root->setAttribute('','http://support.furnishnet.com/xml/schemas/FurnXMLPO_v1.8.xsd');
			
			//now the header is completed, and we'll begin adding the rest of the fields
			//occ is the order chunk which includes:
			//document id (PO infor)
			//buyer, billto, seller, shipto,shipdates
			// - the order closes out, and the general line items begin
			
			//add the Order chunk
			 // add node for each row
		  	$occ = $doc->createElement('Order');
		  	$occ = $root->appendChild($occ);
		  	$occ->setAttribute('comments','');
		
			//populate the general PO information
			$poinf = $doc->createElement('document');
			$poinf = $occ->appendChild($poinf);
			$poinf->setAttribute('id',$po);
			$poinf->setAttribute('status','Original');
			$poinf->setAttribute('type','850');
			$poinf->setAttribute('language','US');
			//get create date from database
			$createDate = $doc->createElement('creationDate');
			$createDate = $poinf->appendChild($createDate);
			//get just the date from db
			//append this UNDER document
			$cdateval = left($dbresult['ordered'],10);
			$createDateVal = $doc->createTextNode($cdateval);
			$createDateVal = $createDate->appendChild($createDateVal);
			
			//create currency field - dunno if Corsicana needs it or not
			$valCurr = $doc->createElement('currency');
			$valCurr = $poinf->appendChild($valCurr);
			//add USD as currency
			//get create date from database
			$valCurrT = $doc->createTextNode('USD');
			$valCurrT = $valCurr->appendChild($valCurrT);
			
			//buyer section - this is some "partyidentifer" number
			$valBuyer = $doc->createElement('buyer');
			$valBuyer = $occ->appendChild($valBuyer);
			$valBuyIDSend = $doc->createElement('fnParty:partyIdentifier');
			$valBuyIDSend = $valBuyer->appendChild($valBuyIDSend);
			$valBuyIDSend->setAttribute('partyIdentifierCode','6145830650');
			$valBuyIDSend->setAttribute('partyIdentifierQualifierCode','SenderAssigned');
			$valBuyIDRec = $doc->createElement('fnParty:partyIdentifier');
			$valBuyIDRec = $valBuyer->appendChild($valBuyIDRec);
			$valBuyIDRec->setAttribute('partyIdentifierCode','6145830650');
			$valBuyIDRec->setAttribute('partyIdentifierQualifierCode','ReceiverAssigned');
		
			//billTo section
			$valBillTo = $doc->createElement('billTo');
			$valBillTo = $occ->appendChild($valBillTo);
			$valBTIDSend = $doc->createElement('fnParty:partyIdentifier');
			$valBTIDSend = $valBillTo->appendChild($valBTIDSend);
			$valBTIDSend->setAttribute('partyIdentifierCode','6145830650');
			$valBTIDSend->setAttribute('partyIdentifierQualifierCode','SenderAssigned');
			
			$valBuyPN = $doc->createElement('fnParty:partyName');
			$valBuyPN = $valBillTo->appendChild($valBuyPN);
			$valPNText = $doc->createTextNode($GLOBALS['companyname']);
			$valPNText = $valBuyPN->appendChild($valPNText);
		
			$valBuyAdd = $doc->createElement('fnParty:addressLine');
			$valBuyAdd = $valBillTo->appendChild($valBuyAdd);
			$valAddText = $doc->createTextNode($GLOBALS['companyaddress']);
			$valAddText = $valBuyAdd->appendChild($valAddText);

			$valBuyC = $doc->createElement('fnParty:city');
			$valBuyC = $valBillTo->appendChild($valBuyC);
			$valCText = $doc->createTextNode($GLOBALS['companycity']);
			$valCText = $valBuyC->appendChild($valCText);
		
			$valBuyS = $doc->createElement('fnParty:stateOrProvince');
			$valBuyS = $valBillTo->appendChild($valBuyS);
			$valSText = $doc->createTextNode($GLOBALS['companystate']);
			$valSText = $valBuyS->appendChild($valSText);
		
			$valBuyZ = $doc->createElement('fnParty:postalCode');
			$valBuyZ = $valBillTo->appendChild($valBuyZ);
			$valZText = $doc->createTextNode($GLOBALS['companyzip']);
			$valZText = $valBuyZ->appendChild($valZText);
			
			//start seller section
			$valSeller = $doc->createElement('seller');
			$valSeller = $occ->appendChild($valSeller);
			
			$valSellID = $doc->createElement('sellerIdentification');
			$valSellID = $valSeller->appendChild($valSellID);
			
			$valSellSend = $doc->createElement('fnParty:partyIdentifier');
			$valSellSend = $valSellID->appendChild($valSellSend);
			$valSellSend->setAttribute('partyIdentifierCode','052738531');
			$valSellSend->setAttribute('partyIdentifierQualifierCode','DUNS');
			$valSellSend2 = $doc->createElement('fnParty:partyIdentifier');
			$valSellSend2 = $valSellID->appendChild($valSellSend2);
			$valSellSend2->setAttribute('partyIdentifierCode','');
			$valSellSend2->setAttribute('partyIdentifierQualifierCode','SenderAssigned');	
			
			//query for ship to information
			$sql = "SELECT shipto,snapshot_user FROM order_forms where ID=".$po_id;
			$shipresult = mysql_query($sql);
			checkDBError($sql, true, __FILE__, __LINE__);
			
			while($rowst = mysql_fetch_array($shipresult, MYSQL_ASSOC))
			{
				$shiptocheck = $rowst['shipto'];
				if (!$shiptocheck) {
					$shipid = $rowst['snapshot_user'];
				}else{
					$shipid = $rowst['shipto'];
				}
			}
			
			//now we've got the right ID to use, let's query for the shipping information
			$shipsql = "SELECT first_name,last_name,address,address2,city,state,zip FROM snapshot_users where ID=".$shipid;
			$shiptoresult = mysql_query($shipsql);
			checkDBError($shipsql, true, __FILE__, __LINE__);
			
			while($rowto = mysql_fetch_array($shiptoresult, MYSQL_ASSOC))
			{
				$shipname = $rowto['first_name'] . ' ' . $rowto['last_name'];
				$shipadd = $rowto['address'];
				$shipadd2 = $rowto['address2'];
				$shipcity = $rowto['city'];
				$shipstate = $rowto['state'];
				$shipzip = $rowto['zip'];
			}
			
			//ship to section
			$valShip = $doc->createElement('shipTo');
			$valShip = $occ->appendChild($valShip);
			$valShip->setAttribute('id','1');
			
			$valShipID = $doc->createElement('fnParty:partyIdentifier');
			$valShipID = $valShip->appendChild($valShipID);
			$valShipID->setAttribute('partyIdentifierCode','270');
			$valShipID->setAttribute('partyIdentifierQualifierCode','ReceiverAssigned');
			
			$valShipID2 = $doc->createElement('fnParty:partyIdentifier');
			$valShipID2 = $valShip->appendChild($valShipID2);
			$valShipID2->setAttribute('partyIdentifierCode','270');
			$valShipID2->setAttribute('partyIdentifierQualifierCode','SenderAssigned');	
			
			$valShipPN = $doc->createElement('fnParty:partyName');
			$valShipPN = $valShip->appendChild($valShipPN);
			$valShipPNText = $doc->createTextNode($shipname);
			$valShipPNText = $valShipPN->appendChild($valShipPNText);
		
			$valShipAdd = $doc->createElement('fnParty:addressLine');
			$valShipAdd = $valShip->appendChild($valShipAdd);
			$valShipAddText = $doc->createTextNode($shipaddress);
			$valShipAddText = $valShipAdd->appendChild($valShipAddText);

			$valShipC = $doc->createElement('fnParty:city');
			$valShipC = $valShip->appendChild($valShipC);
			$valShipCText = $doc->createTextNode($shipcity);
			$valShipCText = $valShipC->appendChild($valShipCText);
		
			$valShipS = $doc->createElement('fnParty:stateOrProvince');
			$valShipS = $valShip->appendChild($valShipS);
			$valShipSText = $doc->createTextNode($shipstate);
			$valShipSText = $valShipS->appendChild($valShipSText);
		
			$valShipZ = $doc->createElement('fnParty:postalCode');
			$valShipZ = $valShip->appendChild($valShipZ);
			$valShipZText = $doc->createTextNode($shipzip);
			$valShipZText = $valShipZ->appendChild($valShipZText);
		
			//requested Ship Date section - this is actually blank I guess
			$valShip = $doc->createElement('shipDate');
	
			unset($sql);
			//create query to get the ordered date
			$sql = "SELECT partno,sku, description, qty + `orders`.setqty + `orders`.mattqty as oqty FROM `orders` INNER JOIN snapshot_items on `orders`.item = snapshot_items.id WHERE `orders`.po_id=$po_id";
			//echo $sql;
			$result = mysql_query($sql);
			checkDBError($sql, true, __FILE__, __LINE__);
			$i=1;
			
			// process one row at a time
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				// add a child node for each field - loop through data
					//create the line item
			  		$child = $doc->createElement('Line');
			  		$child->setAttribute('lineItemNumber',$i++);
			  		//can also do a comment 	$child->setAttribute('comment','');
			    	$child = $root->appendChild($child);
			    	
			    	//now add the productID item to the line item
			    	$value = $doc->createElement('productID');
			    	$value = $child->appendChild($value);
			    	
			    	//requested quantity which actually holds the requested quantity
			    	$qvalue = $doc->createElement('requestedQuantity');
			    	$qvalue = $child->appendChild($qvalue);
			    	//add quantity fields
			    	$qvalue->setAttribute('unitOfMeasure','Each');
			    	$qvalue->setAttribute('value',$line['oqty']);
			    	
			    	$qRecQty = $doc->createElement('shipToLocation');
			    	$qRecQty = $qvalue->appendChild($qRecQty);
			    	
			    	//add shipto 
					$qRecQty->setAttribute('shipToID','1');	
					$qRecQty->setAttribute('quantity','0');	
			    	$qRecQty->setAttribute('arrivalDate',date('Y-m-d'));
		
			    	//unit pricing - although this is blank
			    	$pvalue = $doc->createElement('unitPrice');
			    	$pvalue = $child->appendChild($pvalue);
			    	//price - although this is blank
			    	$prvalue = $doc->createElement('price');
			    	$prvalue = $pvalue->appendChild($prvalue);
	    	
	    			//now we will append 
			    	//1.  itemIdentifier Buyer info to the list - I believe this will be PMD's SKU
			    	//2.  itemIdentifier should be Corsicana SKU - this may change if we add others
			    	//3.  itemdescription
			    	//4.  blank line for specialhandling
			    	//5.  blank line for hazardous materials
			    	$itemIDB = $doc->createElement('itemIdentifier');	    	
			    	$itemIDB = $value->appendChild($itemIDB);
			    	$itemIDB->setAttribute('itemNumber',$line['partno']);
			    	$itemIDB->setAttribute('itemNumberQualifier','BuyerAssigned');
			    	
			    	//2.  
			    	$itemIDS = $doc->createElement('itemIdentifier');	    	
			    	$itemIDS = $value->appendChild($itemIDS);
			    	$itemIDS->setAttribute('itemNumber',$line['sku']);
			    	$itemIDS->setAttribute('itemNumberQualifier','SellerAssigned');
			
					//3.
					$itemDesc = $doc->createElement('itemDescription');	    	
			    	$itemDesc = $value->appendChild($itemDesc);
			    	$itemDesc->setAttribute('descriptionValue',$line['description']);
			    	$itemDesc->setAttribute('itemDescriptionQualifier','BuyerAssigned');
			    	$itemDesc->setAttribute('itemDescriptionClassification','Product');
	    	
					//4.
					$specHan = $doc->createElement('specialHandlingInstructions');	    	
			    	$specHan = $value->appendChild($specHan);
			
					//5.
					$hazMat = $doc->createElement('hazardousMaterialsInformation');	    	
			    	$hazMat = $value->appendChild($hazMat);
			    	
				} // end while
	
				//now get the complete XML document as a string
				//now we can print it out, save to a file, etc
				$xml_string = $doc->saveXML();
				//build filename
				$filename = dirname(dirname(__FILE__))."/doc/corsicana/".$po.".xml";
				// save XML tree to file
				//fopen($filename, 'w') or die("can't open file");
				$doc->formatOutput = true;
				$doc->save($filename);
				chmod($filename, 0777);

				//return the value so it can be added to the array
				return $po.".xml";
			}

//miscellaneous function to imitate VB.NET's left function
function left($str, $length) {
return substr($str, 0, $length);
}

?>
