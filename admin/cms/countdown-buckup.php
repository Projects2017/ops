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

    if (!empty($_POST['cms_countdown_id'])){
        # update
        $sql = "UPDATE cms_countdown set cms_countdown_datetime='".$_POST['cms_countdown_datetime']."' WHERE cms_countdown_id=".$_POST['cms_countdown_id'];
    } else {
        $sql = "INSERT INTO cms_countdown set cms_countdown_datetime='".$_POST['cms_countdown_datetime']."'";
    }
    mysql_query($sql);

    Header("location: index.php");
} else {

    ?>
    <?php require("menu.php");  ?>
    <script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

    <h1>Edit Countdown</h1>

    <?php
    if (!empty($_REQUEST['id'])){
        $sql = "select * from cms_countdown WHERE cms_countdown_id=".$_REQUEST['id'];
        $query = mysql_query($sql);
        checkDBError();
        $row = mysql_fetch_array($query);

    }

    ?>

    <form action="" method="POST" id="frmMain" enctype="multipart/form-data">
        <input type="hidden" name="cms_countdown_id" value="<?=$row['cms_countdown_id']?>"/>

        <table border="0" cellspacing="5" cellpadding="0" width="90%">

            <tr valign="top">
                <td class="fat_black_12">Countdown Date/Time</td>
                <td class="text_12"><input style="width:100%;" type="text" name="cms_countdown_datetime" id="cms_countdown_datetime" value="<?=$row['cms_countdown_datetime']?>"><br></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Countdown"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
            </tr>
        </table>

    </form>

    <?php
}