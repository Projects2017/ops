<?php
// shipping.php
// display orders in the queue - complete or not based on a dropdown value, range dependent upon date of order & vendor access of user
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once('inc_shipping.php');
$chosen_vendor = '';
// apply POST'd vars, first getting the values from the cookie
if($_COOKIE['shipping_show_type'] || $_COOKIE['shipping_show_type']!="") {
	$entry = true;
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
} else {
	$entry = false;
	$groupmulti = 1;
}
if($_POST['show_type'] || $_POST['show_type']!="") {
	$entry = true;
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
}
if($_POST['reset_filters'])
{
	$entry = false;
	$from_date = "";
	$thru_date = "";
	$vendor_entry = "";
	$form_entry = "";
	$dealer_entry = "";
	$show_type = '';
	$vendor = "";
	$showformnames = "";
	$chosen_vendor = "";
	$chosen_form = "";
	$chosen_dealer = "";
	$searchopt = "";
	$searchfor = "";
	$dealer = "";
	$groupmulti = 1;
}
// if we have a message to display, pull it from the cookie collection to a string and reset
if($_COOKIE['BoL_msg']) {
	$msg = $_COOKIE['BoL_msg'];
	setcookie('BoL_msg', '', time()-2);
} else {
	$msg = "";
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Shipping Report Queue</title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<meta name="generator" content="WebDesign">
	<link type="text/css" href="css/styles.css" rel="stylesheet">
	<link type="text/css" href="css/shipping.css" rel="stylesheet">
	<!-- Calendar Control for date selection -->
	<link href="../include/CalendarControl.css" rel="stylesheet" type="text/css">
	<script src="../include/common.js" type="text/javascript"></script>
	<script src="../include/CalendarControl.js" type="text/javascript"></script>
	<script src="../include/sorttable.js" type="text/javascript"></script>
	<script src="bol.js" language="javascript" type="text/javascript"></script>
	<script src="shipping.js" language="javascript" type="text/javascript"></script>
</head>
<body onLoad="chooseVendor('<?php= $chosen_vendor ?>', '<?php= $chosen_form ?>');">
<?php include_once("../menu.php");
// see which of from_date or thru_date is sooner, get them in the right order....
if($from_date > $thru_date)
{
	// switch
	$tmp = $thru_date;
	$thru_date = $from_date;
	$from_date = $tmp;
	unset($tmp);
}
require_once('inc_queue_filters.php');

// parse the date filter blocks for pretty date display
$from_month = $from_date != '' ? substr($from_date, 0, 2) : date('m');
$from_day = $from_date != '' ? substr($from_date, 3, 2) : date('d');
$from_year = $from_date != '' ? substr($from_date, -4) : date('Y');
$thru_month = $thru_date != '' ? substr($thru_date, 0, 2) : date('m');
$thru_day = $thru_date != '' ? substr($thru_date, 3, 2) : date('d');
$thru_year = $thru_date != '' ? substr($thru_date, -4) : date('Y');

?>
<p class="pagetitle">Shipping Report Queue<br /><?php
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
}
if($msg!="") { // display the message if necessary
	echo '<p class="alert">'.$msg."</p>\n";
}
?><form name="settings_form" method="post" action="shipping.php">
<input type="hidden" name="vendor_entry" value="<?php if($vendor_entry) { echo 'true'; } else { echo 'false'; } ?>">
<table id="filter" align="center" border="0" cellspacing="3" cellpadding="3">
  <tr>
    <td colspan="3"><?php

    // pickOrderType function spits out the Display Open/Closed/All dropdown
    pickOrderType();
    // pickOrderDates function spits out the date range selectors
    pickOrderDates();
    // pickGroupOrders function spits out the grouping selector (group by dealer, group multi-po capable orders)
	pickGroupOrders();
	
?></td>
</tr>
<tr>
  <td valign="top">Select <?php
  
if(secure_is_admin()) {
	echo 'Vendor';
} else {
	echo 'Form';
}
?>
</td>
<td align="left">
<input type="hidden" name="chosen_vendor" value="<?php if(isset($chosen_vendor)) echo $chosen_vendor; ?>">
<?php
	pickVendor(); // outputs selector for vendors

?></td>
<td class="fat_black_12" rowspan="3" valign="top">
<?php 
if (secure_is_admin()) { ?>
[<a href="manageagents.php">Manage Shipping Agents</a>]<br /><?php }
if (secure_is_superadmin()) { ?>
[<a href="csvexport.php">CSV Queue</a>]<br />
[<a href="chcsvexport.php">Costco Queue</a>]<br />
[<a href="queueverify.php">Queue Verify</a>]
<?php }
?>
</td>
</tr>
<?php
pickForm(); // outputs selector for forms
pickDealer(); // outputs selector for dealers
?>
<tr>
<?php
pickSearchBy(); // outputs selector for Search By
?>
<td align="left"><input type="submit" value="Apply Filter" onClick="runQuery();"><input type="submit" name="reset_filters" value="Reset Filters">&nbsp;&nbsp;<input type="checkbox" name="showformnames"<?php if(isset($showformnames) && $showformnames) echo ' checked'; ?>>&nbsp;Show Form Names</td>
</tr>
</table>
</form>
<?php
if($entry) {
// Start to build the query, first being the forms we can work with based on vendor filter choice
// If there's a specific vendor chosen, create an SQL query filter for it
// otherwise, let everything through by leaving the query the way it is
$arg['show_type'] = $show_type;
$arg['chosen_vendor'] = $chosen_vendor ? $chosen_vendor : "all";
$arg['chosen_dealer'] = $chosen_dealer;
$arg['chosen_form'] = $chosen_form;
$arg['groupmulti'] = $groupmulti;
$arg['from_date'] = $from_date;
$arg['thru_date'] = $thru_date;
$arg['searchopt'] = $searchopt;
$arg['searchfor'] = $searchfor;
/* debugging code
echo "ARG: <br />\n";
var_dump($arg);
*/
$queue = getQueue($arg);
unset($arg);

if($queue['count']>0)
  { 
  	$data = $queue['data'];
  	?>
  	<form name="queue" method="post" action="openform.php">
  	<input type="hidden" name="chosen" value="">
  	<input type="hidden" name="edi" value="0">
  	<table align="center" class="queue" rules="none" cellspacing="2" cellpadding="2">
  	  <tr class="queueheader">
	<th>&nbsp;</th>
       <th>&nbsp;</th>
	    <th scope="col">PO #</th>
	    <th scope="col">Date Ordered</th>
  <?php if($show_type == "open" || $show_type == "all") { ?>
	    <th scope="col">Total Qty</th>
	    <th scope="col">Shipped/Credited Qty</th>
	    <th scope="col">Due Date</th>
	<?php }
	if($show_type == "all") { ?>
      <th scope="col">Date Complete</th>
	<?php }
	if($show_type == "open" || $show_type == "all") { ?>
		<th scope="col" colspan="2">View</th>
		<th scope="col">Add</th>
  <?php }
  	if($show_type == "closed")
   { ?>
      <th scope="col">Date Complete</th>
      <th scope="col" colspan="2">View</th>
  <?php } ?>
	  </tr>
<?php	if($show_type=="open" || $show_type == "all") { ?>
      <tr id="multiorder" style="visibility: hidden"><input type="hidden" id="checkedorders" name="checkedorders" value="">
        <td colspan="6" align="right" class="queueheader">Selected Orders:</td>
        <td align="center"><button type="button" id="multi_button" onclick="document.queue.chosen.value = 'multi'; submit();">&nbsp;</button><input type="hidden" id="multi_buttontype" name="multi_buttontype" value="">
        </td>
        <td>&nbsp;</td>
      </tr><?php
	}
	if($show_type=="closed" || $show_type == "all") {
		?><tr><td colspan="<?php if($show_type=="closed") { echo "5"; } else { echo "8"; } ?>">&nbsp;</td><td colspan="<?php if($show_type=="closed") { echo "2"; } else { echo "3"; } ?>">{ Freight entered } ; * Freight not entered *<br /><span style="background-color: <?php= getAdminPrintColor() ?>">Admin Print not done</span>&nbsp;<span style="background-color: <?php= getCompleteColor() ?>">Complete</span></td></tr>
<?php	}
	$order_compare = array();
	$dealer_locations = array();
	// go through the orders and find those which are multi-able
	for($i=0; $i<$queue['count']; $i++) {
		$form_sql = "SELECT address, city, state, zip FROM snapshot_forms WHERE id = {$data['form'][$i]}";
		$form_query = mysql_query($form_sql);
		$selected_vendor = mysql_fetch_assoc($form_query);
		$selected_dealer['address'] = $data['address'][$i];
		$selected_dealer['city'] = $data['city'][$i];
		$selected_dealer['state'] = $data['state'][$i];
		$selected_dealer['zip'] = $data['zip'][$i];
		$order_data['vendor'] = $selected_vendor;
		$order_data['dealer'] = $selected_dealer;
		if($order_compare != $order_data) { // compare vendor & dealer info
			if($i==0) { $i=0; } elseif($dealer_locations['begin'][$data['name'][$i-1]]) { 
				$dealer_locations['end'][$data['name'][$i-1]][] = $data['po'][$i-1];
			}
			$dealer_locations['begin'][$data['name'][$i]][] = $data['po'][$i];
			$order_compare = $order_data;
		}
	}
	$dealer_locations['end'][$data['name'][$queue['count']-1]][] = $data['po'][$queue['count']-1];
	foreach($dealer_locations['begin'] as $key => $value) {
		foreach($value as $k => $v) {
			foreach($dealer_locations['end'][$key] as $end_key => $end_value) { // if the user which is able to be multi-po'd is the same starting and stopping, then we know it's a single order...remove from the list
				if($v == $end_value) {
					unset($dealer_locations['begin'][$key][$k]);
					unset($dealer_locations['end'][$key][$end_key]);
				}
			}
		}
	}
				
	$lastdealer = "";
	$lastorig_form = 0;
	$groupable = false;
	$endgroup = false;
	$newgroup = false;
	$ingroup = false;
	static $groupcolor;
	$data = $queue['data'];
	for($i=0; $i<$queue['count']; $i++) 
	{
		$freight_entered = false;
		$qtyshipped = getQtyShipped($data['po'][$i]);
		if($groupmulti)
		{
			if($order_data) $order_compare = $order_data;
			$form_sql = "SELECT name, address, city, state, zip FROM forms WHERE ID = {$data['form'][$i]}";
			$form_query = mysql_query($form_sql);
			$selected_vendor = mysql_fetch_assoc($form_query);
			$selected_dealer['address'] = $data['address'][$i];
			$selected_dealer['city'] = $data['city'][$i];
			$selected_dealer['state'] = $data['state'][$i];
			$selected_dealer['zip'] = $data['zip'][$i];
			$order_data['vendor'] = $selected_vendor;
			$order_data['dealer'] = $selected_dealer;
			if($lastdealer != $data['name'][$i])
			{
				$lastdealer = $data['name'][$i];
				$lastorig_form = 0;
				echo '<tr class="queueheader"><td colspan="';
				switch($show_type)
				{
					case "open":
						echo "7";
						break;
					case "closed":
						echo "5";
						break;
					case "all":
						echo "8";
						break;
				}
				echo '">';
				echo "{$data['name'][$i]}".'</td><td>BoLs</td><td>Credits</td></tr>'; 
			}
			if($show_type=="closed" || $show_type=="all")
			{
				$bols = getAllBols($data['po'][$i]-1000);
				if(count($bols)!=0)
				{
					$status = array();
					foreach($bols as $bol)
					{
						$freightstatus[] = getBolFreightEnteredStatus($bol);
						$trackingstatus[] = getBolTrackingEnteredStatus($bol);
						$printstatus[] = getBolAdminPrintStatus($bol);
					}
					if(array_unique($freightstatus)==true)
					{
						$freight_entered = true;
					}
					else
					{
						$freight_entered = false;
					}
					if(array_unique($trackingstatus)==true)
					{
						$tracking_entered = true;
					}
					else
					{
						$tracking_entered = false;
					}
					if(array_unique($printstatus)==true)
					{
						$admin_printed = true;
					}
					else
					{
						$admin_printed = false;
					}
				}
			}
			if($lastorig_form != $data['orig_form'][$i] && $showformnames)
			{
				$lastorig_form = $data['orig_form'][$i];
				echo '<tr class="queueheader"><td';
				if($ingroup && ($show_type=="open" || $show_type=="all")) echo ' class="ingroup_'.$groupcolor.'"';
				echo ' colspan="';
				switch($show_type)
				{
					case "open":
						echo "10";
						break;
					case "closed":
						echo "8";
						break;
					case "all":
						echo "11";
						break;
				}
				echo '">';
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Form: ".stripslashes($data['form_name'][$i]).'</td></tr>';
			}
			?><input type="hidden" name="source_<?php echo $i; ?>" value="<?php echo $data['source'][$i]; ?>"><?php
			if(in_array($data['po'][$i],$dealer_locations['begin'][$data['name'][$i]]) && $data['notmultiable'][$i]!=1 && $data['edi'][$i] != 1)
			{
				$newgroup = true;  // if this order is at the start of a group, turn on the groupable boolean value
			}
			if(in_array($data['po'][$i],$dealer_locations['end'][$data['name'][$i]]) && $data['edi'][$i] != 1)
			{
				$endgroup = true;
				$ingroup = false;
			} // if this order is the end of a group, turn on the endgroup boolean which will set off the closing of the border/end of color for debugging purposes
			if($newgroup && ($show_type=="open" || ($show_type=="all" && !$freight_entered)))
			{
				echo '<tr class="newgroup_'; 
				if(!$groupcolor)
				{
					$groupcolor = 1;
				} 
				else
				{
					$groupcolor = $groupcolor == 1 ? 2 : 1;
				}
				echo $groupcolor.'">';
				$ingroup = true;
			}
			elseif($endgroup && ($show_type=="open" || ($show_type=="all" && !$freight_entered)))
			{ 
				echo '<tr class="endgroup_'.$groupcolor.'">';
			}
			elseif($ingroup && ($show_type=="open" || ($show_type=="all" && !$freight_entered)))
			{ 
				echo '<tr class="ingroup_'.$groupcolor.'">';
			}
			else
			{
				echo '<tr';
				if(($show_type=="closed" || $show_type=="all") && getBolStatus($data['po'][$i]-1000))
				{
					echo ' class="complete"';
				}
				echo '>';
			}
			$newgroup = false; ?>
			<td align="center"><?php
			if(($ingroup || $endgroup) && ($show_type=="open" || ($show_type=="all" && !$freight_entered)))
			{ ?>
				<input type="checkbox" name="check<?php echo $i; ?>" id="multiple_<?php echo ($data['po'][$i]-1000); ?>" value="<?php echo ($data['po'][$i]-1000); ?>" onclick="checkOrder(<?php echo ($data['po'][$i]-1000).",'".$data['name'][$i]."'"; ?>)"><input type="hidden" id="picktix_<?php= $data['po'][$i]-1000 ?>" name="picktix_<?php= $data['po'][$i]-1000 ?>" value="<?php= $data['picktix'][$i] ?>"><?php
			}
			else
			{
				echo '&nbsp;';
			} ?>
			</td><?php
		}
		else
		{ // don't group orders...pretty bare right now
			echo '<tr';
			if(getBolStatus($data['po'][$i]-1000)) echo ' class="complete"';
			echo '><td>&nbsp;</td>';
		}

		
		if($lastorig_form != $data['orig_form'][$i] && $showformnames)
		{
			$lastorig_form = $data['orig_form'][$i];
			echo '<tr class="queueheader"><td';
			echo ' colspan="';
			switch($show_type)
			{
				case "open":
					echo "10";
					break;
				case "closed":
					echo "8";
					break;
				case "all":
					echo "11";
					break;
			}
			echo '">';
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Form: ".stripslashes($data['form_name'][$i])."</td></tr>\n<tr><td>&nbsp;</td>"; // adding the blank td to align w/ labels
		}
		
		// debugging thing here
		//var_dump($data);
?>
<td>&nbsp;</td>
	<td align="center" class="queue"><?php= $data['po'][$i] ?><?php if($data['orderqty'][$i]-$qtyshipped>0 || secure_is_superadmin()) { ?><br /><button type="button" onclick="window.location='addbol.php?id=<?php= $data['po'][$i] ?>&viewonly'"<?php
	// changes background color of Order/PickTix to green if pick ticket & labels have been printed
	// currently set to only check the pick ticket status
	// this is what it will be:
	// if($data['picktix'][$i] && $data['label'][$i])) echo ' style="background-color: green"';
	if($data['picktix'][$i]) echo ' style="background-color: green"';
	?>>PickTix</button><?php } ?><input type="hidden" name="<?php echo $i; ?>" value="<?php echo $data['po'][$i]; ?>"></td>
	    <td align="center" class="queue"><?php echo $data['orderdate'][$i]; ?><input type="hidden" name="source_<?php echo $i; ?>" value="<?php echo $data['source'][$i] ?>"></td>
    <?php if($show_type=="open" || $show_type=="all") { ?>
	    <td align="center" class="queue"><?php echo $data['orderqty'][$i] ?></td>
	    <td align="center" class="queue"><?php echo $qtyshipped; ?></td>
	    <td align="center" class="queue"><?php echo $data['orderdue'][$i]; ?></td>
<?php }
if($show_type=="all") { ?>
<td align="center" class="queue"><?php echo $data['complete'][$i] ? $data['shipdate'][$i] : "&nbsp;"; ?></td><?php
}
if($show_type=="open" || $show_type=="all") {
	$bols = getAllBols($data['po'][$i]-1000);
	$creds = getAllCredits($data['po'][$i]-1000);
	?><td align="center"><?php
	if($bols) {
		$first = true;
		foreach($bols as $bol)
		{
			if(!$first) echo '<br />';
			echo '<input type="submit" value="';
			if($show_type=="all")
			{
				echo getBolQueueString($bol);
			}
			else
			{
				echo getBolQueueString($bol, true);
			}
			echo '" size="10" name="viewbol" ';
			if($show_type=="all")
			{
				if(getBolFreightEnteredStatus($bol) && getBolTrackingEnteredStatus($bol))
				{
					if(getBolAdminPrintStatus($bol))
					{
						echo 'style="background-color: '.getCompleteColor().'" ';
					}
					else
					{
						echo 'style="background-color: '.getAdminPrintColor().'" ';
					}
				}
			}
			echo 'onclick="document.queue.chosen.value = \''.$bol.'\'">';
			$first = false;
		}
		echo '</td><td align="center">';
		unset($bols);
	} else {
		echo '&nbsp;</td><td align="center">';
	}
	if($creds) {
		foreach($creds as $cred)
			echo '<input type="submit" value="C '.($cred+1000).' C" style="background-color: cyan" name="viewcred" size="10" onclick="document.queue.chosen.value = '.$cred.'">';
?></td>
<?php 		unset($creds);
	} else {
		echo '&nbsp;</td>';
	}
} else { // Closed orders
 ?>
<td align="center" class="queue"><?php echo $data['shipdate'][$i]; ?></td><td><?php
$bols = getAllBols($data['po'][$i]-1000);
$creds = getAllCredits($data['po'][$i]-1000);
if($bols) {
	foreach($bols as $bol)
	{
		echo '<input type="submit" value="';
		echo getBolQueueString($bol);
		echo '" size="10" name="viewbol" ';
		if(getBolFreightEnteredStatus($bol) && getBolTrackingEnteredStatus($bol))
		{
			if(getBolAdminPrintStatus($bol))
			{
				echo 'style="background-color: '.getCompleteColor().'" ';
			}
			else
			{
				echo 'style="background-color: '.getAdminPrintColor().'" ';
			}
		}
		echo 'onclick="document.queue.chosen.value = \''.$bol.'\'">';
	}
	echo '</td><td align="center">';
	unset($bols);
} else {
	echo '&nbsp;</td><td align="center">';
}
if($creds) {
	foreach($creds as $cred)
		echo '<input type="submit" value="C '.($cred+1000).' C" style="background-color: cyan" name="viewcred" size="10" onclick="document.queue.chosen.value = '.$cred.'">';
?></td>
<?php	unset($creds);
} else {
	echo '&nbsp;</td></tr>';
}
}
if($endgroup) {
	$groupable = false;
	$endgroup = false;
	$newgroup = false;
}
if($show_type == "open" || $show_type == "all") { 
?><td align="center"><?php
if(!isPOClosed(($data['po'][$i]-1000), true)) { ?>
<input type="submit" value="BoL" size="10" name="addbol" onclick="<?php if($data['edi'][$i] == 1) { ?>ediBol(<?php= $i ?>)<?php } else { ?>document.queue.chosen.value = '<?php= $i ?>'<?php } ?>"<?php
// disables BOL entry button if the Pick Ticket & Shipping Label hasn't been printed
// currently set to only be disabled without the Pick Ticket
// this is what it will be:
// if(!($data['picktix'][$i] && $data['label'][$i])) echo ' title="Print Pick Ticket & shipping label to enable" disabled';
if(!($data['picktix'][$i])) echo ' title="Print Pick Ticket to enable" disabled';
?>>
<input type="submit" value="Credit" size="10" name="addcred" onclick="document.queue.chosen.value = '<?php echo $i; ?>'">
<?php } else { echo "&nbsp;"; } ?>
</td></tr>
<?php }
} ?>
	</table>
</form>
<?php } else { ?>
<table align="center" border="0" cellspacing="3" cellpadding="3">
<tr><td><p class="text_16">There are no orders meeting your criteria.</p></td></tr></table>
<?php }
}
?>
</body>
</html>
