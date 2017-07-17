<?php
// Back Order System Include
// backorder
//   id
//   date
//   form_id
//   user_id
//   address
//   canceled = 0
//   completed = 0
// backorder_item
//   id
//   backorder_id
//   item_id
//   qty
//   snapshot_id
//   canceled = 0
//   completed = 0 (non-zero = po_id)

function newbackorder($form_id, $items, $address = 1, $user_id = 0) {
	if (!$user_id) $user_id = $GLOBAL['userid'];
	// Data check
	if (!is_numeric($form_id)) die("Backorder: newbackorder: form_id is non-numeric");
	if (!is_numeric($user_id)) die("Backorder: newbackorder: user_id is non-numeric");
	if (!is_numeric($address)) die("Backorder: newbackorder: address is non-numeric");
	foreach ($items as $item => $qty) if ((!is_numeric($item)) ||(!is_numeric($qty))) die("Backorder: newbackorder: item_id or qty is non-numeric");
	// Process Data
	$sql = "INSERT INTO `backorder` (`date`, `form_id`, `user_id`, `address`) VALUES (NOW(), '".$form_id."', '".$user_id."','".$address."')";
	mysql_query($sql);
	checkDBerror($sql);
	$id = mysql_insert_id();
	foreach($items as $item => $qty) {
		$sql = "SELECT `snapshot` FROM `form_items` WHERE `ID` = '".$item."'";
		$result = mysql_query($sql);
		if ($result = mysql_fetch_assoc($result)) {
			$snap = $result['snapshot'];
		} else {
			echo("Warning: Unable to locate item #".$item." to add to backorder #".($id+1000).". Skipping.<br />\n");
			continue;
		}
		$sql = "INSERT INTO `backorder_item` (`backorder_id`,`item_id`,`snapshot_id`,`qty`) VALUES ('".$id."','".$item."','".$snap."','".$qty."')";
		mysql_query($sql);
		checkDBerror($sql);
	}
	// Return New Backorder #
	return $id + 1000;
}

function cancelbackorder($id) {
	if (!is_numeric($id)) die("Backorder: cancelbackorder: backorder id is non-numeric");
	$id = $id - 1000;
	// Set all uncompleted items to canceled
	$sql = "UPDATE `backorder_item` SET `canceled` = 1 WHERE `backorder_id` = '".$id."' AND `completed` = 0";
	mysql_query($sql);
	checkDBerror($sql);
	// Check for completed items
	$sql = "SELECT `id` FROM `backorder_item` WHERE `backorder_id` = '".$id."' AND `completed` != 0";
	$result = mysql_query($sql);
	checkDBerror($sql);
	if (mysql_num_rows($result)) {
		$sql = "UPDATE `backorder` SET `completed` = 1 WHERE `id` = '".$id."'";
		mysql_query($sql);
		checkDBerror($sql);
		if (mysql_affected_rows()) return true;
		else return false;
	} else {
		// Set to canceled since there are no completed items
		$sql = "UPDATE `backorder` SET `canceled` = 1 WHERE `id` = '".$id."'";
		mysql_query($sql);
		checkDBerror($sql);
		if (mysql_affected_rows()) return true;
		else return false;
	}
}

function cancelbackorderpart($id, $items) {
	if (!is_numeric($id)) die("Backorder: cancelbackorderpart: backorder id is non-numeric.");
	foreach ($items as $item) if (!is_numeric($item)) die("Backorder: cancelbackorderpart: item id is non-numeric.");
	if (count($items) == 0) die("Backorder: cancelbackorderpart: no items were passed to cancel");
	$id = $id - 1000;
	$items = implode(", ",$items);
	$sql = "UPDATE `backorder_item` SET `canceled` = 1 WHERE `backorder_id` =  '".$id."' AND `completed` = 0 AND `item_id` IN (".$items.")";
	mysql_query($sql);
	checkDBerror($sql);
	if (mysql_affected_rows() >= count($items)) $return = true;
	else $return = false;
	// Check to see if this completes the full backorder
	$sql = "SELECT `id` FROM `backorder_item` WHERE `backorder_id` = '".$id."' AND (`completed` = 0 AND `canceled` = 0)";
	$result = mysql_query($sql);
	checkDBerror($sql);
	if (!mysql_num_rows($result)) {
		$sql = "UPDATE `backorder` SET `completed` = 1 WHERE `id` = '".$id."'";
		mysql_query($sql);
		checkDBerror($sql);
	}
	return $return;
}

function completebackorder($id, $force = false) {
	if (!is_numeric($id)) die("Backorder: completebackorder: backorder id is non-numeric.");
	$id = $id - 1000;
	$sql = "SELECT `item_id`, `qty` FROM `backorder_item` WHERE `backorder_id` = '".$id."' AND `completed` = 0 AND `canceled` = 0";
	$result = mysql_query($sql);
	checkDBerror($sql);
	$items = array();
	while ($row = mysql_fetch_assoc($result)) {
		$items[$row['item_id']] = $row['qty'];
	}
	return completebackorderpart($id+1000,$items, $force);
}

/**
 * Force Backorder to Process into an order
 * @param int $id backorder id
 */
function pushbackorder($id) {
    completebackorder($id, true);
}

function completebackorderpart($id, $items, $force = false) {
	//print_r($items);
	if (!is_numeric($id)) die("Backorder: completebackorderpart: backorder id is non-numeric.");
	foreach ($items as $item => $qty) if ((!is_numeric($item)) ||(!is_numeric($qty))) die("Backorder: completebackorderpart: item_id or qty is non-numeric");
	$id = $id - 1000;
	require_once("inc_content.php");
	$sql = "SELECT `user_id`, `form_id`, `address` FROM `backorder` WHERE `id` = '".$id."' AND `canceled` = 0 AND `completed` = 0";
	$result = mysql_query($sql);
	checkDBerror($sql);
	if ($result = mysql_fetch_assoc($result)) {
		$user_id = $result['user_id'];
		$form_id = $result['form_id'];
		$address = $result['address'];
	} else {
		echo "Backorder #".($id+1000)." has already been completed or canceled";
		return false;
	}
	// Assemble order array
	$order = array();
	foreach ($items as $item => $qty) {
		$sql = "SELECT `snapshot_id` FROM `backorder_item` WHERE `backorder_id` = '".$id."' AND item_id = '".$item."' AND `canceled` = 0 AND `completed` = 0";
		$result = mysql_query($sql);
		checkDBerror($sql);
		if ($result = mysql_fetch_assoc($result)) {
			$orderitem = array();
			$orderitem['item_id'] = $item;
			$orderitem['qty'] = $qty;
			$orderitem['snapshot_id'] = $result['snapshot_id'];
			$order[] = $orderitem;
		}
	}

	$comment = "Backorder #".($id+1000);
	$po = submitOrder($user_id, $address, $comment, $form_id, $order, false, false, true, !$force); // Submit the order
	if (is_array($po)) {
		// Failed! But why?
		if (array_key_exists("no stock",$po["messages"])) {
			// Crappy... we can't really work around this.. let's just return and try again tomarrow
			return false;
		} elseif (array_key_exists("creditlimit",$po["messages"])) {
			// Credit Limit Problems!
			// TODO: Ping dealer with e-mail
			return false;
		} elseif (array_key_exists("noitems",$po["messages"])) {
			// Ack! This shouldn't be happening!
			return false;
		}
		return false; // Uncoded blockage.
	} elseif (is_numeric($po) && $po > 0) {
		// Success! Now we need to mark them all completed
		foreach ($items as $item => $qty) {
			$sql = "SELECT `id`, `qty`, `item_id`, `snapshot_id` FROM `backorder_item` WHERE `backorder_id` = '".$id."' AND `item_id` = '".$item."' AND `completed` = 0 AND `canceled` = 0";
			$result = mysql_query($sql);
			checkDBerror($sql);
			if ($result = mysql_fetch_assoc($result)) {
				if ($qty >= $result['qty']) {
					// This line is complete... time to update
					$sql = "UPDATE `backorder_item` SET `completed` = '".($po - 1000)."' WHERE `backorder_id` = '".$id."' AND `item_id` = '".$item."' AND `completed` = 0 AND `canceled` = 0";
					mysql_query($sql);
					checkDBerror($sql);
				} else {
					// Reduce qty of open line by amount we just ordered
					$sql = "UPDATE `backorder_item` SET `qty` = '".($result['qty'] - $qty)."' WHERE `id` = '".$result['id']."'";
					mysql_query($sql);
					checkDBerror($sql);
					// Create new closed row with qty ordered and po for our record keeping
					$sql = "INSERT INTO `backorder_item` (`backorder_id`,`item_id`, `qty`, `snapshot_id`, `completed`) VALUES ('".$id."','".$item."','".$qty."','".$result['snapshot_id']."','".$po."')";
					mysql_query($sql);
					checkDBerror($sql);
				}
			}
		}
		$sql = "SELECT `id` FROM `backorder_item` WHERE `completed` = 0 AND `canceled` = 0 AND `backorder_id` = '".$id."'";
		$result = mysql_query($sql);
		checkDBerror($sql);
		if (!mysql_num_rows($result)) {
			$sql = "UPDATE `backorder` SET `completed` = 1 WHERE `id` = '".$id."'";
			mysql_query($sql);
			checkDBerror($sql);
		}
		return $po;
	} else {
		return false;
	}
}

// Call this after a stock update of any sort
function bo_checkstock() {
	// Get open backorders
	$sql = "SELECT `id` FROM `backorder` WHERE `completed` = 0 AND `canceled` = 0 ORDER BY `date` ASC";
	$bo_result = mysql_query($sql);
	checkDBerror($sql);
	while ($bo = mysql_fetch_assoc($bo_result)) {
		//echo "Scanning BO#".($bo['id']+1000)."<br />\n";
		$sql = "SELECT `item_id`, `qty` FROM `backorder_item` WHERE `backorder_id` = '".$bo['id']."' AND `completed` = 0 AND `canceled` = 0";
		$bo_item_result = mysql_query($sql);
		checkDBerror();
		$order = array();
		while ($bo_item = mysql_fetch_assoc($bo_item_result)) {
			//echo "\t&nbsp;Scanning Item#".($bo_item['item_id'])."<br />\n";
			$sql = "SELECT `avail`, `stock` FROM `form_items` WHERE `ID` = '".$bo_item['item_id']."'";
			$fitem_result = mysql_query($sql);
			checkDBerror($sql);
			if ($form_item = mysql_fetch_assoc($fitem_result)) {
				// echo "\t&nbsp;\t&nbsp;Found Item.<br />\n";
				if ($form_item['avail'] == -1) {
					// echo "\t&nbsp;\t&nbsp;Avail -1 Stock ".$form_item['stock']."<br />\n";
					// Check Stock Status
					if (!stock_block($form_item['stock'])) {
						// echo "\t&nbsp;\t&nbsp;Ording Allowed<br />\n";
						$order[$bo_item['item_id']] = $bo_item['qty'];
					} else {
						// echo "\t&nbsp;\t&nbsp;Ording Disallowed<br />\n";
					}
				} elseif ($form_item['avail'] == 0) {
					// echo "\t&nbsp;\t&nbsp;Avail 0<br />\n";
					// OUT OF STOCK! We can't order it
				} elseif ($form_item['avail'] > 0) {
					// echo "\t&nbsp;\t&nbsp;Avail ".$form_item['avail']."<br />\n";
					if ($bo_item['qty'] >= $form_item['avail']) {
						$order[$bo_item['item_id']] = $form_item['avail'];
					} else {
						$order[$bo_item['item_id']] = $bo_item['qty'];
					}
				} else {
					//echo "\t&nbsp;\t&nbsp;ERROR<br />\n";
				}
			}
		}
		if ($order) {
			// We have items... let's order them now =)
			//echo "Submitting PO#";
			//echo 
			completebackorderpart($bo['id']+ 1000, $order);
			//echo "<br />\n";
		}
	}
}


// View Type = D (Dealer), V (Vendor), A (Admin), S (Super-Admin)
function viewbo($bo, $viewtype = "D", $showheader = true) {
	if (!is_numeric($bo)) die ("viewbo: Non-Numeric BoL ID");
	if ($viewtype != 'D' AND $viewtype != 'V' AND $viewtype != 'A' AND $viewtype != 'S') die("viewbo: Invalid Access Type");
	$bo_id = $bo - 1000;
	$sql = "SELECT date, user_id, address FROM `backorder` WHERE `id` = '".$bo_id."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	if ($result = mysql_fetch_assoc($result)) {
		$date = $result['date'];
                $address = $result['address'];
		if (($viewtype == 'D' || $viewtype == 'A' || $viewtype == 'S') && $showheader) {
			$user_id = $result['user_id']; // Only if we have a non-vendor viewing, don't need it otherwise
			$sql = "SELECT last_name, first_name, snapshot, snapshot2 FROM users WHERE ID = '".$user_id."'";
			$result = mysql_query($sql);
			if ($result = mysql_fetch_assoc($result)) {
				$name = $result['last_name'].", ".$result['first_name'];
                                if ($address == '1') {
                                        $address = $result['snapshot'];
                                } elseif ($address == '2') {
                                        $address = $result['snapshot2'];
                                } else {
                                        $address = $result['snapshot'];
                                }
			}
		}
	} else {
		die("Unable to locate backorder# ".$bo.".");
	}
	
	$sql = "SELECT id, item_id, snapshot_id, qty, completed, canceled FROM backorder_item WHERE `backorder_id` = '".$bo_id."'";
	$result = mysql_query($sql);
	checkDBerror($sql);
	// Load up Headers & Items (and get the form snapshot while we're at it)
	$headers = array();
	$totals = array();
	$totals['completed'] = 0;
	$totals['completedqty'] = 0;
	$totals['canceled'] = 0;
	$totals['canceledqty'] = 0;
	$totals['pending'] = 0;
	$totals['pendingqty'] = 0;
	$totals['grand'] = 0;
        $totals['cubic_ft'] = 0;
        $totals['seats'] = 0;
	$form = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$item = array();
		$item['id'] = $row['id'];
                $item['item_id'] = $row['item_id'];
		$item['completed'] = $row['completed'] ? $row['completed'] + 1000:0;
		$item['canceled'] = $row['canceled'];
		$item['qty'] = $row['qty'];
		$sql2 = "SELECT header, partno, description, price, box, display_order, cubic_ft, seats FROM `snapshot_items` WHERE `id` = '".$row['snapshot_id']."'";
		$result2 = mysql_query($sql2);
		checkDBerror($sql2);
		if ($result2 = mysql_fetch_assoc($result2)) {
			$item['partno'] = $result2['partno'];
			$item['description'] = $result2['description'];
			$item['price'] = $result2['box'] ? $result2['box'] : $result2['price'];
			$item['order'] = $result2['display_order'];
                        $item['cubic_ft'] = $result2['cubic_ft'];
                        $item['seats'] = $result2['seats'];
			$item['header_id'] = $result2['header'];
			if (!$headers[$result2['header']]) {
				$sql = "SELECT form, header, display_order FROM `snapshot_headers` WHERE `id` = '".$result2['header']."'";
				$result3 = mysql_query($sql);
				checkDBerror($sql);
				if ($result3 = mysql_fetch_assoc($result3)) {
					$header = array();
					$form = $result3['form'];
					$header['name'] = $result3['header'];
					$header['order'] = $result3['display_order'];
					$header['items'] = array();
					$headers[$result2['header']] = $header;
				} else {
					die("viewbo: Corupted Item Snapshot in BO#".$bo);
				}
			}
		} else {
			die("viewbo: Corupted Item Snapshot in BO#".$bo);
		}
		if ($item['canceled']) {
			$totals['canceled'] += $item['price'] * $item['qty'];
			++$totals['canceledqty'];
		} elseif ($item['completed']) {
			$totals['completed'] += $item['price'] * $item['qty'];
			++$totals['completedqty'];
		} else {
			 $totals['pending'] += $item['price'] * $item['qty'];
			++$totals['pendingqty'];
		}
		$totals['grand'] += $item['price'] * $item['qty'];
                $totals['cubic_ft'] += round($item['cubic_ft'] * $item['qty'], 2);
                $totals['seats'] += round($item['seats'] * $item['qty'], 0);
		$headers[$result2['header']]['items'][] = $item;
	}
	if ($showheader) {
                if ($address) {
                    /* Get Ship To Address */
                    $shipto_address = "";
                    $sql = "SELECT first_name, last_name, address, address2, city, state, zip, phone, fax, email FROM snapshot_users WHERE ID='$address'";
                    $query = mysql_query($sql);
                    checkDBError($sql);
                    if ($result = mysql_fetch_Array($query)) {
                            $shipto_address = $result['last_name'].", ".$result['first_name'];
                            if (secure_is_admin()||secure_is_dealer())
                                    $shipto_address .= " <strong>(".$user_id.")</strong>";
                            $shipto_address .= "<br>";
                            if($result['address']) { 
                                    $shipto_address .= $result['address']."<br>";
                                    if ($result['address2']) $shipto_address .= $result['address2']."<br>";
                                    $shipto_address .= $result['city'].", ".$result['state'].". ".$result['zip']."<br>"; 
                            }
                            if($result['email'] != "") { $shipto_address .= $result['email']."<br>"; }
                            if($result['phone'] != "") { $shipto_address .= "PH:".$result['phone']."<br>"; }
                            if($result['fax'] != "") { $shipto_address .= "FAX:".$result['fax']; }
                    }
                }
		if ($form) {
			$sql = "SELECT * FROM `snapshot_forms` WHERE id = '".$form."'";
			$result = mysql_query($sql);
			checkDBerror($sql);
			if ($result = mysql_fetch_assoc($result)) {
				$form = array();
				$form['name'] = $result['name'];
				$form['shipper'] = $result['shipper'];
				$form['address'] = $result['address'];
				$form['city'] = $result['city'];
				$form['state'] = $result['state'];
				$form['zip'] = $result['zip'];
				$form['phone'] = $result['phone'];
				$form['fax'] = $result['fax'];
			} else {
				die("Unable to locate vendor snapshot on BO#".$bo.".");
			}
		}
	}
	
	// Do all the display crap
	if ($showheader) {
?>
<table width="85%" border="0" align="center" cellpadding="5" cellspacing="0">
	  <tr>
		<td colspan="2"><h1><?php= $form['name'] ?></h1></td>
	  </tr>

	  <tr valign="top">
	  	<?php if ($viewtype == 'D' || $viewtype == 'A' || $viewtype == 'S') { ?>
		<td width="50%"> <p class="text_16"><b>Retail Service Systems Inc.</b><br><?php= $shipto_address ?></td>
		<?php } ?>
		<td width="50%">
			<p class="text_16">
				<b>Vendor:</b>
				<?php if ($form['address']) { ?>
					<br><?php= $form['address'] ?>
					<br><?php= $form['city'] ?>, <?php= $form['state'] ?> <?php= $form['zip'] ?><?php } ?>
				<?php if ($form['phone']) { ?>
					<br>PH:<?php= $form['phone'] ?>
				<?php }
				if ($form['fax']) { ?>
					<br>FAX:<?php= $form['fax'] ?>
				<?php } ?>
			</p>
		</td>

	  </tr>
	</table>
	<?php } ?>
	<?php if ($viewtype == 'V') { ?><h2 align="center">Do not deliver product until purchase order is submitted.</h2><?php } ?>
	<?php if ($viewtype == 'D') { ?><h2 align="center">Orders will be automatically submitted as backordered items become available.</h2><?php } ?>
	<h3 align="center">BO# <?php= $bo ?> Date: <?php echo date("m/d/Y", strtotime($date)); ?> Time: <?php echo date("g:i A", strtotime($date)); ?></h3>
        <?php if ($viewtype == 'A' || $viewtype == 'S') { ?>
        <form id="frm" name="frm" method="post" action="admin/report-backorder-push.php">
        <input type="hidden" name="bo" value="<?php= $bo ?>">
        <input type="hidden" name="action" id ="action" value="pushpart">
        <?php } ?>
	<table width="85%" border="0" align="center" cellpadding="5" cellspacing="0">
		  <tr>
                      <?php if ($viewtype == 'A' || $viewtype == 'S') { ?>
                      <td width="1%" class="orderTH"><input type="checkbox" name="checkall" value="NA" onchange="checkAll(this.checked, this.form)"></td>
                      <?php } else { ?>
                      <td width="1%" class="orderTH">&nbsp;</td>
                      <?php } ?>
		    <td width="5%" class="orderTH">Status</td>
			<td width="25%" colspan="2" class="orderTH">Item</td>
			<td width="20%" colspan="2" class="orderTH">Set</td>

			<td width="20%" colspan="2" class="orderTH">Matt</td>
			<td width="20%" colspan="2" class="orderTH">Box</td>
			<td width="2%" class="orderTH">&nbsp;</td>
			<td width="15%" class="orderTH" align="right">Total</td>
		  </tr>
		  <?php foreach ($headers as $header) { ?>
		  <tr><td class="orderTDheading">&nbsp;</td><td colspan="11" class="orderTDheading"><b><?php= $header['name'] ?></b></td></tr>
		  <?php foreach ($header['items'] as $item) { ?>
		  <tr valign="top">
				<?php if ($item['canceled']) { ?>
                                <td class="orderTD" style="color : #ff0000;">&nbsp;</td>
				<td class="orderTD" style="color : #ff0000;">
					Canceled
				</td>
				<?php } elseif ($item['completed']) { ?>
                                <td class="orderTD" style="color : #ff0000;">&nbsp;</td>
				<td class="orderTD" style="color : #000000;">
					Completed (<a href="viewpo.php?po=<?php= $item['completed'] ?>"><?php= $item['completed'] ?></a>)
				</td>
				<?php } else { ?>
                                <?php if ($viewtype == 'A' || $viewtype == 'S') { ?>
                                <td class="orderTD" style="color : #ff0000;"><input type="checkbox" name="item<?php= $item['item_id'] ?>" value="<?php= $item['qty'] ?>"></td>
                                <?php } else { ?>
                                <td class="orderTD" style="color : #ff0000;">&nbsp;</td>
                                <?php } ?>
				<td class="orderTD" style="color: #00cc00;">
					Pending
				</td>
				<?php } // end if/elseif/else ?>
				<td class="orderTD"><?php= $item['partno'] ?>&nbsp;</td>
				<td class="orderTD"><?php= $item['description'] ?>&nbsp;</td>
				<td colspan="4" class="orderTD">&nbsp;</td>
				<td class="orderTD"><?php= $item['qty'] ?></td>
				<td align="right" class="orderTD"><?php echo makethislooklikemoney($item['price']); ?></td>
				<td class="orderTD">&nbsp;</td>
				<td align="right" class="orderTD"><b><?php echo makethislooklikemoney($item['price']*$item['qty']); ?></b></td>
				</tr>
			<?php } ?>
			<?php } ?>
                <?php if ($totals['cubic_ft']) { ?>
                  <tr> 
			<td colspan="11" align="right" class="text_12">Approximate Volume:</td>
			<td class="text_12" align="right"><?php= $totals['cubic_ft'] ?> cu. ft.</td>
		  </tr>
                <?php } ?>
                <?php if ($totals['seats']) { ?>
                  <tr> 
			<td colspan="11" align="right" class="text_12">Seats:</td>
			<td class="text_12" align="right"><?php= $totals['seats'] ?></td>
		  </tr>
                <?php } ?>
		<?php if ($totals['completedqty']) { ?>
		  <tr> 
			<td colspan="11" align="right" class="text_12">Submitted Total:</td>
			<td class="text_12" align="right"><?php= makethislooklikemoney($totals['completed']) ?></td>
		  </tr>
		<?php } ?>
		<?php if ($totals['pendingqty']) { ?>
		  <tr>
                      <?php if ($viewtype == 'A' || $viewtype == 'S') { ?>
                        <td colspan="4" class="text_12"><input type="submit" value="Order" onclick="getElementById('action').value='pushpart'; return true;"><?php if ($viewtype == 'S') { ?><input type="button" value="Cancel" onclick="getElementById('action').value='cancelpart'; this.form.submit();"><?php } ?></td>
                      <?php } else { ?>
                        <td colspan="4" class="text_12">&nbsp;</td>
                      <?php } ?>
			<td colspan="7" align="right" class="text_12">Pending Total:</td>
			<td class="text_12" align="right"><?php= makethislooklikemoney($totals['pending']) ?></td>
		  </tr>
		<?php } ?>
		<?php if ($totals['canceledqty']) { ?>
		  <tr>
			<td colspan="11" align="right" class="text_12">Canceled Total:</td>
			<td class="text_12" align="right"><?php= makethislooklikemoney($totals['canceled']) ?></td>
		  </tr>
		<?php } ?>
		  <tr> 
			<td colspan="11" align="right" class="fat_black_12">BO Total:</td>
			<td class="fat_black_12" align="right"><?php= makethislooklikemoney($totals['grand']) ?></td>
		  </tr>
		 </table>
        <?php if ($viewtype == 'A' || $viewtype == 'S') { ?>
        </form>
        <?php } ?>
<?php
}

