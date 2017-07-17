<?php
require( "database.php" );
require( "secure.php" );
require( "../inc_orders.php" );

$tablename = "forms";
$self = $_SERVER['PHP_SELF'];

$fields = Array();
DBcreateFields( $tablename, $fields, "<tr><td class=\"fat_black_12\">[TITLE]: </td><td>", "</td></tr>" );
$action = strtolower( $action );
switch( $action )
{
case "update":
        if ($minimum) {
            if ($min_type == 'P') {
                $minimum = 'P:::'.$minimum;
            } else {
                $minimum = 'D:::'.$minimum;
            }
        }
	$sql = buildUpdateQuery( $tablename, "ID=$ID" );
	mysql_query( $sql );
	checkDBError($sql);
	$result = resortheaders($ID);
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('form', $ID);
	}
        if ($freight_null == "N") {
            if (!$freight) $freight = '0.00%';
            saveDiscount($freight,'freight',array("form_id" => $ID),"form");
        } else {
            deleteDiscount('freight',array("form_id" => $ID),"form");
        }
        if ($discount_null == 'N') {
            if (!$discount) $discount = '0.00%';
            saveDiscount($discount,'discount',array("form_id" => $ID),"form");
        } else {
            deleteDiscount('discount',array("form_id" => $ID),"form");
        }
	header( "Location: form-edit.php?ID=$ID" );
	exit;
break;

/* Called From Mass Item Update, when form properties are changed */
case "massupdate":
        if ($minimum) {
            if ($min_type == 'P') {
                $minimum = 'P:::'.$minimum;
            } else {
                $minimum = 'D:::'.$minimum;
            }
        }
        $sql = "UPDATE `forms` SET `vendor` = '".$vendor."', `name` = '".$name."', `minimum` = '".$minimum."' WHERE `ID` = '".$ID."'";
	mysql_query( $sql );
	checkDBError($sql);
	$result = resortheaders($ID);
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('form', $ID);
	}
        if ($freight_null == "N") {
            if (!$freight) $freight = '0.00%';
            saveDiscount($freight,'freight',array("form_id" => $ID),"form");
        } else {
            deleteDiscount('freight',array("form_id" => $ID),"form");
        }
        if ($discount_null == 'N') {
            if (!$discount) $discount = '0.00%';
            saveDiscount($discount,'discount',array("form_id" => $ID),"form");
        } else {
            deleteDiscount('discount',array("form_id" => $ID),"form");
        }
	header( "Location: form-edit.php?ID=$ID" );
	exit;
break;

case "delete":
	$sql = "delete from $tablename where ID=$ID";
	mysql_query( $sql );
	checkDBError($sql);
	snapshot_update('form', $ID);
        deleteDiscount('freight',array("form_id" => $ID),"form");
        deleteDiscount('discount',array("form_id" => $ID),"form");
	header( "Location: $self?vendor=$vendor" );
	exit;
break;

case "create":
	$sql = buildInsertQuery( $tablename );
	mysql_query( $sql );
	checkDBError($sql);
	$ID = mysql_insert_ID();
	snapshot_update('form', $ID);
        saveDiscount($discount,'discount',array("form_id" => $ID),"form");
        saveDiscount($freight,'freight',array("form_id" => $ID),"form");
	header( "Location: form-edit.php?ID=$ID" );
	exit;
break;

case "copy":
	if ($security != "S")
		die("Permission Denied");
    //echo "copy ".$id." to ".$newname."<br>";
    // see database.php to see function instructions.
    // print_r(db_copy_row("claim_test",0,"185","user_id","186"));
    $newform = db_copy_row("forms",1,$id,"name",$newname);
    if (!$newform) die("Whoa... no such form $id");
    $newform = $newform[0]['newid'];
    $heads = db_copy_row("form_headers",0,$id,"form", $newform);
    foreach ($heads as $head) {
    	$items = db_copy_row("form_items",0,$head['oldid'],"header",$head['newid']);
        foreach ($items as $item) {
            $discounts = db_copy_row("form_item_discount",0,$item['oldid'],"item_id",$item['newid']);
            $freights = db_copy_row("form_item_freight",0,$item['oldid'],"item_id",$item['newid']);
        }
    }

    snapshot_update('form', $newform);
    db_copy_row("user_freight", false, $id, "form_id", $newform);
    db_copy_row("user_discount", false, $id, "form_id", $newform);
    db_copy_row("form_discount", false, $id, "form_id", $newform);
    db_copy_row("form_access", false, $id, "form", $newform);
    // Go straight to editing new form
    header( "Location: form-edit.php?ID=$newform" );
	exit;
break;
	
case "view":
	require( "menu.php" );

        /* Insert in Discount & Freight */
        $temp = array();
        $temp[] = new DBField('discount', 'form_discount','0',null, true);
        $temp[0]->prefix = $fields[0]->prefix;
	$temp[0]->postfix = $fields[0]->postfix;
	$temp[0]->type = 'text';
        $temp[] = new DBField('freight', 'form_feight','0',null,true);
        $temp[1]->prefix = $fields[0]->prefix;
	$temp[1]->postfix = $fields[0]->postfix;
	$temp[1]->type = 'text';
        array_splice($fields,10,0,$temp);

	if( $ID == "" ) {
		$action = "create"; 
		$fields[14]->value = 'Y';
		$fields[15]->value = 'ascending';
	} else {
		$sql = "select * from $tablename where ID=$ID";
		$query = mysql_query( $sql );
		checkDBError();
		$action = "update";
                $min_type = 'D';
		
		if( $result = mysql_fetch_array( $query ) )
		{
			$fields[0]->value=$result['name'];
			$fields[2]->value=$result['shipper'];
			$fields[3]->value=$result['minimum'];
                        /* If Minimum has a specification, extract it */
                        if (count(explode(":::",$fields[3]->value)) == 2) {
                            $min_type = explode(":::",$fields[3]->value);
                            $fields[3]->value = $min_type[1];
                            $min_type = $min_type[0];
                        }
			$fields[4]->value=$result['address'];
			$fields[5]->value=$result['city'];
			$fields[6]->value=$result['state'];
			$fields[7]->value=$result['zip'];
			$fields[8]->value=$result['phone'];
			$fields[9]->value=$result['fax'];
                        $fields[10]->value=loadDiscount('discount',array("form_id" => $ID),"form");
                        if (!$fields[10]->value) $fields[10]->value = null;
                        $fields[11]->value=loadDiscount('freight',array("form_id" => $ID),"form");
                        if (!$fields[11]->value) $fields[11]->value = null;
			$fields[12]->value=$result['oorvendor'];
			$fields[14]->value=$result['allowfree'];
			$fields[15]->value=$result['alloworder'];
			$fields[16]->value=$result['useshipping'];
			$fields[17]->value=$result['header_order'];
			$fields[18]->value=$result['backorder'];
                        $fields[19]->value=$result['mattratio'];
			$vendor = $result['vendor'];
		}
	}
	$fields[1]->display = false;
        if ($min_type == 'P') {
            $fields[3]->postfix = "<SELECT id='min_type' name='min_type'><OPTION value='D'>$</OPTION><OPTION value='P' SELECTED>#</OPTION></SELECT>".$fields[3]->postfix;
        } else {
            $fields[3]->postfix = "<SELECT id='min_type' name='min_type'><OPTION value='D' SELECTED>$</OPTION><OPTION value='P'>#</OPTION></SELECT>".$fields[3]->postfix;
        }
        $fields[10]->null_value = loadDiscount('discount',array("vendor_id" => $vendor),"vendor");
        $fields[10]->null_default = '0.00%';
        $fields[10]->title = "Discount:<br>(include % or $ sign)";
        $fields[11]->null_value = loadDiscount('freight',array("vendor_id" => $vendor),"vendor");
        $fields[11]->null_default = '0.00%';
        $fields[11]->title = "Freight:<br>(include % or $ sign)";
	$fields[12]->title = "OOR Vendor";
	$fields[12]->type = "selectval";
		// Get OOR Vendor Options
		$sql = "select id, name from vendor where disabled != 'Y'";
		$query = mysql_query( $sql );
		checkDBError($sql);
		while ($result = mysql_fetch_assoc( $query )) {
			$fields[12]->options[$result['id']] = $result['name'];
		}
	$fields[13]->display = false; // Make Snapshot field disapper!
	$fields[14]->title = "Allow Free Items";
	$fields[14]->type = "checkbox";
	$fields[15]->title = "Allow Ordering";
	$fields[15]->type = "checkbox";
	$fields[16]->title = "Use Shipping System";
	$fields[16]->type = "checkbox";
	$fields[17]->title = "Header Order";
	$fields[17]->type = "selectval";
	$fields[17]->options = array('manual' => "Manual", 'ascending' => "Ascending", 'decending' => "Decending");
	if ($backorder_enable) {
		$fields[18]->title = "Allow Backorder";
		$fields[18]->type = "checkbox";
	} else {
		$fields[18]->display = false;
	}
        $fields[19]->title = "Set to Mattress Ratio (1:?)";
        $fields[19]->postfix = "<spam class='text_12'>  -1 for unlimited</span>";

        /* Help Nodes */
        /* Discount Help */
        $fields[10]->postfix = "<a href=\"\" onclick=\"toggleHelp('discount'); return false;\">[Help]</a>".$fields[10]->postfix;
        $fields[10]->postfix .= "<tr id=\"help_discount\" style=\"display: none\">
            <td>&nbsp;</td>
            <td class=\"text_12\">
                <div style=\"width: 300px\">
                <h3>Form Discounts</h3>
                <p>
                    Discounts may be applied two ways, percentages or dollars. To apply a percentage
                    discount, simply append a % after the number. To apply a dollar discount, prepend
                    the number with a dollar sign ($).
                </p>
                <h4>Inheritance</h4>
                <p>
                    Form Discounts are overridden by user specific form discounts. When the checkbox next
                    to the field is unchecked, the discount for the form will be inherited from the parent
                    of the form.
                </p>
                <h4>Tiered Discounts</h4>
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
                </p>
                <a href=\"\" onclick=\"toggleHelp('discount'); return false;\">(close help)</a>
                </div>
            </td>
        </tr>";
        /* Freight Help */
        $fields[11]->postfix = "<a href=\"\" onclick=\"toggleHelp('freight'); return false;\">[Help]</a>".$fields[11]->postfix;
        $fields[11]->postfix .= "<tr id=\"help_freight\" style=\"display: none\">
            <td>&nbsp;</td>
            <td class=\"text_12\">
                <div style=\"width: 300px\">
                <h3>Freight Charge</h3>
                <p>
                    Freight charges may be applied two ways, percentages or dollars. To apply a percentage
                    discount, simply append a % after the number. To apply a dollar discount, prepend
                    the number with a dollar sign ($).
                </p>
                <h4>Inheritance</h4>
                <p>
                    Freight charges are overridden by user specific form frieghts. When the checkbox next
                    to the field is unchecked, the freight for the form will be inherited from the parent
                    of the form.
                </p>
                <h4>Tiered Freight</h4>
                <p>Tier Freight are in the form of from:to:discount with each tier seperated by a semi-colon (;).</p>
                <p>You may apply a freight regardless of qty, by simply providing a percentage
                or $ amount. You may also provide a from but no to (i.e. 2:25% minimum 2 items
                to reach discount of 25%). All tiers are applied in order. So if you
                have a global first, then a more specific second, and it applies to both, it will
                use the last one.</p>
                <p>For example 35%;2:5:30%;6:25%
                <ol>
                    <li>Application will be against 35%, so the order freight will always be 35% of the total unless another percentage matches.</li>
                    <li>Will only apply to quantities between 2 and 5. If these apply, the percentage will be 30%</li>
                    <li>If the quantity ordered is above 6, it will override any previous percentage with 25%</li>
                </ol>
                </p>
                <a href=\"\" onclick=\"toggleHelp('freight'); return false;\">(close help)</a>
                </div>
            </td>
        </tr>";
	 ?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
	
	<form action="<?php echo $self ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="vendor" value="<?php echo $vendor ?>">
	<input type="hidden" name="ID" value="<?php echo $ID ?>">
	<br>
	<table border="0" cellspacing="5" cellpadding="0">
	<?php
	DBdisplayFields( $fields );
	?>
	</table><br>
<div>
<?php if( $ID == "" ) { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Create">
<?php } else { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Update">	
	&nbsp;
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Delete">
<?php } ?>
</div>	
	</form>
	
<br>
<a href="forms.php?vendor=<?php echo $vendor ?>">Back</a>
<?php
break;

default:
	require( "menu.php" ); 
	
	$sql = "select name from vendors where ID=$vendor";
	$query = mysql_query( $sql );
	checkDBError();
	
	if( $result = mysql_fetch_array( $query ) )
	{
	?>
	<br>
<span class="fat_black">
<?php echo $result['name'] ?> Forms</span><br><br>
<?php
	}
	
	if ($_GET['showdisabled'] == 'Y') {
		$sql = "select * from $tablename where vendor=$vendor order by name";
	} else {
		$sql = "select * from $tablename where vendor=$vendor and alloworder = 'Y' order by name";
	}
	$query = mysql_query( $sql );
	checkDBError();
	?>
<table width="90%" border="0" cellpadding="5" cellspacing="0">
  <tr> 
    <td colspan="7"><a href="<?php echo $self ?>?action=view&vendor=<?php echo $vendor ?>">New
      Form</a></td>
    <td align="right"><a href="<?php echo $self; ?>?vendor=<?php echo $vendor ?><?php if ($_GET['showdisabled'] != "Y") { ?>&showdisabled=Y<?php } ?>"><?php if ($_GET['showdisabled'] == 'Y') { ?>Hide Disabled<?php } else { ?>Show Disabled<?php } ?></a></td>
  </tr>
  <tr bgcolor="#fcfcfc"> 
    <td width="40%" class="fat_black_12"><strong>Name </strong></td>
    <td colspan="11" class="fat_black_12">Administration</td>
  </tr>
  <?php
	while( $result = mysql_fetch_Array( $query ) )
	{
	?>
  <tr> 
    <td width="45%"><a href="form-edit.php?ID=<?php echo $result['ID'] ?>">
      <?php echo $result['name']; ?> </a></td>
	<td width="5%"><a href="forms.php?action=view&ID=<?php echo $result['ID'] ?>">Edit</a></td>
    <td width="5%"><a href="form-edit.php?ID=<?php echo $result['ID'] ?>">Modify&nbsp;Form</a></td>
	<td width="5%">
		<a href="vendor-dealerorder.php?ID=<?php echo $result['ID'] ?>">Order&nbsp;As</a>
	</td>
	<td width="5%"><a href="form-useraccess.php?ID=<?php echo $result['ID'] ?>">Access</a></td>
	<td width="5%"><a OnClick="recordcopy('form','<?php echo $result['ID']; ?>','<?php echo addslashes($result['name']); ?>')">Copy</a></td>
	<td width="5%"><a href="forms.php?ID=<?php echo $result['ID'] ?>&vendor=<?php echo $vendor; ?>&action=delete" onClick="return confirm('Are you sure you wish to delete the form \'<?php echo $result['name']; ?>\'?');">Delete</a></td>
    <td width="15%"><a href="form-view.php?ID=<?php echo $result['ID'] ?>" target="print_view">Printer&nbsp;Friendly&nbsp;Price&nbsp;List</a></td>
  </tr>
  <?php
	}
	?>
</table>
<form action="<?php echo $self; ?>" id="copy" method="put" name="copy">
  <input type="hidden" name="action" value="copy">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="newname" value="">
</form>
<br>
<?php
break;


}
footer($link);
?>
