<?php
// This is called not by the user, but by the main Operational Site.
require("MoS_database.php");
require("../inc_content.php");

if (!$_POST['systemkey'] != $admin_pass) {
	die("ERROR\nInvalid System Key");
}

// Process the PO
MoS_processPO($_GET['po'],strtotime($_GET['date']));


?>