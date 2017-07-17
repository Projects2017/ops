<?php

$week_start  = date("Y-m-d",strtotime("-7 days", strtotime("last sunday")));
$week_end    = date("Y-m-d",strtotime("-8 days", strtotime("this sunday")));

$month_start    = date("Y-m-1");
$month_end      = date("Y-m-31");

if (empty($_REQUEST['start_date'])){
 	$_REQUEST['start_date'] = date("2016-05-01");
} else {
 	$_REQUEST['start_date'] = date("m/d/Y",strtotime($_REQUEST['start_date']));
}

if (empty($_REQUEST['end_date'])){
 	$_REQUEST['end_date'] = date("m/d/Y");
} else {
 	$_REQUEST['end_date'] = date("m/d/Y",strtotime($_REQUEST['end_date']));
}

$month_clean = date("M")." 1 - ".date('j',strtotime("last day of month"));
$week_clean  = date("M j",strtotime($week_start))." - ".date("M j",strtotime($week_end));

/*
$week_start     = '2016-2-1';
$week_end       = '2016-2-7';
$month_start    = '2016-2-1';
$month_end      = '2016-2-29';
*/

$manager = '';
if(isset($_REQUEST['manager']))
    $manager = $_REQUEST['manager'];

$mattress_week      = BigBoard::getLeaders($week_start, $week_end, "Bedding",20,$manager);
$mattress_month     = BigBoard::getLeaders($month_start, $month_end, "Bedding",25,$manager);
$furniture_week     = BigBoard::getLeaders($week_start, $week_end, "Furniture",10,$manager);
$furniture_month    = BigBoard::getLeaders($month_start, $month_end, "Furniture",10,$manager);
$all                = BigBoard::getAllOrders(date("Y-m-d",strtotime($_REQUEST['start_date'])),date("Y-m-d",strtotime($_REQUEST['end_date'])));

function getSlides($intSliderID){
	return mysql_query("SELECT * from cms_slider_slides where cms_slider_id = ".$intSliderID." ORDER BY display_order");
}

function getSliderDelay($intSliderID){
	$sliderDelay = mysql_query("SELECT slide_delay from cms_sliders where cms_slider_id = ".$intSliderID);
	$sliderDelay = mysql_fetch_array($sliderDelay);
	$sliderDelay = $sliderDelay['slide_delay'] * 1000;
	return $sliderDelay;
}

$user = User::getUserFromLogin($_COOKIE['pmd_session_id']);


?>

<link rel="stylesheet" type="text/css" href="/css/unslider-dots.css">
<link rel="stylesheet" type="text/css" href="/css/unslider.css">

<div style="width:410px;height:410px;overflow:scroll">

<div class='content_container' style="width:400px!important;">

	<br style='clear:both;'/>
	<div style="float:left;">
		<h2 class="font_2" style="font-size: 20px;"><span style="font-size:20px;"><span style="text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.498), -1px 1px 0px rgba(0, 0, 0, 0.498), 1px 1px 0px rgba(0, 0, 0, 0.498), 1px -1px 0px rgba(0, 0, 0, 0.498);"><span style="font-weight: bold;"><span class="color_11">&nbsp;Wholesale Purchases - All Orders</span></span></span></span></h2>
	</div>

	<br style='clear:both;'/>
	<br style='clear:both;'/>


			<div
				style=" width: 400px; height: 9px; "
				class="s10" id="idstchev">
				<div id="idstchevline" class="s10line"></div>
			</div>

	<br style='clear:both;'/>
	
		<div style="background:#FFF;width:100%;">

<div id="tableWrapper" style="overflow: auto">
	<table id="theTable" class="footable table outerBorder footable-loaded" style="width: 100%; margin: 7px 0px;">
		<thead>
			<tr>
				<th id="thCol0" data-type="numeric" colspan=3">
				&nbsp;<input type="text" value="<?php=$_REQUEST['start_date']?>" id="start_date_all_popup" name="start_date" class="datepicker" style="width:70px;border-radius:3px;border:1px solid #FFF;"/> &nbsp;to&nbsp; <input value="<?php=$_REQUEST['end_date']?>" type="text" id="end_date_all_popup" name="end_date_" class="datepicker" style="width:70px;border-radius:3px;border:1px solid #FFF;"/> <input rel="matresses_all" type="button" value="GO" class="submit_all_orders"/>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr style="background:#dddddd">
				<td style="padding:5px;">&nbsp;</td>
				<td align="left">Name</td>
				<td align="left">Total</td>
			</tr>

		<?php for($x=0;$x<count($all);$x++) {
			if ($all[$x]['total'] > 0){
				if (!empty($all[$x]['big_board_name'])){
					$strDisplayName = $all[$x]['big_board_name'];
				} else {
					$strDisplayName = $all[$x]['last_name'];
				}
		?>
    		<tr class="<?php=(($x%2==0)?'footableOdd':'footableEven');?>">
    			<td><div style="width:32px;height:32px;  margin-bottom:2px;  display: block; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);    background-color: rgba(255, 255, 255, 1); border: 2px solid rgba(255, 255, 255, 1);  overflow: hidden;"><?php if($all[$x]['photo'] != '') echo "<img src='/images/users/".$all[$x]['photo']."' width='32' height='32'>"; else echo "<img src='images/default.jpg'/>";?></div></td>

    			<td   style=' vertical-align: middle;'><?php=$strDisplayName?></td>
    			<td  style=' vertical-align: middle;'>$<?php=number_format($all[$x]['total'])?></td>
    		</tr>
    	<?php  }
    	} ?>
    
    </tbody>
    </table>
		
		</div>

	<br style='clear:both;'/>
</div>

</div>

<style>

#ui-datepicker-div{
	z-index: 1000000 !important;
}

</style>

<script>

$(function(){
	$(".datepicker").datepicker();
});
</script>