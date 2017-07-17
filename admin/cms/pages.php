<?php
require("../database.php");
require("../secure.php");
?>

<?php require("menu.php");  ?>


<table width="760">
	<tr>
		<td align="left">
		<h1><a href="index.php" style="font-size: 28px">Content Management</a>: Pages</h1>
		</td>
		<td align="right">
		<a href="edit_page.php">+ Add New Page</a>
		</td>
	</tr>
</table>
<?php
if(!empty($_REQUEST['msg'])) {
    echo "<div style='width:100%; background-color:#d9edf7; padding:5px;'><h2>".$_REQUEST['msg']."</h2></div><br style='clear:both;'/>";
}

?>
<?php
$sql = "select * from cms_pages where deleted = 0 order by page_title ";
$query = mysql_query($sql);
checkDBError();
?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Page Title</td>
    <td class="fat_black_12">Actions</td>
  </tr>
	
<?php

while ($row = mysql_fetch_array($query)) {
?>
    <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php=$row['page_title']?></td>
    <td><a href="edit_page.php?cms_page_id=<?php=$row['cms_page_id']?>">Edit</a> &nbsp;|&nbsp; <a href="/leaderboard/index.php?id=<?php=$row['cms_page_id']?>" target="_blank">View</a> &nbsp;|&nbsp; <a href="edit_page.php?cms_page_id=<?php=$row['cms_page_id']?>&copy=1">Copy</a> &nbsp;|&nbsp; <a href="javascript:confirmDelete(<?php=$row['cms_page_id']?>)">Delete</a></td>
  </tr>
<?php
}
?>

</table>
<script>
function confirmDelete(id){
	var r = confirm("Confirm Page Delete");
	if (r == true) {
		document.location = "delete_page.php?id=" + id;
	} 
}

</script>
