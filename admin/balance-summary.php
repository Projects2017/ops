<?php
require("database.php");
require("secure.php");
if ($_REQUEST['special_xmlhttprequest'] != 'Y') {
	require("menu.php");
}

if ($security != 'S') {
  die("Only superadmin's may view dealer balances.");
}
if ($_REQUEST['special_xmlhttprequest'] == 'Y') {
	// Process Credit Limit Changes
	$updated = 0;
	foreach ($_POST as $k => $v) {
		if (substr($k,0,5) == 'limit') {
			$id = substr($k,5);
			$newlimit = $v;
			if (!is_numeric($newlimit))
				continue;
			if (!is_numeric($id))
				continue;
			$sql = "SELECT `credit_limit` FROM `users` WHERE ID = '".$id."'";
			$query = mysql_query($sql);
			checkDBError($sql);
			$result = mysql_fetch_assoc($query);
			$result = $result['credit_limit'];
			if ($result != $newlimit) {
				$sql = "UPDATE `users` SET `credit_limit` = '".$v."' WHERE ID = '".$id."'";
				mysql_query($sql);
				checkDBError($sql);
				++$updated;
			}
		}
	}
	if ($updated != 1) {
		$update_str = $updated." Dealers Updated";
	} else  {
		$update_str = "1 Dealer Updated";
	}
	die($update_str);
}

if ($_REQUEST['defaults'] == 'Y') {
  $month = date('m');
  $day = date('d');
  $year = date('y');
  $nonpmd = "N";
  $disabled = "N";
}

$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');
?>
<p class="fat_black">Balance Summary</p>
<form action="balance-summary.php" method="get"><p>
<b>Select a Date:</b> <select name="month">
<?php
for ($x=1; $x <=12; $x++) {
	if ($x == date("m"))
		echo "<option value=\"$x\" selected>$monthName[$x]</option>";
	else
		echo "<option value=\"$x\">$monthName[$x]</option>";
}
?>
</select> <select name="day">
<?php
for ($x=1; $x <=31; $x++) {
	if ($x == date("d"))
		echo "<option value=\"$x\" selected>$x</option>";
	else
		echo "<option value=\"$x\">$x</option>";
}
?>
</select> <select name="year">
<?php
for ($x=2002; $x <= date("Y")+1; $x++) {
	if ($x == date("Y"))
		echo "<option value=\"$x\" selected>$x</option>";
	else
		echo "<option value=\"$x\">$x</option>";
}
?>
</select>
<br>
<b>Non-RSS Customers:</b> <select name="nonpmd">
<option value="Y">Only</option>
<option value="N" SELECTED>Exclude</option>
<option value="%">Include</option>
</select> <br>
<b>Inactive Customers:</b> <select name="disabled">
<option value "Y">Only</option>
<option value "N" SELECTED>Exclude</option>
<option value "%">Include</option>
</select>
<input type="submit" value="go"></p>
</form>

<?php
if ($year <> "") {

	$query_date = "$year-$month-$day 23:59:59";
?>
<script type="text/javascript">
	var massedit = 0;

	function massedit_submit() {
		var button = getElement('creditsubmit');
		var form = document.forms.creditlimitform;
		var span;
		var bal;
		var avail;
		var id;
		var credit;
		for (var idx in form.elements) {
			if (isNaN(idx)&&(idx.substr(0,5) != 'limit')) {
				continue;
			}
			if (form.elements[idx].name.substr(0,5) != 'limit') {
				continue;
			}
			if (form.elements[idx].style.display == "") {
				id = form.elements[idx].name.substr(5);
				span = getElement('creditspan' + id);
				bal = parseMoney(getInnerText(getElement('balance' + id))).toFixed(2);
				avail = getElement('avail' + id);
				credit = parseFloat(form.elements[idx].value);
				if (isNaN(credit)) {
					form.elements[idx].value = parseMoney(getInnerText(span)).toFixed(2);
					credit = parseFloat(form.elements[idx].value).toFixed(2);
				}
				if (credit > 999999.99) { // Database cannot store values larger than 999,999.99
					credit = 999999.99;
					form.elements[idx].value = 999999.99;
				}
				getInnerTextNode(span).nodeValue = MakeThisLookLikeMoney(credit);
				getInnerTextNode(avail).nodeValue = MakeThisLookLikeMoney(credit - bal);
				if (credit - bal < 0) {
					getParent(form.elements[idx], 'tr').bgcolor = '#EFC2C6';
				} else {
					getParent(form.elements[idx], 'tr').bgcolor = '';
				}
				span.style.display = "";
				//form.elements[idx].value = parseMoney(getInnerText(span)).toFixed(2);
				form.elements[idx].style.display = 'none';
			}
		}
		massedit = 0;
		getElement('mass_switch').style.display = 'none';
		getInnerTextNode(getElement('massswitch')).nodeValue = 'Mass Edit';
		postForm( button );
	}
	
	function massedit_switch() {
		var form = document.forms.creditlimitform;
		var span;
		if (massedit) {
			// stuff to switch it off...
			for (var idx in form.elements) {
				if (isNaN(idx)&&(idx.substr(0,5) != 'limit')) {
					continue;
				}
				if (form.elements[idx].style.display == "") {
					span = getElement('creditspan' + form.elements[idx].name.substr(5));
					if (span) {
						span.style.display = "";
						form.elements[idx].value = parseMoney(getInnerText(span)).toFixed(2);
					}
					form.elements[idx].style.display = 'none';
				}
			}
			massedit = 0;
			getElement('mass_switch').style.display = 'none';
			getInnerTextNode(getElement('massswitch')).nodeValue = 'Mass Edit';
		} else {
			// stuff to turn it on...
			for (var idx in form.elements) {
				if (isNaN(idx)&&(idx.substr(0,5) != 'limit')) {
					continue;
				}
				if (form.elements[idx].style.display == "none") {
					//alert(idx);
					form.elements[idx].style.display = '';
					span = getElement('creditspan' + form.elements[idx].name.substr(5));
					if (span) {
						span.style.display = "none";
					}
				}
			}
			massedit = 1;
			getElement('mass_switch').style.display = '';
			getInnerTextNode(getElement('massswitch')).nodeValue = 'Cancel Edit';
			//.innerText = 'Cancel Edit';
		}
	}
</script>
<form id="creditlimitform" action="balance-summary.php">
<input type="hidden" name="ajax" value="true">
<table class="sortable" id="dealercredit" border="0" cellspacing="0" cellpadding="5" width="90%">
<tr class="skiptop">
	<td colspan="4">Summary of balances as of <b><?php echo date("F j, Y",strtotime($query_date)); ?></b>:</td>
	<td align="right">
		<span id="mass_switch" style="display: none;">[<a onclick="massedit_submit(); return false;">Apply Edit</a>]</span> [<a id="massswitch" onclick="massedit_switch(); return false;">Mass Edit</a>]
	</td>
</tr>
  <tr bgcolor="#fcfcfc"> 
    <th class="fat_black_12">Dealer</td>
    <th class="fat_black_12" align="right">Order To Limit</td>
    <th class="fat_black_12" align="right">Balance</td>
	<th class="fat_black_12" align="right">Avail</td>
    <th class="fat_black_12" align="center">Details</td>
  </tr>
	<?php
	$total_balance = 0;
	$extrawhere = "";
	if ($disabled == "Y")
		$extrawhere .= " AND users.disabled = 'Y'";
	elseif ($disabled == "N")
		$extrawhere .= " AND users.disabled != 'Y'";

	if ($nonpmd == "Y")
		$extrawhere .= " AND users.nonPMD = 'Y'";
	elseif ($nonpmd == "N")
		$extrawhere .= " AND users.nonPMD != 'Y'";
	//$extrawhere = " AND users.orion != 'Y' AND users.disabled != 'Y'";
	
	$sql = "SELECT order_forms.user, SUM(order_forms.total) AS balance, users.ID, users.first_name, users.last_name, users.dealer_type, users.credit_limit FROM order_forms
	 INNER JOIN users ON order_forms.user = users.ID 
	 WHERE order_forms.ordered <= '$query_date' ".$extrawhere."AND order_forms.deleted=0 GROUP BY order_forms.user
	 ORDER BY users.last_name, users.first_name";
	$query = mysql_query($sql);
	checkDBError();
	while ($result = mysql_fetch_array($query))
	{
		$balance = $result['balance'];
		$total_balance += $balance;
                $inactive = false;
                if ($result['dealer_type'] == 'L') {
                    $sql = "SELECT * FROM  `order_forms` WHERE `user` = '".$result['ID']."' AND `ordered` > DATE_SUB( CURDATE( ) , INTERVAL 30 DAY ) AND `total` >= 0 LIMIT 1";
                    $sqlresult = mysql_query($sql);
                    checkDBerror($sql);
                    if (!mysql_num_rows($sqlresult)) {
                        $inactive = true;
                    }
                }
	?>
    <tr<?php if ($balance > $result['credit_limit']) { ?> bgcolor="#EFC2C6"<?php } ?>>
      <td class="text_12"><?php echo $result['last_name'].", ".$result['first_name']; ?></td>
	  <td class="text_12" align="right"><b><span id="creditspan<?php echo $result['ID']; ?>"><?php echo makeThisLookLikeMoney($result['credit_limit']); ?></span><input type="text" name="limit<?php echo $result['ID']; ?>" value="<?php echo $result['credit_limit']; ?>" style="display: none;" size="7"></b></td>
      <td class="text_12" align="right"><b><span id="balance<?php echo $result['ID']; ?>"><?php echo makeThisLookLikeMoney($balance); ?></span></b></td>
	  <td class="text_12" align="right"><b><span id="avail<?php echo $result['ID']; ?>"><?php echo makeThisLookLikeMoney($result['credit_limit'] - $balance); ?></span></b></td>
      <td class="text_12" align="center"<?php if ($inactive): ?> bgcolor="#9999FF"<?php endif; ?>><a href="users-summary.php?ID=<?php echo $result['user']; ?>">Summary</a></td>
    </tr>
	<?php
	}
	?>
    <tr class="sortbottom"> 
      <td class="fat_black_12" align="right" colspan="2">Balance on <?php echo date("F j, Y",strtotime($query_date)); ?>:</td>
      <td class="fat_black_12" align="right"><?php echo makeThisLookLikeMoney($total_balance); ?></td>
      <td class="fat_black_12">&nbsp;</td>
    </tr>
</table>
<input type="submit" id="creditsubmit" style="display: none">
</form>
<div id="debugout">
&nbsp;
</div>
<?php
}

footer($link);
?>
