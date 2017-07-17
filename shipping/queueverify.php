<?php
// queueverify.php
// verify the filters in the Shipping queue
$starttime = time();
require_once('../database.php');
$duallogin = 1;
require_once("../vendorsecure.php");
if (!$vendorid)
	require_once("../secure.php");
require_once('inc_shipping.php');
// define the filter variables
$from_month = date('n');
if(strlen($from_month)==1) $from_month = "0".$from_month;
$from_year = date('Y');
$from_day = date('j');
if(strlen($from_day)==1) $from_day = "0".$from_day;
$thru_month = date('n');
if(strlen($thru_month)==1) $thru_month = "0".$thru_month;
$thru_day = date('j');
if(strlen($thru_day)==1) $thru_day = "0".$thru_day;
$thru_year = date('Y');
$chosen_vendor = 'all';
$chosen_form = '';
$chosen_dealer = '';
$chosen_dealer_name = '';
$searchfor = '';
$searchopt = 'order_forms.ID';
$vendor_amt = 0;
$form_amt = 0;
$dealer_amt = 0;
function showFirst()
{
	global $verify;	
	if($verify['count']>=1)
	{
		?><tr>
		<td>&nbsp;</td>
		<td><a href="/viewpo.php?po=<?php= $verify['data']['po'][0] ?>">PO # <?php= $verify['data']['po'][0] ?></a></td><td>Dealer: <?php= $verify['data']['name'][0] ?></td><td>Order Date: <?php= $verify['data']['orderdate'][0] ?></td></tr><?php
	}
	unset($verify);
}

require_once('inc_queue_filters.php');

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Shipping Report Queue Verification</title>
	<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1">
	<link type="text/css" href="css/styles.css" rel="stylesheet">
	<link type="text/css" href="css/shipping.css" rel="stylesheet">
	<script src="bol.js" language="javascript" type="text/javascript"></script>
	<script src="shipping.js" language="javascript" type="text/javascript"></script>
</head>
<body>
<?php include_once("../menu.php");
// define types array
$viewtype = Array('all', 'open', 'closed');
 ?>
<p class="title">Shipping Report Queue Filter Verification<br />as of <?php= date('F j, Y g:ia') ?></p>
<?php
if(!$_POST['go']) { ?><form name="doit" method="post" action="queueverify.php">
<p align="center">WARNING: This verification can take several minutes to complete<br /><input type="submit" name="go" value="Verify"></p>
</form><?php }
else
{ ?>
<table align="center" border="0" cellspacing="3" cellpadding="3">
<?php
for($mainloop = 0; $mainloop<3; $mainloop++)
{
	?><tr><td colspan="4">&nbsp;</td></tr>
  <tr>
    <td colspan="4" align="center" style="background-color: yellow; font-size: 16px; font-weight: bold">View Type: <?php= ucfirst($viewtype[$mainloop]) ?></td></tr>
    <tr><td colspan="4">&nbsp;</td></tr>
  <tr>
  	<td colspan="4" align="center" style="background-color: green">Date Sort</td></tr>
  <tr>
  	<td colspan="4">Current Date [ <?php= date('F j, Y') ?> ] Record Count: <?php
	$filters = Array('show_type' => $viewtype[$mainloop], 'chosen_vendor' => $chosen_vendor, 'chosen_dealer' => $chosen_dealer, 'chosen_form' => $chosen_form, 'groupmulti' => 1, 'from_year' => $from_year,
	'from_month' => $from_month, 'from_day' => $from_day, 'thru_year' => $thru_year, 'thru_month' => $thru_month, 'thru_day' => $thru_day, 'searchopt' => $searchopt,
	'searchfor' => $searchfor);
	$verify = getQueue($filters, 1); // 1 = verify mode; only return count & first record
	
	// debugging
	//var_dump($verify);
	
	echo $verify['count'];
	?></td></tr>
	<?php showFirst(); ?>
	<tr>
	<td colspan="4">This Month [ <?php= date('F Y') ?> ] Record Count: <?php
	$filters['from_day'] = "01"; // set the from day to the first of the month, gets the whole month
	$verify = getQueue($filters, 1);
	echo $verify['count'];
	?></td></tr>
	<?php showFirst(); ?>
	<tr>
	<?php
	$diff = $filters['from_month'] % 3;
	$filters['from_month'] = (string) ($filters['from_month'] - $diff);
	$this_qtr_month = $filters['from_month'];
	?>
	<td colspan="4">This Quarter [ <?php= floor(date('n')/3) ?> ] Record Count: <?php
	$verify = getQueue($filters, 1);
	echo $verify['count'];
	?></td></tr>
	<?php showFirst(); ?>
	<tr>
	<td colspan="4">This Year [ <?php= date('Y') ?> ] Record Count: <?php
	$filters['from_month'] = "01"; // from_day already set to 1
	$verify = getQueue($filters, 1);
	echo $verify['count'];
	?></td></tr>
	<?php showFirst(); ?>
	<tr><td colspan="4">&nbsp;</td></tr>
	<tr style="background-color: green"><td colspan="4" align="center">Vendor</td></tr>
	<tr>
	<td colspan="4">All Vendors Record Count: <?php	
	$filters['from_month'] = (string)($this_qtr_month - 3);
	if(strlen($filters['from_month'])==1) $filters['from_month'] = "0".$filters['from_month'];
	$filters['from_day'] = "01";
	$filters['from_year'] = "2008";
	// sets date filters to max
	$verify = getQueue($filters, 1);
	echo $verify['count'];
	?></td></tr>
	<?php showFirst();
	for($i = 0; $i <= $vendor_amt; $i++)
	{
		// set the vendor
		pickVendor(0); // mode = 0 for queue verify
		?><tr style="background-color: sienna">
		<td colspan="4">	Vendor <?php= $chosen_vendor ?> Record Count: <?php
		$filters['chosen_vendor'] = $chosen_vendor;
		$verify = getQueue($filters, 1);
		echo $verify['count'];
		?></td></tr>
		<?php showFirst();
		if($verify['count']>=1)
		{
			$forms = pickForm(0); // verify mode; returns count & array of forms IDs & names
			for($j = 0; $j < $forms['count']; $j++)
			{
				?><tr style="background-color: palegreen">
				<td colspan="4">		Form <?php= $forms['data']['name'][$j] ?> Record Count: <?php
				$filters['chosen_form'] = $forms['data']['ID'][$j];
				$verify = getQueue($filters, 1);
				echo $verify['count'];
				?></td></tr>
				<?php showFirst();
			}
			//unset($forms);
			$filters['chosen_form'] = '';
		}
	}
	?><tr><td colspan="4">&nbsp;</td></tr>
	<tr style="background-color: green"><td colspan="4" align="center">Dealer</td></tr>
	<tr>
	<td colspan="4">All Dealers Record Count: <?php
	$filters['chosen_vendor'] = 'all';
	$filters['chosen_form'] = '';
	$verify = getQueue($filters, 1);
	echo $verify['count'];
	?></td></tr>
	<?php showFirst();
	for($i = 0; $i <= $dealer_amt; $i++)
	{
		// set the dealer
		pickDealer(0); // mode = 0 for queue verify
		?><tr style="background-color: sienna">
		<td colspan="4">	Dealer <?php= $chosen_dealer_name ?> Record Count: <?php
		$filters['chosen_dealer'] = $chosen_dealer_name;
		$verify = getQueue($filters, 1);
		echo $verify['count'];
		?></td></tr>
		<?php showFirst();
	}
	$chosen_dealer = '';
	$chosen_dealer_name = '';
	$filters['chosen_dealer'] = '';
	?><tr><td colspan="4">&nbsp;</td></tr>
	<tr style="background-color: green"><td colspan="4" align="center">Search By</td></tr>
	<tr>
	<td colspan="4" align="center">PO</td></tr>
	<tr>
	<td colspan="4">Available PO Range: <?php
	$range = getQueueMinMax();
	echo "{$range['least']} to {$range['most']}"; ?></td></tr>
	<tr><td colspan="4">Random PO = <?php
	$foundpo = false;
	do
	{
		unset($verify);
		$random_po = rand($range['least'], $range['most']);
		$filters['searchopt'] = 'order_forms.ID';
		$filters['searchfor'] = $random_po;
		$verify = getQueue($filters, 1);
		if($verify['count']>=1) $foundpo = true;
	}
	while(!$foundpo);
	echo $random_po; ?></td></tr><?php
	showFirst();
	?><tr><td colspan="4" align="center">BOL</td></tr>
	<tr>
	<td colspan="4">Available BOL Range: <?php
	$range = getBolMinMax();
	echo "{$range['least']} to {$range['most']}"; ?></td></tr>
	<tr><td colspan="4">Random BOL = <?php
	$foundbol = false;
	do
	{
		unset($verify);
		$random_bol = rand($range['least'], $range['most']);
		$filters['searchopt'] = 'BoL_forms.ID';
		$filters['searchfor'] = $random_bol;
		$verify = getQueue($filters, 1);
		if($verify['count']>=1) $foundbol = true;
	}
	while(!$foundbol);
	echo $random_bol; ?></td></tr><?php
	showFirst();
}
$endtime = time();
?><tr><td colspan="4">Total Running Time: <?php
$totaltime = $endtime - $starttime;
echo floor(($totaltime/60))." minutes ".($totaltime % 60)." seconds."; ?></td></tr>
</table>
<?php } ?>
</body>
</html>