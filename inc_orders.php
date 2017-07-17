<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');


function item_add($data) {
	// Set Defaults
	if (!isset($data['setqty'])) $data['setqty'] = 2;
	// Snapshot Creation
	// Get Snapshot Header info...
	$sql = "SELECT snapshot FROM form_headers WHERE ID = '".$data['header']."'";
	$query = mysql_query($sql);
	checkDBerror($sql, true, __FILE__, __LINE__);
	if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$snapshot_header = $result['snapshot'];
	}
        
        // Get table info...
        $snap_data = snapshot_itemadd_gettableinfo('snapshot_items', $data);
        // Assemble Insert crap...
        $first = true;
        $assemble = '';
        foreach ($snap_data as $column) {
            if (!$first) {
                $assemble .= ',';
            }
            $first = false;
            if (is_null($column)) {
                $assemble .= "NULL";
            } else {
                $assemble .= "'".mysql_escape_string($column)."'";
            }
        }
	// $assemble = "'".implode("', '",$snap_data)."'";
	$sql = "INSERT INTO snapshot_items VALUES (NULL,".$assemble.")";
	mysql_query($sql);
	$data['snapshot'] = mysql_insert_id();
	checkDBerror($sql, true, __FILE__, __LINE__);
	
	// Get table info...
        $gooddata = snapshot_itemadd_gettableinfo('form_items', $data);
	// Assemble Insert crap...
	$assemble = "'".implode("', '",$gooddata)."'";
	$sql = "INSERT INTO form_items VALUES (NULL,".$assemble.")";
	mysql_query($sql);
	$return = mysql_insert_id();
	checkDBerror($sql, true, __FILE__, __LINE__);

	$sql = "UPDATE snapshot_items SET orig_id = '".$return."' WHERE id = '".$data['snapshot']."'";
	mysql_query($sql);
	checkDBerror($sql, true, __FILE__, __LINE__);

	return $return;
}

function snapshot_itemadd_gettableinfo($tablename, $data) {
        $result = mysql_query("SHOW COLUMNS FROM `".$tablename."`");
        $tablecolumns = array();
        $autocol = null;
        $gooddata = array();
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $table[$line["Field"]] = $line;
		if ($line["Extra"] == "auto_increment") {
                    $autocol = $line["Field"];
                } else {
                    $tablecolumns[] = $line["Field"];
                }
	}
        
	foreach ($tablecolumns as $column) {
                if ($table[$column]['Null'] == 'NO') {
                    if (isset($data[$column])&&!is_null($data[$column])) {
                        $gooddata[$column] = $data[$column];
                    } else {
                        $gooddata[$column] = '';
                    }
                } else {
                    if (isset($data[$column])&&$data[$column] !== '') {
                        $gooddata[$column] = $data[$column];
                    } else {
                        $gooddata[$column] = null;
                    }
                }
	}
        
        return $gooddata;
}

// Snapshot Updates will clean database as well,
// i.e. a Vendor Update that deletes the vendor, will delete all old forms, items etc.
function snapshot_update($table, $ID) {
	switch ($table) {
		case "item":
			snapshot_update_item($ID);
		break;
		case "header":
			snapshot_update_header($ID);
		break;
		case "form":
			snapshot_update_form($ID);
		break;
		case "vendor":
			snapshot_update_vendor($ID);
		break;
	}
}

function snapshot_update_item($ID) {
	global $link;
	global $databasename;
	global $MoS_enabled;
	if ($MoS_enabled) {
		$table_prefix = 'MoS_';
	} else {
		$table_prefix = '';
	}
	$snapshot_fields = DBlistfields($table_prefix.'snapshot_items');
	$real_fields = DBlistfields($table_prefix.'form_items');
	$fields = array_intersect($snapshot_fields, $real_fields);
	$sql = "SELECT `".implode("`, `",$fields)."` FROM `".$table_prefix."form_items` WHERE `ID` = '".$ID."'";
	$query = mysql_query($sql);
	checkDBerror($sql, true, __FILE__, __LINE__);
	if (mysql_num_rows($query)) {
		$item = mysql_fetch_array($query, MYSQL_ASSOC);
		$snapshot = array();
		foreach ($fields as $field) {
			if ($field == 'header') {
				$sql = "SELECT `snapshot` FROM `".$table_prefix."form_headers` WHERE ID = '".$item[$field]."'";
				$query = mysql_query($sql);
				checkDBerror($sql, true, __FILE__, __LINE__);
				$result = mysql_fetch_array($query, MYSQL_ASSOC);
				$item[$field] = $result['snapshot'];
			}
			$snapshot[$field] = $item[$field];
		}
		$sql = "INSERT INTO ".$table_prefix."snapshot_items (`orig_id`, `".implode('`, `',$fields)."`) VALUES ('".$ID."','".implode("', '",escapearray($snapshot))."')";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$snapshot_id = mysql_insert_id();
		$sql = "UPDATE `".$table_prefix."form_items` SET `snapshot` = '".$snapshot_id."' WHERE `ID` = '".$ID."'";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
	} else {
		$sql = "UPDATE ".$table_prefix."snapshot_items SET `orig_id` = '0' WHERE `orig_id` = '".$ID."'";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
	}
}

function snapshot_update_header($ID) {
	global $link;
	global $databasename;
	global $MoS_enabled;
	if ($MoS_enabled) {
		$table_prefix = 'MoS_';
	} else {
		$table_prefix = '';
	}
	$snapshot_fields = DBlistfields($table_prefix.'snapshot_headers');
	$real_fields = DBlistfields($table_prefix.'form_headers');
	$fields = array_intersect($snapshot_fields, $real_fields);
	$sql = "SELECT `".implode("`, `",$fields)."` FROM `".$table_prefix."form_headers` WHERE `ID` = '".$ID."'";
	$query = mysql_query($sql);
	checkDBerror($sql, true, __FILE__, __LINE__);
	if (mysql_num_rows($query)) {
		$header = mysql_fetch_array($query, MYSQL_ASSOC);
		$snapshot = array();
		foreach ($fields as $field) {
			if ($field == 'form') {
				$sql = "SELECT `snapshot` FROM `".$table_prefix."forms` WHERE ID = '".$header[$field]."'";
				$query = mysql_query($sql);
				checkDBerror($sql, true, __FILE__, __LINE__);
				$result = mysql_fetch_array($query, MYSQL_ASSOC);
				$header[$field] = $result['snapshot'];
			}
			$snapshot[$field] = $header[$field];
		}
		$sql = "INSERT INTO ".$table_prefix."snapshot_headers (`orig_id`, `".implode('`, `',$fields)."`) VALUES ('".$ID."','".implode("', '",escapearray($snapshot))."')";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$snapshot_id = mysql_insert_id();
		$sql = "UPDATE `".$table_prefix."form_headers` SET `snapshot` = '".$snapshot_id."' WHERE `ID` = '".$ID."'";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$sql = "SELECT `ID` FROM `".$table_prefix."form_items` WHERE `header` = '".$ID."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		while ($item = mysql_fetch_array($query, MYSQL_ASSOC)) {
			snapshot_update_item($item['ID']);
		}
	} else {
		$sql = "UPDATE ".$table_prefix."snapshot_headers SET `orig_id` = '0' WHERE `orig_id` = '".$ID."'";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$sql = "SELECT `ID` FROM `".$table_prefix."form_items` WHERE `header` = '".$ID."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		while ($item = @mysql_fetch_array($query, MYSQL_ASSOC)) {
			$sql = "DELETE FROM `".$table_prefix."form_items` WHERE `ID` = '".$item['ID']."'";
			$query = mysql_query($sql);
			checkDBerror($sql, true, __FILE__, __LINE__);
			snapshot_update_item($item['ID']);
		}
	}
}

function snapshot_update_form($ID) {
	global $link;
	global $databasename;
	global $MoS_enabled;
	if ($MoS_enabled) {
		$table_prefix = 'MoS_';
	} else {
		$table_prefix = '';
	}
	$snapshot_fields = DBlistfields($table_prefix.'snapshot_forms');
	$real_fields = DBlistfields($table_prefix.'forms');
	$fields = array_intersect($snapshot_fields, $real_fields);
	$sql = "SELECT `".implode("`, `",$fields)."`, `vendor` FROM `".$table_prefix."forms` WHERE `ID` = '".$ID."'";
	$query = mysql_query($sql);
	checkDBerror($sql, true, __FILE__, __LINE__);
	if (mysql_num_rows($query)) {
		$form = mysql_fetch_array($query, MYSQL_ASSOC);
		$snapshot = array();
		foreach ($fields as $field) {
			if ($field == 'form') {
				$sql = "SELECT `snapshot` FROM `vendors` WHERE ID = '".$form[$field]."'";
				$query = mysql_query($sql);
				checkDBerror($sql, true, __FILE__, __LINE__);
				$result = mysql_fetch_array($query, MYSQL_ASSOC);
				$form[$field] = $form['snapshot'];
			}
			$snapshot[$field] = $form[$field];
		}
		$vendor_fields = DBlistfields('vendors');
		unset($vendor_fields[array_search('name', $vendor_fields)]);
		if ($form['address']) {
			unset($vendor_fields[array_search('address', $vendor_fields)]);
			unset($vendor_fields[array_search('city', $vendor_fields)]);
			unset($vendor_fields[array_search('state', $vendor_fields)]);
			unset($vendor_fields[array_search('zip', $vendor_fields)]);
			unset($vendor_fields[array_search('phone', $vendor_fields)]);
			unset($vendor_fields[array_search('fax', $vendor_fields)]);
		}
		$vensnap_fields = array_intersect($snapshot_fields, $vendor_fields);
		$sql = "SELECT `ID`, `".implode("`, `",$vensnap_fields)."` FROM `vendors` WHERE `ID` = '".$form['vendor']."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		if ($vendor = mysql_fetch_array($query, MYSQL_ASSOC)) {
			foreach ($vensnap_fields as $field) {
				$snapshot[$field] = $vendor[$field];
			}
			$snapshot['orig_vendor'] = $vendor['ID'];
		}
		$sql = "INSERT INTO ".$table_prefix."snapshot_forms (`orig_id`, `".implode('`, `',array_keys($snapshot))."`) VALUES ('".$ID."','".implode("', '",escapearray($snapshot))."')";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$snapshot_id = mysql_insert_id();
		$sql = "UPDATE `".$table_prefix."forms` SET `snapshot` = '".$snapshot_id."' WHERE `ID` = '".$ID."'";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$sql = "SELECT `ID` FROM `".$table_prefix."form_headers` WHERE `form` = '".$ID."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		while ($item = mysql_fetch_array($query, MYSQL_ASSOC)) {
			snapshot_update_header($item['ID']);
		}
	} else {
		$sql = "UPDATE ".$table_prefix."snapshot_forms SET `orig_id` = '0' WHERE `orig_id` = '".$ID."'";
		mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		$sql = "SELECT `ID` FROM `".$table_prefix."form_headers` WHERE `form` = '".$ID."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		while ($header = @mysql_fetch_array($query, MYSQL_ASSOC)) {
			$sql = "DELETE FROM `".$table_prefix."form_headers` WHERE `ID` = '".$header['ID']."'";
			$query = mysql_query($sql);
			checkDBerror($sql, true, __FILE__, __LINE__);
			snapshot_update_header($header['ID']);
		}
	}
}

function snapshot_update_vendor($ID) {
	$sql = "SELECT `ID` FROM `vendors` WHERE `ID` = '".$ID."'";
	$query = mysql_query($sql);
	checkDBerror($sql, true, __FILE__, __LINE__);
	if (mysql_num_rows($query)) {
		$sql = "SELECT `ID` FROM `".$table_prefix."forms` WHERE `vendor` = '".$ID."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		while($form = mysql_fetch_array($query, MYSQL_ASSOC)) {
			snapshot_update_form($form['ID']);
		}
	} else {
		$sql = "SELECT `ID` FROM `".$table_prefix."forms` WHERE `vendor` = '".$ID."'";
		$query = mysql_query($sql);
		checkDBerror($sql, true, __FILE__, __LINE__);
		while($form = mysql_fetch_array($query, MYSQL_ASSOC)) {
			$sql = "DELETE FROM `".$table_prefix."forms` WHERE `ID` = '".$form['ID']."'";
			$query = mysql_query($sql);
			checkDBerror($sql, true, __FILE__, __LINE__);
			snapshot_update_form($form['ID']);
		}
	}
}
