<?php
require("../database.php");
require("../secure.php");


?>

<?php require("menu.php");  ?>


<table width="760">
	<tr>
		<td align="left">
		<h1><a href="index.php" style="font-size: 28px">Content Management</a>: News</h1>
		</td>
		<td align="right">
		<a href="edit_article.php">+ Add News Article</a>
		</td>
	</tr>
</table>
<?php
if(!empty($_REQUEST['msg'])) {
    echo "<div style='width:100%; background-color:#d9edf7; padding:5px;'><h2>".$_REQUEST['msg']."</h2></div><br style='clear:both;'/>";
}
 
?>
<?php
$sql = "select * from cms_news_articles where deleted = 0 order by title ";
$query = mysql_query($sql);
checkDBError();
?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Article Title</td>
    <td class="fat_black_12">Publish Date</td>
    <td class="fat_black_12">Actions</td>
  </tr>
	
<?php

while ($row = mysql_fetch_array($query)) {
?>
    <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php=$row['title']?></td>
    <td class="text_12"><?php=date("m/d/Y h:iA",strtotime($row['publish_date']))?></td>
    <td><a href="edit_article.php?cms_news_article_id=<?php=$row['cms_news_article_id']?>">Edit</a> &nbsp;|&nbsp; <a href="javascript:confirmDelete(<?php=$row['cms_news_article_id']?>)">Delete</a></td>
  </tr>
<?php
}
?>

</table>
<script>
function confirmDelete(id){
	var r = confirm("Confirm News Article Delete");
	if (r == true) {
		document.location = "delete_news_article.php?id=" + id;
	} 
}

</script>