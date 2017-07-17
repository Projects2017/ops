<?php
require("database.php");
require("secure.php");
$g_manager_name = manager_name();
$starting_year = 2004;
$current_year = date("Y", strtotime("now"));
$current_month = date("n", strtotime("now"));
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?><br>
<span class="fat_black">VIEW WODS SALES STATS</span><br>
<?php
if ($type == "") { /* print form links */
?>
  <br>
  <table border="0" cellspacing="0" cellpadding="5" width="760">
    <tr bgcolor="#CCCC99"> 
      <td class="fat_black_12"><p>&nbsp;</p></td>
      <td class="fat_black_12"><p>Select Report Type</p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><p>&nbsp;</p></td>
      <td><p><a href="wodsstats_view.php">Daily</a> | <a href="<?php echo $_SERVER['PHP_SELF']; ?>?type=weekly">Weekly</a> | <a href="<?php echo $_SERVER['PHP_SELF']; ?>?type=monthly">Monthly</a> 
        | <a href="<?php echo $_SERVER['PHP_SELF']; ?>?type=ytd">Year-to-Date</a> | <a href="wodsstats_filter.php">Custom</a></p></td>
    </tr>
  </table>
  <p><a href="selectvendor.php">Return to Vendor List</a>
<?php
/* =========== PRINT REPORT =========== */
} elseif ($process == "y") { /* print report */
	require("wodsstats.php");
	$filter = array();
	if (secure_is_manager()) {
		// If Manager, Allow Sort by Division and Manager
		if ($division != "all") {
			// Filter by Division
			$report_title_clause .= " ($division Division)";
			$filter['division'] = $division;
		}
		if ($manager != "all") {
			// Filter by Manager
			$report_title_clause .= " (".$g_manager_name." $manager)";
			$filter['manager'] = $manager;
		}
	}
	$resolution = 'day';
	if ($type == "monthly") {
		// Filter to Month
		$report_title = "Monthly Report for ".date("F, Y",strtotime($timespan));
		$start = strtotime($timespan);
		$end = $start;
		$resolution = 'month';
	} elseif ($type == "weekly") {
		// Filter to Week
		$tok = strtok($timespan, ",");
		$count = 0;
		while ($tok) {
		   if ($count == 0) $from_date = $tok; else $to_date = $tok;
		   //echo "<p>$tok || $count</p>";
		   $tok = strtok(",");
		   $count++;
		}
		$report_title = "Weekly Report for the week ending ".date("F j, Y",strtotime($to_date));
		$start = strtotime($from_date);
		$end = strtotime($to_date);
	} else { /* $type == "ytd" */
		// Filter to Year to Date
		$report_title = "Year-To-Date Report for the year ".date("Y",strtotime($timespan));
		$resolution = 'year';
		$start = strtotime($timespan);
		$end = $start;
	}
	
	// Process and Display
	$gWodsStats = new WodsStatsQuery();
	if (secure_is_manager()) {
		$filter['wodsable'] = 'Y';
		$gWodsStats->GetSums(db_user_filterlist($filter), $start, $end, $resolution);
		$gWodsStats->Rank('mUserLastName',0);
	} else {
		$virtdates = $gWodsStats->GetDates($start, $end, $resolution);
		$gWodsStats->GetSingleSum($userid, $start, $end, $resolution);
	}
	echo "<p class=\"fat_black\">".strtoupper($report_title)." ".$report_title_clause."</p>";
	$gWodsStats->Display('html');
}
else { /* print criteria form */

	if ($type == "monthly") {
		$title = "Select Monthly Report Criteria";
		$label = "Month";
		$select_string = "";
		for ($year = $current_year; $year >= $starting_year; $year--) {
		//for ($year = $starting_year; $year <= $current_year; $year++) {
			if ($year == $current_year)
				$ubound = $current_month;
			else
				$ubound = 12;
			for ($month = $ubound; $month >= 1; $month--) {
			//for ($month = 1; $month <= $ubound; $month++) {
				$month_name = date("F",strtotime("$year-$month-01"));
				if (($month == $current_month) && ($year == $current_year))
					$select_string .= "<option value=\"$year-$month-01\" selected>$month_name $year</option>\n";
				else
					$select_string .= "<option value=\"$year-$month-01\">$month_name $year</option>\n";
			}
		}
	}
	elseif ($type == "weekly") {
		$title = "Select Weekly Report Criteria";
		$label = "Week Ending";
		$select_string = "";
		function weekDropDown($day, $month, $year, $selected=""){
			$start=mktime(0, 0, 0 , date("$month"), date("$day"),  date("$year"));
			$end=mktime(0, 0, 0 , date("m"), date("d"),  date("Y"));
			$days=floor(($end-$start)/86400)+1;
			for ($i = 0; $i <= $days; $i++){
				$theday = mktime (0,0,0,date("m") ,date("d")-$i ,date("Y"));
				$value=date("Y-m-d",$theday);
				$dow=date("D",$theday);
				if ($dow=="Sun") {
					$start=$value;
					$time=strtotime($start);
					$end= date("Y-m-d", mktime (0,0,0,date("m", $time) ,date("d", $time)+6 ,date("Y",$time)));
					if(($selected==$start)) $select="selected=\"selected\"";
					$html.="<option value=\"$start,$end\" $select>".date("F j, Y", strtotime($end))."</option>\n";
				}
			}
			return $html;
		}
		$select_string = weekDropDown(28,12,2003); /* start week beginning December 28, 2003 */
	}
	else { /* $type == "ytd" */
		$title = "Select Year-To-Date Report Criteria";
		$label = "Year";
		$select_string = "";
		for ($year = $current_year; $year >= $starting_year; $year--)
			if ($year == $current_year)
				$select_string .= "<option value=\"$year-01-01\" selected>$year</option>\n";
			else
				$select_string .= "<option value=\"$year-01-01\">$year</option>\n";
	}
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <table border="0" cellspacing="0" cellpadding="5" width="760">
    <tr bgcolor="#CCCC99"> 
      <td class="fat_black_12"><p>&nbsp;</p></td>
      <td class="fat_black_12"><p><?php echo $title; ?></p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><p><b><?php echo $label; ?>:</b></p></td>
      <td><p><select name="timespan">
            <?php echo $select_string; ?>
          </select></p></td>
    </tr>
	<?php if (secure_is_manager()) { ?>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>Division:</b></p></td>
      <td><p><select name="division">
            <option value="all">All Divisions</option>
            <option value="1">1</option>
            <option value="2">2</option>
          </select></p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b><?php= $g_manager_name ?>:</b></p></td>
      <td><p><select name="manager">
            <option value="all">All <?php=$g_manager_name ?>s</option>
			<?php $managers = managers_list(); foreach ($managers as $manager) { ?>
            <option value="<?php= $manager['name'] ?>"><?php= $manager['name'] ?></option>
			<?php } ?>
          </select></p></td>
    </tr>
    <?php } /* end if manager */ ?>
    <tr> 
      <td colspan="2" align="right"><input name="process" type="hidden" id="process" value="y"> 
	   <input name="type" type="hidden" id="type" value="<?php echo $type; ?>"> 
        <input type="submit" name="Submit" value="View Report"> </td>
    </tr>
  </table>
</form>
<?php
}
mysql_close($link);
?>
</body>
</html>
