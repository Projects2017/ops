<?php
require("database.php");
require("secure.php");
require("../inc_orders.php");

$tablename = "form_items";
$self = $_SERVER['PHP_SELF'];

$fields = Array();
DBcreateFields($tablename, $fields, "<tr><td class=\"text_12\">[TITLE]: </td><td>", "</td></tr>");

if (isset($_GET['header']))
	$header = $_GET['header'];
elseif (isset($_POST['header']))
	$header = $_POST['header'];
else
	die('header not set');
if (!is_numeric($header))
	die('header not a number');
$sql = "SELECT form FROM form_headers WHERE ID=${header}";
$query = mysql_query($sql);
checkDBError($sql, true, __FILE__, __LINE__);
$result = mysql_fetch_array($query, MYSQL_ASSOC);
if ($result)
	$form_idx = $result['form'];
else
	$form_idx = 0;

if (!isset($_POST['enable_alloc'])) {
	$_POST['alloc'] = -1;
	$alloc = -1;
	$_POST['avail'] = -1;
	$avail = -1;
} elseif ($_POST['avail'] != $_POST['original_avail']) {
	$_POST['alloc'] = $_POST['avail'];
	$alloc = $_POST['avail'];
	if ($_POST['avail'] >= 1) {
		$_POST['stock'] = 1;
		$stock = 1;
		$_POST['stock_day'] = 0;
		$stock_day = 0;
	} else {
		$_POST['stock'] = 2;
		$stock = 2;
		$_POST['stock_day'] = 0;
		$stock_day = 0;
	}
} else {
	$alloc = $_POST['original_alloc'];
	$_POST['alloc'] = $_POST['original_alloc'];
	$_POST['stock'] = $_POST['original_stock'];
    $_POST['stock_day'] = $_POST['original_stock_day'];
}

if ($_POST['stock'] != $_POST['original_stock']) {
	$status = stock_status($_POST['stock']);
	if ($status['zeroday'] == 'Y') {
		$_POST['stock_day'] = 0;
		$stock_day = 0;
	}
	unset($status);
}

$action = strtolower($action);
switch($action)
{
case "update":
	$sql = "SELECT `header`, `partno`, `description`, `price`, `cost`, `size`, `color`, `set_`, `set_cost`, `matt`, `matt_cost`, `box`, `box_cost`, `display_order`, `cubic_ft`,`seats`, `weight`, `snapshot`, `setqty`, `sku`, `item_tier_override` FROM form_items WHERE ID = '".$ID."'";
	$query = mysql_query($sql);
	checkDBError($sql, true, __FILE__, __LINE__);
	if ($result = mysql_fetch_array($query)) {
		$snap1up = 0;
		// If First and Last Name Change then both snaps update
		if ($header != $result['header']) {
			$snap1up = 1;
		} elseif ($partno != $result['partno']) {
			$snap1up = 1;
		} elseif ($description != $result['description']) {
			$snap1up = 1;
		} elseif ($price != $result['price']) {
			$snap1up = 1;
		} elseif ($cost != $result['cost']) {
			$snap1up = 1;
                } elseif ($size != $result['size']) {
			$snap1up = 1;
		} elseif ($color != $result['color']) {
			$snap1up = 1;
		} elseif ($set_ != $result['set_']) {
			$snap1up = 1;
                } elseif ($set_cost != $result['set_cost']) {
			$snap1up = 1;
		} elseif ($matt != $result['matt']) {
			$snap1up = 1;
                } elseif ($matt_cost != $result['matt_cost']) {
			$snap1up = 1;
		} elseif ($box != $result['box']) {
			$snap1up = 1;
                } elseif ($box_cost != $result['box_cost']) {
			$snap1up = 1;
		} elseif ($display_order != $result['display_order']) {
			$snap1up = 1;
		} elseif ($cubic_ft != $result['cubic_ft']) {
			$snap1up = 1;
		} elseif ($seats != $result['seats']) {
			$snap1up = 1;
		} elseif ($weight != $result['weight']) {
			$snap1up = 1;
		} elseif ($setqty != $result['setqty']) {
			$snap1up = 1;
		} elseif ($sku != $result['sku']) {
			$snap1up = 1;
		} elseif ($item_tier_override != $result['item_tier_override']) {
			$snap1up = 1;
		}

		// Update Snap 1
		if ($snap1up) {
			$sql = "SELECT snapshot FROM form_headers WHERE ID = '".$header."'";
			$query = mysql_query($sql);
			checkDBError($sql, true, __FILE__, __LINE__);
			if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
				$snapshot_header = $result['snapshot'];
			}
			$sql = "INSERT INTO snapshot_items VALUES (NULL, '".$ID."', '".$snapshot_header."', '".$partno."', '".$description."', '".$price."', '".$cost."', '".$size."', '".$color."', '".$set_."', '".$set_cost."', '".$matt."', '".$matt_cost."', '".$box."', '".$box_cost."', '".$display_order."', '".$cubic_ft."','".$seats."','".$weight."','".$setqty."', '".$sku."', '".$item_tier_override."')";
			mysql_query($sql);
			$snapshot = mysql_insert_id();
			checkDBError($sql, true, __FILE__, __LINE__);
		} else {
			$snapshot = $result['snapshot'];
		}
	}
	/* End Snapshot modification */
	if ($cubic_ft) $cubic_ft_null = 'N';
    if (!isset($_POST['item_tier_override']) || !$_POST['item_tier_override']) {
        $_POST['item_tier_override'] = 0;
    }
	$sql = buildUpdateQuery($tablename, "ID=$ID");
	//echo "<p>".$sql."</p>";
	mysql_query($sql);
	checkDBError($sql, true, __FILE__, __LINE__);

	//only super admins allowed to update fields - addded by goody 10/20/04 2:45pm PST
	/* Removed per task #116 --Will 9-7-2005
	if ($security != "S") {
		require ("menu.php");
		echo "&nbsp;<BR>&nbsp;<BR><CENTER><FONT COLOR=RED>Sorry, only Super Admins are able to update fields. Please choose from the menu above.</FONT></CENTER>";
	echo "</TD></TR></TABLE></BODY></HTML>";
	exit();
	} */ //and now back to our regularly scheduled programming....



	if ($delete_photo == "y") {
		if (file_exists($basedir."photos/".$ID.".jpg")) unlink($basedir."photos/".$ID.".jpg");
		if (file_exists($basedir."photos/t".$ID.".jpg")) unlink($basedir."photos/t".$ID.".jpg");
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
		 values($ID,$userid,".date("Ymd").",'item photo was deleted',${form_idx})";
		mysql_query($sql);
		checkDBError($sql, true, __FILE__, __LINE__);
	}

	if (file_exists($photo)) {
		if (file_exists($basedir."photos/".$ID.".jpg")) unlink($basedir."photos/".$ID.".jpg");
		if (file_exists($basedir."photos/t".$ID.".jpg")) unlink($basedir."photos/t".$ID.".jpg");

		move_uploaded_file($photo, $basedir."photos/".$ID.".jpg");
		createThumb($ID.".jpg");
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
		 values($ID,$userid,".date("Ymd").",'item photo was uploaded',${form_idx})";
		mysql_query($sql);
		checkDBError($sql, true, __FILE__, __LINE__);
	}

	$fieldsarray = Array("partno","sku","description","price","cost","markup","size","color","set_","set_cost","set_markup","matt","matt_cost","matt_markup","box","box_cost","box_markup","cubic_ft","seats", "weight", "stock", "alloc", "avail", "stock_day", "setqty", "item_tier_override");
	$optional = array('sku','cost','markup','set_','set_cost','matt','matt_cost','box','box_cost','numinset');
	foreach($fieldsarray as $field) {
		$ofield = "original_${field}";
		if (isset($_POST[$field]) && isset($_POST[$ofield])) {
			$oldvalue = $_POST[$ofield];
			$newvalue = $_POST[$field];
			if ($newvalue <> $oldvalue) {
				$action = "$field was changed from \"$oldvalue\" to \"$newvalue\"";
				$sql = "insert into form_changes (form_item_id,user,date,action,form)
				 values($ID,$userid,".date("Ymd").",'$action',${form_idx})";
				mysql_query($sql);
				checkDBError($sql, true, __FILE__, __LINE__);
			}
		} else {
			if (!in_array($field,$optional)) {
				print_r($_POST);
				die("Field missing: ${field}");
			}
		}
	}

        saveDiscount($discount,'discount',array("item_id" => $ID),"form_item");
        saveDiscount($freight,'freight',array("item_id" => $ID),"form_item");

	header("Location: form-edit.php?ID=${form_idx}");
	exit;
break;

case "delete":
	/* Update Snapshots so they reflect a deleted user */
	$sql = "update snapshot_items SET orig_id = '0' WHERE orig_id = '".$ID."'";
	mysql_query($sql);
	checkDBError($sql);
	/* End Snapshot Modification */
	$sql = "delete from $tablename where ID=$ID";
	mysql_query( $sql );
	checkDBError($sql, true, __FILE__, __LINE__);
        deleteDiscount('discount',array("item_id" => $ID),"form_item");
        deleteDiscount('freight',array("item_id" => $ID),"form_item");

	header("Location: form-edit.php?ID=${form_idx}");
	exit;
break;

case "create":
	$sql = "SELECT snapshot FROM form_headers WHERE ID = '".$header."'";
	$query = mysql_query($sql);
	checkDBError($sql, true, __FILE__, __LINE__);
	if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$snapshot_header = $result['snapshot'];
	}

	$sql = "INSERT INTO snapshot_items VALUES (NULL, 0, '".$snapshot_header."', '".$partno."', '".$description."', '".$price."', '".$cost."', '".$size."', '".$color."', '".$set_."','".$set_cost."',  '".$matt."','".$matt_cost."',  '".$box."','".$box_cost."',  '".$display_order."','".$cubic_ft."','".$seats."','".$weight."','".$setqty."','".$sku."','".$item_tier_override."')";
	mysql_query($sql);
	$snapshot = mysql_insert_id();
	checkDBError($sql, true, __FILE__, __LINE__);

	$sql = buildInsertQuery($tablename);
	mysql_query($sql);
	checkDBError($sql, true, __FILE__, __LINE__);
	$ID = mysql_insert_ID();
	//passes through to the "view" case

	/* Update Snapshots so they reflect new user */
	$sql = "update snapshot_items SET orig_id = '".$ID."' WHERE id = '".$snapshot."'";
	mysql_query($sql);
	checkDBError($sql, true, __FILE__, __LINE__);
	/* End Snapshot Modification */

	if (file_exists($photo)) {
		if (file_exists($basedir."photos/".$ID.".jpg"))
			unlink($basedir."photos/".$ID.".jpg");
		move_uploaded_file($photo, $basedir."photos/".$ID.".jpg");
		createThumb($ID.".jpg");
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
		 values($ID,$userid,".date("Ymd").",'item photo was uploaded',${form_idx})";
		mysql_query($sql);
		checkDBError($sql, true, __FILE__, __LINE__);
	}
        saveDiscount($discount,'discount',array("item_id" => $ID),"form_item");
        saveDiscount($freight,'freight',array("item_id" => $ID),"form_item");
	if ($addanother) {
		header("Location: form-item-add.php?header=${header}&addanother=Y");
	} else {
		header("Location: form-edit.php?ID=${form_idx}");
	}
	exit;
break;

default:
	require("menu.php");

	if ($ID == "") {
		$action = "create";
		$setqty = 2;
		$set_ = 0;
		$box = 0;
		$matt = 0;
	} else {
		$sql = "select * from $tablename where ID=$ID";
		$query = mysql_query($sql);
		checkDBError($sql, true, __FILE__, __LINE__);
		$action = "update";

		if ($result = mysql_fetch_array($query))
		{
			$partno = $result['partno'];
			$sku = $result['sku'];
            $item_tier_override = $result['item_tier_override'];
			$description = $result['description'];
			$price = $result['price'];
                        $cost = $result['cost'];
                        $markup = $result['markup'];
			$size = $result['size'];
			$numinset = $result['numinset'];
                        $discount=loadDiscount('discount',array("item_id" => $ID),"form_item");
                        $freight=loadDiscount('freight',array("item_id" => $ID),"form_item");
			$color = $result['color'];
			$set_ = $result['set_'];
			$markup = $result['markup'];
                        $set_cost = $result['set_cost'];
												$set_markup = $result['set_markup'];
			$setqty = $result['setqty'];
			$matt = $result['matt'];
                        $matt_cost = $result['matt_cost'];
												$matt_markup = $result['matt_markup'];
			$box = $result['box'];
                        $box_cost = $result['box_cost'];
												$box_markup = $result['box_markup'];
			$cubic_ft = $result['cubic_ft'];
                        $seats = $result['seats'];
			$weight = $result['weight'];
			$alloc = $result['alloc'];
			$avail = $result['avail'];
			$display_order = $result['display_order'];
			$stock = $result['stock'];
			$stock_day = $result['stock_day'];
			if ($alloc == "" || $alloc < 0) {
				$alloc = "";
				$avail = "";
				$alloc_enable = false;
			} else
				$alloc_enable = true;
		}
	}
?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
<br>

	<form action="<?php echo $self; ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="header" value="<?php echo $header; ?>">
	<input type="hidden" name="ID" value="<?php echo $ID; ?>">

  <table border="0" cellspacing="3" cellpadding="2">
    <tr>
      <td align="right" class="text_12"><b>Part Number:</b></td>
      <td><input type="text" name="partno" value="<?php echo $partno; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>SKU:</b></td>
      <td><input type="text" name="sku" value="<?php echo $sku; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Description:</b></td>
      <td><input type="text" name="description" value="<?php echo $description; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Price:</b></td>
      <td><input type="text" name="price" value="<?php echo $price; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Cost:</b></td>
      <td><input type="text" id="cost" name="cost" value="<?php echo $cost; $field = "cost"; ?>"<?php if (is_null($cost)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($cost)) echo "CHECKED"; ?>></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Markup:</b></td>
      <td><input type="text" id="markup" name="markup" value="<?php echo $markup; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Size:</b></td>
      <td><input type="text" name="size" value="<?php echo $size; ?>"></td>
    </tr>
	<tr>
	  <td align="right" class="text_12"><b># in Header Set:</b></td>
	  <td><input type="text" id="numinset" name="numinset" value="<?php echo $numinset; ?>"></td>
	</tr>
	<tr>
	  <td align="right" class="text_12"><b>Item Discount</b></td>
	  <td>
              <input type="text" id="discount" name="discount" value="<?php echo $discount; ?>">
              <a href="" onclick="toggleHelp('discount'); return false;">[Help]</a>
          </td>
	</tr>
        <tr id="help_discount" style="display: none">
            <td>&nbsp;</td>
            <td class="text_12" colspan="1">
                <div style="width: 300px">
                <h3>Item Discounts</h3>
                <p>
                    Discounts may be applied two ways, percentages or dollars. To apply a percentage
                    discount, simply append a % after the number. To apply a dollar discount, prepend the number with a dollar sign ($)
                </p>
                <h3>Inheritance</h3>
                <p>ANY item discount (including $0 or 0%) will cause their totals to be excluded when calculating the overall form/vendor discount.</p>
                <h3>Tier Discounts</h3>
                <p>Tier Discounts are in the form of from:to:discount with each tier seperated by a semi-colon (;).</p>
                <p>You may apply a discount regardless of qty, by simply providing a percentage
                or $ amount. You may also provide a from but no two (i.e. 2:25% minimum 2 items
                to reach discount of 25%). All discounts tiers are applied in order. So if you
                have a global first, then a more specific second, and it applies to both, it will
                use the last one.</p>
                <p>For example 25%;2:5:30%;6:35%
                <ol>
                    <li>Application will be against 25%, so the order of the item will always be 25% off.</li>
                    <li>Will only apply to quantities between 2 and 5. If these apply, the item deiscount percentage will be 30%</li>
                    <li>If the quantity ordered is above 6, it will override any previous discount percentage with 35%</li>
                </ol>
                <h3>Reading Quantity</h3>
                </p>
                <p>Item Discounts count the sum of the box and matt and add the product of quantity in set multiplied by the set quantity.</p>
                <a href="" onclick="toggleHelp('discount'); return false;">(close help)</a>
                </div>
            </td>
        </tr>
        <tr>
	  <td align="right" class="text_12"><b>Item Freight</b></td>
	  <td>
              <input type="text" id="freight" name="freight" value="<?php echo $freight; ?>">
              <a href="" onclick="toggleHelp('freight'); return false;">[Help]</a>
          </td>
	</tr>
        <tr id="help_freight" style="display: none">
            <td>&nbsp;</td>
            <td class="text_12" colspan="1">
                <div style="width: 300px">
                <h3>Item Freight</h3>
                <p>
                    Freight may be applied two ways, percentages or dollars. To apply a percentage, simply append
                    a % after the number. To apply a dollar amount, prepend the number with a dollar sign ($)
                </p>
                <h3>Inheritance</h3>
                <p>ANY item freight (including $0 or 0%)  will cause their totals to be excluded when calculating the overall form/vendor freight.</p>
                <h3>Tiered Freight</h3>
                <p>Tier Freight are in the form of from:to:freight with each tier seperated by a semi-colon (;).</p>
                <p>You may apply a freight regardless of qty, by simply providing a percentage
                or $ amount. You may also provide a from but no two (i.e. 2:25% minimum 2 items
                to reach discount of 25%). All freight tiers are applied in order. So if you
                have a global first, then a more specific second, and it applies to both, it will
                use the last one.</p>
                <p>For example 35%;2:5:30%;6:25%
                <ol>
                    <li>Application will be against 35%, so the order of the item will always be 35% if not otherwise determined.</li>
                    <li>Will only apply to quantities between 2 and 5. If these apply, the item freight percentage will be 30%</li>
                    <li>If the quantity ordered is above 6, it will override any previous freight percentage with 25%</li>
                </ol>
                <h3>Reading Quantity</h3>
                </p>
                <p>Item freight counts the sum of the box and matt and add the product of quantity in set multiplied by the set quantity.</p>
                <a href="" onclick="toggleHelp('freight'); return false;">(close help)</a>
                </div>
            </td>
        </tr>
    <tr>
      <td align="right" class="text_12"><b>Color:</b></td>
      <td><input type="text" name="color" value="<?php echo $color; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Set Price:</b></td>
      <td><input type="text" id="set_" name="set_" value="<?php echo $set_; $field = "set_" ?>"<?php if (is_null($set_)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($set_)) echo "CHECKED"; ?>></td>
    </tr>
		<tr>
      <td align="right" class="text_12"><b>Set Markup:</b></td>
      <td><input type="text" id="set_markup" name="set_markup" value="<?php echo $set_markup; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Set Cost:</b></td>
      <td><input type="text" id="set_cost" name="set_cost" value="<?php echo $set_cost; $field = "set_cost" ?>"<?php if (is_null($set_cost)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($set_cost)) echo "CHECKED"; ?>></td>
    </tr>
    </tr>
	<tr>
	  <td align="right" class="text_12"><b>Qty in Set:</b></td>
	  <td><input type="text" name="setqty" value="<?php echo $setqty; ?>"></td>
	</tr>
    <tr>
      <td align="right" class="text_12"><b>Matt Price:</b></td>
      <td><input type="text" id="matt" name="matt" value="<?php echo $matt; $field = "matt"; ?>"<?php if (is_null($matt)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($matt)) echo "CHECKED"; ?>></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Matt Cost:</b></td>
      <td><input type="text" id="matt_cost" name="matt_cost" value="<?php echo $matt_cost; $field = "matt_cost"; ?>"<?php if (is_null($matt_cost)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($matt_cost)) echo "CHECKED"; ?>></td>
    </tr>
		<tr>
      <td align="right" class="text_12"><b>Matt Markup:</b></td>
      <td><input type="text" id="matt_markup" name="matt_markup" value="<?php echo $matt_markup; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Box Price:</b></td>
      <td><input type="text" id="box" name="box" value="<?php echo $box; $field = "box"; ?>"<?php if (is_null($box)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($box)) echo "CHECKED"; ?>></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Box Cost:</b></td>
      <td><input type="text" id="box_cost" name="box_cost" value="<?php echo $box_cost; $field = "box_cost"; ?>"<?php if (is_null($box_cost)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($box_cost)) echo "CHECKED"; ?>></td>
    </tr>
		<tr>
      <td align="right" class="text_12"><b>Box Markup:</b></td>
      <td><input type="text" id="box_markup" name="box_markup" value="<?php echo $box_markup; ?>"></td>
    </tr>
	<tr>
	  <td align="right" class="text_12"><b>Volume:</b></td>
	  <td><input type="text" name="cubic_ft" value="<?php echo $cubic_ft ?>"> cu. ft.</td>
	</tr>
        <tr>
	  <td align="right" class="text_12"><b>Seats:</b></td>
	  <td><input type="text" name="seats" value="<?php echo $seats ?>"></td>
	</tr>
	<tr>
	  <td align="right" class="text_12"><b>Weight:</b></td>
	  <td><input type="text" name="weight" value="<?php= $weight ?>"> lbs.</td>
	</tr>
	<tr>
		<!--- onPropertyChange is so that IE will run the JS before the focus leaves the field... IE sucks --->
		<td align="right" class="text_12"><input id="enable_alloc" onPropertyChange="updateEnableAlloc();" onchange="updateEnableAlloc();" type="checkbox" name="enable_alloc" <?php if ($alloc_enable) echo "checked"; ?> /><b>Avail:</b></td>
		<td><input id="avail" type="text" name="avail" value="<?php echo $avail ?>"></td>
	</tr>
    <tr valign="top">
      <td align="right" class="text_12"><b>Photo:</b></td>
      <td class="text_12"><input type="file" name="photo"><br>
		<?php
		if (file_exists($basedir."photos/$ID.jpg"))
			echo "Upload a new file if this photo should be replaced:<br>
			 <img src=\"../photos/t$ID.jpg\" alt=\"photo\"><br>
			 <input type=\"checkbox\" name=\"delete_photo\" value=\"y\"> delete this photo";
		?>
	  </td>
    </tr>
	<tr>
	  <td align="right" class="text_12"><b>Stock Status:</b></td>
      <td>
	    <select name="stock" id="stock">
	    <?php $stock_types = stock_status(0);
		   foreach ($stock_types as $stock_type) {
			   echo "        "; // Indent
			   echo "<OPTION VALUE=\"".$stock_type['id']."\" STYLE=\"".$stock_type['style']."\"";
			   if ($stock_type['id'] == $stock) {
				   echo " SELECTED";
			   }
			   echo ">".$stock_type['name']."</OPTION>";
		   }
		?>
		</select>
	  </td>
	</tr>
	<tr>
	  <td align="right" class="text_12"><b>Stock Day:</b></td>
      <td>
	    <select name="stock_day" id="stock_day">
	    <OPTION VALUE="0">-</OPTION>
	    <?php
		   for ($i = 1; $i <= 31; ++$i) {
			   echo "<OPTION VALUE=\"".$i."\"";
			   if ($i == $stock_day) {
				   echo " SELECTED";
			   }
			   echo ">".$i."</OPTION>";
		   }
		?>
		</select>
	  </td>
	</tr>
    <tr>
      <td align="right" class="text_12"><b>Item Tier Overide:</b></td>
      <td><input type="checkbox" id="item_tier_override" name="item_tier_override" value="1" <?php if ($item_tier_override == 1): ?>CHECKED<?php endif; ?>></td>
    </tr>
    <input type="hidden" name="display_order" value="<?php echo $display_order; ?>">
    <input type="hidden" name="original_partno" value="<?php echo $partno; ?>">
    <input type="hidden" name="original_description" value="<?php echo $description; ?>">
    <input type="hidden" name="original_price" value="<?php echo $price; ?>">
    <input type="hidden" name="original_cost" value="<?php echo $cost; ?>">
		<input type="hidden" name="original_markup" value="<?php echo $markup; ?>">
    <input type="hidden" name="original_size" value="<?php echo $size; ?>">
    <input type="hidden" name="original_color" value="<?php echo $color; ?>">
    <input type="hidden" name="original_numinset" value="<?php echo $numinset; ?>">
    <input type="hidden" name="original_set_" value="<?php echo $set_; ?>">
    <input type="hidden" name="original_set_cost" value="<?php echo $set_cost; ?>">
		<input type="hidden" name="original_set_markup" value="<?php echo $set_markup; ?>">
	<input type="hidden" name="original_setqty" value="<?php echo $setqty; ?>">
    <input type="hidden" name="original_matt" value="<?php echo $matt; ?>">
    <input type="hidden" name="original_matt_cost" value="<?php echo $matt_cost; ?>">
		<input type="hidden" name="original_matt_markup" value="<?php echo $matt_markup; ?>">
    <input type="hidden" name="original_box" value="<?php echo $box; ?>">
    <input type="hidden" name="original_box_cost" value="<?php echo $box_cost; ?>">
		<input type="hidden" name="original_box_markup" value="<?php echo $box_markup; ?>">
	<input type="hidden" name="original_cubic_ft" value="<?php echo $cubic_ft ?>">
        <input type="hidden" name="original_seats" value="<?php echo $seats ?>">
	<input type="hidden" name="original_weight" value="<?php= $weight ?>">
	<input type="hidden" name="original_alloc" value="<?php echo $alloc ?>" />
	<input type="hidden" name="original_avail" value="<?php echo $avail ?>" />
	<input type="hidden" name="original_stock" value="<?php echo $stock; ?>">
	<input type="hidden" name="original_stock_day" value="<?php echo $stock_day; ?>">
    <input type="hidden" name="original_item_tier_override" value="<?php echo $item_tier_override; ?>">
    <?php
	//DBdisplayFields( &$fields );
	?>
    <tr>
      <td>&nbsp;</td>
      <td><div><br>
<?php if( $ID == "" ) { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Create">&nbsp;<input type="checkbox" name="addanother" value="Y"<?php if ($addanother) echo " CHECKED"; ?>>Add&nbsp;Another&nbsp;Item
<?php } else { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Update">&nbsp;
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Delete">
<?php } ?>
</div></td>
    </tr>
  </table>
	</form>
	<br>
	<div><a href="form-edit.php?ID=<?php echo $form_idx; ?>">Back</a></div>
	<script language="JavaScript1.2">
	var f_enable = document.getElementById("enable_alloc");
	var f_stock = document.getElementById("stock");
	var f_stock_day = document.getElementById("stock_day");
	var f_avail = document.getElementById("avail");
	function updateEnableAlloc() {
		f_avail.disabled = !f_enable.checked;
		if (!f_enable.checked)
			f_avail.value = "";
		f_stock.disabled = f_enable.checked;
		f_stock_day.disabled = f_enable.checked;
		if (f_enable.checked)
			f_stock_day.value = 0;
	}
	updateEnableAlloc();
	</script>
	<?php
break;
}
footer($link);
?>
