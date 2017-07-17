<?php
// adminprinted.php
// this script will set the adminprinted field in the GET'd BOL ID
require_once('../database.php');
require_once('../secure.php');
require_once('inc_postbol.php');
if(!$_GET['id']) {
	die("This script requires a BoL ID to be passed via GET.");
}
if(!secure_is_admin()) {
	die("This script requires super-admin access.");
}
// lets go
$bol_id = $_GET['id'];
$sql = "UPDATE BoL_forms SET adminprinted = 1 WHERE ID = $bol_id";
$query = mysql_query($sql);
// moved to updateoor.php & renamed addCHQueue($bol)
// adminPrinted($bol_id); // function designed to run after the admin copy is printed
setcookie('BoL_msg', 'Admin print complete for BOL '.($bol_id+1000));
header("Location: shipping.php");
?>
