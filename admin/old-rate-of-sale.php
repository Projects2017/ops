<?php
require("database.php");
require("secure.php");
require("menu.php");


?>

<SCRIPT type='text/javascript'>
function show_hide(divid) {
	if(document.getElementById("div_" + divid)) {
		var displaytype = (document.getElementById("div_" + divid).style.display == 'none') ? new Array('block', 'Hide') : new Array('none', 'Show');
		document.getElementById("div_" + divid).style.display = displaytype[0];
		document.getElementById("link_" + divid).innerHTML = displaytype[1] + " Details";
	}
}
</SCRIPT>
<B>Rate of Sale:</B><BR>
<FORM name='rate_form' method='get' action='rate-of-sale.php'>
Team: <SELECT name='team'><OPTION value=''>All Teams</OPTION>
<?php
$teams = teams_list();
foreach ($teams as $team) {
	echo "<OPTION value='" . $team . "'>Team " . $team . "</OPTION>\n";
}
?>
</SELECT>
<SCRIPT type='text/javascript'>document.rate_form.team.value = '<?php echo $_GET['team']; ?>';</SCRIPT>
<BR><BR>
Date Range:
<?php
$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');
?>
          <select name="m1">
            <?php
for ($x=1; $x <=12; $x++) {
	echo "<option value=\"$x\" selected>$monthName[$x]</option>";
}
?>
          </select>
          <select name="d1">
            <?php
for ($x=1; $x <=31; $x++) {
	echo "<option value=\"$x\">$x</option>";
}
?>
          </select>
          <select name="y1">
            <?php
for ($x=2002; $x <=2010; $x++) {
	echo "<option value=\"$x\">$x</option>";
}
?>
          </select>
          <b>to</b> 
          <select name="m2">
            <?php
for ($x=1; $x <=12; $x++) {
	echo "<option value=\"$x\">$monthName[$x]</option>";
}
?>
          </select>
          <select name="d2">
            <?php
for ($x=1; $x <=31; $x++) {
	echo "<option value=\"$x\">$x</option>";
}
?>
          </select>
          <select name="y2">
            <?php
for ($x=2002; $x <=2010; $x++) {
	echo "<option value=\"$x\">$x</option>";
}
?>
</SELECT>&nbsp;&nbsp;&nbsp;<INPUT type='submit' value='Filter Results'><BR><BR>
<?php
	 if (isset($_GET['vendor'])) {
		 //-- Then dates will have been passed through
		 ?>
			<SCRIPT type='text/javascript'>
				document.rate_form.m1.value = '<?php echo $_GET['m1'];?>';
				document.rate_form.d1.value = '<?php echo $_GET['d1'];?>';
				document.rate_form.y1.value = '<?php echo $_GET['y1'];?>';
				document.rate_form.m2.value = '<?php echo $_GET['m2'];?>';
				document.rate_form.d2.value = '<?php echo $_GET['d2'];?>';
				document.rate_form.y2.value = '<?php echo $_GET['y2'];?>';
			</SCRIPT>
		 <?php
	}
	else {
		$old_date = DATE("Y-m-d", strtotime("-30 days"));
		?>
			<SCRIPT type='text/javascript'>
				document.rate_form.m1.value = '<?php echo DATE("n", strtotime($old_date));?>';
				document.rate_form.d1.value = '<?php echo DATE("j", strtotime($old_date));?>';
				document.rate_form.y1.value = '<?php echo DATE("Y", strtotime($old_date));?>';
				document.rate_form.m2.value = '<?php echo DATE("n");?>';
				document.rate_form.d2.value = '<?php echo DATE("j");?>';
				document.rate_form.y2.value = '<?php echo DATE("Y");?>';
			</SCRIPT>
		<?php
	}
?>
Quantities: <INPUT type='checkbox' id='qSet' name='quantities[]' value='Set' CHECKED>Set Qty&nbsp;&nbsp;&nbsp;
			<INPUT type='checkbox' id='qMatt' name='quantities[]' value='Matt' CHECKED>Matt Qty&nbsp;&nbsp;&nbsp;
			<INPUT type='checkbox' id='qBox' name='quantities[]' value='Box' CHECKED>Box Qty&nbsp;&nbsp;&nbsp;
			<I>Selecting none of these will show only the total (Set + Matt + Box)</I>
<?php
	if (isset($_GET['team'])) {
		?>
			<SCRIPT type='text/javascript'>
				document.getElementById('qSet').checked = false;
				document.getElementById('qMatt').checked = false;
				document.getElementById('qBox').checked = false;
		<?php
		if (is_array($_GET['quantities'])) {
			foreach($_GET['quantities'] as $quant) {
				echo "document.getElementById('q" . $quant . "').checked = true;\n";
			}
		}
		?>
		</SCRIPT>
		<?php
	}
?>
<BR><BR>
Choose a vendor:
<SELECT name='vendor' onChange='if (typeof(document.rate_form.set_or_item) != "undefined") { document.rate_form.set_or_item.value = ""; } submit()'>
<OPTION value=''></OPTION>
<OPTION value=':ALL:'>All Vendors</OPTION>
<?php
	
	$query = "SELECT ID, name FROM vendors ORDER BY name";
	$results = mysql_query($query);
	checkDBError($query);
	while ($row = mysql_fetch_array($results)) {
		echo "<OPTION value='" . $row['ID'] . "'>" . $row['name'] . "</OPTION>\n";
	}
	echo "</SELECT><BR><BR>";
	//-- Grab the forms, or if there is only one, auto select it and keep this part hidden
	if ($_GET['vendor'] != '') {
		echo "<SCRIPT>document.rate_form.vendor.value = '" . $_GET['vendor'] . "';</SCRIPT>\n";
		if ($_GET['vendor'] != ":ALL:") {
			$and_vendor_id = " AND vendors.ID = '" . $_GET['vendor'] . "' ";
		}
		$query = "SELECT forms.ID, forms.name FROM vendors, forms WHERE " .
				 "vendors.ID = forms.vendor " . $and_vendor_id . 
				 "ORDER BY forms.name";
		$results = mysql_query($query);
		checkDBError($query);
		if (mysql_num_rows($results) == 1) {
			$row = mysql_fetch_array($results, MYSQL_ASSOC);
			$_GET['form'] = $row['ID'];
			echo "<B>Vendor only has one form: " . $row['name'] . "</B><BR><BR>";
			$one = TRUE;
		}
		elseif (mysql_num_rows($results) > 1) {
			echo "Choose a form: <SELECT name='form' onChange='submit();'>\n";
			echo "<OPTION value=''></OPTION>\n<OPTION value=':ALL:'>All Forms</OPTION>\n";
			while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
				echo "<OPTION value='" . $row['ID'] . "'>" . $row['name'] . "</OPTION>\n";
			}
			echo "</SELECT><BR><BR>\n";
		}
		else {
			echo "That vendor has no forms.<BR>";
			exit;
		}
	}
	//-- Grab the items/sets
	if ($_GET['form'] != '') {
		if (!$one) {
			echo "<SCRIPT>document.rate_form.form.value = '" . $_GET['form'] . "';</SCRIPT>\n";
		}
		echo "Choose a header/set: <SELECT name='set_or_item' onChange='if (this.value == \":ALL:\") { if (confirm(\"Are you sure you want to choose all? It can take quite some time to processs\")) { submit();}} else { submit();}'>\n";
		echo "<OPTION value=''></OPTION>\n<OPTION value=':ALL:'>All Sets/Items</OPTION>\n";
		if ($_GET['form'] != ":ALL:") {
			$and_form_id = " AND forms.ID = '" . $_GET['form'] . "' ";
		}
		// Get the items
		$query ="SELECT form_headers.ID as a_id, form_headers.header as a_header " . 
				"FROM vendors, forms, form_headers WHERE " . 
				"vendors.ID = forms.vendor " . $and_vendor_id . " AND " . 
				"forms.ID = form_headers.form " . $and_form_id . 
				"ORDER BY CAST(form_headers.header AS SIGNED), form_headers.header";
		$results = mysql_query($query);
		checkDBError($query);
		while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
			echo "<OPTION value='H:::" . $row['a_id'] . "'>" . $row['a_header'] . "</OPTION>\n";
		}
		echo "</SELECT>\n";
	}
	if ($_GET['set_or_item'] != '') {
		echo "<SCRIPT>document.rate_form.set_or_item.value = '" . $_GET['set_or_item'] . "';</SCRIPT>\n";
?>
		<BR><BR>
		<table border="0" cellspacing="0" cellpadding="3">
		<tr bgcolor="#fcfcfc"> 
			<td></TD>
			<?php
			if (is_array($_GET['quantities'])) {
				foreach($_GET['quantities'] as $quant) {
					echo '<td class="fat_black_12" align=center>' . $quant . ' Qty</td>' . "\n";
				}
			}
			if (count($_GET['quantities']) > 1 || !is_array($_GET['quantities'])) {
				echo '<td class="fat_black_12" align=center>Total</td>' . "\n";
			}
			?>
		</tr>
<?php
		$pieces = explode(":::", $_GET['set_or_item']);

		$divider = "header";
		$title_word = "Header: ";
		if ($pieces[0] == "H") {
			$query_add = " AND form_headers.ID = " . $pieces[1];
			$divider = "header";
			$title_word = "Header: ";
		}
		// -- Create the array of ids from the original id (needed to get all the orders)
		$id_sql = "SELECT vendors.name as vname, forms.name fname, form_items.ID as fid, snapshot_items.ID as sid, form_items.set_, form_items.description, form_headers.header, form_items.partno " . 
				  " FROM (form_items, form_headers, forms, vendors) LEFT JOIN snapshot_items ON (snapshot_items.orig_id = form_items.ID) " . 
				  " WHERE form_headers.ID = form_items.header AND form_headers.form = forms.ID AND forms.vendor = vendors.ID " . $and_vendor_id . $and_form_id . 
				  $query_add . " ORDER BY form_headers.header, form_items.partno, form_items.set_, form_items.description, form_items.ID";
		$results = mysql_query($id_sql); // or die(mysql_error());
		checkDBerror($id_sql);
		$info_array = array();
		while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
			if (array_key_exists($row['fid'], $info_array)) {
				if ($row['sid'] != ""  && $row['sid'] != null) {
					$info_array[$row['vname']][$row['fname']][$row[$divider]][$row['fid']]['o_ids'][] = $row['sid'];
				}
			}
			else {
				$info_array[$row['vname']][$row['fname']][$row[$divider]][$row['fid']]['o_ids'][] = $row['fid'];
				if ($row['sid'] != "" && $row['sid'] != null) {
					$info_array[$row['vname']][$row['fname']][$row[$divider]][$row['fid']]['o_ids'][] = $row['sid'];
				}
				$info_array[$row['vname']][$row['fname']][$row[$divider]][$row['fid']]['description'] = $row['description'];
				$info_array[$row['vname']][$row['fname']][$row[$divider]][$row['fid']]['header'] = $row['header'];
				$info_array[$row['vname']][$row['fname']][$row[$divider]][$row['fid']]['partno'] = $row['partno'];
			}
		}
		//-- Now for each item in the info array we have to grab the purchases made
		$from_date = DATE("Y-m-d", strtotime($_GET['y1'] . "-" . $_GET['m1'] . "-" . $_GET['d1']));
		$to_date = DATE("Y-m-d", strtotime($_GET['y2'] . "-" . $_GET['m2'] . "-" . $_GET['d2']));
		if ($_GET['team'] != "") {
			$and_team_selected = " AND users.team = '" . $_GET['team'] . "' ";
		}

		$po_ids = array();
		foreach($info_array as $vendor => $vvalue) {
			foreach($vvalue as $form => $fvalue) {
				foreach($fvalue as $set => $svalue) {
					foreach($svalue as $item => $ivalue) {
						//-- Breaking it here seems to make it faster, maybe
						$query = "SELECT DISTINCT(order_forms.ID) FROM order_forms, orders WHERE order_forms.processed = 'Y' AND order_forms.deleted != 1 and order_forms.type = 'o' and order_forms.total > 0 and order_forms.ID = orders.po_id and orders.item IN (" . implode(", ", $ivalue['o_ids']) . ")";
						$results = mysql_query($query);
						checkDBerror($query);
						if (mysql_num_rows($results) > 0) {
							$po_ids = array();
							while ($line = mysql_fetch_row($results)) {
								$po_ids[] = $line[0];
							}
							$query = "SELECT last_name, po_id, SUM(orders.setqty) as setqty, SUM(orders.mattqty) as mattqty, SUM(qty) as qty " . 
									 " FROM orders, users WHERE orders.po_id IN (" . implode(", ", $po_ids) . ") " . 
									 " AND orders.item IN (" . implode(", ", $ivalue['o_ids']) . ") AND orders.user = users.ID " . $and_team_selected . 
									 " AND orders.ordered BETWEEN '" . $from_date . "' AND '" . $to_date . "' GROUP BY orders.user";

									/*$query = "SELECT last_name, SUM(orders.setqty) as setqty, SUM(orders.mattqty) as mattqty, SUM(qty) as qty " . 
									 " FROM orders, order_forms, users WHERE orders.po_id = order_forms.ID AND order_forms.deleted != 1 " . 
									 " AND orders.item IN (" . implode(", ", $ivalue['o_ids']) . ") AND orders.user = users.ID " . $and_team_selected . 
									 " AND orders.ordered BETWEEN '" . $from_date . "' AND '" . $to_date . "' GROUP BY orders.user";
									 */
							//echo $query . "<BR><BR>";
							$results = mysql_query($query); // or die(mysql_error());
							checkDBerror($query);
							while($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
								$info_array[$vendor][$form][$set][$item]['dealers'][$row['last_name']]['setqty'] = $row['setqty'];
								$info_array[$vendor][$form][$set][$item]['dealers'][$row['last_name']]['mattqty'] = $row['mattqty'];
								$info_array[$vendor][$form][$set][$item]['dealers'][$row['last_name']]['qty'] = $row['qty'];
								$info_array[$vendor][$form][$set][$item]['dealers'][$row['last_name']]['total'] = $row['setqty'] + $row['mattqty'] + $row['qty'];

							}
						}
					}
				}
			}
		}
		//-- Output the results
		$div_id = 0;
		$title_to_db_name = array("Set" => "setqty", "Matt" => "mattqty", "Box" => "qty");
		foreach($info_array as $vendor => $vvalue) {
			if ($_GET['vendor'] == ":ALL:") {
				echo "<TR bgcolor='#669900'><TD class='fat_black_12' colspan=5>Vendor: " . $vendor . "</TD></TR>\n";
			}
			foreach($vvalue as $form => $fvalue) {
				echo "<TR bgcolor='#BB5555'><TD class='fat_black_12' colspan=5>Form: " . $form . "</TD></TR>\n";
				foreach($fvalue as $set => $svalue) {
					if ($set != "" && $set != null) {
						echo "<TR bgcolor='#0099FF'><TD class='fat_black_12' colspan=5>$title_word" . $set . "</TD></TR>\n";
					}
					foreach($svalue as $item => $ivalue) {
						$totals['setqty'] = 0;
						$totals['mattqty'] = 0;
						$totals['qty'] = 0;
						$item_total = 0;
						$dealers_found = FALSE;
						if (count($ivalue['dealers']) > 0) {
							$dealers_found = TRUE;
							$div_id++;
						}
						echo "<TR bgcolor='orange'>\n" . 
							"<TD class='fat_black_12' colspan=5>Item: " . $ivalue['partno'] . " - " . $ivalue['description'];
						if ($dealers_found) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp; <A id='link_" . $div_id . "' href='javascript:show_hide(\"" . $div_id . "\")'>Show Details</A>";
						}
						echo "</TD></TR>\n";
						if ($dealers_found) {
							echo "<TBODY id='div_" . $div_id . "' style='display:none'>\n";
							foreach($ivalue['dealers'] as $dealer => $dvalue) {
								$out_line = "<TR>\n";
								$out_line .= "  <TD align=right>" . $dealer . "</TD>\n";
								$cust_total = 0;
								if (is_array($_GET['quantities'])) {
									foreach($_GET['quantities'] as $quant) {
										$out_line .= "  <TD align=center>" . $dvalue[$title_to_db_name[$quant]] . "</TD>\n";		
										$cust_total += $dvalue[$title_to_db_name[$quant]];
										$item_total += $dvalue[$title_to_db_name[$quant]];
										$totals[$title_to_db_name[$quant]] += $dvalue[$title_to_db_name[$quant]];
									}
								}
								else {
									$cust_total = $dvalue['total'];
									$item_total += $dvalue['total'];
								}
								if (count($_GET['quantities']) > 1 || !is_array($_GET['quantities'])) {
									$out_line .= "  <TD align=center>" . $cust_total . "</TD>\n";
								}
								if ($cust_total > 0) {
									echo $out_line;
								}
							}
							echo"</TBODY>\n";

						}
						if (($item_total) > 0) {
							echo "<TR>\n";
							echo "  <TD class='fat_black_12' align=right>Total:</TD>\n";
							if (is_array($_GET['quantities'])) {
								foreach($_GET['quantities'] as $quant) {
									echo "  <TD class='fat_black_12' align=center>" . $totals[$title_to_db_name[$quant]] . "</TD>\n";
								}
							}
							if (count($_GET['quantities']) > 1 || !is_array($_GET['quantities'])) {
								echo "  <TD class='fat_black_12' align=center>" . $item_total . "</TD>\n";
							}
							echo "</TR>";
						}
						else {
							echo "<TR><TD colspan=5><I>None were sold within that time frame</I></TD></TR>\n";
							//echo "<SCRIPT>document.getElementById('link_" . $div_id . "').innerHTML = '';</SCRIPT>\n";
						}
					}
				}
			}
		}
?>
		</table>
<?php
	}
?>
