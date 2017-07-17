<?php
require("database.php");
require("secure.php");
require("../inc_orders.php");

$tablename = "vendors";
$logintablename = "vendor";
$self = $_SERVER['PHP_SELF'];

$fields = Array();
DBcreateFields ($tablename, $fields, "<tr><td class=\"fat_black_12\">[TITLE]: </td><td>", "</td></tr>");

$action = strtolower($action);
switch ($action)
{
case "update":
	$sql = buildUpdateQuery($tablename, "ID=$ID");
	mysql_query($sql);
	checkDBError($sql);

	if ($delete_logo == "y")
		unlink($basedir."logos/".$ID.".jpg");

	if (file_exists($logo)) {
		if (file_exists($basedir."logos/".$ID.".jpg"))
			unlink($basedir."logos/".$ID.".jpg");
		move_uploaded_file($logo, $basedir."logos/".$ID.".jpg");
	}
	snapshot_update('vendor', $ID);
        saveDiscount($discount,'discount',array("vendor_id" => $ID),"vendor");
        saveDiscount($freight,'freight',array("vendor_id" => $ID),"vendor");
	header("Location: $self");
	exit;
break;

case "delete":
	mysql_query("delete from $tablename where ID=$ID");
	checkDBError($sql);
	mysql_query("delete from `vendor_access` where `vendor` = '$ID'");
	checkDBError($sql);
	mysql_query("delete from `vlogin_access` where `vendor` = '$ID'");
	checkDBError($sql);
	mysql_query("update `forms` set vendor = '0' where `vendor` = '$ID'");
	checkDBError($sql);
        deleteDiscount('freight',array("vendor_id" => $ID),"vendor");
        deleteDiscount('discount',array("vendor_id" => $ID),"vendor");
	snapshot_update('vendor', $ID);
	header("Location: $self");
	exit;
break;

case "copy":
	if ($security != "S")
		die("Permission Denied");
    //echo "copy ".$id." to ".$newname."<br>";
    // see database.php to see function instructions.
    // print_r(db_copy_row("claim_test",0,"185","user_id","186"));
    $newven = db_copy_row("vendors",1,$id,"name",$newname);
    if (!$newven) die("Whoa... no such vendor $id");
    $newven = $newven[0]['newid'];
    db_copy_row("vendor_discount", false, $id, "vendor_id", $newven);
    db_copy_row("vendor_freight", false, $id, "vendor_id", $newven);
    $forms = db_copy_row("forms",0,$id,"vendor", $newven);
    foreach ($forms as $form) {
    	$heads = db_copy_row("form_headers",0,$form['oldid'],"form",$form['newid']);
    	foreach ($heads as $head) {
            $items = db_copy_row("form_items",0,$head['oldid'],"header",$head['newid']);
            foreach ($items as $item) {
                $discounts = db_copy_row("form_item_discount",0,$item['oldid'],"item_id",$item['newid']);
                $freights = db_copy_row("form_item_freight",0,$item['oldid'],"item_id",$item['newid']);
            }
    	}
        db_copy_row("user_freight", false, $form['oldid'], "form_id", $newform);
        db_copy_row("user_discount", false, $form['oldid'], "form_id", $newform);
        db_copy_row("form_discount", false, $form['oldid'], "form_id", $newform);
        db_copy_row("form_freight", false, $form['oldid'], "form_id", $newform);
        db_copy_row("form_access", false, $form['oldid'], "form", $newform);
    }

	snapshot_update('vendor', $newven);
    // Return to default view
    header("Location: $self");
	exit;
break;

case "create":
	$sql = buildInsertQuery($tablename);
	mysql_query($sql);
	checkDBError($sql);

	$ID = mysql_insert_ID();
	//passes through to the "view" case
	snapshot_update('vendor', $ID);
	move_uploaded_file($logo, $basedir."logos/".$ID.".jpg");

        saveDiscount($discount,'discount',array("vendor_id" => $ID),"vendor");
        saveDiscount($freight,'freight',array("vendor_id" => $ID),"vendor");

case "view":
	require("menu.php");
        /* Insert in Discount & Freight */
        $temp = array();
        $temp[] = new DBField('discount', 'vendor_discount','0','');
        $temp[0]->prefix = $fields[0]->prefix;
	$temp[0]->postfix = $fields[0]->postfix;
	$temp[0]->type = 'text';
        $temp[] = new DBField('freight', 'vendor_feight','0','');
        $temp[1]->prefix = $fields[0]->prefix;
	$temp[1]->postfix = $fields[0]->postfix;
	$temp[1]->type = 'text';
        array_splice($fields,10,0,$temp);

	$fields[8]->title = "Email (Secondary)";
	$fields[9]->type = 'checkbox'; // Pre-Paid frieght
	$fields[10]->title = "Discount:<br>(include % or $ sign)";
        $fields[11]->title = "Freight:<br>(include % or $ sign)";
	$fields[13]->type="select";// Access_type
	$fields[13]->options=array('Bedding','Case Goods','Upholstery'); // Access Type
	$fields[14]->type="select"; // MAS90_type
	$fields[14]->options=array('BEDDING','CASE','DISC','ROYAL'); // MAS90_type
	$fields[15]->title = "Email on Process"; //proc_email
	$fields[15]->type="checkbox"; // proc_email
	$fields[16]->title = "Email (2nd) on Process"; //proc_email2
	$fields[16]->type="checkbox"; // proc_email2

	if ($ID == "") $action = "create";
	else
	{
		$query = mysql_query("select * from $tablename where ID=$ID");
		checkDBError();
		$action = "update";

		if ($result = mysql_fetch_array($query)) {
			$fields[0]->value=$result['name'];
			$fields[1]->value=$result['address'];
			$fields[2]->value=$result['city'];
			$fields[3]->value=$result['state'];
			$fields[4]->value=$result['zip'];
			$fields[5]->value=$result['phone'];
			$fields[6]->value=$result['fax'];
			$fields[7]->value=$result['email'];
			$fields[8]->value=$result['email2'];
			$fields[9]->value=$result['prepaidfreight'];
                        $fields[10]->value=loadDiscount('discount',array("vendor_id" => $ID),"vendor");
                        $fields[11]->value=loadDiscount('freight',array("vendor_id" => $ID),"vendor");
			$fields[12]->value=$result['Access_name'];// Access name
			$fields[13]->value=$result['Access_type'];
			$fields[14]->value=$result['MAS90_type'];
			$fields[15]->value=$result['proc_email'];
			$fields[16]->value=$result['proc_email2'];
			$fields[17]->value=$result['proc_url'];
		}
	}

                /* Help Nodes */
        /* Discount Help */
        $fields[10]->postfix = "<a href=\"\" onclick=\"toggleHelp('discount'); return false;\">[Help]</a>".$fields[10]->postfix;
        $fields[10]->postfix .= "<tr id=\"help_discount\" style=\"display: none\">
            <td>&nbsp;</td>
            <td class=\"text_12\">
                <div style=\"width: 300px\">
                <h3>Vendor Discounts</h3>
                <p>
                    Discounts may be applied two ways, percentages or dollars. To apply a percentage
                    discount, simply append a % after the number. To apply a dollar discount, prepend
                    the number with a dollar sign ($).
                </p>
                <h4>Inheritance</h4>
                <p>
                    Discounts are done on an inheritance basis, the form inherits the discount unless it itself specifys
                    a discount. User specific discounts override both vendor and form discounts. Item discounts are always
                    seperately and are never overiden.
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
                    Freight charges are overridden by user specific form frieghts. A form may also override
                    a vendor discount for orders against that form.
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
	<form action="<?php echo $self; ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="ID" value="<?php echo $ID; ?>"><br>
	<table border="0" cellspacing="5" cellpadding="0">
	<tr valign="top">
		<td class="fat_black_12">JPG Logo: </td>
		<td class="text_12"><input type="file" name="logo"><br>
		<?php
		if (file_exists($basedir."logos/".$ID.".jpg"))
			echo "Upload a new file if this logo should be replaced:<br>
			 <img src=\"../logos/".$ID.".jpg\" alt=\"logo\"><br>
			 <input type=\"checkbox\" name=\"delete_logo\" value=\"y\"> delete this logo";
		?>
		</td>
	</tr>
	<?php
	DBdisplayFields($fields);
	?>
	</table><br>
<div>
<?php if( $ID == "" ) { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Create">
<?php } else { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Update">
	<?php if ($security == "S") { ?>
	&nbsp;
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Delete" onclick="return confirm('You are about to permanently delete this vendor. Are you sure you want to delete?')">
	<?php } ?>
<?php } ?>
</div>
	</form>
	<?php
break;

case "update login":
	DBcreateFields ($logintablename, $fields, "<tr><td class=\"fat_black_12\">[TITLE]: </td><td>", "</td></tr>");
	if (!$disabled)
		$disabled = "N";
	$sql = buildUpdateQuery($logintablename, "ID=$ID");
	mysql_query($sql);
	checkDBError($sql);

	$sql = "update login set username = '".$username."', password = '".$password."' WHERE type = 'V' and relation_id = '".$ID."';";
	mysql_query($sql);
	checkDBError($sql);

	header("Location: $self?action=loginlist");
	exit;
break;

case "delete login":
	mysql_query("delete from login where type = 'V' AND relation_id=$ID");
	checkDBError($sql);
	mysql_query("delete from $logintablename where ID=$ID");
	checkDBError($sql);

	header("Location: $self?action=loginlist");
	exit;
break;

case "create login":
	DBcreateFields ($logintablename, $fields, "<tr><td class=\"fat_black_12\">[TITLE]: </td><td>", "</td></tr>");
	if (!$disabled)
		$disabled = "N";
	$sql = buildInsertQuery($logintablename);
	mysql_query($sql);
	checkDBError($sql);

	$ID = mysql_insert_ID();
	$sql = "insert into login (username,password,type,relation_id) VALUES ('".$username."', '".$password."','V','".$ID."');";
	mysql_query($sql);
	checkDBError($sql);
	//passes through to the "view" case

case "loginview":
	unset($fields);
	$fields = Array();
	DBcreateFields ($logintablename, $fields, "<tr><td class=\"fat_black_12\">[TITLE]: </td><td>", "</td></tr>");
	require("menu.php");

	if ($ID == "") {
		$action = "create";
		$fields[0]->display=false;
		$fields[2]->display=false;
		$fields[3]->display=false;
		$fields[4]->value=md5(uniqid(''));
		$username = '';
		$password = '';
	} else {
		$query = mysql_query("select vendor.*, login.username, login.password from vendor,login where vendor.id=$ID and login.type = 'V' and login.relation_id = vendor.id");
		checkDBError();
		$action = "update";

		if ($result = mysql_fetch_array($query)) {
			$fields[0]->display=false;
			$fields[1]->value=$result['name'];
			//$fields[2]->value=$result['login'];
			//$fields[3]->value=$result['password'];
			$fields[2]->display=false;
			$fields[3]->display=false;
			// Non-Supers shouldn't mess with Vendor Keys... it does bad things
			if (secure_is_superadmin())
				$fields[4]->value=$result['key'];
			else
				$fields[4]->display=false;
			$id = $result['id'];
			$username = $result['username'];
			$password = $result['password'];
		}
	}
	 ?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
	<form action="<?php echo $self; ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="ID" value="<?php echo $ID; ?>"><br>
	<table border="0" cellspacing="5" cellpadding="0">
	<?php
	DBdisplayFields($fields);
	if ($action != 'create') {
	?>
	<tr>
		<td class="fat_black_12">Vendor ID:</td>
		<td>
			<?php= $id ?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="fat_black_12">Username:</td>
		<td>
			<input name="username" type="text" value="<?php= $username ?>">
		</td>
	</tr>
	<tr>
		<td class="fat_black_12">Password:</td>
		<td>
			<input name="password" type="text" value="<?php= $password ?>">
		</td>
	</tr>
	<tr>
		<td class="fat_black_12">Type:</td>
		<td>
			<select name="type">
				<option value="furniture" <?php if ($result['type'] == "furniture") echo " SELECTED"; ?>>Furniture</option>
				<option value="bedding" <?php if ($result['type'] == "bedding") echo " SELECTED"; ?>>Bedding</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="fat_black_12">Disabled:</td>
		<td><input type="checkbox" name="disabled" value="Y"<?php if ($result['disabled'] == "Y") echo " CHECKED"; ?>></td>
	</tr>
	</table><br>
<div>
<?php if( $ID == "" ) { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Create Login">
<?php } else { ?>
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Update Login">
	<?php if ($security == "S") { ?>
	&nbsp;
	<input type="submit" name="action" style="background-color:#CA0000;color:white" value="Delete Login" onclick="return confirm('You are about to permanently delete this vendor login. Are you sure you want to delete?')">
	<?php } ?>
<?php } ?>
</div>
	</form>
	<?php
break;

case "loginlist":
	require("menu.php");

	$sql = "select vendor.*, login.username, login.password from vendor, login where login.type = 'V' and login.relation_id = vendor.id order by vendor.name";
	$query = mysql_query($sql);
	checkDBError($sql);
	?><br>

<table class="sortable" id="list" border="0" cellspacing="0" cellpadding="5" align="left">
  <tr class="skiptop">
  	<td colspan="2"><a href="<?php echo $self ?>?action=loginview">New Vendor Login</a></td>
    <td colspan="5" align="right"><a href="<?php echo $self ?>">Vendor List</a> || <a href="<?php echo $self ?>?action=loginlist">Vendor Logins</a> || <a href="report-vendoraccess.php">Vendor Access Report</a></td>
  </tr>
  <tr>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>Name</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>Login</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>Password</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>Type</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc" align="center"><b>Disabled</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
	<td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
	<td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
  </tr>
  <?php
	while ($result = mysql_fetch_Array($query))
	{
	?>
  <tr>
    <td class="text_12"><a href="<?php echo $self ?>?action=loginview&ID=<?php echo $result['id']; ?>">
      <?php echo $result['name']; ?></a></td>
    <td class="text_12"><?php echo $result['username']; ?></td>
    <td class="text_12"><?php echo $result['password']; ?></td>
    <td class="text_12"><?php echo $result['type']; ?></td>
    <td class="text_12"><center><?php echo $result['disabled']; ?></center></td>
    <td><a href="<?php echo $self ?>?action=loginview&ID=<?php echo $result['id']; ?>">Edit</a></td>
	<td><a href="vendors-vendoraccess.php?ID=<?php echo $result['id']; ?>">Access</a></td>
	<td><a href="csvstock.php?vendorid=<?php echo $result['id']; ?>">Stock CSV</a></td>
  </tr>
  <?php
	}
  echo "</table>";
break;

default:
	require("menu.php");

	$sql = "select * from $tablename order by name";
	$query = mysql_query($sql);
	checkDBError($sql);
	?><br>

<table class="sortable" id="list" border="0" cellspacing="0" cellpadding="5" align="left">
  <tr class="skiptop">
    <td colspan="2"><a href="<?php echo $self ?>?action=view">New Vendor</a> || <a href="form-changes.php">Form
      Changes Log</a> || <a href="vendor-orders.php">View All Orders By Day</a></td>
    <td colspan="6" align="right"><a href="<?php echo $self ?>">Vendor List</a> || <a href="<?php echo $self ?>?action=loginlist">Vendor Logins</a> || <a href="report-vendoraccess.php">Vendor Access Report</a></td>
  </tr>
  <tr>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>Name</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>Phone #</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>Fax #</b></td>
    <!---<td class="fat_black_12" bgcolor="#fcfcfc"><b>Email</b></td>-->
    <td class="fat_black_12" bgcolor="#fcfcfc" nowrap><b>City</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc"><b>State</b></td>
    <td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
    <?php if ($security == "S") { ?>
    <td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
    <?php } ?>
    <td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
  </tr>
  <?php
	while ($result = mysql_fetch_Array($query))
	{
	?>
  <tr>
    <td class="text_12"><a href="<?php echo $self ?>?action=view&ID=<?php echo $result['ID']; ?>">
      <?php echo $result['name']; ?></a></td>
    <td class="text_12" nowrap><?php echo $result['phone']; ?></td>
    <td class="text_12" nowrap><?php echo $result['fax']; ?></td>
    <!---<td class="text_12"><?php
	echo $result['email'];
	if ($result['email2'] <> "") echo ", ".$result['email2'];
	?></td>--->
    <td class="text_12" nowrap><?php echo $result['city']; ?></td>
    <td class="text_12"><?php echo $result['state']; ?></td>
    <td><a href="<?php echo $self ?>?action=view&ID=<?php echo $result['ID']; ?>">Edit</a></td>
    <?php if ($security == "S") { ?>
    <td><a OnClick="vendorcopy('<?php echo $result['ID']; ?>','<?php echo addslashes($result['name']); ?>')">Copy</a></td>
    <?php } ?>
    <td><a href="forms.php?vendor=<?php echo $result['ID']; ?>">Vendor&nbsp;Forms</a></td>
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
<?php
break;

}
footer($link);
?>
