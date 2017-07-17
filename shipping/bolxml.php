<?php

// xml.php

// A repository of XML-related functions


// Contains the class for parsing XML data into an object
require('../admin/XML.inc.php');



/* OrderToXML ($po, $header)

	This function creates the XML code for the individual POs.
	
	Attributes:
	
		po (int)			Order number
		header (boolean)	Determines whether the XML file header is added with each order number reading
							This should only be TRUE for the first iteration	
*/

function OrderToXML($po, $header, $contain)
{

	/* Adapted from OrderForWeb ($po, $section), only changed to output to an XML file using the Purchase Order XML schema as
	defined in the po.dtd Document Type Definition file. */
	
	if (!is_numeric($po)) {
		die("Invalid PO#".$po);
	}

	/* Start the XML file
	We begin with the initial XML version line,
	followed by the DTD declaration.
	Make sure the DTD gets to the path, or we change the path to be the correct one. */
	
	$xmlOutput = "";
	
	if($header) {
		$xmlOutput = '<?phpxml version="1.0" ?>'."\n";
		$xmlOutput .= '<!DOCTYPE po SYSTEM "http://www.pmddealer.com/xml/po.dtd">'."\n";
	}
	if($contain) {
		$xmlOutput .= "<orders>\n";
	}
	
	/* get basic order information and variables */
	$sql = "SELECT * FROM order_forms WHERE ID='".$po."'";
	$query = mysql_query($sql);
	if (!mysql_num_rows($query)) {
		die("PO# $po Not Found");
	}
	$result = mysql_fetch_array($query);
	//ADD ERROR CHECK FOR NO ROWS RETURNED HERE
	$processed = $result['processed'];
	// Get order datetime
	$orderDatetime = $result['ordered'];
	// Get process datetime
	$processDatetime = $result['process_time'];
	$origForm = $result['form'];
	$origUser = $result['user'];
	$comments = $result['comments'];
	$freightPercentage = $result['freight_percentage'];
	$discountPercentage = $result['discount_percentage'];
	$total = $result['total'];
	$type = $result['type'];
	$emailVendorDate = $result['email_vendor'];
	$deleted = $result['deleted'];
	$dealerAddressCode = $result['user_address'];
	$snapUser = $result['snapshot_user'];
	$snapForm = $result['snapshot_form'];



	/* Self Heal from bad code (Dealer) */
	if ($snapUser == NULL && $origUser) {
		$sql = "SELECT `snapshot`, `snapshot2` FROM `users` WHERE ID = '".$origUser."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$row = mysql_fetch_assoc($query);
		if ($dealerAddressCode == 2) {
			$row['snapshot'] = $row['snapshot2'];
		}
		$sql = "UPDATE `orders` SET `snapshot_user` = '".$row['snapshot']."' WHERE po_id = '".$po."'";
		$snapUser = $row['snapshot'];
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `order_forms` SET `snapshot_user` = '".$row['snapshot']."' WHERE ID = '".$po."'";
		mysql_query($sql);
		checkdberror($sql);
	}
	/* Self Heal from bad code (Vendor) */
	if ($snapForm == NULL && $origForm) {
		$sql = "SELECT `snapshot` FROM `forms` WHERE ID = '".$origForm."'";
		$query = mysql_query($sql);
		checkdberror($sql);
		$row = mysql_fetch_assoc($query);
		$sql = "UPDATE `orders` SET `snapshot_form` = '".$row['snapshot']."' WHERE po_id = '".$po."'";
		$snapForm = $row['snapshot'];
		mysql_query($sql);
		checkdberror($sql);
		$sql = "UPDATE `order_forms` SET `snapshot_form` = '".$row['snapshot']."' WHERE ID = '".$po."'";
		mysql_query($sql);
		checkdberror($sql);
	}
	/* get dealer name and address */
	if ($dealerAddressCode == 2)
		$sql = "SELECT * FROM snapshot_users WHERE ID='$snapUser' AND secondary='Y'";
	else
		$sql = "SELECT * FROM snapshot_users WHERE ID='$snapUser' AND secondary='N'";
	$query = mysql_query($sql);
	checkDBError($sql);
	$userPhone = '';
	if ($result = mysql_fetch_assoc($query)) {
		$dealerOrigId = $result['orig_id'];
		$dealerLastName = $result['last_name'];
		$dealerFirstName = $result['first_name'];
		if($result['address'] != "") {
			$dealerAddress1 = $result['address'];		
		// Placed for the future when a second address line is in effect	
			$dealerAddress2 = "";
		// End placement
			$dealerCity = $result['city'];		
			$dealerSt = $result['state'];
			$dealerZip = $result['zip'];
		} else {
			$dealerAddress1 = "";
			$dealerAddress2 = "";
			$dealerCity = "";
			$dealerSt = "";
			$dealerZip = "";
		}
		$dealerPhone = $result['phone'];
		$dealerFax = $result['fax'];
		$dealerSecondary = $result['secondary'];
	} else {
		$dealerOrigId = "--NULL--";
	}
			
	/* get vendor address */
	$sql = "SELECT * FROM snapshot_forms WHERE id='".$snapForm."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if($result = mysql_fetch_assoc($query)) {
		$vendorName = $result['name'];
		$vendorOrigId = $result['orig_id'];
		$vendorOrigVendor = $result['orig_vendor'];
		if($result['address'] != "") {
			$vendorAddress1 = $result['address'];		
			// Placed for the future when a second address line is in effect	
			$vendorAddress2 = "";
			// End placement
			$vendorCity = $result['city'];
			$vendorSt = $result['state'];
			$vendorZip = $result['zip'];
		} else {
			$vendorAddress1 = "";
			$vendorAddress2 = "";
			$vendorCity = "";
			$vendorSt = "";
			$vendorZip = "";
		}
		$vendorPhone = $result['phone'];
		$vendorFax = $result['fax'];
		$vendorEmail1 = $result['email'];
		$vendorEmail2 = $result['email2'];
		$vendorFreightPrepaid = $result['prepaidfreight'];
		$vendorDiscount = $result['discount'];
		$vendorProcUrl = $result['proc_url'];
	} else {
		$vendorOrigId = "--NULL--";
	}
	
	
	/* Begin building the actual XML data elements now  */

	$xmlOutput .= '<po>'."\n";
	$xmlOutput .= "\t<ID>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>ID</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$po</value>\n";
	$xmlOutput .= "\t</ID>\n";
	$xmlOutput .= "\t<orderdatetime>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>ordered</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$orderDatetime</value>\n";
	$xmlOutput .= "\t</orderdatetime>\n";
	$xmlOutput .= "\t<processed>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>processed</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$processed</value>\n";
	$xmlOutput .= "\t</processed>\n";
	$xmlOutput .= "\t<processdatetime>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>process_time</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$processDatetime</value>\n";
	$xmlOutput .= "\t</processdatetime>\n";
	$xmlOutput .= "\t<form>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>form</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$origForm</value>\n";
	$xmlOutput .= "\t</form>\n";
	$xmlOutput .= "\t<user>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>user</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$origUser</value>\n";
	$xmlOutput .= "\t</user>\n";
	$xmlOutput .= "\t<comments>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>comments</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$comments</value>\n";
	$xmlOutput .= "\t</comments>\n";
	$xmlOutput .= "\t<freightpercentage>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>freight_percentage</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$freightPercentage</value>\n";
	$xmlOutput .= "\t</freightpercentage>\n";
	$xmlOutput .= "\t<discountpercentage>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>discount_percentage</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$discountPercentage</value>\n";
	$xmlOutput .= "\t</discountpercentage>\n";
	$xmlOutput .= "\t<total>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>total</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$total</value>\n";
	$xmlOutput .= "\t</total>\n";
	$xmlOutput .= "\t<type>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>type</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$type</value>\n";
	$xmlOutput .= "\t</type>\n";
	$xmlOutput .= "\t<deleted>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>deleted</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$deleted</value>\n";
	$xmlOutput .= "\t</deleted>\n";
	$xmlOutput .= "\t<emaildate>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>email_vendor</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$emailVendorDate</value>\n";
	$xmlOutput .= "\t</emaildate>\n";
	$xmlOutput .= "\t<dealeraddressnumber>\n";
	$xmlOutput .= "\t\t<field>\n";
	$xmlOutput .= "\t\t\t<name>user_address</name>\n";
	$xmlOutput .= "\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t</field>\n";
	$xmlOutput .= "\t\t<value>$dealerAddressCode</value>\n";
	$xmlOutput .= "\t</dealeraddressnumber>\n";
	$xmlOutput .= "\t<vendor>\n";
	$xmlOutput .= "\t\t<ID>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>id</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<link>\n";
	$xmlOutput .= "\t\t\t\t<name>snapshot_form</name>\n";
	$xmlOutput .= "\t\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t\t</link>\n";
	$xmlOutput .= "\t\t\t<value>$snapForm</value>\n";
	$xmlOutput .= "\t\t</ID>\n";
	$xmlOutput .= "\t\t<origid>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>orig_id</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorOrigId</value>\n";
	$xmlOutput .= "\t\t</origid>\n";
	$xmlOutput .= "\t\t<origvendor>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>orig_vendor</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorOrigVendor</value>\n";
	$xmlOutput .= "\t\t</origvendor>\n";
	$xmlOutput .= "\t\t<vendorname>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>name</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorName</value>\n";
	$xmlOutput .= "\t\t</vendorname>\n";
	$xmlOutput .= "\t\t<address1>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>address</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorAddress1</value>\n";
	$xmlOutput .= "\t\t</address1>\n";
	$xmlOutput .= "\t\t<address2>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name></name>\n";
	$xmlOutput .= "\t\t\t\t<table></table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorAddress2</value>\n";
	$xmlOutput .= "\t\t</address2>\n";
	$xmlOutput .= "\t\t<city>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>city</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorCity</value>\n";
	$xmlOutput .= "\t\t</city>\n";
	$xmlOutput .= "\t\t<st>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>state</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorSt</value>\n";
	$xmlOutput .= "\t\t</st>\n";
	$xmlOutput .= "\t\t<zip>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>zip</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorZip</value>\n";
	$xmlOutput .= "\t\t</zip>\n";
	$xmlOutput .= "\t\t<phone>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>phone</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorPhone</value>\n";
	$xmlOutput .= "\t\t</phone>\n";
	$xmlOutput .= "\t\t<fax>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>fax</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorFax</value>\n";
	$xmlOutput .= "\t\t</fax>\n";
	$xmlOutput .= "\t\t<email>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>email</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorEmail1</value>\n";
	$xmlOutput .= "\t\t</email>\n";
	$xmlOutput .= "\t\t<email2>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>email2</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorEmail2</value>\n";
	$xmlOutput .= "\t\t</email2>\n";
	$xmlOutput .= "\t\t<freightprepaid>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>prepaidfreight</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorFreightPrepaid</value>\n";
	$xmlOutput .= "\t\t</freightprepaid>\n";
	$xmlOutput .= "\t\t<discount>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>discount</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_forms</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$vendorDiscount</value>\n";
	$xmlOutput .= "\t\t</discount>\n";
	$xmlOutput .= "\t</vendor>\n";
	
	// End Vendor
	
	$xmlOutput .= "\t<dealer>\n";
	$xmlOutput .= "\t\t<ID>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>id</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<link>\n";
	$xmlOutput .= "\t\t\t\t<name>snapshot_user</name>\n";
	$xmlOutput .= "\t\t\t\t<table>order_forms</table>\n";
	$xmlOutput .= "\t\t\t</link>\n";
	$xmlOutput .= "\t\t\t<value>$snapUser</value>\n";
	$xmlOutput .= "\t\t</ID>\n";
	$xmlOutput .= "\t\t<origid>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>orig_id</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerOrigId</value>\n";
	$xmlOutput .= "\t\t</origid>\n";
	$xmlOutput .= "\t\t<dealerfirstname>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>first_name</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerFirstName</value>\n";
	$xmlOutput .= "\t\t</dealerfirstname>\n";
	$xmlOutput .= "\t\t<dealerlastname>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>last_name</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerLastName</value>\n";
	$xmlOutput .= "\t\t</dealerlastname>\n";
	$xmlOutput .= "\t\t<address1>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>address</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerAddress1</value>\n";
	$xmlOutput .= "\t\t</address1>\n";
	$xmlOutput .= "\t\t<address2>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name></name>\n";
	$xmlOutput .= "\t\t\t\t<table></table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerAddress2</value>\n";
	$xmlOutput .= "\t\t</address2>\n";
	$xmlOutput .= "\t\t<city>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>city</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerCity</value>\n";
	$xmlOutput .= "\t\t</city>\n";
	$xmlOutput .= "\t\t<st>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>state</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerSt</value>\n";
	$xmlOutput .= "\t\t</st>\n";
	$xmlOutput .= "\t\t<zip>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>zip</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerZip</value>\n";
	$xmlOutput .= "\t\t</zip>\n";
	$xmlOutput .= "\t\t<phone>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>phone</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerPhone</value>\n";
	$xmlOutput .= "\t\t</phone>\n";
	$xmlOutput .= "\t\t<fax>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>fax</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerFax</value>\n";
	$xmlOutput .= "\t\t</fax>\n";
	$xmlOutput .= "\t\t<secondary>\n";
	$xmlOutput .= "\t\t\t<field>\n";
	$xmlOutput .= "\t\t\t\t<name>secondary</name>\n";
	$xmlOutput .= "\t\t\t\t<table>snapshot_users</table>\n";
	$xmlOutput .= "\t\t\t</field>\n";
	$xmlOutput .= "\t\t\t<value>$dealerSecondary</value>\n";
	$xmlOutput .= "\t\t</secondary>\n";
	$xmlOutput .= "\t</dealer>\n";
	
	
	/* It's time to get more data....we'll leave the XML for now. */
	
	
		$sql = "SELECT DISTINCT orders.ID, orders.user, orders.setqty, orders.mattqty, orders.qty, orders.item, orders.ordered, orders.form, orders.po_id, orders.ordered_time, orders.snapshot_user, orders.snapshot_form, snapshot_items.id, snapshot_items.orig_id, snapshot_items.header, snapshot_items.partno, snapshot_items.description, snapshot_items.price, snapshot_items.size, snapshot_items.color, snapshot_items.set_, snapshot_items.matt, snapshot_items.box, snapshot_items.display_order, snapshot_items.cubic_ft, snapshot_items.setqty AS qtyinset FROM orders INNER JOIN snapshot_items ON snapshot_items.id=orders.item WHERE orders.po_id='$po' ORDER BY snapshot_items.header, snapshot_items.display_order";
		// "DISTINCT" added 3/16/04. Somehow, duplicate items are occasionally being added to the snapshots table.
		// Since the cause of this can't be found, we'll fix the problem here.
		$query = mysql_query($sql);

		$itemgroup = 0;

/* Iterate through all of the rows, building the XML format one line at a time. */
		
		while($result = mysql_fetch_assoc($query)) {
	
			if($itemgroup == 0) {
				$xmlOutput .= "\t<itemgroup>\n";
				$xmlOutput .= "\t\t<ID>\n";
				$xmlOutput .= "\t\t\t<field>\n";
				$xmlOutput .= "\t\t\t\t<name>header</name>\n";
				$xmlOutput .= "\t\t\t\t<table>snapshot_items</table>\n";
				$xmlOutput .= "\t\t\t</field>\n";
				$xmlOutput .= "\t\t\t<value>".$result['header']."</value>\n";
				$xmlOutput .= "\t\t</ID>\n";
			}
			elseif($itemgroup != $result['header']) {
				$xmlOutput .= "\t</itemgroup>\n";
				$xmlOutput .= "\t<itemgroup>\n";
				$xmlOutput .= "\t\t<ID>\n";
				$xmlOutput .= "\t\t\t<field>\n";
				$xmlOutput .= "\t\t\t\t<name>header</name>\n";
				$xmlOutput .= "\t\t\t\t<table>snapshot_items</table>\n";
				$xmlOutput .= "\t\t\t</field>\n";
				$xmlOutput .= "\t\t\t<value>".$result['header']."</value>\n";
				$xmlOutput .= "\t\t</ID>\n";
				}
			$itemgroup = $result['header'];
			$xmlOutput .= "\t\t<lineitem>\n";
			$xmlOutput .= "\t\t\t<ID>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>ID</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['ID']."</value>\n";
			$xmlOutput .= "\t\t\t</ID>\n";
			$xmlOutput .= "\t\t\t<item>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>item</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<link>\n";
			$xmlOutput .= "\t\t\t\t\t<name>id</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</link>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['item']."</value>\n";
			$xmlOutput .= "\t\t\t</item>\n";
			$xmlOutput .= "\t\t\t<origid>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>orig_id</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['orig_id']."</value>\n";
			$xmlOutput .= "\t\t\t</origid>\n";
			$xmlOutput .= "\t\t\t<partno>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>partno</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['partno']."</value>\n";
			$xmlOutput .= "\t\t\t</partno>\n";
			$xmlOutput .= "\t\t\t<description>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>description</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".addslashes(htmlentities($result['description']))."</value>\n";
			$xmlOutput .= "\t\t\t</description>\n";
			$xmlOutput .= "\t\t\t<price>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>price</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['price']."</value>\n";
			$xmlOutput .= "\t\t\t</price>\n";
			$xmlOutput .= "\t\t\t<size>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>size</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['size']."</value>\n";
			$xmlOutput .= "\t\t\t</size>\n";
			$xmlOutput .= "\t\t\t<color>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>color</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['color']."</value>\n";
			$xmlOutput .= "\t\t\t</color>\n";
			$xmlOutput .= "\t\t\t<itemset>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>set_</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['set_']."</value>\n";
			$xmlOutput .= "\t\t\t</itemset>\n";
			$xmlOutput .= "\t\t\t<matt>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>matt</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['matt']."</value>\n";
			$xmlOutput .= "\t\t\t</matt>\n";
			$xmlOutput .= "\t\t\t<box>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>matt</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['matt']."</value>\n";
			$xmlOutput .= "\t\t\t</box>\n";
			$xmlOutput .= "\t\t\t<displayorder>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>display_order</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['display_order']."</value>\n";
			$xmlOutput .= "\t\t\t</displayorder>\n";
			$xmlOutput .= "\t\t\t<cubicft>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>cubic_ft</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['cubic_ft']."</value>\n";
			$xmlOutput .= "\t\t\t</cubicft>\n";
			$xmlOutput .= "\t\t\t<qtyinset>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>setqty</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>snapshot_items</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['qtyinset']."</value>\n";
			$xmlOutput .= "\t\t\t</qtyinset>\n";
			$xmlOutput .= "\t\t\t<setqty>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>setqty</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['setqty']."</value>\n";
			$xmlOutput .= "\t\t\t</setqty>\n";
			$xmlOutput .= "\t\t\t<mattqty>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>mattqty</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['mattqty']."</value>\n";
			$xmlOutput .= "\t\t\t</mattqty>\n";
			$xmlOutput .= "\t\t\t<boxqty>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>qty</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['qty']."</value>\n";
			$xmlOutput .= "\t\t\t</boxqty>\n";
			$xmlOutput .= "\t\t\t<orderdate>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>ordered</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['ordered']."</value>\n";
			$xmlOutput .= "\t\t\t</orderdate>\n";
			$xmlOutput .= "\t\t\t<ordertime>\n";
			$xmlOutput .= "\t\t\t\t<field>\n";
			$xmlOutput .= "\t\t\t\t\t<name>ordered_time</name>\n";
			$xmlOutput .= "\t\t\t\t\t<table>orders</table>\n";
			$xmlOutput .= "\t\t\t\t</field>\n";
			$xmlOutput .= "\t\t\t\t<value>".$result['ordered_time']."</value>\n";
			$xmlOutput .= "\t\t\t</ordertime>\n";
			$xmlOutput .= "\t\t</lineitem>\n";
			
			/* We have the line item info now; loop back to the start for the next row */
			}
			
			if(mysql_num_rows($query) > 0) {
				$xmlOutput .= "\t</itemgroup>\n";
			}					
			
			$xmlOutput .= "</po>";			
	
	return $xmlOutput;
}
/* end OrderToXML */



function displayForm() {
	echo "<form enctype=\"multipart/form-data\" action=\"checkzip.php\" method=\"post\" name=\"sourceForm\" id=\"sourceForm\">
  <p>Import XML Files to RFE BoL Database</p>
  <p>Choose XML file source:  </p>
  <p>
    <label>
    <input type=\"radio\" name=\"chooseType\" checked value=\"server\" onchange=\"checkSelect()\" />
Server file</label>
    <br />
    <label>
    <input type=\"radio\" name=\"chooseType\" value=\"upload\" onchange=\"checkSelect()\" />
Upload file (.xml or .zip'd XML file(s))</label>
    <br />
	<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"2000000\" />
    <input type=\"file\" name=\"upload_file\" />
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
<title>RFE BoL XML Import'.$type;
if($title != "") {
	echo ' - '.$title;
}
echo '</title>
</head>
<body>';
}

function displayZIPs($files, $basedir) {
	global $xmldir;
	echo '<form enctype="multipart/form-data" action="do_importxml.php" method="post" name="chooseXML" id="chooseXML">
	<script language="javascript" src="xml.js" /><p>Select XML files to import</p>'; // First part of the form
	echo '<p><input type="submit" name="submit" value="Import XML records" />&nbsp;&nbsp;&nbsp;&nbsp;<input name="reset" type="reset" value="Reset Form" /></p>';
	$f = 0; // Counter for the files
	foreach($files as $file) {
		if(substr($file, -4) == ".zip") {
			echo '<p><label><input type="checkbox" name="all_'.$f.'" value="'.$file.'" onchange="chooseAll(\''.$f.'\')">All files from '.$file.'</label><input type="hidden" name="'.$f.'" value="'.$file.'"><br />'."\n";
			$zipfile = zip_open($xmldir.$file); // Open the zip file
			$k = 0; // Counter for the individual XML files
			echo "<div id=\"$f\">";
			while ($inner_file = zip_read($zipfile)) {
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="'.$f.'_'.$k.'" value="'.basename(zip_entry_name($inner_file)).'"><label>'.basename(zip_entry_name($inner_file)).'</label>'."\n".'<br />';
				$k++;
			}
			$f++;
			echo '</div></p>';
		} else {
			echo '<p><label><input type="checkbox" name="'.$f.'" value="'.$file.'_only">'.$file.'</label><br />'."\n";
			$f++;
		}
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
	$zipfile = zip_open($xmldir.$sourceZip);
	while($zipFiles = zip_read($zipfile)) {
		if(zip_entry_name($zipFiles) == $xml) {
			$go = zip_entry_open($zipfile, $zipFiles, "r");
			$export = zip_entry_read($zipFiles, zip_entry_filesize($zipFiles));
		}
	}
	return $export;
}



function makeOrders($xmlObj, $source="pmd") {
	if($xmlObj->orders) {
		foreach($xmlObj->orders->po as $po) {
			$rets[] = OrderFromXMLBoL($po, $source);
			unset($po);
			return $rets;
		}
	} else {
		return OrderFromXMLBoL(&$xmlObj, $source);
	}
}


function OrderFromXMLBoL($xmlPO_1, $source) {
	// start assigning XML object data values to variables
	// Basic order information from order_forms
  $xmlPO = &$xmlPO_1->po;
	$orig_po = $xmlPO->ID->value->_value;
	$displayPO = $orig_po + 1000;
	$orderDatetime = $xmlPO->orderdatetime->value->_value;
	$processedYN = $xmlPO->processed->value->_value;
	$processDatetime = $xmlPO->processdatetime->value->_value;
	$deleted = $xmlPO->deleted->value->_value;;
	$type = $xmlPO->type->value->_value;
	$freightPrepaid = $xmlPO->freightprepaid->_value;
	$vendorOrigId = $xmlPO->form->value->_value;
	$dealerOrigId = $xmlPO->user->value->_value;
	$comments = $xmlPO->comments->value->_value;
	$freightPercentage = $xmlPO->freightpercentage->value->_value;
	$discountPercentage = $xmlPO->discountpercentage->value->_value;	
	$total = $xmlPO->total->value->_value;
	$orderType = $xmlPO->type->value->_value;
	$orderDeleted = $xmlPO->deleted->value->_value;
	$emailDate = $xmlPO->emaildate->value->_value;		
	$dealerAddressNum = $xmlPO->dealeraddressnumber->value->_value;
	// Vendor-specific information from snapshot_forms
	$xmlVendor = &$xmlPO->vendor;
	$vendorSnapId = $xmlVendor->ID->value->_value;
	$vendorOrigId = $xmlVendor->origid->value->_value;
	$vendorOrigVendor = $xmlVendor->origvendor->value->_value;
	$vendorName = mysql_real_escape_string($xmlVendor->vendorname->value->_value);
	$vendorAdd1 = mysql_real_escape_string($xmlVendor->address1->value->_value);
	$vendorAdd2 = mysql_real_escape_string($xmlVendor->address2->value->_value);
	$vendorCity = mysql_real_escape_string($xmlVendor->city->value->_value);
	$vendorSt = $xmlVendor->st->value->_value;
	$vendorZip = $xmlVendor->zip->value->_value;
	$vendorPhone = $xmlVendor->phone->value->_value;
	$vendorFax = $xmlVendor->fax->value->_value;
	$vendorEmail1 = mysql_real_escape_string($xmlVendor->email->value->_value);
	$vendorEmail2 = $xmlVendor->email2->value->_value;
	$vendorFreightPrepaid = $xmlVendor->freightprepaid->value->_value;
	$vendorDiscount = $xmlVendor->discount->value->_value;
	unset($xmlVendor);
	$xmlDealer =&$xmlPO->dealer;
	$dealerSnapId = $xmlDealer->ID->value->_value;
	$dealerOrigId = $xmlDealer->origid->value->_value;
	$dealerFirstName = mysql_real_escape_string($xmlDealer->dealerfirstname->value->_value);
	$dealerLastName = mysql_real_escape_string($xmlDealer->dealerlastname->value->_value);
	$dealerAdd1 = mysql_real_escape_string($xmlDealer->address1->value->_value);
	$dealerAdd2 = mysql_real_escape_string($xmlDealer->address2->value->_value);
	$dealerCity = mysql_real_escape_string($xmlDealer->city->value->_value);
	$dealerSt = $xmlDealer->st->value->_value;
	$dealerZip = $xmlDealer->zip->value->_value;
	$dealerPhone = $xmlDealer->phone->value->_value;
	$dealerFax = $xmlDealer->fax->value->_value;
	$dealerSecondary = $xmlDealer->secondary->value->_value;
	unset($xmlDealer);
	static $qtycount = 0;
	static $qtystart = Array();
	if(count($xmlPO->itemgroup) > 1) {
		foreach($xmlPO->itemgroup as $linegroup) {
			if(count($linegroup->lineitem) > 1) {
				foreach($linegroup->lineitem as $lineitem) {
					$head[] = $linegroup->ID->value->_value;
					$orderid[] = $lineitem->ID->value->_value;
					$item[] = $lineitem->item->value->_value;
					$origid[] = $lineitem->origid->value->_value;
					$partno[] = $lineitem->partno->value->_value;
					$desc[] = $lineitem->description->value->_value;
					$price[] = $lineitem->price->value->_value;
					$size[] = $lineitem->size->value->_value;
					$color[] = $lineitem->color->value->_value;
					$itemset[] = $lineitem->itemset->value->_value;
					$matt[] = $lineitem->matt->value->_value;
					$box[] = $lineitem->box->value->_value;
					$displayorder[] = $lineitem->displayorder->value->_value;
					$cubicft[] = $lineitem->cubicft->value->_value;
					$qtyinset[] = $lineitem->qtyinset->value->_value;
					$setqty[] = $lineitem->setqty->value->_value;
					$mattqty[] = $lineitem->mattqty->value->_value;
					$boxqty[] = $lineitem->boxqty->value->_value;
					$orderdate[] = $lineitem->orderdate->value->_value;
					$ordertime[] = $lineitem->ordertime->value->_value;
				}
			} else {
				$head[] = $linegroup->ID->value->_value;
				$orderid[] = $linegroup->lineitem->ID->value->_value;
				$item[] = $linegroup->lineitem->item->value->_value;
				$origid[] = $linegroup->lineitem->origid->value->_value;
				$partno[] = $linegroup->lineitem->partno->value->_value;
				$desc[] = $linegroup->lineitem->description->value->_value;
				$price[] = $linegroup->lineitem->price->value->_value;
				$size[] = $linegroup->lineitem->size->value->_value;
				$color[] = $linegroup->lineitem->color->value->_value;
				$itemset[] = $linegroup->lineitem->itemset->value->_value;
				$matt[] = $linegroup->lineitem->matt->value->_value;
				$box[] = $linegroup->lineitem->box->value->_value;
				$displayorder[] = $linegroup->lineitem->displayorder->value->_value;
				$cubicft[] = $linegroup->lineitem->cubicft->value->_value;
				$qtyinset[] = $linegroup->lineitem->qtyinset->value->_value;
				$setqty[] = $linegroup->lineitem->setqty->value->_value;
				$mattqty[] = $linegroup->lineitem->mattqty->value->_value;
				$boxqty[] = $linegroup->lineitem->boxqty->value->_value;
				$orderdate[] = $linegroup->lineitem->orderdate->value->_value;
				$ordertime[] = $linegroup->lineitem->ordertime->value->_value;
			}
		}
	} else {
		if(count($xmlPO->itemgroup->lineitem) > 1) {
			foreach($xmlPO->itemgroup->lineitem as $lineitem) {
				$head[] = $linegroup->ID->value->_value;
				$orderid[] = $lineitem->ID->value->_value;
				$item[] = $lineitem->item->value->_value;
				$origid[] = $lineitem->origid->value->_value;
				$partno[] = $lineitem->partno->value->_value;
				$desc[] = $lineitem->description->value->_value;
				$price[] = $lineitem->price->value->_value;
				$size[] = $lineitem->size->value->_value;
				$color[] = $lineitem->color->value->_value;
				$itemset[] = $lineitem->itemset->value->_value;
				$matt[] = $lineitem->matt->value->_value;
				$box[] = $lineitem->box->value->_value;
				$displayorder[] = $lineitem->displayorder->value->_value;
				$cubicft[] = $lineitem->cubicft->value->_value;
				$qtyinset[] = $lineitem->qtyinset->value->_value;
				$setqty[] = $lineitem->setqty->value->_value;
				$mattqty[] = $lineitem->mattqty->value->_value;
				$boxqty[] = $lineitem->boxqty->value->_value;
				$orderdate[] = $lineitem->orderdate->value->_value;
				$ordertime[] = $lineitem->ordertime->value->_value;
			}
		} else {
			$head[] = $xmlPO->itemgroup->ID->value->_value;
			$orderid[] = $xmlPO->itemgroup->lineitem->ID->value->_value;
			$item[] = $xmlPO->itemgroup->lineitem->item->value->_value;
			$origid[] = $xmlPO->itemgroup->lineitem->origid->value->_value;
			$partno[] = $xmlPO->itemgroup->lineitem->partno->value->_value;
			$desc[] = $xmlPO->itemgroup->lineitem->description->value->_value;
			$price[] = $xmlPO->itemgroup->lineitem->price->value->_value;
			$size[] = $xmlPO->itemgroup->lineitem->size->value->_value;
			$color[] = $xmlPO->itemgroup->lineitem->color->value->_value;
			$itemset[] = $xmlPO->itemgroup->lineitem->itemset->value->_value;
			$matt[] = $xmlPO->itemgroup->lineitem->matt->value->_value;
			$box[] = $xmlPO->itemgroup->lineitem->box->value->_value;
			$displayorder[] = $xmlPO->itemgroup->lineitem->displayorder->value->_value;
			$cubicft[] = $xmlPO->itemgroup->lineitem->cubicft->value->_value;
			$qtyinset[] = $xmlPO->itemgroup->lineitem->qtyinset->value->_value;
			$setqty[] = $xmlPO->itemgroup->lineitem->setqty->value->_value;
			$mattqty[] = $xmlPO->itemgroup->lineitem->mattqty->value->_value;
			$boxqty[] = $xmlPO->itemgroup->lineitem->boxqty->value->_value;
			$orderdate[] = $xmlPO->itemgroup->lineitem->orderdate->value->_value;
			$ordertime[] = $xmlPO->itemgroup->lineitem->ordertime->value->_value;
		}
	}

	// Start building the SQL queries needed; display them for debugging as a start				
	// See if the dealer already exists in the db; if so, find the snapshot id for (possible) addition to the orders db
	


	if($dealerOrigId != "--NULL--") {

	$sql = "SELECT snapshot, snapshot2 FROM users WHERE first_name = '$dealerFirstName' AND last_name = '$dealerLastName' AND address = '$dealerAdd1' AND city = '$dealerCity' AND state = '$dealerSt' AND zip = '$dealerZip' AND ID = '$dealerOrigId'";
	$query = mysql_query($sql);
	checkdberror($sql);
	if($row = mysql_fetch_assoc($query)) {
		$sql2 = "SELECT * from snapshot_users WHERE orig_id = '$dealerOrigId' and first_name = '$dealerFirstName' and last_name = '$dealerLastName' and address = '$dealerAdd1' and city = '$dealerCity' and state = '$dealerSt' and zip = '$dealerZip' and phone = '$dealerPhone' and fax = '$dealerFax' and secondary = '$dealerSecondary'";
		$query2 = mysql_query($sql2);
		checkdberror($sql2);
		if($row2 = mysql_fetch_assoc($query2)) {
			$snapuserID = $row2['id'];
		} else {
			$sql3 = "SELECT MAX(ID) AS idmax FROM snapshot_users";
			$query3 = mysql_query($sql3);
			checkdberror($sql3);
			if($row = mysql_fetch_assoc($query3)) {
				$snapuserID = $row['idmax'] + 1;
			}
			$sql4 = "INSERT INTO snapshot_users (ID, orig_id, first_name, last_name, address, city, state, zip, phone, fax, secondary) VALUES ('$snapuserID', '$dealerOrigId', '$dealerFirstName', '$dealerLastName', '$dealerAdd1', '$dealerCity', '$dealerSt', '$dealerZip', '$dealerPhone', '$dealerFax', '$dealerSecondary')";
			$query4 = mysql_query($sql4);
			checkdberror($sql4);
			if($query4 == FALSE) {
				$ret = Array(-1, "There was a problem with the snapshot user insert (bolxml.php line 960).\nSQL query = $sql4\ncheckdberror = ".checkdberror($sql4)."\npo# in db = $orig_po");
				return $ret;
			}
		}
	} else {
		$sql3 = "SELECT MAX(ID) AS idmax FROM snapshot_users";
		$query3 = mysql_query($sql3);
		checkdberror($sql3);
		if($row = mysql_fetch_assoc($query3)) {
			$snapuserID = $row['idmax'] + 1;
		}
		$dealerID = 0;
		$sql5 = "INSERT INTO snapshot_users (ID, orig_id, first_name, last_name, address, city, state, zip, phone, fax, secondary) VALUES ('$snapuserID', '0', '$dealerFirstName', '$dealerLastName', '$dealerAdd1', '$dealerCity', '$dealerSt', '$dealerZip', '$dealerPhone', '$dealerFax', '$dealerSecondary')";
		$query5 = mysql_query($sql5);
		checkdberror($sql5);
		if($query5 == FALSE) {
			$ret = Array(-1, "There was a problem with the snapshot user insert (bolxml.php line 976).\nSQL query = $sql5\ncheckdberror = ".checkdberror($sql5)."\npo# in db = $orig_po") ;
			return $ret;
		}
	}

	}


	// Find the vendor

	if($vendorOrigId != "--NULL--") {
	
	$sql = "SELECT * FROM forms WHERE ID = '$vendorOrigId'";
	$query = mysql_query($sql);
	checkdberror($sql);
	if($row = mysql_fetch_assoc($query)) {
		$sql2 = "SELECT MAX(id) FROM snapshot_forms WHERE orig_id = '$vendorOrigId' and name = '$vendorName' and address = '$vendorAdd1' and city = '$vendorCity' and state = '$vendorSt' and zip = '$vendorZip' and phone = '$vendorPhone' and fax = '$vendorFax' and prepaidfreight = '$vendorFreightPrepaid' and discount = '$vendorDiscount'";
		$query2 = mysql_query($sql2);
		checkdberror($sql2);
		if($row2 = mysql_fetch_array($query2)) {
			$snapformID = $row2[0];
		}
		if($snapformID == 0) {
			$sql3 = 'SELECT MAX(id) FROM snapshot_forms';
			$query3 = mysql_query($sql3);
			checkdberror($sql3);
			if($row3 = mysql_fetch_array($query3)) {
				$snapformID = $row3[0] + 1;
			}
			$addsnapforform = TRUE;
			$sql4 = "INSERT INTO snapshot_forms (id, orig_id, orig_vendor, name, address, city, state, zip, phone, fax, email, email2, prepaidfreight, discount) VALUES ('$snapformID', '".$row['ID']."', '$vendorOrigVendor', '$vendorName', '$vendorAdd1', '$vendorCity', '$vendorSt', '$vendorZip', '$vendorPhone', '$vendorFax', '$vendorEmail1', '$vendorEmail2', '$vendorFreightPrepaid', '$vendorDiscount')";
			checkdberror($sql4);
			$runno4 = mysql_query($sql4);
			if($runno4 == FALSE) {
				$ret = Array(-1, "There was a problem with the snapshot form insert (bolxml.php line 1010).\nSQL query = $sql4\ncheckdberror = ".checkdberror($sql4)."\npo# in db = $orig_po\n");
				return $ret;
			}
		}
	} else {
		$sql3 = 'SELECT MAX(id) FROM snapshot_forms';
		$query3 = mysql_query($sql3);
		checkdberror($sql3);
		if($row = mysql_fetch_array($query3)) {
			$snapformID = $row[0] + 1;
		}
		$addsnapformonly = TRUE;
		$sql3_2 = "INSERT INTO snapshot_forms (id, orig_id, orig_vendor, name, address, city, state, zip, phone, fax, email, email2, prepaidfreight, discount) VALUES ('$snapformID', '0', '$vendorOrigVendor', '$vendorName', '$vendorAdd1', '$vendorCity', '$vendorSt', '$vendorZip', '$vendorPhone', '$vendorFax', '$vendorEmail1', '$vendorEmail2', '$vendorFreightPrepaid', '$vendorDiscount')";
		checkdberror($sql3_2);
		$runsql3_2 = mysql_query($sql3_2);
		if($runsql3_2 == FALSE) {
			$ret = Array(-1, "There was a problem with the snapshot form insert (bolxml.php line 1026).\nSQL query = $sql3_2\ncheckdberror = ".checkdberror($sql3_2)."\npo# in db = $orig_po");
			return $ret;
		}
	}	
	
	}
	
	// Fix the NULL values
	if($vendorOrigId == "--NULL--") {
		$vendorOrigId = "";
		$snapformID = "";
	}
	if($dealerOrigId == "--NULL--") {
		$dealerOrigId = "";
		$snapuserID = "";
	}
	
			
//       Start for the actual INSERT statements

		$sql = "INSERT INTO order_forms (processed, ordered, process_time, form, user, comments, freight_percentage, discount_percentage, total, type, email_vendor, deleted, user_address, snapshot_user, snapshot_form) VALUES ('$processedYN', '$orderDatetime', '$processDatetime', '$vendorOrigId', '$dealerOrigId', '".mysql_real_escape_string($comments)."', '$freightPercentage', '$discountPercentage', '$total', '$type', '$emailDate', '$deleted', '$dealerAddressNum', '$snapuserID', '$snapformID')";
		$runsql = mysql_query($sql);
    $po = mysql_insert_id();
		$j = 0;
    $add2queue = true;
		while($j < count($orderid)) {

			$sqlMaxItem = "SELECT MAX(id) FROM snapshot_items WHERE orig_id = '".$origid[$j]."'";
			$sqlMaxCheck = mysql_query($sqlMaxItem);
   			if($getrow = mysql_fetch_array($sqlMaxCheck)) {
				$snapitemadd = $getrow[0];
			} else {
				$ret = Array(-1, "There was a problem with the item # search query (bolxml.php line 1072).\nSQL query = $sqlMaxitem\ncheckdberror = ".checkdberror($sqlMaxItem)."\npo# in db = $orig_po");
				return $ret;
			}
			$sql2 = "INSERT INTO orders (user, setqty, mattqty, qty, item, ordered, form, po_id, ordered_time, snapshot_user, snapshot_form) VALUES ('$dealerOrigId', '".$setqty[$j]."', '".$mattqty[$j]."', '".$boxqty[$j]."', '$snapitemadd', '".$orderdate[$j]."', '$vendorOrigId', '$po', '".$ordertime[$j]."', '$snapuserID', '$snapformID')";
			$runsql2 = mysql_query($sql2);
			if($runsql2 == FALSE) {
				$ret = Array(-1, "There was a problem with the order item insert (bolxml.php line 1080).\nSQL query = $sql2\ncheckdberror = ".checkdberror($sql2)."\npo# in db = $orig_po");
				return $ret;
			}
			$new_orderid[] = mysql_insert_id();
			$j++;
		}
    // BOL queue addition loop
    $j = 0;
    $add2queue = true;
		foreach($new_orderid as $k => $ord) {
			$sql2_check = "SELECT setqty, mattqty, qty FROM orders WHERE ID = '$ord'";
			$runsql2_check = mysql_query($sql2_check);
			$row2 = mysql_fetch_assoc($runsql2_check);
      if(($row2['setqty']==0 && $row2['mattqty']==0 && $row2['qty']==0) || ($row2['setqty']<0 || $row2['mattqty']<0 || $row2['qty']<0)) {
        $add2queue = false;
      } else {
        $add2queue = true;
  			$totalset += $row2['setqty'];
  			$totalmatt += $row2['mattqty'];
  			$totalbox += $row2['qty'];
      }
    }
    if($add2queue) {
		  $sql = "INSERT INTO BoL_queue (po, orig_po, source, totalset, totalmatt, totalbox, prepaidfreight, createdate) VALUES ($po, $orig_po, '$source', $totalset, $totalmatt, $totalbox, ".number_format($total - ($total/(1+($freightPercentage/100))), 2).", NOW())";
  		$runsql = mysql_query($sql);
  		if(!$runsql) {
  			$ret = Array(-1, "There was a problem with the shipping queue insert (bolxml.php line 1105).\nSQL query = $sql\ncheckdberror = ".checkdberror($sql)."\npo# in db = $orig_po");
  			return $ret;
  		}
		}
		
// return Array(1, 0);
// End OrderFromXMLBoL($xmlObj)
}