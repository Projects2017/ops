<?php
require("MoS_database.php");
require("MoS_admin_secure.php");
require( "../inc_orders.php" );

$tablename = "MoS_form_items";
$self = $_SERVER['PHP_SELF'];

$fields = Array();
DBcreateFields($tablename, &$fields, "<tr><td class=\"text_12\">[TITLE]: </td><td>", "</td></tr>");

if (isset($_GET['header']))
	$header = $_GET['header'];
elseif (isset($_POST['header']))
	$header = $_POST['header'];
else
	die('header not set');
if (!is_numeric($header))
	die('header not a number');
$sql = "SELECT form FROM MoS_form_headers WHERE ID=${header}";
$query = mysql_query($sql);
checkDBError($sql);
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
	$sql = "SELECT `header`, `partno`, `description`, `price`, `size`, `color`, `set_`, `matt`, `box`, `display_order`, `cubic_ft`, `weight`, `snapshot`, `setqty`, `discount` FROM MoS_form_items WHERE ID = '".$ID."'";
	$query = mysql_query($sql);
	checkDBError($sql);
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
		} elseif ($size != $result['size']) {
			$snap1up = 1;
		} elseif ($color != $result['color']) {
			$snap1up = 1;
		} elseif ($set_ != $result['set_']) {
			$snap1up = 1;
		} elseif ($matt != $result['matt']) {
			$snap1up = 1;
		} elseif ($box != $result['box']) {
			$snap1up = 1;
		} elseif ($display_order != $result['display_order']) {
			$snap1up = 1;
		} elseif ($cubic_ft != $result['cubic_ft']) {
			$snap1up = 1;
		} elseif ($weight != $result['weight']) {
			$snap1up = 1;
		} elseif ($setqty != $result['setqty']) {
			$snap1up = 1;
		} elseif ($discount != $result['discount']) {
			$snap1up = 1;
		}

		// Update Snap 1
		if ($snap1up) {
			$sql = "SELECT snapshot FROM MoS_form_headers WHERE ID = '".$header."'";
			$query = mysql_query($sql);
			checkDBError($sql);
			if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
				$snapshot_header = $result['snapshot'];
			}
			$sql = "INSERT INTO MoS_snapshot_items VALUES (NULL, '".$ID."', '".$snapshot_header."', '".$partno."', '".$description."', '".$price."', '".$size."', '".$color."', '".$set_."', '".$matt."', '".$box."', '".$display_order."', '".$cubic_ft."','".$weight."','".$setqty."','".$discount."')";
			mysql_query($sql);
			$snapshot = mysql_insert_id();
			checkDBError($sql);
		} else {
			$snapshot = $result['snapshot'];
		}
	}
	/* End Snapshot modification */
	$sql = buildUpdateQuery($tablename, "ID=$ID");

	mysql_query($sql);
	checkDBError($sql);

	//only super admins allowed to update fields - addded by goody 10/20/04 2:45pm PST
	/* Removed per task #116 --Will 9-7-2005
	if ($security != "S") {
		require ("menu.php");
		echo "&nbsp;<BR>&nbsp;<BR><CENTER><FONT COLOR=RED>Sorry, only Super Admins are able to update fields. Please choose from the menu above.</FONT></CENTER>";
	echo "</TD></TR></TABLE></BODY></HTML>";
	exit();
	} */ //and now back to our regularly scheduled programming....


//-- Don't want to mess with this for MoS right now
/*	if ($delete_photo == "y") {
		unlink($basedir."photos/".$ID.".jpg");
		unlink($basedir."photos/t".$ID.".jpg");
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
		 values($ID,$userid,".date("Ymd").",'item photo was deleted',${form_idx})";
		mysql_query($sql);
		checkDBError();
	}

	if (file_exists($photo)) {
		if (file_exists($basedir."photos/".$ID.".jpg")) {
			unlink($basedir."photos/".$ID.".jpg");
			unlink($basedir."photos/t".$ID.".jpg");
		}
		move_uploaded_file($photo, $basedir."photos/".$ID.".jpg");
		createThumb($ID.".jpg");
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
		 values($ID,$userid,".date("Ymd").",'item photo was uploaded',${form_idx})";
		mysql_query($sql);
		checkDBError();
	}

	$fieldsarray = Array("partno","description","price","size","color","set_","matt","box", "cubic_ft", "stock", "alloc", "avail", "stock_day", "setqty");
	$optional = array('set_','matt','box');
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
				checkDBError();
			}
		} else {
			if (!in_array($field,$optional)) {
				print_r($_POST);
				die("Field missing: ${field}");
			}
		}
	}
*/
	header("Location: MoS_edit-forms-edit.php?ID=${form_idx}");
	exit;
break;

case "delete":
	/* Update Snapshots so they reflect a deleted user */
	$sql = "update MoS_snapshot_items SET orig_id = '0' WHERE orig_id = '".$ID."'";
	mysql_query($sql);
	checkDBError($sql);
	/* End Snapshot Modification */
	$sql = "delete from $tablename where ID=$ID";
	mysql_query( $sql );
	checkDBError($sql);

	header("Location: MoS_edit-forms-edit.php?ID=${form_idx}");
	exit;
break;

case "create":
	$sql = "SELECT snapshot FROM MoS_form_headers WHERE ID = '".$header."'";
	$query = mysql_query($sql);
	checkDBError($sql);
	if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
		$snapshot_header = $result['snapshot'];
	}

	$sql = "INSERT INTO MoS_snapshot_items VALUES (NULL, 0, '".$snapshot_header."', '".$partno."', '".$description."', '".$price."', '".$size."', '".$color."', '".$set_."', '".$matt."', '".$box."', '".$display_order."','".$cubic_ft."','".$weight."','".$setqty."','".$discount."')";
	mysql_query($sql);
	$snapshot = mysql_insert_id();
	checkDBError($sql);

	$sql = buildInsertQuery($tablename);
	mysql_query($sql);
	checkDBError($sql);
	$ID = mysql_insert_ID();
	//passes through to the "view" case

	/* Update Snapshots so they reflect new user */
	$sql = "update MoS_snapshot_items SET orig_id = '".$ID."' WHERE id = '".$snapshot."'";
	mysql_query($sql);
	checkDBError($sql);
	/* End Snapshot Modification */

	/*if (file_exists($photo)) {
		if (file_exists($basedir."photos/".$ID.".jpg"))
			unlink($basedir."photos/".$ID.".jpg");
		move_uploaded_file($photo, $basedir."photos/".$ID.".jpg");
		createThumb($ID.".jpg");
		$sql = "insert into form_changes (form_item_id,user,date,action,form)
		 values($ID,$userid,".date("Ymd").",'item photo was uploaded',${form_idx})";
		mysql_query($sql);
		checkDBError();
	}*/
	if ($addanother) {
		header("Location: MoS_form-item-add.php?header=${header}&addanother=Y");
	} else {
		header("Location: MoS_edit-forms-edit.php?ID=${form_idx}");
	}
	exit;
break;

default:
	require("MoS_menu.php");

	if ($ID == "") {
		$action = "create";
		$setqty = 2;
		$set_ = 0;
		$box = 0;
		$matt = 0;
	} else {
		$sql = "select * from $tablename where ID=$ID";
		$query = mysql_query($sql);
		checkDBError($sql);
		$action = "update";

		if ($result = mysql_fetch_array($query))
		{
			$partno = $result['partno'];
			$description = $result['description'];
			$price = $result['price'];
			$size = $result['size'];
			$color = $result['color'];
			$set_ = $result['set_'];
			$setqty = $result['setqty'];
			$discount = $result['discount'];
			$matt = $result['matt'];
			$box = $result['box'];
			$cubic_ft = $result['cubic_ft'];
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
      <td align="right" class="text_12"><b>Description:</b></td>
      <td><input type="text" name="description" value="<?php echo $description; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Price:</b></td>
      <td><input type="text" name="price" value="<?php echo $price; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Size:</b></td>
      <td><input type="text" name="size" value="<?php echo $size; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Color:</b></td>
      <td><input type="text" name="color" value="<?php echo $color; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Discount:</b></td>
      <td><input type="text" name="discount" value="<?php echo $discount; ?>"></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Set:</b></td>
      <td><input type="text" id="set_" name="set_" value="<?php echo $set_; $field = "set_" ?>"<?php if (is_null($set_)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($set_)) echo "CHECKED"; ?>></td>
    </tr>
	<tr>
	  <td align="right" class="text_12"><b>Qty in Set:</b></td>
	  <td><input type="text" name="setqty" value="<?php echo $setqty; ?>"></td>
	</tr>
    <tr>
      <td align="right" class="text_12"><b>Matt:</b></td>
      <td><input type="text" id="matt" name="matt" value="<?php echo $matt; $field = "matt"; ?>"<?php if (is_null($matt)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($matt)) echo "CHECKED"; ?>></td>
    </tr>
    <tr>
      <td align="right" class="text_12"><b>Box:</b></td>
      <td><input type="text" id="box" name="box" value="<?php echo $box; $field = "box"; ?>"<?php if (is_null($box)) echo " DISABLED" ?>><input type="checkbox" id="<?php= $field ?>_null" name="<?php= $field ?>_null" value="N"  onchange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" onpropertychange="if (document.getElementById('<?php= $field ?>_null').checked) { document.getElementById('<?php= $field ?>').disabled = false; } else { document.getElementById('<?php= $field ?>').disabled = true; document.getElementById('<?php= $field ?>').value=''; }" <?php if (!is_null($box)) echo "CHECKED"; ?>></td>
    </tr>
	<tr>
	  <td align="right" class="text_12"><b>Volume:</b></td>
	  <td><input type="text" name="cubic_ft" value="<?php echo $cubic_ft ?>"> cu. ft.</td>
	</tr>
	<tr>
	  <td align="right" class="text_12"><b>Weight:</b></td>
	  <td><input type="text" name="weight" value="<?php echo $weight ?>"> lbs.</td>
	</tr>
	<tr>
		<!--- onPropertyChange is so that IE will run the JS before the focus leaves the field... IE sucks --->
		<td align="right" class="text_12"><input id="enable_alloc" onPropertyChange="updateEnableAlloc();" onchange="updateEnableAlloc();" type="checkbox" name="enable_alloc" <?php if ($alloc_enable) echo "checked"; ?> /><b>Avail:</b></td>
		<td><input id="avail" type="text" name="avail" value="<?php echo $avail ?>"></td>
	</tr>
    <!-- <tr valign="top">
      <td align="right" class="text_12"><b>Photo:</b></td>
      <td class="text_12"><input type="file" name="photo"><br>
		<?php
		if (file_exists($basedir."photos/$ID.jpg"))
			echo "Upload a new file if this photo should be replaced:<br>
			 <img src=\"../photos/t$ID.jpg\" alt=\"photo\"><br>
			 <input type=\"checkbox\" name=\"delete_photo\" value=\"y\"> delete this photo";
		?>
	  </td>
    </tr> -->
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
    <input type="hidden" name="display_order" value="<?php echo $display_order; ?>">
    <input type="hidden" name="original_partno" value="<?php echo $partno; ?>">
    <input type="hidden" name="original_description" value="<?php echo $description; ?>">
    <input type="hidden" name="original_price" value="<?php echo $price; ?>">
    <input type="hidden" name="original_size" value="<?php echo $size; ?>">
    <input type="hidden" name="original_color" value="<?php echo $color; ?>">
    <input type="hidden" name="original_discount" value="<?php echo $discount; ?>">
    <input type="hidden" name="original_set_" value="<?php echo $set_; ?>">
	<input type="hidden" name="original_setqty" value="<?php echo $setqty; ?>">
    <input type="hidden" name="original_matt" value="<?php echo $matt; ?>">
    <input type="hidden" name="original_box" value="<?php echo $box; ?>">
	<input type="hidden" name="original_cubic_ft" value="<?php echo $cubic_ft ?>">
	<input type="hidden" name="original_weight" value="<?php echo $weight ?>">
	<input type="hidden" name="original_alloc" value="<?php echo $alloc ?>" />
	<input type="hidden" name="original_avail" value="<?php echo $avail ?>" />
	<input type="hidden" name="original_stock" value="<?php echo $stock; ?>">
	<input type="hidden" name="original_stock_day" value="<?php echo $stock_day; ?>">
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
	<div><a href="MoS_edit-forms-edit.php?ID=<?php echo $form_idx; ?>">Back</a></div>
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
?>
