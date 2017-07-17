<?php

/* MakeXML - creates XML files from PO data
chosen via form served by exportxml.php */

require('database.php');
require('secure.php');
require('archive.php');

require('xml.php');
/* POST variables required

potype (date | num) determines whether POs were selected by date range or number

	If by date:
		from_month, from_day, from_year = month, day, year of the from date
		thru_month, thru_day, thru_year = month, day, year of the thru date

		*_month value is numeric month, e.g. April = 4, September = 9, etc.
	
	If by number:
		from_number, thru_number = PO number range to export
	
createtype (ind | one) whether files are created for each individual PO or one file is made for all

dload (true | false) download a ZIP'd file immediately after creation?
*/

// Assign POST variables to PHP variables and create the file type suffix
$potype = $_POST['potype'];
if($potype == "date") {
	$from_month = $_POST['from_month'];
	$from_day = $_POST['from_day'];
	$from_year = $_POST['from_year'];
	$thru_month = $_POST['thru_month'];
	$thru_day = $_POST['thru_day'];
	$thru_year = $_POST['thru_year'];
	$filesuffix = "Dates (".$from_month."-".$from_day."-".$from_year." to ".$thru_month."-".$thru_day."-".$thru_year.") ".date('m-d-Y');
} elseif($potype == "num") {
	$from_number = $_POST['from_number'] - 1000;
	$thru_number = $_POST['thru_number'] - 1000;
	$filesuffix = "Number (".$_POST['from_number']."-".$_POST['thru_number'].") ".date('m-d-Y');
} else {
	$dealer = $_POST['dealer'];
	$filesuffix = "Dealer (".$dealer.") ".date('m-d-Y');
}
$dload = $_POST['dload'];	
$createtype = $_POST['createtype'];

// XMLheader = place XML file header in the output file for the first PO only
$XMLheader = TRUE;

// Based on createtype, either track individual files for later cleanup or add container elements
if($createtype == "ind") {
	$madexml = array();
	$contain = FALSE;
} else {
	$contain = TRUE;
}

if($potype == "date") {

	// Find POs by Date Range

	// Get the POs that were started within the date range
	$sql = "SELECT ID FROM order_forms WHERE ordered BETWEEN '".$from_year."-".$from_month."-".$from_day."' AND '".$thru_year."-".$thru_month."-".$thru_day."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	if (!mysql_num_rows($query)) {
		die("Date range not found");
	}
	if ($createtype == "ind") $i = 0;
	while($result = mysql_fetch_array($query)) {
		// Generate the XML
		$xml = OrderToXML($result['ID'], $XMLheader, $contain);
		if($createtype == "ind") {
			// If we're making individual XML files...
			$dispnumber = $result['ID'] + 1000;
			$name = $dispnumber.".xml";
			$filetarget = fopen($xmldir.$name,"w");
			fwrite($filetarget, $xml);
			fclose($filetarget);
			$i++;
			$madexml[$i] = $name;
		} else {
			// Now that we've generated the start of the XML file, don't put on the XML header nor the container elements
			$XMLheader = FALSE;
			$contain = FALSE;
			// We're making one large file, so open it in append mode
			$filetarget = fopen($xmldir."RSS Order Export by ".$filesuffix.".xml","a");
			fwrite($filetarget, $xml);
			fclose($filetarget);
		}
	}

} elseif($potype == "num") {

	// Find POs by PO Number
	
	// Generate the XML by iterating thru each PO by number
	if ($createtype == "ind") $i = 0;
	for($j=$from_number; $j<=$thru_number; $j++) {
		$xml = OrderToXML($j, $XMLheader, $contain);
		if($createtype == "ind") {
			// If we're making individual XML files...
			$name = ($j + 1000).".xml";
			$filetarget = fopen($xmldir.$name,"w");
			fwrite($filetarget, $xml);
			fclose($filetarget);
			$i++;
			$madexml[$i] = $name;
		} else {
			// Now that we've generated the start of the XML file, don't put on the XML header nor the container elements
			$XMLheader = FALSE;
			$contain = FALSE;
			// We're making one large file, so open it in append mode
			$filetarget = fopen($xmldir."RSS Order Export by ".$filesuffix.".xml","a");
			fwrite($filetarget, $xml);
			fclose($filetarget);
		}	
	}
} else {
	// Find POs by the dealer's name
	
	// First, find the POs with the ID (either current or snapshot) for this particular dealer
	$sql = "SELECT ID from order_forms WHERE snapshot_user IN (SELECT id FROM snapshot_users WHERE last_name = '".$dealer."') OR user = (SELECT ID FROM users WHERE last_name = '".$dealer."')";
	$query = mysql_query($sql);
	if (!mysql_num_rows($query)) {
		die("User ID not found");
	}
	if ($createtype == "ind") $i = 0;
	while($result = mysql_fetch_array($query)) {
		// Generate the XML
		$xml = OrderToXML($result['ID'], $XMLheader, $contain);
		if($createtype == "ind") {
			// If we're making individual XML files...
			$displaynumber = $result['ID'] + 1000;
			$name = $displaynumber.".xml";
			$filetarget = fopen($xmldir.$name,"w");
			fwrite($filetarget, $xml);
			fclose($filetarget);
			$i++;
			$madexml[$i] = $name;
		} else {
			// Now that we've generated the start of the XML file, don't put on the XML header nor the container elements
			$XMLheader = FALSE;
			$contain = FALSE;
			// We're making one large file, so open it in append mode
			$filetarget = fopen($xmldir."RSS Order Export by ".$filesuffix.".xml","a");
			fwrite($filetarget, $xml);
			fclose($filetarget);
		}
	}
}

if($createtype != "ind") {
	$filetarget = fopen($xmldir."RSS Order Export by ".$filesuffix.".xml","a");
	fwrite($filetarget, "\n</orders>");
	fclose($filetarget);
}

// Zipping via archive.php classes

$zipfile = new zip_file("RSS Order Export by ".$filesuffix.".zip");
$zipfile->set_options(array('basedir' => $xmldir, 'storepaths'=> 0, 'overwrite' => 1));
$zipfile->add_files("*.xml");
$zipfile->create_archive();

// Clean up the mess

if($createtype == "ind") {
	foreach($madexml as $writtenfile) {
		unlink($xmldir.$writtenfile);
	}
} else {
	unlink($xmldir."RSS Order Export by ".$filesuffix.".xml");
}

// Do the download if selected

if($dload) {
	header('Content-type: application/zip');
	header('Content-Disposition: attachment; filename="RSS Order Export by '.$filesuffix.'.zip"');
	readfile($xmldir."RSS Order Export by ".$filesuffix.".zip");
} else {
require('menu.php');
require('xmlmenu.php');
echo "Successfully generated the XML orders.";
}
/* end makexml.php */
?>