<?php
require("../database.php");
require("../secure.php");
?>

<?php require("menu.php");  ?>

<table width="760">
	<tr>
		<td align="left">
		<h1><a href="index.php" style="font-size: 28px">Content Management</a>: <a style="font-size: 28px" href="sliders.php">Sliders</a>: Slides</h1>
		</td>
		<td align="right">
		<a href="edit_slide.php?cms_slider_id=<?php=$_REQUEST['cms_slider_id']?>">+ Add New Slide</a>
		</td>
	</tr>
</table>

<?php
$sql = "select * from cms_slider_slides WHERE cms_slider_id=".$_REQUEST['cms_slider_id']." order by display_order";
$query = mysql_query($sql);
checkDBError();

?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
  <tr bgcolor="#CCCC99">
    <td class="fat_black_12">Slide Title</td>
    <td class="fat_black_12">Duration</td>
    <td class="fat_black_12">Actions</td>
  </tr>
	
<?php

while ($row = mysql_fetch_array($query)) {
?>
    <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php=$row['title']?></td>
    <td class="text_12"><?php=$row['duration']?> sec</td>
    <td><a href="edit_slide.php?cms_slider_slide_id=<?php=$row['cms_slider_slide_id']?>">Edit</a> | <a href="edit_slide.php?a=delete&cms_slider_slide_id=<?php=$row['cms_slider_slide_id']?>&cms_slider_id=<?php=$row['cms_slider_id']?>">Delete</a></td>
  </tr>
<?php
}
?>

</table>