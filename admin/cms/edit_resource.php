<?php
require("../database.php");
require("../secure.php");

if ($_POST){
	# save the page

	if ($_POST['cms_page_id'] == 'file') {
		$strPhotoSQL = "";

		$r = rand(1,1000000000);
		if(isset($_FILES['filename']) && !empty($_FILES['filename']['name'])) {
			$uploadfile = $_SERVER['DOCUMENT_ROOT']."/uploads/resources/".$r."_".basename($_FILES['filename']['name']);
			if (move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfile)) {
			   $strPhotoSQL = ",filename='".$r."_".basename($_FILES['filename']['name'])."'";
			} 
		}
	} else {
		$strPhotoSQL = ",filename='".$_POST['cms_page_id']."',is_page=1";
	}

	if (!empty($_POST['cms_resource_id'])){
		# update
		$sql = "UPDATE cms_resources set cms_resource_category_id='".$_POST['cms_resource_category_id']."',title='".$_POST['title']."'".$strPhotoSQL." WHERE cms_resource_id=".$_POST['cms_resource_id'].";";
	} else {
		$sql = "INSERT INTO cms_resources set cms_resource_category_id='".$_POST['cms_resource_category_id']."',title='".$_POST['title']."'".$strPhotoSQL.";";
	}
	mysql_query($sql);
	checkDberror($sql);

	Header("location: resources.php");
} else {

?>
<?php require("menu.php");  ?>
<script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>
<script src="js/edit_resource.js"></script>

<h1>Edit Resource</h1>

<?php
if (!empty($_REQUEST['cms_resource_id'])){
	$sql = "select * from cms_resources WHERE cms_resource_id=".$_REQUEST['cms_resource_id'];
	$query = mysql_query($sql);
	checkDBError();
	$row = mysql_fetch_array($query);
}

	$arrCategories = mysql_query("select * from cms_resource_categories WHERE deleted=0");
?>

	<form action="" method="POST" id="frmMain" enctype="multipart/form-data">
		<input type="hidden" name="cms_resource_id" value="<?php=$row['cms_resource_id']?>"/>

	<table border="0" cellspacing="5" cellpadding="0" width="90%">

	<tr valign="top">
		<td class="fat_black_12">Resource Name</td>
		<td class="text_12"><input style="width:100%;" type="text" name="title" id="title" value="<?php=$row['title']?>"><br></td>
	</tr>

	<tr valign="top">
		<td class="fat_black_12">Resource Category</td>
		<td class="text_12">
		<select name="cms_resource_category_id">
		<?php while ($objCategory = mysql_fetch_array($arrCategories)){?>
		<option value="<?php=$objCategory['cms_resource_category_id']?>"<?php if ($objCategory['cms_resource_category_id']==$row['cms_resource_category_id']) echo " selected";?>><?php=$objCategory['title']?></option>
		<?php } ?>
		</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="fat_black_12">Resource Link</td>
		<td class="text_12">
			<?php
				$sql = "SELECT * from cms_pages ORDER BY page_title";
				$arrPages = mysql_query($sql);
				checkDBerror($sql); 
			?>
			<select name="cms_page_id" id="cms_page_id">
				<optgroup label="DOWNLOAD">
					<option value="file">Link to File</option>
				</optgroup>
				<optgroup label="CONTENT PAGES">
				<?php
					while ($objPage = mysql_fetch_array($arrPages)) {
						$strSelected="";
						if ($row['is_page'] && ($objPage['cms_page_id'] == $row['filename'])) $strSelected = " selected";
						echo "\t\t\t\t\t<option value='".$objPage['cms_page_id']."'".$strSelected.">".$objPage['page_title']."</option>\n";
					}
				?>
				</optgroup>
			</select>
		</td>
	</tr>

	<tr valign="top" id="resource_link">
		<td class="fat_black_12">Resource File</td>
		<td class="text_12"><input style="width:100%;" type="file" name="filename" id="filename"/><br>(Select to Replace)</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Resource"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
	</tr>
	</table>

	</form>
	
<?php
}
