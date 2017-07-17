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

$sql = "select form_items.partno, form_items.description, form_items.price, form_items.size, form_items.color, form_items.set_, form_items.matt, form_items.box, form_headers.header, form_items.ID 
 from form_items left join form_headers on form_headers.ID=form_items.header 
 where form_headers.form=$ID order by form_headers.display_order,form_items.display_order";
$query = mysql_query($sql);
checkDBError();

//figure out what fields we need
$fields = array();
for( $c = 0; $c < 8; $c++ )
	$fields[$c] = false;

while($result = mysql_fetch_array($query)) {
	for($c = 0; $c < 8; $c++)
		if($result[$c] != "")
			$fields[$c] = true;
}

$numfields = 0;
for($c = 0; $c < 8; $c++)
	if($fields[$c])
		$numfields++;

if($fields[5]) $numfields++;
if($fields[6]) $numfields++;
$numfields++; // this is for the last Quantity field which is in every report
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
	<td align="right"><a href="" onclick="window.print(); return false;">Print</a> &nbsp;&nbsp;<font color="#0000FF"><span class="text_12">Minimum: </span> 
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
<tr>
	<td colspan="2">
<?php
$query = mysql_query($sql);
checkDBError();
?>
	  <table border="1" cellspacing="0" cellpadding="4" bgcolor="#FFFFFF">
        <tr> 
          <?php if($fields[0]) { ?>
          <td class="orderTH">Part #</td>
          <?php } ?>
          <td class="orderTH">Photo</td>
          <?php if($fields[1]) { ?>
          <td class="orderTH">Description</td>
          <?php } ?>
          <?php if($fields[3]) { ?>
          <td class="orderTH">Size</td>
          <?php } ?>
          <?php if($fields[4]) { ?>
          <td class="orderTH">Color</td>
          <?php } ?>
          <?php if($fields[2]) { ?>
          <td class="orderTH">Price</td>
          <?php } ?>
          <?php if($fields[5]) { ?>
          <td class="orderTH">Set</td>
          <td width="75" class="orderTH">Qty</td>
          <?php } ?>
          <?php if($fields[6]) { ?>
          <td class="orderTH">Matt</td>
          <td width="75" class="orderTH">Qty</td>
          <?php } ?>
          <?php if($fields[7]) { ?>
          <td class="orderTH">Box</td>
          <?php } ?>
          <td width="75" class="orderTH">Qty</td>
          <td width="75" class="orderTH">Total</td>
        </tr>
        <?php
function writeCell($value)
{
	if($value == "") $value = "&nbsp;";
	if(stristr($value, "$") != false) $align = " align=\"right\"";
	echo "<td class=\"text_12$align\">$value</td>";
}

function writeField($name, $f)
{
	global $qtys;
	$qtys++;
?>
	<td class="text_12"><input type="text" name="<?php echo $name.$f; ?>" size="5" maxlength="10" value="0" onchange="recalctotal();"></td>
<?php
}
?>

<form name="frm" action="form-confirm.php" method="post">
<?php
$oldheader = "";
$f = 0;
while($result = mysql_fetch_array($query))
{
	$newheader = $result['header'];
	if($oldheader != $newheader)
	{
?>	
		<tr><td align="center" colspan="<?php echo $numfields + 2?>" class="fat_black_12"><?php echo $newheader ?></td></tr>
<?php		
		$oldheader = $newheader;
	}
	
	$qtys = 0;

	if( $result[7] != "" )
		$price = $result[7];
	else
	{
		$price = $result[2];
		if($price == "") $price = 0;
	}
	
	$price = str_replace("$", "", $price);
	$set = str_replace("$", "", $result['set_']);
	$matt = str_replace("$", "", $result['matt']);
	
	$setfield = false;
	$mattfield = false;
?>
<input type="hidden" name="price<?php echo $f ?>" value="<?php echo $price; ?>">
<input type="hidden" name="set<?php echo $f ?>" value="<?php echo $set; ?>">
<input type="hidden" name="matt<?php echo $f ?>" value="<?php echo $matt; ?>">
	
    <tr> 
    <?php
	if($fields[0]) { writeCell($result[0]); }
	echo "<td class=\"text_12\">";
	if (file_exists($basedir."photos/".$result[9].".jpg")) // print photo cell
		echo "<a href=\"javascript:featureWindow('".$result[9].".jpg');\"><img src=\"photos/t".$result[9].".jpg\" alt=\"photo\" border=\"0\"></a>";
	else
		echo "&nbsp;";
	echo "</td>";
	if($fields[1]) { writeCell($result[1]); }
	if($fields[3]) { writeCell($result[3]); }
	if($fields[4]) { writeCell($result[4]); }
	if($fields[2]) { writeCell(makeThisLookLikeMoney($result[2])); }
	if($fields[5]) { writeCell(makeThisLookLikeMoney($result[5])); writeField("setqty", $f); $setfield = true; }
	if($fields[6]) { writeCell(makeThisLookLikeMoney($result[6])); writeField("mattqty", $f); $mattfield = true; }
	if($fields[7]) { writeCell(makeThisLookLikeMoney($result[7])); }
	
	if(!$setfield) { ?><input type="hidden" name="setqty<?php echo $f; ?>" value="0"><?php }
	if(!$mattfield) { ?><input type="hidden" name="mattqty<?php echo $f; ?>" value="0"><?php } ?>
	
    <td class="text_12"><input type="text" name="qty<?php echo $f; ?>" size="5" maxlength="10" value="0" onchange="recalctotal();"></td>
    <td class="text_12">$<input type="text" name="total<?php echo $f; ?>" size="5" maxlength="10"></td>
	<input type="hidden" name="item<?php echo $f ?>" value="<?php echo $result['ID']; ?>">
    </tr>
    <?php
	$f++;
}
?>
        <tr> 
          <td align="right" colspan="<?php echo $numfields+1; ?>" class="fat_black">INVOICE TOTAL: </td>
          <td class="fat_black">$<input type="text" name="grandtotal" size="5" maxlength="10"></td>
        </tr>
		<input type="hidden" name="num_of_items" value="<?php echo $f; ?>">
		<input type="hidden" name="form" value="<?php echo $ID; ?>">
		<input type="hidden" name="vendorid" value="<?php echo $vendorid; ?>">
		<tr>
			<td colspan="<?php echo $numfields+1; ?>">&nbsp;</td>
			<td align="center"><input type="submit" value="Preview Order"></td>
		</tr>
</form>
      </table>
	
	</td>
</tr>
</table>

<script language="JavaScript1.2">

recalctotal();

function recalctotal()
{
	grandtotal = 0;
	
	for( c = 0; c < Math.floor(frm.elements.length / 8); c++ )
	{
		total = 0;
		if( frm.elements[c*8+0].value != "" )
		{
			if( frm.elements[c*8+5].value == "" ) frm.elements[c*8+5].value = "0";
	
			if( isNaN( frm.elements[c*8+5].value ) ) frm.elements[c*8+5].value = "0";
			if( !isNaN( frm.elements[c*8+0].value ) )
				total += frm.elements[c*8+0].value * frm.elements[c*8+5].value;
		}

		if( frm.elements[c*8+1].value != "" )
		{
			if( frm.elements[c*8+3].value == "" ) frm.elements[c*8+3].value = "0";
	
			if( isNaN( frm.elements[c*8+3].value ) ) frm.elements[c*8+3].value = "0";
			if( !isNaN( frm.elements[c*8+1].value ) )
				total += frm.elements[c*8+1].value * frm.elements[c*8+3].value;
		}

		if( frm.elements[c*8+2].value != "" )
		{
			if( frm.elements[c*8+4].value == "" ) frm.elements[c*8+4].value = "0";
	
			if( isNaN( frm.elements[c*8+4].value ) ) frm.elements[c*8+4].value = "0";
			if( !isNaN( frm.elements[c*8+2].value ) )
				total += frm.elements[c*8+2].value * frm.elements[c*8+4].value;
		}

		frm.elements[c*8+6].value = total;
		grandtotal += total;
	}

	frm.grandtotal.value = grandtotal;
}
</script>
<?php mysql_close($link); ?>
</body>
</html>
