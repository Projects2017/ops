<?php
/* do_removexml.php

This script verifies from the user whether they want to remove the selected ZIP files.
If so, the files are immediately deleted.

First, bring in the required security scripts. */
require('database.php');
require('secure.php');
require('menu.php');
require('xmlmenu.php');
// Also bring in any XML functions
require('xml.php');

// Bring in the page header
displayHeader("Remove", "Verify removing file(s)");

// $targetFile[] = array of file(s) to delete; $i = counter
$i = 0;
// Set up warning blurb
echo '<form action="post_removexml.php" method="post" name="verifyZIP" id="verifyZIP"><p>You are asking to ';
if($_POST['download'] == "true") {
	echo 'download and delete ';
} else {
	echo 'delete ';
}

foreach($_POST as $var => $val) {
	if($var <> 'submit') { $targetFile[] = $val; }
}
if(count($targetFile) > 1) { echo 'these files:</p>'."\n<p>"; } else { echo 'this file:</p>'."\n<p>"; }
foreach($targetFile as $fileName) {
	$i++;
	echo $fileName.'<br /><input type="hidden" name="file'.$i.'" value="'.$fileName.'" />'."\n";
}
echo '<p>Are you sure?</p>'."\n";
echo '<input type="submit" name="submit" value="Yes, Delete" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="No, Cancel" />';
?>
</body>
</html>