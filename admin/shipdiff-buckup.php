<?
require('database.php');
require('secure.php');

$sql = "SELECT id, name FROM vendor WHERE disabled != 'Y'";
$query = mysql_query($sql);
checkdberror($sql);


// Define Special Properties per field
$fields = array();

$title = 'Shipping Speed Report';
$width = "300px";

$filter = '
<form action="shipdiff.php">
Vendor: <select name="vendor">
<option value="">All Vendors</option>
';

while ($row = mysql_fetch_assoc($query)) {
    $filter .= '<option value="'.$row['id'].'"';
    if ($_REQUEST['vendor'] == $row['id']) $filter .= ' SELECTED';
    $filter .= '>'.htmlentities($row['name']).'</option>'."\n";
}

if (is_numeric($_REQUEST['days'])) {
    $days = $_REQUEST['days'];
} else {
    $days = 7;
}

$filter .= '</select><br>
Days: <input type="text" name="days" value="'.$days.'"><br>
Summary: <input type="checkbox" name="summary"';
if ($_REQUEST['summary'] == 'on')
    $filter .= ' checked';
$filter .= '><br />
Type: <select name="type">
<option value="html">HTML</option>
<option value="csv">CSV</option>
</select><br>
<input type="submit">
</form>
';
if (!is_numeric($_REQUEST['vendor'])) unset($_REQUEST['vendor']);
$where = '';
if ($_REQUEST['vendor']) {
    $where = ' AND `vendor_id` = "'.$_REQUEST['vendor'].'" ';
}
if ($_REQUEST['summary'] == 'on') {
    $sql_what = "SUM((DATEDIFF(`shipdate`,`date`)+1)) / COUNT(`po`) as `avg ship diff`";
} else {
    $sql_what = "po, date, (DATEDIFF(`shipdate`,`date`) + 1) AS 'days to ship'";
}
$sql = "SELECT ".$sql_what." FROM `claim_order` WHERE   DATE_SUB(CURDATE(),INTERVAL ".$days." DAY) <= `date` AND `shipdate`". $where." ORDER BY po";
$query = mysql_query($sql);
checkdberror($sql);

$type = 'html';
if ($_REQUEST['type']) $type = $_REQUEST['type'];

$adminside = true;
require('../inc_querydisplay.php');
