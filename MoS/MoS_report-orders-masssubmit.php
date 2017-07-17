<?php
require("MoS_database.php");
require("../inc_content.php");
require("MoS_inc_content.php");
require("MoS_admin_secure.php");


//print_r($_POST);
$orders = array();
foreach ($_POST as $post => $garbage) {
	if (substr($post,0,2) == 'po') {
		$post = substr($post,2);
		MoS_process_order('A', $post);
		//echo $post."\n<br/>";
	}
}

//MoS_process_order($_POST['action'], $po);

header("location: MoS_report_orders.php?" . $_GET['request']);
?>
