<?php
require("database.php");
require("inc_content.php");
$duallogin = 1;
include("vendorsecure.php");
if (!$vendorid)
   include("secure.php");
   
$intGrandTotal = 0;

if (!is_numeric($po)) {
	die("PO# is not numeric!");
}

if (!$po) {
	die("Need a non-zero PO#");
}
   
if (!secure_is_admin()) {
	if (secure_is_dealer()) {
		if ($userid != viewpo_dealerowner($po)) {
			die("That PO# doesn't belong to you!");
		}
	} else {
		$vid = viewpo_vendorowner($po);
		$sql = 'SELECT vlogin_access.ID FROM vlogin_access WHERE vlogin_access.user='.$vendorid." AND vlogin_access.vendor=".$vid;
		$query = mysql_query($sql);
		if (mysql_num_rows($query) == 0) {
			die('You do not have access to that PO# Sorry');
		}
		mysql_free_result($query);
	}
}
  
?>
<html>
<head>
<title>RSS</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="/js/jquery.modal.min.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="/css/jquery.modal.min.css" type="text/css" media="screen" />
<link rel="stylesheet" href="styles.css" type="text/css">
</head>
<body>

<?php
require('menu.php');
?>


<BR>
<?php
$section = "dealer";

echo OrderForWeb($po,$section);
?>

<?php
require('payment_history.php');
?>

<center>
<br>
	<button id="btnPayment" style="font-size:26px;">MAKE PAYMENT</button><br><br>
	<script type='text/javascript' src='https://www.rapidscansecure.com/siteseal/siteseal.js?code=65,C934EFCA8C0DC9D7ABA80B659C434D15B3F6F9B1'></script>
</center>

<p align="center">[<a href="javascript:history.back();">Back to Order List</a>]</p>

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
