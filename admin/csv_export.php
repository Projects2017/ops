<?php
require("database.php");
require("secure.php");

$ID = $_GET['ID'];

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=exported_form_" . $ID . "_" . str_replace(" ", "_", $_GET['name']) . ".csv");
header("Content-Transfer-Encoding: binary");
//header("Content-Length: ". filesize($file_name));

$fp=fopen("php://output","w");

$headers = array (
    'Order',
    'Part #',
    'SKU',
    'Desc',
    'Price',
    'Cost',
    'Markup',
    'Size',
    'Set',
    'Set Cost',
    'Set Markup',
    'Matt',
    'Matt Cost',
    'Matt Markup',
    'Box',
    'Box Cost',
    'Box Markup',
    '',
    '',
    'Set Qty',
    'Qty in Set',
    'Tier Override',
    'Discount',
    'Freight',
    'Volume',
    'Seats',
    'Weight');
$headerkey = array_flip($headers);

function convertarray($items) {
    global $headerkey;
    $output = array();
    for ($i = 0; $i < count($items); $i++) {
        $output[] = '';
    }
    foreach ($items as $key => $val) {
        if (!isset($headerkey[$key])) {
            print_r($items);
            die("\n\nUnknown Array Key: ".$key);
        }
        $output[$headerkey[$key]] = $val;
    }
    return $output;
}

fputcsv($fp,$headers);
fputcsv($fp,convertarray(array())); // Blank Line
$sql = "select * from form_headers where form=$ID order by display_order";
$query = mysql_query($sql);
checkDBError();

while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
        fputcsv($fp, array('',$result['header']));
	$order = 1;
	$sql = "select * from form_items where header=".$result['ID']." order by display_order";
	$query2 = mysql_query($sql);
	checkDBError();

	while ($result2 = mysql_fetch_array($query2, MYSQL_ASSOC))	{
                $item = array(
                    'Order' => $order,
                    'Part #' => $result2['partno'],
                    'SKU' => $result2['sku'],
                    'Desc' => $result2['description'],
                    'Price' => $result2['price'],
                    'Cost' => $result2['cost'],
                    'Markup' => $result2['markup'],
                    'Size' => $result2['size'],
                    'Set' => $result2['set_'],
                    'Set Cost' => $result2['set_cost'],
                    'Set Markup' => $result2['set_markup'],
                    'Matt' => $result2['matt'],
                    'Matt Cost' => $result2['matt_cost'],
                    'Matt Markup' => $result2['matt_markup'],
                    'Box' => $result2['box'],
                    'Box Cost' => $result2['box_cost'],
                    'Box Markup' => $result2['box_markup'],
                    'Set Qty' => $result2['setqty'],
                    'Qty in Set' => $result2['numinset'],
                    'Tier Override' => $result2['item_tier_override']?'Y':'N',
                    'Discount' => loadDiscount('discount',array("item_id" => $result2['ID']),"form_item"),
                    'Freight' => loadDiscount('freight',array("item_id" => $result2['ID']),"form_item"),
                    'Volume' => $result2['cubic_ft'],
                    'Seats' => $result2['seats'],
                    'Weight' => $result2['weight']
                );
                fputcsv($fp,convertarray($item));
		$order++;
	}
	fputcsv($fp,convertarray(array())); // Blank Line
}
fclose($fp);
exit;
