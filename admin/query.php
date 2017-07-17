<?php
require('database.php');
require('secure.php');
if (!secure_is_superadmin()) die("Access Denied");
$sql = stripslashes($_REQUEST['sql']);

$query = mysql_query($sql);

// Define Special Properties per field
$fields = array();

$title = 'SQL Query - '.$sql;

$filter = '
<form action="query.php">
<textarea name="sql" cols=100 rows=10>
'.htmlentities($sql).'
</textarea><br>
<select name="type">
<option value="html">HTML</option>
<option value="csv">CSV</option>
</select><br>
<input type="submit">
</form>
';

$type = 'html';
if ($_REQUEST['type']) $type = $_REQUEST['type'];

$adminside = true;
require('../inc_querydisplay.php');
