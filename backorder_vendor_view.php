<?php
require("database.php");
require("vendorsecure.php");
require("inc_backorder.php");
?>
<html>
<head>
<title>RSS View PO #<?php=$po?></title>
<link rel="stylesheet" href="../styles.css" type="text/css">
</head>
<body>
<?php
require("menu.php");

if (secure_is_vendor()) $type = 'V';
if (secure_is_dealer()) $type = 'D';
if (secure_is_admin()) $type = 'A';
if (secure_is_superadmin()) $type = 'S';
viewbo($_REQUEST['bo'], $type);

if ($_REQUEST['return']) {
	?>
	<br />
	<p align="center">[<a href="<?php= htmlentities($_REQUEST['return']) ?>">Back to Back Order List</a>]</p>
	<?php
}
?>
