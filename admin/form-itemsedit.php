<?php
require("database.php");
require("secure.php");
require("../inc_orders.php");
//die("boom!");
//if ($_POST['special_xmlhttprequest'] == 'Y') {
//	print_r($_REQUEST);
//}

$data = array();
$old = array();

foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^data_(.+)",$key, $reg))
		$data[$reg[1]] = $value;
}


foreach($_REQUEST as $key => $value) {
	$reg = array();
	if(ereg("^old_(.+)",$key, $reg))
		$old[$reg[1]] = $value;
}

$update = array();
$olddata = array();
foreach($data as $key => $value) {
	$reg = array();
	if(ereg("^([0-9]+)_(.+)",$key, $reg)) {
		if ($old[$key] != $value) {
			$update[$reg[1]][$reg[2]] = $value;
			$olddata[$reg[1]][$reg[2]] = $old[$key];
		}
		if ($_REQUEST['allstock'] != 0) {
			$update[$reg[1]]['stock'] = $_REQUEST['allstock'];
		}
	}
}

// Handle checkbox wierdness (an unchecked box will not show up in the data, but it's old portion will)
foreach ($old as $key => $value) {
    if (!isset($data[$key])) {
        if(ereg("^([0-9]+)_(.+)",$key, $reg)) {
            $data[$key] = '0';
            if ($value != '0') {
                $update[$reg[1]][$reg[2]] = '0';
    			$olddata[$reg[1]][$reg[2]] = $old[$key];
            }
        }
    }
}

foreach ($update as $id => $data) {
        if (isset($data['freight'])) {
            saveDiscount($data['freight'],'freight',array("item_id" => $id),"form_item");
            unset($data['freight']);
        }
        if (isset($data['discount'])) {
            saveDiscount($data['discount'],'discount',array("item_id" => $id),"form_item");
            unset($data['discount']);
        }
	if (count($data) == 0)
		continue;
	// Setup Stock Options
	if ($data['stock']) {
		$stock_set = stock_status($data['stock']);
		if ($stock_set['zeroday'] == 'Y') {
			$data['stock_day'] = 0;
		}
		$data['alloc'] = -1;
		$data['avail'] = -1;
	} elseif (isset($data['avail'])) {
		if (!is_numeric($data['avail']))
			$data['avail'] = -1;
			$data['stock'] = 2;
			$stock_set = stock_status($data['stock']);
			if ($stock_set['zeroday'] == 'Y') {
				$data['stock_day'] = 0;
			}
		if ($data['avail'] < 1 || $data['avail'] == '') {
			$data['avail'] = -1;
			$data['stock'] = 2;
			$stock_set = stock_status($data['stock']);
			if ($stock_set['zeroday'] == 'Y') {
				$data['stock_day'] = 0;
			}
		} else {
			$data['stock'] = 1;
		}
		$data['alloc'] = $data['avail'];
	}
	$setstring = array();
	foreach ($data as $key => $value) {
		if (get_magic_quotes_gpc()) { // Remove the Magic Quotes if their enabled
			$key = stripslashes($key);
			$value = stripslashes($value);
		}
		$setstring[] = "`".mysql_escape_string($key)."` = '".mysql_escape_string($value)."'";
		$action = "$key was changed from \"".$olddata[$id][$key]."\" to \"$value\"\n";
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
				 values($id,$userid,".date("Ymd").",'".mysql_escape_string($action)."',${form_id})";
		mysql_query($sql);
		checkDBError($sql, true, __FILE__, __LINE__);
	}
	$setstring = implode(", ",$setstring);
	$id = mysql_escape_string($id);
	$sql = "UPDATE `form_items` SET ".$setstring." WHERE ID = '".$id."'";
	mysql_query($sql);
	CheckDBError($sql);
	snapshot_update('item', $id); // Update Snapshot for the item
}

mysql_close($link);
if ($_POST['special_xmlhttprequest'] == 'Y') {
	echo "Header Updated";
} else {
	header("location: form-edit.php?ID=$form_id");
}
?>
