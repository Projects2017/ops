<?php
require('database.php');
require('secure.php');
if (secure_is_manager()&&$_REQUEST['f_userid']) {
	$f_userid = $_REQUEST['f_userid'];
} else {
	$f_userid = $userid;
}
if (!(isset($_REQUEST['rep_month'])&&$_REQUEST['rep_month']&&is_numeric($_REQUEST['rep_month']))) {
	header('Location: sos_date.php');
	exit();
}

function formatdate($date) {
	return date('m/d/Y',$date);
}

function endweek($date) {
	return strtotime("+6 days",$date);
}

function weekrange($date) {
	$output = "";
	$ndate = endweek($date);
	$output .= formatdate($date);
	$output .= " - ";
	$output .= formatdate($ndate);
	return $output;
}

$ym = $_REQUEST['rep_month'];
$year = substr($ym,0,4);
$month = substr($ym,4);
$time = mktime(1,1,1,$month,1,$year);
$month_text = date('F',$time);

$sql = 'SELECT `id`, `user_id`, `date`, `trs`, `cogs`, `bd`, `salestax`, `ufunds`, `ops_pickups`, `ops_deliveries`, `ops_deliveryfees`, `wsent` FROM `sos` WHERE `user_id` = "'.$f_userid.'" AND EXTRACT( YEAR_MONTH FROM `date` ) = "'.$ym.'" ORDER BY `date`;';
$query = mysql_query($sql);
checkdberror($sql);
$weekly_count = mysql_num_rows($query);
$sos_weekly = array();
$sos_cexpenses = array();
$sos_iexpenses = array();
while ($row = mysql_fetch_assoc($query)) {
	$row['date'] = strtotime($row['date']);
	// Expenses
	$row['cexpenses'] = array();
	$row['iexpenses'] = array();
	$row['ctotal'] = 0;
	$row['itotal'] = 0;
	$sql = "SELECT `sos_exp`.`id`, `sos_exp`.`type`, `sos_exp`.`cat_id`, `sos_exp`.`subcat_id`, `sos_exp`.`note`, `sos_user_cat`.`value` FROM `sos_user_cat` INNER JOIN `sos_exp` ON `sos_user_cat`.`exp_id` = `sos_exp`.`id` WHERE `sos_user_cat`.`sos_id` = '".$row['id']."' AND `sos_exp`.`user_id` = '".$f_userid."'";
	$query2 = mysql_query($sql);
	checkdberror($sql);
	while ($expense = mysql_fetch_assoc($query2)) {
		$row[$expense['type'].'total'] += $expense['value'];
		$row[$expense['type'].'expenses'][$expense['id']] = $expense['value'];
		if ($expense['type'] == 'c') {
			$sos_cexpenses[$expense['id']] = $expense;
		} elseif ($expense['type'] == 'i') {
			$sos_iexpenses[$expense['id']] = $expense;
		}
	}
	// Income Statement
	$row['gp'] = $row['trs'] - $row['cogs'];
	$row['npi'] = $row['gp'] - $row['itotal'] - $row['bd'];
	// Cash Flow Overview
	$row['tpcs'] = $row['trs'] + $row['salestax'] - $row['ufunds'];
	$row['ops_total'] = $row['ops_pickups'] + $row['ops_deliveries'] + $row['deliveryfees'];
	$row['tpc'] = $row['tpcs'] + $row['ops_total'];
	$row['gcfo'] = $row['tpc'] - $row['ctotal'];
	$row['ncf'] = $row['gcfo'] - $row['wsent'];
	// Storage
	$sos_weekly[] = $row;
}

foreach ($sos_cexpenses as $aid => $expense) {
	$sql = "SELECT `name`, `order` FROM `sos_cat` WHERE `id` = '".$expense['cat_id']."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	$row = mysql_fetch_assoc($query);
	$sos_cexpenses[$aid]['cat_name'] = $row['name'];
	$sos_cexpenses[$aid]['cat_order'] = $row['order'];
	$sql = "SELECT `name`, `order` FROM `sos_subcat` WHERE `cat_id` = '".$expense['cat_id']."' AND `id` = '".$expense['subcat_id']."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	if (mysql_num_rows($query)) {
		$row = mysql_fetch_assoc($query);
		$sos_cexpenses[$aid]['subcat_name'] = $row['name'];
		$sos_cexpenses[$aid]['subcat_order'] = $row['order'];
	} else {
		$sos_cexpenses[$aid]['subcat_name'] = '';
		$sos_cexpenses[$aid]['subcat_order'] = 0;
	}
}

foreach ($sos_iexpenses as $aid => $expense) {
	$sql = "SELECT `name`, `order` FROM `sos_cat` WHERE `id` = '".$expense['cat_id']."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	$row = mysql_fetch_assoc($query);
	$sos_iexpenses[$aid]['cat_name'] = $row['name'];
	$sos_iexpenses[$aid]['cat_order'] = $row['order'];
	$sql = "SELECT `name`, `order` FROM `sos_subcat` WHERE `cat_id` = '".$expense['cat_id']."' AND `id` = '".$expense['subcat_id']."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	if (mysql_num_rows($query)) {
		$row = mysql_fetch_assoc($query);
		$sos_iexpenses[$aid]['subcat_name'] = $row['name'];
		$sos_iexpenses[$aid]['subcat_order'] = $row['order'];
	}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1"><title>Monthly Financial Report - Income Statement</title>
  <link type="text/css" href="fsstyle.css" rel="stylesheet">
</head><body>
<?php require('menu.php'); ?>
  <table cellpadding="2" cellspacing="2">
    <tbody>
      <tr>
        <th colspan="<?php= 5 + $weekly_count ?>" class="mainheader" style="margin-bottom: 2px;">Monthly Financial Report</th>
      </tr>
	  <tr>
        <td colspan="<?php= 5 + $weekly_count ?>" align="center">
			<?php $user = db_user_getuserinfo($f_userid); ?>
			<?php= $user['last_name'].' '.$user['first_name']; ?>
		</td>
      </tr>
	  <tr>
        <td colspan="<?php= 5 + $weekly_count ?>" align="center"><?php= $month_text ?> <?php= $year ?></td>
      </tr>
      <tr>
        <th colspan="<?php= 5 + $weekly_count ?>" class="mainheader">Income Statement</th>
      </tr>
<tr>
<td colspan="5">&nbsp;</td>
<?php foreach($sos_weekly as $week) { ?>
	<td class="data"><?php= date('m/d/y',$week['date']); ?></td>
<?php } ?>
</tr>
      <tr>
        <th colspan="4" class="subhead">Total Retail
Sales (not including sales tax)</th>
        <td>&nbsp;</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['trs']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">LESS:</th>
        <td colspan="3">Cost of Goods Sold</td>
        <td>-</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['cogs']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">EQUALS:</th>
        <td colspan="3">Gross Profit</td>
        <td>=</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['gp']); ?></td>
<?php } ?>
      </tr>
<tr><td>&nbsp;</td></tr>
      <tr>
        <th colspan="<?php= 5 + $weekly_count ?>" class="subhead">Expenses</th>
      </tr>
<?php
// Income Expenses
foreach ($sos_iexpenses as $expense) {
?>
      <tr>
        <td>&nbsp;</td>
        <td><?php= $expense['cat_name']; ?></td>
        <td><?php= $expense['subcat_name']; ?></td>
        <td><?php= $expense['note']; ?></td>
        <td></td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['iexpenses'][$expense['id']]); ?></td>
<?php } ?>
      </tr>
<?php } ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Total</td>
        <td></td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['itotal']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">LESS:</th>
        <td>Bad Debt</td>
        <td></td>
        <td></td>
        <td>-</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['bd']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">EQUALS:</th>
        <td>Net Profit/Income</td>
        <td></td>
        <td></td>
        <td>=</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['npi']); ?></td>
<?php } ?>
      </tr>
	  <tr>
			<td>&nbsp;</td>
	  </tr>
      <tr>
        <th colspan="<?php= 5 + $weekly_count ?>" class="header">CASH FLOW
OVERVIEW</th>
      </tr>
      <tr>
        <th colspan="5" class="subhead">Total Retail
Sales</th>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['trs']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">PLUS:</th>
        <td colspan="3">Sales Tax</td>
        <td>+</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['salestax']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">LESS:</th>
        <td colspan="3">"Uncollected
Funds" from SALES</td>
        <td>-</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ufunds']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">EQUALS:</th>
        <td colspan="3">TOTAL Proceeds
COLLECTED from Sales</td>
        <td>=</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['tpcs']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">PLUS:</th>
        <th colspan="<?php= 4 + $weekly_count ?>" class="subhead">Proceeds
Collected from Operations</th>
      </tr>
      <tr>
        <td></td>
        <td>Pick Ups</td>
        <td></td>
        <td></td>
        <td>+</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ops_pickups']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <td></td>
        <td>Deliveries</td>
        <td></td>
        <td></td>
        <td>+</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ops_deliveries']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <td></td>
        <td>Delivery Fees</td>
        <td></td>
        <td></td>
        <td>+</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ops_deliveryfees']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td colspan="3">Total Proceeds Collected From Ops</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ops_total']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">EQUALS:</th>
        <td colspan="3" rowspan="1">Total Proceeds
Collected</td>
        <td>=</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['tpc']); ?></td>
<?php } ?>
      </tr>
<tr><td>&nbsp;</td></tr>
      <tr>
        <th colspan="<?php= 5 + $weekly_count ?>" class="subhead">Expenses</th>
      </tr>
<?php
// Cash Expenses
foreach ($sos_cexpenses as $expense) {
?>
      <tr>
        <td>&nbsp;</td>
        <td><?php= $expense['cat_name']; ?></td>
        <td><?php= $expense['subcat_name']; ?></td>
        <td><?php= $expense['note']; ?></td>
        <td></td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['cexpenses'][$expense['id']]); ?></td>
<?php } ?>
      </tr>
<?php } ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Total</td>
        <td></td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ctotal']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">EQUALS:</th>
        <td colspan="3">Gross Cash Flow From Operations</td>
        <td>=</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['gcfo']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">LESS:</th>
        <td colspan="3">Wires Sent</td>
        <td>-</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['wsent']); ?></td>
<?php } ?>
      </tr>
      <tr>
        <th class="subhead">EQUALS:</th>
        <td colspan="3">Net  Cash Flow</td>
        <td>=</td>
<?php foreach($sos_weekly as $week) { ?>
        <td class="data">$<?php= sprintf('%.2f',$week['ncf']); ?></td>
<?php } ?>
      </tr>
    </tbody>
</table>
</body></html>
