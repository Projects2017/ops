<?php
/* dl_fieldvisit_attach.php

Downloads a selected file

Import required security files */
require('database.php');
require('secure.php');
require('admin/archive.php'); // class for zipping files

// If the user didn't get here through regular channels, bail out

//print_r($_POST);

if($_POST['go'] != "Download File(s)") {
	require('menu.php');
	echo "There was a problem. Please try again.";
}

foreach($_POST as $var => $val) {
	if($var <> "go" && $var <> "id")
	{
		if($val=="allfiles")
		{
			// get the name of the dealer from the fieldvisit record to name the zip file properly
			$sql = "SELECT last_name FROM users WHERE ID IN (SELECT dealer_id FROM fieldvisit WHERE visit_id = {$_POST['id']})";
			checkdberror($sql);
			$que = mysql_query($sql);
			$res = mysql_fetch_assoc($que);
			$dealername = $res['last_name'];
			// now get the date of the visit
			$sql = "SELECT field_visit_date FROM fieldvisit WHERE visit_id = {$_POST['id']}";
			checkdberror($sql);
			$que = mysql_query($sql);
			$res = mysql_fetch_assoc($que);
			$visitdate = date('m-d-Y', strtotime($res['field_visit_date']));
			// zip the files in the folder
			$targetfile = "$dealername $visitdate Field Visit.zip";
			$zipfile = new zip_file($targetfile);
			$zipfile->set_options(array('basedir' => $_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$_POST['id'].'/', 'storepaths'=> 0, 'overwrite' => 1));
			$zipfile->add_files("*.*");
			$zipfile->create_archive();
			$type = "application/zip";
		}
		else
		{
			$targetfile = $val;
			//$finfo = finfo_open(FILEINFO_MIME);
			//$type = finfo_file($finfo, $_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$_POST['id'].'/'.$targetfile);
			$type = mime_content_type($_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$_POST['id'].'/'.$targetfile);
		}
		header('Content-type: '.$type);
		header('Content-Disposition: attachment; filename="'.$targetfile.'"');
		readfile($_SERVER['DOCUMENT_ROOT'].'/doc/manager/visit'.$_POST['id'].'/'.$targetfile);
	}
}
?>