<?php

error_reporting(0);
require("database.php");

if (MoS_checkip($_SERVER['REMOTE_ADDR'])) {
	if ($_GET['table_to_use'] == "order_forms") {
		$req_fields = array("ordered", "process_time", "form", "user", "comments", "freight_percentage", "discount_percentage", "total", "type", "email_vendor", "user_address", "snapshot_user", "snapshot_form");
		foreach($req_fields as $value) {
			if (!isset($_GET[$value])) {
				echo "ERROR||(1)Missing field " . $value;
				exit;
			}
		}
		$sql = "INSERT INTO order_forms (ID, processed, ordered, process_time, form, user, comments, freight_percentage, discount_percentage, total, type, email_vendor, deleted, user_address, snapshot_user, snapshot_form, site) VALUES (null, 'N', '" . $_GET['ordered'] . "', '" . $_GET['process_time'] . "', '" . 
			$_GET['form'] . "', '" . $_GET['user'] . "', '" . $_GET['comments'] . "', " . $_GET['freight_percentage'] . 
			", " . $_GET['discount_percentage'] . ", " . $_GET['total'] . ", '" . $_GET['type'] . "', '" . $_GET['email_vendor'] . 
			"', " . "0" . ", " . $_GET['user_address'] . ", " . $_GET['snapshot_user'] . ", " .
			$_GET['snapshot_form'] . ", '".mysql_escape_string(MoS_checkip($_SERVER['REMOTE_ADDR']))."')";
		mysql_query($sql);
		if (mysql_error() == "") {
			echo "SUCCESS||" . mysql_insert_id();
			exit;
		}
		echo "ERROR||(1)Bad Mysql";
		exit;
	}
	elseif ($_GET['table_to_use'] == "orders") {
		$req_fields = array("user", "setqty", "mattqty", "qty", "item","discount", "freight", "ordered", "form", "po_id", "ordered_time", "snapshot_user", "snapshot_form");
		foreach($req_fields as $value) {
			if (!isset($_GET[$value])) {
				echo "ERROR||(2)Missing field " . $value;
				exit;
			}
		}
		$sql = "INSERT INTO orders (user, setqty, mattqty, qty, item, discount, freight, ordered, form, po_id, ordered_time, snapshot_user, snapshot_form)".
		   "VALUES ('" . $_GET['user'] . "', '" . $_GET['setqty'] . "', '" . $_GET['mattqty'] . "', '" . $_GET['qty'] . 
		   "', '" . $_GET['item'] . "', '" . $_GET['discount'] . "', '" . $_GET['freight'] . "', '" . $_GET['ordered'] . "', '" . $_GET['form'] . "', '" . $_GET['po_id'] . "', '" . $_GET['ordered_time'] .
		   "', '" . $_GET['snapshot_user'] . "', '" . $_GET['snapshot_form'] . "')";
		mysql_query($sql);
		if (mysql_error() == "") {
			echo "SUCCESS||" . mysql_insert_id();
			exit;
		}
		echo "ERROR||(2)Bad Mysql ".$sql;
		exit;
	}
	elseif ($_GET['table_to_use'] == "rem_order_forms") {
		if ($_GET['id'] != "") {
			mysql_query("UPDATE order_forms SET deleted = 1 WHERE ID = " . $_GET['id'] . " LIMIT 1");
			if (mysql_affected_rows() > 0) {
				echo "SUCCESS";
				exit;
			}
		}
		echo "FAILED";
		exit;
	}
	elseif ($_GET['table_to_use'] == "snapshot_forms") {
		$req_fields = array("orig_id", "orig_vendor", "name", "address", "city", "state", "zip", "phone", "fax", "email", "email2", "prepaidfreight", "discount");
		foreach($req_fields as $value) {
			if (!isset($_GET[$value])) {
				echo "ERROR||(3)Missing field " . $value;
				exit;
			}
		}
		$sql = "INSERT INTO snapshot_forms (id, orig_id, orig_vendor, name, address, city, state, zip, phone, fax, email, email2, prepaidfreight, discount) VALUES (null, " . $_GET['orig_id'] . ", " . $_GET['orig_vendor'] . ", '" . $_GET['name'] . "', '" . 
			$_GET['address'] . "', '" . $_GET['city'] . "', '" . $_GET['state'] . "', '" . $_GET['zip'] . "', '" . $_GET['phone'] . "', '" . 
			$_GET['fax'] . "', '" . $_GET['email'] . "', '" . $_GET['email2'] . "', '" . $_GET['prepaidfreight'] . "', " . $_GET['discount'] . ")";
		mysql_query($sql);
		if (mysql_error() == "") {
			echo "SUCCESS||" . mysql_insert_id();
			exit;
		}
		echo "ERROR||(3)Bad Mysql";
		exit;
	}
	elseif ($_GET['table_to_use'] == "snapshot_headers") {
		$req_fields = array("orig_id", "form", "header", "display_order");
		foreach($req_fields as $value) {
			if (!isset($_GET[$value])) {
				echo "ERROR||(4)Missing field " . $value;
				exit;
			}
		}
		$sql = "INSERT INTO snapshot_headers (id, orig_id, form, header, display_order) VALUES (null, " . $_GET['orig_id'] . ", " . $_GET['form'] . ", '" . 
			$_GET['header'] . "', " . $_GET['display_order'] . ")";
		mysql_query($sql);
		if (mysql_error() == "") {
			echo "SUCCESS||" . mysql_insert_id();
			exit;
		}
		echo "ERROR||(4)Bad Mysql";
		exit;
	}
	elseif ($_GET['table_to_use'] == "snapshot_items") {
		$req_fields = array("orig_id", "header", "partno", "description", "price", "size", "color", "set_", "matt", "box", "display_order", "cubic_ft", "setqty","sku");
		foreach($req_fields as $value) {
			if (!isset($_GET[$value])) {
				echo "ERROR||(5)Missing field " . $value;
				exit;
			}
		}
		$sql = "INSERT INTO snapshot_items (orig_id, header, partno, description, price, size, color, set_, matt, box, display_order, cubic_ft, setqty, sku) VALUES ('" . $_GET['orig_id'] . "', '" . $_GET['header'] . "', '" . $_GET['partno'] . "', '" . 
			$_GET['description'] . "', '" . $_GET['price'] . "', '" . $_GET['size'] . "', '" . $_GET['color'] . "', '" . 
			$_GET['set_'] . "', '" . $_GET['matt'] . "', '" . $_GET['box'] . "', '" . $_GET['display_order'] . "', '" . 
			$_GET['cubic_ft'] . "', '" . $_GET['setqty'] . "', '" . $_GET['sku'] . "')";
		mysql_query($sql);
		if (mysql_error() == "") {
			echo "SUCCESS||" . mysql_insert_id();
			exit;
		}
		echo "ERROR||(5)Bad Mysql";
		exit;
	}
	echo "ERROR||Bad Table";
	exit;
}
echo "ERROR||Bad User";
?>
