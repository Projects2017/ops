<?php
require("database.php");
require("secure.php");

function printVendorAddress($form) {
	global $sql;
	$sql = "select vendors.* from forms left join vendors on vendors.ID=forms.vendor where forms.ID=$form";
	$query = mysql_query($sql);
	checkDBError();
	
	if ($result = mysql_fetch_Array($query)) {
		if($result['address'] != "") { echo $result['address']."<br>".$result['city'].", ".$result['state'].". ".$result['zip']."<br>"; }
		if($result['phone'] != "") { echo "PH # ".$result['phone']."<br>"; }
		if($result['fax'] != "") { echo "FAX # ".$result['fax']; }
	}
}

$sql = "select forms.minimum, vendors.name, vendors.ID, vendors.fax from forms left join vendors on vendors.ID=forms.vendor where forms.ID=$ID";
$query = mysql_query($sql);
checkDBError();

if ($result = mysql_fetch_Array($query)) {
	$vendorid = $result['ID'];
	$vendorname = $result['name'];
	$minimum = $result['minimum'];
	$fax = $result['fax'];
}

$sql = "select form_items.partno, form_items.description, form_items.price, form_items.size, form_items.set_, form_items.matt, form_items.box, form_items.stock, form_headers.header, form_items.ID, form_items.stock_day 
 from form_items left join form_headers on form_headers.ID=form_items.header 
 where form_headers.form=$ID order by form_headers.display_order,form_items.display_order";
$sql = "SELECT form_items.*, form_headers.header FROM form_items LEFT JOIN form_headers ON form_headers.ID=form_items.header WHERE form_headers.form=${ID} ORDER BY form_headers.display_order,form_items.display_order";
$query = mysql_query($sql);
checkDBError();

// Figure out which columns are not completely blank
$column_names = array("partno", "description", "price", "size", "set_", "matt", "box");
$display_column = array();
foreach ($column_names as $column) {
	$display_column[$column] = false;
}
while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
	foreach ($column_names as $column) {
		if ($row[$column] != "")
			$display_column[$column] = true;
	}
}

$numcolumns = 4;
foreach ($column_names as $column) {
	if ($display_column[$column])
		$numcolumns++;
}

if ($display_column['matt'])
	$numcolumns++;
if ($display_column['set_'])
	$numcolumns++;

?>
<html>
<head>
	<title>RSS</title>
<link rel="stylesheet" href="styles.css" type="text/css">
<script language="javascript">
<!--
function featureWindow(filename) {
	popUp = window.open('photos/'+filename,'featureWin','width=500,height=400');
}
-->
</script>
</head>

<body bgcolor="EDECDA" leftmargin="10" topmargin="10" marginwidth="10" marginheight="10">
<table width="700" border="0">
  <tr valign="top"> 
    <td height="30" class="fat_black"> 
      <?php echo $vendorname; ?>
	</td>
	<td align="right"><!--<a href="" onclick="window.print(); return false;">Print</a>--><a href="form-print.php?ID=<?php=$ID ?>" target="_new">Printer Friendly View</a> &nbsp;&nbsp;<font color="#0000FF"><span class="text_12">Minimum: </span> 
      <span class="text_12"><b><?php echo $minimum ?></b></span></font></td>
</tr>

<?php
if(file_exists($basedir."logos/".$vendorid.".jpg"))
{
?>
<tr><td colspan="2"><img src="logos/<?php echo $vendorid ?>.jpg"></td></tr>
<?php
}
?>
<tr><td colspan="2" class="fat_black_12">Click on the thumbnail images to view product photos full-size.</td></tr>
</table>
<?php
$query = mysql_query($sql);
checkDBError();
?>
<form name="frm" action="form-confirm.php" method="post">
	<table width="700" border="1" cellspacing="0" cellpadding="4" bgcolor="#FFFFFF">
	<tr class="orderTHRow"> 
	  	<?php
			if ($display_column['partno'])
				print "<td>Part #</td>";
			print "<td>Photo</td>";
			if ($display_column['description'])
				print "<td>Description</td>";
			if ($display_column['size'])
				print "<td>Size</td>";
			//print "<td>Volume</td>";
			if ($display_column['price'])
				print "<td>Price</td>";
			if ($display_column['set_'])
				print "<td>Set</td>"
					. "<td width=\"75\">Qty</td>";
			if ($display_column['matt'])
				print "<td>Matt</td>"
					. "<td width=\"75\">Qty</td>";
			if ($display_column['box'])
				print "<td>Box</td>";
		?>
		<td width="75" class="orderTH">Stock</td>
		<td width="75" class="orderTH">Qty</td>
		<td width="75" class="orderTH">Total</td>
	</tr>
<?php
function writeCell($value)
{
	if($value == "") $value = "&nbsp;";
	if(stristr($value, "$") != false) $align = " align=\"right\"";
	echo "\t\t\t<td class=\"text_12$align\">$value</td>\n";
}

function writeField($name, $f)
{
	global $qtys;
	$qtys++;
	print "\t\t\t<td class=\"text_12\"><input type=\"text\" name=\"${name}${f}\" size=\"5\" maxlength=\"10\" value=\"0\" onchange=\"recalctotal();\" /></td>\n";
}

$oldheader = "";
$f = 0;
while($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
	$newheader = $row['header'];
	if($oldheader != $newheader) {
		print "\t\t<tr><td align=\"center\" colspan=\"${numcolumns}\" class=\"fat_black_12\">$newheader</td><tr>\n";
		$oldheader = $newheader;
	}
	
	$qtys = 0;

	$price = $row['box'];
	if ($price == "") {
		$price = $row['price'];
		if ($price == "")
			$price = 0;
	}
	
	$price = str_replace("$", "", $price);
	$set = str_replace("$", "", $row['set_']);
	$matt = str_replace("$", "", $row['matt']);
	$vol = $row['cubic_ft'];
	
	$setfield = false;
	$mattfield = false;
	print "\t\t<input type=\"hidden\" name=\"price${f}\" value=\"$price\" />\n";
	print "\t\t<input type=\"hidden\" name=\"set${f}\" value=\"$set\" />\n";
	print "\t\t<input type=\"hidden\" name=\"matt${f}\" value=\"$matt\" />\n";
	
	print "\t\t<tr>\n";
	// Part Number
	if ($display_column['partno'])
		writeCell($row['partno']);
	// Photo
	print "\t\t\t<td class=\"text_12\">";
	$item_id = $row['ID'];
	if (file_exists("${basedir}photos/${item_id}.jpg"))
		print "<a href=\"javascript:featureWindow('${item_id}.jpg');\"><img src=\"photos/t${item_id}.jpg\" alt=\"photo\" border=\"0\"></a>";
	else
		print "&nbsp;";
	print "</td>\n";
	// Description
	if ($display_column['description'])
		writeCell($row['description']);
	// Size
	if ($display_column['size'])
		writeCell($row['size']);
	// Volume
	//writeCell("42");
	// Price
	if ($display_column['price'])
		writeCell(makeThisLookLikeMoney($row['price']));
	// Set
	if ($display_column['set_']) {
		writeCell(makeThisLookLikeMoney($row['set_']));
		writeField("setqty", $f);
		$setfield = true;
	}
	// Matt
	if ($display_column['matt']) {
		writeCell(makeThisLookLikeMoney($row['matt']));
		writeField("mattqty", $f);
		$mattfield = true;
	}
	// Box
	if ($display_column['box'])
		writeCell(makeThisLookLikeMoney($row['box']));
	
	if(!$setfield)
		print "\t\t\t<input type=\"hidden\" name=\"setqty${f}\" value=\"0\">\n";
	if (!$mattfield)
		print "\t\t\t<input type=\"hidden\" name=\"mattqty${f}\" value=\"0\">\n";
	
	// Stock
	$stock = stock_status($row['stock']);
	$stock_style = $stock['style'];
	$stock_text = $stock['name'];
	$stock_day = $row['stock_day'];
	if ($stock_day)
		$stock_text = "${stock_text} (${stock_day})";
	print "\t\t\t<td class=\"text_12\" style=\"${stock_style}\">${stock_text}</td>\n";

	if ($stock['block_order'] == 'Y') {
		$input_type = "hidden";
		$qty_value = "0";
		$total_value = "0";
	} else {
		$input_type = "text";
		$qty_value = "";
		$total_value = "";
	}
	// Qty
	print "\t\t\t<td class=\"text_12\">";
	print "<input type=\"${input_type}\" name=\"qty${f}\" size=\"5\" maxlength=\"10\" value=\"0\" onchange=\"recalctotal();\" />";
	print $qty_value;
	print "</td>\n";
	// Total
	print "\t\t\t<td class=\"text_12\">$";
	print "<input type=\"hidden\" name=\"totalvol${f}\" size=\"5\" maxlength=\"10\" />"; // Total Vol
	print "<span id=\"dtotal${f}\">0</span>";
	print "<input type=\"hidden\" name=\"total${f}\" size=\"6\" maxlength=\"10\" />";
	print "</td>\n";
	
	print "\t\t\t<input type=\"hidden\" name=\"item${f}\" value=\"${item_id}\" />\n";
	print "\t\t<input type=\"hidden\" name=\"vol${f}\" value=\"$vol\" />\n";
	print "\t\t</tr>\n";
	$f++;
}
?>
        <tr> 
        	<td align="right" colspan="<?php echo $numcolumns-2; ?>" class="fat_black">INVOICE TOTAL: </td>
			<td class="fat_black"><span id="dgrandtotalvol">0</span>cft.<input type="hidden" disabled name="grandtotalvol" size="5" maxlength="10"></td>
			<td class="fat_black">$<span id="dgrandtotal">0</span><input type="hidden" disabled name="grandtotal" size="8" maxlength="10"></td>
        </tr>
		<input type="hidden" name="num_of_items" value="<?php echo $f; ?>">
		<input type="hidden" name="form" value="<?php echo $ID; ?>">
		<input type="hidden" name="vendorid" value="<?php echo $vendorid; ?>">
		<tr>
			<td colspan="<?php echo $numcolumns-2; ?>">&nbsp;</td>
			<td align="center" colspan="2"><input type="submit" value="Preview Order"></td>
		</tr>
	</table>
</form>
	
<script language="JavaScript1.2">

recalctotal();

function recalctotal()
{
	grandtotal = 0;
	grandtotalvol = 0;
	cols = 10;
	
	for( c = 0; c < Math.floor(frm.elements.length / cols); c++ )
	{
		total = 0;
		totalvol = 0;
		off = c*cols;
		unit_price = frm.elements[off+0].value;
		set_price = frm.elements[off+1].value;
		matt_price = frm.elements[off+2].value;
		unit_vol = frm.elements[off+9].value;

		if (unit_price != "") {
			unit_qty = frm.elements[off+5].value;
			if (unit_qty == "" || isNaN(unit_qty))
				frm.elements[off+5].value = "0";
			else {
				if (!isNaN(unit_price))
					total += unit_qty * unit_price;
				if (!isNaN(unit_vol))
					totalvol += unit_qty * unit_vol;
			}
		}

		if (set_price != "") {
			set_qty = frm.elements[off+3].value;
			if (set_qty == "" || isNaN(set_qty))
				frm.elements[off+3].value = "0";
			else {
				if (!isNaN(set_price))
					total += set_qty * set_price;
			}
		}

		if (matt_price != "") {
			matt_qty = frm.elements[off+4].value;
			if (matt_qty == "" || isNaN(matt_qty))
				frm.elements[off+4] = "0";
			else {
				if (!isNaN(matt_price))
					total += matt_qty * matt_price;
			}
		}
			
		frm.elements[off+7].value = total;
		document.getElementById('dtotal' + c).innerHTML = total;
		frm.elements[off+6].value = totalvol;
		grandtotal += total;
		grandtotalvol += totalvol;
	}

	frm.grandtotal.value = grandtotal;
	document.getElementById('dgrandtotal').innerHTML = grandtotal;
	frm.grandtotalvol.value = grandtotalvol;
	document.getElementById('dgrandtotalvol').innerHTML = grandtotalvol;
}
</script>
<?php mysql_close($link); ?>
</body>
</html>
