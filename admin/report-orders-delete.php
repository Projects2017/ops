<?php
require("database.php");
require("../inc_content.php");

if ($delete) {
	deletePO($po);
} else {
	restorePO($po);
}
/* moved to inc_content:deletePO/restorePO
$sql = "UPDATE order_forms SET deleted=$delete WHERE ID=$po_id";
$query = mysql_query($sql);

$sql = "SELECT users.email, users.email2, users.email3 FROM users INNER JOIN order_forms 
 ON users.ID = order_forms.user WHERE order_forms.ID=$po_id";
$query = mysql_query($sql);
checkDBError();
if ($result = mysql_fetch_array($query)) {
	$email = $result[0];
	$email2 = $result[1];
	$email3 = $result[2];
}

if ($delete == 0)
	$action = "Reinstated";
else
	$action = "Deleted";

$msg = 
"The following order has been ".strtoupper($action).".

-----------------------------------------------------------
";
$po = $po_id+1000;
$msg .= OrderForEmail($po);
$subject = "Order $action";
$headers = "From: PMD Orders <orders@pmdfurniture.com>";
if ($email2 <> "") $headers .= "\nCc: ".$email2;
if ($email3 <> "") $headers .= "\nCc: ".$email3;
$headers .= "\nBcc: orders@pmdfurniture.com";
sendmail($email, $subject, $msg, $headers);
*/
mysql_close($link);

header("location: report-orders.php?ordered=$ordered&ordered2=$ordered2&deleted=0&" . urldecode($_POST['request']));
?>
