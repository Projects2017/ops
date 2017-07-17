<?php
include 'includes/header.php';
require ("../database.php");
require ("../secure.php");
require ("../form.inc.php");
require ("../announce.inc.php");
include ('../include/BigBoard.php');
include ('../include/User.php');

?>
<?php

$qNewsArticles = mysql_query("SELECT * from cms_news_articles WHERE cms_news_article_id = ".$_GET['id']); 
$objNewsArticle = mysql_fetch_assoc($qNewsArticles);

?>
<div class="container">
	<div class="page-content maincontent resources">
		<div class="col-md-12"><div class="page-title" style="font-size:16px;"><h1 style="padding:0px;margin:0px !important;">NEWS</h1></div>
		<Br>
		<b><?php=$objNewsArticle['title']?></b>
		<br>
		<?php=date('m/d/Y h:iA',strtotime($objNewsArticle['publish_date']))?>
		<Br><br>
		<?php=$objNewsArticle['content']?>

		</div>

		
		</div>
		
		
<?php include 'includes/footer.php';?>
