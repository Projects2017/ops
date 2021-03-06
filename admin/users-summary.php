<?php
require("database.php");
require("secure.php");
require("menu.php");

function getFormName($form)
{
	global $sql;
	$sql = "select snapshot_forms.name from snapshot_forms where snapshot_forms.ID='$form'";
	$query = mysql_query($sql);
	checkDBError();

	if ($result = mysql_fetch_array($query))
		return $result['name'];
	return "";
}

function getUserName($user)
{
	global $sql;
	$sql = "select first_name,last_name from users where ID='$user'";
	$query = mysql_query($sql);
	checkDBError();

	if($result = mysql_fetch_array($query))
		return $result['last_name'].", ".$result['first_name'];
	return "";
}

function getOrderType($type) {
	if ($type == "c")
		$order_type = "credit";
	elseif ($type == "f")
		$order_type = "bill";
	else
		$order_type = "order";
	return $order_type;
}

?>
<p class="fat_black"><?php echo getUserName($ID); ?></p>
<p><a href="users-payment.php?ID=<?php echo $ID; ?>">Insert A Payment</a></p>
<?php

$sql = "SELECT ID, ordered, form, snapshot_form, comments, total, type FROM order_forms WHERE user=$ID AND deleted=0 ORDER BY ordered ASC";
$query = mysql_query($sql);
checkDBError();

if (mysql_num_rows($query) == 0) {
	echo "<p class=\"alert\">There are no transactions in the database for this dealer.</p>";
}
else {
?>
<p><b>Pages of orders: </b>&nbsp;
<?php
/* select first page if none is selected */

$pageSize = 25; //number of records per page

/* retrieve total number of orders */
$result = mysql_query("SELECT count(*) FROM order_forms WHERE user=$ID AND deleted=0");
checkDBError();
$totalRows = mysql_result($result, 0);
mysql_free_result($result);

$totalPages = ceil($totalRows / $pageSize);

for ($i=1; $i <= $totalPages; $i++) {
	echo "<a href=\"users-summary-paged.php?ID=$ID&page_number=$i\">page $i</a> ";
	if ($i < $totalPages)
		echo "- ";
}
?>
- All with Balance
</p>
<table border="0" cellspacing="0" cellpadding="5" width="80%">
  <tr bgcolor="#fcfcfc"> 
    <td class="fat_black_12">Order Date</td>
    <td class="fat_black_12">PO #</td>
    <td class="fat_black_12">Form</td>
    <td class="fat_black_12" align="right">Total</td>
    <td class="fat_black_12" align="right">Balance</td>
    <td class="fat_black_12" align="center">Details</td>
    <td class="fat_black_12">Comments</td>
  </tr>
<?php
$balance = 0;
while ($result = mysql_fetch_array($query)) {
	$balance += $result['total'];
	//if ($result['type'] == "o")
		$po = $result['ID'] + 1000;
	//else
	//	$po = "";
?>
    <tr valign="top"> 
      <td class="text_12"><?php echo date('m/d/Y', strtotime($result['ordered'])); ?></td>
      <td class="text_12"><?php echo $po; ?></td>
      <td class="text_12"><?php echo getFormName($result['snapshot_form']); ?></td>
      <td class="text_12" align="right"><?php echo makeThisLookLikeMoney($result['total']); ?></td>
      <td class="text_12" align="right"><b><?php echo makeThisLookLikeMoney($balance); ?></b></td>
      <td class="text_12" align="center"><a href="report-orders-details.php?po=<?php echo $result['ID']+1000; ?>">view&nbsp;<?php echo getOrderType($result['type']); ?></a></td>
      <td class="text_12"><?php echo $result['comments']; ?></td>
    </tr>
<?php
}
?>
    <tr> 
      <td class="fat_black_12" colspan="4" align="right">Current Balance:</td>
      <td class="fat_black_12"><?php echo makeThisLookLikeMoney($balance); ?></td>
      <td class="fat_black_12">&nbsp;</td>
    </tr>
</table>

<?php
}
footer($link);
?>