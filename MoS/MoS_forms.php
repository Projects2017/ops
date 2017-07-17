<?php
require("MoS_database.php");
require("MoS_admin_secure.php");
require( "../inc_orders.php" );

$tablename = "MoS_forms";
$self = $_SERVER['PHP_SELF'];

$fields = Array();
DBcreateFields( $tablename, &$fields, "<tr><td class=\"fat_black_12\">[TITLE]: </td><td>", "</td></tr>" );
$action = strtolower( $action );
switch( $action )
{
case "update":
	$sql = buildUpdateQuery( $tablename, "ID=$ID" );
	mysql_query( $sql );
	checkDBError($sql);
	$result = resortheaders($ID, 'MoS_');
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('form', $ID);
	}
        if ($freight_null == "N") {
            if (!$freight) $freight = '0.00%';
            saveDiscount($freight,'freight',array("form_id" => $ID),"MoS_form");
        } else {
            deleteDiscount('freight',array("form_id" => $ID),"MoS_form");
        }
        if ($discount_null == 'N') {
            if (!$discount) $discount = '0.00%';
            saveDiscount($discount,'discount',array("form_id" => $ID),"MoS_form");
        } else {
            deleteDiscount('discount',array("form_id" => $ID),"MoS_form");
        }
	header( "Location: MoS_edit-forms-edit.php?ID=$ID" );
	exit;
break;

case "massupdate":
        //echo "<pre>"; print_r($GLOBALS); echo "</pre>";
        if ($minimum) {
            if ($min_type == 'P') {
                $minimum = 'P:::'.$minimum;
            } else {
                $minimum = 'D:::'.$minimum;
            }
        }
        $sql = "UPDATE `MoS_forms` SET `vendor` = '".$vendor."', `name` = '".$name."', `minimum` = '".$minimum."' WHERE `ID` = '".$ID."'";
	mysql_query( $sql );
	checkDBError($sql);
	$result = resortheaders($ID);
	if ($result == -1) { // If it was a manual update, we're not getting updated as apart of the rest
		snapshot_update('form', $ID);
	}
        if ($freight_null == "N") {
            if (!$freight) $freight = '0.00%';
            saveDiscount($freight,'freight',array("form_id" => $ID),"MoS_form");
        } else {
            deleteDiscount('freight',array("form_id" => $ID),"MoS_form");
        }
        if ($discount_null == 'N') {
            if (!$discount) $discount = '0.00%';
            saveDiscount($discount,'discount',array("form_id" => $ID),"MoS_form");
        } else {
            deleteDiscount('discount',array("form_id" => $ID),"MoS_form");
        }
        //print_r(array($freight_null,$freight, $discount_null,$discount));
	header( "Location: MoS_edit-forms-edit.php?ID=$ID" );
	exit;
break;

case "delete":
	$sql = "delete from $tablename where ID=$ID";
	mysql_query( $sql );
	checkDBError($sql);
	snapshot_update('form', $ID);
        deleteDiscount('freight',array("form_id" => $ID),"MoS_form");
        deleteDiscount('discount',array("form_id" => $ID),"MoS_form");
	header( "Location: $self?vendor=$vendor" );
	exit;
break;

case "create":
	$sql = buildInsertQuery( $tablename );
	mysql_query( $sql );
	checkDBError($sql);
	$ID = mysql_insert_ID();
	snapshot_update('form', $ID);
        saveDiscount($discount,'discount',array("form_id" => $ID),"MoS_form");
        saveDiscount($freight,'freight',array("form_id" => $ID),"MoS_form");
	header( "Location: MoS_edit-forms-edit.php?ID=$ID" );
	exit;
break;

case "revert":
	$sql = "DELETE FROM MoS_forms WHERE ID = $ID";
	mysql_query($sql);
        checkDBerror($sql);
        $sql = "DELETE FROM MoS_form_discount WHERE `form_id` = ".$ID;
        mysql_query($sql);
        checkDBerror($sql);
        $sql = "DELETE FROM MoS_form_freight WHERE `form_id` = ".$ID;
        mysql_query($sql);
        checkDBerror($sql);
	$sql = "SELECT ID FROM MoS_form_headers WHERE form = $ID";
	$result = mysql_query($sql);
        checkDBerror($sql);
	while ($line = mysql_fetch_row($result)) {
                $sql = "SELECT ID FROM MoS_form_items WHERE header = " .$line[0];
                $result_item = mysql_query($sql);
                checkDBerror($sql);
                while ($line_item = mysql_fetch_assoc($result_item)) {
                    $sql = "DELETE FROM MoS_form_item_discount WHERE item_id = ".$line_item['ID'];
                    mysql_query($sql);
                    checkDBerror($sql);
                    $sql = "DELETE FROM MoS_form_item_freight WHERE item_id = ".$line_item['ID'];
                    mysql_query($sql);
                    checkDBerror($sql);
                }
		$sql = "DELETE FROM MoS_form_items WHERE header = " . $line[0];
		mysql_query($sql);
                checkDBerror($sql);
	}
	$sql = "DELETE FROM MoS_form_headers WHERE form = $ID";
	mysql_query($sql);
        checkDBerror($sql);
	$sql = "DELETE FROM MoS_director WHERE MoS_form_id = $ID";
	mysql_query($sql);
        checkDBerror($sql);
	header( "Location: MoS_edit-forms-view.php" );
break;
/*
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
    	$heads = db_copy_row("form_items",0,$head['oldid'],"header",$head['newid']);
    }

	snapshot_update('form', $newform);
    // Go straight to editing new form
    header( "Location: MoS_edit-forms-edit.php?ID=$newform" );
	exit;
break; */
case "view":
	require( "MoS_menu.php" ); 

	if( $ID == "" ) $action = "create"; 
	else 
	{
		$sql = "select * from $tablename where ID=$ID";
		$query = mysql_query( $sql );
		checkDBError($sql);
		$action = "update";
		
		if( $result = mysql_fetch_array( $query ) )
		{
			$fields[0]->value=$result['name'];
			$fields[2]->value=$result['minimum'];
			$fields[3]->value=$result['address'];
			$fields[4]->value=$result['city'];
			$fields[5]->value=$result['state'];
			$fields[6]->value=$result['zip'];
			$fields[7]->value=$result['phone'];
			$fields[8]->value=$result['fax'];
			$fields[9]->value=$result['discount'];
			$fields[10]->value=$result['freight'];
			$vendor = $result['vendor'];
		}
	}
	$fields[1]->display = false;
	$fields[11]->display = false; // Make Snapshot field disapper!
	 ?>
<title>RSS Market Order System Administration</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
	
	<form action="<?php echo $self ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="vendor" value="<?php echo $vendor ?>">
	<input type="hidden" name="ID" value="<?php echo $ID ?>">
	<br>
	<table border="0" cellspacing="5" cellpadding="0">
	<?php
	DBdisplayFields( &$fields );
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
}
?>
