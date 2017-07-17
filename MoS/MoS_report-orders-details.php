<?php
require("MoS_database.php");
require("../inc_content.php");

if ($_GET['u'] == 1) {
	require("MoS_user_secure.php");
	require("MoS_dealer_menu.php");
	$section = "web";
}
else {
	require("MoS_admin_secure.php");
	require("MoS_menu.php");
	$section = "admin";
}
?>
<script language="javascript">
function verifydelete() {
    msg = "Are you sure you want to delete this order?";
    return confirm(msg);
}
function verifyreinstate() {
    msg = "Are you sure you want to reinstate this order?";
    return confirm(msg);
}
</script>
<?php

echo "<H3>Market Order System - Order Detail</H3>";

echo OrderForWeb($po,$section);

if ($security == "S") {
	$po_id = $po-1000;
	$query = mysql_query("SELECT deleted FROM MoS_order_forms WHERE ID=$po_id");
	$result = mysql_fetch_array($query);
	//ADD ERROR CHECK FOR NO ROWS RETURNED HERE
	$deleted = $result['deleted'];
	if ($deleted == 0) //rename submit button text from "Delete this order" to "DO" per Gary Davis instructions 2004-06-04 by Goody
		echo "<div align=\"center\"><form action=\"MoS_report-orders-delete.php\" method=\"post\" onSubmit=\"return verifydelete()\">
		 <input type=\"hidden\" name=\"delete\" value=\"1\">
		 <input type=\"hidden\" name=\"po_id\" value=\"$po_id\">
		 <input type=\"hidden\" name=\"ordered\" value=\"$ordered\">
		 <input type=\"hidden\" name=\"ordered2\" value=\"$ordered2\">
		 <input type=\"hidden\" name=\"request\" value=\"" . $_GET['request'] . "\">
		 <input type=\"submit\" value=\"DO\">
		 </form></div>";
	else
		echo "<div align=\"center\"><form action=\"MoS_report-orders-delete.php\" method=\"post\" onSubmit=\"return verifyreinstate()\">
		 <input type=\"hidden\" name=\"delete\" value=\"0\">
		 <input type=\"hidden\" name=\"po_id\" value=\"$po_id\">
		 <input type=\"hidden\" name=\"ordered\" value=\"$ordered\">
		 <input type=\"hidden\" name=\"ordered2\" value=\"$ordered2\">
		 <input type=\"hidden\" name=\"request\" value=\"" . $_GET['request'] . "\">
		 <input type=\"submit\" value=\"Reinstate This Order\">
		 </form></div>";
}

mysql_close($link);
?>
</body>
</html>
