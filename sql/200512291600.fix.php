<?php
#
# Fixes Items that somehow did not match snapshot
#

function checkDBError($link, $sql = 0) {
	if (mysql_error($link) != "") {
		if (!$sql) {
			unset($sql);
			global $sql;
		}
		echo "\n<br />".mysql_error($link);
		echo "\n<br />".$sql;
		exit;
	}
}
$starttime = time();
$newlink = mysql_connect('localhost', 'pmddealer', 'maquis22');
mysql_select_db('pmddealer', $newlink);
$oldlink = mysql_connect('localhost', 'oldpmd', 'maquis22');
mysql_select_db('oldpmd', $oldlink);

$sql = "SELECT DISTINCT po_id  FROM `orders` WHERE `item` = 0";
$broken_pos = mysql_query($sql, $newlink);
checkDBError($newlink, $sql);
$unfixablepos = 0;
$unfixablelines = 0;
$nosnaps = 0;
$addedheaders = 0;
$addeditems = 0;
$updatedorders = 0;
$fixedpos = 0;
$unknownaddy = 0;
$addedlines = 0;
echo "\n<br />! - Cannot fix PO: No matching orders in pmdold";
echo "\n<br /># - Cannot fix order line";
echo "\n<br />$ - Added Header";
echo "\n<br />% - Added Snapshot Item";
echo "\n<br />+ - Finished Order Line";
echo "\n<br />. - Finished PO";
echo "\n<br />";
$chars = 0;
$width = 40;

function checkwidth() {
	global $chars;
	global $width;

	++$chars;

	if ($width < $chars) {
		echo "\n<br />";
		$chars = 0;
	}
}

while ($broken_po = mysql_fetch_array($broken_pos, MYSQL_ASSOC)) {
	$broken_po = $broken_po['po_id'];
	$sql = "SELECT ID, item FROM `orders` WHERE `po_id` = '".mysql_escape_string($broken_po)."'";
	$broken_orders = mysql_query($sql, $oldlink);
	checkDBError($oldlink, $sql);
	if (!mysql_num_rows($broken_orders)) {
		echo "!";
		checkwidth();
		++$unfixablepos;
		continue;
	}
	while ($broken_order = mysql_fetch_array($broken_orders, MYSQL_ASSOC)) {
		$sql = "SELECT item FROM orders WHERE item = 0 AND ID = '".mysql_escape_string($broken_order['ID'])."'";
		$result = mysql_query($sql, $newlink);
		checkDBError($newlink, $sql);
		if (!mysql_num_rows($result)) {
			// Don't need to mess with it
			continue;
			$sql = "SELECT ID, user, form, setqty, mattqty, qty, item, ordered, form, po_id, ordered_time FROM orders WHERE ID = '".mysql_escape_string($broken_order['ID'])."'";
			$query2 = mysql_query($sql, $oldlink);
			checkDBerror($oldlink,$sql);
			$result2 = mysql_fetch_array($query2, MYSQL_ASSOC);
			$sql = "SELECT snapshot_user, snapshot_form FROM order_forms WHERE ID = '".$broken_po."'";
			$query3 = mysql_query($sql, $newlink);
			checkDBerror($newlink,$sql);
			$result3 = mysql_fetch_array($query3, MYSQL_ASSOC);
			$sql = "INSERT INTO orders (`ID`, `user`, `setqty`, `mattqty`, `qty`, `item`, `ordered`, `form`,`po_id`, `ordered_time`, `snapshot_user`, `snapshot_form`) VALUES ('".mysql_escape_string($broken_order['ID'])."','".mysql_escape_string($result2['user'])."','".mysql_escape_string($result2['setqty'])."','".mysql_escape_string($result2['mattqty'])."','".mysql_escape_string($result2['qty'])."','0','".mysql_escape_string($result2['ordered'])."','".mysql_escape_string($result2['form'])."','".mysql_escape_string($result2['po_id'])."','".mysql_escape_string($result2['ordered_time'])."','".mysql_escape_string($result3['snapshot_user'])."','".mysql_escape_string($result3['snapshot_form'])."')";
			mysql_query($sql,$newlink);
			checkDBerror($newlink,$sql);
			echo "@";
			checkwidth();
			++$addedlines;
			continue;
		}
		$result = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($result['item']) {
			// We don't need to fix something that already works...
			continue;
		}
		$sql = "SELECT partno, description, price, set_, matt, box, size, color, header, cubic_ft FROM order_snapshot WHERE orders_id = '".mysql_escape_string($broken_order['ID'])."'";
		$result = mysql_query($sql, $oldlink);
		checkDBError($oldlink, $sql);
		if (!mysql_num_rows($result)) {
			echo "#";
			checkwidth();
			++$nosnaps;
			continue;
		}
		$result = mysql_fetch_array($result, MYSQL_ASSOC);
		$sql = "SELECT `id` FROM `snapshot_headers` WHERE `header` = '".mysql_escape_string($result['header'])."'";
		$result2 = mysql_query($sql, $newlink);
		checkDBError($newlink, $sql);
		if (!mysql_num_rows($result2)) {
			echo "$";
			checkwidth();
			++$addedheaders;
			$sql = "INSERT INTO snapshot_headers (`orig_id`, `form`, `header`, `display_order`) VALUES ('0','0','".mysql_escape_string($result['header'])."','0')";
			mysql_query($sql, $newlink);
			checkDBError($newlink, $sql);
			$result['header'] = mysql_insert_id($newlink);
		} else {
			$result2 = mysql_fetch_array($result2, MYSQL_ASSOC);
			$result['header'] = $result2['id'];
		}
		$sql = "SELECT `id` FROM snapshot_items WHERE `header` = '".mysql_escape_string($result['header'])."' AND `partno` = '".mysql_escape_string($result['partno'])."' AND `description` = '".mysql_escape_string($result['description'])."' AND `price` = '".mysql_escape_string($result['price'])."' AND `size` = '".mysql_escape_string($result['size'])."' AND `color` = '".mysql_escape_string($result['color'])."' AND `set_` = '".mysql_escape_string($result['set_'])."' AND `matt` = '".mysql_escape_string($result['matt'])."' AND `box` = '".mysql_escape_string($result['box'])."' AND `cubic_ft` = '".mysql_escape_string($result['cubic_ft'])."'";
		$result2 = mysql_query($sql, $newlink);
		checkDBError($newlink, $sql);
		if (!mysql_num_rows($result2)) {
			$sql = "INSERT INTO snapshot_items (`orig_id`, `header`, `partno`, `description`, `price`, `size`, `color`, `set_`, `matt`, `box`, `display_order`, `cubic_ft`) VALUES ('0','".mysql_escape_string($result['header'])."','".mysql_escape_string($result['partno'])."','".mysql_escape_string($result['description'])."','".mysql_escape_string($result['price'])."','".mysql_escape_string($result['size'])."','".mysql_escape_string($result['color'])."','".mysql_escape_string($result['set_'])."','".mysql_escape_string($result['matt'])."','".mysql_escape_string($result['box'])."','0','".mysql_escape_string($result['cubic_ft'])."')";
			mysql_query($sql, $newlink);
			checkDBError($newlink, $sql);
			$newitem = mysql_insert_id($newlink);
			echo "%";
			checkwidth();
			++$addeditems;
		} else {
			$result2 = mysql_fetch_array($result2, MYSQL_ASSOC);
			$newitem = $result2['id'];
		}
		$sql = "UPDATE orders SET `item` = '".mysql_escape_string($newitem)."' WHERE ID = '".mysql_escape_string($broken_order['ID'])."'";
		mysql_query($sql, $newlink);
		checkDBError($newlink, $sql);
		++$updatedorders;
		set_time_limit(0);
		echo "+";
		checkwidth();
	}
	++$fixedpos;
	set_time_limit(0); // No running out of time
	flush(); // Get some of our progress out to the client
	ob_flush(); // ^^
	echo ".";
	checkwidth();
}
echo "\n<br />\n<br />= Summary =\n<br />";
echo "\n<br />Non-Fixable POs: ".$unfixablepos;
echo "\n<br />Non-Fixable Lines: ".$unfixablelines;
echo "\n<br />No Old Snapshot Lines: ".$nosnaps;
echo "\n<br />Headers Added: ".$addedheaders;
echo "\n<br />Added Items: ".$addeditems;
echo "\n<br />Unknown Addresses: ".$unknownaddy;
echo "\n<br />Fixed Order Lines: ".$updatedorders;
echo "\n<br />Fixed POs: ".$fixedpos;
$totaltime = $time - $starttime;
$seconds = $totaltime;
$minutes = $seconds / 60;
$seconds = $seconds % 60;
$hours = $minutes / 60;
$minutes = $minutes % 60;
echo "\n<br />Total Time: ".$hours.":".$minutes.":".$seconds;
?>
