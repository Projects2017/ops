<?php
require("../database.php");
require("../secure.php");


if ($_POST){
    # save the page
    //$strPageContent = addslashes($_POST['page_content']);
    $strPageContent = $_POST['page_content'];

    $show_title = 0;
    if(isset($_POST['show_title']) && $_POST['show_title'] == 1)
        $show_title = 1;


    if (!empty($_POST['cms_page_id'])){
        # update
        $sql = "UPDATE cms_pages set page_title='".$_POST['page_title']."',template_id='".$_POST['template']."', show_title='".$show_title."', content='".$strPageContent."' WHERE cms_page_id=".$_POST['cms_page_id'].";";
    } else {
        $sql = "INSERT INTO cms_pages (page_title, show_title, content, template_id) values ('".$_POST['page_title']."', '".$show_title."', '".$strPageContent."', '".$_POST['template']."') ;";
    }
    mysql_query($sql);


    Header("location: pages.php?msg=Page Saved");
} else {

    $tmp = mysql_query("select * from cms_templates where deleted = 0 ");
    $templates = array();
    while($row = mysql_fetch_array($tmp)){
        $templates[] = $row;
    }

    $page_title = $show_title = $template_id = $content = '';
    if (!empty($_REQUEST['cms_page_id'])){
        $sql = "select * from cms_pages WHERE cms_page_id=".$_REQUEST['cms_page_id'];
        $query = mysql_query($sql);
        checkDBError();
        $row = mysql_fetch_array($query);

        $page_title = $row['page_title'];
        $show_title = $row['show_title'];
        $template_id = $row['template_id'];
        $content = $row['content'];
    }




    ?>
    <?php require("menu.php");  ?>
    <script src="/js/ckeditor/ckeditor.js"></script>
    <script src="/admin/ckfinder/ckfinder.js"></script>

    <h1>Pages</h1>


    <form action="" method="POST" id="frmMain">
        <?php if (empty($_REQUEST['copy'])){ ?>
            <input type="hidden" name="cms_page_id" value="<?=$row['cms_page_id']?>"/>
        <?php } ?>

        <table border="0" cellspacing="5" cellpadding="0" width="90%">
            <tr valign="top">
                <td class="fat_black_12">Page Title</td>
                <td class="text_12"><input style="width:100%;" type="text" name="page_title" id="page_title" value="<?=$page_title?><?php if (!empty($_REQUEST['copy'])){ ?> (Copy)<?php } ?>"><br><br></td>
            </tr>
            <tr valign="top">
                <td class="fat_black_12">Show Title on Page</td>
                <td class="text_12"><input type='checkbox' value='1' name='show_title' <?php if($show_title == 1) echo 'checked';?>/> Yes <br><br></td>
            </tr>
            <tr valign="top">
                <td class="fat_black_12">Template</td>
                <td class="text_12">
                    <select name='template'>
                        <?php
                        foreach($templates as $template){
                            echo "<option value='".$template['id']."' ";
                            if($template['id'] == $template_id)
                                echo "selected";
                            echo ">".$template['name']."</option>";
                        }
                        ?></select><br><br></td>
            </tr>


            <tr valign="top">
                <td class="fat_black_12">&nbsp;</td>
                <td class="text_12">
                    <textarea style="width:100%;height:350px;" name="page_content" id="page_content"><?=$content?></textarea>
                </td>
            </tr>

            <script>
              CKEDITOR.config.allowedContent = true;
              CKEDITOR.config.height = '500';
              CKEDITOR.replace( 'page_content' );
              CKFinder.setupCKEditor();
            </script>

            <tr>
                <td>&nbsp;</td>
                <td> <br><input type="submit" style="background-color:#CA0000;color:white" value="Save Page"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
            </tr>
        </table>

    </form><br>

    <?php
}