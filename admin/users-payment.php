<?php
require("database.php");
require("secure.php");
require("../inc_content.php");

$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');

function getUserName($user) {
	global $sql;
	$query = mysql_query("select first_name,last_name,username from users where ID=$user");
	checkDBError();

	if($result = mysql_fetch_array($query))
		return $result['last_name'].", ".$result['first_name']." (".$result['username'].")";
	return "";
}

if ($process == "y") {
	$values = array();
	$th1 = $_POST['th1'];
	$ta1 = $_POST['ta1'];
	if ($th1 == 12 && $ta1 =='pm') {
		$ta1 = 'am';
	} elseif ($th1 == 12 && $ta1 == 'am') {
		$th1 = 0;
	}
	if ($ta1 == 'pm') $th1 += 12;
	$ordered = strtotime($_POST['y1'].'-'.$_POST['m1'].'-'.$_POST['d1'].' '.$th1.':'.$_POST['tm1'].':00');
	$po_id = submitCreditFee($_POST['user_id'], $_POST['type'], $_POST['comments'], $_POST['total'], $ordered);
	$ID = $_POST['user_id'];
	/* Removed Autoprint of Bill 08-28-2007 (Will)
	if ($type == 'f') {
		$extra_javascript = "
function popUp(URL) {
	day = new Date();
	id = day.getTime();
	eval(\"page\" + id + \" = window.open(URL, '\" + id + \"', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=2,height=2');\");
}
		";
		$extra_onload = "popUp('viewpo.php?po=".$po_id."&printclose=1')";
	}
	*/
}
require('menu.php');
?>
<br>
<?php
if ($process == "y")
	echo '<p><b>The payment was successfully added. <a href="viewpo.php?po='.$po_id.'" target="_new">view Credit/Fee/Bill</a>, add another payment or <a href="users-summary-paged.php?ID='.$user_id.'">return to Payment Summary</a>.</b></p>';
?>

<table border="0" cellspacing="0" cellpadding="5" width="70%">
  <form action="users-payment.php" method="post">
    <input name="processed" type="hidden" id="processed" value="Y">
    <input name="form" type="hidden" id="form" value="0">
    <tr> 
      <td class="text_12" align="right"><b>Dealer:</b></td>
      <td class="text_12"> <select name="user_id" size="1">
          <?php
		$query = mysql_query("select ID, first_name, last_name from users where disabled != 'Y' order by last_name");
		checkDBError();
		while ($result = mysql_fetch_Array($query)) {
			if ($result['ID'] == $ID)
				echo "<option value=\"".$result[0]."\" selected>".$result[2]." ".$result[1]."</option>";
			else
				echo "<option value=\"".$result[0]."\">".$result[2]." ".$result[1]."</option>";
		}
	  ?>
        </select> </td>
    </tr>
    <tr> 
      <td class="text_12" align="right"><b>Date:</b></td>
      <td class="text_12"> <select name="m1">
          <?php
		for ($x=1; $x <=12; $x++) {
			if ($x == date("m"))
				echo "<option value=\"$x\" selected>$monthName[$x]</option>";
			else
				echo "<option value=\"$x\">$monthName[$x]</option>";
		}
		?>
        </select> <select name="d1">
          <?php
		for ($x=1; $x <=31; $x++) {
			if ($x == date("d"))
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
		?>
        </select> <select name="y1">
          <?php
		for ($x=2003; $x <=date("Y")+1; $x++) {
			if ($x == date("Y"))
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
		?>
        </select> <select name="th1">
	  <?php
		for ($x=1; $x <=12; $x++) {
			if ($x == date("g"))
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
	  ?>
	</select> <select name="tm1">
	  <?php
		for ($x=1; $x <=59; $x++) {
			if ($x == date("i"))
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
	  ?>
	</select> <select name="ta1">
	  <?php
		$tempx = array('am','pm');
		foreach ($tempx as $x) {
			if ($x == date("a"))
				echo "<option value=\"$x\" selected>$x</option>";
			else
				echo "<option value=\"$x\">$x</option>";
		}
	  ?>
	</select>
	</td>
    </tr>
    <tr> 
      <td class="text_12" align="right"><b> Amount:</b></td>
      <td class="text_12">$ <input name="total" type="text" id="total" size="10"></td>
    </tr>
    <tr> 
      <td align="right" class="text_12"><b>Type:</b></td>
      <td class="text_12"><input type="radio" name="type" value="c" checked>Credit 
        <input type="radio" name="type" value="f">Fee/Bill</td>
    </tr>
    <tr> 
      <td align="right" class="text_12"><b>Comments:</b></td>
      <td class="text_12">
        <textarea name="comments" cols="50" rows="7" id="comments"></textarea>
      </td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td><input name="process" type="hidden" id="process" value="y">
	  <input type="submit" name="submit1" style="background-color:#CA0000;color:white" value="Add Payment"></td>
    </tr>
  </form>
</table>
<?php footer($link); ?>
