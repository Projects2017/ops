<?php
require("MoS_database.php");
require("../inc_content.php");
require("MoS_inc_content.php");
require("MoS_admin_secure.php");


//print_r($_POST);
MoS_process_order($_POST['action'], $po);

header("location: MoS_report_orders.php?" . $_POST['request']);
?>
