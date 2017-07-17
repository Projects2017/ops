<?php
require("../database.php");
require("../secure.php");

if ($_POST){
    # save the page
    $strMenuText = addslashes($_POST['menu_text']);
    if (!empty($_POST['cms_menu_item_id'])){
        # update
        $sql = "UPDATE cms_menu_items set menu_text='".$strMenuText."',cms_page_id='".$_POST['cms_page_id']."',menu_link='".$_POST['menu_link']."' WHERE cms_menu_item_id=".$_POST['cms_menu_item_id'].";";
    } else {
        $lastOrder = mysql_query("SELECT menu_order from cms_menu_items WHERE depth=0 ORDER BY menu_order DESC LIMIT 1");
        $lastOrder = mysql_fetch_row($lastOrder);
        $newOrder = $lastOrder[0]+1;
        $sql = "INSERT INTO cms_menu_items set depth=0,menu_order=".$newOrder.",menu_text='".$strMenuText."',cms_page_id='".$_POST['cms_page_id']."',menu_link='".$_POST['menu_link']."';";
    }
    mysql_query($sql);

    # save content blocks
    foreach($_POST as $key => $val){
        if (strpos($key,"page_content_") > -1){
            $arrKey = explode("_",$key);
            mysql_query("UPDATE cms_page_content set content_block_content = '".addslashes($val)."' WHERE cms_page_content_id = ".$arrKey[2].";");
        }
    }

    Header("location: menus.php");
} else {
    if ($_REQUEST['a'] == "delete"){
        $sql = "DELETE FROM cms_menu_items WHERE cms_menu_item_id=".$_GET['cms_menu_item_id'].";";
        mysql_query($sql);
        Header("location: menus.php");
        die();
    }

    ?>
    <?php require("menu.php");  ?>
    <script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

    <h1>Menu Item</h1>

    <?php
    if (!empty($_REQUEST['cms_menu_item_id'])){
        $sql = "select * from cms_menu_items WHERE cms_menu_item_id=".$_REQUEST['cms_menu_item_id'];
        $query = mysql_query($sql);
        checkDBError();
        $row = mysql_fetch_array($query);
    }
    ?>


    <form action="" method="POST" id="frmMain">
        <input type="hidden" name="cms_menu_item_id" value="<?=$row['cms_menu_item_id']?>"/>

        <table border="0" cellspacing="5" cellpadding="0" width="90%">
            <tr valign="top">
                <td class="fat_black_12">Menu Text</td>
                <td class="text_12"><input style="width:100%;" type="text" name="menu_text" id="menu_text" value="<?=$row['menu_text']?>"><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Menu Link</td>
                <td class="text_12">


                    <select name="cms_page_id" id="cms_page_id">

                        <optgroup label="OTHER URL">
                            <option value="">External Link</option>
                            <optgroup label="CONTENT PAGES">
                                <?php
                                $arrPages = mysql_query("SELECT * from cms_pages ORDER BY page_title");
                                while ($objPage = mysql_fetch_array($arrPages)){
                                    $strSelected="";
                                    if ($objPage['cms_page_id'] == $row['cms_page_id']) $strSelected = " selected";
                                    echo "<option value='".$objPage['cms_page_id']."'".$strSelected.">".$objPage['page_title']."</option>\n";
                                }
                                ?>
                            </optgroup>
                    </select>
                    <Br>
                    <Br>
                    <div id="external_link"<?php if (!empty($row['cms_page_id'])) echo " style='display:none;'";?>>
                        Enter External Link:

                        <input style="width:100%;" type="text" name="menu_link" id="menu_link" value="<?=$row['menu_link']?>"><br>

                    </div>

                </td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Menu Item"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
            </tr>
        </table>

    </form>


    <script>

      $(function(){
        $("#cms_page_id").change(function(){
          if ($(this).val() == ""){
            $("#external_link").show();
          } else {
            $("#external_link").hide();
          }
        });
      });

    </script>

    <?php
}