<?php include 'includes/header.php';?>

<div class="content_container">&nbsp;
<?php
    if($content['show_title'] == 1) {
?>
    <div style="float: left;">&nbsp;
   	 	<h2 class="font_2" style="font-size: 30px;"><span style="font-size: 30px;"><span style="text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.498), -1px 1px 0px rgba(0, 0, 0, 0.498), 1px 1px 0px rgba(0, 0, 0, 0.498), 1px -1px 0px rgba(0, 0, 0, 0.498);"><span style="font-weight: bold;"><span class="color_11">&nbsp;<?php=$content['page_title']?></span></span></span></span></h2>
    </div>
<?php
    }
?>

<br style="clear: both;" />
<br style="clear: both;" />
<br style="clear: both;" />
<style>
.white_box img { width:320px; height: 245px;}    
</style>
<div class="white_box" style=" width: 980px;  ">&nbsp;

<?php echo $content['content'];?>
<br style='clear:both;'/>
</div>
</div>

<?php include 'includes/footer.php';?>
