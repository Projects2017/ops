<?php
require("MoS_database.php");
require("MoS_admin_secure.php");
require( "../inc_orders.php" );

$data = array();
$old = array();

foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^data_(.+)",$key, $reg))
		$data[$reg[1]] = $value;
}


foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^old_(.+)",$key, $reg))
		$old[$reg[1]] = $value;
}

$update = array();
foreach($data as $key => $value) {
	$reg = array();
	if(ereg("^([0-9]+)_(.+)",$key, $reg)) {
		if ($old[$key] != $value) {
			$update[$reg[1]][$reg[2]] = $value;
		}
		if ($_REQUEST['allstock'] != 0) {
			$update[$reg[1]]['stock'] = $_REQUEST['allstock'];
		}
	}
}

foreach ($update as $id => $data) {
	if (count($data) == 0)
		continue;
	$setstring = array();
	foreach ($data as $key => $value) {
		if (get_magic_quotes_gpc()) { // Remove the Magic Quotes if their enabled
			$key = stripslashes($key);
			$value = stripslashes($value);
		}
		$setstring[] = "`".mysql_escape_string($key)."` = '".mysql_escape_string($value)."'";
	}
	$setstring = implode(", ",$setstring);
	$id = mysql_escape_string($id);
	$sql = "UPDATE `MoS_form_items` SET ".$setstring." WHERE ID = '".$id."'";
	mysql_query($sql);
	CheckDBError($sql);
	snapshot_update('item', $id); // Update Snapshot for the item
}

mysql_close($link);

header("location: MoS_edit-forms-edit.php?ID=$form_id");
?>