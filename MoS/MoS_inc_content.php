<?php
function fail_recover($ch, $new_order_id) {
	global $MoS_MasterPath;
	echo "<BR><BR>Attempting to delete sent over order: " . ($new_order_id+1000) . "<BR><BR>";
	curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?table_to_use=rem_order_forms&id=" . $new_order_id);
	$del_result = curl_exec($ch);
	if ($del_result == "SUCCESS") {
		echo "Order was deleted from the RSS system. Please try approving this order again.";
	}
	else {
		echo "Unable to delete order. Please note that order " . ($new_order_id+1000) . " in the RSS system is an invalid order.";
	}
	curl_close($ch);
	exit;
}

// $action = A | D
function MoS_process_order($action, $po) {
	global $MoS_MasterPath;
	$po_id = $po-1000;
	if ($action == "A") {
		//-- Add the RSS connection to the mix, defined as $link2
		//require("MoS_config_PMD_db.inc.php");

		/* To have this order work correctly, we have to do a number of things:
				1. Select * from the MoS order form for the po_id
				2. Check if the order's snapshots are on the RSS system or the MOS system
					If they are MOS:
						2-A: *Step removed*
						2-B: Get the MoS_snapshot_form from the 'snapshot_form' id in 1
							 If it has a transfer ID change the variable version of 1's 'snapshot_form' to the transfer_id
							 If it does not have a transfer ID
								2-B-1: Insert the snapshot_form into RSS, get it's insert ID and update the PMD_transfer_id on the MoS_snapshot_form
								2-B-2: Update the variable version of 1's 'snapshot_form' to the transfer_id
						2-C: Insert 2-C into RSS and update it's PMD_order_id in the DB to the newly inserted ID
						2-D: Take all the entries in MoS orders that pertain to this order form
							2-D-1: Change the entry's 'po_id' to the newly insert id from 2-C
							2-D-2: Change the entry's snapshot_form to the one obtained in 2-B-1
							2-D-3: Get the snapshot_item pertaining to this entry
							2-D-4: If the snapshot_item has a transfer_id change the order's 'item' to this ID
								   If the snapshot_item does not have a transfer_id
									2-D-4-A: Get the snapshot_header pertaining to this snapshot_item
									2-D-4-B: If the snapshot_header has a transfer_id set the snapshot item's 'header' to the transfer_id
											 If it doesn't have a transfer_id
											 2-D-4-B-1: Set the variable version's 'form' to the snapshot_form's transfer_id (2-B)
											 2-D-4-B-2: Insert the snapshot_header and save it's new_insert_id to the variable version
															of 2-D-4, as the 'header'
									2-D-4-C: Insert the snapshot_item into RSS, updating it's transfer_id as the new_insert_id
									2-D-4-D: change 2-D-1's variable version of 'item' to the id obtained in 2-D-4-C
							2-D-5: Insert the entry(MoS_order) into RSS
						2-E: DONE
					If they are RSS:
						2-A: Insert the order form into the RSS system, getting it's insert_id and saving it to PMD_order_id
						2-B: Get the orders pertaining to that form inserting them into RSS with the new insert_id instead of their current po_id
				2. DONE
		*/
	//-- 1
		$sql = "SELECT * FROM MoS_order_forms WHERE ID = '" . $po_id . "'";
		$order_form = mysql_query($sql) or die(mysql_error());
		$order_form = mysql_fetch_array($order_form, MYSQL_ASSOC);
	//-- 2
		if ($order_form['snapshot_location'] == "PMD") {
	//#########################################
	//-- They are RSS
	//-- 2-A
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
			$get_string = "table_to_use=order_forms";
			foreach ($order_form as $key => $value) {
				if ($key == 'comments') $value = str_replace('%po%',($po_id+1000),getconfig('comment','MoS_'))."\n".$value;
				if ($key == 'ordered') $value = date("Y-m-d H:i:s");
				$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
			}
			curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
			$order_form_send_result = curl_exec($ch);
			$order_form_send_result = explode("||", trim(trim($order_form_send_result, "\n")));
			if ($order_form_send_result[0] != "SUCCESS") {
				echo $order_form_send_result[0] . " -- " . $order_form_send_result[1];
				exit;
			}

			$new_order_id = $order_form_send_result[1];
	//-- 2-B
			$sql = "SELECT * FROM MoS_orders WHERE po_id = '" . $po_id . "'";
			$orders = mysql_query($sql) or die(mysql_error());
			while ($line = mysql_fetch_array($orders, MYSQL_ASSOC)) {
				$get_string = "table_to_use=orders";
				$line['po_id'] = $new_order_id;
				foreach($line as $key => $value) {
					if ($key == 'ordered') $value = date("Y-m-d");
					if ($key == 'ordered_time') $value = date("H:i:s");
					$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
				}
				curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
				$order_send_result = curl_exec($ch);
				$order_send_result = explode("||", trim(trim($order_send_result, "\n")));
				if ($order_send_result[0] != "SUCCESS") {
					echo $order_send_result[0] . " -- " . $order_send_result[1];
					fail_recover($ch, $new_order_id);
				}
			}
			$sql = "UPDATE MoS_order_forms SET PMD_order_id = $new_order_id WHERE ID = $po_id";
			mysql_query($sql) or die(mysql_error());
			curl_close($ch);
		}
		elseif ($order_form['snapshot_location'] == "MOS") {
	//#########################################
	//-- They are MOS, time for a lot of work to ensure snapshots aren't duplicated and wasting space
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//-- 2-A
			//-- ** STEP REMOVED **
	//-- 2-B
			$sql = "SELECT * FROM MoS_snapshot_forms WHERE id = " . $order_form['snapshot_form'];
			$snapshot_form = mysql_query($sql) or die(mysql_error());
			$snapshot_form = mysql_fetch_array($snapshot_form, MYSQL_ASSOC);
			if (!is_null($snapshot_form['PMD_transfer_id'])) {
				$PMD_transfer_id_snapshot_form = $snapshot_form['PMD_transfer_id'];
			}
			else {
	//-- 2-B-1
				$get_string = "table_to_use=snapshot_forms";
				foreach($snapshot_form as $key => $value) {
					$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
				}
				curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
				$snapshot_form_send_result = curl_exec($ch);
				$snapshot_form_send_result = explode("||", trim(trim($snapshot_form_send_result, "\n")));
				if ($snapshot_form_send_result[0] != "SUCCESS") {
					echo $snapshot_form_send_result[0] . " -- " . $snapshot_form_send_result[1];
					exit;
				}
				$PMD_transfer_id_snapshot_form = $snapshot_form_send_result[1];
	//-- 2-B-2
			}
			$order_form['snapshot_form'] = $PMD_transfer_id_snapshot_form;
	//-- 2-C
			$get_string = "table_to_use=order_forms";
			foreach ($order_form as $key => $value) {
				if ($key == 'comments') $value = str_replace('%po%',($po_id+1000),getconfig('comment','MoS_'))."\n".$value;
				if ($key == 'ordered') $value = date("Y-m-d H:i:s");
				$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
			}
			curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
			$order_form_send_result = curl_exec($ch);
			$order_form_send_result = explode("||", trim(trim($order_form_send_result, "\n")));
			if ($order_form_send_result[0] != "SUCCESS") {
				echo $order_form_send_result[0] . " -- " . $order_form_send_result[1];
				exit;
			}
			$new_order_id = $order_form_send_result[1];
	//-- 2-D
			$sql = "SELECT * FROM MoS_orders WHERE po_id = '" . $po_id . "'";
			$orders = mysql_query($sql) or die (mysql_error());
			while ($order = mysql_fetch_array($orders, MYSQL_ASSOC)) {
	//-- 2-D-1
				$order['po_id'] = $new_order_id;
	//-- 2-D-2
				$order['snapshot_form'] = $PMD_transfer_id_snapshot_form;
	//-- 2-D-3
				$sql = "SELECT * FROM MoS_snapshot_items WHERE id = " . $order['item'];
				$snapshot_item = mysql_query($sql) or die(mysql_error());
				$snapshot_item = mysql_fetch_array($snapshot_item, MYSQL_ASSOC);
	//-- 2-D-4
				if (!is_null($snapshot_item['PMD_transfer_id'])) {
					$order['item'] = $snapshot_item['PMD_transfer_id'];
				}
				else {
	//-- 2-D-4-A
					$sql = "SELECT * FROM MoS_snapshot_headers WHERE id = " . $snapshot_item['header'];
					$snapshot_header = mysql_query($sql) or die(mysql_error());
					$snapshot_header = mysql_fetch_array($snapshot_header, MYSQL_ASSOC);
	//-- 2-D-4-B
					if (!is_null($snapshot_header['PMD_transfer_id'])) {
						$snapshot_item['header'] = $snapshot_header['PMD_transfer_id'];
					}
					else {
	//-- 2-D-4-B-1
						$snapshot_header['form'] = $PMD_transfer_id_snapshot_form;
	//-- 2-D-4-B-2
						$get_string = "table_to_use=snapshot_headers";
						foreach($snapshot_header as $key => $value) {
							$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
						}
						curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
						$snapshot_header_send_result = curl_exec($ch);
						$snapshot_header_send_result = explode("||", trim(trim($snapshot_header_send_result, "\n")));
						if ($snapshot_header_send_result[0] != "SUCCESS") {
							echo $snapshot_header_send_result[0] . " -- " . $snapshot_header_send_result[1];
							fail_recover($ch, $new_order_id);
						}
						$PMD_transfer_id_snapshot_header = $snapshot_header_send_result[1];
						$sql = "UPDATE MoS_snapshot_headers SET PMD_transfer_id = $PMD_transfer_id_snapshot_header WHERE id = " . $snapshot_item['header'];
						mysql_query($sql);
						$snapshot_item['header'] = $PMD_transfer_id_snapshot_header;
					}
	//-- 2-D-4-C
					$get_string = "table_to_use=snapshot_items";
					foreach($snapshot_item as $key => $value) {
						$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
					}
					curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
					$snapshot_item_send_result = curl_exec($ch);
					$snapshot_item_send_result = explode("||", trim(trim($snapshot_item_send_result, "\n")));
					if ($snapshot_item_send_result[0] != "SUCCESS") {
						echo $snapshot_item_send_result[0] . " -- " . $snapshot_item_send_result[1];
						fail_recover($ch, $new_order_id);
					}
					$PMD_transfer_id_snapshot_item = $snapshot_item_send_result[1];
					$sql = "UPDATE MoS_snapshot_items SET PMD_transfer_id = $PMD_transfer_id_snapshot_item WHERE id = " . $order['item'];
					mysql_query($sql);
	//-- 2-D-4-D
					$order['item'] = $PMD_transfer_id_snapshot_item;
				}
	//-- 2-D-5
				$get_string = "table_to_use=orders";
				foreach($order as $key => $value) {
					if ($key == 'ordered') $value = date("Y-m-d");
					if ($key == 'ordered_time') $value = date("H:i:s");
					$get_string .= "&" . urlencode($key) . "=" . urlencode($value);
				}
				curl_setopt($ch, CURLOPT_URL, $MoS_MasterPath . "MoS_accept_order.php?" . $get_string);
				$order_send_result = curl_exec($ch);
				$order_send_result = explode("||", trim(trim($order_send_result, "\n")));
				if ($order_send_result[0] != "SUCCESS") {
					echo $order_send_result[0] . " -- " . $order_send_result[1];
					fail_recover($ch, $new_order_id);
				}
			}	
			$sql = "UPDATE MoS_order_forms SET PMD_order_id = $new_order_id WHERE ID = $po_id";
			mysql_query($sql);
			curl_close($ch);
		}
		else {
			echo "The order cannot be processed, it does not have a snapshot_location";
		}
	
		// Removed because the Op site will transmit Processed Date back to us
		$sql = "UPDATE MoS_order_forms SET processed='" . $action . "' WHERE ID='$po_id'";
		mysql_query($sql);
		checkDBError();
		//$po_id = $new_order_id;
		//$po = $new_order_id + 1000;
	
	}

	if ($action == "D") {

		$sql = "UPDATE MoS_order_forms SET processed='" . $action . "' WHERE ID='$po_id'";
		mysql_query($sql);
		checkDBError();

		$sql = "SELECT form, user, ordered, snapshot_form FROM MoS_order_forms WHERE ID = ".$po_id;
		$query = mysql_query($sql);
		checkDBError();
		if ($result = mysql_fetch_array($query)) {
			$user2 = $result['user'];
			$form = $result['form'];
			$date = $result['ordered'];
			$snapshot_form = $result['snapshot_form'];
		} else {
			// Can't find the Order, so let's make sure we don't get some stupid values.
			$user2 = 0;
			$form = 0;
			$date = 0;
			$snapshot_form = 0;
		}

		$sql = "SELECT email, email2, email3 FROM users WHERE ID=$user2";
		$query = mysql_query($sql);
		checkDBError();
		if ($result = mysql_fetch_array($query)) {
			$email = $result[0];
			$email2 = $result[1];
			$email3 = $result[2];
		} else {
			unset($email);
			unset($email2);
			unset($email3);
		}

		$sql = "SELECT name FROM " . $table_prefix . "snapshot_forms WHERE id='$snapshot_form'";
		$query = mysql_query($sql);
		checkDBError();
		if ($result = mysql_fetch_Array($query))
			$vendor = $result['name'];

		$msg = 
		"Your Market Order has been declined.

		Do not reply to this e-mail, contact your Dealer Support Person for any issues associated with this order.

		-----------------------------------------------------------
		";


		/* 
		$msg .= OrderForEmail($po);

		$subject = date( "m/d/Y", strtotime($date))." $vendor Market Order - Declined";
		$headers = "From: PMD Orders <orders@pmdfurniture.com>";
		if ($email2 <> "") $headers .= "\nCc: ".$email2;
		if ($email3 <> "") $headers .= "\nCc: ".$email3;
		$headers .= "\nBcc: orders@pmdfurniture.com";
		sendmail($email, $subject, $msg, $headers);
		*/
	}
}
?>
