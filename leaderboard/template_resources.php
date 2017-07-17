<?php include 'includes/header.php';?>
<?php

$catid = $_REQUEST['crm_resource_category_id'];

?>
<div class="container">
	<div class="page-content maincontent resources">
		<div class="col-md-12"><div class="page-title" style="font-size:16px;"><h1 style="padding:0px;margin:0px !important;">RESOURCES</h1>
		
				Please select from a list of available resources below. Thank you.
				<BR><BR>
</div></div>
	
		<div class="col-md-12">
		
			<div class="content">
			<?php
			if (!empty($_REQUEST['crm_resource_category_id'])){
				$sql = "select cr.title as resource_title,cr.filename,cr.is_page,crc.title as resource_category_title from cms_resources cr LEFT JOIN cms_resource_categories crc USING(cms_resource_category_id) where cr.deleted = 0 AND cms_resource_category_id=".$catid." order by cr.title ";
			} else {
				$sql = "select cr.title as resource_title,cr.filename,cr.is_page,crc.title as resource_category_title from cms_resources cr LEFT JOIN cms_resource_categories crc USING(cms_resource_category_id) where cr.deleted = 0 order by cr.title ";
			}

			$query = mysql_query($sql);
checkDBError();

	$arrCategories = mysql_query("select * from cms_resource_categories WHERE deleted=0");

?>
<style>
td{
	padding: 10px;
}

.fat_black_12{
	text-transform: uppercase;
	font-size: 14px;
	font-weight: bold;
}

</style>

<div style="width:100%;margin-bottom:15px;">
<div class="row">
<div class="col-sm-3" style="max-width:170px;padding-top:5px;">
Show resources in: 
</div>
<div class="col-sm-9">
<select name="cms_resource_category_id" class="form-control" onChange="window.location.href='/leaderboard/resources/'+this.value;">
	<option value="">All Categories</option>
		<?php while ($objCategory = mysql_fetch_array($arrCategories)){?>
		<option value="<?php=$objCategory['cms_resource_category_id']?>"<?php if ($objCategory['cms_resource_category_id']==$catid) echo " selected";?>><?php=$objCategory['title']?></option>
		<?php } ?>
</select>
</div>
</div>
</div>

<table border="0" cellspacing="0" cellpadding="5" align="left" width="100%">
  <tr bgcolor="#dae2ef">
    <td class="fat_black_12">Resource Title</td>
    <td class="fat_black_12">Resource Category</td>
    <td class="fat_black_12">Actions</td>
  </tr>
	
<?php

while ($row = mysql_fetch_array($query)) {
?>
    <tr bgcolor="#FFFFFF">
    <td class="text_12"><?php=$row['resource_title']?></td>
    <td class="text_12"><?php=$row['resource_category_title']?></td>
    <?php if ($row['is_page']): ?>
    <td><a href="index.php?id=<?php=$row['filename']?>">View</a></td>
    <?php else: ?>
    <td><a target="_blank" href="/uploads/resources/<?php=$row['filename']?>">View</a></td>
    <?php endif; ?>
  </tr>
<?php
}
?>

</table>

			</div>
	
		</div>

		<!--<div class="col-md-4 sidebar">
			<div class="widgetbox">
				<div class="cntn">
					<p><img style="margin-left:-25px" src="img/icon_dashboard.jpg" alt="" /></p>
					<div class="line1">Incentive Trip</div>
					<div class="line2">January 9-13, 2017</div>
					<a href="javascript:void(0)" class="btn greenbtn">View Details</a>
				</div>
			</div>
		
			<div class="widgetbox">
				<div class="cntn">
					<p><img src="img/icon_graphs.jpg" alt="" /></p>
					<div class="line1">Thanksgiving Week Contest</div>
					<div class="line2">11/21 - 11/28 on all Nature Sleep sales.</div>
					<a href="javascript:void(0)" class="btn greenbtn">Register Here</a>
				</div>
			</div>
		</div>-->

	</div>
</div>

<?php include 'includes/footer.php';?>
