<?php

error_reporting(0);
require("database.php");
require("include/json.php");

if (MoS_checkip($_SERVER['REMOTE_ADDR'])) {
	$fields = array();
	$values = array();
	$return_array = array();
	$return_pre = array();
	
	// Get Form
	$sql = "SELECT * FROM forms WHERE ID = " . $_GET['form'];
	$result = mysql_query($sql) or die ("ERROR1");
	checkdberror($sql);
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	
	$return_array['forms'] = array();
	$return_array['forms'][] = $line;

        $result_disc = mysql_query("SELECT * FROM form_discount WHERE form_id = '".$line['ID']."'");
        while ($line_disc = mysql_fetch_assoc($result_disc)) {
            $return_array['form_discount'][] = $line_disc;
        }
        $result_disc = mysql_query("SELECT * FROM form_freight WHERE form_id = '".$line['ID']."'");
        while ($line_disc = mysql_fetch_assoc($result_disc)) {
            $return_array['form_freight'][] = $line_disc;
        }

        // Get Form Discount
        //$sql = "SELECT * FROM `form_discount` WHERE ID = " . $_GET['form'];
	
	// Get Form Snapshot
	$return_pre['snapshot_forms'] = array();
	$sql = "SELECT * FROM snapshot_forms WHERE `id` = '".$line['snapshot']."'";
	$result = mysql_query($sql) or die ("ERROR1");
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	$return_pre['snapshot_forms'][] = $line;


	
	// Get Form Headers
	$return_array['form_headers'] = array();
	$return_pre['snapshot_headers'] = array();
	$result = mysql_query("SELECT * FROM form_headers WHERE form = '" . $_GET['form'] . "'") or die("ERROR1");
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$return_array['form_headers'][] = $line;
		$header_array[] = $line['ID'];
		// Get Form Header Snapshot
		$sql = "SELECT * FROM snapshot_headers WHERE `id` = '".$line['snapshot']."'";
		$result = mysql_query($sql) or die ("ERROR1");
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$return_pre['snapshot_headers'][] = $line;
	}
	
	// Get Form Items
	$return_array['form_items'] = array();
	$return_pre['snapshot_items'] = array();
	$result = mysql_query("SELECT * FROM form_items WHERE header IN (" . implode(",", $header_array) . ")") or die ("ERROR1");
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$return_array['form_items'][] = $line;
                $result_disc = mysql_query("SELECT * FROM form_item_discount WHERE item_id = '".$line['ID']."'");
                while ($line_disc = mysql_fetch_assoc($result_disc)) {
                    $return_array['form_item_discount'][] = $line_disc;
                }
                $result_disc = mysql_query("SELECT * FROM form_item_freight WHERE item_id = '".$line['ID']."'");
                while ($line_disc = mysql_fetch_assoc($result_disc)) {
                    $return_array['form_item_freight'][] = $line_disc;
                }
		// Get Form Item Snapshot
		$sql = "SELECT * FROM snapshot_items WHERE `id` = '".$line['snapshot']."'";
		$result = mysql_query($sql) or die ("ERROR1");
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		$return_pre['snapshot_items'][] = $line;
	}
	// Merge Pre with Return
	$return_array = array_merge($return_pre, $return_array);
	
	// Convert to JSON for output
	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	$output = $json->encode($return_array);
	print("SUCCESS\n".$output);
	//echo $return_string;
	exit;
}

echo "ERROR4";


?>