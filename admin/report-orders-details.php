<?php
require("database.php");
require("../inc_content.php");
require("secure.php");
?>
<html>
<head>
<title>RSS Administration</title>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="/js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="/css/jquery.modal.min.css" type="text/css" media="screen" />
<link rel="stylesheet" href="../styles.css" type="text/css">
</head>
<body>
<?php
$section = "admin";
$for = 'U';
if (isset($_GET['for']) && $_GET['for'] == 'vendor') {
    $for = 'V';
}

echo OrderForWeb($po,$section, $for);

if (secure_is_superadmin()) {
	$po_id = $po-1000;
	$query = mysql_query("SELECT deleted FROM order_forms WHERE ID=$po_id");
	$result = mysql_fetch_array($query);
	//ADD ERROR CHECK FOR NO ROWS RETURNED HERE
	$deleted = $result['deleted'];
	if ($deleted == 0) //rename submit button text from "Delete this order" to "DO" per Gary Davis instructions 2004-06-04 by Goody
		echo "<div align=\"center\"><form action=\"report-orders-delete.php\" method=\"post\" onSubmit=\"return verifydelete()\">
		 <input type=\"hidden\" name=\"delete\" value=\"1\">
		 <input type=\"hidden\" name=\"po\" value=\"$po\">
		 <input type=\"hidden\" name=\"ordered\" value=\"$ordered\">
		 <input type=\"hidden\" name=\"ordered2\" value=\"$ordered2\">
		 <input type=\"hidden\" name=\"request\" value=\"" . $_GET['request'] . "\">
		 <input type=\"submit\" value=\"DO\">
		 </form></div>";
	else
		echo "<div align=\"center\"><form action=\"report-orders-delete.php\" method=\"post\" onSubmit=\"return verifyreinstate()\">
		 <input type=\"hidden\" name=\"delete\" value=\"0\">
		 <input type=\"hidden\" name=\"po\" value=\"$po\">
		 <input type=\"hidden\" name=\"ordered\" value=\"$ordered\">
		 <input type=\"hidden\" name=\"ordered2\" value=\"$ordered2\">
		 <input type=\"hidden\" name=\"request\" value=\"" . $_GET['request'] . "\">
		 <input type=\"submit\" value=\"Reinstate This Order\">
		 </form></div>";
}

#mysql_close($link);
?>

<?php
require('../payment_history.php');
?>


<center>
<br>
	<button id="btnPayment" style="font-size:26px;">MAKE PAYMENT</button><br><br>
</center>

<script>
var totalBalance;
var totalDue;

$(function(){
var amt = $("#grandTotal").html().replace(/(<([^>]+)>)/ig,"");
totalBalance = amt;
totalDue = amt;
});
var totalPayment = 0;
var orderFormID = '<?php=$po?>';
var forwardToDetails = 0;
</script>

<script src="/js/checkout.js" type="text/javascript" charset="utf-8"></script>

</body>
</html>
