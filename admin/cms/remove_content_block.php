<?php
require("../database.php");
require("../secure.php");

	$sql = "DELETE FROM cms_page_content WHERE cms_page_content_id='".$_REQUEST['cms_page_content_id']."';";
	mysql_query($sql);
	Header("location: edit_page.php?cms_page_id=".$_REQUEST['cms_page_id']);
