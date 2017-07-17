<?php
require("../database.php");
require("../secure.php");

if ($_POST){
	# save the page

	$strPhotoSQL = "";

	$r = rand(1,1000000000);
	if(isset($_FILES['filename']) && !empty($_FILES['filename']['name'])) {
		$uploadfile = $_SERVER['DOCUMENT_ROOT']."/uploads/resources/".$r."_".basename($_FILES['filename']['name']);
		if (move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfile)) {
		   $strPhotoSQL = ",filename='".$r."_".basename($_FILES['filename']['name'])."'";
		} 
	}

	if (!empty($_POST['cms_news_article_id'])){
		# update
		$sql = "UPDATE cms_news_articles set title='".$_POST['title']."',description='".$_POST['description']."',publish_date='".$_POST['publish_date']."',content='".$_POST['content']."' WHERE cms_news_article_id=".$_POST['cms_news_article_id'].";";
	} else {
		$sql = "INSERT INTO cms_news_articles set deleted=0, title='".$_POST['title']."',description='".$_POST['description']."',publish_date='".date('Y-m-d H:i:s')."',content='".$_POST['content']."';";
	}
	mysql_query($sql);

	Header("location: news.php");
} else {

?>
<?php require("menu.php");  ?>
<script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

<h1>Edit News Article</h1>

<?php
if (!empty($_REQUEST['cms_news_article_id'])){
	$sql = "select * from cms_news_articles WHERE cms_news_article_id=".$_REQUEST['cms_news_article_id'];
	$query = mysql_query($sql);
	checkDBError();
	$row = mysql_fetch_array($query);
}

?>

	<form action="" method="POST" id="frmMain" enctype="multipart/form-data">
		<input type="hidden" name="cms_news_article_id" value="<?php=$row['cms_news_article_id']?>"/>

	<table border="0" cellspacing="5" cellpadding="0" width="90%">

	<tr valign="top">
		<td class="fat_black_12">Article Title</td>
		<td class="text_12"><input style="width:100%;" type="text" name="title" id="title" value="<?php=$row['title']?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Article Description</td>
		<td class="text_12"><input style="width:100%;" type="text" name="description" id="description" value="<?php=$row['description']?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Article Content</td>
		<td class="text_12"><textarea name="content" style="height:90px;"><?php=$row['content']?></textarea><br></td>
	</tr>
<!--
	<tr valign="top">
		<td class="fat_black_12">Publish Date</td>
		<td class="text_12"><input style="width:100%;" type="text" name="publish_date" value="<?php=$row['publish_date']?>"><br></td>
	</tr>
-->
	<tr>
		<td>&nbsp;</td>
		<td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Event"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
	</tr>
	</table>

	</form>
	
<?php
}