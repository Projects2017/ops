<?php
require("database.php");
require("../inc_content.php");
require("xml.php");

processPO($po);
mysql_close($link);
header("location: report-orders.php?ordered=$ordered&ordered2=$ordered2&deleted=0&" . urldecode($_POST['request']));
?>
