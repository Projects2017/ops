<?php
require( "database.php" );
require( "secure.php" );
require( "menu.php" );

if (!is_numeric($ID))
    die("ID must be numeric and present");

function hasFormAccess ($user, $form) {
    $sql = "select ID from form_access where user=$user and form=$form";
    $query = mysql_query( $sql );
    checkDBerror();

    if( mysql_num_rows( $query ) > 0 )
        return true;
    return false;
}

if( $submit1 != "" ) {
//-- Remove all form access
    //$sql = "delete from form_access where form=$ID";
    //mysql_query($sql);
    //checkDBError();
    //die("<pre>".print_r($_POST['forms'],true)."</pre>");

    foreach($_POST['forms'] as $key => $value) {
    //-- Add access
        if ($value['access'] == "Y") {
            $sql = "select `id` FROM `form_access` WHERE `user` = '".$ID."' AND `form` = '".$key."'";
            $query = mysql_query($sql);
            checkDBerror($sql);
            if (!mysql_num_rows($query)) {
                $sql = "insert into form_access (`user`,`form`) values ('".$ID."', '".$key."')";
                mysql_query($sql);
                checkDBError($sql);
            }
        } else {
            $sql = "delete from `form_access` where `user` = '".$ID."' and `form` = '".$key."'";
            mysql_query($sql);
            checkDBerror($sql);
        }
        //-- Freight
        if (!is_null($value['FF'])) {
            if (!$value['FF']) $value['FF'] = '0.00%';
            saveDiscount($value['FF'],'freight',array("user_id" => $ID,"form_id"=>$key),"user");
        } else {
            deleteDiscount('freight',array("user_id" => $ID,"form_id"=>$key),"user");
        }
        //-- Discount
        if (!is_null($value['FD'])) {
            if (!$value['FD']) $value['FD'] = '0.00%';
            saveDiscount($value['FD'],'discount',array("user_id" => $ID,"form_id"=>$key),"user");
        } else {
            deleteDiscount('discount',array("user_id" => $ID,"form_id"=>$key),"user");
        }
        //-- Markup
        if (!is_null($value['FM'])) {
            if (!$value['FM']) $value['FM'] = '0.00%';
            saveDiscount($value['FM'],'markup',array("user_id" => $ID,"form_id"=>$key),"user");
        } else {
            deleteDiscount('markup',array("user_id" => $ID,"form_id"=>$key),"user");
        }
        //-- Tier
        if (!is_null($value['FI'])) {
            if (!$value['FI']) $value['FI'] = '0.00%';
            saveDiscount($value['FI'],'tier',array("user_id" => $ID,"form_id"=>$key),"user");
        } else {
            deleteDiscount('tier',array("user_id" => $ID,"form_id"=>$key),"user");
        }
    }
}


if ($action) {
    $sql = "select ID from forms where `alloworder` = 'Y'";
    $query = mysql_query( $sql );
    checkDBError($sql);
    while( $result = mysql_fetch_Array( $query ) ) {
        if ($action == 'enableall') {
            $sql = "select id from form_access WHERE form = ".$result['ID']." AND user = '".$ID."'";
            $query2 = mysql_query($sql);
            checkdberror($sql);
            if (!mysql_num_rows($query2)) {
                $sql = "insert into form_access (form,user) Values (".$result['ID'].", '".$ID."')";
                mysql_query($sql);
                checkdberror($sql);
            }
        } elseif ($action == 'disableall') {
            $sql = "delete from form_access WHERE form = ".$result['ID']." AND user = '".$ID."'";
            mysql_query($sql);
            checkdberror($sql);
            deleteDiscount('freight',array("form_id" => $result['ID'],"user_id"=>$ID),"user");
            deleteDiscount('discount',array("form_id" => $result['ID'],"user_id"=>$ID),"user");
            deleteDiscount('markup',array("form_id" => $result['ID'],"user_id"=>$ID),"user");
            deleteDiscount('tier',array("form_id" => $result['ID'],"user_id"=>$ID),"user");
        }
    }
}

$sql = "select first_name, last_name from users where ID = ".$ID;
$query = mysql_query( $sql );
checkDBError();

if( mysql_num_rows( $query ) > 0 )
    assignFieldsToVars( $query );


// Find out what the "All Dealers" Frieght is as well
/*
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
 *
 */

//$sql = "select ID, username, first_name, last_name from users where disabled != 'Y' ORDER BY last_name, first_name";
//$sql = "select users.ID as uid, users.first_name, users.last_name " .
//		"FROM users  " .
//		"WHERE users.disabled !='Y' ORDER BY users.last_name, users.first_name";
//$query = mysql_query( $sql );
//checkDBError();

$sql = "select forms.ID as `fid`, forms.vendor as `vendor_id`, forms.name, vendors.name as vendor from forms inner join vendors on vendors.ID = forms.vendor where forms.alloworder = 'Y' ORDER BY `vendor_id`, `forms`.`name`";
$query = mysql_query( $sql );
checkDBError( $sql );

if( mysql_num_rows( $query ) == 0 ) {
    ?>
<title>RSS Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">

<div>
    <span class="fat_red">No Vendors In The Database!</span>
</div>
    <?php
    footer();
    exit;
}
?><br>
<div class="fat_black"><?php echo $last_name.", ".$first_name; ?></div>
<br>
<a href="users-formaccess.php?ID=<?php echo $ID; ?>&action=enableall">Enable All</a> | <a href="users-formaccess.php?ID=<?php echo $ID; ?>&action=disableall">Disable All</a> | <a href="" onclick="toggleHelp('instructions'); return false;">Help</a>
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
<br />    
<form action="users-formaccess.php" method="post" onsubmit="return confirm('Have you verified the freights/discounts with accouting? Click okay if you have, cancel if you have not.')">
    <input type="hidden" name="ID" value="<?php echo $ID ?>">
    <table border="0" cellspacing="0" cellpadding="5">
        <tr bgcolor="#fcfcfc">
            <td class="fat_black_12">Access</td>
            <td class="fat_black_12">Name</td>
            <td class="fat_black_12">Freight Charge</td>
            <td class="fat_black_12">Discount</td>
            <td class="fat_black_12">Markup</td>
            <td class="fat_black_12">Tier</td>
        </tr>

        <?php
        $curvendor = 0;
        while( $result = mysql_fetch_Array( $query ) ) {
            /* Display Vendor Row if needed */
            if ($curvendor != $result['vendor_id']) {
                $curvendor = $result['vendor_id'];
                $freight_vdefault = loadDiscount('freight',array("vendor_id" => $result['vendor_id']),"vendor");
                $discount_vdefault = loadDiscount('discount',array("vendor_id" => $result['vendor_id']),"vendor");
                $markup_vdefault = ''; // No vendor level default at this point
                $tier_vdefault = ''; // No vendor level default at this point
                ?>
        <tr bgcolor="#fcfcfc">
            <td class="fat_black_12" colspan="6" align="left">
                <?php echo $result['vendor']; ?>
            </td>
        </tr>
            <?php
            }
            ?>
        <tr>

            <td>
                <input type="checkbox" id="forms[<?php echo $result['fid'] ?>][access]" name="forms[<?php echo $result['fid'] ?>][access]"  value="Y"<?php if( hasFormAccess( $ID, $result['fid'] ) ) echo " CHECKED"; ?>>
            </td>
            <td class="text_12">
                    <?php echo $result['name']; ?>
            </td>
            <?php
            $default_freight = loadDiscount('freight',array("form_id" => $result['fid']),"form");
            if (!$default_freight) $default_freight = $freight_vdefault;
            $default_discount = loadDiscount('discount',array("form_id" => $result['fid']),"form");
            if (!$default_discount) $default_discount = $discount_vdefault;
            $discount_markup = ''; // No default markup for vendors implimented yet.
            if (!$default_tier) $default_tier = $tier_vdefault;
            $tier_markup = ''; // No default markup for vendors implimented yet.
            

            $fjs = "if (this.checked) { document.getElementById('forms[".$result['fid']."][FF]').disabled = false; } else { document.getElementById('forms[".$result['fid']."][FF]').disabled = true; document.getElementById('forms[".$result['fid']."][FF]').value = document.getElementById('forms[".$result['fid']."][FFD]').value; }";
            $fjs = htmlentities($fjs);
            $djs = "if (this.checked) { document.getElementById('forms[".$result['fid']."][FD]').disabled = false; } else { document.getElementById('forms[".$result['fid']."][FD]').disabled = true; document.getElementById('forms[".$result['fid']."][FD]').value = document.getElementById('forms[".$result['fid']."][FDD]').value; }";
            $djs = htmlentities($djs);
            $mjs = "if (this.checked) { document.getElementById('forms[".$result['fid']."][FM]').disabled = false; } else { document.getElementById('forms[".$result['fid']."][FM]').disabled = true; document.getElementById('forms[".$result['fid']."][FM]').value = document.getElementById('forms[".$result['fid']."][FMD]').value; }";
            $mjs = htmlentities($mjs);
            $ijs = "if (this.checked) { document.getElementById('forms[".$result['fid']."][FI]').disabled = false; } else { document.getElementById('forms[".$result['fid']."][FI]').disabled = true; document.getElementById('forms[".$result['fid']."][FI]').value = document.getElementById('forms[".$result['fid']."][FID]').value; }";
            $ijs = htmlentities($ijs);
            $result['discount'] = loadDiscount('discount',array("form_id" => $result['fid'],"user_id"=>$ID),"user");
            $result['freight'] = loadDiscount('freight',array("form_id" => $result['fid'],"user_id"=>$ID),"user");
            $result['markup'] = loadDiscount('markup',array("form_id" => $result['fid'],"user_id"=>$ID),"user");
            $result['tier'] = loadDiscount('tier',array("form_id" => $result['fid'],"user_id"=>$ID),"user");
            ?>
            <td class="text_12">
                <div id="FFV_<?php echo $result['fid'] ?>">
                    <input type="text" id="forms[<?php echo $result['fid']; ?>][FF]" name="forms[<?php echo $result['fid']; ?>][FF]" value="<?php if(!$result['freight']) { echo $default_freight;} else {echo $result['freight']; }?>" size="5"<?php if (!$result['freight']) echo " DISABLED"; ?>>
                    <input type="checkbox" id="forms[<?php echo $result['fid']; ?>][FFC]" name="forms[<?php echo $result['fid']; ?>][FFC]" onchange="<?php echo $fjs; ?>" onpropertychange="<?php echo $fjs; ?>" value="Y"<?php if ($result['freight']) echo " CHECKED"; ?>>
                    <input type="hidden" id="forms[<?php echo $result['fid']; ?>][FFD]" name="forms[<?php echo $result['fid']; ?>][FFD]" value="<?php echo $default_freight; ?>">
                </div>
            </td>
            <td class="text_12">
                <div id="FDV<?php echo $result['fid'] ?>">
                    <input type="text" id="forms[<?php echo $result['fid']; ?>][FD]" name="forms[<?php echo $result['fid']; ?>][FD]" value="<?php if(!$result['discount']) { echo $default_discount;} else {echo $result['discount']; }?>" size="5"<?php if (!$result['discount']) echo " DISABLED"; ?>>
                    <input type="checkbox" id="forms[<?php echo $result['fid']; ?>][FDC]" name="forms[<?php echo $result['fid']; ?>][FDC]" onchange="<?php echo $djs; ?>" onpropertychange="<?php echo $djs; ?>" value="Y"<?php if ($result['discount']) echo " CHECKED"; ?>>
                    <input type="hidden" id="forms[<?php echo $result['fid']; ?>][FDD]" name="forms[<?php echo $result['fid']; ?>][FDD]" value="<?php echo $default_discount; ?>">
                </div>
            </td>
            <td class="text_12">
                <div id="FMV<?php echo $result['fid'] ?>">
                    <input type="text" id="forms[<?php echo $result['fid']; ?>][FM]" name="forms[<?php echo $result['fid']; ?>][FM]" value="<?php if(!$result['markup']) { echo $default_markup;} else {echo $result['markup']; }?>" size="5"<?php if (!$result['markup']) echo " DISABLED"; ?>>
                    <input type="checkbox" id="forms[<?php echo $result['fid']; ?>][FMC]" name="forms[<?php echo $result['fid']; ?>][FMC]" onchange="<?php echo $mjs; ?>" onpropertychange="<?php echo $mjs; ?>" value="Y"<?php if ($result['markup']) echo " CHECKED"; ?>>
                    <input type="hidden" id="forms[<?php echo $result['fid']; ?>][FMD]" name="forms[<?php echo $result['fid']; ?>][FMD]" value="<?php echo $default_markup; ?>">
                </div>
            </td>
            <td class="text_12">
                <div id="FIV<?php echo $result['fid'] ?>">
                    <input type="text" id="forms[<?php echo $result['fid']; ?>][FI]" name="forms[<?php echo $result['fid']; ?>][FI]" value="<?php if(!$result['tier']) { echo $default_tier;} else {echo $result['tier']; }?>" size="5"<?php if (!$result['tier']) echo " DISABLED"; ?>>
                    <input type="checkbox" id="forms[<?php echo $result['fid']; ?>][FIC]" name="forms[<?php echo $result['fid']; ?>][FIC]" onchange="<?php echo $ijs; ?>" onpropertychange="<?php echo $ijs; ?>" value="Y"<?php if ($result['tier']) echo " CHECKED"; ?>>
                    <input type="hidden" id="forms[<?php echo $result['fid']; ?>][FID]" name="forms[<?php echo $result['fid']; ?>][FID]" value="<?php echo $default_tier; ?>">
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

    </table>
</form>
<?php
footer($link);
?>
