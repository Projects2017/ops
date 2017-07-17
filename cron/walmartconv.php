#!/usr/bin/php
<?php
include(dirname(__FILE__)."/../database.php");//imparts $link as the database handle
$sql = "SELECT `form_items`.* FROM `form_items` WHERE `header` = 13315";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['avail'] == -1) {
        $code = 'AA';
    } elseif ($row['avail'] > 0) {
        $code = 'AC';
    } else {
        $code = 'NA';
    }

	if ($row['stock'] != 1) {
		$code = 'NA';
	} elseif ($row['stock'] == 3) {
		$code = 'DT';
	}

    $parts = explode(":",$row['sku']);
    if (count($parts) == 1) {
        $upc = $parts[0];
        $sku = ''; // Walmart Item ID
    } elseif (count($parts) == 2) {
        $upc = $parts[0];
        $sku = $parts[1]; // Walmart Item ID
    }
	$upc =  str_pad($upc,12,"0",STR_PAD_LEFT); // UPC should have twelve digits, prefix with 0's appropriately.

	// Neither of these codes need avail
	if ($code == 'NA'||$code == 'AA'||$code == 'DT') {
		$sql = "INSERT INTO `walmart_inventory` (`formitems_id`, `item_id`, `upc`, `sku`,`avail_code`,`numavail`,`mindaytoship`,`maxdaytoship`,`msrp`,`retail`,`cost`,`facility`)
    VALUES ('".$row['ID']."','".$sku."','".$upc."','".$row['partno']."','".$code."',NULL,NULL,NULL,NULL,NULL,'".$row['price']."','')";
	} else {
		$sql = "INSERT INTO `walmart_inventory` (`formitems_id`, `item_id`, `upc`, `sku`,`avail_code`,`numavail`,`mindaytoship`,`maxdaytoship`,`msrp`,`retail`,`cost`,`facility`)
    VALUES ('".$row['ID']."','".$sku."','".$upc."','".$row['partno']."','".$code."','".$row['avail']."',1,2,NULL,NULL,'".$row['price']."','TX')";
	}
    
    mysql_query($sql);
}