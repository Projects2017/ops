<?php
require("../database.php");
require("../secure.php");

if (!empty($_REQUEST['id'])){
    
	$sql = "UPDATE cms_events set deleted = 1  WHERE cms_event_id=".$_REQUEST['id'].";";

	mysql_query($sql);
	# save content blocks

	Header("location: events.php?msg=Event Deleted");
} 