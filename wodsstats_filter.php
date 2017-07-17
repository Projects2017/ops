<?php
require("database.php");
require("secure.php");
require("wodsstats.php");

$is_manager = secure_is_manager();

// Load Variables
$start = $_POST['start'] ? $_POST['start'] : date('m/d/Y',strtotime('-1 week'));
$end = $_POST['end'] ? $_POST['end'] : date('m/d/Y');
$resolution = $_POST['resolution'] ? $_POST['resolution'] : 'day';
$process = $_POST['process'] ? true : false;
if ($is_manager) {
	$filter['team'] = $_POST['team'] ? $_POST['team'] : '*';
	$filter['manager'] = $_POST['manager'] ? $_POST['manager'] : '*';
	$filter['division'] = $_POST['division'] ? $_POST['division'] : '*';
	$filter['level'] = $_POST['level'] ? $_POST['level'] : '*';
	$filter['disabled'] = $_POST['inactive'] ? $_POST['inactive'] : 'N';
	$filter['state'] = $_POST['state'] ? $_POST['state'] : '';
	$limit = $_POST['limit'] ? $_POST['limit'] : 5;
	$sort = $_POST['sort'] ? $_POST['sort'] : 'mTotalProfit';
	$dir = $_POST['dir'] ? $_POST['dir'] : 1;
}
?>
<html>
<head>
<title>RSS FILTER & RANK WODS STATS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
<link href="include/CalendarControl.css" rel="stylesheet" type="text/css">
<script src="include/common.js"></script>
<script src="include/CalendarControl.js"></script>
<script src="include/sorttable.js"></script>
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<span class="fat_black">FILTER & RANK WODS STATS</span><br>
<form method="post"><input type="hidden" id="process" name="process" value="1">
	<table>
	<tr><td>Start Date:</td> <td><input class="date" type="text" value="<?php echo $start; ?>" name="start" id="start"></td>
	<?php if ($is_manager) { ?>
	<td>Team:</td><td><select id="team" name="team">
		<option value="*" <?php if ($filter['team'] == '*') echo "SELECTED"; ?>>All</option>
		<option value="=*" <?php if ($filter['team'] == '=*') echo "SELECTED"; ?>>Only *</option>
		<option value="=" <?php if ($filter['team'] == '=') echo "SELECTED"; ?>>None</option>
		<?php
	$teamlist = teams_list();
	foreach ($teamlist as $value) {
		echo "<OPTION VALUE=\"".$value."\"";
		if ($filter['team'] == $value)
			echo " SELECTED";
		echo ">".$value."</OPTION>";
	}
	?>
	</select></td>
	<?php } ?></tr>
	<tr><td>End Date:</td> <td><input class="date" type="text" value="<?php echo $end; ?>" name="end" id="end"></td>
	<?php if ($is_manager) { ?>
	<td>
	<?php echo manager_name(); ?>:</td><td> <select id="manager" name="manager"><?php $managers =  managers_list(); ?>
		<option value="*" <?php if ($filter['manager'] == '*') echo "SELECTED"; ?>>All</option>
		<option value="=" <?php if ($filter['manager'] == '=') echo "SELECTED"; ?>>None</option>
		<?php foreach ($managers as $managerid) {
			?><option value="<?php=$managerid['name'] ?>" <?php if ($filter['manager'] == $managerid['name']) echo "SELECTED"; ?>><?php=$managerid['name'] ?></option><?php
		}
		?>
	</select></td>
	<?php } ?></tr>
	<tr><td>Resolution:</td> <td><select id="resolution" name="resolution">
		<option value="day" <?php if ($resolution == 'range') echo "SELECTED"; ?>>day</option>
		<option value="week" <?php if ($resolution == 'week') echo "SELECTED"; ?>>week</option>
		<option value="month" <?php if ($resolution == 'month') echo "SELECTED"; ?>>month</option>
		<option value="year" <?php if ($resolution == 'year') echo "SELECTED"; ?>>year</option>
	</select></td>
	<?php if ($is_manager) { ?><td>
	Level: </td><td><select id="level" name="level">
		<option value="*" <?php if ($filter['level'] == '*') echo "SELECTED"; ?>>All</option>
		<option value="=" <?php if ($filter['level'] == '=') echo "SELECTED"; ?>>None</option>
		<option value="1" <?php if ($filter['level'] == '1') echo "SELECTED"; ?>>1</option>
		<option value="TBD" <?php if ($filter['level'] == 'TBD') echo "SELECTED"; ?>>TBD</option>
		<option value="2" <?php if ($filter['level'] == '2') echo "SELECTED"; ?>>2</option>
		<option value="3" <?php if ($filter['level'] == '3') echo "SELECTED"; ?>>3</option>
		<option value="4/5" <?php if ($filter['level'] == '4/5') echo "SELECTED"; ?>>4/5</option>
	</select></td></tr>
	<tr><td>
	<?php $js = "if (this.checked) { document.getElementById('limit').disabled = true; document.getElementById('limit').value = -1 } else { document.getElementById('limit').disabled = false; document.getElementById('limit').value = 5 }"; ?>
	Limit: </td><td> <input type="text" size="4" value="<?php echo $limit; ?>" name="limit" id="limit"<?php if ($limit == -1) echo " DISABLED"; ?>>
	<input type="checkbox" onchange="<?php echo $js; ?>" onpropertychange="<?php echo $js; ?>" value="<?php echo $limit; ?>" <?php if ($limit == -1) echo "CHECKED"; ?>>Unlimited</td>
	<td>
	Division:</td><td> <select id="division" name="division">
		<option value="*" <?php if ($filter['division'] == '*') echo "SELECTED"; ?>>All</option>
		<option value="=" <?php if ($filter['division'] == '=') echo "SELECTED"; ?>>None</option>
		<option value="East" <?php if ($filter['division'] == 'East') echo "SELECTED"; ?>>East</option>
		<option value="West" <?php if ($filter['division'] == 'West') echo "SELECTED"; ?>>West</option>
	</select></td></tr>
	<td>State:</td>
	<td><input type="text" value="<?php echo htmlentities($state); ?>" id="state" name="state" size="2"></td>
	<td>
	Inactives:</td><td> <select id="inactive" name="inactive">
		<option value="N" <?php if ($filter['disabled'] == "N") echo "SELECTED"; ?>>Exclude</option>
		<option value="*" <?php if ($filter['disabled'] == "*") echo "SELECTED"; ?>>Include</option>
		<option value="Y" <?php if ($filter['disabled'] == "Y") echo "SELECTED"; ?>>Only</option>
	</select></td>
	<tr><td>
	Sort By:</td><td colspan="3"> <select id="sort" name="sort">
		<?php $temp = 'mUserLastName' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Dealer Name</option>
		<?php $temp = 'mUserFirstName' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Location</option>
		<?php $temp = 'mInsertsOut' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Inserts Out</option>
		<?php $temp = 'mInsertsShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Inserts Shown</option>
		<?php $temp = 'mInsertsSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Inserts Sold</option>
		<?php $temp = 'mInsertsRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Inserts Retail $</option>
		<?php $temp = 'mInsertsProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Inserts Profit $</option>
		<?php $temp = 'mSignsOut' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Signs Out</option>
		<?php $temp = 'mSignsShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Signs Shown</option>
		<?php $temp = 'mSignsSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Signs Sold</option>
		<?php $temp = 'mSignsRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Signs Retail $</option>
		<?php $temp = 'mSignsProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Signs Profit $</option>
		<?php $temp = 'mRepeatsOut' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Repeats Out</option>
		<?php $temp = 'mRepeatsShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Repeats Shown</option>
		<?php $temp = 'mRepeatsSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Repeats Sold</option>
		<?php $temp = 'mRepeatsRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Repeats Retail $</option>
		<?php $temp = 'mRepeatsProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Repeats Profit $</option>
		<?php $temp = 'mOthersOut' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Others Out</option>
		<?php $temp = 'mOthersShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Others Shown</option>
		<?php $temp = 'mOthersSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Others Sold</option>
		<?php $temp = 'mOthersRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Others Retail $</option>
		<?php $temp = 'mOthersProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Others Profit $</option>
		<?php $temp = 'mTotalOut' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Total Out</option>
		<?php $temp = 'mTotalShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Total Shown</option>
		<?php $temp = 'mTotalSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Total Sold</option>
		<?php $temp = 'mTotalRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Total Retail $</option>
		<?php $temp = 'mTotalProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Total Profit $</option>
	</select></td></tr><tr><td>
	Direction: </td><td> <select id="dir" name="dir">
		<option value="2"<?php if ($dir == '2') echo "SELECTED"; ?>>Ascending</option>
		<option value="1"<?php if ($dir == '1') echo "SELECTED"; ?>>Decending</option>
	</select></td><?php } /* end if (is_manager) */ ?></tr>
	<tr><td>&nbsp;</td><td>
	<input type="submit" value="Filter & Rank" onClick="document.getElementById('limit').disabled = false;">
	</td></tr>
	</table>
</form><p>
<a href="selectvendor.php">Return to Vendor List</a><br>
<?php
if ($process) {
	$gWodsStats = new WodsStatsQuery();
	$start = strtotime($start);
	$end = strtotime($end);
	$virtdates = $gWodsStats->GetDates($start,$end,$resolution);
	?>
	Start Date: <?php echo date('m/d/Y',$virtdates['date']); ?><br>
	End Date: <?php echo date('m/d/Y',$virtdates['enddate']); ?><br>
	(May take a minute to calculate your results)<br>
	<?php
	flush();
	if ($is_manager) {
		$filter['wodsable'] = 'Y';
		$gWodsStats->GetSums(db_user_filterlist($filter), $start, $end, $resolution);
		if ($dir == 1) {
			$gWodsStats->Rank($sort,0);
		} elseif ($dir == 2) {
			$gWodsStats->Rank($sort,1);
		}
		if ($limit == -1) $limit = 0;
		$gWodsStats->Display('html',$limit);
	} else {
		$gWodsStats->GetSingleSum($userid, $start, $end, $resolution);
		$gWodsStats->Display('html');
	}
} else {
	?><br>Default WODS Stats are not generated, please press 'Filter & Rank' to see your stats<br>
	<?php
}
?>
