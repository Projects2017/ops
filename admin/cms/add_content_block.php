<?php
require("../database.php");
require("../secure.php");

if ($_POST){
	$sql = "INSERT INTO cms_page_content set content_block_name='".$_POST['content_block_name']."', content_block_variable='".$_POST['content_block_variable']."', cms_page_id='".$_POST['cms_page_id']."';";
	mysql_query($sql);
	Header("location: edit_page.php?cms_page_id=".$_POST['cms_page_id']);
} else {
?>

<?php require("menu.php");  ?>

<h1>Pages: Add Content Block</h1>

	<form action="" method="POST" id="frmMain">
		<input type="hidden" name="cms_page_id" value="<?php=$_REQUEST['cms_page_id']?>"/>

	<table border="0" cellspacing="5" cellpadding="0" width="90%">
	<tr valign="top">
		<td class="fat_black_12">Content Block Name</td>
		<td class="text_12"><input style="width:100%;" type="text" name="content_block_name" id="content_block_name" value="<?php=$row['content_block_name']?>"><br></td>
	</tr>
	<tr valign="top">
		<td class="fat_black_12">Content Block Variable</td>
		<td class="text_12"><input style="width:100%;" type="text" name="content_block_variable" id="content_block_variable" value="<?php=$row['content_block_variable']?>"><br></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td> <input type="submit" style="background-color:#CA0000;color:white" value="Add Content Block"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
	</tr>
	</table>

	</form>

<?php
}