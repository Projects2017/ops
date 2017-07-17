<?php
/* do_downloadxml.php

Downloads a selected XML ZIP file

Import required security files */
require('database.php');
require('secure.php');

// If the user didn't get here through regular channels, bail out

if($_POST['submit'] != "Download XML records") {
	require('xmlmenu.php');
	echo "There was a problem. Please try again.";
}

foreach($_POST as $var => $val) {
	if($var <> "submit") {
		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename="'.$val.'"');
		readfile($xmldir.$val);
	}
}
?>
