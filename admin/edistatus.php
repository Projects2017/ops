<?php
// editest.php
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
if(!secure_is_admin()) die("404 Error.");
require_once('../inc_content.php');
require_once('../include/edi/edi.php');
require_once('../include/edi/bo_shippingedi.php');
require_once('../shipping/inc_queue_filters.php');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>EDI System Status</title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="WebDesign">
	<link type="text/css" href="../css/styles.css" rel="stylesheet">
	<!-- Calendar Control for date selection -->
	<link href="../include/CalendarControl.css" rel="stylesheet" type="text/css">
	<script src="../include/common.js" type="text/javascript"></script>
	<script src="../include/CalendarControl.js" type="text/javascript"></script>
	<script src="../include/sorttable.js" type="text/javascript"></script>
</head>
<body>
<?php
include_once("../menu.php");
// apply POST'd vars, first getting the values from the cookie
if(isset($_COOKIE['from_date']) || $_COOKIE['from_date']!="") {
	$entry = true;
	extract($_COOKIE);
	/*
	$vendor_entry = $_COOKIE['vendor_entry'];
	$form_entry = $_COOKIE['form_entry'];
	$dealer_entry = $_COOKIE['dealer_entry'];
	$show_type = $_COOKIE['shipping_show_type'];
	$from_date = $_COOKIE['shipping_fr_date'];
	$thru_date = $_COOKIE['shipping_th_date'];
	$vendor = $_COOKIE['shipping_vendor'];
	$dealer = $_COOKIE['shipping_dealer'];
	$groupmulti = $_COOKIE['group_multi'];
	$showformnames = $_COOKIE['showformnames'];
	$chosen_vendor = $_COOKIE['chosen_vendor'];
	$chosen_form = $_COOKIE['chosen_form'];
	$chosen_dealer = $_COOKIE['chosen_dealer'];
	$searchopt = $_COOKIE['searchopt'];
	$searchfor = $_COOKIE['searchfor'];
	*/
} else {
	$entry = false;
}
if(isset($_POST['from_date']) || $_POST['from_date']!="") {
	$entry = true;
	extract($_POST);
	/*
	$vendor_entry = $_POST['vendor_entry'];
	setcookie('vendor_entry', $vendor_entry, 0);  
	$form_entry = $_POST['form_entry'];
	setcookie('form_entry', $form_entry, 0);  
	$dealer_entry = $_POST['dealer_entry'];
	setcookie('dealer_entry', $dealer_entry, 0);
	$show_type = $_POST['show_type'];
	setcookie('shipping_show_type', $show_type, 0);
	$from_date = $_POST['from_date'];
	setcookie('shipping_fr_date', $from_date, 0);
	$thru_date = $_POST['thru_date'];
	setcookie('shipping_th_date', $thru_date, 0);
	$vendor = $_POST['vendor'];
	setcookie('shipping_vendor', $vendor, 0);
	$showformnames = $_POST['showformnames'];
	setcookie('showformnames', $showformnames, 0);
	$chosen_vendor = $_POST['chosen_vendor'];
	setcookie('chosen_vendor', $chosen_vendor, 0);
	$chosen_form = $_POST['chosen_form'];
	setcookie('chosen_form', $chosen_form, 0);
	$chosen_dealer = $_POST['chosen_dealer'];
	setcookie('chosen_dealer', $chosen_dealer, 0);
	$searchopt = $_POST['searchopt'];
	setcookie('searchopt', $searchopt, 0);
	$searchfor = $_POST['searchfor'] != '[Enter desired value]' ? $_POST['searchfor'] : "";
	setcookie('searchfor', $searchfor, 0);
	$dealer = $_POST['dealer'];
	setcookie('shipping_dealer', $dealer, 0);
	if(!$_POST['groupmulti']) {
		$groupmulti = 0;
	} else {
		$groupmulti = $_POST['groupmulti'];
	}
	setcookie('group_multi', $groupmulti, 0);
	*/
}
if($_POST['reset_filters'])
{
	$entry = false;
	$from_date = "";
	$thru_date = "";
	$show_type = '';
}
// if we have a message to display, pull it from the cookie collection to a string and reset
if($_COOKIE['BoL_msg']) {
	$msg = $_COOKIE['BoL_msg'];
	setcookie('BoL_msg', '', time()-2);
} else {
	$msg = "";
}


// parse the date filter blocks for pretty date display
$from_month = $from_date != '' ? substr($from_date, 0, 2) : date('m');
$from_day = $from_date != '' ? substr($from_date, 3, 2) : date('d');
$from_year = $from_date != '' ? substr($from_date, -4) : date('Y');
$thru_month = $thru_date != '' ? substr($thru_date, 0, 2) : date('m');
$thru_day = $thru_date != '' ? substr($thru_date, 3, 2) : date('d');
$thru_year = $thru_date != '' ? substr($thru_date, -4) : date('Y');

?>
<p class="pagetitle">EDI System Status<br /><?php
// if the filter's been applied, show the right header
if($entry) {
	switch($show_type)
	{
		case "closed":
			echo "Completed ";
			break;
		case "open":
			echo "Open ";
			break;
		case "all":
			echo "All ";
			break;
	}
	echo "Orders from $from_month/$from_day/$from_year through $thru_month/$thru_day/$thru_year</p>\n";
	echo "this is the post data...";
	print_r($_POST);
}
if($msg!="") { // display the message if necessary
	echo '<p class="alert">'.$msg."</p>\n";
}
?><form name="settings_form" method="post" action="edistatus.php">
<table id="filter" align="center" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td colspan="3"><?php

    // pickOrderType function spits out the Display Open/Closed/All dropdown
    pickOrderType();
    // pickOrderDates function spits out the date range selectors
    pickOrderDates();
?></td>
</tr>
<tr>
<td colspan="3" align="center"><input type="submit" value="Display Status"></td>
</tr>
</table>
</form>
<?php
// now we spit out the EDI file data called for
$sql = "SELECT * FROM edi_files WHERE sent >= '$from_year-$from_month-$from_day 00:00:00' AND sent <= '$thru_year-$thru_month-$thru_day 23:59:59'";
$que = mysql_query($sql);
checkDBerror($sql);
if(mysql_num_rows($que) > 0)
{
	?><table>
	<tr>
		<td colspan="7">Number of EDI Files Sent/Received in Target Range: <?php= mysql_num_rows($que) ?>
	</tr>
	<tr>
		<td>Filename</td>
		<td>Date Sent/Received</td>
		<td>File Type</td>
		<td>Confirmed</td>
		<td>Processed</td>
		<td>Retailer PO(s)</td>
		<td>RSS PO(s)</td>
	</tr>
	<?php
	while($return = mysql_fetch_assoc($que))
	{
		?><tr>
			<td><?php= $return['filename'] ?></td>
			<td><?php= date('F j, Y', strtotime($return['sent'])) ?></td>
			<td><?php
			// switch(
	}
}
?></body>
</html>