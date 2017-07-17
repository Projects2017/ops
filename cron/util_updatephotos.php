<?php
require('../database.php');
if (!is_dir($basedir.'/photos')) die ('Photos Directory is incorrect');

$sql = "SELECT ID FROM form_items";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	if (file_exists($basedir."photos/".$row['ID'].".jpg")) {
		if (file_exists($basedir."photos/t".$row['ID'].".jpg")) unlink($basedir."photos/t".$row['ID'].".jpg");
		createThumb($row['ID'].".jpg");
		echo $row['ID']." - ";
	}
}
echo "Done!";
