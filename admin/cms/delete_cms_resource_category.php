<?php
require("../database.php");
require("../secure.php");

if (!empty($_REQUEST['id'])){
    
	$sql = "UPDATE cms_resource_categories set deleted = 1  WHERE cms_resource_category_id=".$_REQUEST['id'].";";

	mysql_query($sql);
	# save content blocks

	Header("location: resource_categories.php?msg=Resoruce Cagegory Deleted");
} 