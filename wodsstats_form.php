<?php
header('Location: wodsstats_edit.php');
exit();
require("database.php");
require("secure.php");

$starting_year = 2004;
$current_year = date("Y", strtotime("now"));
$current_month = date("n", strtotime("now"));

$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body bgcolor="#EDECDA">
<?php require('menu.php'); ?><br>
<span class="fat_black">ENTER WODS STATS</span>
<?php
if ($process == "y") {

	if (checkdate($m1,$d1,$y1) == FALSE)
		echo "<p class=\"alert\">You entered a date that does not exist!</p>
		 <p><b><a href=\"javascript:history.back();\">Please Try Again</a></b></p>";
	else {
		if ($m1 < 10)
			$m1 = "0".$m1;
		if ($d1 < 10)
			$d1 = "0".$d1;
		$stat_date = $y1."-".$m1."-".$d1;
			//date("".$y1.$m1.$d1." H:i:s");
		$stat_just_date = $y1."-".$m1."-".$d1;
			//date("".$y1."-".$m1."-".$d1."");
		$user_id = $userid;

		$sql = "SELECT stat_date FROM wodsstats WHERE user_id=$user_id AND stat_date='$stat_just_date'";
		$query = mysql_query($sql);
		checkDBError();		
		if (mysql_num_rows($query) > 0)
			echo "<p class=\"alert\">You already entered WODS statistics for this date.</p>
			 <p><b><a href=\"javascript:history.back();\">Select another date</a></b> or 
			 <b><a href=\"wodsstats_edit.php?select_date=$y1-$m1-$d1&action=display\">Edit ".date("F j, Y",strtotime($stat_just_date))." Stats</a></b></p>";
		else {
			$sql = buildInsertQuery("wodsstats");
			mysql_query($sql);
			checkDBError();
			echo "<p>Thank you. Your WODS sales statistics have been received.</p>
			 <p><b><a href=\"wodsstats_form.php\">Enter more WODS sales statistics</a></b></p>
			 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
		}
	}
}
else {
?>
<form action="wodsstats_form.php" method="post">
  <table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
    <tr> 
      <td colspan="6"> <p><b>Dealer:</b> 
          <?php
		$sql = "SELECT first_name, last_name FROM users WHERE ID=$userid";
		$query = mysql_query($sql);
		checkDBError();
		$result = mysql_fetch_array($query);
		echo $result["first_name"]." ".$result["last_name"];
	  ?>
        </p>
        <p><b>Date:</b> 
          <select name="m1">
            <?php
		for ($x=1; $x <=12; $x++) {
			if ($x == $current_month)
				echo "<option value=\"$x\" selected>$monthName[$x]</option>";
			else
				echo "<option value=\"$x\">$monthName[$x]</option>";
		}
		?>
          </select>
          <select name="d1">
            <?php
		for ($x=1; $x <=31; $x++) {
			if ($x == date("d"))
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
		?>
          </select>
          <select name="y1">
            <?php
		for ($x=$starting_year; $x <= $current_year; $x++) {
			if ($x == $current_year)
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
		?>
          </select>
        </p></td>
    </tr>
    <tr bgcolor="#CCCC99"> 
    <td class="fat_black_12"><p>&nbsp;</p></td>
    <td class="fat_black_12"><p align="right">Inserts</p></td>
    <td class="fat_black_12"><p align="right">Insert Sales</p></td>
    <td class="fat_black_12"><p align="right">Signs</p></td>
    <td class="fat_black_12"><p align="right">Sign Sales</p></td>
    <td class="fat_black_12"><p align="right">Total Customers</p></td>
    <td class="fat_black_12"><p align="right">Total # of Sales</p></td>
    <td class="fat_black_12"><p align="right">Total Retail Sales ($)</p></td>
    <td class="fat_black_12"><p align="right">Total Gross Profit</p></td>
    <td class="fat_black_12"><p align="right">Total Expenses</p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>&nbsp;</p></td>
      <td><p align="right">&nbsp;</p></td>
      <td><p align="right">
          <input name="insert_qty" type="text" size="6" maxlength="6" value="<?php echo $result["insert_qty"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="insert_sales_qty" type="text" size="6" maxlength="6" value="<?php echo $result["insert_sales_qty"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="sign_qty" type="text" size="6" maxlength="6" value="<?php echo $result["sign_qty"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="sign_sales_qty" type="text" size="6" maxlength="6" value="<?php echo $result["sign_sales_qty"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="customers_qty" type="text" size="8" maxlength="8" value="<?php echo $result["customers_qty"]; ?>">
        </p></td>
      <td><p align="right">
          <input name="sales_qty" type="text" size="8" maxlength="8" value="<?php echo $result["sales_qty"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="retail_sales_sum" type="text" size="15" maxlength="15" value="<?php echo $result["retail_sales_sum"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="gross_profit_sum" type="text" size="15" maxlength="15" value="<?php echo $result["gross_profit_sum"]; ?>">
        </p></td>
      <td><p align="right">$
          <input name="expenses_sum" type="text" size="15" maxlength="15" value="<?php echo $result["expenses_sum"]; ?>">
        </p></td>
    </tr>
    <!--<tr> 
      <td><p><b>Retail Sales</b></p></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td align="right">
<p>$ 
          <input name="retail_sales" type="text" id="retail_sales" size="8" maxlength="15">
        </p></td>
    </tr>-->
    <tr> 
      <td colspan="7" align="right"> <input name="process" type="hidden" id="process" value="y"> 
        <input type="submit" name="Submit" value="Submit"> </td>
    </tr>
  </table>
</form>
<?php
}
mysql_close($link);
?>
</body>
</html>