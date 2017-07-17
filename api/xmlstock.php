<?php
/**
 * Export XML Stock
 */
require("../database.php");
require("../secure.php");

header("Content-type: text/xml");

// Header
echo '<?phpxml version="1.0" encoding="utf-8"?>'."\n";
echo '<message xmlns="http://www.pmdfurniture.com/schemas/"'."\n";
echo 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
echo 'xsi:schemaLocation="http://www.pmdfurniture.com/schemas/'."\n";
echo 'http://www.pmdfurniture.com/schemas/message.xsd">'."\n";

// Greeting
echo "\t".'<greeting>'."\n";
echo "\t\t".'<vendorid>'.$userid.'</vendorid>'."\n";
echo "\t\t".'<vendorkey>xxxNOTUSEDxxx</vendorkey>'."\n";
echo "\t".'</greeting>'."\n";


// Actual Stock Report

$sql = "select forms.ID from forms inner join form_access ON form_access.form = forms.ID where form_access.user = '$userid' AND forms.alloworder = 'Y'";
$form_query = mysql_query($sql);
checkDBerror($sql);
echo "\t".'<stock>'."\n";
while ($form = mysql_fetch_assoc($form_query)) {
	$sql = "select form_items.partno, form_items.stock, form_items.stock_day, form_items.avail from form_items inner join form_headers ON form_headers.ID = form_items.header WHERE form_headers.form = '".$form['ID']."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($item = mysql_fetch_assoc($query)) {
		echo "\t\t".'<item sku="'.$item['partno'].'">'."\n";
		$status = stock_status($item['stock']);
		echo "\t\t\t".'<stockstatus>'.$status['name'].'</stockstatus>'."\n";
		if ($item['stock_day'] > 0 && $status['zeroday'] == 'N') {
			echo "\t\t\t".'<stockday>'.$item['stock_day'].'</stockday>'."\n";
		}
		if ($item['avail'] > 0 && $status['block_order'] == 'N') {
			echo "\t\t\t".'<allocation>'.$item['avail'].'</allocation>'."\n";
		}
		echo "\t\t".'</item>'."\n";
	}
}
echo "\t".'</stock>'."\n";

// Footer
echo '</message>';
?>
