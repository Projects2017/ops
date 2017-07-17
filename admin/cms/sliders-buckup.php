<?php
require("../database.php");
require("../secure.php");
?>

<?php require("menu.php");  ?>

<table width="760">
    <tr>
        <td align="left">
            <h1><a href="index.php" style="font-size: 28px">Content Management</a>: Sliders</h1>
        </td>
        <td align="right">
            <a href="edit_slider.php">+ Add New Slider</a>
        </td>
    </tr>
</table>

<?php
$sql = "select * from cms_sliders order by slider_name";
$query = mysql_query($sql);
checkDBError();

?>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="760">
    <tr bgcolor="#CCCC99">
        <td class="fat_black_12">Slider Title</td>
        <td class="fat_black_12">Actions</td>
    </tr>

    <?php

    while ($row = mysql_fetch_array($query)) {
        ?>
        <tr bgcolor="#FFFFFF">
            <td class="text_12"><?=$row['slider_name']?></td>
            <td><a href="edit_slider.php?cms_slider_id=<?=$row['cms_slider_id']?>">Edit</a> | <a href="manage_slides.php?cms_slider_id=<?=$row['cms_slider_id']?>">Manage Slides</a></td>
        </tr>
        <?php
    }
    ?>

</table>