<?php
/**
 * Export CSV of all dealers orders with line items.
 */
require("database.php");
require("secure.php");

if (isset($_GET['ID'])) {
	$user = $_GET['ID'];
} else {
	$user = $userid;
}

function getFormName($form)
{
	$sql = "select snapshot_forms.name from snapshot_forms where snapshot_forms.ID='".$form."'";
	$query = mysql_query($sql);
	checkDBError($sql);

	if ($result = mysql_fetch_array($query))
		return $result['name'];
	return "";
}

function getItem($item)
{
	$sql = "select * from snapshot_items where ID='".$item."'";
	$query = mysql_query($sql);
	checkDBError($sql);

	if ($result = mysql_fetch_array($query))
		return $result;
	return "";
}

function getOrderType($type)
{
	if ($type == "c") return "Credit";
	elseif ($type == "f") return "Bill";
	else return "order";
}

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=orders_".$user.".csv");
header("Content-Transfer-Encoding: binary");

$sql = "SELECT * FROM order_forms WHERE user=".$user." AND deleted=0 ORDER BY `ID` DESC";
$query = mysql_query($sql);
checkdberror($sql);

$out = fopen('php://output', 'w');
// Output header
fputcsv($out, array('PO','Date', 'Vendor','','Item','Price','Qty','Total'));
while ($order = mysql_fetch_assoc($query)) {
    if ($order['type'] == 'o') {
        $form = getFormName($order['snapshot_form']);
    } else {
        $form = getOrderType($order['type']);
    }
    
    fputcsv($out, array(
            $order['ID'] + 1000,
            date('m/d/Y', strtotime($order['ordered'])),
            $form,
            makeThisLookLikeMoney($order['total']),
            '',
            '',
            '',
            ''
        ));
    $sql = "SELECT * FROM orders WHERE po_id = ".$order['ID']." ORDER BY `ID` DESC";
    $result = mysql_query($sql);
    checkDBerror($sql);
    while ($item = mysql_fetch_assoc($result)) {
        $snap = getItem($item['item']);
	$partno_label = '';
        if ($snap['box'] != '') {
        	$snap['price'] = $snap['box'];
		$partno_label = ' (Box)';
        }
        if ($item['qty'] > 0) {
            fputcsv($out, array(
                '',
                '',
                '',
                '',
                $snap['partno'].$partno_label,
                makeThisLookLikeMoney($snap['price']),
                $item['qty'],
                makeThisLookLikeMoney($item['qty'] * $snap['price']),
            ));
        }

        if ($item['setqty'] > 0) {
            fputcsv($out, array(
                '',
                '',
                '',
                '',
                $snap['partno']. ' (Set)',
                makeThisLookLikeMoney($snap['set_']),
                $item['setqty'],
                makeThisLookLikeMoney($item['setqty'] * $snap['set_']),
            ));
        }

        if ($item['mattqty'] > 0) {
            fputcsv($out, array(
                '',
                '',
                '',
                '',
                $snap['partno'].' (Matt)',
                makeThisLookLikeMoney($snap['matt']),
                $item['mattqty'],
                makeThisLookLikeMoney($item['mattqty'] * $snap['matt']),
            ));
        }
    }
}
fclose($out);

?>
