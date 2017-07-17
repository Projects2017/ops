#!/usr/bin/php -q
<?php
// Purpose of this is to check for unadded orders and identify where they are

chdir(dirname(__FILE__));

require('../database.php');
require('../admin/XML.inc.php');
require('../inc_content.php');
require('inc_commercehub.php');

$sql = "UPDATE `snapshot_users` SET `email` = (select `ch_personplace`.`email` from `ch_personplace` WHERE `snapshot_users`.`id` = `ch_personplace`.`snapshot`) WHERE `snapshot_users`.`orig_id` IS NULL;";
mysql_query($sql); // One hell of a massive update
checkDBerror($sql);

function ch_readline ($prompt) {
    echo $prompt;
    # 4092 max on win32 fopen

    if (!defined('STDIN')) define('STDIN', fopen('php://stdin','r'));
    $in = fread(STDIN,30); // Reads up to 30 characters or up to a new line.

    return $in;
}

function findlostorders() {
	global $commercehub_tmp_dir;
	$lostorders = array();
	$orderdir = $commercehub_tmp_dir.'/costco/incomming/orders';
	$d = dir($orderdir);
	while (false !== ($entry = $d->read()))  {
		if (is_dir($orderdir.'/'.$entry)) continue;
		if (strpos($entry,'.retry')) continue; // This is a duplicate most likely.
		$xml = simplexml_load_file($orderdir.'/'.$entry);
		$batchId = $xml['batchNumber'];
		foreach ($xml->hubOrder as $order) {
			$sql = "SELECT `id` FROM `ch_order` WHERE `merchantpo` = '".$order->poNumber."'";
			$result = mysql_query($sql);
			checkDBerror($sql);
			if (!mysql_num_rows($result)) {
				$lost = array();
				$lost['merchantpo'] = (string) $order->poNumber;
				$lost['batchNumber'] = (string) $batchId;
				$lost['trxID'] = (string) $order['transactionID'];
				$lostorders[] = $lost;
				unset($lost);
			}
		}
	}
	return $lostorders;
}

function reschedulelostorders($lostorders) {
	global $commercehub_tmp_dir;
	$orderdir = $commercehub_tmp_dir.'/costco/incomming/orders';
	
	// Initialize Arrays
	$batches = array();
	foreach ($lostorders as $order) {
		if (!isset($batches[$order['batchNumber']])) {
			$batches[$order['batchNumber']] = array();
		}
		$batches[$order['batchNumber']][] = $order['trxID'];
	}
	
	foreach($batches as $batchId => $orders) {
		$xml = simplexml_load_file($orderdir.'/'.$batchId.".neworders");
		$d_xml = dom_import_simplexml($xml);
		foreach (dnl2array($d_xml->childNodes) as $order) {
			if ($order->nodeType != XML_ELEMENT_NODE) continue;
			if ($order->tagName != 'hubOrder') continue;
			if (!in_array($order->getAttribute('transactionID'),$orders)) {
				// Not a missing order, remove it
				$d_xml->removeChild($order);
			}
		}
		$xml = simplexml_import_dom($d_xml);
		$xml->messageCount = (string) count($orders);
		file_put_contents($orderdir.'/reprocess/'.$batchId.".neworders.retry", $xml->asXML());
	}
}

// Converts a DOMNodeList to an Array that can be easily foreached
function dnl2array($domnodelist) {
	$return = array();
	for ($i = 0; $i < $domnodelist->length; ++$i) {
		$return[] = $domnodelist->item($i);
	}
	return $return;
}

putenv("GNUPGHOME=".$commercehub_tmp_dir."/gpghome/");
$gnupg = new gnupg();
$gnupg->setarmor(0); // Remove ASCII Armoring as we're only dealing with files.

// Get Key Fingerprint of Encrypt Key
$iterator = new gnupg_keylistiterator("CommerceHub");
foreach($iterator as $fingerprint => $userid){
    $encrypt_key = $fingerprint;
    $gnupg->addencryptkey($encrypt_key);
}

// Get Key Fingerprint of Decrypt Key
$iterator = new gnupg_keylistiterator("Power Marketing Direct, Inc.");
foreach($iterator as $fingerprint => $userid){
    $decrypt_key = $fingerprint;
    $gnupg->adddecryptkey($decrypt_key,"maquis22");
}

commercehub_mkdir($commercehub_tmp_dir.'/costco');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming/orders');


$lostorders = findlostorders();
print_pretty_table($lostorders, "Missing CH Orders");

if (!$lostorders) {
	echo "No further actions available.\n";
	exit(0);
}

// Find out if They want to schedule orders for processing.
do {
	$ans = ch_readline("Schedule Orders for Processing? [Y/N]: ");
} while (strtoupper($ans[0]) != 'Y' && strtoupper($ans[0]) != 'N');

if (strtoupper($ans[0]) == 'N') {
	echo "No further actions available.\n";
	exit(0);
}

// Proceed to scheduling orders for processing
echo "Re-scheduling Orders...\n";
reschedulelostorders($lostorders);

do {
	$ans = ch_readline("Run CH Processing Now? [Y/N]: ");
} while (strtoupper($ans[0]) != 'Y' && strtoupper($ans[0]) != 'N');

if (strtoupper($ans[0]) == 'N') {
	echo "No further actions available.\n";
	exit(0);
}

echo "Processing Orders...\n";
passthru('./commercehub.php');
echo "\n";

$lostorders = array();
$lostorders = findlostorders();
if ($lostorders) {
	print_pretty_table($lostorders, "Still Missing Orders");
} else {
	echo "Order Reinserts Successful!\n";
}
echo "No further actions available.\n";
exit(0);
?>