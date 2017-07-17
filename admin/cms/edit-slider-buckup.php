<?php
require("../database.php");
require("../secure.php");

if ($_POST){
    # save the page

    if (!empty($_POST['cms_slider_id'])){
        # update
        $sql = "UPDATE cms_sliders set slider_name='".$_POST['slider_name']."' WHERE cms_slider_id=".$_POST['cms_slider_id'].";";
    } else {
        $sql = "INSERT INTO cms_sliders set slider_name='".$_POST['slider_name']."';";
    }
    mysql_query($sql);

    Header("location: sliders.php");
} else {

    ?>
    <?php require("menu.php");  ?>
    <script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

    <h1>Edit Slider</h1>

    <?php
    if (!empty($_REQUEST['cms_slider_id'])){
        $sql = "select * from cms_sliders WHERE cms_slider_id=".$_REQUEST['cms_slider_id'];
        $query = mysql_query($sql);
        checkDBError();
        $row = mysql_fetch_array($query);
    }
    ?>

    <form action="" method="POST" id="frmMain">
        <input type="hidden" name="cms_slider_id" value="<?=$row['cms_slider_id']?>"/>

        <table border="0" cellspacing="5" cellpadding="0" width="90%">
            <tr valign="top">
                <td class="fat_black_12">Slider Name</td>
                <td class="text_12"><input style="width:100%;" type="text" name="slider_name" id="slider_name" value="<?=$row['slider_name']?>"><br></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Slider"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
            </tr>
        </table>

    </form>

    <?php
}