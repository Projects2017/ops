<?php
require( "database.php" );
require( "secure.php" );
require( "../inc_orders.php" );

if( $header == "" )
{
	$sql = "select ID from form_items where header=$headerID";
	$query = mysql_query($sql);
	checkDBError($sql);
	while ($item = mysql_fetch_array($query)) {
		$sql = "update snapshot_items set orig_id = 0 where orig_id = '".$item['ID']."'";
		mysql_query($sql);
		checkDBError($sql);
		$sql = "delete from form_items where ID = '".$item['ID']."' LIMIT 1";
		mysql_query($sql);
		checkDBError($sql);
	}
	$sql = "delete from form_items where header=$headerID";
	mysql_query( $sql );
	checkDBError($sql);

	$sql = "update snapshot_headers set orig_id = 0 where orig_id = '$headerID'";
	mysql_query($sql);
	checkDBError($sql);
	
	$sql = "delete from form_headers where ID='$headerID'";
	mysql_query($sql);
	checkDBError($sql);
} else {
	$sql = "SELECT form_headers.form FROM form_headers WHERE form_headers.ID = $headerID LIMIT 1";
	$query = mysql_query($sql);
	checkDBerror($sql);
	$result = mysql_fetch_array($query);
	// Prep for update...
	$form = $result['form']; // Can't update which form this header belongs to...
	$sql = buildUpdateQuery( "form_headers", "ID=$headerID" );
	mysql_query($sql);
	checkDBError($sql);
	
	$result = resortheaders($form);
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('header', $headerID);
	}
}

mysql_close($link);

header( "Location: form-edit.php?ID=$form" );
?>
