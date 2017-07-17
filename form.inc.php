<?php
/*********************************************************************
    Licensed for Jeff Hosking (PMD Furniture) by Radium Development
        (c) 2004 Radium Development with full rights granted to
                   Jeff Hosking (PMD Furniture)
 *********************************************************************/

// Layout of tables:
// claims:
//   id - ID of form
//   name - Name of form (also related to table of claim_%name% i.e. claim_damage
//   layout - comma delimeted list of fields that are insertable (id, timestamp and client ip are restricted and cannot be inserted into or updated via form)
//   required - comma delimeted list of fields which are required (form will error out unpretty if SQL requires a field not on here) use this instead of 'NOT NULL'
// Claim's upload dir... WITH trailing slash
$claims_debug = 0;
$claims_uploadstorageurl = "claim_upload/";
$claims_uploadstoragedir = $basedir."claim_upload/";
// **** IF YOU CHANGE THE CLOSED NUMBER, DO IT BELOW TOO
$claims_status = array ("1" => "NEW","2" => "Vendor", "3" => "Dealer","4" => "Dallas", "5" => "RSS", "6" => "CREDIT", "7" => "REPLACE", "8" => "DISCOUNT", "9" => "CLOSED", "10" => "REPL PART", "11" => "BACKORDER", "12" => "RSS CREDIT", "13" => "RSS DISC");
// claim_name
// id - ID of claim (for this claim table)
// timestamp - Time claim submitted
// clientip - IP of client who submitted the claim
// username - User ID of Vendor who submitted claim=

$claims_commentemail = array();


 /** order = 0 for descending, 1 for ascending
  ** column = column to sort by
  **/
function form_multisort(&$array, $column, $order = 1) {
	if ($order) {
		for ($i = 0; $i < sizeof($array); $i++)
			for ($c = 1; $c < sizeof($array); $c++)
				if ($array[$c][$column] < $array[$c-1][$column]) {
					$temp = $array[$c];
					$array[$c] = $array[$c-1];
					$array[$c-1] = $temp;
				}
	} else {
		for ($i = 0; $i < sizeof($array); $i++)
			for ($c = 1; $c < sizeof($array); $c++)
				if ($array[$c][$column] > $array[$c-1][$column]) {
					$temp = $array[$c];
					$array[$c] = $array[$c-1];
					$array[$c-1] = $temp;
				}
	}
} 
// INF: replacement for loadcsv (not effecient, but works)
// first arg = form name
// second arg = field to order by (optional)
function formdata ($form, $orderby = 0, $filterarray = 0) {
	$form = mysql_escape_string($form);
	$formprop = forminfo($form, 1);
	
	if (!$orderby) {
		$orderby = $formprop['default_order'];
	}

	if ($orderby[0] == "!") {
		$orderdir = "DESC";
		$orderby = ltrim($orderby, "!");
	} else {
		$orderdir = "ASC";
	}
	
	$orderby = mysql_escape_string($orderby);
	if ($filterarray == 0)
		$filterarray = array();
	
	// Remove all filters set to 0 (EXPLICITLY)
	// It's hack to remove it happily here rather than somewhere else
	// 0 is a string! otherwise blank matches! (which sucks if we're trying to filter by checkboxes)
	foreach ($filterarray as $i => $x) {
		if ($x == "0")
			unset($filterarray[$i]);
	}
	unset($i);
	unset($x);

	$record = forminfo($form);
	if (!$record)
		die("Form does not exist");
	$forminfo = $record;
	foreach ($record as $i => $x)
		$newrecord[] = $i;

	unset($record);
	$record = $newrecord;
	unset($newrecord);
	
	// $record = explode(",", $record['layout']);
	// Go through each piece of the layout and make sure it looks good. Then Check to make sure orderby is valid.
	//foreach ($record as $i)
	//	$i = trim($i);
	// Done in reverse order because we're pushing them onto the begining of the array
	if (!in_array("vendor_id", $record))
		array_unshift($record, "vendor_id");
	if (!in_array("user_id", $record))
		array_unshift($record, "user_id");
	if (!in_array("clientip", $record))
		array_unshift($record, "clientip");
	if (!in_array("timestamp", $record))
		array_unshift($record, "timestamp");
	if (!in_array("id", $record))
		array_unshift($record, "id");

	// Who's taking? An Admin?
	if (secure_is_admin()) {
		$admin = 1;
	} else {
		$userid = $GLOBALS['userid'];
	}

	if ((!$admin) && (secure_is_dealer()))
		$filterarray['user_id'] = $userid;
		// $query .= " WHERE user_id = '".$userid."'";
	if (!secure_is_dealer())
		$filterarray['vendor_id'] = $GLOBALS['vendorid'];
		// $query .= " WHERE vendor_id = '".$GLOBALS['vendorid']."'";
	
	// Assemble Where clause
	$where = " WHERE ";
	$x = 0;
	if ($filterarray['userteam'] == "*") {
		unset($filterarray['userteam']);
	}

        if ($formprop['default_dateperiod']) {
            $filterarray['timestamp'] = "`timestamp` > (NOW( ) - INTERVAL ".$formprop['default_dateperiod'].')';
        } else {
        	if (!empty($_REQUEST['daysback'])){
	        	# look back a certain amount for last email sent
	            $filterarray['timestamp'] = "`timestamp` < (NOW( ) - INTERVAL ".$_REQUEST['daysback']." DAY) AND (last_claim_email_sent IS NULL OR last_claim_email_sent < (NOW( ) - INTERVAL ".$_REQUEST['daysback']." DAY))";
	        } else {
	            unset($filterarray['timestamp']);
	        }
        }
	foreach ($filterarray as $k => $i) {
		$x++;
		if ((!in_array($k,$record))&&($k != "vendortype")&&($k != "userteam"))
			continue;
		if ($x != 1)
			$where .= " AND ";
		if ($i[0] == "+") {
			$type = 1;
			$i = ltrim($i, "+");
			$sign = ">=";
		} elseif ($i[0] == "-") {
			$type = 1;
			$i = ltrim($i, "-");
			$sign = "<=";
		} elseif ($i[0] == "!") {
			$type = 1;
			$i = ltrim($i, "!");
			$sign = "!=";
		} elseif ($i[0] == "|") {
			$type = 2;
			$cond = "OR";
			$i = ltrim($i, "|");
			$i = explode('|',$i,2);
			$sign = array();
			$sign[0] = '=';
			$sign[1] = '=';
		} else {
			$type = 1;
			$sign = "=";
		}
		// echo "$k -> $i<br>";
		if ($k == "vendortype") {
			if ($type == 1) {
				$where .= "`vendor`.`type` ".$sign." '".mysql_escape_string($i)."'";
			} elseif ($type == 2) {
				$where .= "(`vendor`.`type` ".$sign[0]." '".mysql_escape_string($i[0])."' ".$cond." `vendor`.`type` ".$sign[1]." '".mysql_escape_string($i[1])."')";
			}
			$vendortype = 1;
		} elseif ($k == "userteam") {
			if (empty($_REQUEST['daysback'])){
				if ($type == 1) {
					$where .= "`users`.`team` ".$sign." '".mysql_escape_string($i)."'";
				} elseif ($type == 2) {
					$where .= "(`users`.`team` ".$sign[0]." '".mysql_escape_string($i[0])."' ".$cond." `users`.`team` ".$sign[1]." '".mysql_escape_string($i[1])."')";
				}
			}
			$usertype = 1;
		} elseif ($k == 'timestamp') {
                        $where .= $i;
                } else {
			if ($type == 1) {
				$where .= "`claim_".$form."`.`".mysql_escape_string($k)."` ".$sign." '".mysql_escape_string($i)."'";
			} elseif ($type == 2) {
				$where .= "(`claim_".$form."`.`".mysql_escape_string($k)."` ".$sign[0]." '".mysql_escape_string($i[0])."' ".$cond." `claim_".$form."`.`".mysql_escape_string($k)."` ".$sign[1]." '".mysql_escape_string($i[1])."')";
			}
		}
	}
	if ($x == 0)
		$where = "";

	// Remove Items marked Invisible
	$unset = array();
	foreach ($record as $k => $i) {
		if ($i == $orderby)
			$ordered = 1;
		if ($i != 'upsincesms' && !$forminfo[$i]["visible"])
			$unset[] = $k;
	}
	foreach ($unset as $i) {
		unset($record[$i]);
	}

	// Assemble Initial Query
	$record = "`claim_".$form."`.`".implode("`, `claim_".$form."`.`", $record)."`";
	$query = "SELECT ". $record . " FROM `claim_" . $form . "`";
	if ($vendortype) {
		$query .= " INNER JOIN vendor ON vendor.id = `claim_".$form."`.vendor_id";
	}
	if ($usertype) {
		$query .= " INNER JOIN users ON users.id = `claim_".$form."`.user_id";
	}
	$where = str_replace("AND  AND","AND ",$where);

	$query .= $where;

	// Order Data
	if ($ordered)
		$query .= " ORDER BY `claim_".$form."`.`". $orderby ."` ".$orderdir;

	// Query DB
	// echo $query;
	$result = mysql_query($query." LIMIT 2000");
	
	//  Turn mysql_query into a full fledged array $record
	unset($record); // Reused variable... getting rid of the original so that it's blank
	$record = array(); 
	while ($row = @mysql_fetch_array($result, MYSQL_ASSOC)) 
		$record[] = $row;
	@mysql_free_result($result);

	return $record;
}

// getShippingData(po)
// returns carrier[], tracking[] & shipdate[] info on the specified PO
function form_getShippingData($po_id)
{
	$return = array();
	//$sql = "SELECT carrier, trackingnum AS tracking, shipdate FROM BoL_forms WHERE ID IN (SELECT DISTINCT bol_id FROM BoL_items WHERE po = $po_id)";
	$sql = "SELECT trackingnum AS tracking FROM BoL_forms WHERE ID IN (SELECT DISTINCT bol_id FROM BoL_items WHERE po = $po_id)";
	$que = mysql_query($sql);
	checkdberror($sql);
	while($res = mysql_fetch_assoc($que))
	{
		$return[] = $res;
	}
	foreach($return as $rec)
	{
		//$carrier[] = $rec['carrier'] ? $rec['carrier'] : '[None entered]';
		$tracking[]= $rec['tracking'];
		//$shipdate[]= $rec['shipdate'] ? $rec['shipdate'] : '[Unknown date]';
	}
	if($carrier || $tracking || $shipdate)
	{
		//return array('carrier' => $carrier, 'tracking' => $tracking, 'shipdate' => $shipdate);
		return $tracking;
	}
	else
	{
		return;
	}
}

// Useful for redirecting for the error back to originating form with a comma delimited 
function redirect($form, $incomplete){
	$incomplete = implode(",",$incomplete);
    header("Location: ".$_SERVER["HTTP_REFERER"] ."?form=".urlencode($form)."incomplete=".$incomplete);
	// TESTING
	// echo("Location: ".$_SERVER["HTTP_REFERER"] ."incomplete=".$incomplete);
    exit();
}

function formselect ($form, $id, $username = 0) {
	$form = mysql_escape_string($form);
	$id = mysql_escape_string($id);
	// TODO: replace above with the real thing!
	/* OLD STUFF!
	$query = "SELECT layout FROM claims WHERE name = '" . $form . "'";
	$result = mysql_query($query);
    if (!mysql_num_rows($result))
		die("Form does not exist");
	$record = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
    */
	$record = forminfo($form);

	if (!$record)
		die("Form does not exist");

	foreach ($record as $i => $x)
		$newrecord[] = $i;

	unset($record);
	$record = $newrecord;
	unset($newrecord);

	// $record = explode(",", $record['layout']);
	// Go through each piece of the layout and make sure it looks good. Then Check to make sure orderby is valid.
	foreach ($record as $i)
		$i = trim($i);
	// Done in reverse order because we're pushing them onto the begining of the array
	if (!in_array("vendor_id", $record))
		array_unshift($record, "vendor_id");
	if (!in_array("user_id", $record))
		array_unshift($record, "user_id");
	if (!in_array("clientip", $record))
		array_unshift($record, "clientip");
	if (!in_array("timestamp", $record))
		array_unshift($record, "timestamp");
	if (!in_array("id", $record))
		array_unshift($record, "id");

	// Who's talkin? an Admin?
	if (secure_is_admin()) {
		$admin = 1;
	} else {
		$userid = $GLOBALS['userid'];
	}
	$unset = array();
	$forminfo = forminfo($form);
	foreach ($record as $k => $i) {
		if (!$forminfo[$i]["visible"])
			$unset[] = $k;
	}

	foreach ($unset as $i)
		unset($record[$i]);

	// Get actual form data
	$record = "`".implode("`, `", $record)."`";
	$query = "SELECT ". $record . " FROM `claim_" . $form . "` WHERE id = '".$id."'";
	if ((!$admin) && (secure_is_dealer()))
		$query .= " AND user_id = '".$userid."'";
	elseif (!secure_is_dealer())
		$query .= " AND vendor_id = '".$GLOBALS['vendorid']."'";
	$result = mysql_query($query);
	//  Turn mysql_query into a full fledged array $record
	unset($record); // Reused variable... getting rid of the original so that it's blank
	$record = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	return $record;
}

// forminsert($form, $dataarray)
// Returns 0 if success
// Returns true if failed.
// returns array of array(missing => array of missing records, wrongtype => entries of wrong type)

//function forminsert ($form, $dataarray, $user_id = 0) {
//	print_r($form);
//	print_r($dataarray);
//	print_r($user_id);
//	return(1);
//}

function forminsert ($form, $dataarray, $user_id = 0) {
	$form = mysql_escape_string($form);
	// Initialize Variables
	$todo = array();
	// Form Info Retrieval
	$forminfo = forminfo($form);
	$pforminfo = forminfo($form,1);

	if (!$forminfo)
		die("Form does not exist");
	if (secure_is_dealer()) {
		if ((!secure_is_admin()) && (!$pforminfo["dealer_insertable"]))
			die("This form can only be inserted by admins");
		
		if (!secure_is_dealer())
			die("Only dealers can insert data");
	} elseif (secure_is_vendor()) {
		if (!$pforminfo['vendor_insertable'])
			die("This form cannot be inserted by vendors.");
		$dataarray['vendor_id'] = $GLOBALS['vendorid'];
	} else {
		die("Unconfigured permission access.");
	}

	// Take out fields we don't want really in there
	if ($forminfo['id'])
		unset($forminfo['id']);
	if ($forminfo['timestamp'])
		unset($forminfo['timestamp']);
	if ($forminfo['clientip'])
		unset($forminfo['clientip']);
	if (!(secure_is_admin() || secure_is_vendor())) {
		if ($forminfo['user_id'])
			unset($forminfo['user_id']);
	}
	if (!secure_is_admin()) {
		if ($forminfo['status'])
			unset($forminfo['status']);
	}

    $gooddata = array();
    $missing = array();
    $wrongtype = array();
    $tolong = array();
    
	// Check if all required parts are present and that they are of the right type
	foreach ($forminfo as $i) {
		if ($i['insert']) {
			// Data Type Checking
			$gooddata[$i['id']] = $dataarray[$i['id']];
			if ($i['datatype'] == "date") {
				if ($gooddata[$i['id']]) {
					$gooddata[$i['id']] = strtotime($gooddata[$i['id']]);
					if (!$gooddata[$i['id']])
						$wrongtype[] = $i['id'];
					$gooddata[$i['id']] = date("Y-m-d", $gooddata[$i['id']]);
				}
			}
			if ($i['datatype'] == "upload") {
				$todo[] = $_FILES['data_'.$i['id']];
				if (file_exists($_FILES['data_'.$i['id']]['tmp_name']))
					$gooddata[$i['id']] = 1;
				else
					$gooddata[$i['id']] = 0;
			}
			if ($i['datatype'] == "number") {
				if ($gooddata[$i['id']])
					if (!is_numeric($gooddata[$i['id']]))
						$wrongtype[] = $i['id'];
			}
			if (($i['required']) && (!$gooddata[$i['id']]))
				$missing[] = $i['id'];
			if (($i['limit'] != -1)&&(strlen($gooddata[$i['id']]) > $i['limit']))
				$tolong[] = $i['id'];
		}
	}
	// If crap is missing, return with what it was!
	if (($missing) || ($wrongtype) || ($tolong))
		return (array("missing" => $missing, "wrongtype" => $wrongtype, "tolong" => $tolong));
	
	unset($missing);
	unset($wrongtype);
	// ==== CHANGE VAR NAME $gooddata IS NOW $data ====
	$data = $gooddata;
	unset($gooddata);
	// Add Client IP and client user_id
	$data['clientip'] = $_SERVER["REMOTE_ADDR"];
	if (!array_key_exists("user_id",$data))
		$data['user_id'] = $GLOBALS['userid'];

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
	$success = !mysql_query($sql);
	if (!$success) {
		$new_id = mysql_insert_id();
		global $global_last_insert_id;
		$global_last_insert_id = $new_id;
		foreach ($todo as $value)
			formfileadd($form, $new_id, $value['name'], $value['tmp_name']);
		// DEBUG
		// echo($sql);
		formaddcomment($form,$new_id,"Submitted");
		return 0;
	} else
		return $sql;

}


// formupdate($form, $what)
//   Updates a previously inserted form entry.
//   =!= Will not allow required fields to be blank =!=
//   Cannot update internal fields (id, timestamp, clientip)
// $form - Form name
// $id - ID of field being updated
// $dataarray - array of entries to be updated (key = column name)
function formupdate($form, $id, $dataarray) {
	if (!(formselect($form, $id)))
		return 0;
	$form = mysql_escape_string($form);
	if (!$id)
		die("No Row to Edit!");
	// Form Info Retrieval
	$forminfo = forminfo($form);
        $formprop = forminfo($form, true);

	if (!$forminfo)
		die("Form does not exist");
    $gooddata = array();
    $wrongtype = array();
    $missing = array();
	// var_dump(isset($dataarray["factory_confirm"]));
	// Check if all required parts are present and that they are of the right type
	foreach ($forminfo as $i) {
		// DataType Checking
		// print_r(""); echo "<BR>";
		if ((array_key_exists($i['id'],$dataarray))&&($i['edit'])) {
			$gooddata[$i['id']] = $dataarray[$i['id']];
			//echo $i['id']."-".$gooddata[$i['id']]."<BR>";
			if ($i['datatype'] == "date") {
				if ($gooddata[$i['id']]) {
					$temp = strtotime($gooddata[$i['id']]);
					if (!$temp)
						$wrongtype[] = $i['id'];
					else
						$gooddata[$i['id']] = date("Y-m-d", $temp);
				}
			}
			if ($i['datatype'] == "upload") {
				// OLD: Skip altogether.
				// OLD: unset($gooddata[$i['id']]);$_FILES['data_'.$i['id']];
				if (file_exists($_FILES['data_'.$i['id']]['tmp_name']))
					$gooddata[$i['id']] = 1;
				else
					$gooddata[$i['id']] = 0;
				formfileadd($form, $id, $_FILES['data_'.$i['id']]['name'], $_FILES['data_'.$i['id']]['tmp_name']);
			}
			if ($i['datatype'] == "number") {
				if (!is_numeric($gooddata[$i['id']]))
					$wrongtype[] = $i['id'];
			}
			if (($i['limit'] != -1)&&(strlen($gooddata[$i['id']]) > $i['limit']))
				$tolong[] = $i['id'];
		}
	}
	// If crap is missing, return with what it was!
	if (($missing) || ($wrongtype) || ($tolong))
		return (array("missing" => $missing, "wrongtype" => $wrongtype, "tolong" => $tolong));
	
	unset($missing);
	unset($wrongtype);
	$data = $gooddata;
	unset($gooddata);

        if ($formprop['sms'] == '1') {
            foreach ($data as $key => $val) {
                if ($forminfo[$key]['triggersms']) {
                    $data['upsincesms'] = 1;
                }
            }
        }

	// Build Query
	foreach ($data as $key => $value) {
		if ($forminfo[$key]['logedit']) formaddcomment($form, $id, "Setting ".$forminfo[$key]['nicename']." to ".$value);
		$setstring .= "`".mysql_escape_string($key)."` = '".mysql_escape_string($value)."',";
	}
	$id = mysql_escape_string($id);
	// Strip last comma off the end and add parenthesis around it.
	$setstring = substr($setstring,0,strlen($setstring)-1);
	
	$sql = "UPDATE `claim_".$form."` SET ". $setstring . " WHERE `id` = '".$id."'";
	// echo $sql;
	if (mysql_query($sql)) {
		$succ = 0;
	} else {
		$succ = 1;
	}
        if ($formprop['sms'] == '1' && $data['upsincesms'] == 1) {
            formsendclaimsms($form, $id);
        }
	if (!$succ) {
	    // If CLOSED delete the data
		//print_r($GLOBALS['claims_status'][$data['status']]);
		if ($GLOBALS['claims_status'][$data['status']] == "CLOSED")
	    	exec("/bin/rm -r ".$GLOBALS['claims_uploadstoragedir'].$form."/".$id);
		return 0;
	} else {
		return 1;
	}
}

function formsmsformat($formid, $id) {
    $form = forminfo($formid);
    $data = formselect($formid, $id);
    $output = '';
    foreach ($form as $key => $field) {
        // Check if we should be doing this to this field
        if ($field['onsms'] != '1')
            continue;
        // Check if it is an unsupported field type.
        if ($form[$key]['datatype'] == "upload")
            continue;
        // Add the output together!
        $output .= $field['nicename'].': ';
        if ($key == 'timestamp') {
            $output .= timestamp2date($data[$key]);
        } elseif ($form[$key]['datatype'] == "checkbox") {
            if ($data[$key] == 'on')
                $output .= 'X';
        } elseif ($key == "user_id") {
            $output .= db_user_getuserinfo($$data[$key], "last_name");
        } elseif ($key == "vendor_id") {
            $output .= db_vendor_getinfo($data[$key], "name");
        } elseif ($key == "status") {
            $output .= $claims_status[$x];
        } else {
            $output .= $data[$key];
        }
        $output .= "\n";
    }
    return $output;
}

function formsendclaimsms($form, $id, $message = null) {
    if (!$message)
        $message = formsmsformat($form, $id);
    require_once('include/sms.php');
    $data = formselect($form, $id);
    $dealer = db_user_getuserinfo($data['user_id']);
    $subject = "RSS SMS Notification";
    $emails = array();
    if ($dealer['email'])
        $emails[] = $dealer['email'];
    if ($dealer['email2'])
        $emails[] = $dealer['email2'];
    if ($dealer['email3'])
        $emails[] = $dealer['email3'];
    formemail($form, $id, $emails);
    $number = str_replace("-","",$dealer['cell_phone']);
    if (sms_send($number,$subject,$message,'sms@retailservicesystems.com', $dealer['cell_provider'])) {
        // Update claimsdb with the fact that it's sent.

        // Mark sent
    } else {
        // Don't mark sent sms_error() could get us the error if we wanted.
    }
    $sql = "UPDATE `claim_".mysql_escape_string($form)."` SET `upsincesms` = '2' WHERE `id` = '".mysql_escape_string($id)."'";
    mysql_query($sql);
    checkDBerror($sql);
}

// List claim forms available
function formlistforms () {
	if ($GLOBALS['claims_debug']) {
		$table = "claimstest";
	} else {
		$table = "claims";
	}
	$query = "SELECT `name` FROM `".$table."`";
	$result = mysql_query($query);
	checkDBError();
	if (!mysql_num_rows($result)) {
		return array();
	} else {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
			$record[] = $row['name'];
		return $record;
	}
}

// Retrieve form info
// forminfo($form)
// returns specialized array ["layout" => [form,insertable,columns], "required" => [form,required,columnns]]
// May change in future, allow for extra array entries
// MUCH simpler than the last version
// Caches, so if called multiple times in the same page, it doesn't eat up MySQL resources 
//    with the same query over and over
function forminfo ($form, $prop = 0) {
	static $pregen;
	
	if ($pregen[$form][$prop])
		return $pregen[$form][$prop];
	// Get layout for table, and check existance
	$form = mysql_escape_string($form);
	if ($prop) {
		if ($GLOBALS['claims_debug']) {
			$table = "claimstest";
		} else {
			$table = "claims";
		}
		$query = "SELECT * FROM `".$table."` WHERE `name` = '" . $form . "'";
		$result = mysql_query($query);
		checkDBError();
		if (!mysql_num_rows($result))
			return 0;
		$record = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		$return = $record;
	} else {
		$query = "SELECT * FROM claimscolumns WHERE form = '" . $form . "' order by `order`";
		$result = mysql_query($query);
		checkDBError($sql);
		if (!mysql_num_rows($result))
			return 0;
		// Perpetuate into an array that looks identical to former forminfo outputs
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
			$return[$line['id']] = $line;
		if (!$return['vendor_id'])
			$return = array( "vendor_id" => array( "id" => "vendor_id", "nicename" => "Vendor", "on_summary" => "1", "insert" => "1", "edit" => "1", "visible" => "5", "limit" => -1)) + $return;
		if (!$return['user_id'])
			$return = array( "user_id" => array( "id" => "user_id", "nicename" => "Dealer", "on_summary" => "1", "insert" => "1", "edit" => "0", "visible" => "1", "order" => "4", "limit" => -1)) + $return;
		if (!$return['clientip'])
			$return = array( "clientip" => array( "id" => "clientip", "nicename" => "Client IP", "on_summary" => "0", "insert" => "0", "edit" => "0", "visible" => "1", "order" => "3", "limit" => -1)) + $return;
		if (!$return['upsincesms'])
			$return = array( "upsincesms" => array( "id" => "upsincesms", "namename" => "Updated Since SMS", "on_summary" => "0", "insert" => "0", "edit" => "0", "visible" => "0", "order" => "30", "limit" => -1)) + $return;
		if (!$return['status'])
			$return = array( "status" => array( "id" => "status", "nicename" => "Status", "on_summary" => "1", "insert" => "0", "edit" => "1", "visible" => "1", "order" => "3", "limit" => -1)) + $return;
		if (!$return['timestamp'])
			$return = array( "timestamp" => array( "id" => "timestamp", "nicename" => "Time Stamp", "on_summary" => "1", "insert" => "0", "edit" => "0", "visible" => "1", "order" => "2", "limit" => -1)) + $return;
		if (!$return['id'])
			$return = array( "id" => array( "id" => "id", "nicename" => "Claim ID#", "on_po" => "1", "on_summary" => "1", "insert" => "0", "edit" => "0", "visible" => "1", "order" => "1", "limit" => -1)) + $return;
		// $return = form_multisort(&$return, "order");
		form_multisort($return, "order");
	}
	$pregen[$form][$prop] = $return;
	return($return);
}

function formdelete($form, $id) {
	if (!(formselect($form, $id)))
		return 0;
	if (!secure_is_dealer())
		return 0;
	$form = mysql_escape_string($form);
	$id = mysql_escape_string($id);
	if (!is_numeric($id))
		die("Invalid ID");
	
	$sql = "DELETE FROM `claim_" . $form. "` WHERE id = '".$id."'";
	mysql_query($sql);
	
	// Delete Associated Files
	exec("/bin/rm -r ".$GLOBALS['claims_uploadstoragedir'].$form."/".$id);

    // Delete All Comments
	$sql = "DELETE FROM `claimscomment` WHERE row = '".$id."' AND form = '".$form."'";
	mysql_query($sql);

	return 1;
}

// Adds a comment about the particular row
function formaddcomment($form,$id,$comment, $dealer_only = 0) {
	global $claims_commentemail;
	// If they can't view the form, they can't comment on it obviously
	$formselect = formselect($form, $id);
	if (!($formselect))
		return 0;
	if (secure_is_dealer()) {
		$user_id = $GLOBALS['userid'];
		if ((!secure_is_admin()) || $dealer_only) {
			// If not admin or want dealer_only... we set it so that only PMD people can see it
			$user_type = "O";
		} else {
			$user_type = "D";
		}
	} else {
		$user_id = $GLOBALS['vendorid'];
		$user_type = "V";
	}

	$sql = "INSERT INTO claimscomment (user_id, user_type, comment, row, form) VALUES ('".mysql_escape_string($user_id)."','".mysql_escape_string($user_type)."','".mysql_escape_string($comment)."','".mysql_escape_string($id)."','".mysql_escape_string($form)."')";
	mysql_query($sql);
	
	$forminfo = forminfo($form,1);
	if ($forminfo['changeemail'] && !$claims_commentemail[$forminfo['changeemail']]) {
		$claims_commentemail[$forminfo['changeemail']] = true;
		formemaillink($form, $id, $forminfo['changeemail'], "Claim has been commented on.", 0);
	}
}

function formdelcomment($form,$id) {
	if (!secure_is_admin())
		return false;
	$sql = "DELETE FROM claimscomment WHERE id = '".mysql_escape_string($id)."' LIMIT 1";
	mysql_query($sql);
	return true;
}

function ext2mime($ext) {
	$ext = strtolower($ext);
	switch ($ext) {
		case "jpg":
		case "jpeg":
		case "jpe":
			$mime = "image/jpeg";
			
			break;
		case "gif":
			$mime = "image/gif";
			break;
		case "tif":
		case "tiff":
			$mime = "image/tiff";
			break;
		case "png":
			$mime = "image/x-png";
			break;
		case "bmp":
			$mime = "image/bmp";
			break;
		default:
			$mime = "applicaton/octet-stream";
	}
	return $mime;
}

function formcomments($form, $id) {
	// If they can't view it, they can't see the comments
	$formselect = formselect($form, $id);
	$forminfo = forminfo($form);
	if (!($formselect))
		return 0;
	$query = "SELECT * FROM claimscomment WHERE row = '".$id."' AND form = '" . $form . "' ORDER BY timestamp ASC";
	$result = mysql_query($query);
	checkDBError();
	if (!mysql_num_rows($result))
		return 0;
	// Perpetuate into an array that looks identical to former forminfo outputs
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if (!(!secure_is_dealer() && ($line['user_type'] == "O")))
			$return[] = $line;
	}
	return $return;
}

function formclaimfiles($form, $row) {
	return formfilelist($form, $row);
}

function formfilelist($form, $row) {
	if (!file_exists($GLOBALS['claims_uploadstoragedir']."/".$form."/".$row))
		return(array());
	$files = array();
	if ($handle = opendir($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/")) {
        while (false  !== ($file = readdir($handle))) {
            if ($file  != "." && $file  != "..") {
                $files[] = $GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$file;
            }
        }
        closedir($handle);
    }
	return $files;
}

function formfilesplit($form, $row, $newrow) {
	if (!file_exists($GLOBALS['claims_uploadstoragedir']."/".$form."/".$row))
	return(false);
	if (!file_exists($GLOBALS['claims_uploadstoragedir']."/".$form."/".$newrow))
		mkdir($GLOBALS['claims_uploadstoragedir']."/".$form."/".$newrow);
	if ($handle = opendir($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/")) {
        while (false  !== ($file = readdir($handle))) {
            if ($file  != "." && $file  != "..") {
				copy($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$file,$GLOBALS['claims_uploadstoragedir'].$form."/".$newrow."/".$file);
            }
        }
        closedir($handle);
    }
	return true;
}

function formfileadd($form, $row, $filename, $tmp) {
		if (!file_exists($GLOBALS['claims_uploadstoragedir']))
			mkdir($GLOBALS['claims_uploadstoragedir'],0755);
		if (!file_exists($GLOBALS['claims_uploadstoragedir'].$form))
			mkdir($GLOBALS['claims_uploadstoragedir'].$form,0755);
		if (!file_exists($GLOBALS['claims_uploadstoragedir'].$form."/".$row))
			mkdir($GLOBALS['claims_uploadstoragedir'].$form."/".$row,0755);
		$origfilename = $filename;
		$i = 1; // initialize counter
		while (file_exists($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$filename)) {
			$filename = $origfilename.$i;
			$i++;
		}
		move_uploaded_file($tmp, $GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$filename);
}

function formfiledel($form, $row, $filename) {
	if (!(formselect($form, $row)))
		return 0;
	if (!file_exists($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$filename))
		return 1;
	$fileprop = pathinfo($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$filename);
	if ($fileprop["dirname"] != $GLOBALS['claims_uploadstoragedir'].$form."/".$row)
	    return 0;
	$return = unlink($GLOBALS['claims_uploadstoragedir'].$form."/".$row."/".$filename);
	// Don't want to record deletions PER Gary Davis
	//if ($return) 
	//	formaddcomment($form,$row,"Deleted File: ".$filename);
	return $return;
}

function formemaillink($form, $row, $email, $message = "", $add_comment = 1) {
	$max_length = 20;
	$files = formclaimfiles($form, $row);
	$date_time = date('Y-m-d H:i:s');
    $mime_delimiter = md5(time());
	$fields = formselect ($form, $row);
	$forminfo = forminfo($form);
	$pform = forminfo($form,1);
	$subject = $pform['subject'];
	$comments = formcomments($form, $row);
	$regs = array();
	
	if (!$comments) {
		$comments = array();
	}

	// Determine subject
	while (ereg("(%[_a-zA-Z0-9]+\.[_a-zA-Z0-9]+%)",$subject, $match)) {
		if (ereg ("^%([_a-zA-Z0-9]+)\.([_a-zA-Z0-9]+)%$",$match[1],$matchreg)) {
			if ($matchreg[1] == "row") {
				$subject = str_replace($match,$fields[$matchreg[2]],$subject);
			} elseif ($matchreg[1] == "form") {
				$subject = str_replace($match,$pform[$matchreg[2]],$subject);
			}
		}
		unset($match);
		unset($matchreg);
	};
	foreach ($regs as $match) {
		$matchreg = array();
		if (ereg ("^%(.+)\.(.+)%.*$",$match,$matchreg)) {
			if ($matchreg[1] == "row") {
				if ($forminfo[$matchreg[2]] == "vendor_id") {
					$subject = str_replace($match,db_vendor_getinfo($fields[$matchreg[2]], "name"));
				} elseif ($forminfo[$matchreg[2]] == "user_id") {
					$subject = str_replace($match,db_user_getuserinfo($fields[$matchreg[2]], "last_name"));
				} else {
					$subject = str_replace($match,$fields[$matchreg[2]]);
				}
			} elseif ($matchreg[1] == "form") {
				$subject = str_replace($match,$pform[$matchreg[2]]);
			}
		}
	}
	unset($match);
	unset($regs);
	unset($matchreg);
	if (!$subject) {
		$subject = "RSS Claims Form";
	}
	unset($fill);
	unset($max_length);
	unset($k);
	unset($v);
	unset($out);
	$formname = $pform['nicename'];
	$mail = <<<EOF
$message

Please View The Claim At:
https://login.retailservicesystems.com/form.php?action=view&form=$form&viewid=$row
EOF;
	sendmail($email, $subject, $mail,
    "From: ".$pform['fromemail']."\n");
	// echo "To: ".$email."\nFrom: ".$pform['fromemail']."\nSubject: ".$subject."\n\n".$mail;
	if ($add_comment)
		formaddcomment($form,$row,"Form Link E-Mailed");
}

// E-Mail may be an array of e-mails or a e-mail address string
function formemail($form, $row, $email) {
	$max_length = 20;
	$files = formclaimfiles($form, $row);
	$date_time = date('Y-m-d H:i:s');
    $mime_delimiter = md5(time());
	$fields = formselect ($form, $row);
	$forminfo = forminfo($form);
	$pform = forminfo($form,1);
	$subject = $pform['subject'];
	$comments = formcomments($form, $row);
	$regs = array();
	
	if (!$comments) {
		$comments = array();
	}

	// Determine subject
	while (ereg("(%[_a-zA-Z0-9]+\.[_a-zA-Z0-9]+%)",$subject, $match)) {
		if (ereg ("^%([_a-zA-Z0-9]+)\.([_a-zA-Z0-9]+)%$",$match[1],$matchreg)) {
			if ($matchreg[1] == "row") {
				$subject = str_replace($match,$fields[$matchreg[2]],$subject);
			} elseif ($matchreg[1] == "form") {
				$subject = str_replace($match,$pform[$matchreg[2]],$subject);
			}
		}
		unset($match);
		unset($matchreg);
	};
	foreach ($regs as $match) {
		$matchreg = array();
		if (ereg ("^%(.+)\.(.+)%.*$",$match,$matchreg)) {
			if ($matchreg[1] == "row") {
				if ($forminfo[$matchreg[2]] == "vendor_id") {
					$subject = str_replace($match,db_vendor_getinfo($fields[$matchreg[2]], "name"));
				} elseif ($forminfo[$matchreg[2]] == "user_id") {
					$subject = str_replace($match,db_user_getuserinfo($fields[$matchreg[2]], "last_name"));
				} else {
					$subject = str_replace($match,$fields[$matchreg[2]]);
				}
			} elseif ($matchreg[1] == "form") {
				$subject = str_replace($match,$pform[$matchreg[2]]);
			}
		}
	}
	unset($match);
	unset($regs);
	unset($matchreg);
	if (!$subject) {
		$subject = "RSS Claims Form";
	}

	foreach ($fields as $k=>$v){
		if ($forminfo[$k]['datatype'] != "upload") {
			$name = $forminfo[$k]['nicename'];
			$len_diff = $max_length - strlen($name);
			if ($len_diff > 0)
			    $fill = str_repeat('.', $len_diff);
			else
			    $fill = '';
			if ($k == "user_id")
				$v = db_user_getuserinfo($v, "last_name");
			elseif ($k == "vendor_id")
				$v = db_vendor_getinfo($v, "name");
			elseif ($k == "status")
				$v = $claims_status[$v];
			elseif ($k == "timestamp")
				$v = timestamp2datetime($v);
			if (!$forminfo[$k]['visible']) {
				// Do Nothing!
			} elseif ($forminfo[$k]['datatype'] == "checkbox")
				$v == "on" ? $out .= $name."$fill...: X\n" : $out .= $name."$fill...: \n";
			elseif ($forminfo[$k]['multiline'])
				$out .= "=".$name."=\n$v\n";
			else
				$out .= $name."$fill...: $v\n";
		}
    }
	$fields = $out;
	unset($out);
	unset($fill);
	unset($max_length);
	unset($k);
	unset($v);
	foreach ($comments as $value) {
			if ($value["user_type"] == "D") {
				$userout = db_user_getuserinfo($value['user_id'], "last_name");
			} elseif ($value["user_type"] == "V") {
				$userout = db_vendor_getinfo($value['user_id'], "name");
			}
			$out .= timestamp2datetime($value['timestamp'])."-".$userout.": ".$value['comment']."\n";
			unset($userout);
	}
	$comments = $out;
	unset($out);
	$formname = $pform['nicename'];
	$mail = <<<EOF
This is a MIME-encapsulated message

--$mime_delimiter
Content-type: text/plain
Content-Transfer-Encoding: 8bit

$formname info submitted:
$fields
-------------------
  Record Comments
-------------------
$comments
EOF;
	// Read Files into Mail
    if (count($files)){
        foreach ($files as $file){
            $file_name = pathinfo($file);
            $file_cnt = "";

            $f=@fopen($file, "rb");
            if (!$f)
                die("Error: Unable to locate file for attachment");
            while($f && !feof($f))
                $file_cnt .= fread($f, 4096);
            fclose($f);
            $file_type = ext2mime($file_name["extension"]);
            $mail .= "\n--$mime_delimiter\n";
            $mail .= "Content-type: $file_type\n";
            $mail .= "Content-Disposition: attachment; filename=\"".$file_name["basename"]."\"\n";
            $mail .= "Content-Transfer-Encoding: base64\n\n";
            $mail .= chunk_split(base64_encode($file_cnt));
        }
    }
    $mail .= "\n--$mime_delimiter--";
    if (!is_array($email)) {
    	$email = array($email);
    }
    foreach ($email as $emal) {
		sendmail($emal, $subject, $mail,
    		"Mime-Version: 1.0\nFrom: ".$pform['fromemail']."\nContent-Type: multipart/mixed;\n boundary=\"$mime_delimiter\"\nContent-Disposition: inline");
    }
	// echo "To: ".$email."\nFrom: ".$pform['fromemail']."\nSubject: ".$subject."\n\n".$mail;
	formaddcomment($form,$row,"Form Copy E-Mailed");
}

function timestamp2datetime($x) {
	// ereg("^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})", $x, $x);
	// $x = $x[2]. "/".$x[3]."/".$x[1]." ".$x[4].":".$x[5];
	// return $x;
	return date('m/d/Y h:ia',strtotime($x));
}

function timestamp2date($x) {
	return date('m/d/Y',strtotime($x));
}

// function formmassupdate($form, $array)
// array contains arrays of what to update with the id being the thing you're updating.
// NOTES:
// function formupdate($form, $id, $dataarray)
function formmassupdate($form, $array) {
	foreach ($array as $id => $data) {
		if (!is_array($data))
			continue;
		if (count($data) == 0)
			continue;
		formupdate($form, $id, $data);
		$comment = "Mass Update: ";
		if (count($data) <= 3) {
			foreach($data as $key => $value)
				$comment .= $key."=".$value." ";
			formaddcomment($form, $id, $comment);
		} else {
			formaddcomment($form, $id, "Mass update performed");
		}
	}
}

// Returns an array of tasks that are open/assigned to you
// array( "dbname" => array("open" => 5, "own" => 2), "dbname2" => array("open" => 5, "own" => 2))
// is an example of what it returns.
// If admin, it returns false... because the query would take too long.
// If dealer, it only searches your tasks
// If vendor, it only searches your tasks
function formsummaries() {
	$team = $GLOBALS['dealerteam'];
	$summaries = array();
	if (secure_is_admin()) {
		$dealer = 2;
	} elseif (secure_is_dealer()) {
		$dealer = 1;
	} else { // Must be a vendor
		$dealer = 0;
	}
	// SELECT WHERE status <= 5 
	$formlist = formlistforms();
	foreach ($formlist as $x) {
		$forminfo = forminfo($x, 1);
		if (!$forminfo['sum']) {
			continue; // Skip this db
		}
		$summaries[$x] = array();
		
		// Get the number of open claims
		$sql = 'SELECT count(*) FROM claim_'.$x.' WHERE status <= 5'; // Only open claims
		if ($dealer == 2) {
			$sql = 'SELECT count(*) FROM claim_'.$x.' INNER JOIN users ON claim_'.$x.'.user_id = users.ID WHERE';
			$sql .= ' claim_'.$x.'.status <= 5';
			if ($team != '*')
				$sql .= ' AND users.team = '."'".$team."'";
		} elseif ($dealer == 1) {
			$sql .= ' AND user_id = '.$GLOBALS['userid'];
		} else {
			$sql .= ' AND vendor_id = '.$GLOBALS['vendorid'];
		}
		$result = mysql_query($sql);
		$row = mysql_fetch_row($result);
		$summaries[$x]['open'] = $row[0];
		mysql_free_result($result); // Conserve Memory... this is a loop
		unset($result);
		unset($row);
		unset($sql); // Done cleaning out memory
		
		// Get the number of claims assigned to New or PMD/Dealer/Vendor
		$sql = 'SELECT count(*) FROM claim_'.$x;
		if ($dealer == 2) {
			$sql .= ' INNER JOIN users ON claim_'.$x.'.user_id = users.ID WHERE';
			$sql .= ' (claim_'.$x.'.status = 1';
			$sql .= ' OR claim_'.$x.'.status = 5)';
			if ($team != '*')
				$sql .= ' AND users.team = '."'".$team."'";
		} elseif ($dealer == 1) {
			$sql .= ' WHERE status = 3 AND user_id = '.$GLOBALS['userid'];
		} else {
			$sql .= ' WHERE status = 2 AND vendor_id = '.$GLOBALS['vendorid'];
		}
		$result = mysql_query($sql);
		$row = mysql_fetch_row($result);
		$summaries[$x]['own'] = $row[0];
		mysql_free_result($result); // Clean out memory for this part
		unset($result);
		unset($row);
		unset($sql); // Done cleaning out memory
		// Clean out full loop memory.
		unset($forminfo);
	}
	return $summaries;
}

function formsplit($form, $id) {
	$finfo = forminfo($form, 1);
	if (!$finfo) return false;
	if (!is_numeric($id)) return false;
	if (!$finfo['cansplit']) return false;
	if (!(secure_is_vendor()||secure_is_admin())) return false;

	$sql = "SELECT * FROM `claim_".$form."` WHERE `id` = '".$id."'";
	$result = mysql_query($sql);
	CheckDBError($sql);
	$row = mysql_fetch_assoc($result);
	mysql_free_result($result);
	unset($row['id']); // Remove ID
	$clear = explode(',',$finfo['splitclear']);
	foreach($clear as $cleared) {
		unset($row[$cleared]);
	}

	$keys = array_keys($row);
	$keys = '(`'.implode('`,`', $keys).'`)';
	$row = "('".implode("','", $row)."')";
	$sql = "INSERT INTO `claim_".$form."` ".$keys." VALUES ".$row;
	mysql_query($sql);
	checkdberror($sql);
	$newid = mysql_insert_id();
	
	$sql = "SELECT * FROM `claimscomment` WHERE `row` = '".$id."' AND `form` = '".$form."'";
	$result = mysql_query($sql);
	checkdberror($sql);
	while($row = mysql_fetch_assoc($result)) {
		$row['row'] = $newid;
		unset ($row['id']);
		$keys = array_keys($row);
		$keys = '(`'.implode('`,`', $keys).'`)';
		$row = "('".implode("','", $row)."')";
		$sql = "INSERT INTO `claimscomment` ".$keys." VALUES ".$row;
		mysql_query($sql);
		checkdberror($sql);
	}
	formfilesplit($form, $id, $newid);
	$comment = "Split Record into ID# ".$newid;
	formaddcomment($form,$id,$comment);
	$comment = "Split Record from ID# ".$id;
	formaddcomment($form,$newid,$comment);
	return $newid;
}

function form_item_js_array($outputname, $array)
{
	// exports a form item PHP array into a JavaScript array definition
	// returns as a string for eventual output
	$output = "var $outputname = new Array();\n";
	// $val will be Array(item, set, matt, qty)
	
	foreach($array as $key => $val)
	{
		$output .= "if(!isset($outputname".'[\''.$val['item']."'])) $outputname".'[\''.$val['item'].'\'] = new Object()'.";\n";
		$output .= "$outputname".'[\''.$val['item'].'\'][\''.$val['desc'].'\'] = new Object()'.";\n";
		$output .= "$outputname".'[\''.$val['item'].'\'][\''.$val['desc'].'\'][\'item\'] = \''.$val['item']."';\n";
		$output .= "$outputname".'[\''.$val['item'].'\'][\''.$val['desc'].'\'][\'desc\'] = \''.$val['desc']."';\n";
		$output .= "$outputname".'[\''.$val['item'].'\'][\''.$val['desc'].'\'][\'set\'] = '.$val['set'].";\n";
		$output .= "$outputname".'[\''.$val['item'].'\'][\''.$val['desc'].'\'][\'matt\'] = '.$val['matt'].";\n";
		$output .= "$outputname".'[\''.$val['item'].'\'][\''.$val['desc'].'\'][\'qty\'] = '.$val['qty'].";\n";
	}
	return $output;
}
?>
