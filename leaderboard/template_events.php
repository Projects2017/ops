<?php include 'includes/header.php';?>

<?php

$sql = "select * FROM cms_events WHERE deleted = 0 order by title";
$query = mysql_query($sql);
checkDBError();

?>

<style>

td{
	padding: 5px;
}

.fat_black_12{
	text-transform: uppercase;
	font-size: 14px;
	font-weight: bold;
}

</style>


<div class="container">
	<div class="page-content maincontent resources">
		<div class="col-md-12"><div class="page-title"><h1>Events</h1></div>
	
			<div class="content">
			
			<div class="row">

			
		<?php

		while ($row = mysql_fetch_array($query)) {
		?>
			<div class="col-sm-4">
			<div style="background:#FFF;padding:10px;border-bottom:1px solid #ccc;">
			<font style="font-size:21px;font-weight:bold;"><?php=$row['title']?></font><br>
			<?php=date("m/d/Y",strtotime($row['start_date']))?> - <?php=date("m/d/Y",strtotime($row['end_date']))?>
			</div>
			<br>
			<img src="/uploads/events/<?php=$row['filename']?>" style="max-width:100%;"/>
			</div>
		<?php
		}
		?>

			</div>


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
