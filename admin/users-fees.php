<?php
require("database.php");
require("secure.php");
require("menu.php");
require("../inc_content.php");

/* 
function getUserName($user)
{
	global $sql;
	$sql = "select first_name,last_name,username from users where ID=$user";
	$query = mysql_query($sql);
	checkDBError();

	if($result = mysql_fetch_array($query))
		return $result['last_name'].", ".$result['first_name']." (".$result['username'].")";
	return "";
}

*/
?>
<p class="fat_black">Fee Payments to Bill</p>
<?php
if ($process == "y") {

	/* update last payment date */
	mysql_query("UPDATE fee_payments SET last_payment='".date("Y-m-d")."'");
	checkDBError();	

	foreach ($user_array as $user) {
                if (!is_numeric($user))
                    continue;
                $sql = "SELECT fee_pmt_amount FROM users WHERE ID = '".$user."' LIMIT 1";
                $query = mysql_query($sql);
                checkDBError($sql);
                $result = mysql_fetch_Array($query);
                $total = $result['fee_pmt_amount'];

		/* add payment */
		submitCreditFee($user, 'f', 'Franchisee note payable', $total);
		
		/* decrease number of remaining payments */
		$sql = "UPDATE `users` SET `remaining_fee_pmts`=`remaining_fee_pmts` - 1 WHERE `ID`='".$user."'";
		mysql_query($sql);
		checkDBError($sql);
	}

	echo "<p class=\"text_12\">The payments have been successfully added.</p>";
	echo "<p class=\"text_12\"><a href=\"users.php\">Back to User list</a></p>";

}
else {
?>
<form action="users-fees.php" method="post"><table border="0" cellspacing="0" cellpadding="5" width="80%">
  <tr bgcolor="#fcfcfc"> 
    <td class="fat_black_12">Select</td>
    <td class="fat_black_12">Dealer</td>
    <td class="fat_black_12" align="right">Total Amount Remaining</td>
    <td class="fat_black_12" align="right">Payment Amount</td>
    <td class="fat_black_12" align="right"># Payments Remaining</td>
  </tr>
<?php
$query = mysql_query("SELECT last_payment FROM fee_payments");
checkDBError();
$data = mysql_fetch_object($query);
$last_payment = $data->last_payment;
if (date("n",strtotime($last_payment)) == date("n") ) {
        ?>
  <tr bgcolor="#FF9999">
      <td colspan="5" class="text_12" align="center">It is not yet time to process fee payments, records show you have already billed for this month.</td>
  </tr>
        <?php
}
$sql = "SELECT ID, first_name, last_name, remaining_fee_pmts, fee_pmt_amount FROM users WHERE remaining_fee_pmts > 0 ORDER BY last_name ASC";
$query = mysql_query($sql);
checkDBError();
while ($result = mysql_fetch_Array($query)) {
?>
    <tr> 
      <td class="text_12"><input type="checkbox" name="user_array[]" value="<?php echo $result['ID']; ?>" checked></td>
      <td class="text_12"><?php echo $result['last_name'].", ".$result['first_name']; ?></td>
      <td class="text_12" align="right"><?php echo makeThisLookLikeMoney($result['fee_pmt_amount']*$result['remaining_fee_pmts']); ?></td>
      <td class="text_12" align="right"><?php echo makeThisLookLikeMoney($result['fee_pmt_amount']); ?></td>
      <td class="text_12" align="right"><?php echo $result['remaining_fee_pmts']; ?></td>
    </tr>
<?php
}
?>
    <tr> 
      <td class="fat_black_12" colspan="3">
	  <input type="hidden" name="process" value="y">
	  <input type="submit" value="Submit Fee Charges"></td>
    </tr>
</table></form>
<?php } ?>
<?php footer($link); ?>
