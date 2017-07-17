<?php
require( "MoS_database.php" );
require( "../inc_orders.php" );

$sql = buildInsertQuery( "MoS_form_headers" );
mysql_query( $sql );
checkDBError($sql);
$ID = mysql_insert_id();
$result = resortheaders($form, 'MoS_');
if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
	snapshot_update('header', $ID);
}
mysql_close($link);

header( "Location: MoS_edit-forms-edit.php?ID=$form" );
?>
