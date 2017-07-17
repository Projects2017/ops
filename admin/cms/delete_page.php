<?php
require("../database.php");
require("../secure.php");

if (!empty($_REQUEST['id'])){
    
	$sql = "UPDATE cms_pages set deleted = 1  WHERE cms_page_id=".$_REQUEST['id'].";";

	mysql_query($sql);
	# save content blocks

	Header("location: pages.php?msg=Page Deleted");
} 