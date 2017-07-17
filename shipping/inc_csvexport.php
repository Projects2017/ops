<?php
// CSV Export Classes

function salesord_queue($po) {
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_salesord.csv";
	if (!is_numeric($po)) die("Salesord Queue: PO is not numeric");
	
	$input = array(); // Input lines
	
	$sql = "SELECT `a`.`po_id`, `a`.`qty`, UNIX_TIMESTAMP(`a`.`ordered`) AS `date`, `b`.`partno`, `b`.`box`, `b`.`price` FROM `orders` AS `a` INNER JOIN `snapshot_items` AS `b` ON `a`.`item` = `b`.`id` WHERE `po_id` = '".($po - 1000)."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($result = mysql_fetch_assoc($query)) {
		$price = $result['box'] ? $result['box'] : $result['price'];
		$line = array();
		$line[] = $po; // PO#
		$line[] = date("n/j/Y",$result['date']); // PO Date
		$line[] = 'PFD'; // Customer Name (Dealers = PFD, Trade = Individual)
		$line[] = $result['partno']; // Part No
		$line[] = '0'; // Item Warehouse (unused)
		$line[] = $result['qty']; // Qty Ordered
		$line[] = $price; // Unit price
		$line[] = $price * $result['qty']; // MAS90 CALC (unused)
		$input[] = $line;
	}
	
	if (!count($input)) die("Salesord_Queue Error: Found 0 Lines for PO, does it exist?");
	
	
	// Add Row to File Processing
	$newfile = false;
	if (!file_exists($filename)) $newfile = true;
	$handle = fopen($filename,"ab");
	/*
	if ($newfile) {
		$line = array();
		$line[] = "Order number";
		$line[] = "Order date";
		$line[] = "Customer number";
		$line[] = "Item number";
		$line[] = "Item warehouse";
		$line[] = "Qty ordered";
		$line[] = "Unit price";
		$line[] = "Line extension";
		fputcsv($handle, $line);
	}
	*/
	foreach ($input as $line) {
		fputcsv($handle, $line);
	}
	fclose($handle);

	// set the DateTime of csv_exported in BoL_forms to now()
	$sql = "UPDATE order_forms SET csv_exported = NOW() WHERE ID = '".($po - 1000)."'";
	mysql_query($sql);
}

function salesord_release() {
	// Copies Pending CSV and copies it to a final destination
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_salesord.csv";
	$filename_new = $basedir."admin/exported_csvs/".date('Y-m-d')."_salesord.csv";
	if (!file_exists($filename)) {
		setcookie('BoL_msg', "No rows queued currently.", 0);
		header("Location: csvexport.php");
	}
	rename($filename, $filename_new); // Rename pending to date
	return "../admin/exported_csvs/".date('Y-m-d')."_salesord.csv"; // Return to allow front end to redirect properly
}

function soi_queue($bol) {
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_soi.csv";
	if (!is_numeric($bol)) die("SOI Queue: BoL ID# is not numeric");
	
	$input = array(); // Input lines
	// 2-12-08 OPD
	// changed SQL query to pull the BoL create date instead of the PO create date
	// before: within the UNIX_TIMESTAMP() function was `c`.`ordered` [ i.e. the date the PO was placed ]
	// after: within the UNIX_TIMESTAMP() function is `d`.`createdate`, which is the create date of the BoL
	$sql = "SELECT DISTINCT `a`.`po`, UNIX_TIMESTAMP(`d`.`createdate`) AS `date`, `d`.`freight` FROM `BoL_items` AS `a` INNER JOIN `BoL_forms` AS `d` ON `a`.`bol_id` = `d`.`ID` WHERE `a`.`type` = 'bol' AND `bol_id` = '".($bol - 1000)."'"; // TODO: There has to be a better way to do this outside of the distinct
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($result = mysql_fetch_assoc($query)) {
		$line = array();
		$line[] = $result['po'] + 1000; // PO#
		$line[] = date("n/j/Y",$result['date']); // BoL create Date
		$line[] = 'PFD'; // Customer Name (Dealers = PFD, Trade = Individual)
		if (!$freight) {
			$freight = $result['freight'];
			$line[] = $result['freight'];
		} else {
			$line[] = '0';
		}
		$input[] = $line;
	}
	
	if (!count($input)) die("Salesord_Queue Error: Found 0 Lines for BoL, does it exist?");
	
	
	// Add Row to File Processing
	$newfile = false;
	if (!file_exists($filename)) $newfile = true;
	$handle = fopen($filename,"ab");
	/*
	if ($newfile) {
		$line = array();
		$line[] = "Order number";
		$line[] = "Order date";
		$line[] = "Customer number";
		$line[] = "Freight";
		fputcsv($handle, $line);
	}
	*/
	foreach ($input as $line) {
		fputcsv($handle, $line);
	}
	fclose($handle);
	
	// set the DateTime of csv_exported in BoL_forms to now()
	$sql = "UPDATE BoL_forms SET csv_exported = NOW() WHERE ID = '".($bol-1000)."'";
	mysql_query($sql);
}

// Not used, but kept around in case we need it again
function soi_queue_complex($bol) {
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_soi.csv";
	if (!is_numeric($bol)) die("SOI Queue: BoL ID# is not numeric");
	
	$input = array(); // Input lines
	// 2-12-08 OPD
	// changed SQL query to pull the BoL create date instead of the PO create date
	// before: within the UNIX_TIMESTAMP() function was `c`.`ordered` [ i.e. the date the PO was placed ]
	// after: within the UNIX_TIMESTAMP() function is `d`.`createdate`, which is the create date of the BoL
	$sql = "SELECT `a`.`po`, `a`.`boxamt` AS `qty`, UNIX_TIMESTAMP(`d`.`createdate`) AS `date`, `b`.`partno`, `d`.`freight`, `b`.`box`, `b`.`price`  FROM `BoL_items` AS `a` INNER JOIN `snapshot_items` AS `b` ON `a`.`item` = `b`.`id` INNER JOIN `order_forms` AS `c` ON `a`.`po` = `c`.`ID` INNER JOIN `BoL_forms` AS `d` ON `a`.`bol_id` = `d`.`ID` WHERE `a`.`type` = 'bol' AND `bol_id` = '".($bol - 1000)."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($result = mysql_fetch_assoc($query)) {
		$price = $result['box'] ? $result['box'] : $result['price'];
		$line = array();
		$line[] = $result['po'] + 1000; // PO#
		$line[] = date("n/j/Y",$result['date']); // BoL create Date
		$line[] = 'PFD'; // Customer Name (Dealers = PFD, Trade = Individual)
		$line[] = $result['partno']; // Part No
		$line[] = '0'; // Item Warehouse (unused)
		$line[] = $result['qty']; // Qty Ordered
		$line[] = $price; // Unit price
		$line[] = $price * $result['qty']; // MAS90 CALC (Line Extension)
		if (!$freight) {
			$freight = $result['freight'];
			$line[] = $result['freight'];
		} else {
			$line[] = '0';
		}
		$input[] = $line;
	}
	
	if (!count($input)) die("Salesord_Queue Error: Found 0 Lines for BoL, does it exist?");
	
	
	// Add Row to File Processing
	$newfile = false;
	if (!file_exists($filename)) $newfile = true;
	$handle = fopen($filename,"ab");
	/*
	if ($newfile) {
		$line = array();
		$line[] = "Order number";
		$line[] = "Order date";
		$line[] = "Customer number";
		$line[] = "Item number";
		$line[] = "Item warehouse";
		$line[] = "Qty shipped";
		$line[] = "Unit price";
		$line[] = "Line extension";
		$line[] = "Freight";
		fputcsv($handle, $line);
	}
	*/
	foreach ($input as $line) {
		fputcsv($handle, $line);
	}
	fclose($handle);
	
	// set the DateTime of csv_exported in BoL_forms to now()
	$sql = "UPDATE BoL_forms SET csv_exported = NOW() WHERE ID = '".($bol-1000)."'";
	mysql_query($sql);
}

function soi_release() {
	// Copies Pending CSV and copies it to a final destination
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_soi.csv";
	$filename_new = $basedir."admin/exported_csvs/".date('Y-m-d')."_soi.csv";
	if (!file_exists($filename)) {
		setcookie('BoL_msg', "No rows queued currently.", 0);
		header("Location: csvexport.php");
	}
	rename($filename, $filename_new); // Rename pending to date
	return "../admin/exported_csvs/".date('Y-m-d')."_soi.csv"; // Return to allow front end to redirect properly
}