<?php
require("MoS_database.php");

$sql = "UPDATE MoS_order_forms SET deleted=$delete WHERE ID=$po_id";
$query = mysql_query($sql);
checkDBerror($sql);

mysql_close($link);

header("location: MoS_report_orders.php?" . urldecode($_POST['request']));
?>