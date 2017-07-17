<?php
require("database.php");
require("../inc_content.php");
require("secure.php");
   
if (!is_numeric($po)) {
	die("PO# is not numeric!");
}

if (!$po) {
	die("Need a non-zero PO#");
}
   
if (!secure_is_admin()) {
	if (secure_is_dealer()) {
		if ($userid != viewpo_dealerowner($po)) {
			die("That PO# doesn't belong to you!");
		}
	} else {
		$vid = viewpo_vendorowner($po);
		$sql = 'SELECT vlogin_access.ID FROM vlogin_access WHERE vlogin_access.user='.$vendorid." AND vlogin_access.vendor=".$vid;
		$query = mysql_query($sql);
		if (mysql_num_rows($query) == 0) {
			die('You do not have access to that PO# Sorry');
		}
		mysql_free_result($query);
	}
}
  
?>
<html>
<head>
<title>RSS View PO #<?php=$po?></title>
<link rel="stylesheet" href="../styles.css" type="text/css">
</head>
<body<?php
if($_GET['autoprintclose'] == '1')
{
	// print w/o printer selector, then close
	// just like the option below, but using a different JS print method
	echo ' OnLoad="printPage();window.close();"';
	if(secure_is_superadmin()&&$_GET['erasesummary'] == 1)
	{
		markpoprinted($po);
	}
}
	
if ($_GET['printclose'] == '1') {
	echo ' OnLoad="window.print();window.close();"';
	if (secure_is_superadmin()&&$_GET['erasesummary'] == 1) {
		markpoprinted($po);
	}
}
?>>
<?php require('menu.php'); ?>

<?php
$section = "claims";

$for = 'U';
if (isset($_GET['for']) && $_GET['for'] == 'vendor') {
    $for = 'V';
}

echo OrderForWeb($po,$section, $for);

mysql_close($link);
?>
</body>
</html>
