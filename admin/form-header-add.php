<?php
require( "database.php" );
require( "secure.php" );
require( "../inc_orders.php" );

$sql = buildInsertQuery( "form_headers" );
mysql_query( $sql );
checkDBError($sql);
$ID = mysql_insert_id();
$result = resortheaders($form);
if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
	snapshot_update('header', $ID);
}

mysql_close($link);

header( "Location: form-edit.php?ID=$form" );
?>
