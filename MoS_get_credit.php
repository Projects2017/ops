<?php

//-- echoes the credit limit||balance of a user
error_reporting(0);
require("database.php");

if (MoS_checkip($_SERVER['REMOTE_ADDR'])) {
	if ($_GET['hvar'] == "valid") {
		$user3 = $_GET['user'];
		$sql = "SELECT credit_limit FROM users WHERE ID=$user3";
		$query = mysql_query($sql);
		if ($result = mysql_fetch_array($query)) {
			$credit_limit = $result[0];
			$sql = "SELECT SUM(total) FROM order_forms WHERE user=$user3 AND deleted=0";
			$query = mysql_query($sql);
			$result = mysql_fetch_array($query);
			$balance = $result[0];
	
			echo $credit_limit . "||" . $balance;
			exit;
		}
	}
} else {
	echo "0||0"; // Credit Limit is 0 for everyone
}

