<?php

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct Execution Prohibited</h2>');

if ($_POST) {
	foreach($_POST as $key => $val) {
		if ($val == 'show') {
			setPref('me-'.$key, true);
		} elseif ($val == 'hide') {
			setPref('me-'.$key, false);
		} else {
			setPref('me-'.$key, null);
		}
	}
}

$showfields = array();
$showfields[] = array('display_order', 'Display Order');
$showfields[] = array('partno', 'Part Number');
$showfields[] = array('sku', 'SKU');
$showfields[] = array('description','Description');
$showfields[] = array('price','Price');
$showfields[] = array('cost','Cost');
$showfields[] = array('markup','Markup');
$showfields[] = array('size','Size');
$showfields[] = array('numinset','Number in Set');
$showfields[] = array('set_', 'Set Price');
$showfields[] = array('set_cost', 'Set Cost');
$showfields[] = array('set_markup', 'Set Markup');
$showfields[] = array('matt', 'Matt Price');
$showfields[] = array('matt_cost', 'Matt Cost');
$showfields[] = array('matt_markup', 'Set Markup');
$showfields[] = array('box', 'Box Price');
$showfields[] = array('box_cost', 'Box Cost');
$showfields[] = array('box_markup', 'Box Markup');
$showfields[] = array('stock', 'Allocation/Stock');
$showfields[] = array('cubic_ft', 'Cubic Ft');
$showfields[] = array('seats','Seats');
$showfields[] = array('weight', 'Weight');
$showfields[] = array('setqty', 'Set Qty');
$showfields[] = array('item_tier_override', 'Item Tier Override');
$showfields[] = array('discount', 'Discount %/$');
$showfields[] = array('freight', 'Freight %/$');


?>
<br>
<div class="fat_black">Mass Edit Preferences</div>
<br>

<table border="0" cellspacing="0" cellpadding="5">
  <tr bgcolor="#fcfcfc">
    <td class="fat_black_12">Enabled</td>
    <td class="fat_black_12">Column Name</td>
  </tr>
  <form action="<?php if ($MoS_enabled) { echo "MoS_"; } ?>form-prefs.php" method="post">
  <?php
foreach ($showfields as $name) {
	$nicename = $name[1];
	$name = $name[0];
	$value = getPref("me-".$name);
  ?>
  <tr>
  	<td class="text_12">
  		<select name="<?php= $name ?>">
  			<option value="show"<?php if ($value === true) echo ' selected'; ?>>show</option>
  			<option value="hide"<?php if ($value === false) echo ' selected'; ?>>hide</option>
  			<option value="default"<?php if (is_null($value)) echo ' selected'; ?>>default</option>
  		</select>
  	</td>
  	<td class="text_12">
  		<?php= $nicename ?>
  	</td>
  </tr>
  <?php
}
  ?>
  <tr>
  	<td colspan="2" class="text_12"><input type="submit"></td>
  </tr>
  </form>
</table>
