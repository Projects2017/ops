<?php
/* downloadxml.php

This page will present the user with a list of the ZIP files on the server.
If selected and submitted, that ZIP file will be downloaded to the user's computer.

First, bring in the required PHP files for db access. */
require('database.php');
require('secure.php');
require('menu.php');
require('xmlmenu.php');
if(!secure_is_superadmin()) die("Unauthorized user. Permission denied.");
// Lastly, bring in the main XML PHP file.
require('xml.php');

// Show the main page header
displayHeader("Download", "Download ZIP'd File");

// Get the ZIP files on the server
$target_folder = opendir($xmldir);
while(false !== ($target_file = readdir($target_folder))) {
	if(substr($target_file, -4) == ".zip") {
		$file_array[] = basename($target_file);
	}
}
echo '<form action="do_downloadxml.php" method="post" name="chooseZIP" id="chooseZIP"><p>Choose the ZIP file to download:'."</p>\n<p>";
foreach($file_array as $zipfile) {
	echo '<label><input type="radio" id="'.$zipfile.'" name="'.$zipfile.'" value="'.$zipfile.'">'.$zipfile.'</label><br />'."\n";
	}
echo '<p><input type="submit" name="submit" value="Download XML records" />&nbsp;&nbsp;&nbsp;&nbsp;<input name="reset" type="reset" value="Reset Form" />
  </p></form>';
?>
</body>
</html>
