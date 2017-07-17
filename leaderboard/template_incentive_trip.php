<?php include 'includes/header.php';?>
<?php
$totalUsers = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_total_to_display'"));

if (sizeof($totalUsers)>0 && !empty($totalUsers['option_value'])) { 
	$totalUsers = $totalUsers['option_value'];
} else {
	$totalUsers = 60;
}

$imagePath = mysql_fetch_array(mysql_query("select option_value from cms_options WHERE option_name='incentive_trip_image_path'"));
$imagePath = $imagePath['option_value'];
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
$arrAllTimeSales = BigBoard::allTimeSales($totalUsers,$incentive_trip_initial_sales_date,$incentive_trip_sales_floor);
$totalResults = sizeof($arrAllTimeSales);
?>
<style>

.profilePhotoCrop {
    width: 70px;
    height: 70px;
    position: relative;
    overflow: hidden;
    border-radius: 50% !important;
    border: 1px solid #777;
}

.smallProfilePhotoCrop {
    width: 39px;
    height: 39px;
    position: relative;
    overflow: hidden;
    border-radius: 50% !important;
    margin-left: 8px;
    border: 1px solid #ccc;
}

img.profilePhoto {
    display: inline;
    margin: 0 auto 0;
    height: 100% !important;
    width: 100% !important;
}

img.smallProfilePhoto {
    display: inline;
    margin: 0 auto 0;
    height: 100% !important;
    width: 100% !important;
}

.col{
	padding-left: 5px !important;
	padding-right: 5px !important;
}

.widgetbox{
}

.incentiveMemberRow{
	border-top: 1px solid #ddd;
	padding-left: 0px !important;
	padding-right: 0px !important;
	padding-top: 4px !important;
	padding-bottom: 4px !important;
	margin-left: 15px;
	margin-right: 15px;
	font-size: 14px;
}

</style>

<div class="container">
	<div class="page-content maincontent resources">
		<div class="col-md-12"><div class="page-title"><h1>Incentive Trip Qualifiers</h1></div>
	
			<div class="content">
							<div class="widgetbox newteam">

								<div class="bigsales">

								<?php if(!empty($incentive_trip_countdown_date) || !empty($imagePath)) { ?>
								
								<div class="row" style="margin-left:0px;margin-right:0px;padding:20px 35px;border-bottom:1px solid #ccc;padding-bottom:20px;margin-bottom:15px;">
								
									<div class="col-sm-<?php if(empty($incentive_trip_countdown_date)) { ?>12<?php } else { ?>8<?php } ?>">
										<img src="/uploads/incentive_trip/<?php=$imagePath?>" style="max-width:100%;"/>
									</div>

									<div class="col-sm-4">

									<?php if(!empty($incentive_trip_countdown_date)) { ?>

										<div class="counter" id="counter">

										<div class="row dayRow" style="background:#333;margin:0px;padding:10px;color:#FFF;font-size:20px;">

											<div class="col-sm-12">
												<i class="fa fa-clock-o"></i> <span id="days">23</span> <span class="daysText">Days</span>, <span id="hours">14</span>:<span id="minutes">21</span>:<span id="seconds">28</span> until...
											</div>

										</div>
										</div>

						<div style="background:#FFF;padding:20px;border:1px solid #eee;">
						
						<b><?php=$incentive_trip_line_1?></b><br>
						<?php=$incentive_trip_line_2?><br>
						<?php=$incentive_trip_line_3?>
						</div>


									<?php } ?>
									</div>

								</div>

								<?php } ?>

								<div class="row" style="padding:20px 35px;">
								
								<div class="col-sm-4">
								
									<?php
									
									$i = 0;

									foreach ($arrAllTimeSales as $allTimeUser ){
									
									
										if (!empty($allTimeUser['big_board_name'])){
											$strDisplayName = $allTimeUser['big_board_name'];
										} else {
											$strDisplayName = $allTimeUser['last_name'];
										}

									?>
									<div class="row incentiveMemberRow">
										<div class="col-sm-2"><div class="smallProfilePhotoCrop"><img alt="" class="smallProfilePhoto" src="<?php=profilePhoto($allTimeUser[photo])?>"/></div></div>
										<div class="col-sm-7" style="padding-top:5px;"><?php=$strDisplayName?></div>
										<div class="col-sm-2" style="text-align:right;padding-top:5px;">$<?php= number_format($allTimeUser[total_sales])?></div>
									</div>
									<?php
										$i++;
									
																			if ($i == ceil($totalResults/3) || $i == ceil(($totalResults/3)*2)){
											echo '</div><div class="col-sm-4">';
}

									} ?>

								</div>
								
								</div>

								</div>
								</div>


<BR><BR><BR>

		</div>
	</div>
</div>



<?php include 'includes/footer.php';?>
<script src="/leaderboard/js/countdown.js"></script>
<script>

$(function(){
	var ending = new Date(Date.parse(new Date('<?php=substr($incentive_trip_countdown_date,0,4)?>','<?php=substr($incentive_trip_countdown_date,5,2)-1?>','<?php=substr($incentive_trip_countdown_date,8,2)?>','<?php=substr($incentive_trip_countdown_date,11,2)?>','<?php=substr($incentive_trip_countdown_date,14,2)?>','<?php=substr($incentive_trip_countdown_date,17,2)?>')));

	var arrCountdown = countdown(ending);
		ttlDays = arrCountdown.days + (arrCountdown.months * 30);
		if (arrCountdown.days < 10) arrCountdown.days = "0" + arrCountdown.days;
		if (arrCountdown.hours < 10) arrCountdown.hours = "0" + arrCountdown.hours;
		if (arrCountdown.minutes < 10) arrCountdown.minutes = "0" + arrCountdown.minutes;
		if (arrCountdown.seconds < 10) arrCountdown.seconds = "0" + arrCountdown.seconds;
		$("#days").html(ttlDays);
	$("#hours").html(arrCountdown.hours);
	$("#minutes").html(arrCountdown.minutes);
	$("#seconds").html(arrCountdown.seconds);

	setInterval(function(){
		arrCountdown = countdown(ending);
		ttlDays = arrCountdown.days + (arrCountdown.months * 30);
		if (arrCountdown.days < 10) arrCountdown.days = "0" + arrCountdown.days;
		if (arrCountdown.hours < 10) arrCountdown.hours = "0" + arrCountdown.hours;
		if (arrCountdown.minutes < 10) arrCountdown.minutes = "0" + arrCountdown.minutes;
		if (arrCountdown.seconds < 10) arrCountdown.seconds = "0" + arrCountdown.seconds;
		$("#days").html(ttlDays);
		$("#hours").html(arrCountdown.hours);
		$("#minutes").html(arrCountdown.minutes);
		$("#seconds").html(arrCountdown.seconds);
	}, 1000);
});

</script>
