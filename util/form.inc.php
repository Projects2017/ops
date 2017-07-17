<?php

// Layout of tables:
// claims:
//   id - ID of form
//   name - Name of form (also related to table of claim_%name% i.e. claim_damage
//   layout - comma delimeted list of fields that are insertable (id, timestamp and client ip are restricted and cannot be inserted into or updated via form)
//   required - comma delimeted list of fields which are required (form will error out unpretty if SQL requires a field not on here) use this instead of 'NOT NULL'

// claim_name
// id - ID of claim (for this claim table)
// timestamp - Time claim submitted
// clientip - IP of client who submitted the claim
// username - User ID of Vendor who submitted claim=

// INF: replacement for loadcsv (not effecient, but works)
// first arg = form name
// second arg = field to order by (optional)
function formdata ($form, $orderby = 0) {
	$form = mysql_escape_string($form);
	$orderby = mysql_escape_string($orderby);
	// TODO: replace above with the real thing!
	
	$query = "SELECT layout FROM claims WHERE name = '" . $form . "'";
	$result = mysql_query($query);
    if (!mysql_num_rows($result))
		die("Form does not exist");
	$record = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);

	$record = explode(",", $record['layout']);
	// Go through each piece of the layout and make sure it looks good. Then Check to make sure orderby is valid.
	foreach ($record as $i)
		$i = trim($i);
	// Done in reverse order because we're pushing them onto the begining of the array
	if (!in_array("clientip", $record))
		array_unshift($record, "clientip");
	if (!in_array("timestamp", $record))
		array_unshift($record, "timestamp");
	if (!in_array("id", $record))
		array_unshift($record, "id");
	foreach ($record as $i)
		if ($i == $orderby)
			$ordered = 1;
	// Get actual form data
	$record = "`".implode("`, `", $record)."`";
	$query = "SELECT ". $record . " FROM `claim_" . $form . "`";
	if ($ordered)
		$query .= " ORDER BY `". $orderby ."` ASC";
	$result = mysql_query($query);
	//  Turn mysql_query into a full fledged array $record
	unset($record); // Reused variable... getting rid of the original so that it's blank
	$record = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
		$record[] = $row;
	mysql_free_result($result);
	return $record;
}

// Useful for redirecting for the error back to originating form
function redirect($incomplete){
	$incomplete = implode(",",$incomplete);
    header("Location: ".$_SERVER["HTTP_REFERER"] ."?incomplete=".$incomplete);
	// TESTING
	// echo("Location: ".$_SERVER["HTTP_REFERER"] ."incomplete=".$incomplete);
    exit();
}

// forminsert($form, $dataarray)
//  Returns 1 if succeeded
//  Returns 0 if failed
//  Redirects to previous form with ?incomplete= <-- a comma delimited list of missing items that are required
// $form = the name of the form
// $dataarray = array of data to insert... key should be column, and value should be what you want in the row.
function forminsert ($form, $dataarray) {
	$form = mysql_escape_string($form);
	// load data into a string with each element seperated by ','
	// TODO: simplify/optimize this with implode
	// OLD: foreach ($dataarray as $data)
	// OLD:	   $record .= "'" . mysql_escape_string($data) . "', ";
	// Remove last comma and surround statement with ()'s
	// OLD: $record = "(".substr($record,0,strlen($record)-1).")";
	// Get layout for table, and check existance
	$query = "SELECT layout, required FROM claims WHERE name = '" . $form . "'";
	$result = mysql_query($query);
    if (!mysql_num_rows($result))
		die("Form does not exist");
	$record = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	$required = $record['required'];
	$record = explode(",", $record['layout']);
	foreach ($record as $i)
		$i = trim($i);
	$required = explode(",", $required);
	foreach ($required as $i)
		$i = trim($i);
	// if timestamp, id or client ip are in our array, get rid of them, specifying them can screw up our whole scheme
	if (in_array("id",$record))
		unset($record['id']);
	if (in_array("timestamp",$record))
		unset($record['timestamp']);
	if (in_array("clientip",$record))
		unset($record['clientip']);
    
	// Check if all required parts are present
	$missing = array();
	foreach ($required as $i)
		if (!$dataarray[$i])
			$missing[] = $i;
	if ($missing)
		redirect($missing);
	unset($missing);
	// Trim out crap not in the database layout
	$data = array();
    foreach ($dataarray as $i => $x) {
		if (!in_array($i,$record))
		   break;
		$data[$i] = $x;
	}
	// Add Client IP
	$data['clientip'] = $_SERVER["REMOTE_ADDR"];
	// Start contstructing our SQL INSERT string
    foreach ($data as $i => $x) {
		$colstring .= "`" . mysql_escape_string($i) . "`, ";
		$valstring .= "'" . mysql_escape_string($x) . "', ";
	}
	// Remove space and , from last entry on above string
	$colstring = "(".substr($colstring,0,strlen($colstring)-2).")";
	$valstring = "(".substr($valstring,0,strlen($valstring)-2).")";
	
	// Finally the query
	$sql = "INSERT INTO `claim_".$form."` ".$colstring." VALUES".$valstring;
	if (mysql_query($sql)) {
		return 1;
	} else {
		// return 0;
	}
}
		
// All below is testing stuff 
// echo "<PRE>";
//include("../database.php");
//print_r(array(forminsert("damage",array("name" => "Will2", "vendor" => "Blue Corp.", "po" => "23232", "date" => "2004-12-15","item" => "Arm Chair","transport"=> "EGL", "carton" => "Stained Blue","description"=>"Big Bad things happened!"))));

//print_r(formdata("damage","po"));
//echo "</PRE>";
?>