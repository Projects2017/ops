<?php
if(!class_exists('ItemField'))
{
	class ItemField
	{
		// Field display information
		// id = field name
		// shown = whether the field is shown in the view
		// fieldSize = display size of the text field
		// fieldMax = maximum length of the field data
		// nullDisable = whether the field entry is disabled if the value of the field is null
		// fieldPre = extra string to display before field (aka '$')
		// fieldPost = extra string to display after field (aka ' %')
		public $type, $id, $shown, $fieldSize, $fieldMax, $nullDisable;
		public function __construct($id, $show, $nullDisable, $fieldSize, $fieldMax, $fieldPre = '', $fieldPost = '')
		{
            $this->type = 'text';
			$this->id = $id;
			$this->shown = $show;
			$this->nullDisable = $nullDisable;
			$this->fieldSize = $fieldSize;
			$this->fieldMax = $fieldMax;
			$this->fieldPre = $fieldPre;
			$this->fieldPost = $fieldPost;
		}
	}
    class ItemCheckbox extends ItemField
    {
        public $id, $shown, $fieldSize, $fieldMax, $nullDisable;
		public function __construct($id, $show, $fieldPre = '', $fieldPost = '')
		{
            parent::__construct($id, $show, false, 0, 0, $fieldPre, $fieldPost);
            $this->type = 'checkbox';
		}
    }
}

#if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
#	die ('<h2>Direct Execution Prohibited</h2>');
if ($MoS_enabled) {
	$prefix = 'MoS_';
} else {
	$prefix = '';
}
if (!function_exists('showfieldcheck')) {
	function showfieldcheck($name, $default) {
		global $showfields;
		$val = getPref("me-".$name);
		if (is_null($val)) {
			$showfields[$name] = $default;
		} else {
			$showfields[$name] = $val;
		}
	}
}

$showfields = array();
showfieldcheck('display_order', true);
showfieldcheck('partno', true);
if (!$MoS_enabled) {
	showfieldcheck('sku', true);
	showfieldcheck('description', true);
} else {
	showfieldcheck('sku', false);
	showfieldcheck('description', false);
}
showfieldcheck('price', false);
showfieldcheck('cost', true);
showfieldcheck('markup', true);
showfieldcheck('size', true);
showfieldcheck('numinset', true);
showfieldcheck('set_', false);
showfieldcheck('set_cost', true);
showfieldcheck('set_markup', true);
showfieldcheck('matt', false);
showfieldcheck('matt_cost', true);
showfieldcheck('matt_markup', true);
showfieldcheck('box', false);
showfieldcheck('box_cost', true);
showfieldcheck('box_markup', true);
showfieldcheck('stock', true);
showfieldcheck('cubic_ft', true);
showfieldcheck('seats', true);
showfieldcheck('weight', false);
showfieldcheck('setqty', true);
showfieldcheck('item_tier_override', true);
if ($MoS_enabled) {
	showfieldcheck('discount', true);
} else {
	showfieldcheck('discount', false);
}
showfieldcheck('freight', false);

// make an array of ItemField objects
// ItemField(strFieldName, boolIsShown, boolNullDisable, intFieldSize, intFieldMaxSize)
// ItemCheckbox($id, $show, $fieldPre = '', $fieldPost = '')
// first we unset the old ones
unset($fields);
$fields[] = new ItemField('display_order',$showfields['display_order'],false,3,6);
$fields[] = new ItemField('partno',$showfields['partno'],false,7,100);
$fields[] = new ItemField('sku',$showfields['sku'],false,7,100);
$fields[] = new ItemField('description',$showfields['description'],false,20,100);
$fields[] = new ItemField('price',$showfields['price'],false,4,20);
$fields[] = new ItemField('cost',$showfields['cost'],false,4,20);
$fields[] = new ItemField('markup',$showfields['markup'], false,3,20,'','%');
$fields[] = new ItemField('size',$showfields['size'],false,4,20);
$fields[] = new ItemField('numinset',$showfields['numinset'],false,1,20);
$fields[] = new ItemField('set_',$showfields['set_'],true,3,20);
$fields[] = new ItemField('set_cost',$showfields['set_cost'],true,3,20);
$fields[] = new ItemField('set_markup',$showfields['set_markup'], false,3,20,'','%');
$fields[] = new ItemField('matt',$showfields['matt'],true,3,20);
$fields[] = new ItemField('matt_cost',$showfields['matt_cost'],true,3,20);
$fields[] = new ItemField('matt_markup',$showfields['matt_markup'], false,3,20,'','%');
$fields[] = new ItemField('box',$showfields['box'],true,3,20);
$fields[] = new ItemField('box_cost',$showfields['box_cost'],true,3,20);
$fields[] = new ItemField('box_markup',$showfields['box_markup'], false,3,20,'','%');
$fields[] = new ItemField('stock',$showfields['stock'],true,1,20); // stock day field
$fields[] = new ItemField('cubic_ft',$showfields['cubic_ft'],false,3,20);
$fields[] = new ItemField('seats',$showfields['seats'],false,3,20);
$fields[] = new ItemField('weight',$showfields['weight'],false,3,20);
$fields[] = new ItemField('setqty',$showfields['setqty'],false,1,20);
$fields[] = new ItemField('avail',$showfields['stock'],true,3,20);
$fields[] = new ItemCheckbox('item_tier_override', $showfields['item_tier_override']);
$fields[] = new ItemField('discount',$showfields['discount'],false,7,100);
$fields[] = new ItemField('freight',$showfields['freight'],false,7,100);

if (!isset($header_result)) {
	$sql = "select * from ".$prefix."form_headers where ID=".$_POST['ajax_header_id'];
	$query = mysql_query($sql);
	checkDBError($sql);
	if (!mysql_num_rows($query)) {
		echo "Header Deleted";
	}
	$result = mysql_fetch_array($query, MYSQL_ASSOC);
} else {
	$result = $header_result;
}
?>
<table width="1024" border="0" cellpadding="5" cellspacing="0">
<tr><td colspan="2"><hr></td></tr>
<tr>
	<td colspan=4> <span class="text_12">(Clear Heading And Submit To Delete)</span>
      <form action="<?php echo $prefix; ?>form-header-edit.php" method="post">
	<input type="hidden" name="headerID" value="<?php echo $result['ID']; ?>">
	<input type="hidden" name="form" value="<?php echo $result['form']; ?>">
	<input type="text" name="header" size="30" maxlength="100" value="<?php echo $result['header']; ?>">
	<input type="submit" style="background-color:#CA0000;color:white" value="Edit Heading">
	</form>
	</td>
	<td colspan=9>
	<?php if (!$MoS_enabled) { ?>
	<span class="text_12">(Replace group with a CSV group import)</span>
	<form action="csv_import.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="form" value="<?php echo $result['form']; ?>">
		<input type="hidden" name="header" value="<?php echo $result['ID']; ?>">
		<input type="file" name="csvfile">
	    <input type="submit" style="background-color:#CA0000;color:white" value="Submit CSV">
	</form>
	<?php } ?>
	</td>
	<td align="right" valign="bottom" colspan="2">
	<p><b>[<a href="<?php echo $prefix; ?>form-item-add.php?header=<?php echo $result['ID']; ?>">Add Item</a>]</b></p>
	</td>
</tr>
</table>
<form action="<?php echo $prefix; ?>form-itemsedit.php" method="post">
<span class="text_12">
Set All <select name="allstock" id="allstock">
<?php
	$stock_types = stock_status(0);
	echo "    <option value=\"0\" selected>No Change</option>\n";
	foreach ($stock_types as $stock_type) {
		echo "   "; // Indent
		echo "<option value=\"".$stock_type['id']."\" style=\"".$stock_type['style']."\"";
		echo ">".$stock_type['name']."</option>\n";
	}
	echo '</select>'."\n";
?>
</select>
</span>
<table width="1024" border="0" cellpadding="5" cellspacing="0">
	  <!-- <table border="0" cellpadding="5" cellspacing="0"> -->
<?php
$sql = "select * from ".$prefix."form_items where header=".$result['ID']." order by display_order";
$query2 = mysql_query($sql);
checkDBError();

if (mysql_num_rows($query2) > 0) {
?>
        <tr bgcolor="#fcfcfc" class="fat_black_12">
          <?php if ($showfields['display_order']) { ?><td>Order</td><?php } ?>
          <?php if ($showfields['partno']) { ?><td>Part&nbsp;#</td><?php } ?>
	  <?php if ($showfields['sku']) { ?><td>SKU</td><?php } ?>
          <?php if ($showfields['description']) { ?><td>Desc.</td><?php } ?>
          <?php if ($showfields['price']) { ?><td>Price</td><?php } ?>
          <?php if ($showfields['cost']) { ?><td>Cost</td><?php } ?>
          <?php if ($showfields['markup']) { ?><td>Markup</td><?php } ?>
          <?php if ($showfields['size']) { ?><td>Size</td><?php } ?>
          <?php if ($showfields['numinset']) { ?><td>#&nbsp;in&nbsp;Set</td><?php } ?>
          <?php if ($showfields['set_']) { ?><td>Set Price</td><?php } ?>
          <?php if ($showfields['set_cost']) { ?><td>Set Cost</td><?php } ?>
          <?php if ($showfields['set_markup']) { ?><td>Set Markup</td><?php } ?>
          <?php if ($showfields['matt']) { ?><td>Matt Price</td><?php } ?>
          <?php if ($showfields['matt_cost']) { ?><td>Matt Cost</td><?php } ?>
          <?php if ($showfields['matt_markup']) { ?><td>Matt Markup</td><?php } ?>
          <?php if ($showfields['box']) { ?><td>Box Price</td><?php } ?>
          <?php if ($showfields['box_cost']) { ?><td>Box Cost</td><?php } ?>
          <?php if ($showfields['box_markup']) { ?><td>Box Markup</td><?php } ?>
          <?php if ($showfields['stock']) { ?>
		  	<td align="center">Stock&nbsp;Status</td>
			<td align="center">Day</td>
		  <?php } ?>
		  <?php if ($showfields['cubic_ft']) { ?><td>Volume</td><?php } ?>
                  <?php if ($showfields['seats']) { ?><td>Seats</td><?php } ?>
		  <?php if ($showfields['weight']) { ?><td>Weight(lbs.)</td><?php } ?>
		  <?php if ($showfields['setqty']) { ?><td>Set&nbsp;Qty</td><?php } ?>
		  <?php if ($showfields['stock']) { ?><td>Alloc</td><?php } ?>
		  <?php if ($showfields['stock']) { ?><td>Avail</td><?php } ?>
          <?php if ($showfields['item_tier_override']) { ?><td>Tier Override</td><?php } ?>
		  <?php if ($showfields['discount']) { ?><td>Item Discount</td><?php } ?>
                  <?php if ($showfields['freight']) { ?><td>Item Freight</td><?php } ?>
		  <td>&nbsp;</td>
        </tr>
<?php }
while ($result2 = mysql_fetch_array($query2, MYSQL_ASSOC))
{
	$stock = stock_status($result2['stock']);
        $result2['discount'] = loadDiscount('discount',array("item_id" => $result2['ID']),"form_item");
        $result2['freight'] = loadDiscount('freight',array("item_id" => $result2['ID']),"form_item");
?>
          <tr class="text_12">

<?php	// this is the old code, commented out for now
	/*

            <?php $field = "display_order"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="3" maxlength="6" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "partno"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="7" maxlength="100" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
	    <?php $field = "sku"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="7" maxlength="100" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "description"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="20" maxlength="100" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "price"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="4" maxlength="20" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "size"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="4" maxlength="20" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "numinset"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="1" maxlength="20" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "set_"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="3" maxlength="20" value="<?php echo $result2[$field]; ?>"<?php if (is_null($result2[$field])) echo " DISABLED"; ?>><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "matt"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="3" maxlength="20" value="<?php echo $result2[$field]; ?>"<?php if (is_null($result2[$field])) echo " DISABLED"; ?>><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php $field = "box"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="3" maxlength="20" value="<?php echo $result2[$field]; ?>"<?php if (is_null($result2[$field])) echo " DISABLED"; ?>><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
            <?php if ($showfields['stock']) { ?>
			<?php
				$style = $stock['style'];
				$name = $stock['name'];
				$stock_types = stock_status(0);
				echo "\n".'<td><select name="data_'.$result2['ID'].'_stock" id="data_stock">'."\n";
				foreach ($stock_types as $stock_type) {
					echo "        "; // Indent
					echo "<OPTION VALUE=\"".$stock_type['id']."\" STYLE=\"".$stock_type['style']."\"";
					if ($stock_type['id'] == $result2['stock']) {
						echo " SELECTED";
					}
					echo ">".$stock_type['name']."</OPTION>\n";
				}
				echo '</select>'."\n";
				echo '<input name="old_'.$result2['ID'].'_stock" type="hidden" id="old_'.$result2['ID'].'_stock" value="'.$result2['stock'].'"></td>'."\n";
			?>
			<?php $field = "stock_day"; ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="1" maxlength="20" value="<?php echo $result2[$field]; ?>"<?php if (is_null($result2[$field])) echo " DISABLED"; ?>><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td>
			<?php } ?>
			 <?php $field = "cubic_ft"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="3" maxlength="20" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
			 <?php $field = "weight";

			 <?php $field = "setqty"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="1" maxlength="20" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?>
			 <?php if ($showfields['stock']) { ?>
			<?php


				$alloc = $result2['alloc'];
				if ($alloc != "" && $alloc >= 0) {
					$avail = $result2['avail'];
				} else {
					$alloc = '&nbsp;';
					$avail = '';
				}
				print "<td>${alloc}</td>\n";
				?>
				<?php $field = "avail"; ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="3" maxlength="20" value="<?php echo $avail; ?>"<?php if (is_null($result2[$field])) echo " DISABLED"; ?>><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $avail; ?>"></td>
				<?php } ?>
			<?php $field = "discount"; ?><?php if ($showfields[$field]) { ?><td><input name="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="text" id="data_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" size="7" maxlength="100" value="<?php echo $result2[$field]; ?>"><input name="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" type="hidden" id="old_<?php echo $result2['ID']; ?>_<?php echo $field; ?>" value="<?php echo $result2[$field]; ?>"></td><?php } ?><?php
				// Removed Super-Admin restriction per Task #116 -- Will 9-7-2005
				//if ($security == "S") {//only all super admin users to access edit link - added by goody 10-20-04
					$item_id = $result2['ID'];
					$header_id = $result['ID'];
					print "<td align=\"right\"><b>[<a href=\"".$prefix."form-item-add.php?ID=${item_id}&header=${header_id}\">Edit</a>]&nbsp;[<a href=\"".$prefix."form-item-add.php?action=delete&ID=${item_id}&header=${header_id}\" onClick=\"return confirm('Are you sure you wish to delete the item \\'".$result2['partno']." - ".$result2['description']."\\'?');\">Delete</a>]</b></td>\n";
				//} Removed Super-Admin restriction per Task #116 -- Will 9-7-2005
			?>
          </tr>
        <?php
    */
    // new code goes here, using classes
    foreach($fields as $item)
    {
    	if($item->shown)
    	{
    		if($item->id == 'stock')
    		{
    			$style = $stock['style'];
				$name = $stock['name'];
				$stock_types = stock_status(0);
				echo "\n".'<td><select name="data_'.$result2['ID'].'_stock" id="data_stock">'."\n";
				foreach ($stock_types as $stock_type) {
					echo "        "; // Indent
					echo "<OPTION VALUE=\"".$stock_type['id']."\" STYLE=\"".$stock_type['style']."\"";
					if ($stock_type['id'] == $result2['stock']) {
						echo " SELECTED";
					}
					echo ">".$stock_type['name']."</OPTION>\n";
				}
				echo '</select>'."\n";
				echo '<input name="old_'.$result2['ID'].'_stock" type="hidden" id="old_'.$result2['ID'].'_stock" value="'.$result2['stock'].'"></td>'."\n";
				?><td><input name="data_<?php= $result2['ID'] ?>_<?php= $item->id."_day" ?>" type="text" id="data_<?php= $result2['ID'] ?>_<?php= $item->id."_day" ?>" size="<?php= $item->fieldSize ?>" maxlength="<?php= $item->fieldMax ?>" value="<?php= $result2[$item->id."_day"] ?>"<?php
				if($item->nullDisable)
				{
					if(is_null($result2[$item->id."_day"])) echo " DISABLED";
				}?>><input name="old_<?php= $result2['ID']; ?>_<?php= $item->id."_day" ?>" type="hidden" id="old_<?php= $result2['ID']; ?>_<?php= $item->id."_day" ?>" value="<?php echo $result2[$item->id."_day"]; ?>"></td>
				<?php
    		}
    		else if($item->id == 'avail')
    		{
    			$alloc = $result2['alloc'];
				if ($alloc != "" && $alloc >= 0) {
					$avail = $result2['avail'];
				} else {
					$alloc = '&nbsp;';
					$avail = '';
				}
				print "<td>${alloc}</td>\n";
				?>
				<td><input name="data_<?php= $result2['ID'] ?>_<?php= $item->id ?>" type="text" id="data_<?php= $result2['ID'] ?>_<?php= $item->id ?>" size="<?php= $item->fieldSize ?>" maxlength="<?php= $item->fieldMax ?>" value="<?php= $avail ?>"<?php
				if($item->nullDisable)
				{
					if(is_null($result2[$item->id])) echo " DISABLED";
				}?>><input name="old_<?php= $result2['ID'] ?>_<?php= $item->id ?>" type="hidden" id="old_<?php= $result2['ID'] ?>_<?php= $item->id ?>" value="<?php= $avail ?>"></td>
				<?php
    		}
            elseif ($item->type == 'checkbox')
            {
                ?>
                    <td nowrap>
                        <?php= htmlentities($item->fieldPre) ?>
                        <input
                            type="checkbox"
                            id="data_<?php= $result2['ID'] ?>_<?php= $item->id ?>"
                            name="data_<?php= $result2['ID'] ?>_<?php= $item->id ?>"
                            value="1"
                            <?php if ($result2[$item->id] == 1): ?>CHECKED<?php endif; ?>
                            <?php if($item->nullDisable && is_null($result2[$item->id])): ?>DISABLED<?php endif; ?>
                        >
                        <?php= htmlentities($item->fieldPost) ?>
                        <input
                            name="old_<?php= $result2['ID'] ?>_<?php= $item->id ?>"
                            type="hidden" id="old_<?php= $result2['ID'] ?>_<?php= $item->id; ?>"
                            value="<?php= $result2[$item->id]; ?>"
                        >
                    </td>
                <?php
            }
    		else
    		{
    			?><td nowrap><?php= htmlentities($item->fieldPre) ?><input name="data_<?php= $result2['ID'] ?>_<?php= $item->id ?>" type="text" id="data_<?php= $result2['ID'] ?>_<?php= $item->id ?>" size="<?php= $item->fieldSize ?>" maxlength="<?php= $item->fieldMax ?>" value="<?php= $result2[$item->id] ?>"<?php
    			if($item->nullDisable)
    			{
    				if(is_null($result2[$item->id])) echo " DISABLED";
    			}?>><?php= htmlentities($item->fieldPost) ?><input name="old_<?php= $result2['ID'] ?>_<?php= $item->id ?>" type="hidden" id="old_<?php= $result2['ID'] ?>_<?php= $item->id; ?>" value="<?php= $result2[$item->id]; ?>"></td><?php
    		}
    	}
    }
				// Removed Super-Admin restriction per Task #116 -- Will 9-7-2005
				//if ($security == "S") {//only all super admin users to access edit link - added by goody 10-20-04
					$item_id = $result2['ID'];
					$header_id = $result['ID'];
					print "<td align=\"right\"><b>[<a href=\"".$prefix."form-item-add.php?ID=${item_id}&header=${header_id}\">Edit</a>]&nbsp;[<a href=\"".$prefix."form-item-add.php?action=delete&ID=${item_id}&header=${header_id}\" onClick=\"return confirm('Are you sure you wish to delete the item \\'".$result2['partno']." - ".$result2['description']."\\'?');\">Delete</a>]</b></td>\n";
				//} Removed Super-Admin restriction per Task #116 -- Will 9-7-2005

}
?>
		<tr>
		    <td colspan="12"> <input name="form_id" type="hidden" id="form_id" value="<?php echo $result['form']; ?>">
	        	<input type="submit" name="submit" value="Submit Item Changes" style="background-color:#CA0000;color:white" onclick="formedit_formsubmit(<?php echo $result['ID']; ?>); postForm(this); return false;" >
	        </td>
		</tr>
	</table>
</form>
