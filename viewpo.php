<?php
require("database.php");
require_once("inc_content.php");
$duallogin = 1;
require("vendorsecure.php");
require_once("inc_viewpo.php"); // script with additional functions which display OOR & shipping data
if (!$vendorid)
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
<?php if($_GET['autoprint'])
{
	// if we're in autoprint mode, include the JS necessary for the print
	?><script src="shipping/bol.js" language="javascript" type="text/javascript"></script><?php
} ?>
</head>
<body<?php
// as of 12/3/08, added the ability to automatically print, then close the page
if($_GET['autoprint'])
{
	// this is an autoprint run: after the page loads, print and then close
	?> onload="doPrintAndClose();"<?php
}
echo ">";
require('menu.php');
$section = "claims";

echo OrderForWeb($po,$section);

mysql_close($link);
?>
</body>
</html>
