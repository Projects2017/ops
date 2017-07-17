<?php
require("database.php");
require("secure.php");
require("inc_content.php");

$ID = $_REQUEST['ID'];
$from_date = $_REQUEST['from_date'];
$to_date = $_REQUEST['to_date'];
$customer = '';
if (secure_is_admin() && isset($_REQUEST['customer'])) {
    $customer = $_REQUEST['customer'];
}
if ($customer === '') {
    $customer = $userid;
}

if (!is_numeric($ID)) die("Invalid Form");

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

$sql = "select f.minimum, f.name from ".$table_prefix."forms as f where f.ID=$ID";
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
  	<?php if (1==2 && secure_is_admin()) { ?>
  	<td class="text_12" colspan="2">
  		<form method="post" action="form-view.php?ID=<?php echo $ID; ?>">
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
  			<input type="submit">
  		</form>
  	</td>
  	<?php } ?>
  </tr>
</thead>
<tbody>
  <tr>
    <td colspan="2"> <table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
  <?php
$sql = "SELECT fi.*, fh.header FROM ".$table_prefix."form_items AS fi LEFT JOIN ".$table_prefix."form_headers AS fh ON fh.ID=fi.header WHERE fh.form=${ID} ORDER BY fh.display_order,fi.display_order";
$query = mysql_query($sql);
checkDBError();
$result = db_result2array($query);
// Figure out which columns are not completely blank
$column_names = array("partno", "description", "price", "numinset", "size", "set_", "matt", "box");
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
$display_column = array();
$headers = array();
foreach ($column_names as $column) {
	$display_column[$column] = false;
}
foreach ($result as $row_id => $row) {
	foreach ($column_names as $column) {
		// $row[$column] != "" && !is_null($row[$column])
		if ($row[$column])
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
          <td colspan="<?php=$numcolumns ?>" class="text_12"><b><?php echo $newheader; ?><!-- - Set Cost: <?php echo makeThisLookLikeMoney($headers[$newheader]); ?>--></b></td>
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

        if (($row['price'] == "" || is_null($row['price']))
                    && (
                            ($row['markup'] == "" || is_null($row['markup']))
                            || ($row['cost'] == "" || is_null($row['cost']))
                    )) {
                    $row['price'] = $row['box'];
                    $row['cost'] = $row['box_cost'];
                    $row['markup'] = $row['box_markup'];
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
          <td class="text_12"><?php= makeThisLookLikeMoney(calcPrice('box', $row, $userid, $ID, $table_prefix)) ?></td>
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
          <td class="text_12"><?php= makeThisLookLikeMoney(calcPrice('set', $row, $userid, $ID, $table_prefix)) ?></td>
		  <?php } if ($display_column['matt']) { ?>
          <td class="text_12"><?php= makeThisLookLikeMoney(calcPrice('matt', $row, $userid, $ID, $table_prefix)) ?></td>
		  <?php } if ($display_column['box']) { ?>
          <td class="text_12"><?php= makeThisLookLikeMoney(calcPrice('box', $row, $userid, $ID, $table_prefix)) ?></td>
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
	<td colspan="<?php echo $numcolumns; ?>" bgcolor="#fcfcfc" class="fat_black_12" align="center"><?php require('footer.php'); ?></td>
  </tr>
  </tfoot>
</table>
</body>
</html>
