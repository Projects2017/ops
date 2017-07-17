<?php
require("database.php");
require("secure.php");
if (secure_is_manager() && $_REQUEST['user_id']) $userid = $_REQUEST['user_id'];
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
<span class="fat_black">ENTER & EDIT WODS STATS</span><br>
<br>
<?php
if ($action == "save") {
	$sql = "SELECT * FROM `wodsstats` WHERE `user_id` = '".$user_id."' AND `stat_date` = '".$stat_date."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	if (mysql_num_rows($result) > 1) {
		$sql = "DELETE FROM `wodsstats` WHERE `user_id` = '".$user_id."' AND `stat_date` = '".$stat_date."'";
		mysql_query($sql);
		checkDBerror($sql);
		$action = "create";
	} elseif (mysql_num_rows($result) == 1) {
		$row = mysql_fetch_assoc($result);
		$stat_id = $row['stat_id'];
		$action = "edit";
		// increment the edits tracking field by 1
		$edits = $row['edits']+1;
		$sql = "UPDATE `wodsstats` SET edits = ".($row['edits']+1)." WHERE stat_id = ".$row['stat_id'];
		mysql_query($sql);
		checkDBerror($sql);
	} elseif (mysql_num_rows($result) < 1) {
		$action = "create";
	}
} 
// Do edits!
if ($action == "edit") {
	
	$sql = buildUpdateQuery("wodsstats", "stat_id=$stat_id");
	mysql_query($sql);
	checkDBError();
	echo "<p>Your changes have been saved.</p>
	 <p><b><a href=\"wodsstats_edit.php";
	 if (secure_is_manager()) echo "?user_id=$userid";
	echo "\">Back to WODS Statistics listing</a></b></p>
	 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
}
elseif ($action == "create") {
	$sql = buildInsertQuery("wodsstats");
	mysql_query($sql);
	checkDBError();
	echo "<p>Thank you. Your WODS statistics have been received.</p>
	 <p><b><a href=\"wodsstats_form.php";
	 if (secure_is_manager()) echo "?user_id=$userid";
	 echo "\">Enter more WODS statistics</a></b></p>
	 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
}
elseif ($action == "delete") {
	mysql_query("DELETE FROM wodsstats WHERE stat_id=$stat_id");
	checkDBError();
	echo "<p>The statistics set has been deleted.</p>
	 <p><b><a href=\"wodsstats_edit.php";
	if (secure_is_manager()) echo "?user_id=$userid";
	echo "\">Back to WODS Statistics listing</a></b></p>
	 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
}
elseif ($action == "display") { /* print form */
	$sql = "SELECT wodsstats.*, users.ID, users.first_name, users.last_name
	 FROM wodsstats INNER JOIN users ON wodsstats.user_id=users.ID
	 WHERE wodsstats.user_id=$userid AND wodsstats.stat_date LIKE '$select_date%' ORDER BY users.last_name";
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
    <td class="fat_black_12"><p align="right"># Out</p></td>
    <td class="fat_black_12"><p align="right">Show</p></td>
    <td class="fat_black_12"><p align="right">Sold</p></td>
    <td class="fat_black_12"><p align="right">Retail</p></td>
    <td class="fat_black_12"><p align="right">Profit</p></td>
  </tr>
  <form action="wodsstats_edit.php" method="post">
    <tr> 
      <td colspan="7"><p><b>Warehouse One-Day Sale</b></p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Inserts</p></td>
      <td><p align="right">
          <input name="inserts_out" type="text" size="6" maxlength="6" value="<?php echo $result["inserts_out"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="inserts_show" type="text" size="6" maxlength="6" value="<?php echo $result["inserts_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="inserts_sold" type="text" size="6" maxlength="6" value="<?php echo $result["inserts_sold"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="inserts_retail" type="text" size="10" maxlength="11" value="<?php echo $result["inserts_retail"] ? $result["inserts_retail"] : "0.00"; ?>">
        </p></td>
      <td><p align="right">
	<input name="inserts_profit" type="text" size="10" maxlength="11" value="<?php echo $result["inserts_profit"] ? $result["inserts_profit"] : "0.00"; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Signs</p></td>
      <td><p align="right">
          <input name="signs_out" type="text" size="6" maxlength="6" value="<?php echo $result["signs_out"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="signs_show" type="text" size="6" maxlength="6" value="<?php echo $result["signs_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="signs_sold" type="text" size="6" maxlength="6" value="<?php echo $result["signs_sold"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="signs_retail" type="text" size="10" maxlength="11" value="<?php echo $result["signs_retail"] ? $result["signs_retail"] : "0.00"; ?>">
        </p></td>
      <td><p align="right">
	<input name="signs_profit" type="text" size="10" maxlength="11" value="<?php echo $result["signs_profit"] ? $result["signs_profit"] : "0.00"; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Repeat Customers</p></td>
      <td><p align="right">
          <input name="repeats_out" type="text" size="6" maxlength="6" value="<?php echo $result["repeats_out"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="repeats_show" type="text" size="6" maxlength="6" value="<?php echo $result["repeats_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="repeats_sold" type="text" size="6" maxlength="6" value="<?php echo $result["repeats_sold"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="repeats_retail" type="text" size="10" maxlength="11" value="<?php echo $result["repeats_retail"] ? $result["repeats_retail"] : "0.00"; ?>">
        </p></td>
      <td><p align="right">
	<input name="repeats_profit" type="text" size="10" maxlength="11" value="<?php echo $result["repeats_profit"] ? $result["repeats_profit"] : "0.00"; ?>">
        </p></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><p align="right">Others</p></td>
      <td><p align="right">
          <input name="others_out" type="text" size="6" maxlength="6" value="<?php echo $result["others_out"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="others_show" type="text" size="6" maxlength="6" value="<?php echo $result["others_show"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="others_sold" type="text" size="6" maxlength="6" value="<?php echo $result["others_sold"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="others_retail" type="text" size="10" maxlength="11" value="<?php echo $result["others_retail"] ? $result["others_retail"] : "0.00"; ?>">
        </p></td>
      <td><p align="right">
	<input name="others_profit" type="text" size="10" maxlength="11" value="<?php echo $result["others_profit"] ? $result["others_profit"] : "0.00"; ?>">
        </p></td>
    </tr>

    <tr> 
      <td colspan="7" align="right"> <input name="action" type="hidden" id="action" value="save"> 
        <input name="stat_id" type="hidden" id="stat_id" value="<?php echo $result["stat_id"]; ?>"> 
        <input name="user_id" type="hidden" id="user_id" value="<?php echo $result["user_id"]; ?>"> 
        <input name="stat_date" type="hidden" id="stat_date" value="<?php echo $result["stat_date"]; ?>">
	<input name="edits" type="hidden" id="edits" value="<?php if($result['edits'] || $result['edits']>=1) { echo $result['edits']; } else { echo '1'; } ?>">
	<?php if($result['action']=="create") { ?><input name="createdate" type="hidden" id="createdate" value="<?php echo date('Y-m-d'); ?>"><?php } ?>
        <input type="submit" name="Save" value="Save Changes"> </td>
    </tr>
  </form>
  <?php if ($result['stat_id']) { ?>
  <tr> 
    <td colspan="7" align="right"><a href="javascript:confirmDelete('wodsstats_edit.php?action=delete&stat_id=<?php echo $result["stat_id"]; if (secure_is_manager()) echo "&user_id=$userid"; ?>')">Delete 
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
	<form action="wodsstats_edit.php" method="get">
	<tr bgcolor="#FFFFFF">
		<td align="right"> <p><b>Select A Dealer:</b></p></td>
		<td><p><select name="user_id">
		<?php
		$sql = "SELECT users.ID, users.first_name, users.last_name FROM users WHERE users.nonPMD != 'Y' AND users.disabled != 'Y' AND `wodsable` = 'Y' ORDER BY users.last_name";
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
	<?php }
	if (db_user_getuserinfo($userid, 'wodsable') == 'Y') {
	?><form action="wodsstats_edit.php" method="get">
    <tr bgcolor="#FFFFFF"> 
      <td align="right"> <p><b>or Select A Date:</b></p></td>
      <td><p>
        <?php
		/*$sql = "SELECT DISTINCT stat_date FROM wodsstats WHERE user_id=$userid ORDER BY stat_date";
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
		
		$sql = "SELECT DISTINCT stat_date FROM wodsstats WHERE user_id=$userid ORDER BY stat_date DESC LIMIT 14";
		$query = mysql_query($sql);
		checkDBError();
		$has_done = array();
		while ($row = mysql_fetch_assoc($query)) {
			$has_done[strtotime($row['stat_date'])] = true;
		}
		
		if (secure_is_admin()) $limit = 365;
		elseif (secure_is_manager()) $limit = 60;
		else $limit = 14;
		
		echo "<select name=\"select_date\">";
		$curday = time();
		$i = 0;
		while ($i < $limit) {
			$curday = mktime(0,0,0,date("m",$curday),date("j",$curday),date("Y",$curday));
			echo "<option value=\"".date("Y-m-d",$curday)."\">".
				 date("F j, Y",$curday);
			if (isset($has_done[$curday])) echo "*";
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
    <?php } ?>
  </table>
  <p><a href="selectvendor.php">Return to Vendor List</a>
<?php
}
mysql_close($link);
?>
</body>
</html>