<?php
require("database.php");
require("secure.php");
require("menu.php");

$mode = 'day';
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'month') {
    $mode = 'month';
}

$monthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');

function getTotal($po_id) {

	$sql = "SELECT freight_percentage, discount_percentage, total FROM order_forms WHERE ID=$po_id";
	$query = mysql_query($sql);
	$result = mysql_fetch_array($query);
	$grandtotal = $result['total'];
	return $grandtotal;
}

function is_processed($po_id) {
	$sql = "select processed from order_forms where ID='$po_id'";
	$query = mysql_query($sql);
	checkDBError();
	
	if($result = mysql_fetch_array($query))
		return $result['processed'];
	return 'N';
}

function get_po_num($po_id,$form,$ordered,$user) {
	global $sql;
	if ($po_id == "0") {
		$sql = "insert into order_forms values('NULL','N','$ordered','$form','$user','');";
		mysql_query($sql);
		checkDBError();	
		$po_id = mysql_insert_id();

		$sql = "update orders set po_id='$po_id' where form='$form' and ordered='$ordered' and user='$user'";
		mysql_query($sql);
		checkDBError();
	}
	return $po_id + 1000;
}

function getFormName($header) {
	$sql = "select snapshot_forms.name from snapshot_forms where snapshot_forms.ID=$header";
	$query = mysql_query($sql);
	checkDBError();

	if($result = mysql_fetch_array($query))
		return $result['name'];
	return "";
}

function getVendorName($vendor_id) {
        if (is_null($vendor_id)) return '';
	$query = mysql_query("SELECT name FROM vendors WHERE ID=$vendor_id");
	checkDBError();
	if ($result = mysql_fetch_array($query))
		return $result['name'];
	return "";
}

function getUserName($user_id) {
        if (is_null($user_id)) return '';
	$query = mysql_query("SELECT first_name, last_name FROM snapshot_users WHERE ID=$user_id");
	checkDBError();
	if ($result = mysql_fetch_array($query))
		$return_string = $result['last_name'].", ".$result['first_name'];
	return $return_string;
}

function formatDate($date) {
	return date('m/d/Y', strtotime($date));
}

if ($search == "" && $ordered == "")
{
?>
<title>RSS Administration</title>
<p align="center"><b>Select a date and vendor:</b></p>
<form action="vendor-orders.php" method="post">
  <table border="0" cellpadding="0" cellspacing="3" align="center">
    <tr> 
      <td><p> 
          <select name="month">
            <?php
for ($x=1; $x <=12; $x++) {
	if ($x == date("m"))
		echo "<option value=\"$x\" selected>$monthName[$x]</option>";
	else
		echo "<option value=\"$x\">$monthName[$x]</option>";
}
?>
          </select>
<?php if ($mode == 'day'): ?>
          <select name="day">
            <?php
for ($x=1; $x <=31; $x++) {
	if ($x == date("d"))
		echo "<option value=\"$x\" selected>$x</option>";
	else
		echo "<option value=\"$x\">$x</option>";
}
?>
          </select>
<?php endif; ?>
          <select name="year">
            <?php
for ($x=2002; $x <=date('Y')+1; $x++) {
	if ($x == date("Y"))
		echo "<option value=\"$x\" selected>$x</option>";
	else
		echo "<option value=\"$x\">$x</option>";
}
?>
          </select>
          <input type="hidden" name="mode" value="<?php= htmlentities($mode) ?>" />
          <?php if ($mode == 'day'): ?>(<a href="<?php= $_SERVER['PHP_SELF']?>?mode=month">Switch to by Month</a>)<?php endif; ?>
          <?php if ($mode == 'month'): ?>(<a href="<?php= $_SERVER['PHP_SELF']?>?mode=day">Switch to by Day</a>)<?php endif; ?>
        </p></td>
    </tr>
    <tr> 
      <td><p><b>Vendor:</b> <select name="vendor" size="1">
	  <option value="0">All Vendors</option>
          <?php
$query = mysql_query("SELECT ID, name FROM vendors ORDER BY name");
checkDBError();
while ($result = mysql_fetch_Array($query))
	echo "<option value=\"".$result['ID']."\">".$result['name']."</option>";
?>
        </select></p></td>
    </tr>
    <tr> 
      <td><input type="submit" name="search" value="View Orders"> <input type="reset" value="Reset"></td>
    </tr>
  </table>
</form>
<?php
	footer($link);
	exit;
}

?>
<link rel="stylesheet" href="../styles.css" type="text/css">
<?php
function MinusOneDay($year, $month, $day) {
	if ($day == 1) {
		if ($month == 1) {
			//january, so change year
			$year -= 1;
			$month = 12;
		}
		else
			$month -= 1;
		if (($month==1)||($month==3)||($month==5)||($month==7)||($month ==8)||($month==10)||($month==12))
			$day = 31;
		elseif (($month==4)||($month==6)||($month==9)||($month==11))
			$day = 30;
		else {
			if (date("L",strtotime("$year-$month-$day")) == 1) //check for leap year
				$day = 29;
			else
				$day = 28;
		}
		$return_date = "$year-$month-$day";
	}
	else
		$return_date = "$year-$month-".($day-1);
	return $return_date;
}

function EndOfMonth($year, $month) {
        if (($month==1)||($month==3)||($month==5)||($month==7)||($month ==8)||($month==10)||($month==12))
                $day = 31;
        elseif (($month==4)||($month==6)||($month==9)||($month==11))
                $day = 30;
        else {
                if (date("L",strtotime("$year-$month-$day")) == 1) //check for leap year
                        $day = 29;
                else
                        $day = 28;
        }
        $return_date = "$year-$month-$day";
        
	return $return_date;
}

function BeginOfMonth($year, $month) {
	return "$year-$month-1";
}

if ($mode == 'day') {
 	$query_date = "$year-$month-$day 16:00:00";
	$query_date_minus_one = MinusOneDay($year, $month, $day)."  15:59:59";
} elseif ($mode == 'month') {
	$query_date = EndOfMonth($year, $month)." 23:59:59";
	$query_date_minus_one = BeginOfMonth($year, $month)." 00:00:00";
}

if ($vendor == 0) {
	$sql = "SELECT DISTINCT SUM(orders.total) as total, orders.snapshot_user, forms.vendor  FROM order_forms AS orders 
	 INNER JOIN forms ON orders.form = forms.ID WHERE orders.deleted = 0 AND
	 ((orders.ordered > '$query_date_minus_one')
	 AND (orders.ordered <= '$query_date')) GROUP BY forms.vendor, orders.snapshot_user";
}
else {
	$sql = "SELECT DISTINCT SUM(orders.total) as total, orders.snapshot_user, forms.vendor FROM order_forms AS orders 
	 INNER JOIN forms ON orders.form = forms.ID WHERE orders.deleted = 0 AND forms.vendor=$vendor
	 AND ((orders.ordered > '$query_date_minus_one')
	 AND (orders.ordered <= '$query_date')) GROUP BY forms.vendor, orders.snapshot_user";
}
$query = mysql_query($sql);
checkDBError();
?>
<p><b><?php echo getVendorName($vendor); ?></b></p>
<p>
<?php
echo "The following orders were placed between ";
if ($mode == 'day') {
    echo "4:00 PM ";
}
echo "<b>";
echo date("F j, Y",strtotime($query_date_minus_one))."</b> and ";
if ($mode == 'day') {
    echo "4:00 PM ";
}
echo "<b>";
echo date("F j, Y",strtotime($query_date))."</b>:";
?>
</p>
<table border="0" cellspacing="0" cellpadding="5" width="80%">
  <tr> 
    <td class="fat_black_12" bgcolor="#fcfcfc">Name</td>
    <td class="fat_black_12" bgcolor="#fcfcfc">Vendor</td>
    <td class="fat_black_12" bgcolor="#fcfcfc">Total</td>
    <td class="fat_black_12" bgcolor="#fcfcfc">&nbsp;</td>
  </tr>
<?php
$total = 0;
while ($result = mysql_fetch_array($query)) {
    $total += $result['total'];
?>
  <tr> 
    <td class="text_12"><?php echo getUserName($result['snapshot_user']); ?></td>
    <td class="text_12"><?php echo getVendorName($result['vendor']); ?></td>
    <td class="text_12"><?php echo makeThisLookLikeMoney($result['total']); ?></td>
    <td class="text_12">&nbsp;</td>
  </tr>
  <?php
}
?>
  <tr> 
    <td class="text_12">&nbsp;</td>
    <td class="text_12">&nbsp;</td>
    <td class="fat_black_12"><?php echo makeThisLookLikeMoney($total); ?></td>
    <td class="text_12">&nbsp;</td>
  </tr>
</table>
<?php footer($link); ?>