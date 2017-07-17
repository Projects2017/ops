<?php
require("database.php");
require("secure.php");
require("salestats.php");

$is_manager = secure_is_manager();
$is_admin = secure_is_admin();

// Load Variables
$start = $_POST['start'] ? $_POST['start'] : date('m/d/Y',strtotime('-1 week'));
$end = $_POST['end'] ? $_POST['end'] : date('m/d/Y');
$resolution = $_POST['resolution'] ? $_POST['resolution'] : 'day';
$process = $_POST['process'] ? true : false;
if ($is_manager) {
	$filter['team'] = $_POST['team'] ? $_POST['team'] : '*';
	$filter['manager'] = $_POST['manager'] ? $_POST['manager'] : '*';
	$filter['dealer_type'] = $_POST['dealer_type'] ? $_POST['dealer_type'] : 'B';
	$filter['level'] = $_POST['level'] ? $_POST['level'] : '*';
	$filter['disabled'] = $_POST['inactive'] ? $_POST['inactive'] : 'N';
	$filter['state'] = $_POST['state'] ? $_POST['state'] : '';
        if ($is_admin) {
            $filter['nonPMD'] = $_POST['nonPMD'] ? $_POST['nonPMD'] : 'N';
        }
	$limit = $_POST['limit'] ? $_POST['limit'] : 5;
	$sort = $_POST['sort'] ? $_POST['sort'] : 'mBusinessTotalProfit';
	$dir = $_POST['dir'] ? $_POST['dir'] : 1;
        $showentry = $_POST['showentry'] == 'Y' ? true: false;
        $inc0entries = $_POST['inc0entries'] == 'Y' ? true: false;
}

?>
<html>
<head>
<title>RSS FILTER & RANK STATS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
<link href="include/CalendarControl.css" rel="stylesheet" type="text/css">
<script src="include/common.js"></script>
<script src="include/CalendarControl.js"></script>
<script src="include/sorttable.js"></script>
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<span class="fat_black">FILTER & RANK SALES STATS</span><br>
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
	Type:</td><td> <select id="dealer_type" name="dealer_type">
		<option value="B" <?php if ($filter['dealer_type'] == 'B') echo "SELECTED"; ?>>Both</option>
		<option value="F" <?php if ($filter['dealer_type'] == 'F') echo "SELECTED"; ?>>Franchisee</option>
		<option value="L" <?php if ($filter['dealer_type'] == 'L') echo "SELECTED"; ?>>Licensee</option>
	</select></td></tr>
	<td>State:</td>
	<td><input type="text" value="<?php echo htmlentities($state); ?>" id="state" name="state" size="2"></td>
	<td>
	Inactives:</td><td> <select id="inactive" name="inactive">
		<option value="N" <?php if ($filter['disabled'] == "N") echo "SELECTED"; ?>>Exclude</option>
		<option value="*" <?php if ($filter['disabled'] == "*") echo "SELECTED"; ?>>Include</option>
		<option value="Y" <?php if ($filter['disabled'] == "Y") echo "SELECTED"; ?>>Only</option>
	</select></td>
        <tr>
            <td>
	Num Entries:</td><td> <input type="checkbox" name="showentry" value="Y"<?php if ($showentry) { echo ' CHECKED'; } ?>></td>
	<td>Inc. 0 Entries:</td>
	<td><input type="checkbox" name="inc0entries" value="Y"<?php if ($inc0entries) { echo ' CHECKED'; } ?>></td>
        <?php if ($is_admin): ?>
        <tr>
            <td>Show Non-RSS</td>
            <td><select id="dealer_type" name="nonPMD">
		<option value="*" <?php if ($filter['nonPMD'] == '*') echo "SELECTED"; ?>>All</option>
		<option value="Y" <?php if ($filter['nonPMD'] == 'Y') echo "SELECTED"; ?>>Yes</option>
		<option value="N" <?php if ($filter['nonPMD'] == 'N') echo "SELECTED"; ?>>No</option>
	</select></td>
        <?php endif; ?>
	<tr><td>
	Sort By:</td><td colspan="3"> <select id="sort" name="sort">
		<?php $temp = 'mUserLastName' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Dealer Name</option>
		<?php $temp = 'mUserFirstName' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Location</option>
		<?php $temp = 'mMattressCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Calls</option>
		<?php $temp = 'mMattressApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Appointments</option>
		<?php $temp = 'mMattressAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Appointments %</option>
		<?php $temp = 'mMattressShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Showings</option>
		<?php $temp = 'mMattressShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Showings %</option>
		<?php $temp = 'mMattressSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Sold</option>
		<?php $temp = 'mMattressSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Sold %</option>
		<?php $temp = 'mMattressRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Retail</option>
		<?php $temp = 'mMattressProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Profit</option>
		<?php $temp = 'mMattressProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Profit/Sale</option>
		<?php $temp = 'mMattressGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Gross Margin</option>
		<?php $temp = 'mMattressCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Calls/Sale</option>
		<?php $temp = 'mMattressProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress $ per Call</option>
		<?php $temp = 'mMattressProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Profit as % of Business</option>
		<?php $temp = 'mEntryFurnitureCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Calls</option>
		<?php $temp = 'mEntryFurnitureApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Appointments</option>
		<?php $temp = 'mEntryFurnitureAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Appointments %</option>
		<?php $temp = 'mEntryFurnitureShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Showings</option>
		<?php $temp = 'mEntryFurnitureShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Showings %</option>
		<?php $temp = 'mEntryFurnitureSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Sold</option>
		<?php $temp = 'mEntryFurnitureSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Sold %</option>
		<?php $temp = 'mEntryFurnitureRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Retail</option>
		<?php $temp = 'mEntryFurnitureProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Profit</option>
		<?php $temp = 'mEntryFurnitureProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Profit/Sale</option>
		<?php $temp = 'mEntryFurnitureGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Gross Margin</option>
		<?php $temp = 'mEntryFurnitureCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Calls/Sale</option>
		<?php $temp = 'mEntryFurnitureProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture $ per Call</option>
		<?php $temp = 'mEntryFurnitureProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Entry Furniture Profit as % of Business</option>
		<?php $temp = 'mSignsCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Calls</option>
		<?php $temp = 'mSignsApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Appointments</option>
		<?php $temp = 'mSignsAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Appointments %</option>
		<?php $temp = 'mSignsShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Showings</option>
		<?php $temp = 'mSignsShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Showings %</option>
		<?php $temp = 'mSignsSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Sold</option>
		<?php $temp = 'mSignsSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Sold %</option>
		<?php $temp = 'mSignsRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Retail</option>
		<?php $temp = 'mSignsProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Profit</option>
		<?php $temp = 'mSignsProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Profit/Sale</option>
		<?php $temp = 'mSignsGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Gross Margin</option>
		<?php $temp = 'mSignsCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Calls/Sale</option>
		<?php $temp = 'mSignsProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs $ per Call</option>
		<?php $temp = 'mSignsProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Signs Profit as % of Business</option>
		<?php $temp = 'mInternetCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Calls</option>
		<?php $temp = 'mInternetApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Appointments</option>
		<?php $temp = 'mInternetAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Appointments %</option>
		<?php $temp = 'mInternetShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Showings</option>
		<?php $temp = 'mInternetShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Showings %</option>
		<?php $temp = 'mInternetSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Sold</option>
		<?php $temp = 'mInternetSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Sold %</option>
		<?php $temp = 'mInternetRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Retail</option>
		<?php $temp = 'mInternetProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Profit</option>
		<?php $temp = 'mInternetProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Profit/Sale</option>
		<?php $temp = 'mInternetGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Gross Margin</option>
		<?php $temp = 'mInternetCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Calls/Sale</option>
		<?php $temp = 'mInternetProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet $ per Call</option>
		<?php $temp = 'mInternetProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress Internet Profit as % of Business</option>
		<?php $temp = 'mCLCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Calls</option>
		<?php $temp = 'mCLApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Appointments</option>
		<?php $temp = 'mCLAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Appointments %</option>
		<?php $temp = 'mCLShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Showings</option>
		<?php $temp = 'mCLShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Showings %</option>
		<?php $temp = 'mCLSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Sold</option>
		<?php $temp = 'mCLSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Sold %</option>
		<?php $temp = 'mCLRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Retail</option>
		<?php $temp = 'mCLProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Profit</option>
		<?php $temp = 'mCLProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Profit/Sale</option>
		<?php $temp = 'mCLGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Gross Margin</option>
		<?php $temp = 'mCLCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Calls/Sale</option>
		<?php $temp = 'mCLProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta $ per Call</option>
		<?php $temp = 'mCLProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Mattress CL Beta Profit as % of Business</option>
		<?php $temp = 'mBeddingTotalCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Calls</option>
		<?php $temp = 'mBeddingTotalApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Appointments</option>
		<?php $temp = 'mBeddingTotalAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Appointments %</option>
		<?php $temp = 'mBeddingTotalShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Showings</option>
		<?php $temp = 'mBeddingTotalShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Showings %</option>
		<?php $temp = 'mBeddingTotalSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Sold</option>
		<?php $temp = 'mBeddingTotalSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Sold %</option>
		<?php $temp = 'mBeddingTotalRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Retail</option>
		<?php $temp = 'mBeddingTotalProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Profit</option>
		<?php $temp = 'mBeddingTotalProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Profit/Sale</option>
		<?php $temp = 'mBeddingTotalGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Gross Margin</option>
		<?php $temp = 'mBeddingTotalCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Calls/Sale</option>
		<?php $temp = 'mBeddingTotalProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total $ per Call</option>
		<?php $temp = 'mBeddingTotalProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Bedding Total Profit as % of Business</option>
		<?php $temp = 'mBedroomCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Calls</option>
		<?php $temp = 'mBedroomApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Appointments</option>
		<?php $temp = 'mBedroomAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Appointments %</option>
		<?php $temp = 'mBedroomShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Showings</option>
		<?php $temp = 'mBedroomShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Showings %</option>
		<?php $temp = 'mBedroomSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Sold</option>
		<?php $temp = 'mBedroomSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Sold %</option>
		<?php $temp = 'mBedroomRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Retail</option>
		<?php $temp = 'mBedroomProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Profit</option>
		<?php $temp = 'mBedroomProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Profit/Sale</option>
		<?php $temp = 'mBedroomGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Gross Margin</option>
		<?php $temp = 'mBedroomCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Calls/Sale</option>
		<?php $temp = 'mBedroomProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR $ per Call</option>
		<?php $temp = 'mBedroomProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>BR Profit as % of Business</option>
		<?php $temp = 'mLivingRoomCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Calls</option>
		<?php $temp = 'mLivingRoomApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Appointments</option>
		<?php $temp = 'mLivingRoomAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Appointments %</option>
		<?php $temp = 'mLivingRoomShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Showings</option>
		<?php $temp = 'mLivingRoomShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Showings %</option>
		<?php $temp = 'mLivingRoomSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Sold</option>
		<?php $temp = 'mLivingRoomSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Sold %</option>
		<?php $temp = 'mLivingRoomRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Retail</option>
		<?php $temp = 'mLivingRoomProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Profit</option>
		<?php $temp = 'mLivingRoomProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Profit/Sale</option>
		<?php $temp = 'mLivingRoomGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Gross Margin</option>
		<?php $temp = 'mLivingRoomCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Calls/Sale</option>
		<?php $temp = 'mLivingRoomProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR $ per Call</option>
		<?php $temp = 'mLivingRoomProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>LR Profit as % of Business</option>
		<?php $temp = 'mDiningRoomCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Calls</option>
		<?php $temp = 'mDiningRoomApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Appointments</option>
		<?php $temp = 'mDiningRoomAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Appointments %</option>
		<?php $temp = 'mDiningRoomShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Showings</option>
		<?php $temp = 'mDiningRoomShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Showings %</option>
		<?php $temp = 'mDiningRoomSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Sold</option>
		<?php $temp = 'mDiningRoomSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Sold %</option>
		<?php $temp = 'mDiningRoomRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Retail</option>
		<?php $temp = 'mDiningRoomProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Profit</option>
		<?php $temp = 'mDiningRoomProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Profit/Sale</option>
		<?php $temp = 'mDiningRoomGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Gross Margin</option>
		<?php $temp = 'mDiningRoomCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Calls/Sale</option>
		<?php $temp = 'mDiningRoomProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR $ per Call</option>
		<?php $temp = 'mDiningRoomProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>DR Profit as % of Business</option>
		<?php $temp = 'mFurnitureSignsCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Calls</option>
		<?php $temp = 'mFurnitureSignsApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Appointments</option>
		<?php $temp = 'mFurnitureSignsAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Appointments %</option>
		<?php $temp = 'mFurnitureSignsShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Showings</option>
		<?php $temp = 'mFurnitureSignsShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Showings %</option>
		<?php $temp = 'mFurnitureSignsSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Sold</option>
		<?php $temp = 'mFurnitureSignsSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Sold %</option>
		<?php $temp = 'mFurnitureSignsRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Retail</option>
		<?php $temp = 'mFurnitureSignsProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Profit</option>
		<?php $temp = 'mFurnitureSignsProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Profit/Sale</option>
		<?php $temp = 'mFurnitureSignsGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Gross Margin</option>
		<?php $temp = 'mFurnitureSignsCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Calls/Sale</option>
		<?php $temp = 'mFurnitureSignsProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs $ per Call</option>
		<?php $temp = 'mFurnitureSignsProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Signs Profit as % of Business</option>
				<?php $temp = 'mFurnitureInternetCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Calls</option>
		<?php $temp = 'mFurnitureInternetApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Appointments</option>
		<?php $temp = 'mFurnitureInternetAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Appointments %</option>
		<?php $temp = 'mFurnitureInternetShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Showings</option>
		<?php $temp = 'mFurnitureInternetShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Showings %</option>
		<?php $temp = 'mFurnitureInternetSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Sold</option>
		<?php $temp = 'mFurnitureInternetSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Sold %</option>
		<?php $temp = 'mFurnitureInternetRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Retail</option>
		<?php $temp = 'mFurnitureInternetProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Profit</option>
		<?php $temp = 'mFurnitureInternetProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Profit/Sale</option>
		<?php $temp = 'mFurnitureInternetGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Gross Margin</option>
		<?php $temp = 'mFurnitureInternetCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Calls/Sale</option>
		<?php $temp = 'mFurnitureInternetProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet $ per Call</option>
		<?php $temp = 'mFurnitureInternetProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Internet Profit as % of Business</option>
				<?php $temp = 'mFurnitureCLCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Calls</option>
		<?php $temp = 'mFurnitureCLApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Appointments</option>
		<?php $temp = 'mFurnitureCLAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Appointments %</option>
		<?php $temp = 'mFurnitureCLShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Showings</option>
		<?php $temp = 'mFurnitureCLShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Showings %</option>
		<?php $temp = 'mFurnitureCLSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Sold</option>
		<?php $temp = 'mFurnitureCLSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Sold %</option>
		<?php $temp = 'mFurnitureCLRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Retail</option>
		<?php $temp = 'mFurnitureCLProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Profit</option>
		<?php $temp = 'mFurnitureCLProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Profit/Sale</option>
		<?php $temp = 'mFurnitureCLGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Gross Margin</option>
		<?php $temp = 'mFurnitureCLCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Calls/Sale</option>
		<?php $temp = 'mFurnitureCLProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta $ per Call</option>
		<?php $temp = 'mFurnitureCLProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture CL Beta Profit as % of Business</option>
		<?php $temp = 'mFurnitureTotalCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Calls</option>
		<?php $temp = 'mFurnitureTotalApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Appointments</option>
		<?php $temp = 'mFurnitureTotalAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Appointments %</option>
		<?php $temp = 'mFurnitureTotalShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Showings</option>
		<?php $temp = 'mFurnitureTotalShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Showings %</option>
		<?php $temp = 'mFurnitureTotalSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Sold</option>
		<?php $temp = 'mFurnitureTotalSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Sold %</option>
		<?php $temp = 'mFurnitureTotalRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Retail</option>
		<?php $temp = 'mFurnitureTotalProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Profit</option>
		<?php $temp = 'mFurnitureTotalProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Profit/Sale</option>
		<?php $temp = 'mFurnitureTotalGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Gross Margin</option>
		<?php $temp = 'mFurnitureTotalCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Calls/Sale</option>
		<?php $temp = 'mFurnitureTotalProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total $ per Call</option>
		<?php $temp = 'mFurnitureTotalProfitPercentBusiness' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Furniture Total Profit as % of Business</option>
		<?php $temp = 'mBusinessTotalCalls' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Calls</option>
		<?php $temp = 'mBusinessTotalApts' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Appointments</option>
		<?php $temp = 'mBusinessTotalAptsPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Appointments %</option>
		<?php $temp = 'mBusinessTotalShow' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Showings</option>
		<?php $temp = 'mBusinessTotalShowPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Showings %</option>
		<?php $temp = 'mBusinessTotalSold' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Sold</option>
		<?php $temp = 'mBusinessTotalSoldPercent' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Sold %</option>
		<?php $temp = 'mBusinessTotalRetail' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Retail</option>
		<?php $temp = 'mBusinessTotalProfit' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Profit</option>
		<?php $temp = 'mBusinessTotalProfitSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Profit/Sale</option>
		<?php $temp = 'mBusinessTotalGrossMargin' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Gross Margin</option>
		<?php $temp = 'mBusinessTotalCallsPerSale' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total Calls/Sale</option>
		<?php $temp = 'mBusinessTotalProfitPerCall' ?><option value="<?php=$temp?>" <?php if ($sort == $temp) echo "SELECTED"; ?>>Business Total $ per Call</option>
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
	$gSaleStats = new SaleStatsQuery();
	$start = strtotime($start);
	$end = strtotime($end);
	$virtdates = $gSaleStats->GetDates($start,$end,$resolution);
	?>
	Start Date: <?php echo date('m/d/Y',$virtdates['date']); ?><br>
	End Date: <?php echo date('m/d/Y',$virtdates['enddate']); ?><br>
	(May take a minute to calculate your results)<br>
	<?php
	flush();
	if ($is_manager) {
		$gSaleStats->GetSums(db_user_filterlist($filter), $start, $end, $resolution, !$inc0entries);
		if ($dir == 1) {
			$gSaleStats->Rank($sort,0);
		} elseif ($dir == 2) {
			$gSaleStats->Rank($sort,1);
		}
		if ($limit == -1) $limit = 0;
		$gSaleStats->Display('html',$limit,'',$showentry);
	} else {
		$gSaleStats->GetSingleSum($userid, $start, $end, $resolution);
		$gSaleStats->Display('html');
	}
} else {
	?><br>Default Stats are no longer generated, please press 'Filter & Rank' to see your stats<br>
	<?php
}
?>
