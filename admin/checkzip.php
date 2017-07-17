<?php
require('database.php');
require('secure.php');
require('archive.php');
require('menu.php');
require('xmlmenu.php');
require('xml.php');

// Set POST'd variables as PHP variables

if(!$_POST['submit']) {
	displayHeader("Import", "Choose ZIP file(s)");
	echo '<p>You need to choose a ZIP file</p>'."\n";
	displayForm();
} else {
	$type = $_POST['chooseType'];
	if($type == 'server') {
		$target_folder = opendir($xmldir);
		while(false !== ($target_file = readdir($target_folder))) {
			if(substr($target_file, -4) == ".zip") {
				$file_array[] = basename($target_file);
			}
		}
	} else {
		if(substr($_FILES['upload_file']['name'], -4) != ".zip" && substr($_FILES['upload_file']['name'], -4) != ".xml") {
			displayHeader("Import", "Choose file(s)");
			echo "<p>Uploaded file is not an XML or ZIP file. Please choose an XML or ZIP file to upload.</p>";
			displayForm();
			exit();
		}
		$upload_filename = $xmldir.$_FILES['upload_file']['name'];
		move_uploaded_file($_FILES['upload_file']['tmp_name'], $upload_filename);
		$file_array[] = basename($upload_filename);
	}
	displayZIPs($file_array, $basedir);
}
?>
</body>
</html>
