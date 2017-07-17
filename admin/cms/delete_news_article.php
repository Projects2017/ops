<?php
require("../database.php");
require("../secure.php");

if (!empty($_REQUEST['id'])){
    
	$sql = "UPDATE cms_news_articles set deleted = 1  WHERE cms_news_article_id=".$_REQUEST['id'].";";

	mysql_query($sql);
	# save content blocks

	Header("location: news.php?msg=Article Deleted");
} 