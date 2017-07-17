<?php
require("../database.php");
require("../secure.php");

if (!empty($_REQUEST['id'])){
    
	$sql = "UPDATE cms_resources set deleted = 1  WHERE cms_resource_id=".$_REQUEST['id'].";";

	mysql_query($sql);
	# save content blocks

	Header("location: resources.php?msg=Resoruce Deleted");
} 