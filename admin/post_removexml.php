<?php
/* post_removexml.php

Processes removing the XML ZIP files

Import required security files */
require('database.php');
require('secure.php');
require('menu.php');
require('xmlmenu.php');


// If the user chose to cancel, return to removexml.php

if($_POST['submit']=="No, Cancel") {
	$host = $_SERVER['HTTP_HOST'];
	$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$file = 'removexml.php';
	header("Location: http://$host$uri/$file");
	exit();
}

require('xml.php');
displayHeader("Remove", "File(s) deleted");

foreach($_POST as $var => $val) {
	if($var <> "submit") {
		unlink($xmldir.$val);
		echo '<p>Successfully deleted '.$val.'</p>'."\n";
	}
}
?>
</body>
</html>