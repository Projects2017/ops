<?php

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');

if (!is_numeric($ID)) die("Invalide Form ID"); // This should help with SQL insertion attacks

$orderas = false;
$modify_avail = true;
$block_blocks = true;
if (isset($_GET['orderas']) && is_numeric($_GET['orderas']) && secure_is_admin()) {
    $orderas = true;
    $block_blocks = false;
    $userid = $_GET['orderas'];
}

require("inc_content.php");

// Does Dealer have Access to this Form
if (!secure_is_admin()&&!vendor_access('D', $userid, $ID)) die("You don't have Access to this form");

if ($MoS_enabled) {
	$sql = "SELECT * FROM MoS_director WHERE form_id = $ID";
	$query = mysql_query($sql);
	if (mysql_num_rows($query) == 1) {
		//-- Change the ID to the one in MoS_director, in case it somehow changed
		$line = mysql_fetch_array($query, MYSQL_ASSOC);
		$ID = $line['MoS_form_id'];
		$table_prefix = "MoS_";
	}
	else {
		$table_prefix = "";
	}
} else {
	$table_prefix = "";
}

$sql = "SELECT a.minimum, a.allowfree, a.name, a.backorder, b.ID FROM " . $table_prefix . "forms AS a LEFT JOIN vendors as b ON b.ID=a.vendor WHERE a.ID=$ID";
$query = mysql_query($sql);
checkDBError($sql);

if ($result = mysql_fetch_Array($query)) {
	$vendorid = $result['ID'];
	$formname = $result['name'];
	$minimum = $result['minimum'];
	$allowfree = checkbox2boolean($result['allowfree']);
	$allowbackorder = checkbox2boolean($result['backorder']);

	$minimum = viewpo_getmin($minimum);
	$raw_min = $minimum['minimum'];
	$min_type = $minimum['type'];
	$formatted = $minimum['formatted'];
	$minimum = $minimum['text'];
}
if (!$backorder_enable)
	$allowbackorder = false;

$sql = "SELECT a.*, b.header FROM " . $table_prefix . "form_items AS a LEFT JOIN " . $table_prefix . "form_headers AS b ON b.ID=a.header WHERE b.form=${ID} ORDER BY b.display_order,a.display_order";
$query = mysql_query($sql);
checkDBError();
$result = db_result2array($query);

// Figure out which columns are not completely blank
$column_names = array("partno", "description", "price", "numinset", "size", "set_", "matt", "box");
$display_column = array();
$alloc_avail = false;
$headers = array();

$hybridcolumns = array(
    "price" => array(
        "markup",
        "cost"
    ),
    "set_" => array(
        "set_markup",
        "set_cost"
    ),
    "matt" => array(
        "matt_markup",
        "matt_cost"
    ),
    "box" => array(
        "box_markup",
        "box_cost"
    )
);

foreach ($column_names as $column) {
	$display_column[$column] = false;
}
foreach ($result as $row_id => $row) {
	foreach ($column_names as $column) {
		if ($row[$column] != "" && !is_null($row[$column]))
			$display_column[$column] = true;
	}
        foreach ($hybridcolumns as $colname => $columnreq) {
            $result[$row_id]['calc_'.$colname] = false;
            foreach($columnreq as $column) {
                if (!($row[$column] != "" && !is_null($row[$column])))
                    continue 2;
            }
            $display_column[$colname] = true;
            $result[$row_id]['calc_'.$colname] = true;
        }
	$alloc = $row['alloc'];
	if ($alloc != "" && $alloc >= 0)
		$alloc_avail = true;
	// Start Loading $headers with set items
	if (!isset($headers[$row['header']])) {
		$headers[$row['header']] = array();
	}
	if ($row['numinset']) {
		$headers[$row['header']][$row['ID']] = $row['numinset'];
	}
}
$numcolumns = 4;
foreach ($column_names as $column) {
	if ($display_column[$column])
		$numcolumns++;
}
if ($alloc_avail)
	$numcolumns += 2;
if ($display_column['matt'])
	$numcolumns++;
if ($display_column['set_'])
	$numcolumns++;
if (!($display_column['price']||$display_column['box']))
	$numcolumns--;
if ($allowbackorder)
	$numcolumns++;

?>
<script language="javascript" src="include/common.js"></script>
<script language="javascript">
<!--
function featureWindow(filename) {
	popUp = window.open('photos/'+filename,'featureWin','width=500,height=400');
}

function do_min_warning(min_type, min, formatted) {
	if (formatted == "true") {
		if (min_type == "D") {
			if (parseFloat(min) > parseFloat(grandTotalElt.innerHTML)) {
				return confirm("You are $" + (parseFloat(min) - parseFloat(grandTotalElt.innerHTML)) + " below the $" + parseFloat(min) + " minimum. Do you wish to continue anyway?");
			}
		}
		else {
			if (parseInt(min) > parseInt(grandTotalPcElt.innerHTML)) {
				return confirm("You are " + (parseInt(min) - parseInt(grandTotalPcElt.innerHTML)) + " pieces below the " + parseInt(min) + " piece minimum. Do you wish to continue anyway?");
			}
		}
	}
}
-->
</script>
<table width="700" border="0">
  <tr valign="top">
    <td height="30" class="fat_black">
      <?php echo $formname; ?>
	</td>
	<td align="right"><?php if (!$MoS_enabled) { ?><!--<a href="" onclick="window.print(); return false;">Print</a>--><a href="print-view.php?ID=<?php=$ID ?>" target="_new">Printer Friendly View</a> &nbsp;&nbsp;<?php } ?><font color="#0000FF"><span class="text_12">Minimum: </span>
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
<tr><td colspan="2" class="fat_black_12">Click on the thumbnail images to view product photos full-size.<br>May take a minute to load.</td></tr>
</table><?php
if ($MoS_enabled) {
	?><form id="frm" action="MoS_form-confirm.php<?php if ($orderas): ?>?orderas=<?php= $userid ?><?php endif; ?>" method="post"><?php
} else { // Don't do min warning on Market
	?><form id="frm" action="form-confirm.php<?php if ($orderas): ?>?orderas=<?php= $userid ?><?php endif; ?>" method="post" onsubmit='return do_min_warning(<?php echo "\"" . $min_type . "\", \"" . $raw_min . "\", \"" . $formatted . "\""; ?>)'><?php
}
?>
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
			if ($display_column['numinset'])
				print "<td># in Set</td>";
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
		<td width="75">Stock</td>
		<?php if ($alloc_avail) { print "<td>Alloc</td><td>Avail</td>"; } ?>
		<?php if ($display_column['price']||$display_column['box']) { ?>
			<td width="75">Qty</td>
		<?php } ?>
		<td width="75">Total</td>
		<!--<td>Alloc</td>
		<td>Avail</td>-->
<?php
		if ($allowbackorder)
			print "\t\t<td>Back Order</td>\n";
?>
	</tr>
<?php
function writeCell($value)
{
	if(!$value|| $value == '(-$0.00)') $value = "&nbsp;";
	if(stristr($value, "$") != false) $align = " align=\"right\"";
	echo "\t\t\t<td class=\"text_12$align\">$value</td>\n";
}

function writeField($name, $f, $value = '')
{
	global $qtys;
	$qtys++;
	print "\t\t\t<td class=\"text_12\"><input type=\"text\" id=\"${name}${f}\" name=\"${name}${f}\" size=\"5\" maxlength=\"10\" onchange=\"updateTotal(${f});\" ";
	if ($value)
		echo "value=\"".$value."\"";
	else
		echo "value=\"0\"";
	echo "/></td>\n";
}

function writeHidden($name, $f) {
	print "\t\t\t<td class=\"text_12\"><input type=\"hidden\" name=\"${name}${f}\" size=\"5\" maxlength=\"10\" value=\"0\" onchange=\"updateTotal(${f});\" />&nbsp;</td>\n";
}

$unit_cuft_array = array();
$unit_seat_array = array();
$unit_price_array = array();
$unit_discount_array = array();
$unit_freight_array = array();
$set_price_array = array();
$mat_price_array = array();
$set_qty_array = array();
$item_id_array = array();

$oldheader = "";
$f = 0;
foreach($result as $row) {
	$newheader = $row['header'];
	if($oldheader != $newheader) {
		print "\t\t<tr><td align=\"center\" colspan=\"${numcolumns}\" class=\"fat_black_12\">$newheader";
		if ($headers[$newheader]) {
			print " (";
			$headerjs = 'var ele;';
			foreach ($headers[$newheader] as $i => $u ) {
				$headerjs .= "ele = document.getElementById('qty' + unitId[".$i."]);if (ele.type != 'text') { alert('Part or all of this set is not orderable at this time.'); return false; }";
			}
			foreach ($headers[$newheader] as $i => $u ) {
				$headerjs .= 'ele = document.getElementById(\'qty\' + unitId['.$i.']);if (ele.type == \'text\') { ele.value = parseInt(ele.value) + parseInt('.$u.'); }'; // document.getElementById
			}
			$headerjs .= 'updateTotal(-1);return false;';
			print "<a href=\"\" onclick=\"".$headerjs."\">Order Set</a>) (";
			$headerjs = 'var ele;';
			foreach ($headers[$newheader] as $i => $u ) {
				$headerjs .= 'ele = document.getElementById(\'qty\' + unitId['.$i.']);if (ele.type == \'text\') { ele.value = parseInt(ele.value) - parseInt('.$u.'); if (parseInt(ele.value) < 0) ele.value = 0; }';
			}
			$headerjs .= 'updateTotal(-1);return false;';
			print "<a href=\"\" onclick=\"".$headerjs."\">Remove Set</a>)";
		}
		print "</td><tr>\n";
		$oldheader = $newheader;
	}

	$qtys = 0;
        // print_r($row);
        
	if (($row['price'] == "" || is_null($row['price']))
                && (
                        ($row['markup'] == "" || is_null($row['markup']))
                        || ($row['cost'] == "" || is_null($row['cost']))
                )) {
                $row['price'] = $row['box'];
                $row['cost'] = $row['box_cost'];
                $row['markup'] = $row['box_markup'];
	}
        if (is_null($row['price']))
            $row['price'] = "";
        //print_r($row);
        //die();
	$discount = loadDiscount('discount',array("item_id" => $row['ID']),$table_prefix."form_item");
        $freight = loadDiscount('freight',array("item_id" => $row['ID']),$table_prefix."form_item");
	//$price = str_replace("$", "", $price);
        $price = calcPrice('box', $row, $userid, $ID, $table_prefix);
	// $set = str_replace("$", "", $row['set_']);
        $set = calcPrice('set', $row, $userid, $ID, $table_prefix);
	$setqty = $row['setqty'];
	// $matt = str_replace("$", "", $row['matt']);
        $matt = calcPrice('matt', $row, $userid, $ID, $table_prefix);
	$vol = $row['cubic_ft'];
        $seat = $row['seats'];
        $weight = $row['weight'];

	$unit_price_array[] = (float)$price;
	$unit_discount_array[] = $discount;
        $unit_freight_array[] = $freight;
	$set_price_array[] = (float)$set;
	$set_qty_array[] = (int)$setqty;
	$mat_price_array[] = (float)$matt;
	$unit_cuft_array[] = (float)$vol;
        $unit_seat_array[] = (int)$seat;
        $unit_weight_arrray[] = (float)$weight;
	$item_id_array[] = (int)$row['ID'];

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
	if ($display_column['numinset'])
		writeCell($row['numinset']);
	// Volume
	//writeCell("42");
	// Price
	if ($display_column['price'])
		writeCell(makeThisLookLikeMoney($price));
	// Set
	if ($display_column['set_']) {
		writeCell(makeThisLookLikeMoney($set));
		if ($set) {
			writeField("setqty", $f, $_REQUEST['setqty'.$f]);
		} else
			writeHidden("setqty", $f);
		$setfield = true;
	} else
		print "\t\t\t<input type=\"hidden\" name=\"setqty${f}\" value=\"0\">\n";
	// Matt
	if ($display_column['matt']) {
		writeCell(makeThisLookLikeMoney($matt));
		if ($matt)
			writeField("mattqty", $f, $_REQUEST['mattqty'.$f]);
		else
			writeHidden("mattqty", $f);
		$mattfield = true;
	} else
		print "\t\t\t<input type=\"hidden\" name=\"mattqty${f}\" value=\"0\">\n";
	// Box
	if ($display_column['box'])
		writeCell(makeThisLookLikeMoney($price));

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
		$total_value = "0.00";
	} else {
		$input_type = "text";
		$qty_value = "";
		$total_value = "";
	}
	if ($alloc_avail) {
		$alloc = $row['alloc'];
		if ($alloc == "" || $alloc < 0) {
			$avail = '&nbsp;';
			$alloc = '&nbsp;';
		} else {
			$avail = $row['avail'];
		}
		print "\t\t<td>${alloc}</td>\n";
		print "\t\t<td>${avail}</td>\n";
	}
	// Qty
	if (!$allowfree&&!$price) {
		$input_type = 'hidden';
		$qty_value = '&nbsp;';
	}
	if (!($display_column['price']||$display_column['box'])) {
		$input_type="hidden";
		$qty_value = '&nbsp;';
		//print "\t\t\t<td class=\"text_12\" style=\"display=none\">";
		print "<input type=\"${input_type}\" id=\"qty${f}\" name=\"qty${f}\" size=\"5\" maxlength=\"10\" onchange=\"updateTotal(${f});\" ";
		if ($input_type != 'hidden'&&isset($_REQUEST['qty'.$f])) {
			echo "value=\"".$_REQUEST['qty'.$f]."\" ";
		} else {
			echo "value=\"0\" ";
		}
		echo "/>";
	} else {
		print "\t\t\t<td class=\"text_12\">";
		print "<input type=\"${input_type}\" id=\"qty${f}\" name=\"qty${f}\" size=\"5\" maxlength=\"10\" onchange=\"updateTotal(${f});\" ";
		if ($input_type != 'hidden'&&isset($_REQUEST['qty'.$f])) {
			echo "value=\"".$_REQUEST['qty'.$f]."\" ";
		} else {
			echo "value=\"0\" ";
		}
		echo "/>";
		print $qty_value;
		print "</td>\n";
	}
	// Total
	print "\t\t\t<td class=\"text_12\" align=\"right\">";
	print "<span id=\"dtotal${f}\">$0.00</span>";
	print "</td>\n";
	if ($allowbackorder) {
		//print "\t\t\t<td class=\"text_12\">\n";
		//print "\t\t\t\t";
		if ($stock['block_order'] == 'Y') {
			writeField("backorderqty", $f, $_REQUEST['backorder'.$f]);
		} else {
			writeHidden("backorderqty", $f);
		}
		//print "\n";
		//print "\t\t\t</td>\n";
	}

	print "\t\t\t<input type=\"hidden\" name=\"item${f}\" value=\"${item_id}\" />\n";
	print "\t\t<input type=\"hidden\" name=\"vol${f}\" value=\"$vol\" />\n";
	print "\t\t<input type=\"hidden\" name=\"price${f}\" value=\"$price\" />\n";
	print "\t\t<input type=\"hidden\" name=\"set${f}\" value=\"$set\" />\n";
	print "\t\t<input type=\"hidden\" name=\"matt${f}\" value=\"$matt\" />\n";
	print "\t\t</tr>\n";
	$f++;
}
$discount = getDiscount('discount',$userid,$ID,$table_prefix);
$freight = getDiscount('freight',$userid,$ID,$table_prefix);
?>
		<tr>
			<td class="fat_black_12" colspan="3" style="vertical-align: top;">
				Volume: <span id="dgrandtotalvol">0</span> cft.<br>
                                Seats: <span id="dgrandtotalseat">0</span><br>
				Pieces: <span id="dgrandtotalpc">0</span><br>
                                Weight: <span id="dgrandtotalweight">0</span> lbs.
			</td>
			<td colspan="<?php echo $numcolumns-($allowbackorder?5:4); ?>" class="fat_black_12" align="right">
				Product Total:<br>
				Discount:<br>
				<span id="ddiscountset" style="display:none;">Item Discounts:<br /></span>
				Subtotal:<br>
				Freight:<br>
                                <span id="dfreightset" style="display:none;">Item Freight:<br /></span>
				<span class="fat_black_14">Grand Total:</span>
			</td>
			<td class="fat_black_12" align="right">
				<span id="dproducttotal">$0.00</span><br>
				<span id="ddiscount">$0.00</span><br>
				<span id="ddiscountset2" style="display:none;"><span id="ditemdiscounts">$0.00</span><br></span>
				<span id="dsubtotal">$0.00</span><br>
				<span id="dfreight">$0.00</span><br>
                                <span id="dfreightset2" style="display:none;"><span id="ditemfreight">$0.00</span><br></span>
				<span id="dgrandtotal" class="fat_black_14">$0.00</span>
			</td>
		</tr>
		<tr>
			<td colspan="<?php echo $numcolumns-2; ?>" class="text_12" align="right">&nbsp;<span id="errortext" style="color: rgb(255,0,0);">Test</span></td>
			<td align="center" colspan="2"><input id="submitbutton" type="submit" value="Preview Order"></td>
		</tr>
		<!--
        <tr>
        	<td align="right" colspan="<?php echo $numcolumns-($allowbackorder?2:1); ?>" class="fat_black">Subtotal: </td>
			<td class="fat_black">$<span id="dsubtotal">0</span></td>
			<?php if ($allowbackorder) { ?><td class="fat_black">&nbsp;</td><?php } ?>
        </tr>
        <tr>
        	<td align="right" colspan="<?php echo $numcolumns-($allowbackorder?2:1); ?>" class="fat_black">Grand Total: </td>
			<td class="fat_black">$<span id="dgrandtotal">0</span></td>
			<?php if ($allowbackorder) { ?><td class="fat_black">&nbsp;</td><?php } ?>
        </tr>
		<tr>
			<td colspan="<?php echo $numcolumns-2; ?>" class="text_12">Volume:&nbsp;<b><span id="dgrandtotalvol">0</span>&nbsp;cft.</b>&nbsp;Pieces:&nbsp;<b><span id="dgrandtotalpc">0</span></b></td>
			<td align="center" colspan="2"><input type="submit" value="Preview Order"></td>
		</tr>
		-->
	</table>
	<input type="hidden" name="num_of_items" value="<?php echo $f; ?>">
	<input type="hidden" name="form" value="<?php echo $ID; ?>">
	<input type="hidden" name="vendorid" value="<?php echo $vendorid; ?>">
</form>

<script language="JavaScript1.2">

<?php
print js_array("unitVol", $unit_cuft_array);
print js_array("unitSeat", $unit_seat_array);
print js_array("unitWeight", $unit_weight_arrray);
print js_array("unitPrice", $unit_price_array);
print js_array("unitDiscount",$unit_discount_array);
print js_array("unitFreight",$unit_freight_array);
print js_array("setPrice", $set_price_array);
print js_array("setQty", $set_qty_array);
print js_array("matPrice", $mat_price_array);
print js_array("unitId", array_flip($item_id_array));
print "var itemCount = ".$f.";\n";
?>
var totalPrices = new Array(itemCount);
var totalDiscounts = new Array(itemCount);
var totalFreights = new Array(itemCount);
var totalItemDiscounts = new Array(itemCount);
var totalItemFreights = new Array(itemCount);
var totalVolumes = new Array(itemCount);
var totalSeats = new Array(itemCount);
var totalWeights = new Array(itemCount);
var totalPieces = new Array(itemCount);
var negQty = new Array(itemCount);
var priceElements = new Array(itemCount);
var productTotalElt = document.getElementById("dproducttotal");
var discountElt = document.getElementById("ddiscount");
var itemDiscountsElt = document.getElementById("ditemdiscounts");
var itemFreightElt = document.getElementById("ditemfreight");
var discountSetElt = document.getElementById("ddiscountset");
var discountSet2Elt = document.getElementById("ddiscountset2");
var freightSetElt = document.getElementById("dfreightset");
var freightSet2Elt = document.getElementById("dfreightset2");
var frieghtElt = document.getElementById("dfreight");
var subTotalElt = document.getElementById("dsubtotal");
var grandTotalElt = document.getElementById("dgrandtotal");
var grandTotalVolElt = document.getElementById("dgrandtotalvol");
var grandTotalSeatElt = document.getElementById("dgrandtotalseat");
var grandTotalWeightElt = document.getElementById("dgrandtotalweight");
var grandTotalPcElt = document.getElementById("dgrandtotalpc");
var form = document.getElementById("frm");
for (var item = 0; item < itemCount; item++) {
	priceElements[item] = document.getElementById("dtotal" + item);
}
var submitButton = document.getElementById("submitbutton");
var errorText = document.getElementById("errortext");

// Multiplies float to float and rounds off to 2 digits
function round2(num) {
	num=Math.round(num*100)/100;
	return num;
}

function updateItem(item, field) {
	var total = 0;
	var totalvol = 0;
        var totalseat = 0;
        var totalweight = 0;
	var totalpc = 0;
	var discount = 0;
        var freight = 0;
	var item_discount = 0;
        var item_freight = 0;
	var off = item*<?php echo $allowbackorder?9:8; ?>;
	negQty[item] = false;

        // Calculate Quantity
        var unit_qty = form.elements[off+2].value;
	if (unit_qty == "" || isNaN(unit_qty)) {
		form.elements[off+2].value = "0";
                unit_qty = 0;
	} else {
		if (unit_qty < 0)
			negQty[item] = true;
		total = unit_qty * unitPrice[item];
		totalvol = unit_qty *unitVol[item];
                totalseat = unit_qty *unitSeat[item];
                totalweight = unit_qty *unitWeight[item];
	}
        total = unit_qty * unitPrice[item];

	var set_qty = form.elements[off].value;
	if (set_qty == "" || isNaN(set_qty)) {
		form.elements[off].value = "0";
                set_qty = 0;
	} else {
		if (set_qty < 0)
			negQty[item] = true;
		totalpc += parseInt(set_qty) * setQty[item];
	}
        total += set_qty * setPrice[item];

	var mat_qty = form.elements[off+1].value;
	if (mat_qty == "" || isNaN(mat_qty)) {
		form.elements[off+1].value = "0";
                mat_qty = 0;
	} else {
		if (mat_qty < 0)
			negQty[item] = true;
		totalpc += parseInt(mat_qty);
	}
        total +=  mat_qty * matPrice[item];

        // Calculate Discount/Freight
        // -- Unit
	if (calcItemDiscount(unitPrice[item],totalpc,unitDiscount[item]) == unitPrice[item]) {
		discount = unit_qty * unitPrice[item];
	} else {
		item_discount = (unitPrice[item] - calcItemDiscount(unitPrice[item],totalpc,unitDiscount[item]))*unit_qty;
	}
	if (calcItemDiscount(unitPrice[item],totalpc,unitFreight[item]) == unitPrice[item]) {
		freight = unit_qty * unitPrice[item];
	} else {
		item_freight = (unitPrice[item] - calcItemDiscount(unitPrice[item],totalpc,unitFreight[item]))*unit_qty;
	}
	// -- Set
        if (calcItemDiscount(setPrice[item],totalpc,unitDiscount[item]) == setPrice[item]) {
                discount += set_qty * setPrice[item];
        } else {
                item_discount += (setPrice[item] - calcItemDiscount(setPrice[item],totalpc,unitDiscount[item]))*set_qty;
        }
        if (calcItemDiscount(setPrice[item],totalpc,unitFreight[item]) == setPrice[item]) {
                freight += set_qty * setPrice[item];
        } else {
                item_freight += (setPrice[item] - calcItemDiscount(setPrice[item],totalpc,unitFreight[item]))*set_qty;
        }
        // -- Matt
        if (calcItemDiscount(matPrice[item],totalpc,unitDiscount[item]) == matPrice[item]) {
                discount += mat_qty * matPrice[item];
        } else {
                item_discount += (matPrice[item] - calcItemDiscount(matPrice[item],totalpc,unitDiscount[item]))*mat_qty;
        }
        if (calcItemDiscount(matPrice[item],totalpc,unitFreight[item]) == matPrice[item]) {
                freight += mat_qty * matPrice[item];
        } else {
                item_freight += (matPrice[item] - calcItemDiscount(matPrice[item],totalpc,unitFreight[item]))*mat_qty;
        }
	priceElements[item].innerHTML = formatCurrency(round2(total));
	totalItemDiscounts[item] = round2(item_discount);
        totalItemFreights[item] = round2(item_freight);
	totalDiscounts[item] = round2(discount);
        totalFreights[item] = round2(freight);
	totalPrices[item] = round2(total);
	totalVolumes[item] = round2(totalvol);
        totalSeats[item] = Math.round(totalseat);
        totalWeights[item] = round2(totalweight);
	totalPieces[item] = totalpc;
}

function updateTotal(item) {
	clearError();
	var producttotal = 0;
	var grandtotal = 0;
	var subtotal = 0;
	var grandtotalvol = 0;
        var grandtotalseat = 0;
        var grandtotalweight = 0;
	var grandtotalpc = 0;
	var discount = 0;
        var freight = 0;
	var discounttotal = 0;
        var freighttotal = 0;
	var itemdiscount = 0;
        var itemfreight = 0;
	var negativeproduct = false;

	if (item >= 0) {
		updateItem(item);
		for (var i = 0; i < itemCount; i++) {
			if (negQty[i])
				negativeproduct = true;
			producttotal += totalPrices[i];
			discounttotal += totalDiscounts[i];
                        freighttotal += totalFreights[i];
			itemdiscount += totalItemDiscounts[i];
                        itemfreight += totalItemFreights[i];
			grandtotalvol += totalVolumes[i];
                        grandtotalseat += totalSeats[i];
                        grandtotalweight += totalWeights[i];
			grandtotalpc += totalPieces[i];
		}
	} else {
		for (var i = 0; i < itemCount; i++) {
			updateItem(i);
			if (negQty[i])
				negativeproduct = true;
			producttotal += totalPrices[i];
			discounttotal += totalDiscounts[i];
                        freighttotal += totalFreights[i];
			itemdiscount += totalItemDiscounts[i];
                        itemfreight += totalItemFreights[i];
			grandtotalvol += totalVolumes[i];
                        grandtotalseat += totalSeats[i];
                        grandtotalweight += totalWeights[i];
			grandtotalpc += totalPieces[i];
		}
	}
	discount = calcDiscount(discounttotal, grandtotalpc, "<?php= $discount ?>"); //  discounttotal * <?php /* echo ($discount * .01) */ ?>;
	discount = discount * -1;
        freight = calcDiscount(freighttotal, grandtotalpc, "<?php= $freight ?>"); // freight = producttotal * <?php /* echo ($freight * .01) */ ?>;
        itemdiscount = itemdiscount * -1;
	subtotal = producttotal + discount;
	subtotal = subtotal + itemdiscount;
	grandtotal = subtotal + freight;
        grandtotal = grandtotal + itemfreight;
	productTotalElt.innerHTML = formatCurrency(round2(producttotal));
	discountElt.innerHTML = formatCurrency(round2(discount));
	itemDiscountsElt.innerHTML = formatCurrency(round2(itemdiscount));
        itemFreightElt.innerHTML = formatCurrency(round2(itemfreight));
	if (itemdiscount == 0) {
		discountSetElt.style.display = "none";
		discountSet2Elt.style.display = "none";
	} else {
		discountSetElt.style.display = "";
		discountSet2Elt.style.display = "";
	}
        if (itemfreight == 0) {
                freightSetElt.style.display = "none";
                freightSet2Elt.style.display = "none";
        } else {
                freightSetElt.style.display = "";
                freightSet2Elt.style.display = "";
        }
	subTotalElt.innerHTML = formatCurrency(round2(subtotal));
	frieghtElt.innerHTML = formatCurrency(round2(freight));
	grandTotalElt.innerHTML = formatCurrency(round2(grandtotal));
	grandTotalVolElt.innerHTML = round2(grandtotalvol);
        grandTotalSeatElt.innerHTML = Math.round(grandtotalseat);
        grandTotalWeightElt.innerHTML = round2(grandtotalweight)
	grandTotalPcElt.innerHTML = grandtotalpc;
	if (negativeproduct)
		setError("Cannot order negative amounts of merchendise.");
<?php
if (!$MoS_enabled) {
?>	if (grandtotal > <?php= creditAvail($userid) ?>)
		setError("This order will cause you to exceed your Order To Limit. Please make payment immediately. <br /> If you have questions please contact Amy Bowen (614-203-6126). Thank You");
<?php
}
?>
}

function setError(text) {
	errorText.innerHTML = text;
        <?php if ($block_blocks): ?>
	submitButton.disabled = true;
        <?php endif; ?>
}

function clearError() {
	errorText.innerHTML = "";
        <?php if ($block_blocks): ?>
	submitButton.disabled = false;
        <?php endif; ?>
}
updateTotal(-1);
</script>
<?php mysql_close($link); ?>
</body>
</html>
