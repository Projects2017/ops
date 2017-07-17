<?php
require("database.php");
require("secure.php");
require("menu.php");

function getFormName($form) {
	if ($form == NULL) return "Corrupt";
	$sql = "select name from forms where ID=$form";
	$query = mysql_query($sql);
	checkDBError($sql);
	if($result = mysql_fetch_array($query))
		return $result[0];
	return "";
}

function customerRow($first, $last) {
	$return_string = "<tr> 
		<td class=\"customerRowLeft\" colspan=5> ".$first.", ".$last."</td>
		<td class=\"customerRowRight\">&nbsp;</td>
	  </tr>";
	return $return_string;
}

function formatDate($date) {
	return date('m/d/Y', strtotime($date));
}

if ($ordered == '') {
	$ordered = "$y1-$m1-$d1 $time1";
	$ordered2 = "$y2-$m2-$d2 $time2";
}
$daterange = "backorder.date BETWEEN '$ordered' AND '$ordered2'";

if ($deleted == "") $deleted = 0; /* error catch in case this is not defined */
$alreadyteam = 0;
$and_customer = "";
$and_join = "";
$and_order_proc = "";
if (ereg("^team=(.+)",$customer, $reg)) {
	if ($reg[1] == 'all') {
		// Return all teams in team list
		$teams = teams_list();
		if (count($teams) > 0) {
			foreach ($teams as $id => $val) {
				$teams[$id] = "users.team='".mysql_escape_string($val)."'";
			}
			$and_customer = " (".implode(" OR ",$teams).") AND ";
		}
	} else {
		$and_customer = " users.team='".mysql_escape_string($reg[1])."' AND ";
	}
	$alreadyteam = 1;
}

if (($customer <> "") && (!$alreadyteam))
	$and_customer = " backorder.user_id=$customer AND ";

if($order_proc == 0)
	$and_order_proc = " backorder.completed = 0  AND ";

$sql = "SELECT backorder.id, backorder.date, users.last_name, users.first_name, backorder.user_id, backorder.form_id, backorder.completed, backorder.canceled FROM backorder INNER JOIN users ON backorder.user_id = users.ID $and_join
 WHERE $and_customer backorder.canceled=$deleted AND $and_order_proc $daterange
 ORDER BY users.last_name, users.first_name, backorder.date";
 
if ($ponum <> "") {
	$po_id = ($ponum-1000);
	$sql = "SELECT backorder.id, backorder.date, users.last_name, users.first_name, backorder.user_id, backorder.form_id, backorder.completed, backorder.canceled FROM backorder INNER JOIN users ON backorder.user_id = users.ID INNER JOIN backorder_item ON backorder.id = backorder_item.backorder_id $and_join WHERE $and_customer backorder.canceled=$deleted AND $and_order_proc $daterange AND backorder_item.completed = '$po_id' ORDER BY users.last_name, users.first_name, backorder.date";
}
if ($vendor <> "") {
	$sql = "SELECT backorder.id, backorder.date, users.last_name, users.first_name, backorder.user_id, backorder.form_id, backorder.completed, backorder.canceled FROM backorder INNER JOIN users ON backorder.user_id = users.ID INNER JOIN forms ON backorder.form_id = forms.ID $and_join WHERE $and_customer backorder.canceled=$deleted AND $and_order_proc $daterange AND forms.vendor = $vendor ORDER BY users.last_name, users.first_name, backorder.date";
}
if ($itemnum <> "") {
	$sql = "SELECT DISTINCT backorder.id, backorder.date, users.last_name, users.first_name, backorder.user_id, backorder.form_id, backorder.completed, backorder.canceled FROM backorder INNER JOIN users ON backorder.user_id = users.ID INNER JOIN backorder_item ON backorder.id = backorder_item.backorder_id INNER JOIN snapshot_items ON backorder_item.snapshot_id = snapshot_items.id $and_join WHERE $and_customer backorder.canceled=$deleted AND $and_order_proc $daterange AND snapshot_items.partno LIKE '".trim($itemnum)."' ORDER BY users.last_name, users.first_name, backorder.date";
	//echo $sql;
	/* item description will be in either form_items or order_snapshot depending on the date of the order */
}
$query = mysql_query($sql);
checkDBError($sql);
?>
	<p class="fat_black">Back Order Summary</p>
	<table width="45%" align="left" class="sortable" border="0" cellspacing="0" cellpadding="5">
		<tr bgcolor="#fcfcfc">
			<th width="15%" align="center" valign="top" class="fat_black_12">
				Dealer
			</td>
			<th width="15%" align="left" valign="top" class="fat_black_12">
				BO#
			</td>
			<th width="15%" align="center" valign="top" class="fat_black_12">
				Date
			</td>
			<th width="50%"align="left" valign="top" class="fat_black_12">
				Form Name
			</td>
			<th width="10%" align="center" valign="top" class="fat_black_12">
				Status
			</td>
			<th width="10%" align="center" valign="top" class="fat_black_12">
				Action
			</td>
		</tr>
<?php
while ($bo = mysql_fetch_assoc($query)) {
	$bo['id'] = $bo['id'] + 1000;
	if ($customer != $bo['user_id']) {
		echo customerRow($bo['first_name'], $bo['last_name']);
		$customer = $bo['user_id'];
	}
?>
		<tr>
			<td class="text_12">&nbsp;</td>
			<td class="text_12">
				<?php echo $bo['id']; ?>
			</td>
			<td class="text_12">
				<?php echo formatdate($bo['date']); ?>
			</td>
			<td class="text_12">
				<?php echo getFormName($bo['form_id']); ?>
			</td>
<?php if ($bo['canceled']) { ?>
			<td class="text_12" style="color : #ff0000;">
				Canceled
			</td>
<?php } elseif ($bo['completed']) { ?>
			<td class="text_12" style="color : #000000;">
				Completed
			</td>
<?php } else { ?>
			<td class="text_12" style="color: #00cc00;">
				Pending
			</td>
<?php } // end if/elseif/else ?>
			<td class="text_12" nowrap>
				[<a href="../backorder_view.php?bo=<?php echo $bo['id']; ?>&return=<?php echo urlencode("admin/report-backorder.php?".$_SERVER['QUERY_STRING']); ?>">View BO</a>]
                                [<a onClick="return confirm('Are you sure you want to push BO#<?php echo $bo['id']; ?> through, even if there is not enough available stock?')" href="report-backorder-push.php?bo=<?php echo $bo['id']; ?>&action=push&return=<?php echo urlencode("admin/report-backorder.php?".$_SERVER['QUERY_STRING']); ?>">Order</a>]
				<?php if (secure_is_superadmin()) { ?>
				[<a onClick="return confirm('Are you sure you want to delete BO#<?php echo $bo['id']; ?>?')" href="report-backorder-push.php?bo=<?php echo $bo['id']; ?>&action=cancel&return=<?php echo urlencode("admin/report-backorder.php?".$_SERVER['QUERY_STRING']); ?>">Cancel</a>]
				<?php } ?>
			</td>
		</tr>
<?php } // end foreach ?>
	</table>
 </BODY>
</HTML>
