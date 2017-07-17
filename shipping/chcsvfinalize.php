<?php
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once("inc_chcsvexport.php");

if (!secure_is_superadmin()) die("Access Denied");

if ($_GET['type'] == 'pending') {
	if ($_GET['mode'] == 'salesord') {
		$filename = "admin/exported_csvs/pending_chsalesord.csv";
	} elseif ($_GET['mode'] == 'soi') {
		$filename = "admin/exported_csvs/pending_chsoi.csv";
	} else {
		die("Invalid Mode");
	}
	if (file_exists("../".$filename)) {
		header("Location: /".$filename);
	} else {
		setcookie('BoL_msg', "No rows queued currently.", 0);
		header("Location: chcsvexport.php");
	}
} else {
	if ($_GET['mode'] == 'salesord') {
		header("Location: ".salesord_release());
	} elseif ($_GET['mode'] == 'soi') {
		header("Location: ".soi_release());
	} else {
		die("Invalid Mode");
	}
}


exit(); // Exit now so we don't accidentally output anything.
?>