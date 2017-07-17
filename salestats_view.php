<?php
require("database.php");
require("secure.php");
if (!secure_is_manager()) {
	$user_id = $userid;
	$_POST['user_id'] = $userid;
}
$starting_year = 2004;
$current_year = date("Y", strtotime("now"));
$current_month = date("n", strtotime("now"));

function divide($num1, $num2) {
	if ($num2 == 0)
		$return_num = 0;
	else
		$return_num = $num1/$num2;
	return $return_num;
}

function percent($num, $decimals) {
	$num = round($num, $decimals+2);
	$return_num = $num * 100;
	return $return_num;
}
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?><br>
<span class="fat_black">VIEW SALES STATS</span><br>
<br>
<?php
if ($report_type == "display") { /* print report */
	require('salestats.php');
	$gSaleStats = new SaleStatsQuery();
	$gSaleStats->GetSingleSum($user_id, strtotime($select_date), strtotime($select_date), 'day');
	echo "<p class=\"fat_black\">".date("F j, Y",strtotime($select_date))."</p>";
	$gSaleStats->Display('html');
}
elseif ($report_type == "date") {
?>
<table border="0" cellspacing="0" cellpadding="5" width="760">
  <tr bgcolor="#FFFFFF"> 
    <td align="right"> <p><b>Date:</b></p></td>
    <td><p><?php echo date("F j, Y",strtotime($select_date)); ?></p></td>
  </tr>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>Select A Dealer:</b></p></td>
      <td><p><select name="user_id">
		<?php
		$sql = "SELECT DISTINCT users.ID, users.first_name, users.last_name FROM users INNER JOIN 
		 salestats ON users.ID=salestats.user_id WHERE users.nonPMD != 'Y' AND users.disabled != 'Y' AND salestats.stat_date LIKE '$select_date%'";
		$query = mysql_query($sql);
		checkDBError();
		if (mysql_num_rows($query) > 0)
			while ($result = mysql_fetch_array($query)) {
				echo "<option value=\"".$result["ID"]."\"";
				if ($result['ID'] == $userid) {
					echo " SELECTED";
				}
				echo ">".$result["last_name"].", ".$result["first_name"]."</option>\n";
			}
		?>
          </select></p></td>
    </tr>
    <tr bgcolor="#FFFFFF">
      <td>&nbsp;</td>
      <td><p><input name="report_type" type="hidden" id="report_type" value="display">
          <input name="select_date" type="hidden" id="select_date" value="<?php echo $select_date; ?>">
          <input type="submit" name="Submit" value="View Statistics"></p></td>
    </tr>
  </form>
</table>
<?php
}
elseif ((!secure_is_manager()) || $report_type == "user") { 
?>
<table border="0" cellspacing="0" cellpadding="5" width="760">
  <tr bgcolor="#FFFFFF"> 
    <td align="right"> <p><b>Dealer:</b></p></td>
    <td><p>
	<?php
	$sql = "SELECT first_name, last_name FROM users WHERE ID=$user_id AND nonPMD != 'Y' AND disabled != 'Y'";
	$query = mysql_query($sql);
	checkDBError();
	if (mysql_num_rows($query) > 0)
		$result = mysql_fetch_array($query);
		echo $result["last_name"].", ".$result["first_name"];
	?>
	</p></td>
  </tr>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>Select A Date:</b></p></td>
      <td><p>
          <select name="select_date">
        <?php
		$sql = "SELECT DISTINCT stat_date FROM salestats WHERE user_id=$user_id ORDER BY stat_date DESC";
		$query = mysql_query($sql);
		checkDBError();
		$num_rows = mysql_num_rows($query);
		if ($num_rows == 0)
			echo "<p>There is no data for the report you selected.</p>";
		else
			while ($result = mysql_fetch_array($query))
				echo "<option value=\"".date("Y-m-d",strtotime($result["stat_date"]))."\">".
				 date("F j, Y",strtotime($result["stat_date"]))."</option>\n";
		?>
          </select>
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF">
      <td>&nbsp;</td>
      <td><p><input name="report_type" type="hidden" id="report_type" value="display">
          <input name="user_id" type="hidden" id="user_id" value="<?php echo $user_id; ?>">
          <input type="submit" name="Submit" value="View Statistics"></p></td>
    </tr>
  </form>
</table>
<?php
}
else { /* print criteria form */
?>
  <table border="0" cellspacing="0" cellpadding="5" width="760">
    <tr bgcolor="#CCCC99"> 
      <td class="fat_black_12"><p>&nbsp;</p></td>
      <td class="fat_black_12"><p>Select a dealer or date</p></td>
    </tr>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>Select A Dealer:</b></p></td>
      <td><p><select name="user_id">
		<?php
		$sql = "SELECT ID, first_name, last_name FROM users WHERE nonPMD != 'Y' AND disabled != 'Y' ORDER BY last_name, first_name";
		$query = mysql_query($sql);
		checkDBError();
		if (mysql_num_rows($query) > 0)
			while ($result = mysql_fetch_array($query)) {
				echo "<option value=\"".$result["ID"]."\"";
				if ($result['ID'] == $userid) {
					echo " SELECTED";
				}
				echo ">".$result["last_name"].", ".$result["first_name"]."</option>\n";
			}
		?>
          </select><input name="report_type" type="hidden" id="report_type" value="user"> 
        <input type="submit" name="Submit" value="Go"></p></td>
    </tr>
</form>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>Or A Date:</b></p></td>
      <td><p><select name="select_date">
		<?php
		/*
			for ($year = $current_year; $year >= $starting_year; $year--) {
			//for ($year = $starting_year; $year <= $current_year; $year++) {
				if ($year == $current_year)
					$max_day = date("z",strtotime("now"))+2; //current day of the year
				else
					$max_day = 366; //all days of the year
				for ($day = $max_day; $day >= 1; $day--) {
				//for ($day = 1; $day < $max_day; $day++) {
					$timestamp = strtotime(($day)." January $year");
					echo "<option value=\"".date("Y-m-d",$timestamp)."\">".date("F j, Y",$timestamp)."</option>\n";
				}
			}
		*/
		for ($year = $current_year; $year >= $starting_year; $year--) {
		//for ($year = $starting_year; $year <= $current_year; $year++) {
			echo "<!-- year = $year, current_year = $current_year, ";
			if ($year == $current_year)
			{
				$max_day = gregoriantojd(date('m'), date('d'), date('Y'));
				$first_day_this_year = gregoriantojd(1, 1, date('Y'));
				//$max_day = date("z",strtotime("now"))+2; //current day of the year
			}
			else
			{
				$max_day = gregoriantojd(12, 31, $year);
				$first_day_this_year = gregoriantojd(1,1,$year);
				//$max_day = 366; //all days of the year
			}
			echo "max_day = $max_day -->\n";
			for ($day = $max_day; $day >= $first_day_this_year; $day--) {
			//for ($day = 1; $day < $max_day; $day++) {
				$timestamp = jdtounix($day);
				//$timestamp = strtotime(($day)." January $year");
				echo "<option value=\"".date("Y-m-d",$timestamp)."\">".date("F j, Y",$timestamp)."</option>\n";
			}
		}
		?>
          </select><input name="report_type" type="hidden" id="report_type" value="date"> 
        <input type="submit" name="Submit" value="Go"></p></td>
    </tr>
</form>
  </table>
<?php
}
mysql_close($link);
?>
</body>
</html>
