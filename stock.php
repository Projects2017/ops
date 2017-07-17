<?php
require('database.php');
require('secure.php');
$extra_where = array();

$extra_where[] = "f.ID IN (SELECT form FROM form_access where user = '$userid')";


$sql = "SELECT f.name as form, h.header, i.partno, i.description, i.stock, i.stock_day FROM form_items AS i INNER JOIN form_headers AS h ON i.header = h.ID INNER JOIN forms AS f ON h.form = f.ID WHERE stock IN (SELECT id FROM stock_status WHERE block_order = 'Y')  AND f.alloworder = 'Y'";
if ($extra_where) $sql .= 'AND '.implode(' AND ',$extra_where);
$sql .= " ORDER BY f.name, f.ID, h.display_order, h.header, h.ID, i.display_order, i.description";
$query = mysql_query($sql);
checkdberror($sql);

// Define Special Properties per field
$fields = array();
$fields['stock'] = array('type' => 'stock');
$fields['stock_day'] = array('type' => 'hidden');
$fields['form'] = array('type' => 'norepeat','class' => 'fat_black_12');
$fields['header'] = array('type' => 'norepeat');
$fields['partno'] = array('type' => 'norepeat', 'name' => 'Part #');
$title = 'Out of Stock Report - '.date('j F Y');

$type = 'html';
if ($_REQUEST['type']) $type = $_REQUEST['type'];

require('inc_querydisplay.php');