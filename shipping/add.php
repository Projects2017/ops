<?php
// add.php

require('bolxml.php');  // Uses XML functions to get the XML data from the incoming POST
require('database.php');
// require('secure.php');

if (!$_POST)
{
	die("This script requires XML data to be POST'd in.");
}
// Get the XML data into a string
if($_POST['source']) {
  $source = $_POST['source'];
} else {
  $source = "pmd";
}
$xmlstring = urldecode($_POST['xml']);
if(get_magic_quotes_gpc()) {
  $xmlstring = stripslashes($xmlstring);
}
// Read the XML data into an XML object
$xml = makeXMLObject($xmlstring);
// Write the order data to the local order tables, source determines where it came from
// source = 'pmd' = PMD; source = 'rfe' = RFE
$check = makeOrders(&$xml, $source);
if ($check[0] != -1) {
  return 1;
} else {
  ini_set(sendmail_from, 'Shipping Queue Daemon <noreply@retailservicesystems.com>');
  sendmail('Shipping DBA <will@retailservicesystems.com>', 'Shipping Queue Daemon Error - Order Addition From Production', $check[1]);
  return 1;
}
?>
