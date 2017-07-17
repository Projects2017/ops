<?php
require("../database.php");
require("../secure.php");

if ($_POST){
    # save the page

    if (!empty($_POST['cms_slider_slide_id'])){
        # update
        $sql = "UPDATE cms_slider_slides set title='".$_POST['title']."',duration='".$_POST['duration']."',display_order='".$_POST['display_order']."',image_url='".$_POST['image_url']."',link='".$_POST['link']."' WHERE cms_slider_slide_id=".$_POST['cms_slider_slide_id'].";";
    } else {
        $sql = "INSERT INTO cms_slider_slides set title='".$_POST['title']."',duration='".$_POST['duration']."',display_order='".$_POST['display_order']."',image_url='".$_POST['image_url']."',cms_slider_id='".$_POST['cms_slider_id']."',link='".$_POST['link']."';";
    }
    mysql_query($sql);

    Header("location: manage_slides.php?cms_slider_id=".$_POST['cms_slider_id']);
} else {

    if ($_REQUEST['a'] == "delete"){
        $sql = "DELETE FROM cms_slider_slides WHERE cms_slider_slide_id=".$_GET['cms_slider_slide_id'].";";
        mysql_query($sql);
        Header("location: manage_slides.php?cms_slider_id=".$_GET['cms_slider_id']);
        die();
    }

    ?>
    <?php require("menu.php");  ?>
    <script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>
    <script src="/admin/ckfinder/ckfinder.js"></script>

    <script>
      function openPopup() {
        CKFinder.popup( {
          chooseFiles: true,
          onInit: function( finder ) {
            finder.on( 'files:choose', function( evt ) {
              var file = evt.data.files.first();
//                         alert(file.getUrl());
              document.getElementById( 'image_url' ).value = file.getUrl();
            } );
            finder.on( 'file:choose:resizedImage', function( evt ) {
              document.getElementById( 'image_url' ).value = evt.data.resizedUrl;
            } );
          }
        } );
      }
    </script>

    <h1>Edit Slide</h1>

    <?php
    if (!empty($_REQUEST['cms_slider_slide_id'])){
        $sql = "select * from cms_slider_slides WHERE cms_slider_slide_id=".$_REQUEST['cms_slider_slide_id'];
        $query = mysql_query($sql);
        checkDBError();
        $row = mysql_fetch_array($query);
        if (!empty($row['cms_slider_id'])){
            $intSliderID = $row['cms_slider_id'];
        }
    } else {
        $intSliderID = $_REQUEST['cms_slider_id'];
    }
    ?>

    <form action="/admin/cms/edit_slide.php" method="POST" id="frmMain">
        <input type="hidden" name="cms_slider_slide_id" value="<?=$row['cms_slider_slide_id']?>"/>
        <input type="hidden" name="cms_slider_id" value="<?=$intSliderID?>"/>

        <table border="0" cellspacing="5" cellpadding="0" width="90%">
            <tr valign="top">
                <td class="fat_black_12">Slide Title</td>
                <td class="text_12"><input style="width:100%;" type="text" name="title" id="title" value="<?=$row['title']?>"><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Duration (seconds)</td>
                <td class="text_12"><input style="width:100%;" type="text" name="duration" id="duration" value="<?=$row['duration']?>"><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Display Order</td>
                <td class="text_12"><input style="width:100%;" type="text" name="display_order" id="display_order" value="<?=$row['display_order']?>"><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Image URL</td>
                <td class="text_12">

                    <div class="popup-example" style="width:100%;" >
                        <div style="float:left;"><input type="button" style="background-color:#6699CC;color:white" id="ckfinder-popup" class="btn button-a button-a-background" onClick="openPopup();" value="Select Image..."/></div>
                        <div style="float:left;padding-top:0px;">
                            <input style="width:450px;" type="text" name="image_url" id="image_url" value="<?=$row['image_url']?>">
                        </div>
                        <div style="clear:both;"></div>
                    </div>

                </td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Link</td>
                <td class="text_12"><input style="width:100%;" type="text" name="link" id="link" value="<?=$row['link']?>"><br></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Slide"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
            </tr>
        </table>

    </form>

    <?php
}