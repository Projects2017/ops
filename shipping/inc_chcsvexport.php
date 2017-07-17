<?php
// CSV Export Functions for CH orders

function salesord_queue($po) {
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_chsalesord.csv";
	if (!is_numeric($po)) die("Salesord Queue: PO is not numeric");
	
	$input = array(); // Input lines
	
	$sql = "SELECT `a`.`ID`, UNIX_TIMESTAMP(`a`.`ordered`) AS `date`, `b`.`merchantpo` AS `chpo`, `a`.`shipto` AS `userid` FROM `order_forms` AS `a` INNER JOIN `ch_order` AS `b` ON `a`.`ID` = `b`.`po` WHERE `a`.`ID` = '".($po - 1000)."'";
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($result = mysql_fetch_assoc($query)) {
		$line = array();
		$line[] = $po; // PO#
		$line[] = date("n/j/Y",$result['date']); // PO Date
		$line[] = 'COSTCO'; // COSTCO always
		$line[] = "'".$result['chpo']; // Costco PO # -- adding single ' to prevent conversion to a number
		$sql2 = "SELECT `last_name`, `address`, `zip` FROM snapshot_users WHERE id = '{$result['userid']}'";
		$que = mysql_query($sql2);
		checkDBerror($sql2);
		$res2 = mysql_fetch_assoc($que);
		$line[] = $res2['last_name'];
		$line[] = $res2['address'];
		$line[] = $res2['zip'];
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
		$line[] = "PO#";
		$line[] = "Date";
		$line[] = "Customer number";
		$line[] = "Costco PO#";
		$line[] = "Name";
		$line[] = "Address";
		$line[] = "Zip Code";
		fputcsv($handle, $line);
	}
	*/
	foreach ($input as $line) {
		fputcsv($handle, $line);
	}
	fclose($handle);

	// set the DateTime of csv_exported in BoL_forms to now()
	$sql = "UPDATE order_forms SET chcsv_exported = NOW() WHERE ID = '".($po - 1000)."'";
	mysql_query($sql);
}

function salesord_release() {
	// Copies Pending CSV and copies it to a final destination
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_chsalesord.csv";
	$filename_new = $basedir."admin/exported_csvs/".date('Y-m-d')."_chsalesord.csv";
	if (!file_exists($filename)) {
		setcookie('BoL_msg', "No rows queued currently.", 0);
		header("Location: chcsvexport.php");
	}
	rename($filename, $filename_new); // Rename pending to date
	return "../admin/exported_csvs/".date('Y-m-d')."_chsalesord.csv"; // Return to allow front end to redirect properly
}

function soi_queue($bol) {
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_chsoi.csv";
	if (!is_numeric($bol)) die("SOI Queue: BoL ID# is not numeric");
	
	$input = array(); // Input lines
	// 2-12-08 OPD
	// changed SQL query to pull the BoL create date instead of the PO create date
	// before: within the UNIX_TIMESTAMP() function was `c`.`ordered` [ i.e. the date the PO was placed ]
	// after: within the UNIX_TIMESTAMP() function is `d`.`createdate`, which is the create date of the BoL
	$sql = "SELECT DISTINCT `a`.`po`, `a`.`user_id`, `ch`.`po` AS `chpo`, UNIX_TIMESTAMP(`d`.`createdate`) AS `date` FROM `BoL_items` AS `a` INNER JOIN `BoL_forms` AS `d`
	 ON `a`.`bol_id` = `d`.`ID`, `ch_order` AS `ch` ON `a`.`po` = `ch`.`po` WHERE `a`.`type` = 'bol' AND `bol_id` = '".($bol - 1000)."'";
	// TODO: There has to be a better way to do this outside of the distinct
	$query = mysql_query($sql);
	checkDBerror($sql);
	while ($result = mysql_fetch_assoc($query)) {
		$line = array();
		$line[] = $result['po'] + 1000; // PO#
		$line[] = date("n/j/Y",$result['date']); // BoL create Date
		$line[] = 'COSTCO'; // Always COSTCO
		$line[] = "'".$result['chpo']; // adding single ' to prevent conversion to a number
		$sql2 = "SELECT `last_name`, `address`, `zip` FROM snapshot_users WHERE id = '{$result['userid']}'";
		$que = mysql_query($sql2);
		checkDBerror($sql2);
		$res2 = mysql_fetch_assoc($que);
		$line[] = $res2['last_name'];
		$line[] = $res2['address'];
		$line[] = $res2['zip'];
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
	$sql = "UPDATE BoL_forms SET chcsv_exported = NOW() WHERE ID = '".($bol-1000)."'";
	mysql_query($sql);
}

function soi_release() {
	// Copies Pending CSV and copies it to a final destination
	global $basedir;
	$filename = $basedir."admin/exported_csvs/pending_chsoi.csv";
	$filename_new = $basedir."admin/exported_csvs/".date('Y-m-d')."_chsoi.csv";
	if (!file_exists($filename)) {
		setcookie('BoL_msg', "No rows queued currently.", 0);
		header("Location: chcsvexport.php");
	}
	rename($filename, $filename_new); // Rename pending to date
	return "../admin/exported_csvs/".date('Y-m-d')."_chsoi.csv"; // Return to allow front end to redirect properly
}