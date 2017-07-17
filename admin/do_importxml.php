<?php
/* do_importxml.php

This file imports chosen XML RSS-formatted purchase orders into the orders database.

First we must import all of the required database functions and access codes. */

require('database.php');
require('secure.php');
// And any XML-specific functions will be pulled from xml.php
require('xml.php');
require('menu.php');
require('xmlmenu.php');
echo "<p>Order Import from XML</p>\n";

/*
// First, show all POST variables for debugging purposes
foreach($_POST as $varName => $varValue) {
	echo "$varName => $varValue<br />\n";
}
*/
// Now transform all of the POST variables into PHP variables


foreach($_POST as $varName => $varValue) {
	if(substr($varValue, -4) == ".xml") {
		$importOrders[$varValue] = getZipTarget($varName, $_POST);
		$stuff = getXMLFromZip(key($importOrders), $importOrders[$varValue]);
		$stuff2xml = makeXMLObject($stuff);
		makeOrders(&$stuff2xml);
		unset($stuff2xml);
	} elseif(substr($varValue, -5) == "_only") {
		$fileRead = $xmldir.substr($varValue, 0, strlen($varValue) - 5);
		$fileReader = fopen($fileRead, 'r');
		$fileData = fread($fileReader, filesize($fileReader));
		$stuff2xml = makeXMLObject($fileData);
		makeOrders(&$stuff2xml);
		unset($stuff2xml);
	} elseif(strtolower($varName) != 'submit' && substr($varName, 0, 4) == "all_") {
	// this is when the entire zip file is added
		$openZip = zip_open($xmldir.$varValue);
		if($openZip) {
			while($getXML = zip_read($openZip)) {
				if(zip_entry_open($openZip, $getXML, "r")) {
					$stuff = zip_entry_read($getXML, zip_entry_filesize($getXML));
					zip_entry_close($getXML);
				}
				$stuff2xml = makeXMLObject($stuff);
				makeOrders(&$stuff2xml);
				unset($stuff2xml);
			}
		}
		zip_close($openZip);
	}
}
?>
</body>
</html>