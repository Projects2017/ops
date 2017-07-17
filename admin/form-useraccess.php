<?php
require( "database.php" );
require( "secure.php" );
require( "menu.php" );

if (!is_numeric($ID))
    die("ID must be numeric and present");

function hasFormAccess ($user, $form)
{
	$sql = "select ID from form_access where user=$user and form=$form";
	$query = mysql_query( $sql );
	checkDBerror();
	
	if( mysql_num_rows( $query ) > 0 )
		return true;
	return false;
}

if( $submit1 != "" ) {
	//-- Remove all form access
	$sql = "delete from form_access where form=$ID";
	mysql_query($sql);
	checkDBError();

	foreach($_POST['users'] as $key => $value) {
		//-- Add access
		if ($value['access'] == "Y") {
			$sql = "insert into form_access values ('null', '$key', '$ID')";
			mysql_query($sql);
			checkDBError($sql);
		}
		//-- Freight
                if (!is_null($value['FF'])) {
                    if (!$value['FF']) $value['FF'] = '0.00%';
                    saveDiscount($value['FF'],'freight',array("user_id" => $key,"form_id"=>$ID),"user");
                } else {
                    deleteDiscount('freight',array("user_id" => $key,"form_id"=>$ID),"user");
                }
		//-- Discount
                if (!is_null($value['FD'])) {
                    if (!$value['FD']) $value['FD'] = '0.00%';
                    saveDiscount($value['FD'],'discount',array("user_id" => $key,"form_id"=>$ID),"user");
                } else {
                    deleteDiscount('discount',array("user_id" => $key,"form_id"=>$ID),"user");
                }
                //-- Markup
                if (!is_null($value['FM'])) {
                    if (!$value['FM']) $value['FM'] = '0.00%';
                    saveDiscount($value['FM'],'markup',array("user_id" => $key,"form_id"=>$ID),"user");
                } else {
                    deleteDiscount('markup',array("user_id" => $key,"form_id"=>$ID),"user");
                }
                //-- Tier
                if (!is_null($value['FI'])) {
                    if (!$value['FI']) $value['FI'] = '0.00%';
                    saveDiscount($value['FI'],'tier',array("user_id" => $key,"form_id"=>$ID),"user");
                } else {
                    deleteDiscount('tier',array("user_id" => $key,"form_id"=>$ID),"user");
                }
	}
}


if ($action) {
	$sql = "select ID from users where disabled != 'Y'";
	$query = mysql_query( $sql );
	checkDBError($sql);
	while( $result = mysql_fetch_Array( $query ) )
	{
		if ($action == 'enableall') {
			$sql = "select id from form_access WHERE user = ".$result['ID']." AND form = '".$ID."'";
			$query2 = mysql_query($sql);
			checkdberror($sql);
			if (!mysql_num_rows($query2)) {
				$sql = "insert into form_access (user,form) Values (".$result['ID'].", '".$ID."')";
				mysql_query($sql);
				checkdberror($sql);
			}
		} elseif ($action == 'disableall') {
			$sql = "delete from form_access WHERE user = ".$result['ID']." AND form = '".$ID."'";
			mysql_query($sql);
			checkdberror($sql);
                        deleteDiscount('freight',array("user_id" => $result['ID'],"form_id"=>$ID),"user");
			deleteDiscount('discount',array("user_id" => $result['ID'],"form_id"=>$ID),"user");
                        deleteDiscount('markup',array("user_id" => $result['ID'],"form_id"=>$ID),"user");
                        deleteDiscount('tier',array("user_id" => $result['ID'],"form_id"=>$ID),"user");
		}
	}
}

$sql = "select forms.vendor as `vendor_id`, forms.name, vendors.name as vendor from forms inner join vendors on vendors.ID = forms.vendor where forms.ID=$ID";
$query = mysql_query( $sql );
checkDBError();

if( mysql_num_rows( $query ) > 0 )
	assignFieldsToVars( $query );


// Find out what the "All Dealers" Frieght is as well
$default_freight = loadDiscount('freight',array("form_id" => $ID),"form");
if ($default_freight == '') {
    $default_freight = loadDiscount('freight',array("vendor_id" => $vendor_id),"vendor");
    if ($default_freight == '') {
        $default_freight = '0.00%';
    }
}

// Find out what the "All Dealers" Frieght is as well
$default_discount = loadDiscount('discount',array("form_id" => $ID),"form");
if ($default_discount == '') {
    $default_discount = loadDiscount('discount',array("vendor_id" => $vendor_id),"vendor");
    if ($default_discount == '') {
        $default_discount = '0.00%';
    }
}

$default_markup = ''; // default markup does not exist at this juncture.
$default_tier = ''; // default tier does not exist at this juncture.

//$sql = "select ID, username, first_name, last_name from users where disabled != 'Y' ORDER BY last_name, first_name";
$sql = "select users.ID as uid, users.first_name, users.last_name " . 
		"FROM users  " . 
		"WHERE users.disabled !='Y' ORDER BY users.last_name, users.first_name";
$query = mysql_query( $sql );
checkDBError();

if( mysql_num_rows( $query ) == 0 )
{
?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">

	<div>
  <span class="fat_red">No Dealers In The Database!</span>
</div>
<?php
	footer();
	exit;
}
?><br>
<div class="fat_black"><?php echo $vendor." - ".$name; ?></div>
<br>
<a href="form-useraccess.php?ID=<?php echo $ID; ?>&action=enableall">Enable All</a> | <a href="form-useraccess.php?ID=<?php echo $ID; ?>&action=disableall">Disable All</a> | <a href="" onclick="toggleHelp('instructions'); return false;">Help</a>
<div id="help_instructions"  class="text_12" style="display: none; width: 300px">
    <h3>Freight & Discount Values</h3>
    <p>
        These may be applied two ways, percentages or dollars. To apply a percentage,
        simply append a % after the number. To apply a dollar amount, prepend the number
        with a dollar sign ($)
    </p>
    <h3>Inheritance</h3>
    <p>Freight and Discount values here will override vendor or form values (but not item) for
        discount and freight. An example usage would be if someone picked up their product at
        the warehouse, and thus did not need to be charged the normal shipping rate.</p>
    <h3>Tiered Values</h3>
    <p>Values are in the form of from:to:discount with each tier seperated by a semi-colon (;).</p>
    <p>You may apply a value regardless of qty, by simply providing a percentage
    or $ amount. You may also provide a from but no to values (i.e. 2:25% minimum 2 items
    to reach a percentage of 25%). All tiers are applied in order. So if you
    have a global first, then a more specific second, and it applies to both, it will
    use the last one.</p>
    <p>For example 25%;2:5:30%;6:35%
    <ol>
        <li>Application will be against 25%, so the order of the item will be 25% by default.</li>
        <li>Will only apply to quantities between 2 and 5. If these apply, the item deiscount percentage will be 30%</li>
        <li>If the quantity ordered is above 6, it will override any previous discount percentage with 35%</li>
    </ol>
    </p>
    <a href="" onclick="toggleHelp('instructions'); return false;">(close help)</a>
</div>
<br>
<table border="0" cellspacing="0" cellpadding="5">
  <tr bgcolor="#fcfcfc"> 
    <td class="fat_black_12">Access</td>
    <td class="fat_black_12">Dealer Name</td>
	<td class="fat_black_12">Dealer Location</td>
	<td class="fat_black_12">Freight Charge</td>
	<td class="fat_black_12">Discount</td>
        <td class="fat_black_12">Markup</td>
        <td class="fat_black_12">Tier</td>
  </tr>
  <form action="form-useraccess.php" method="post" onsubmit="return confirm('Have you verified the freights/discounts with accouting? Click okay if you have, cancel if you have not.')">
    <input type="hidden" name="ID" value="<?php echo $ID ?>">
    <?php
$curvendor = 0;
while( $result = mysql_fetch_Array( $query ) )
{
?>
    <tr> 
		
      <td>
        <input type="checkbox" id="users[<?php echo $result['uid'] ?>][access]" name="users[<?php echo $result['uid'] ?>][access]"  value="Y"<?php if( hasFormAccess( $result['uid'], $ID ) ) echo " CHECKED"; ?>>
      </td>
      <td class="text_12">
        <?php echo $result['last_name']; ?>
      </td>
      <td class="text_12">
        <?php echo $result['first_name']; ?>
      </td>
<?php
  		$fjs = "if (this.checked) { document.getElementById('users[".$result['uid']."][FF]').disabled = false; } else { document.getElementById('users[".$result['uid']."][FF]').disabled = true; document.getElementById('users[".$result['uid']."][FF]').value = document.getElementById('users[".$result['uid']."][FFD]').value; }";
		$fjs = htmlentities($fjs);
		$djs = "if (this.checked) { document.getElementById('users[".$result['uid']."][FD]').disabled = false; } else { document.getElementById('users[".$result['uid']."][FD]').disabled = true; document.getElementById('users[".$result['uid']."][FD]').value = document.getElementById('users[".$result['uid']."][FDD]').value; }";
		$djs = htmlentities($djs);
                $mjs = "if (this.checked) { document.getElementById('users[".$result['uid']."][FM]').disabled = false; } else { document.getElementById('users[".$result['uid']."][FM]').disabled = true; document.getElementById('users[".$result['uid']."][FM]').value = document.getElementById('users[".$result['uid']."][FMD]').value; }";
		$mjs = htmlentities($mjs);
                $ijs = "if (this.checked) { document.getElementById('users[".$result['uid']."][FI]').disabled = false; } else { document.getElementById('users[".$result['uid']."][FI]').disabled = true; document.getElementById('users[".$result['uid']."][FI]').value = document.getElementById('users[".$result['uid']."][FID]').value; }";
                $result['discount'] = loadDiscount('discount',array("user_id" => $result['uid'],"form_id"=>$ID),"user");
                $result['freight'] = loadDiscount('freight',array("user_id" => $result['uid'],"form_id"=>$ID),"user");
                $result['markup'] = loadDiscount('markup',array("user_id" => $result['uid'],"form_id"=>$ID),"user");
                $result['tier'] = loadDiscount('tier',array("user_id" => $result['uid'],"form_id"=>$ID),"user");
?>
		<td class="text_12">
			<div id="FFV_<?php echo $result['uid'] ?>">
				<input type="text" id="users[<?php echo $result['uid']; ?>][FF]" name="users[<?php echo $result['uid']; ?>][FF]" value="<?php if(!$result['freight']) { echo $default_freight;} else {echo $result['freight']; }?>" size="5"<?php if (!$result['freight']) echo " DISABLED"; ?>>
				<input type="checkbox" id="users[<?php echo $result['uid']; ?>][FFC]" name="users[<?php echo $result['uid']; ?>][FFC]" onchange="<?php echo $fjs; ?>" onpropertychange="<?php echo $fjs; ?>" value="Y"<?php if ($result['freight']) echo " CHECKED"; ?>>
				<input type="hidden" id="users[<?php echo $result['uid']; ?>][FFD]" name="users[<?php echo $result['uid']; ?>][FFD]" value="<?php echo $default_freight; ?>">
			</div>
		</td>
		<td class="text_12">
			<div id="FDV<?php echo $result['uid'] ?>">
				<input type="text" id="users[<?php echo $result['uid']; ?>][FD]" name="users[<?php echo $result['uid']; ?>][FD]" value="<?php if(!$result['discount']) { echo $default_discount;} else {echo $result['discount']; }?>" size="5"<?php if (!$result['discount']) echo " DISABLED"; ?>>
				<input type="checkbox" id="users[<?php echo $result['uid']; ?>][FDC]" name="users[<?php echo $result['uid']; ?>][FDC]" onchange="<?php echo $djs; ?>" onpropertychange="<?php echo $djs; ?>" value="Y"<?php if ($result['discount']) echo " CHECKED"; ?>>
				<input type="hidden" id="users[<?php echo $result['uid']; ?>][FDD]" name="users[<?php echo $result['uid']; ?>][FDD]" value="<?php echo $default_discount; ?>">
			</div>
		</td>
                <td class="text_12">
			<div id="FMV<?php echo $result['uid'] ?>">
				<input type="text" id="users[<?php echo $result['uid']; ?>][FM]" name="users[<?php echo $result['uid']; ?>][FM]" value="<?php if(!$result['markup']) { echo $default_markup;} else {echo $result['markup']; }?>" size="5"<?php if (!$result['markup']) echo " DISABLED"; ?>>
				<input type="checkbox" id="users[<?php echo $result['uid']; ?>][FMC]" name="users[<?php echo $result['uid']; ?>][FMC]" onchange="<?php echo $mjs; ?>" onpropertychange="<?php echo $mjs; ?>" value="Y"<?php if ($result['markup']) echo " CHECKED"; ?>>
				<input type="hidden" id="users[<?php echo $result['uid']; ?>][FMD]" name="users[<?php echo $result['uid']; ?>][FMD]" value="<?php echo $default_markup; ?>">
			</div>
		</td>
                <td class="text_12">
			<div id="FIV<?php echo $result['uid'] ?>">
				<input type="text" id="users[<?php echo $result['uid']; ?>][FI]" name="users[<?php echo $result['uid']; ?>][FI]" value="<?php if(!$result['tier']) { echo $default_tier;} else {echo $result['tier']; }?>" size="5"<?php if (!$result['tier']) echo " DISABLED"; ?>>
				<input type="checkbox" id="users[<?php echo $result['uid']; ?>][FIC]" name="users[<?php echo $result['uid']; ?>][FIC]" onchange="<?php echo $ijs; ?>" onpropertychange="<?php echo $ijs; ?>" value="Y"<?php if ($result['tier']) echo " CHECKED"; ?>>
				<input type="hidden" id="users[<?php echo $result['uid']; ?>][FID]" name="users[<?php echo $result['uid']; ?>][FID]" value="<?php echo $default_tier; ?>">
			</div>
		</td>
    </tr>
    <?php
}
?>
    <tr> 
      <td colspan=3 align="center">
        <input type="submit" name="submit1" style="background-color:#CA0000;color:white" value="Submit Changes">
      </td>
    </tr>
  </form>
</table>

<?php
footer($link);
?>
