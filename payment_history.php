<?php // payment history ?>

<table width="85%" border="0" align="center" cellpadding="5" cellspacing="0">
	  <tr>
			<td colspan="9" class="orderTH"><b>Payments</b></td>
	  </tr>
<?php

$new_poid = $po-1000;

$sql = "SELECT * from po_payments WHERE po_id = '".$new_poid."';";
$query = mysql_query($sql);
checkdberror($sql);

if (mysql_num_rows($query) > 0){
?>
	  <tr>
 			<td colspan="9" class="orderTD" align="center"><p style="font-size: 16px; font-weight: bold">Payment History</p></td>
	  </tr>
	  <tr>
			<td colspan="3" class="orderTD"><b>Payment Date</b></td>
			<td colspan="3" class="orderTD"><b>Payment Amount</b></td>
			<td colspan="3" class="orderTD"><b>Payment Status</b></td>
		  </tr>
<?php
while ($row = mysql_fetch_array($query)){
?>
		  <tr>
			<td colspan="3" class="orderTD orderTD_<?php=$row['payment_status']?>"><?php=date("m/d/y h:iA",strtotime($row['payment_datetime']))?></td>
			<td colspan="3" class="orderTD orderTD_<?php=$row['payment_status']?>"><?php=$row['payment_amount']?></td>
			<td colspan="3" class="orderTD orderTD_<?php=$row['payment_status']?>"><?php=ucfirst($row['payment_status'])?></td>
		  </tr>
<?php
}
} else {
?>
		<tr>
			<td colspan="9" align="center" style="padding:15px;background:#FFF;" class="orderTD">No Payments</td>
		</tr>

<?php
}
?>



	</table>