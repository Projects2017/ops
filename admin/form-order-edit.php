<?php
require("database.php");
require("secure.php");
require("../inc_orders.php");

function header_order_compare($x, $y) {
	if ( $x[1] == $y[1] )
		return 0;
	else if ( $x[1] < $y[1] )
		return -1;
	else
		return 1;
}

if ($_POST['what'] == "headers") {
	$newheaders = array();
	// Load everything into an array
	foreach ($_POST as $headerID => $display_order) {
		if (is_numeric($headerID))
			$newheaders[] = array($headerID,$display_order);
	}
	// Sort the array
	usort($newheaders, 'header_order_compare');
	// Loop through it
	$i = 1;
	foreach ($newheaders as $header) {
		$display_order = $i;
		$headerID = $header[0];
		$sql = "Update form_headers set display_order='$display_order' where ID=$headerID";
		mysql_query($sql);
		checkDBError($sql);
		snapshot_update('header', $headerID);
		++$i;
	} 
	/*
	foreach ($_POST as $headerID => $display_order) {
		if (is_numeric($headerID)) {
			/ Prep for update...
			$sql = "Update form_headers set display_order='$display_order' where ID=$headerID";
			mysql_query($sql);
			checkDBError($sql);
			snapshot_update('header', $headerID);
		}
	}
	*/
}

mysql_close($link);

header("location: form-edit.php?ID=".$_POST['form_id']);
?>
