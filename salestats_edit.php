<?php
require("database.php");
require("secure.php");
require("salestats.php");
if (secure_is_manager() && $_REQUEST['user_id']) $userid = $_REQUEST['user_id'];
$starting_year = 2004;
$current_year = date("Y", strtotime("now"));
$current_month = date("n", strtotime("now"));
$field_width = 6;
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
<script type="text/javascript">
function confirmDelete(delUrl) {
  if (confirm("Are you sure you want to delete this set of statistics? This action can not be undone.")) {
    document.location = delUrl;
  }
}
</script>
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?>
<br>
<span class="fat_black">ENTER & EDIT SALES STATS</span><br>
<br>
<?php
if ($action == "save") {
	$sql = "SELECT * FROM `salestats` WHERE `user_id` = '".$user_id."' AND `stat_date` = '".$stat_date."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	if (mysql_num_rows($result) > 1) {
		$sql = "DELETE FROM `salesstats` WHERE `user_id` = '".$user_id."' AND `stat_date` = '".$stat_date."'";
		mysql_query($sql);
		checkDBerror($sql);
		$action = "create";
	} elseif (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		$stat_id = $row['stat_id'];
		$action = "edit";
	} elseif (mysql_num_rows($result) < 1) {
		$action = "create";
	}
} 
// Do edits!
if ($action == "edit") {
	$sql = buildUpdateQuery("salestats", "stat_id=$stat_id");
	mysql_query($sql);
	checkDBError();
	echo "<p>Your changes have been saved.</p>
	 <p><b><a href=\"salestats_edit.php";
	 if (secure_is_manager()) echo "?user_id=$userid";
	echo "\">Back to Sales Statistics listing</a></b></p>
	 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
}
elseif ($action == "create") {
	$sql = buildInsertQuery("salestats");
	mysql_query($sql);
	checkDBError();
	echo "<p>Thank you. Your sales statistics have been received.</p>
	 <p><b><a href=\"salestats_form.php";
	 if (secure_is_manager()) echo "?user_id=$userid";
	 echo "\">Enter more sales statistics</a></b></p>
	 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
}
elseif ($action == "delete") {
	mysql_query("DELETE FROM salestats WHERE stat_id=$stat_id");
	checkDBError();
	echo "<p>The statistics set has been deleted.</p>
	 <p><b><a href=\"salestats_edit.php";
	if (secure_is_manager()) echo "?user_id=$userid";
	echo "\">Back to Sales Statistics listing</a></b></p>
	 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
}
elseif ($action == "display") { /* print form */
	$sql = "SELECT salestats.*, users.ID, users.first_name, users.last_name
	 FROM salestats INNER JOIN users ON salestats.user_id=users.ID
	 WHERE salestats.user_id=$userid AND salestats.stat_date LIKE '$select_date%' ORDER BY users.last_name";
	$query = mysql_query($sql);
	checkDBError();
	$num_rows = mysql_num_rows($query);
	if ($num_rows == 0) {
		$sql = "SELECT users.ID, users.first_name, users.last_name FROM users WHERE users.ID = '$userid'";
		$query = mysql_query($sql);
		$result = mysql_fetch_assoc($query);
		$result['stat_date'] = $select_date;
		$result['user_id'] = $userid;
		$result['action'] = 'create';
	} else {
		$result = mysql_fetch_assoc($query);
		$result['action'] = 'edit';
	}
?>
<p><b><?php echo $result["last_name"].", ".$result["first_name"]." 
 (".date("F j, Y",strtotime($result["stat_date"])).")"; ?></b></p>
<table width="790" border="0" cellpadding="5" cellspacing="0">
  <tr bgcolor="#CCCC99"> 
    <td class="fat_black_12"><p>&nbsp;</p></td>
    <td class="fat_black_12"><p align="right">Calls</p></td>
    <td class="fat_black_12"><p align="right">Appts</p></td>
    <td class="fat_black_12"><p align="right">Show</p></td>
    <td class="fat_black_12"><p align="right">Sold</p></td>
    <td class="fat_black_12"><p align="right">Retail</p></td>
    <td class="fat_black_12"><p align="right">Profit</p></td>
  </tr>
  <form action="salestats_edit.php" method="post">
    <tr> 
      <td colspan="7"><p><b>Bedding</b></p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Mattress</p></td>
      <td><p align="right">
          <input name="ads_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["ads_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="ads_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["ads_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="ads_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["ads_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="ads_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["ads_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="ads_retail" type="text" size="8" maxlength="15" value="<?php echo $result["ads_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="ads_profit" type="text" size="8" maxlength="15" value="<?php echo $result["ads_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Entry Furniture</p></td>
      <td><p align="right">
          <input name="babycase_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["babycase_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="babycase_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["babycase_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="babycase_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["babycase_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="babycase_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["babycase_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="babycase_retail" type="text" size="8" maxlength="15" value="<?php echo $result["babycase_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="babycase_profit" type="text" size="8" maxlength="15" value="<?php echo $result["babycase_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Mattress Signs</p></td>
      <td><p align="right">
          <input name="bedding_signs_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_signs_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_signs_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_signs_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_signs_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_signs_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_signs_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_signs_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedding_signs_retail" type="text" size="8" maxlength="15" value="<?php echo $result["bedding_signs_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedding_signs_profit" type="text" size="8" maxlength="15" value="<?php echo $result["bedding_signs_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Mattress Internet</p></td>
      <td><p align="right">
          <input name="bedding_internet_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_internet_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_internet_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_internet_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_internet_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_internet_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_internet_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_internet_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedding_internet_retail" type="text" size="8" maxlength="15" value="<?php echo $result["bedding_internet_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedding_internet_profit" type="text" size="8" maxlength="15" value="<?php echo $result["bedding_internet_profit"]; ?>">
        </p></td>
    </tr>
    <?php if (SaleStats::inCLBeta($userid)): ?>
        <tr bgcolor="#FFFFFF">
      <td><p align="right">Mattress CL Beta</p></td>
      <td><p align="right">
          <input name="bedding_craigslist_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_craigslist_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_craigslist_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_craigslist_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_craigslist_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_craigslist_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedding_craigslist_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedding_craigslist_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedding_craigslist_retail" type="text" size="8" maxlength="15" value="<?php echo $result["bedding_craigslist_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedding_craigslist_profit" type="text" size="8" maxlength="15" value="<?php echo $result["bedding_craigslist_profit"]; ?>">
        </p></td>
    </tr>
    <?php endif; ?>
    <tr> 
      <td colspan="7"><p><b>Case Goods</b></p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Bedroom sets</p></td>
      <td><p align="right">
          <input name="bedroom_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedroom_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedroom_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedroom_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedroom_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedroom_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="bedroom_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["bedroom_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedroom_retail" type="text" size="8" maxlength="15" value="<?php echo $result["bedroom_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="bedroom_profit" type="text" size="8" maxlength="15" value="<?php echo $result["bedroom_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Living Room sets</p></td>
      <td><p align="right">
          <input name="living_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["living_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="living_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["living_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="living_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["living_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="living_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["living_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="living_retail" type="text" size="8" maxlength="15" value="<?php echo $result["living_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="living_profit" type="text" size="8" maxlength="15" value="<?php echo $result["living_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Dining Room</p></td>
      <td><p align="right">
          <input name="dining_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["dining_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="dining_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["dining_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="dining_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["dining_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="dining_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["dining_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="dining_retail" type="text" size="8" maxlength="15" value="<?php echo $result["dining_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="dining_profit" type="text" size="8" maxlength="15" value="<?php echo $result["dining_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Furniture Signs</p></td>
      <td><p align="right">
          <input name="cg_signs_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_signs_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_signs_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_signs_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_signs_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_signs_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_signs_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_signs_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="cg_signs_retail" type="text" size="8" maxlength="15" value="<?php echo $result["cg_signs_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="cg_signs_profit" type="text" size="8" maxlength="15" value="<?php echo $result["cg_signs_profit"]; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Furniture Internet</p></td>
      <td><p align="right">
          <input name="cg_internet_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_internet_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_internet_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_internet_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_internet_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_internet_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_internet_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_internet_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="cg_internet_retail" type="text" size="8" maxlength="15" value="<?php echo $result["cg_internet_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="cg_internet_profit" type="text" size="8" maxlength="15" value="<?php echo $result["cg_internet_profit"]; ?>">
        </p></td>
    </tr>
    <?php if (SaleStats::inCLBeta($userid)): ?>
    <tr bgcolor="#FFFFFF">
      <td><p align="right">Furniture CL Beta</p></td>
      <td><p align="right">
          <input name="cg_craigslist_calls" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_craigslist_calls"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_craigslist_appts" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_craigslist_appts"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_craigslist_show" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_craigslist_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="cg_craigslist_sold" type="text" size="<?php echo $field_width; ?>" maxlength="<?php echo $field_width; ?>" value="<?php echo $result["cg_craigslist_sold"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="cg_craigslist_retail" type="text" size="8" maxlength="15" value="<?php echo $result["cg_craigslist_retail"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="cg_craigslist_profit" type="text" size="8" maxlength="15" value="<?php echo $result["cg_craigslist_profit"]; ?>">
        </p></td>
    </tr>
    <?php endif; ?>
    <tr> 
      <td colspan="7" align="right"> <input name="action" type="hidden" id="action" value="save"> 
        <input name="stat_id" type="hidden" id="stat_id" value="<?php echo $result["stat_id"]; ?>">
        <input name="user_id" type="hidden" id="user_id" value="<?php echo $result["user_id"]; ?>">
        <input name="stat_date" type="hidden" id="stat_date" value="<?php echo $result["stat_date"]; ?>">
        <input type="submit" name="Save" value="Save Changes"> </td>
    </tr>
  </form>
  <?php if ($result['stat_id']) { ?>
  <tr> 
    <td colspan="7" align="right"><a href="javascript:confirmDelete('salestats_edit.php?action=delete&stat_id=<?php echo $result["stat_id"]; if (secure_is_manager()) echo "&user_id=$userid"; ?>')">Delete
      These Statistics</a></td>
  </tr>
  <?php } ?>
</table>
<br>
<?php
}
else { /* print criteria form */
?>
  <table border="0" cellspacing="0" cellpadding="5" width="760">
    <tr bgcolor="#CCCC99"> 
      <td class="fat_black_12"><p>&nbsp;</p></td>
      <td class="fat_black_12"><p>Choose statistics to edit or delete</p></td>
    </tr>

	<?php if (secure_is_manager()) { ?>
	<form action="salestats_edit.php" method="get">
	<tr bgcolor="#FFFFFF">
		<td align="right"> <p><b>Select A Dealer:</b></p></td>
		<td><p><select name="user_id">
		<?php
		if (!secure_is_admin()) {
			$extra = "AND ((users.admin = '') OR (users.ID = '".$userid."'))"; //"AND (!users.admin OR users.ID = '".$userid."')";
		}
		$sql = "SELECT DISTINCT users.ID, users.first_name, users.last_name FROM users INNER JOIN 
		 salestats ON users.ID=salestats.user_id WHERE users.nonPMD != 'Y' AND users.disabled != 'Y' ".$extra." AND salestats.stat_date LIKE '$select_date%' ORDER BY users.last_name";
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
          </select><input type="submit" name="Submit" value="Go">
          
		</p></form></td>
	</td>
	</tr>
	</form>
	<?php } ?><form action="salestats_edit.php" method="get">
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>or Select A Date:</b></p></td>
      <td><p>
        <?php
		/*$sql = "SELECT DISTINCT stat_date FROM salestats WHERE user_id=$userid ORDER BY stat_date";
		$query = mysql_query($sql);
		checkDBError();
		$num_rows = mysql_num_rows($query);
		if ($num_rows == 0)
			echo "You have no statistics to edit.";
		else {
			echo "<select name=\"select_date\">";
			while ($result = mysql_fetch_array($query)) {
				echo "<option value=\"".date("Y-m-d",strtotime($result["stat_date"]))."\">".
				 date("F j, Y",strtotime($result["stat_date"]))."</option>\n";
			}
			echo "</select><input name=\"action\" type=\"hidden\" id=\"action\" value=\"display\"> 
	        <input type=\"submit\" name=\"Submit\" value=\"Go\">";
		} */
		
		if (secure_is_admin()) $limit = 365;
		elseif (secure_is_manager()) $limit = 60;
		else $limit = 14;
		$limit = 365;
		
		$sql = "SELECT DISTINCT stat_date FROM salestats WHERE user_id=$userid ORDER BY stat_date DESC LIMIT $limit";
		$query = mysql_query($sql);
		checkDBError();
		$has_done = array();
		while ($row = mysql_fetch_assoc($query)) {
			$has_done[date("Y-m-d",strtotime($row['stat_date']))] = true;
		}
		
		echo "<select name=\"select_date\">";
		$curday = time();
		$i = 0;
		while ($i < $limit) {
			$curday = mktime(12,0,0,date("m",$curday),date("j",$curday),date("Y",$curday));
			$d = date("Y-m-d",$curday);
			echo "<option value=\"".$d."\">".
				 date("F j, Y",$curday);
			if (isset($has_done[$d])) echo "*";
			echo "</option>\n";
			++$i;
			$curday -= 60*60*24;
		}
		
		echo "</select>";
		if (secure_is_manager()) echo "<input name=\"user_id\" type=\"hidden\" id=\"user_id\" value=\"$userid\">";
		echo "<input name=\"action\" type=\"hidden\" id=\"action\" value=\"display\"> 
	        <input type=\"submit\" name=\"Submit\" value=\"Go\">";
		?>
          </p></td>
    </tr></form>
  </table>
  <p><a href="selectvendor.php">Return to Vendor List</a>
<?php
}
mysql_close($link);
?>
</body>
</html>
