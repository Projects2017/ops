<?php
require('database.php');
require('secure.php');

$sql = "SELECT id, name FROM vendor WHERE disabled != 'Y'";
$query = mysql_query($sql);
checkdberror($sql);


// Define Special Properties per field
$fields = array();

$title = 'Temporary Managers R\eport';

$filter = '';
$sql = "SELECT `last_name` , `manager` FROM `users` WHERE `nonPMD` != 'Y' AND `disabled` != 'Y' ORDER BY `last_name`";
$query = mysql_query($sql);

$type = 'html';
if ($_REQUEST['type']) $type = $_REQUEST['type'];

$adminside = true;
require('../inc_querydisplay.php');
