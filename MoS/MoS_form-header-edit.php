<?php
require( "MoS_database.php" );
require( "../inc_orders.php" );
if( $header == "" )
{
	$sql = "select ID from MoS_form_items where header=$headerID";
	$query = mysql_query($sql);
	checkDBError($sql);
	while ($item = mysql_fetch_array($query)) {
		$sql = "update MoS_snapshot_items set orig_id = 0 where orig_id = '".$item['ID']."'";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "delete from MoS_form_items where ID = '".$item['ID']."' LIMIT 1";
		mysql_query($sql);
		checkDBError($sql);
	}
	$sql = "delete from MoS_form_items where header=$headerID";
	mysql_query( $sql );
	checkDBError($sql);

	$sql = "update MoS_snapshot_headers set orig_id = 0 where orig_id = '$headerID'";
	mysql_query($sql);
	checkDBError($sql);
	
	$sql = "delete from MoS_form_headers where ID='$headerID'";
	mysql_query($sql);
	checkDBError($sql);
} else {
	$sql = "SELECT MoS_form_headers.header, MoS_form_headers.form, MoS_form_headers.display_order, MoS_forms.snapshot FROM MoS_form_headers INNER JOIN MoS_forms ON MoS_forms.ID = MoS_form_headers.form WHERE MoS_form_headers.ID = $headerID LIMIT 1";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$result = mysql_fetch_array($query);
	// Make header snapshot
	$sql = "INSERT INTO MoS_snapshot_headers (`orig_id`, `header`, `form`,`display_order`) VALUES ($headerID, '$header', ".$result['snapshot'].",'".$result['display_order']."')";
	mysql_query($sql);
	checkDBerror($sql);
	$snapshot = mysql_insert_id();
	// Update item snapshots...
	$sql = "SELECT ID, header, partno, description, price, size, color, set_, matt, box, display_order, cubic_ft FROM MoS_form_items WHERE header = $headerID";
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($row = mysql_fetch_array($query)) {
		$sql = "INSERT INTO MoS_snapshot_items (`orig_id`, `header`, `partno`, `description`, `price`, `size`, `color`, `set_`, `matt`, `box`, `display_order`, `cubic_ft`) VALUES (".$row['ID'].", $snapshot, '".$row['partno']."', '".$row['description']."', '".$row['price']."', '".$row['size']."', '".$row['color']."', '".$row['set_']."', '".$row['matt']."', '".$row['box']."', '".$row['display_order']."', '".$row['cubic_ft']."')";
		mysql_query($sql);
		checkDBerror($sql);
		$item_snapshot = mysql_insert_id();
		$sql = "UPDATE MoS_form_items SET snapshot = ".$item_snapshot." WHERE ID = ".$row['ID'];
		mysql_query($sql);
		checkDBerror($sql);
	}
	// Prep for update...
	$form = $result['form']; // Can't update which form this header belongs to...
	$sql = buildUpdateQuery( "MoS_form_headers", "ID=$headerID" );
	mysql_query($sql);
	checkDBError($sql);
	
	$result = resortheaders($form, 'MoS_');
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('header', $headerID);
	}
}

mysql_close($link);

header( "Location: MoS_edit-forms-edit.php?ID=$form" );
?>
