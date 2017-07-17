<?php
require("database.php");
require("vendorsecure.php");

$data = array();

$sql = "select forms.vendor from forms where ID=".$ID;
$query2 = mysql_query($sql);
checkDBError($sql);
$temp = mysql_fetch_array($query2);

$sql = "SELECT vlogin_access.*, vendors.name FROM vlogin_access
 LEFT JOIN vendors ON vendors.ID=vlogin_access.vendor WHERE vlogin_access.user=$vendorid AND vendors.ID = ".$temp['vendor'];
$query = mysql_query($sql);
checkDBError($sql);
if (mysql_num_rows($query) == 0)
	die("You don't have access to this vendor");

if ($_POST['update']) {
	foreach($_REQUEST as $key => $value) {
	   $reg = array();
	   if(ereg("^stock([0-9]+)",$key, $reg))
		  $data[$reg[1]]['stock'] = $value;
	   $reg = array();
	   if(ereg("^oldstock([0-9]+)",$key, $reg))
		  $data[$reg[1]]['oldstock'] = $value;
	   $reg = array();
	   if(ereg("^stock_day([0-9]+)",$key, $reg))
		  $data[$reg[1]]['stock_day'] = $value;
	   $reg = array();
	   if(ereg("^oldstock_day([0-9]+)",$key, $reg))
		  $data[$reg[1]]['oldstock_day'] = $value;
	   if(ereg("^oldavail([0-9]+)",$key, $reg))
		  $data[$reg[1]]['oldavail'] = $value;
	   if(ereg("^oldenable_avail([0-9]+)",$key, $reg))
		  $data[$reg[1]]['oldenable_avail'] = $value;
	   $reg = array();
	   if(ereg("^item([0-9]+)",$key, $reg))
		  $data[$reg[1]]['item'] = $value;
	   if(ereg("^headerid([0-9]+)",$key, $reg))
		  $data[$reg[1]]['header'] = $value;
	   if(ereg("^avail([0-9]+)",$key, $reg)) {
	   	  if (!$value) $value = "0";
		  $data[$reg[1]]['avail'] = $value;
		  //echo "(Avail".$reg[1]."=".$value."), ";
	   }
	   if(ereg("^enable_avail([0-9]+)",$key, $reg)) {
		  $data[$reg[1]]['enable_avail'] = $value;
		  //echo "(Enable".$reg[1]."=".$value."), ";
	   }
    }
    // print_r($data);
    
    foreach($_REQUEST as $key => $value) {
       if(ereg("^stock_head_([0-9]+)",$key, $reg)) {
       	  if ($value > 0) {
             foreach ($data as $num => $item) {
          	    if ($item['header'] == $reg[1]) {
          	       $data[$num]['stock'] = $value;
          	    }
             }
       	  }
       }
    }
    foreach ($data as $item) {
    	if ($item['enable_avail']) {
    		//echo 'hit '.$item['item'].',',$item['avail'];
    		if ($item['oldavail'] != $item['avail']) {
    			$sql = "update form_items set alloc = '".mysql_escape_string($item['avail'])."', avail = '".mysql_escape_string($item['avail'])."' where ID = '".mysql_escape_string($item['item'])."'";
    			$query = mysql_query($sql);
	        	checkDBError();
    		}
    		if ($item['avail'] > 0)
	        	$item['stock'] = 1;
	        else
	        	$item['stock'] = 2;
	        $item['stock_day'] = 0;
    	} else {
    		$sql = "update form_items set alloc = -1, avail = -1 where ID = '".mysql_escape_string($item['item'])."'";
    		$query = mysql_query($sql);
    		checkDBError();
    	}
    	if ($item['oldstock'] != $item['stock']) {
    		$sql = "update form_items set stock = '".mysql_escape_string($item['stock'])."' where ID = '".mysql_escape_string($item['item'])."'";
    		$query = mysql_query($sql);
	        checkDBError();
    	}
       	if ($item['oldstock_day'] != $item['stock_day']) {
    		$sql = "update form_items set stock_day = '".mysql_escape_string($item['stock_day'])."' where ID = '".mysql_escape_string($item['item'])."'";
    		$query = mysql_query($sql);
	        checkDBError();
    	}
    }
}

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

$sql = "select vendors.name, vendors.ID, vendors.fax from forms left join vendors on vendors.ID=forms.vendor where forms.ID=$ID";
$query = mysql_query($sql);
checkDBError();

if ($result = mysql_fetch_Array($query)) {
	$vendorsid = $result['ID'];
	$vendorname = $result['name'];
	//$minimum = $result['minimum'];
	$fax = $result['fax'];
}

$sql = "select form_items.partno, form_items.description, form_items.size, form_items.color, form_items.stock,form_headers.header, form_headers.ID as headerid, form_items.ID, form_items.stock_day, form_items.avail, form_items.avail, form_items.alloc
 from form_items left join form_headers on form_headers.ID=form_items.header 
 where form_headers.form=$ID order by form_headers.display_order, form_items.display_order";
$query = mysql_query($sql);
checkDBError();

//figure out what fields we need
$fields = array();
for( $c = 0; $c < 5; $c++ )
	$fields[$c] = false;

while($result = mysql_fetch_array($query)) {
	for($c = 0; $c < 5; $c++)
		if($result[$c] != "")
			$fields[$c] = true;
}

$numfields = 0;
for($c = 0; $c < 5; $c++)
	if($fields[$c])
		$numfields++;
// Add another since for availation
$numfields++;
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
<?php require('menu.php'); ?>
<table width="700" border="0">
  <tr valign="top"> 
    <td height="30" class="fat_black"> 
      <?php echo $vendorname; ?>
	</td>
	<td align="right"><a href="" onclick="window.print(); return false;">Print</a></td>
</tr>

<?php
if(file_exists($basedir."logos/".$vendorsid.".jpg"))
{
?>
<tr><td colspan="2"><img src="logos/<?php echo $vendorsid ?>.jpg"></td></tr>
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
          <?php if($fields[2]) { ?>
          <td class="orderTH">Size</td>
          <?php } ?>
          <?php if($fields[3]) { ?>
          <td class="orderTH">Color</td>
          <?php } ?>
		  <td class="orderTH">Allocated</td>
          <td class="orderTH">Available</td>
          <td width="75" class="orderTH">Stock</td>
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

<form name="frm" id="frm" method="post">
<?php
$oldheader = "";
$f = 0;
$offset = 0;
$itemoffset = array();
while($result = mysql_fetch_array($query))
{
	$newheader = $result['header'];
	if($oldheader != $newheader)
	{
?>	
		<tr><td align="center" colspan="<?php echo $numfields+1?>" class="fat_black_12"><?php echo $newheader ?></td>
		<td><select name="stock_head_<?php echo $result['headerid']; ?>">
		<option value="-1"></option>
	    <?php 
	       $stock_types = stock_status(0);
		   foreach ($stock_types as $stock_type) {
			   echo "        "; // Indent
			   echo "<OPTION VALUE=\"".$stock_type['id']."\" STYLE=\"".$stock_type['style']."\"";
			   echo ">Overall ".$stock_type['name']."</OPTION>";
		   }
		   $offset++;
		?>
		</select></td>
		</tr>
<?php		
		$oldheader = $newheader;
	}
	
	$qtys = 0;
	
	$price = str_replace("$", "", $price);
	$set = str_replace("$", "", $result['set_']);
	$matt = str_replace("$", "", $result['matt']);
	
	$setfield = false;
	$mattfield = false;
?>
	
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
	if($fields[2]) { writeCell($result[2]); }
	if($fields[3]) { writeCell($result[3]); }
	
	if (($result['avail'] == "") || ($result['avail'] < 0)) {
		$enable_avail = false;
		$result['avail'] = '';
	} else {
		$enable_avail = true;
	}
	?>
	<td class="text_12">
	<?php if ($result['alloc'] == -1) {
	      echo "&nbsp;";
	   } else {
	      echo $result['alloc']; 
	   } ?>
	</td>
	<td class="text_12">
	<input id="enable_avail<?php echo $f ?>" onPropertyChange="updateEnableavail(<?php echo $offset ?>);" onchange="updateEnableavail(<?php echo $offset ?>);" type="checkbox" name="enable_avail<?php echo $f ?>" <?php if ($enable_avail) { echo "checked"; } ?> />
	<?php $itemoffset[] = $offset;
	   $offset++; 
	?>
	<input id="avail<?php echo $f ?>" type="text" name="avail<?php echo $f ?>" value="<?php echo $result['avail']; ?>" size="3">
	<?php $offset++; ?>
	</td>
    <td class="text_12">
    	    <select id="stock<?php echo $f ?>" name="stock<?php echo $f ?>">
	    <?php $stock_types = stock_status(0);
		   foreach ($stock_types as $stock_type) {
			   echo "        "; // Indent
			   echo "<OPTION VALUE=\"".$stock_type['id']."\" STYLE=\"".$stock_type['style']."\"";
			   if ($stock_type['id'] == $result["stock"]) {
				   echo " SELECTED";
			   }
			   echo ">".$stock_type['name']."</OPTION>";
		   }
		   $offset++;
		?>
		</select>
		<select id="stock_day<?php echo $f ?>" name="stock_day<?php echo $f ?>">
	    <OPTION VALUE="0">-</OPTION>
	    <?php 
		   for ($i = 1; $i <= 31; ++$i) {
			   echo "<OPTION VALUE=\"".$i."\"";
			   if ($i == $result['stock_day']) {
				   echo " SELECTED";
			   }
			   echo ">".$i."</OPTION>";
		   }
		   $offset++;
		?>
		</select>
    </td>
    <input type="hidden" name="oldstock<?php echo $f ?>" value="<?php echo $result['stock']; $offset++; ?>">
    <input type="hidden" name="oldstock_day<?php echo $f ?>" value="<?php echo $result['stock_day']; $offset++; ?>">
    <input type="hidden" name="oldavail<?php echo $f ?>" value="<?php echo $result['avail']; $offset++; ?>">
    <input type="hidden" name="oldenable_avail<?php echo $f ?>" value="<?php echo $result['avail']; $offset++; ?>">
	<input type="hidden" name="item<?php echo $f ?>" value="<?php echo $result['ID']; $offset++; ?>">
	<input type="hidden" name="headerid<?php echo $f ?>" value="<?php echo $result['headerid']; $offset++; ?>">
    </tr>
    <?php
	$f++;
}
?>
		<input type="hidden" name="form" value="<?php echo $ID; ?>">		
		<input type="hidden" name="vendorid" value="<?php echo $vendorsid; ?>">
		<input type="hidden" name="update" value="1">
		<tr>
			<td colspan="<?php echo $numfields + 1; ?>">&nbsp;</td>
			<td align="center"><input type="submit" value="Change Stock Status"></td>
		</tr>
</form>
      </table>
	
	</td>
</tr>
</table>
<script language="javascript">
var form = document.getElementById("frm");

function updateEnableavail(offset) {
	var f_enable = form.elements[offset];
	var f_stock = form.elements[offset+2];
	var f_stock_day = form.elements[offset+3];
	var f_avail = form.elements[offset+1];
	f_avail.disabled = !f_enable.checked;
	if (!f_enable.checked)
		f_avail.value = "";
	f_stock.disabled = f_enable.checked;
	f_stock_day.disabled = f_enable.checked;
	if (f_enable.checked)
		f_stock_day.value = 0;
}

<?php foreach ($itemoffset as $item) {
	echo "updateEnableavail(".$item.");\n";
} ?>
</script>
<?php mysql_close($link); ?>
</body>
</html>
