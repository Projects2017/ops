#!/usr/bin/php -q
<?php
error_reporting(E_ERROR | E_PARSE);
chdir(dirname(__FILE__));

require('../database.php');
require('../admin/XML.inc.php');
require('../inc_content.php');
require('inc_commercehub.php');

// Commerce Hub Transit System

// Init GNUpg for encryption/decryption
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

// Determine Dealer ID from Login
$sql = "select `relation_id` from login where username='".$commercehub_dealerlogin."' and type != 'V'";
$query = mysql_query( $sql );
checkDBError($sql);
if ($result = mysql_fetch_assoc($query)) {
	$commercehub_dealerid = $result['relation_id'];
} else {
	commercehub_senderr('Cannot Identify Costco Login', 'I cannot identify the costco login. Dieing now');
	die('Cannot Identify Costco Login');
}

// Connecting to FTP Server
$ftp_conn = ftp_conn($commercehub_host,$commercehub_user,$commercehub_pass, $commercehub_port);
// Move to local processing dir
chdir($commercehub_tmp_dir);
// Build dir structure
commercehub_mkdir($commercehub_tmp_dir.'/tmp');
commercehub_mkdir($commercehub_tmp_dir.'/tmpOut');

commercehub_mkdir($commercehub_tmp_dir.'/costco');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming/orders');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming/orders/reprocess');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming/orders/reprocess/encrypted');


// Retrieve Orders
ftp_getdir($ftp_conn, $commercehub_order_dl, $commercehub_tmp_dir.'/tmp');

// Get Orders from temp to process
mvdir($commercehub_tmp_dir.'/costco/incomming/orders/reprocess/encrypted', $commercehub_tmp_dir.'/tmp');

// Decrypt Orders
decrypt_dir($gnupg, $commercehub_tmp_dir.'/tmp');
// DTD Check Orders

// Copy Orders to Home
$message = verify_dir($commercehub_tmp_dir.'/tmp',
	$commercehub_tmp_dir.'/costco/incomming/orders/failed',
	'OrderMessageBatch',
	'../dtd/OrderMessageBatch.dtd');
foreach ($message['success'] as $mess) {
	procorder($mess, $commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/template/fa.xml');
}
mvdir($commercehub_tmp_dir.'/tmp', $commercehub_tmp_dir.'/costco/incomming/orders');

mvdir($commercehub_tmp_dir.'/costco/incomming/orders/reprocess', $commercehub_tmp_dir.'/tmp');
$message = verify_dir($commercehub_tmp_dir.'/tmp',
	$commercehub_tmp_dir.'/costco/incomming/orders/failed',
	'OrderMessageBatch',
	'../dtd/OrderMessageBatch.dtd');
foreach ($message['success'] as $mess) {
	procorder($mess, $commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/template/fa.xml');
}
mvdir($commercehub_tmp_dir.'/tmp', $commercehub_tmp_dir.'/costco/incomming/orders');

// Retrieve Payments
ftp_getdir($ftp_conn, $commercehub_remit_dl, $commercehub_tmp_dir.'/tmp');
// Decrypt Payments
decrypt_dir($gnupg, $commercehub_tmp_dir.'/tmp');
commercehub_mkdir($commercehub_tmp_dir.'/costco/incomming/payment');
verify_dir($commercehub_tmp_dir.'/tmp', $commercehub_tmp_dir.'/costco/incomming/payment/failed','RemittanceAdviceBatch','../dtd/RemittanceAdviceBatch.dtd');
mvdir($commercehub_tmp_dir.'/tmp', $commercehub_tmp_dir.'/costco/incomming/payment');


// Generate Outgoing FA file
$fa = FunctionalAwk::instance();
if ($fa->getMessageCount()) {
	// Save FA to file
	$fa->save($commercehub_tmp_dir.'/tmpOut/'.$fa->getBatchId().".fa");
	$fa->destroy(); // Permenantly Delete FA instance (that way if we ever use it again, it will generate a new instance.
	unset($fa); // Unset our local copy, thus destroying the last reference so garbage collection can grab it.
	// Setup FA Archive Dirs
	commercehub_mkdir($commercehub_tmp_dir.'/costco/outgoing');
	commercehub_mkdir($commercehub_tmp_dir.'/costco/outgoing/fa');
	// Verify FAs are DTD compliant
	verify_dir($commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/costco/outgoing/fa/failed', 'FAMessageBatch', '../dtd/FAMessageBatch.dtd');
	// Copy Compliant FAs to archive (before Encryption)
	mvdir($commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/costco/outgoing/fa',true); // Copy Dir!
	encrypt_dir($gnupg, $commercehub_tmp_dir.'/tmpOut'); // Encypt FAs
	ftp_putdir($ftp_conn,$commercehub_tmp_dir.'/tmpOut',$commercehub_fa_up); // Upload Encrypted FAs
	mvdir($commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/costco/outgoing/fa'); // Archive Encrypted FAs
}

// Generate Confirms
confirm_bols($commercehub_tmp_dir.'/costco/incomming/orders');

$conf = ConfirmMsg::instance();
if ($conf->getMessageCount()) {
	// Setup Archive Dirs
	commercehub_mkdir($commercehub_tmp_dir.'/costco/outgoing');
	commercehub_mkdir($commercehub_tmp_dir.'/costco/outgoing/confirms');
	// Save Confirms
	$conf->save($commercehub_tmp_dir.'/tmpOut/'.$conf->getBatchId().".confirm");
	$conf->destroy();
	// Verify Confirms are DTD compliant
	verify_dir($commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/costco/outgoing/confirms/failed', 'ConfirmMessageBatch', '../dtd/ConfirmMessageBatch.dtd');
	mvdir($commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/costco/outgoing/confirms',true); // Copy Dir!
	encrypt_dir($gnupg, $commercehub_tmp_dir.'/tmpOut'); // Encypt Confirms
	ftp_putdir($ftp_conn,$commercehub_tmp_dir.'/tmpOut',$commercehub_poconfirms_up); // Upload Encrypted Confirms
	mvdir($commercehub_tmp_dir.'/tmpOut', $commercehub_tmp_dir.'/costco/outgoing/confirms'); // Archive Encrypted Confirms
}

