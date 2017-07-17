<?php
require('database.php');
require('secure.php');

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


// Valid Modes
// 'form', 'preview', 'submit'
if (!isset($_POST['rep_week'])) {
	header("Location: sos_date.php");
	exit();
}
if (!$_POST['rep_week']) {
	header("Location: sos_date.php");
	exit();
}
$mode = '';
if (secure_is_manager()&&$_REQUEST['f_userid']) {
	$f_userid = $_REQUEST['f_userid'];
} else {
	$f_userid = $userid;
}

$sql = "SELECT `id` FROM `sos` WHERE `date` >= '".date('Y-m-d',$_POST['rep_week'])."'";
$query = mysql_query($sql);
if (mysql_num_rows($query)) {
	header("Location: sos_date.php?note=".urlencode('A report has already been submitted for this date.'));
	exit();
}
switch ($_POST['mode']) {
	case 'submit':
		$mode = 'submit';
		break;
	case 'preview':
		$mode = 'preview';
		break;
	default:
		$mode = 'form';
		break;
}

if ($mode == 'submit') {
	$cnew = array();
	$inew = array();
	$cexist = array();
	$iexist = array();
	foreach ($_POST as $id => $val) {
		if (substr($id,1,3) == 'exp') {
			if (substr($id,0,1) == 'c') {
				if (substr($id,4,3) == 'new') {
					$num = substr($id, 7);
					$parts = explode(',',$_POST['cat_cexpnew'.$num]);
					$cnew[$num] = array('val' => $val, 'num' => $num, 'cat_id' => $parts[0], 'subcat_id' => $parts[1], 'note' => $parts[2]);
				} else {
					$cexist[substr($id,4)] = array('id' => substr($id,4), 'val' => $val);
				}
			} elseif (substr($id,0,1) == 'i') {
				if (substr($id,4,3) == 'new') {
					$num = substr($id, 7);
					$parts = explode(',',$_POST['cat_iexpnew'.$num]);
					$inew[$num] = array('val' => $val, 'num' => $num, 'cat_id' => $parts[0], 'subcat_id' => $parts[1], 'note' => $parts[2]);
				} else {
					$iexist[substr($id,4)] = array('id' => substr($id,4), 'val' => $val);
				}
			}
		}
	}

	$cerase = explode(',',$_POST['removeexpc']);
	$ierase = explode(',',$_POST['removeexpi']);
	foreach ($cerase as $erase) {
		if (!$erase) continue; // If blank or 0.. those are invalid..
		if (!is_numeric($erase)) continue; // If it's not a number, we're not interested
		$sql = "UPDATE `sos_exp` SET `active` = 0 WHERE `id` = '".$erase."' AND `user_id` = '".$f_userid."'";
		mysql_query($sql);
		checkdberror($sql);
	}
	foreach ($ierase as $erase) {
		if (!$erase) continue; // If blank or 0.. those are invalid..
		if (!is_numeric($erase)) continue; // If it's not a number, we're not interested
		$sql = "UPDATE `sos_exp` SET `active` = 0 WHERE `id` = '".$erase."' AND `user_id` = '".$f_userid."'";
		mysql_query($sql);
		checkdberror($sql);
	}

	foreach ($cnew as $new) {
		$new['user_id'] = $f_userid;
		$new['type'] = 'c';
		$new['active'] = '1';
		$sql = buildInsertQuery('sos_exp',$new, true);
		mysql_query($sql);
		checkdberror($sql);
		$newid = mysql_insert_id();
		$cexist[$newid] = array('id' => $newid, 'val' => $new['val']);
	}

	foreach ($inew as $new) {
		$new['user_id'] = $f_userid;
		$new['type'] = 'i';
		$new['active'] = '1';
		$sql = buildInsertQuery('sos_exp',$new, true);
		mysql_query($sql);
		checkdberror($sql);
		$newid = mysql_insert_id();
		$iexist[$newid] = array('id' => $newid, 'val' => $new['val']);
	}

	$insert = array();
	$insert['user_id'] = $f_userid;
	$insert['date'] = date('Y-m-d', $_POST['rep_week']);
	$insert['trs'] = $_POST['trs'];
	$insert['cogs'] = $_POST['cogs'];
	$insert['bd'] = $_POST['bd'];
	$insert['salestax'] = $_POST['salestax'];
	$insert['ufunds'] = $_POST['ufunds'];
	$insert['ops_pickups'] = $_POST['ops_pickups'];
	$insert['ops_deliveries'] = $_POST['ops_deliveries'];
	$insert['ops_deliveryfees'] = $_POST['ops_deliveryfees'];
	$insert['wsent'] = $_POST['wsent'];
	$sql = buildInsertQuery('sos',$insert, true);
	mysql_query($sql);
	checkdberror($sql);
	$sosid = mysql_insert_id();

	foreach ($cexist as $row) {
		$insert = array();
		$insert['sos_id'] = $sosid;
		$insert['exp_id'] = $row['id'];
		$insert['value'] = $row['val'];
		$sql = buildInsertQuery('sos_user_cat',$insert, true);
		mysql_query($sql);
		checkdberror($sql);
	}

	foreach ($iexist as $row) {
		$insert = array();
		$insert['sos_id'] = $sosid;
		$insert['exp_id'] = $row['id'];
		$insert['value'] = $row['val'];
		$sql = buildInsertQuery('sos_user_cat',$insert, true);
		mysql_query($sql);
		checkdberror($sql);
	}
	$ext_header = '';
	if ($_REQUEST['f_userid']) {
		$ext_header = '&f_userid='.urlencode($_REQUEST['f_userid']);
	}
	header('Location: sos_date.php?note='.urlencode('Weekly Financial Report Filed for '.weekrange($_POST['rep_week'])).'&date='.urlencode($_POST['rep_week']).$ext_header);
	exit();
}

// Loading up the catagory definitions
$sql = "SELECT `id`, `name`, `needsub`, `order` FROM `sos_cat` ORDER BY `order`";
$query = mysql_query($sql);
checkDBerror($sql);
$catagories = array();
while ($cat = mysql_fetch_assoc($query)) {
	$catagories[$cat['id']] = $cat;
	$catagories[$cat['id']]['subcats'] = array();
	$sql = "SELECT `id`, `name`, `order` FROM `sos_subcat` WHERE `cat_id` = '".$cat['id']."' ORDER BY `order`";
	$query2 = mysql_query($sql);
	checkDBerror($sql);
	while ($subcat = mysql_fetch_assoc($query2)) {
		$catagories[$cat['id']]['subcats'][$subcat['id']] = $subcat;
	}
}

function getval($name, $type = 'monetary') {
	global $db;
	if ($type == 'monetary') {
		$value = '0.00';
		if (isset($_POST[$name])) $value = sprintf('%.2f',$_POST[$name]);
		elseif (isset($db[$name])) $value = sprintf('%.2f',$db[$name]);
	} elseif ($type == 'text') {
		$value = '';
		if (isset($_POST[$name])) $value = $_POST[$name];
		elseif (isset($db[$name])) $value = $db[$name];
	}
	return $value;
}

function editfield($name) {
	global $mode;
	global $db;
	$value = getval($name);
	
	if ($mode == 'form') {
		return '<input size="7" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" />';
	} elseif ($mode == 'preview') {
		return '$'.$value.'<input type="hidden" id="'.$name.'" name="'.$name.'" value="'.$value.'" />';
	}
}

function local_exp_cmp($a, $b) {
	global $catagories;
	/* htmlspecialchars($catagories[$row['cat_id']]['name']);
	   htmlspecialchars($catagories[$row['cat_id']]['subcats'][$row['subcat_id']]['name']);
	   htmlspecialchars($row['note']); */
	$al = $catagories[$a['cat_id']]['order'];
	$bl = $catagories[$b['cat_id']]['order'];
	if ($al > $bl) {
		return +1;
	} elseif ($al < $bl) {
		return -1;
	}
	$al = $catagories[$a['cat_id']]['subcats'][$a['subcat_id']]['order'];
	$bl = $catagories[$b['cat_id']]['subcats'][$b['subcat_id']]['order'];
	if ($al > $bl) {
		return +1;
	} elseif ($al < $bl) {
		return -1;
	}
	return strcmp($a['note'],$b['note']);
}

function local_exp_sort(&$exp) {
	usort(&$exp, 'local_exp_cmp');
}

$db = array();
$newi = 0;
$newc = 0;
$inew = array();
$cnew = array();
$ctotal = 0;
$itotal = 0;
foreach ($_POST as $id => $val) {
	if (substr($id,1,3) == 'exp') {
		if (substr($id,0,1) == 'c') {
			$ctotal += $val;
			if (substr($id,4,3) == 'new') {
				$num = substr($id, 7);
				if ($num + 1 > $newc) $newc = $num + 1;
				$parts = explode(',',$_POST['cat_cexpnew'.$num]);
				$cnew[$num] = array('num' => $num, 'cat_id' => $parts[0], 'subcat_id' => $parts[1], 'note' => $parts[2]);
			}
		} elseif (substr($id,0,1) == 'i') {
			$itotal += $val;
			if (substr($id,4,3) == 'new') {
				$num = substr($id, 7);
				if ($num + 1 > $newi) $newi = $num + 1;
				$parts = explode(',',$_POST['cat_iexpnew'.$num]);
				$inew[$num] = array('num' => $num, 'cat_id' => $parts[0], 'subcat_id' => $parts[1], 'note' => $parts[2]);
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
	<title>Weekly Financial Report</title>
	<link type="text/css" href="styles.css" rel="stylesheet" />
<?php if ($mode == 'form') { ?>
	<script type="text/javascript">
		gNew = Array();
		gExpenses = Array();
<?php
			foreach ($catagories as $cat) {
				echo "\t\tgExpenses[".$cat['id']."] = Array();\n";
				echo "\t\tgExpenses[".$cat['id']."]['id'] = \"".$cat['id']."\";\n";
				echo "\t\tgExpenses[".$cat['id']."]['name'] = \"".addslashes($cat['name'])."\";\n";
				echo "\t\tgExpenses[".$cat['id']."]['subcats'] = Array();\n";
				foreach ($cat['subcats'] as $subcat) {
					echo "\t\tgExpenses[".$cat['id']."].subcats[".$subcat['id']."] = Array();\n";
					echo "\t\tgExpenses[".$cat['id']."].subcats[".$subcat['id']."].id = \"".$subcat['id']."\";\n";
					echo "\t\tgExpenses[".$cat['id']."].subcats[".$subcat['id']."].name = \"".addslashes($subcat['name'])."\";\n";
				}
				echo "\t\tgExpenses[".$cat['id']."].needsub = ";
				if ($cat['needsub']) echo "true;\n";
				else echo "false;\n";
			}
?>
		gNew.i = <?php= $newi; ?>;
		gNew.c = <?php= $newc; ?>;
	</script>
	<script src="include/common.js" type="text/javascript"></script>
	<script src="include/sos.js" type="text/javascript"></script>
<?php } ?>
</head>
<body>
<?php require('menu.php'); ?>
<form action="sos_insert.php" method="post" id="wfr">
<?php if ($mode == 'form') { ?>
<input type="hidden" id="mode" name="mode" value="preview">
<?php } elseif ($mode == 'preview') { ?>
<input type="hidden" id="mode" name="mode" value="submit">
<?php } ?>
<input type="hidden" id="rep_week" name="rep_week" value="<?php= getval('rep_week','text'); ?>">
<input type="hidden" id="f_userid" name="f_userid" value="<?php= $_REQUEST['f_userid']; ?>">
  <table style="text-align: left; width: 80%;" cellpadding="2" cellspacing="2">
    <tbody>
      <tr>
        <th colspan="7" rowspan="1" style="text-align: center;">
        <h2 style="margin-bottom: 1px;">Weekly
Financial Report</h2>
		<?php $user = db_user_getuserinfo($f_userid); ?><?php= $user['last_name'].' '.$user['first_name']; ?><br />
		<?php= weekrange($_POST['rep_week']); ?>
        </th>
      </tr>
<?php if ($mode == 'form') { ?>
      <tr>
        <td colspan="7"><input type="submit" value="&lt;&lt;&lt; Select Date" onclick="document.location = 'sos_date.php'; return false;">&nbsp;<input type="submit" value="Review Stats &gt;&gt;&gt;"></td>
      </tr>
<?php } ?>
<?php if ($mode == 'preview') { ?>
	  <tr>
		<th colspan="7" style="color: darkred; text-align: center;">
			Please review your report carefully for accuracy, once submitted a report may not be revised.
		</th>
	  </tr>
      <tr>
        <td colspan="7"><input type="submit" value="&lt;&lt;&lt; Revise Stats" onclick="getElementById('mode').value = 'form'; return true;">&nbsp;<input type="submit" value="Submit Stats &gt;&gt;&gt;"></td>
      </tr>
<?php } ?>
      <tr>
        <th colspan="7" rowspan="1" style="text-align: center;">INCOME STATEMENT</th>
      </tr>
      <tr>
        <th colspan="4" rowspan="1">
			Total Retail Sales (not including sales tax)
		</th>
        <td colspan="1"></td>
        <td colspan="1"><?php= editfield('trs'); ?></td>
        <td></td>
      </tr>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			LESS:
<?php } ?>
		</th>
        <td colspan="3" rowspan="1">Cost of Goods
Sold</td>
        <td><?php if ($mode == 'preview') { ?>-<?php } ?></td>
        <td width="10px"><?php= editfield('cogs'); ?></td>
        <td></td>
      </tr>
<?php if ($mode == 'preview') { ?>
      <tr>
        <th colspan="1" rowspan="1">EQUALS:<br />
        </th>
        <td rowspan="1" colspan="3">Gross Profit</td>
        <td colspan="1">=</td>
        <td colspan="1">$<span id="gp"><?php $gp = getval('trs') - getval('cogs'); printf('%.2f',$gp); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			LESS:
<?php } ?>
		</th>
        <th colspan="5" rowspan="1">Expenses</th>
        <td><?php if ($mode == 'form') { ?><a id="new_iexp_show" onclick="setupaddexp('i'); return false;"><img src="images/add.jpg" border="0" /></a><?php } ?><input type="hidden" name="removeexpi" id="removeexpi" value="<?php= getval('removeexpi','text') ?>"></td>
      </tr>
<?php if ($mode == 'form') { ?>
      <tr id="new_iexp_row" style="display: none;">
        <td></td>
        <td>
        <select id="new_iexp_catagory" onchange="selectcat('i');">
        </select>
        </td>
        <td>
        <select id="new_iexp_subcatagory" onchange="selectsubcat('i');" style="display: none;">
        </select>
        </td>
        <td><input id="new_iexp_note" style="display: none;" type="text" /></td>
        <td></td>
        <td></td>
        <td><img src="images/add.jpg" onclick="addexp('i');" border="0" />&nbsp;<img src="images/button_drop.png" onclick="resetaddexp('i');" border="0" /></td>
      </tr>
<?php
}
	$type = 'i';
	$sql = "SELECT `id`, `cat_id`, `subcat_id`, `note`, `active` FROM `sos_exp` WHERE `user_id` = '".$f_userid."' AND `type` = '".$type."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	$rows = array();
	$exclude = explode(',',$_POST['removeexp'.$type]);
	while ($row = mysql_fetch_assoc($query)) {
		if (in_array($row['id'], $exclude)) continue;
		$row['new'] = false;
		$rows[] = $row;
	}
	foreach ($inew as $row) {
		$row['new'] = true;
		$rows[] = $row;
	}
	local_exp_sort(&$rows);
	foreach ($rows as $row) {
		if ($row['new']) {
?>
<tr>
	<td></td>
	<td><?php= htmlspecialchars($catagories[$row['cat_id']]['name']); ?></td>
	<td><?php= htmlspecialchars($catagories[$row['cat_id']]['subcats'][$row['subcat_id']]['name']); ?></td>
	<td><?php= htmlspecialchars($row['note']); ?></td>
	<td></td>
	<td>
		<input name="cat_<?php= $type ?>expnew<?php= $row['num']; ?>" id="cat_<?php= $type ?>expnew<?php= $row['num']; ?>" value="<?php= $row['cat_id'] ?>,<?php= $row['subcat_id'] ? $row['subcat_id'] : '0'; ?>,<?php= htmlspecialchars($row['note']); ?>" type="hidden"><?php= editfield($type.'expnew'.$row['num']); ?>
	</td>
	<td>
		<?php if ($mode == 'form') { ?><a href="#" onclick="if (confirm('Are you sure you want to permanently drop this expense?')) rmrow(this); return false;"><img src="images/button_drop.png" border="0"></a><?php } ?>
	</td>
</tr>
<?php
		} else {
			if (!$row['active']) continue;
?>
      <tr>
        <td></td>
        <td><?php= htmlspecialchars($catagories[$row['cat_id']]['name']); ?></td>
        <td><?php= htmlspecialchars($catagories[$row['cat_id']]['subcats'][$row['subcat_id']]['name']); ?></td>
        <td><?php= htmlspecialchars($row['note']); ?></td>
        <td></td>
        <td><?php= editfield($type.'exp'.$row['id']); ?></td>
        <td><?php if ($mode == 'form') { ?><a href="#" onclick="if (confirm('Are you sure you want to permanently drop this expense?')) { rmrow(this); remexp('<?php= $type ?>', '<?php= $row['id']; ?>'); } return false;"><img src="images/button_drop.png" border="0" /></a><?php } ?></td>
      </tr>
<?php
		}
	}
?>
<?php if ($mode == 'preview') { ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Total</td>
        <td><?php if ($mode == 'preview') { ?>-<?php } ?></td>
        <td>$<span id="sum_iexp"><?php= sprintf('%.2f',$itotal); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			LESS:
<?php } ?>
		</th>
        <td>Bad Debt</td>
        <td></td>
        <td></td>
        <td><?php if ($mode == 'preview') { ?>-<?php } ?></td>
        <td><?php= editfield('bd'); ?></td>
        <td></td>
      </tr>
<?php if ($mode == 'preview') { ?>
      <tr>
        <th>EQUALS:</th>
        <td>Net Profit/Income</td>
        <td></td>
        <td></td>
        <td>=</td>
        <td>$<span id="npi"><?php $npi = $gp - $itotal - getval('bd'); printf('%.2f',$npi); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th colspan="7" rowspan="1" style="text-align: center;">CASH FLOW
OVERVIEW</th>
      </tr>
<?php if ($mode == 'preview') { ?>
      <tr>
        <th colspan="5" rowspan="1">Total Retail
Sales</th>
        <td>$<span id="trs2"><?php= getval('trs'); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			PLUS:
<?php } ?>
		</th>
        <td colspan="3" rowspan="1">Sales Tax</td>
        <td><?php if ($mode == 'preview') { ?>+<?php } ?></td>
        <td><?php= editfield('salestax'); ?></td>
        <td></td>
      </tr>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			LESS:
<?php } ?>
		</th>
        <td colspan="3" rowspan="1">"Uncollected
Funds" from SALES this week</td>
        <td><?php if ($mode == 'preview') { ?>-<?php } ?></td>
        <td><?php= editfield('ufunds'); ?></td>
        <td></td>
      </tr>
<?php if ($mode == 'preview') { ?>
      <tr>
        <th>EQUALS:</th>
        <td colspan="3" rowspan="1">TOTAL Proceeds
COLLECTED from Sales</td>
        <td>=</td>
        <td>$<span id="tpcs"><?php $tpcs = getval('trs') + getval('salestax') - getval('ufunds'); printf('%.2f',$tpcs); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			PLUS:
<?php } ?>
		</th>
        <th colspan="5" rowspan="1">Proceeds
Collected from Operations</th>
        <td></td>
      </tr>
      <tr>
        <td></td>
        <td>Pick Ups</td>
        <td></td>
        <td></td>
        <td><?php if ($mode == 'preview') { ?>+<?php } ?></td>
        <td><?php= editfield('ops_pickups'); ?></td>
        <td></td>
      </tr>
      <tr>
        <td></td>
        <td>Deliveries</td>
        <td></td>
        <td></td>
        <td><?php if ($mode == 'preview') { ?>+<?php } ?></td>
        <td><?php= editfield('ops_deliveries'); ?></td>
        <td></td>
      </tr>
      <tr>
        <td></td>
        <td>Delivery Fees</td>
        <td></td>
        <td></td>
        <td><?php if ($mode == 'preview') { ?>+<?php } ?></td>
        <td><?php= editfield('ops_deliveryfees'); ?></td>
        <td></td>
      </tr>
<?php if ($mode == 'preview') { ?>
      <tr>
        <td></td>
        <td></td>
        <td colspan="3" rowspan="1">Total
&nbsp;Proceeds Collected From Ops</td>
        <td>$<span id="ops_total"><?php $ops_total = getval('ops_pickups') + getval('ops_deliveries') + getval('ops_deliveryfees'); printf('%.2f',$ops_total); ?></span></td>
        <td></td>
      </tr>
      <tr>
        <th>EQUALS:</th>
        <td colspan="3" rowspan="1">Total Proceeds
Collected</td>
        <td>=</td>
        <td>$<span id="tpc"><?php $tpc = $tpcs + $ops_total; printf('%.2f',$tpc); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			LESS:
<?php } ?>
		</th>
        <th colspan="5" rowspan="1">Expenses actually paid</th>
        <td><?php if ($mode == 'form') { ?><a id="new_cexp_show" onclick="setupaddexp('c'); return false;"><img src="images/add.jpg" border="0" /></a><?php } ?><input type="hidden" name="removeexpc" id="removeexpc" value="<?php= getval('removeexpc','text') ?>"></td>
      </tr>
<?php if ($mode == 'form') { ?>
      <tr id="new_cexp_row" style="display: none;">
        <td></td>
        <td>
			<select id="new_cexp_catagory" onchange="selectcat('c');">
			</select>
        </td>
        <td>
			<select id="new_cexp_subcatagory" onchange="selectsubcat('c');" style="display: none;">
			</select>
        </td>
        <td><input id="new_cexp_note" style="display: none;" type="text" /></td>
        <td></td>
        <td></td>
        <td><img src="images/add.jpg" onclick="addexp('c');" border="0" />&nbsp;<img src="images/button_drop.png" onclick="resetaddexp('c');" border="0" /></td>
      </tr>
	  <?php
}
	$type = 'c';
	$sql = "SELECT `id`, `cat_id`, `subcat_id`, `note`, `active` FROM `sos_exp` WHERE `user_id` = '".$f_userid."' AND `type` = '".$type."'";
	$query = mysql_query($sql);
	checkdberror($sql);
	$rows = array();
	while ($row = mysql_fetch_assoc($query)) {
		$row['new'] = false;
		$rows[] = $row;
	}
	foreach ($cnew as $row) {
		$row['new'] = true;
		$rows[] = $row;
	}
	foreach ($rows as $row) {
		if ($row['new']) {
?>
<tr>
	<td></td>
	<td><?php= htmlspecialchars($catagories[$row['cat_id']]['name']); ?></td>
	<td><?php= htmlspecialchars($catagories[$row['cat_id']]['subcats'][$row['subcat_id']]['name']); ?></td>
	<td><?php= htmlspecialchars($row['note']); ?></td>
	<td></td>
	<td>
		<input name="cat_<?php= $type ?>expnew<?php= $row['num']; ?>" id="cat_<?php= $type ?>expnew<?php= $row['num']; ?>" value="<?php= $row['cat_id'] ?>,<?php= $row['subcat_id'] ? $row['subcat_id'] : '0'; ?>,<?php= htmlspecialchars($row['note']); ?>" type="hidden"><?php= editfield($type.'expnew'.$row['num']); ?>
	</td>
	<td>
		<?php if ($mode == 'form') { ?><a href="#" onclick="if (confirm('Are you sure you want to permanently drop this expense?')) rmrow(this); return false;"><img src="images/button_drop.png" border="0"></a><?php } ?>
	</td>
</tr>
<?php
		} else {
			if (!$row['active']) continue;
?>
      <tr>
        <td></td>
        <td><?php= htmlspecialchars($catagories[$row['cat_id']]['name']); ?></td>
        <td><?php= htmlspecialchars($catagories[$row['cat_id']]['subcats'][$row['subcat_id']]['name']); ?></td>
        <td><?php= htmlspecialchars($row['note']); ?></td>
        <td></td>
        <td><?php= editfield($type.'exp'.$row['id']); ?></td>
        <td><?php if ($mode == 'form') { ?><a href="#" onclick="if (confirm('Are you sure you want to permanently drop this expense?')) { rmrow(this); remexp('<?php= $type ?>', '<?php= $row['id']; ?>'); } return false;"><img src="images/button_drop.png" border="0" /></a><?php 	} ?></td>
      </tr>
<?php
		}
	}
?>
<?php if ($mode == 'preview') { ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Total</td>
        <td><?php if ($mode == 'preview') { ?>-<?php } ?></td>
        <td>$<span id="sum_cexp"><?php= sprintf('%.2f',$ctotal); ?></span></td>
        <td></td>
      </tr>
      <tr>
        <th>EQUALS:</th>
        <td colspan="3" rowspan="1">Gross Cash Flow
From Operations</td>
        <td>=</td>
        <td>$<span id="gcffo"><?php $gcffo = $tpc - $ctotal; printf('%.2f',$gcffo); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
      <tr>
        <th>
<?php if ($mode == 'preview') { ?>
			LESS:
<?php } ?>
		</th>
        <td colspan="3" rowspan="1">Wires Sent</td>
        <td><?php if ($mode == 'preview') { ?>-<?php } ?></td>
        <td><?php= editfield('wsent'); ?></td>
        <td></td>
      </tr>
<?php if ($mode == 'preview') { ?>
      <tr>
        <th>EQUALS:</th>
        <td colspan="3" rowspan="1">Net Weekly Cash
Flow</td>
        <td>=</td>
        <td>$<span id="nwcf"><?php $nwcf = $gcffo - getval('wires'); printf('%.2f',$nwcf); ?></span></td>
        <td></td>
      </tr>
<?php } ?>
<?php if ($mode == 'form') { ?>
      <tr>
        <td colspan="7"><input type="submit" value="&lt;&lt;&lt; Select Date" onclick="document.location = 'sos_date.php'; return false;">&nbsp;<input type="submit" value="Review Stats &gt;&gt;&gt;"></td>
      </tr>
<?php } ?>
<?php if ($mode == 'preview') { ?>
      <tr>
        <td colspan="7"><input type="submit" value="&lt;&lt;&lt; Revise Stats" onclick="getElementById('mode').value = 'form'; return true;">&nbsp;<input type="submit" value="Submit Stats &gt;&gt;&gt;"></td>
      </tr>
<?php } ?>
    </tbody>
  </table>
  <br />
</form>
<div id="debugout"></div>
</body>
</html>
