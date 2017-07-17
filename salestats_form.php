<?php
header('Location: salestats_edit.php');
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
<span class="fat_black">ENTER SALES STATS</span>
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

		$sql = "SELECT stat_date FROM salestats WHERE user_id=$user_id AND stat_date='$stat_just_date'";
		$query = mysql_query($sql);
		checkDBError();		
		if (mysql_num_rows($query) > 0)
			echo "<p class=\"alert\">You already entered statistics for this date.</p>
			 <p><b><a href=\"javascript:history.back();\">Select another date</a></b> or 
			 <b><a href=\"salestats_edit.php?select_date=$y1-$m1-$d1&action=display\">Edit ".date("F j, Y",strtotime($stat_just_date))." Stats</a></b></p>";
		else {
			$sql = buildInsertQuery("salestats");
			mysql_query($sql);
			checkDBError();
			echo "<p>Thank you. Your sales statistics have been received.</p>
			 <p><b><a href=\"salestats_form.php\">Enter more sales statistics</a></b></p>
			 <p><a href=\"selectvendor.php\">Return to Vendor List</a>";
		}
	}
}
else {
?>
<form action="salestats_form.php" method="post">
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
    <tr> 
      <td bgcolor="#CCCC99" class="fat_black_12"> <p>&nbsp;</p></td>
      <td align="center" bgcolor="#CCCC99" class="fat_black_12"> <p>Calls</p></td>
      <td align="center" bgcolor="#CCCC99" class="fat_black_12"> <p>Appts.</p></td>
      <td align="center" bgcolor="#CCCC99" class="fat_black_12"> <p>Show</p></td>
      <td align="center" bgcolor="#CCCC99" class="fat_black_12"> <p>Sold</p></td>
      <td align="right" bgcolor="#CCCC99" class="fat_black_12"> <p>Retail</p></td>
      <td align="right" bgcolor="#CCCC99" class="fat_black_12"> <p>Profit</p></td>
    </tr>
    <tr> 
      <td colspan="7"><p><b>Bedding</b></p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Mattress</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="ads_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="ads_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="ads_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="ads_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="ads_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="ads_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Entry Furniture</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="babycase_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="babycase_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="babycase_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="babycase_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="babycase_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="babycase_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Mattress Signs</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_signs_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_signs_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_signs_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_signs_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="bedding_signs_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="bedding_signs_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Mattress Internet</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_internet_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_internet_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_internet_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedding_internet_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="bedding_internet_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="bedding_internet_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td colspan="7"> <p><b>Case Goods</b></p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Bedroom sets</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedroom_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedroom_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedroom_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="bedroom_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="bedroom_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="bedroom_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Living Room sets</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="living_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="living_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="living_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="living_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="living_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="living_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Dining Room</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="dining_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="dining_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="dining_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="dining_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="dining_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="dining_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Furniture Signs</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_signs_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_signs_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_signs_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_signs_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="cg_signs_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="cg_signs_profit" type="text" size="8" maxlength="15">
        </p></td>
    </tr>
    <tr> 
      <td align="right" bgcolor="#FFFFFF"> <p>Furniture Internet</p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_internet_calls" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_internet_appts" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_internet_show" type="text" size="3" maxlength="3">
        </p></td>
      <td align="center" bgcolor="#FFFFFF"> <p> 
          <input name="cg_internet_sold" type="text" size="3" maxlength="3">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="cg_internet_retail" type="text" size="8" maxlength="15">
        </p></td>
      <td align="right" bgcolor="#FFFFFF"> <p>$ 
          <input name="cg_internet_profit" type="text" size="8" maxlength="15">
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
