<?php
	require("database.php");
	require("secure.php");

	if ($_POST['submit'] == "Submit") {
		do_insert();
		//opener.location.reload(true);
		echo "<html><body onLoad=\"opener.csvqueuepost();window.close();\">Order AutoLogged</body></html>";
	}
	elseif ($_POST['submit'] == "Export") {
		if ($_POST['exportonly'] != "yes") {
			do_insert();
		}
		echo "<html><body onLoad=\"opener.location.reload(true);\">";
		echo '<link rel="stylesheet" href="../styles.css" type="text/css">';
		$source_filename = "exported_csvs/" . DATE("Y-m-d") . "_" . $_GET['type'];
		$filename = $source_filename;
		$int_count = 1;
		while(file_exists($filename . ".csv")) {
			$filename = $source_filename . "_" . $int_count;
			$int_count++;
		}
		$filename .= ".csv";
		if ($_GET['type'] == "Access") {
			$sql_fields = "name, vendor,date,po_id,vendor,product,total";
			$write_fields = '"Dealer","Vender","Date","Order#","Product","Amount"'."\n";
		}
		else {
			$sql_fields = "po_id,name,date,product,total";
			$write_fields = '';
		}
		if ($fp = fopen($filename, "w")) {
			$sql = "SELECT $sql_fields FROM exported_orders WHERE type='" . $_GET['type'] . "' AND isnull(export_id)";
			$query = mysql_query($sql);
			checkDBError($sql);
			fwrite($fp, $write_fields);
			while ($line = mysql_fetch_array($query, MYSQL_ASSOC)) {
				$write_arr = array();
				foreach($line as $value) {
					$write_arr[] = str_replace('"', '""', $value);
				}
				fwrite($fp, '"' . implode('","', $write_arr) . '"' . "\n");
			}
			fclose($fp);
			$sql = "SELECT MAX(export_id) FROM exported_orders";
			$result = mysql_query($sql);
			checkDBError($sql);
			$line = mysql_fetch_row($result);
			$sql = "UPDATE exported_orders SET export_id = " . (intval($line[0]) + 1) . " WHERE isnull(export_id) AND type='" . $_GET['type'] . "'";
			mysql_query($sql);
			checkDBError($sql);
			if (!(mysql_affected_rows() > 0)) {
				echo "<DIV align=center><B>Could not update exported orders, the file was written but the orders were not flagged as being output to a file. Further exports will include these orders as well.</DIV>";
			}
			$sql = "UPDATE exported_orders_log SET " . strtolower($_GET['type']) . "_queue = 0";
			mysql_query($sql);
			checkDBError($sql);
			echo "<DIV align=center>Orders exported to file: <BR><A href='" . $filename . "'>" . $filename . "</A><BR>(Right-click and Save-as)</DIV>";
			echo "<BR><BR><DIV align=center><A href='javascript:window.close();'>Close Window</A></DIV>";
			echo "</body></html>";
		}
		else {
			echo "<DIV align=center>Could not write to file.</DIV>";
		}
	}
	elseif (!isset($_POST['submit']) && !($_GET['exportonly'] == "yes")) {
		$po = $_GET['po'];
		$po_id = $po - 1000;
		$dealer = $_GET['dealer'];
		$formvendor = $_GET['formvendor'];

		//-- Dealer
		if ($dealer != "") {
			$sql = "SELECT users." . $_GET['type'] . "_name FROM users, snapshot_users WHERE users.ID = snapshot_users.orig_id AND snapshot_users.ID=" . $dealer;
			$query = mysql_query($sql);
			checkDBError($sql);
			$result = mysql_fetch_row($query);
			$dealer = $result[0];
		}

		//-- formvendor
		if ($formvendor != "") {
			$sql = "select vendors.Access_name, vendors." . $_GET['type'] . "_type from vendors, snapshot_forms where snapshot_forms.ID=$formvendor AND snapshot_forms.orig_vendor = vendors.ID";
	//		$sql = "select Access_ from snapshot_forms where ID=$formvendor";
			$query = mysql_query($sql);
			checkDBError($sql);
			$result = mysql_fetch_row($query);
			$formvendor = $result[0];
			$vendorType = $result[1];
		}

		//-- Totals
		$sql = "SELECT ordered, total, type, freight_percentage, discount_percentage FROM order_forms WHERE ID='$po_id'";
		$query = mysql_query($sql);
		checkDBError($sql);
		if (mysql_num_rows($query) > 0) {
			$rows = mysql_fetch_array($query);
			$freight_percentage = $rows["freight_percentage"];
			$discount_percentage = $rows["discount_percentage"];
			$total = $rows['total'];
			$grandtotal = $total;
			$type = $rows['type'];
			if ($_GET['type'] == 'Access') {
				$formatted_date = DATE("m/d/Y");
			} else {
				$formatted_date = DATE("m/d/Y", strtotime($rows['ordered']));
			}
		}
                /*
		if (!(($type == "c") || ($type == "f"))) {
			$total = 0;
			$sql = "SELECT DISTINCT orders.setqty, orders.mattqty, orders.qty, snapshot_items.partno, snapshot_items.description, snapshot_items.price, snapshot_items.set_,	 snapshot_items.matt, snapshot_items.box, snapshot_items.header, snapshot_items.cubic_ft, snapshot_items.setqty as qtyinset FROM orders INNER JOIN snapshot_items ON snapshot_items.id=orders.item WHERE orders.po_id='".$po_id."' ORDER BY snapshot_items.header, snapshot_items.display_order";
			$query = mysql_query($sql);
			checkDBError($sql);
			while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
				if ($result['box'] != "")
					$price = $result['box'];
				else {
					$price = $result['price'];
					if ($price == "")
						$price = 0;
				}
				$price = str_replace("$", "", $price);
				$set = str_replace("$", "", $result['set_']);
				$matt = str_replace("$", "", $result['matt']);

				if($result['setqty'] != 0) {
					$total += round($set * $result['setqty'], 2);
				}
				if($result['mattqty'] != 0) {
					$total += round($matt * $result['mattqty'], 2);
				}
				if($result['qty'] != 0) {
					$total += round($price * $result['qty'], 2);
				}
			}
			$producttotal = $total;
			$discount = $producttotal * ($discount_percentage * .01);
			$discount = "-".$discount; //negative
			$total = $producttotal + $discount;
			$freight = $total * ($freight_percentage * .01);
			$grandtotal = $total + $freight;
		}
                */
		/*if ($_GET['type'] == "Access") {
			$total_select = "";
			$sub_select = "CHECKED";
		}
		elseif($_GET['type'] == "MAS90") {
			$total_select = "CHECKED";
			$sub_select = "";
		} */
                $total_select = "CHECKED";
		$sub_select = "";

		$onclick_string = "return confirm(\"Are you sure you want to export all the " . $_GET['type'] . " logs in the query?(Including this one)\");";
		?>
		<html>
		<head>
		<title> RSS Order - Log <?php echo $_GET['type'];?></title>
		<link rel="stylesheet" href="../styles.css" type="text/css">
		</head>
		<body>
		<FORM name='logging_form' id='logging_form' method='post' action='report-logAandM90.php?type=<?php echo $_GET['type'];?>'>
		<TABLE width='100%'>
		<TR><TD colspan=2 class="fat_black_12"><?php echo $_GET['type']; ?></TD></TR>
		<TR><TD class="fat_black_12">PO#</TD><TD class='text_12'><INPUT type='hidden' name='po' value="<?php echo $_GET['po']; ?>"><?php echo $_GET['po'];?></TD></TR>
		<!--<TR><TD class="fat_black_12">Dealer</TD><TD class='text_12'><INPUT type='hidden' name='dealer' value="<?php echo $dealer; ?>"><?php echo $dealer;?></TD></TR>-->
		<TR><TD class="fat_black_12">Dealer Name</TD><TD class='text_12'><INPUT size=35 type='text' name='name' value="<?php echo $dealer; ?>"></TD></TR>
		<TR><TD class="fat_black_12">Date</TD><TD class='text_12'><INPUT type='hidden' name='date' value="<?php echo $formatted_date;?>"><?php echo $formatted_date;?></TD></TR>
		<?php
			if ($_GET['type'] == "Access") {
		?>
		<TR><TD class="fat_black_12">Vendor</TD><TD class='text_12'><INPUT size=35 type='text' name='vendor' value="<?php echo $formvendor; ?>"></TD></TR>
		<?php
			}
		?>
		<TR><TD class="fat_black_12">Product</TD><TD class='text_12'>
				<SELECT id='product' name='product'>
				<?php
					if ($_GET['type'] == "Access") {
				?>
						<OPTION value='Bedding'>Bedding</OPTION><OPTION value='Case Goods'>Case Goods</OPTION>
				<?php
					}
					else {
				?>
						<OPTION value='BEDDING'>BEDDING</OPTION><OPTION value='CASE'>CASE</OPTION><OPTION value='DISC'>DISC</OPTION><OPTION value='ROYAL'>ROYAL</OPTION>

				<?php
					}
				?>
				</SELECT></TD></TR>
				<SCRIPT type='text/javascript'>
					document.getElementById('product').value = '<?php echo $vendorType; ?>';
				</SCRIPT>
		<!--- <TR><TD class="fat_black_12">Sub Total</TD><TD class='text_12'>$<?php echo number_format($total,2);?><INPUT type='radio' name='total_to_use' value='<?php echo $total;?>' <?php echo $sub_select;?>></TD></TR> --->
		<TR><TD class="fat_black_12">Total</TD><TD class='text_12'>$<?php echo number_format($grandtotal,2);?><INPUT type='radio' name='total_to_use' value='<?php echo $grandtotal;?>' <?php echo $total_select;?>></TD></TR>
		<TR><TD colspan=2 align=center><INPUT type='submit' name='submit' value='Submit'> <INPUT type='button' value='Cancel' onclick='window.close();'> <INPUT type='submit' name='submit' value='Export' onclick='<?php echo $onclick_string; ?>'></TD></TR>
		</TABLE>
		</FORM>
		<?php
	}
	elseif ($_GET['exportonly'] == "yes") {
		?>
		<html>
		<head>
		<title> RSS Order - Log <?php echo $_GET['type'];?></title>
		<link rel="stylesheet" href="../styles.css" type="text/css">
		</head>
		<body>
		<FORM name='logging_form' id='logging_form' method='post' action='report-logAandM90.php?type=<?php echo $_GET['type'];?>'>
		<INPUT type='hidden' name='exportonly' value='yes'>
		<DIV align=center>
			Export the <?php echo $_GET['type']; ?> orders in the queue?<BR>
			<INPUT type='button' value='Cancel' onclick='window.close();'> <INPUT type='submit' name='submit' value='Export'>
		</DIV>
		</FORM>
		<?php


	}



	function do_insert() {
		$eo_sql = "INSERT INTO exported_orders VALUES ('', " . $_POST['po'] . ", null, '" . addslashes($_POST['name']) . "', '" . addslashes($_POST['date']) . "', '" . addslashes($_POST['vendor']) . "', '" . addslashes($_POST['product']) . "', '" . addslashes(number_format($_POST['total_to_use'],2,'.','')) . "', '" . addslashes($_GET['type']) . "')";
		mysql_query($eo_sql) or die(mysql_error());
		checkDBError($eo_sql);
		$insert_id = mysql_insert_id();
		if ($_GET['type'] == "Access") {
			$eol_sql = "INSERT INTO exported_orders_log VALUES ('', " . ($_POST['po']-1000) . ", " . $insert_id . ", null, 1, 0) ON DUPLICATE KEY UPDATE access = $insert_id, access_queue = 1";
		}
		elseif ($_GET['type'] == "MAS90") {
			$eol_sql = "INSERT INTO exported_orders_log VALUES ('', " . ($_POST['po']-1000) . ", null, " . $insert_id . ", 0, 1) ON DUPLICATE KEY update mas90 = $insert_id, mas90_queue = 1";
		}
		mysql_query($eol_sql);
		checkDBError($eol_sql);
	}
?>
