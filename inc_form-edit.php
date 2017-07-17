<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');
if ($MoS_enabled) {
	$prefix = 'MoS_';
} else {
	$prefix = '';
}
$gExtraJS = <<<EOD
var gHeaderFormDiv = false;
var gHeaderFormId = 0;
function formedit_formsubmit(id) {
	gHeaderFormId = id;
	gFormAlertResult = function(text) { }
	gFormPreRequest = function(thisTag) {
		gHeaderFormDiv = getParent(thisTag, 'div');
		gHeaderFormDiv.style.display = 'none';
	}
	gFormPostProcess = function(discard) {
		var div = gHeaderFormDiv;
		secreq = getHTTPObject();
		secreq.onreadystatechange = function()
		{
			if( secreq.readyState == 4 )
			{
				if (secreq.responseText) {
					gHeaderFormDiv.innerHTML = secreq.responseText;
				} else {
					gHeaderFormDiv.innerHTML = 'Error';
				}
				gHeaderFormId = 0;
				gHeaderFormDiv.style.display = '';
				gHeaderFormDiv = false;
			}
		}
		query = 'ajax_header_id=' + gHeaderFormId;
		secreq.open( 'POST', 'inc_itemheaderedit.php', true );
		secreq.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
		secreq.setRequestHeader( 'Content-Length', query.length );
		secreq.send( query );
	}
	
}
EOD;
if ($MoS_enabled) {
	require("MoS_menu.php");
} else {
	require("menu.php");
}

$sql = "select a.*, b.name AS vendorname from ".$prefix."forms AS a left join vendors AS b on b.ID=a.vendor where a.ID=$ID";
$query = mysql_query($sql);
checkDBError();

if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
	$vendorname = $result['vendorname'];
	$minimum = $result['minimum'];
	$name = $result['name'];
	$vendor = $result['vendor'];
        $minimum = $result['minimum'];
        /* If Minimum has a specification, extract it */
        if (count(explode(":::",$minimum)) == 2) {
            $min_type = explode(":::",$minimum);
            $minimum = $min_type[1];
            $min_type = $min_type[0];
        }
}

?>
<br>
<table width="800" border="0" cellpadding="5" cellspacing="0">
<tr>
	<td colspan="2" align="right" class="text_12">
		[<a href="<?php= $prefix ?>form-prefs.php">Display Preferences</a>]
	</td>
</tr>
<form action="<?php echo $prefix; ?>forms.php" name='form_form' method="post">
<tr>
	<td class="text_12">
		<b>Vendor:</b>&nbsp;<select name="vendor">
<?php
$sql = "SELECT ID, name FROM vendors ORDER BY name";
$query = mysql_query($sql);
while ($row = mysql_fetch_array($query)) {
	echo "\t\t\t<option value=\"".$row['ID']."\"";
	if ($vendor == $row['ID']) {
		echo " SELECTED";
	}
	echo ">".$row['name']."</option>\n";
}
?>
		</select>
	</td>
	<td align="right" valign="bottom" class="text_12">
		<?php
                $freight = loadDiscount('freight',array("form_id" => $ID),$prefix."form");
                if (!$freight) $freight = null;
                $freight = new DBField('freight', 'form_feight','6',$freight,true);
                $freight->prefix = '[TITLE]: ';
                $freight->type = 'text';
                $freight->null_value = loadDiscount('freight',array("vendor_id" => $vendor),"vendor");
                $freight->null_default = '0.00%';
                $freight->display();
                $discount = loadDiscount('discount',array("form_id" => $ID),$prefix."form");
                if (!$discount) $discount = null;
                $discount = new DBField('discount', 'form_discount','6',$discount, true);
                $discount->prefix = '[TITLE]: ';
                $discount->type = 'text';
                $discount->null_value = loadDiscount('discount',array("vendor_id" => $vendor),"vendor");
                $discount->null_default = '0.00%';
                $discount->display();
		?>
	</td>
</tr>
  <tr>
	<td class="text_12">
		<b>Form&nbsp;Name:</b>&nbsp;<input type="text" name="name" size="50" value="<?php echo htmlentities($name); ?>"><input type="hidden" name="action" value="massupdate"><input type="hidden" name="ID" value="<?php echo $ID; ?>">
	</td>
	<td align="right" valign="bottom" class="text_12"><b>Minimum:</b>&nbsp;<input type="text" name="minimum" value="<?php echo $minimum; ?>" size="10"> &nbsp;<SELECT name='min_type'><OPTION value='D' <?php if ($min_type == 'D') echo 'SELECTED'; ?>>$</OPTION><OPTION value='P' <?php if ($min_type == 'P') echo 'SELECTED'; ?>>#</OPTION></SELECT> <?php echo $min_js; ?></td>
</tr>
<tr>
	<td>
		<input type="submit" style="background-color:#CA0000;color:white" value="Edit Form Name, Vendor &amp; Miniumum">
	</td>
</tr>
</form>
<tr>
	<td> <span class="text_12">To get started on the form, enter a heading.
      After a header<br>
      has been created, you may enter items under the heading.</span><br>
	<form action="<?php echo $prefix; ?>form-header-add.php" method="post">
	    <p>
          <input type="hidden" name="form" value="<?php echo $ID; ?>">
          <input type="text" name="header" size="30" maxlength="100">
          <input type="submit" style="background-color:#CA0000;color:white" value="Add Heading">
        </p>
      </form>
        <p>
			<b><?php
			if ($result['header_order'] == 'manual') {
				?>[<a href="<?php echo $prefix; ?>form-order.php?ID=<?php echo $ID; ?>">Reorder Headers</a>]<?php
			} elseif ($result['header_order'] == 'ascending') {
				?>[Form Ordered Ascending]<?php
			} elseif ($result['header_order'] == 'decending') {
				?>[Form Ordered Decending]<?php
			}
			?></b> &nbsp;&nbsp;&nbsp;
			<?php if (!$MoS_enabled) { ?>
			<b>[<a href="csv_export.php?ID=<?php echo $ID; ?>&name=<?php echo urlencode($name);?>">Export to CSV</a>]</b>
			<?php } ?>
		</p>
	<?php if (!$MoS_enabled) { ?>
	</td>
	<td valign=top align=center> <span class="text_12"><B>OR</B> you can import an entire form from a CSV file.</span><br>
		<form action="csv_import.php" method="post" enctype="multipart/form-data">
			<p>
				<input type="hidden" name="form" value="<?php echo $ID; ?>">
				<input type="file" name="csvfile">
	            <input type="submit" style="background-color:#CA0000;color:white" value="Submit CSV">
			</p>
		</form>
	<?php } ?>
	</td>
</tr>
</table>
<?php
$sql = "select * from ".$prefix."form_headers where form=$ID order by display_order";
$query = mysql_query($sql);
checkDBError();

while ($header_result = mysql_fetch_array($query, MYSQL_ASSOC)) {
?>
<div name="headeredit">
<?php include('inc_itemheaderedit.php'); ?>
</div>
<?php
} ?>
<?php if (!$MoS_enabled) { footer($link); } ?>
