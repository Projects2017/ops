<?php
require("../database.php");
require("../secure.php");

if ($_POST){
	# save the page
	if (!empty($_POST['cms_resource_category_id'])){
		# update
		$sql = "UPDATE cms_resource_categories set title='".$_POST['title']."' WHERE cms_resource_category_id=".$_POST['cms_resource_category_id'].";";
	} else {
		$sql = "INSERT INTO cms_resource_categories set title='".$_POST['title']."';";
	}
	mysql_query($sql);

	Header("location: resource_categories.php");
} else {

?>
<?php require("menu.php");  ?>
<script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

<h1>Edit Resource Category</h1>

<?php
if (!empty($_REQUEST['cms_resource_category_id'])){
	$sql = "select * from cms_resource_categories WHERE cms_resource_category_id=".$_REQUEST['cms_resource_category_id'];
	$query = mysql_query($sql);
	checkDBError();
	$row = mysql_fetch_array($query);
}

	$arrCategories = mysql_query("select * from cms_resource_categories");
?>

	<form action="" method="POST" id="frmMain" enctype="multipart/form-data">
		<input type="hidden" name="cms_resource_category_id" value="<?php=$row['cms_resource_category_id']?>"/>

	<table border="0" cellspacing="5" cellpadding="0" width="90%">

	<tr valign="top">
		<td class="fat_black_12">Resource Category Name</td>
		<td class="text_12"><input style="width:100%;" type="text" name="title" id="title" value="<?php=$row['title']?>"><br></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Resource Category"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
	</tr>
	</table>

	</form>
	
<?php
}