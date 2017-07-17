<?php
require("../database.php");
require("../secure.php");

if ($_POST){
    # save the page

    $strPhotoSQL = "";

    $r = rand(1,1000000000);
    if(isset($_FILES['filename']) && !empty($_FILES['filename']['name'])) {
        $uploadfile = $_SERVER['DOCUMENT_ROOT']."/uploads/incentive_trip/".$r."_".basename($_FILES['filename']['name']);
        if (move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfile)) {
            $sql = "UPDATE cms_options set option_value='".$r."_".basename($_FILES['filename']['name'])."' WHERE option_name='incentive_trip_image_path'";
            mysql_query($sql);
        }
    }

    $sql = "UPDATE cms_options set option_value='".$_POST['incentive_trip_total_to_display']."' WHERE option_name='incentive_trip_total_to_display'";
    mysql_query($sql);
    $sql = "UPDATE cms_options set option_value='".date("Y-m-d",strtotime($_POST['incentive_trip_countdown_date']))."' WHERE option_name='incentive_trip_countdown_date'";
    mysql_query($sql);
    $sql = "UPDATE cms_options set option_value='".date("Y-m-d",strtotime($_POST['incentive_trip_initial_sales_date']))."' WHERE option_name='incentive_trip_initial_sales_date'";
    mysql_query($sql);
    $sql = "UPDATE cms_options set option_value='".$_POST['incentive_trip_line_1']."' WHERE option_name='incentive_trip_line_1'";
    mysql_query($sql);
    $sql = "UPDATE cms_options set option_value='".$_POST['incentive_trip_line_2']."' WHERE option_name='incentive_trip_line_2'";
    mysql_query($sql);
    $sql = "UPDATE cms_options set option_value='".$_POST['incentive_trip_line_3']."' WHERE option_name='incentive_trip_line_3'";
    mysql_query($sql);
    $sql = "UPDATE cms_options set option_value='".$_POST['incentive_trip_sales_floor']."' WHERE option_name='incentive_trip_sales_floor'";
    mysql_query($sql);

    Header("location: /admin/cms");
} else {

    ?>
    <?php require("menu.php");  ?>
    <script src="//cdn.ckeditor.com/4.5.10/standard/ckeditor.js"></script>

    <h1>Incentive Trip Options</h1>

    <?php
    if (!empty($_REQUEST['cms_resource_id'])){
        $sql = "select * from cms_resources WHERE cms_resource_id=".$_REQUEST['cms_resource_id'];
        $query = mysql_query($sql);
        checkDBError();
        $row = mysql_fetch_array($query);
    }

    $totalUsers = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_total_to_display'"));
    $totalUsers = $totalUsers['option_value'];
    $imagePath = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_image_path'"));
    $imagePath = $totalUsers['option_value'];
    $incentive_trip_countdown_date = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_countdown_date'"));
    $incentive_trip_countdown_date = $incentive_trip_countdown_date['option_value'];
    $incentive_trip_initial_sales_date = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_initial_sales_date'"));
    $incentive_trip_initial_sales_date = $incentive_trip_initial_sales_date['option_value'];
    $incentive_trip_line_1 = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_line_1'"));
    $incentive_trip_line_1 = $incentive_trip_line_1['option_value'];
    $incentive_trip_line_2 = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_line_2'"));
    $incentive_trip_line_2 = $incentive_trip_line_2['option_value'];
    $incentive_trip_line_3 = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_line_3'"));
    $incentive_trip_line_3 = $incentive_trip_line_3['option_value'];
    $incentive_trip_sales_floor = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_sales_floor'"));
    $incentive_trip_sales_floor = $incentive_trip_sales_floor['option_value'];
    ?>

    <form action="" method="POST" id="frmMain" enctype="multipart/form-data">
        <input type="hidden" name="cms_resource_id" value="<?=$row['cms_resource_id']?>"/>

        <table border="0" cellspacing="5" cellpadding="0" width="90%">

            <tr valign="top">
                <td class="fat_black_12">Total Users to Display</td>
                <td class="text_12">
                    <select name="incentive_trip_total_to_display">
                        <option value="30"<?php if ($totalUsers == 30) echo ' selected';?>>30</option>
                        <option value="60"<?php if ($totalUsers == 60) echo ' selected';?>>60</option>
                        <option value="90"<?php if ($totalUsers == 90) echo ' selected';?>>90</option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Countdown Date</td>
                <td class="text_12"><input style="width:100%;" type="text" name="incentive_trip_countdown_date" id="incentive_trip_countdown_date" value="<?=date("m/d/Y",strtotime($incentive_trip_countdown_date))?>" class="datepicker"/><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Starting Date for Purchases</td>
                <td class="text_12"><input style="width:100%;" type="text" name="incentive_trip_initial_sales_date" id="incentive_trip_initial_sales_date" value="<?=date("m/d/Y",strtotime($incentive_trip_initial_sales_date))?>" class="datepicker"/><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Sales Floor for Purchases</td>
                <td class="text_12"><input style="width:100%;" type="text" name="incentive_trip_sales_floor" id="incentive_trip_sales_floor" value="<?=$incentive_trip_sales_floor?>"/><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Trip Description Line 1</td>
                <td class="text_12"><input style="width:100%;" type="text" name="incentive_trip_line_1" id="incentive_trip_line_1" value="<?=$incentive_trip_line_1?>"/><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Trip Description Line 2</td>
                <td class="text_12"><input style="width:100%;" type="text" name="incentive_trip_line_2" id="incentive_trip_line_2" value="<?=$incentive_trip_line_2?>"/><br></td>
            </tr>

            <tr valign="top">
                <td class="fat_black_12">Trip Description Line 3</td>
                <td class="text_12"><input style="width:100%;" type="text" name="incentive_trip_line_3" id="incentive_trip_line_3" value="<?=$incentive_trip_line_3?>"/><br></td>
            </tr>


            <tr valign="top">
                <td class="fat_black_12">Header Image</td>
                <td class="text_12"><input style="width:100%;" type="file" name="filename" id="filename"/><br></td>
            </tr>


            <tr>
                <td>&nbsp;</td>
                <td> <input type="submit" style="background-color:#CA0000;color:white" value="Save Options"> <input type="button" style="background-color:#444;color:white" value="Cancel" onClick="javascript:history.back();"></td>
            </tr>
        </table>

    </form>

    <?php
}