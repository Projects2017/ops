<?php
require("database.php");
require("secure.php");
require("../inc_rateofsale.inc.php");

$ID = $_REQUEST['ID'];
$from_date = $_REQUEST['from_date'];
$to_date = $_REQUEST['to_date'];
$customer = $_REQUEST['customer'];

if (!is_numeric($ID)) die("Invalid Form");

$sql = "select forms.minimum, forms.name from forms where forms.ID=$ID";
$query = mysql_query($sql);
checkDBError();

if ($result = mysql_fetch_Array($query))
{
	$name = $result['name'];
	$minimum = viewpo_getmin($result['minimum']);
	$minimum = $minimum['text'];
} else {
	die("Form not found");
}
if ($ros_enabled) {
	if (!$from_date) $from_date = date('m/d/Y',strtotime('-90 days'));
	if (!$to_date) $to_date = date('m/d/Y');
        if (!$customer) $customer = null;
	$ros = ros_form($ID, $customer, strtotime($from_date), strtotime($to_date." 23:59:59"));
}
?>
<html>
<head>
<title>RSS</title>
<link rel="stylesheet" href="../styles.css" type="text/css">
<script src="../include/common.js" type="text/javascript"></script>
<link href="../include/CalendarControl.css" rel="stylesheet" type="text/css">
<script src="../include/CalendarControl.js" type="text/javascript"></script>
</head>
<body>
<br>
<table width="100%" border="0" cellpadding="5" cellspacing="0">
<thead>
  <tr> 
    <td><span class="fat_black"><?php echo $name ?></span></td>
    <td align="right"> 
      <?php if( $minimum != "" ) { ?>
      <span class="text_12"><b>Minimum:</b> <?php echo $minimum; ?></span>
      <?php } ?>
    </td>
  </tr>
  <tr class="noprint">
  	<?php if ($ros_enabled) { ?>
  	<td class="text_12" colspan="2">
  		<form method="post" action="form-view.php?ros_enabled=1&ID=<?php echo $ID; ?>">
                        <select name="customer" size="1">
                        <?php
                            $sql = "SELECT ID,first_name,last_name FROM users ORDER BY last_name,first_name";
                            $query = mysql_query($sql);
                            checkDBError($sql);
                            echo "<option value=\"\">All Customers</option>\n";
                            while ($result = mysql_fetch_Array($query)) {
                                echo "<option value=\"".$result['ID']."\"";
                                if ($result['ID'] == $customer) {
                                    echo ' SELECTED';
                                }
                                echo ">".$result['last_name']." - ".$result['first_name']."</option>";
                            }
                        ?>
                        </select><br />
  			To: <input class="date" type="text" id="from_date" name="from_date" value="<?php echo date('m/d/Y',strtotime($from_date)); ?>">
  			From: <input class="date" type="text" id="to_date" name="to_date" value="<?php echo date('m/d/Y',strtotime($to_date)); ?>">
  			<input type="submit">
  		</form>
  	</td>
  	<?php } else { ?>
  	<td class="text_12">(<a href="form-view.php?ros_enabled=1&ID=<?php echo $ID; ?>">Enable RoS</a>)</td>
  	<?php } ?>
  </tr>
</thead>
<tbody>
  <tr> 
    <td colspan="2"> <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
  <?php
$sql = "SELECT form_items.*, form_headers.header FROM form_items LEFT JOIN form_headers ON form_headers.ID=form_items.header WHERE form_headers.form=${ID} ORDER BY form_headers.display_order,form_items.display_order";
$query = mysql_query($sql);
checkDBError();
$result = db_result2array($query);
// Figure out which columns are not completely blank
$column_names = array("partno", "description", "price", "numinset", "size", "set_", "matt", "box");
$display_column = array();
$headers = array();
foreach ($column_names as $column) {
	$display_column[$column] = false;
}
foreach ($result as $row) {
	foreach ($column_names as $column) {
		// $row[$column] != "" && !is_null($row[$column])
		if ($row[$column])
			$display_column[$column] = true;
	}
	if (!isset($headers[$row['header']])) {
		$headers[$row['header']] = 0;
	}
	if ($row['numinset']) {
		$headers[$row['header']] += $row['price'] * $row['numinset'];
	}
}

$numcolumns = 1;
foreach ($column_names as $column) {
	if ($display_column[$column])
		$numcolumns++;
}

$numcolumns++; // Incriment for Stock Column
if ($ros_enabled) {
	$numcolumns += 2;
}

foreach($result as $row) {
		$newheader = $row['header'];
		if($oldheader != $newheader) {
?>
        <tr>
          <td colspan="<?php=$numcolumns ?>" class="text_12"><hr></td>
        </tr>
        <tr> 
          <td colspan="<?php=$numcolumns ?>" class="text_12"><b><?php echo $newheader; ?> - Set Cost: <?php echo makeThisLookLikeMoney($headers[$newheader]); ?></b></td>
        </tr>
        <tr>
		  <td bgcolor="#fcfcfc" class="fat_black_12" width="10px"></td>
		  <?php if ($display_column['partno']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Part&nbsp;#</td>
		  <?php } if ($display_column['description']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Desc.</td>
		  <?php } if ($display_column['price']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Price</td>
		  <?php } ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Stock</td>
		  <?php if ($display_column['size']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Size</td>
		  <?php } if ($display_column['numinset']) { ?>
		  <td bgcolor="#fcfcfc" class="fat_black_12">#&nbsp;in&nbsp;Set</td>
		  <?php } if ($display_column['color']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Color</td>
		  <?php } if ($display_column['set_']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Set</td>
		  <?php } if ($display_column['matt']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Matt</td>
		  <?php } if ($display_column['box']) { ?>
          <td bgcolor="#fcfcfc" class="fat_black_12">Box</td>
		  <?php } ?>
		  <?php if ($ros_enabled) { ?>
		  <td bgcolor="#fcfcfc" class="fat_black_12" width="1%">RoS&nbsp;/</td>
		  <td bgcolor="#fcfcfc" class="fat_black_12" width="1%">Days</td>
		  <?php } ?>
        </tr>
        <?php
		$oldheader = $newheader;
		}
?>
        <tr> 
		  <?php
             $stock = stock_status($row['stock']);
             if ($stock['block_order'] == 'Y') {
				 $stock = '*';
			 } else {
				 $stock = '';
			 }
          ?>
		  <td class="text_12" align="right" width="10px"><?php echo $stock; ?></td>
		  <?php if ($display_column['partno']) { ?>
          <td class="text_12"><?php echo $row['partno'] ?></td>
		  <?php } if ($display_column['description']) { ?>
          <td class="text_12"><?php echo $row['description'] ?></td>
		  <?php } if ($display_column['price']) { ?>
          <td class="text_12"><?php echo makeThisLookLikeMoney($row['price']) ?></td>
		  <?php } ?>
          <?php $stock = stock_status($row['stock']); ?>
          <td class="text_12"><?php echo $row['name']; ?><?php
          if ($row['stock_day']) {
          	echo ' ('.$row['stock_day'].')';
          }
          ?></td>
		  <?php if ($display_column['size']) { ?>
          <td class="text_12"><?php echo $row['size'] ?></td>
		  <?php } if ($display_column['numinset']) { ?>
		  <td class="text_12"><?php echo $row['numinset'] ?></td>
		  <?php } if ($display_column['color']) { ?>
          <td class="text_12"><?php echo $row['color'] ?></td>
		  <?php } if ($display_column['set_']) { ?>
          <td class="text_12"><?php echo $row['set_'] ?></td>
		  <?php } if ($display_column['matt']) { ?>
          <td class="text_12"><?php echo $row['matt'] ?></td>
		  <?php } if ($display_column['box']) { ?>
          <td class="text_12"><?php echo $row['box'] ?></td>
		  <?php } ?>
		  <?php if ($ros_enabled) { ?>
		  <td class="text_12"><?php echo $ros['partnos'][$row['partno']][$row['description']] ? $ros['partnos'][$row['partno']][$row['description']] : "0"; ?></td>
		  <td class="text_12"><?php echo $ros['days']; ?></td>
		  <?php } ?>
        </tr>
        <?php
} ?>
        </table></td>
  </tr>
  <tr>
	<td colspan="<?php echo $numcolumns; ?>" bgcolor="#fcfcfc" class="fat_black_12" align="center">Items marked with an asterisk * are either out of stock or will be available in the near future.<br />
	Contact dealer support for more information on this item's availability.</td>
  </tr>
  </tbody>
  <tfoot>
  <tr>
	<td colspan="<?php echo $numcolumns; ?>" bgcolor="#fcfcfc" class="fat_black_12" align="center"><?php require('../footer.php'); ?></td>
  </tr>
  </tfoot>
</table>
</body>
</html>
