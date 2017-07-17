<?php
require("../database.php");
require("../secure.php");

#added cms_resources and cms_resource_categories tables to DB

?>

<?php require("menu.php");  ?>


<table width="760">
    <tr>
        <td align="left">
            <h1><a href="index.php" style="font-size: 28px">Content Management</a>: Resources</h1>
        </td>
        <td align="right">
            <a href="edit_resource.php">+ Add New Resource</a>
        </td>
    </tr>
</table>
<?php
if(!empty($_REQUEST['msg'])) {
    echo "<div style='width:100%; background-color:#d9edf7; padding:5px;'><h2>".$_REQUEST['msg']."</h2></div><br style='clear:both;'/>";
}

?>
<?php
$sql = "select * from cms_resources where deleted = 0 order by title ";
$query = mysql_query($sql);
checkDBError();
?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
    <tr bgcolor="#CCCC99">
        <td class="fat_black_12">Resource Title</td>
        <td class="fat_black_12">File Name</td>
        <td class="fat_black_12">Actions</td>
    </tr>

    <?php

    while ($row = mysql_fetch_array($query)) {
        ?>
        <tr bgcolor="#FFFFFF">
            <td class="text_12"><?=$row['title']?></td>
            <td class="text_12"><?=$row['filename']?></td>
            <td><a href="edit_resource.php?cms_resource_id=<?=$row['cms_resource_id']?>">Edit</a> &nbsp;|&nbsp; <a href="javascript:confirmDelete(<?=$row['cms_resource_id']?>)">Delete</a></td>
        </tr>
        <?php
    }
    ?>

</table>
<script>
  function confirmDelete(id){
    var r = confirm("Confirm Page Delete");
    if (r == true) {
      document.location = "delete_resource.php?id=" + id;
    }
  }

</script>