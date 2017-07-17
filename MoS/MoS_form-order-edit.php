<?php
require("MoS_database.php");
require("MoS_admin_secure.php");
require( "../inc_orders.php" );

if ($what == "headers") {
	foreach ($HTTP_POST_VARS as $headerID => $display_order) {
		if (is_numeric($headerID)) {
			// Prep for update...
			$sql = "Update MoS_form_headers set display_order='$display_order' where ID=$headerID";
			mysql_query($sql);
			checkDBError($sql);
			snapshot_update('header', $headerID);
		}
	}
}

mysql_close($link);

header("location: MoS_edit-forms-edit.php?ID=$form_id");
?>
