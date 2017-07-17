<?php
require("database.php");
require("secure.php");
require("../inc_orders.php"); // Snapshot Functions

if ($_FILES['csvfile']['size'] > 0) {

	$form = $_POST['form'];
	$header = $_POST['header'];
	csv_remove_headers($form, $header);

	$csvfp = fopen($_FILES['csvfile']['tmp_name'], "r");
	$line = fgetcsv($csvfp, 2000); //-- Headers
	$headers = $line;
	foreach($headers as $key => $value) {
		if ($value == "") {
			unset($headers[$key]);
		}
		else {
			$headers[$key] = preg_replace("/[^A-Za-z0-9#]/", "_", $headers[$key]);
                        $headers[$key] = strtolower($headers[$key]);
		}
	}
	$headers = array_flip($headers);

	$line = fgetcsv($csvfp, 2000); //-- Blank Line

	$insert_header_order = 1;

	while ($line = fgetcsv($csvfp, 2000)) {
		if (preg_match("/[a-zA-z0-9]/", implode("", $line)) != 0) {
			if ($line[0] == "") {
				// Add Heading
				$header = addslashes($line[1]);
				$display_order = $insert_header_order;
				$sql = buildInsertQuery( "form_headers" );
				mysql_query( $sql );
				$last_insert_id = mysql_insert_id();
				checkdberror($sql);
				$insert_header_order++;
			}
			else {
				// Add item
				foreach($line as $key => $value) {
					$line[$key] = addslashes($value);
				}
                                $data = array();
				$data['header'] = $last_insert_id;
				$data['display_order'] = $line[$headers['order']];
				$data['partno'] = $line[$headers['part_#']];
				$data['sku'] = $line[$headers['sku']];
				$data['description'] = $line[$headers['desc']];
				$data['size'] = $line[$headers['size']];
				$data['price'] = $line[$headers['price']];
                                $data['cost'] = $line[$headers['cost']];
                                $data['markup'] = $line[$headers['markup']];
				$data['set_'] = $line[$headers['set']];
                                $data['set_cost'] = $line[$headers['set_cost']];
                                $data['set_markup'] = $line[$headers['set_markup']];
				$data['matt'] = $line[$headers['matt']];
                                $data['matt_cost'] = $line[$headers['matt_cost']];
                                $data['matt_markup'] = $line[$headers['matt_markup']];
				$data['box'] = $line[$headers['box']];
                                $data['box_cost'] = $line[$headers['box_cost']];
                                $data['box_markup'] = $line[$headers['box_markup']];
				$data['setqty'] = $line[$headers['set_qty']];
				$data['numinset'] = $line[$headers['qty_in_set']];
                $data['item_tier_override'] = (strtoupper(trim($line[$headers['tier_override']])) == 'Y')?'1':'0';
				$data['discount'] = $line[$headers['discount']];
                                $data['freight'] = $line[$headers['freight']];
				$data['cubic_ft'] = $line[$headers['volume']];
                                $data['seats'] = $line[$headers['seats']];
				$data['weight'] = $line[$headers['weight']];
				$data['stock'] = "1";
				$data['avail'] = "-1";
				$data['alloc'] = "-1";

                                $support_null = array('cost','set_','set_cost','matt','matt_cost','box','box_cost','cubic_ft');

                                foreach ($support_null as $fnull) {
                                    if ($data[$fnull] == '') {
                                        $data[$fnull] = null;
                                    }
                                }

				$item_id = item_add($data);
                                saveDiscount($data['discount'],'discount',array("item_id" => $item_id),"form_item");
                                saveDiscount($data['freight'],'freight',array("item_id" => $item_id),"form_item");
			}
		}
		elseif ($_POST['header'] != "") {
			//-- If the header is set and it hits a blank line, stop
			break;
		}
	}
	fclose($csvfp);
	$result = resortheaders($form);
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('form', $form);
	}
	header("Location: form-edit.php?ID=".$form);
	exit;
}


function csv_remove_headers($form, $header) {
	if ($header != "") {
		$sql = "select ID from form_headers where form=$form and ID = $header order by display_order";
	}
	else {
		$sql = "select ID from form_headers where form=$form order by display_order";
	}
	$bigquery = mysql_query($sql);
	checkDBError();
	while ($line = mysql_fetch_row($bigquery)) {
		$headerID = $line[0];
		$sql = "select ID from form_items where header=$headerID";
		$query = mysql_query($sql);
		checkDBError($sql);
		while ($item = mysql_fetch_array($query)) {
			$sql = "update snapshot_items set orig_id = 0 where orig_id = '".$item['ID']."'";
			mysql_query($sql);
			checkDBError($sql);
			$sql = "delete from form_items where ID = '".$item['ID']."' LIMIT 1";
			mysql_query($sql);
			checkDBError($sql);
		}
		$sql = "delete from form_items where header=$headerID";
		mysql_query( $sql );
		checkDBError($sql);

		$sql = "update snapshot_headers set orig_id = 0 where orig_id = '$headerID'";
		mysql_query($sql);
		checkDBError($sql);

		$sql = "delete from form_headers where ID='$headerID'";
		mysql_query($sql);
		checkDBError($sql);
	}
}

?>
