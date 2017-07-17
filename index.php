<?php
if (strpos($_SERVER['HTTP_HOST'],"pmddealer") !== false) {
    header("Location: http://". $_SERVER['HTTP_HOST'] ."/ops/down.php");
    exit(0);
}
@include("config.inc.php");
$link = mysql_connect($databasehost, $databaseuser, $databasepass);
mysql_select_db($databasename);

if (!$link)
	header( "Location: down.php" );
else {
	if ($MoS_enabled) header( "Location: MoS/MoS_login.php" );
	else header( "Location: login.php" );
}
?>
