<?php

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');

$orderas = false;
$modify_avail = true;
$block_blocks = true;
if (isset($_GET['orderas']) && is_numeric($_GET['orderas']) && secure_is_admin()) {
    $orderas = true;
    $userid = $_GET['orderas'];
    $block_blocks = false;
}

require_once("inc_backorder.php");

// Does Dealer have Access to this Form
if (!secure_is_admin()&&!vendor_access('D', $userid, $_POST['form'])) die("You don't have Access to this form");

// Does form have Dropship?
$dropshipforms = array(987,928);
if (in_array($form, $dropshipforms)) {
	$dropship = true;
} else {
	$dropship = false;
}

$items = array();
for($c = 0; $c < $num_of_items; $c++ ) {
	if (!isset($_POST["item${c}"]))
		break;
	$item['item_id'] = $_POST["item${c}"];
	$item['setqty'] = (int)$_POST["setqty${c}"];
	$item['mattqty'] = (int)$_POST["mattqty${c}"];
	$item['qty'] = (int)$_POST["qty${c}"];
		if ($_POST["backorderqty${c}"] > 0) {
		$item['backorder'] = $_POST["backorderqty${c}"];
		$backorder[$item['item_id']] = $_POST["backorderqty${c}"];
	}
        if (!$orderas) {
            if (!$MoS_enabled&&($item['qty'] < 0)||($item['setqty'] < 0)||($item['mattqty'] < 0)) {
                    die("You may not order negative amounts of items");
            }
        }
	$items[] = $item;
}

$shipto = null;
if ($dropship && isset($_POST['shipto_dropship']) && $_POST['shipto_dropship'] == 'Y') {
	$shipto =  add_user($userid, $_POST['shipto_last_name'], $_POST['shipto_address'], null, $_POST['shipto_city'], $_POST['shipto_state'], $_POST['shipto_zip'], $_POST['shipto_phone'], null);
}

$po_obtained = false;
$form = $_POST['form'];
$user_address = $_POST['user_address'];
$_POST['user_address'] = $_POST['user_address'] ? $_POST['user_address'] : "1";
$po_id = submitOrder($userid, $user_address, $_POST['comments'], $_POST['form'], $items, false, false, $modify_avail, $block_blocks, null, $shipto);
if (is_array($po_id)) {
	echo "<p class=\"alert\">This order has not been submitted to home office and will not be saved.</p>";
	foreach ($po_id['messages'] as $message) {
		echo "<p class=\"alert\">".$message['text']."</p>";
	}
	if (!$MoS_enabled)
		echo "<p><b>[<a href=\"selectvendor.php\">Back to Vendor List</a>]</b></p>";
	exit(0);
}
$section = "web";
if ($po_id) {
    $po = ($po_id);
    echo OrderForWeb($po,$section);
}

if ($backorder) {
	$boid = newbackorder($_POST['form'], $backorder, $user_address, $userid);
	if ($boid)
		viewbo($boid, 'D', false);
	else
		echo "<h2>Backorder Creation Failed.</h2>";
}

mysql_close($link);
if (!$MoS_enabled) {
	?>
	<div align="center"><form name="form2" method="post" action="javascript:window.print();">
	<button id="btnPayment" style="font-size:26px;">MAKE PAYMENT</button><br><br>
	<input type="submit" name="Submit" value="Print Order">
	</form></div>
	<p align="center"><a href="selectvendor.php">Select Another Vendor</a></p>
	<?php
}
?>

<script>
var totalBalance;;
var totalDue;
var totalPayment = 0;
var orderFormID = '<?php=$po?>';
var forwardToDetails = 1;
</script>
<script src="/js/checkout.js" type="text/javascript" charset="utf-8"></script>

</body>
</html>
