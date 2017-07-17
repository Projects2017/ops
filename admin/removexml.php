<?php
/* removexml.php

This page will present the user with a list of the ZIP files on the server.
If selected and submitted, those ZIP files will be deleted.

This is meant to be a nice, clean method of removing the ZIP files.

First, bring in the required PHP files for db access. */

require('database.php');
require('secure.php');
require('menu.php');
require('xmlmenu.php');
if(!secure_is_superadmin()) die("Unauthorized user. Permission denied.");
// Lastly, bring in the main XML PHP file.
require('xml.php');

// Show the main page header
displayHeader("Remove", "Remove ZIP'd File");


// Get the ZIP files on the server
$target_folder = opendir($xmldir);
while(false !== ($target_file = readdir($target_folder))) {
	if(substr($target_file, -4) == ".zip") {
		$file_array[] = basename($target_file);
	}
}
echo '<form action="do_removexml.php" method="post" name="chooseZIP" id="chooseZIP"><p>Choose the ZIP file(s) to remove:'."</p>\n<p>";
foreach($file_array as $zipfile) {
	echo '<label><input type="checkbox" id="'.$zipfile.'" name="'.$zipfile.'" value="'.$zipfile.'"">'.$zipfile.'</label><br />'."\n";
	}
echo '<p><input type="submit" name="submit" value="Delete XML records" />&nbsp;&nbsp;&nbsp;&nbsp;<input name="reset" type="reset" value="Reset Form" />
  </p></form>';
?>
</body>
</html>