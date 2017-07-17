
<?php include 'includes/header.php';?>

<?php

$week_start  = date("Y-m-d",strtotime("-7 days", strtotime("last sunday")));
$week_end    = date("Y-m-d",strtotime("-8 days", strtotime("this sunday")));

$month_start    = date("Y-m-1");
$month_end      = date("Y-m-31");

$month_clean = date("M")." 1 - ".date('j',strtotime("last day of month"));
$week_clean  = date("M j",strtotime($week_start))." - ".date("j",strtotime($week_end));

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
$all                = BigBoard::getLeaders($month_start, $month_end, "all",25,$manager);

function getSlides($intSliderID){
	return mysql_query("SELECT * from cms_slider_slides where cms_slider_id = ".$intSliderID." ORDER BY display_order");
}


$user = User::getUserFromLogin($_COOKIE['pmd_session_id']);


?>

<link rel="stylesheet" type="text/css" href="/css/unslider-dots.css">
<link rel="stylesheet" type="text/css" href="/css/unslider.css">

<!-- SLIDER -->
<div style='width: 100%; background-image: url("images/bg_gradient.jpg"); background-size: cover; background-position: left top; background-repeat: no-repeat; height:390px;'>
    <div style='width: 980px; margin:0px auto; padding: 10px 0;'>
	<div class="banner-slider">
		<ul>
			<?php
			$arrSlides = getSlides(1);
			while ($objSlide = mysql_fetch_array($arrSlides)) { ?>
			<li><a href="<?php=$objSlide['link']?>"><img src="<?php=$objSlide['image_url']?>" alt="banner1" style="height:362px;widht:979px;"></a></li>
			<?php } ?>
		</ul>
	</div>
    </div>
</div>
<!-- // SLIDER -->

<div class='content_container' >

	<br style='clear:both;'/>
	<div style="float:left;">
		<h2 class="font_2" style="font-size: 30px;"><span style="font-size:30px;"><span style="text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.498), -1px 1px 0px rgba(0, 0, 0, 0.498), 1px 1px 0px rgba(0, 0, 0, 0.498), 1px -1px 0px rgba(0, 0, 0, 0.498);"><span style="font-weight: bold;"><span class="color_11">&nbsp;Wholesale Purchases</span></span></span></span></h2>
	</div>
	<div style="float:right;">
		<div  style=" width: 160px; height: 33px; float:left;" class="s31" >
    		<select name="manager" size="1" style='margin-top:7px;' onchange="changeSelect(this.value)">
	  		  <option value="">All Regions</option>
	  		 <?php if($user['admin'] == "M" || $user['admin'] == "Y" || $user['admin'] == "S" ) {?>
    	  	  <option value="Joel Bergeson"  <?php if($manager == 'Joel Bergeson') echo 'selected';?>>Joel Bergeson</option>
    	  	  <option value="Brad Loy" <?php if($manager == 'Brad Loy') echo 'selected';?>>Brad Loy</option>
    	  	  <option value="Bob Wert" <?php if($manager == 'Bob Wert') echo 'selected';?>>Bob Wert</option>
    	  	  <option value="Kyle Sherratt"  <?php if($manager == 'Kyle Sherratt') echo 'selected';?>>Kyle Sherratt</option>
    	  	  <option value="George Varn" <?php if($manager == 'George Varn') echo 'selected';?>>George Varn</option>
    	  	  <option value="Theron Wilson" <?php if($manager == 'Theron Wilson') echo 'selected';?>>Theron Wilson</option>

    	  	  <?php } else if($user['manager'] !='') { ?>
				<option value="<?php=$user['manager']?>"  <?php if($manager == $user['manager']) echo 'selected';?>><?php=$user['manager']?></option>
				<?php } ?>
	  	  	</select>
    	</div>
    	<script>
		function changeSelect(val) {
			document.location="index.php?manager=" + val;
		}
    	</script>
    	<div  	style=" width: 160px; height: 33px; float:left;" class="s31" >
    		<a href="index.php?id=7"
    			id="idvvkxmnlink" class="s31link" style='position:relative; text-align: center; padding:10px;'>
    			<span id="idvvkxmnlabel" class="s31label" style="line-height: 33px;">Grand Day Submission</span></a>
    	</div>
	</div>

	<br style='clear:both;'/>
	<br style='clear:both;'/>

	<table>
		<tr>
			<td  style='padding-right:30px; vertical-align:top;'>

<?php  // --------------------------- Matt Week ----------------//?>

											<div
												style="width: 224px; height: 950px; position: relative;"
												class="s17" id="ibwqi8r2">
												<div id="ibwqi8r2bg" class="s17bg"></div>
												<div id="ibwqi8r2inlineContent" class="s17inlineContent">
													<div
														style="top: 31px; bottom:; left: 4px; right:; width: 198px; height: 54px; position: absolute;"
														 title="bd-matt.jpg"
														class="s18" id="ibwqi8r2_0">
														<div class="s18_left s18_shd"></div>
														<div class="s18_right s18_shd"></div>
														<div style="width: 194px; height: 50px;"
															id="ibwqi8r2_0link" class="s18link">
															<div
																style="width: 194px; height: 50px; position: relative;"
																id="ibwqi8r2_0img" class="s18img">
																<div class="s18imgpreloader" id="ibwqi8r2_0imgpreloader"></div>
																<img id="ibwqi8r2_0imgimage" alt=""
																	src="./files/d3fbdd_11609c06fc4f45a3a4df36f63beff7c0.jpg"
																	style="width: 194px; height: 50px; object-fit: cover;">
															</div>
														</div>
													</div>
													<div style="top: 5px; left: 6px; width: 176px; position: absolute;" class="s8" id="ibwqi8r4">
														<h5 class="font_5"
															style="line-height: 1.2em; font-size: 18px;">
															<span style="font-size: 18px;"><font color="#552c00">Mattresses
																	- Week</font></span>
														</h5>
													</div>
													<div style="min-height: 850px; min-width: 219px; visibility: visible; top: 99px; left: 2px; width: 219px; height: 850px; position: absolute;"
														class="s19" id="icmm3ysl">


                                                        <div id="tableWrapper" style="overflow: auto">
                                                        	<table id="theTable" class="footable table outerBorder footable-loaded" style="width: 100%; margin: 0px;">
                                                        		<thead>
                                                        			<tr>
                                                        				<th id="thCol0" data-type="numeric">Wk</th>
                                                        				<th id="thCol1" data-type="alpha"><?php=$week_clean?></th>
                                                        				<th id="thCol2" data-type="alpha"></th>
                                                        			</tr>
                                                        		</thead>
                                                        		<tbody>
                                                        		<?php for($x=0;$x<count($mattress_week);$x++) {
                                                        			if (!empty($mattress_week[$x]['big_board_name'])){
                                                        				$strDisplayName = $mattress_week[$x]['big_board_name'];
                                                        			} else {
                                                        				$strDisplayName = $mattress_week[$x]['last_name'];
                                                        			}
                                                        		?>
                                                            		<tr class="<?php=(($x%2==0)?'footableOdd':'footableEven');?>">
    																	<td><div style="width:32px;height:32px;  margin-bottom:2px;  display: block; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);    background-color: rgba(255, 255, 255, 1); border: 2px solid rgba(255, 255, 255, 1);  overflow: hidden;"><?php if($mattress_week[$x]['photo'] != '') echo "<img src='/images/users/".$mattress_week[$x]['photo']."' width='32' height='32'>"; else echo "<img src='images/default.jpg'/>";?></div></td>
                                                            			<td style=' vertical-align: middle;'><?php=$strDisplayName?></td>
                                                            			<td style=' vertical-align: middle;'>$<?php=number_format($mattress_week[$x]['total'])?></td>
                                                            		</tr>
                                                            	<?php  } ?>


                                                        		</tbody>
                                                        	</table>
                                                        </div>

														<div id="icmm3ysloverlay" class="s19overlay" ></div>
													</div>
													<div
														style="top: -7px; left: 156px; width: 94px; height: 117px; position: absolute; visibility: inherit;"
														title="" class="s5"
														id="icgu08gj">
														<div style="width: 94px; height: 117px;" id="icgu08gjlink"
															class="s5link">
															<div
																style="width: 94px; height: 117px; position: relative;"
																id="icgu08gjimg" class="s5img">
																<div class="s5imgpreloader" id="icgu08gjimgpreloader" ></div>
																<img id="icgu08gjimgimage" alt=""
																	src="./files/a69938_4bd36c487fb44025be02aaea33800993.png"
																	style="width: 94px; height: 117px; object-fit: cover;"
																	>
															</div>
														</div>
													</div>

													<div
														style="top: 0px; left: 178px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu5nsp">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">&nbsp;</h2>

														<h5 class="font_5" style="font-size: 36px;">
															<span style="font-size: 36px;"><span
																style='font-family: arial black, arial-w01-black, arial-w02-black, arial-w10 black, sans-serif;'><span
																	style="font-weight: bold;">20</span></span></span>
														</h5>
													</div>
													<div
														style="top: 5px; left: 157px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu3lp8">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">
															<span style="font-weight: bold;"><span
																style="font-size: 24px;">TOP</span></span>
														</h2>

														<h2 class="font_2">&nbsp;</h2>
													</div>



												</div>
											</div>


			</td>
			<td  style='padding-right:30px; vertical-align:top;'>



<?php  // --------------------------- Matt Month ----------------//?>
											<div
												style="width: 224px; height: 1100px; position: relative;"
												class="s17" id="ibwqi8r2">
												<div id="ibwqi8r2bg" class="s17bg"></div>
												<div id="ibwqi8r2inlineContent" class="s17inlineContent">
													<div
														style="top: 31px; bottom:; left: 4px; right:; width: 198px; height: 54px; position: absolute;"
														 title="bd-matt.jpg"
														class="s18" id="ibwqi8r2_0">
														<div class="s18_left s18_shd"></div>
														<div class="s18_right s18_shd"></div>
														<div style="width: 194px; height: 50px;"
															id="ibwqi8r2_0link" class="s18link">
															<div
																style="width: 194px; height: 50px; position: relative;"
																id="ibwqi8r2_0img" class="s18img">
																<div class="s18imgpreloader" id="ibwqi8r2_0imgpreloader"></div>
																<img id="ibwqi8r2_0imgimage" alt=""
																	src="./files/d3fbdd_11609c06fc4f45a3a4df36f63beff7c0.jpg"
																	style="width: 194px; height: 50px; object-fit: cover;">
															</div>
														</div>
													</div>
													<div style="top: 5px; left: 6px; width: 176px; position: absolute;" class="s8" id="ibwqi8r4">
														<h5 class="font_5"
															style="line-height: 1.2em; font-size: 18px;">
															<span style="font-size: 18px;"><font color="#552c00">Mattresses
																	- Month</font></span>
														</h5>
													</div>
													<div style="min-height: 1050px; min-width: 219px; visibility: visible; top: 99px; left: 2px; width: 219px; height: 850px; position: absolute;"
														class="s19" id="icmm3ysl">


<div id="tableWrapper" style="overflow: auto">
	<table id="theTable" class="footable table outerBorder footable-loaded" style="width: 100%; margin: 0px;">
		<thead>
			<tr>
				<th id="thCol0" data-type="numeric">Month</th>
				<th id="thCol1" data-type="alpha"><?php=$month_clean?></th>
				<th id="thCol2" data-type="alpha"></th>
			</tr>
		</thead>
		<tbody>
		<?php for($x=0;$x<count($mattress_month);$x++) {
				if (!empty($mattress_month[$x]['big_board_name'])){
					$strDisplayName = $mattress_month[$x]['big_board_name'];
				} else {
					$strDisplayName = $mattress_month[$x]['last_name'];
				}
		?>
    		<tr class="<?php=(($x%2==0)?'footableOdd':'footableEven');?>">
    			<td><div style="width:32px;height:32px;  margin-bottom:2px;  display: block; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);    background-color: rgba(255, 255, 255, 1); border: 2px solid rgba(255, 255, 255, 1);  overflow: hidden;"><?php if($mattress_month[$x]['photo'] != '') echo "<img src='/images/users/".$mattress_month[$x]['photo']."' width='32' height='32'>"; else echo "<img src='images/default.jpg'/>";?></div></td>
    			<td style=' vertical-align: middle;'><?php=$strDisplayName?></td>
    			<td style=' vertical-align: middle;'>$<?php=number_format($mattress_month[$x]['total'])?></td>
    		</tr>
    	<?php  } ?>
		</tbody>
	</table>
</div>

														<div id="icmm3ysloverlay" class="s19overlay" ></div>
													</div>
													<div
														style="top: -7px; left: 156px; width: 94px; height: 117px; position: absolute; visibility: inherit;"
														title="" class="s5"
														id="icgu08gj">
														<div style="width: 94px; height: 117px;" id="icgu08gjlink"
															class="s5link">
															<div
																style="width: 94px; height: 117px; position: relative;"
																id="icgu08gjimg" class="s5img">
																<div class="s5imgpreloader" id="icgu08gjimgpreloader" ></div>
																<img id="icgu08gjimgimage" alt=""
																	src="./files/a69938_4bd36c487fb44025be02aaea33800993.png"
																	style="width: 94px; height: 117px; object-fit: cover;"
																	>
															</div>
														</div>
													</div>

													<div
														style="top: 0px; left: 178px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu5nsp">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">&nbsp;</h2>

														<h5 class="font_5" style="font-size: 36px;">
															<span style="font-size: 36px;"><span
																style='font-family: arial black, arial-w01-black, arial-w02-black, arial-w10 black, sans-serif;'><span
																	style="font-weight: bold;">25</span></span></span>
														</h5>
													</div>
													<div
														style="top: 5px; left: 157px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu3lp8">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">
															<span style="font-weight: bold;"><span
																style="font-size: 24px;">TOP</span></span>
														</h2>

														<h2 class="font_2">&nbsp;</h2>
													</div>
												</div>
											</div>



			</td>
			<td  style='padding-right:30px; vertical-align:top;'>

<?php  // --------------------------- Furniture Week ----------------//?>
											<div
												style=" width: 224px; height: 556px; position: relative;"
												class="s17" id="ibwqi8r2">
												<div id="ibwqi8r2bg" class="s17bg"></div>
												<div id="ibwqi8r2inlineContent" class="s17inlineContent">
													<div
														style="top: 31px; bottom:; left: 4px; right:; width: 198px; height: 54px; position: absolute;"
														 title="bd-matt.jpg"
														class="s18" id="ibwqi8r2_0">
														<div class="s18_left s18_shd"></div>
														<div class="s18_right s18_shd"></div>
														<div style="width: 194px; height: 50px;"
															id="ibwqi8r2_0link" class="s18link">
															<div
																style="width: 194px; height: 50px; position: relative;"
																id="ibwqi8r2_0img" class="s18img">
																<div class="s18imgpreloader" id="ibwqi8r2_0imgpreloader"></div>
																<img id="ibwqi8r2_0imgimage" alt=""
																	src="./files/d3fbdd_9301681e44664e358bf41f10db697c18.jpg"
																	style="width: 194px; height: 50px; object-fit: cover;">
															</div>
														</div>
													</div>
													<div style="top: 5px; left: 6px; width: 176px; position: absolute;" class="s8" id="ibwqi8r4">
														<h5 class="font_5"
															style="line-height: 1.2em; font-size: 18px;">
															<span style="font-size: 18px;"><font color="#552c00">Furniture
																	- Week</font></span>
														</h5>
													</div>
													<div style="min-height: 850px; min-width: 219px; visibility: visible; top: 99px; left: 2px; width: 219px; height: 850px; position: absolute;"
														class="s19" id="icmm3ysl">


<div id="tableWrapper" style="overflow: auto">
	<table id="theTable" class="footable table outerBorder footable-loaded" style="width: 100%; margin: 0px;">
		<thead>
			<tr>
				<th id="thCol0" data-type="numeric">Wk</th>
				<th id="thCol1" data-type="alpha"><?php=$week_clean?></th>
				<th id="thCol2" data-type="alpha"></th>
			</tr>
		</thead>
		<tbody>
		<?php for($x=0;$x<count($furniture_week);$x++) {
				if (!empty($furniture_week[$x]['big_board_name'])){
					$strDisplayName = $furniture_week[$x]['big_board_name'];
				} else {
					$strDisplayName = $furniture_week[$x]['last_name'];
				}
		?>
    		<tr class="<?php=(($x%2==0)?'footableOdd':'footableEven');?>">
    			<td><div style="width:32px;height:32px;  margin-bottom:2px;  display: block; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);    background-color: rgba(255, 255, 255, 1); border: 2px solid rgba(255, 255, 255, 1);  overflow: hidden;"><?php if($furniture_week[$x]['photo'] != '') echo "<img src='/images/users/".$furniture_week[$x]['photo']."' width='32' height='32'>"; else echo "<img src='images/default.jpg'/>";?></div></td>
    			<td style=' vertical-align: middle;'><?php=$strDisplayName?></td>
    			<td style=' vertical-align: middle;'>$<?php=number_format($furniture_week[$x]['total'])?></td>
    		</tr>
    	<?php  } ?>
		</tbody>
	</table>
</div>

														<div id="icmm3ysloverlay" class="s19overlay" ></div>
													</div>
													<div
														style="top: -7px; left: 156px; width: 94px; height: 117px; position: absolute; visibility: inherit;"
														title="" class="s5"
														id="icgu08gj">
														<div style="width: 94px; height: 117px;" id="icgu08gjlink"
															class="s5link">
															<div
																style="width: 94px; height: 117px; position: relative;"
																id="icgu08gjimg" class="s5img">
																<div class="s5imgpreloader" id="icgu08gjimgpreloader" ></div>
																<img id="icgu08gjimgimage" alt=""
																	src="./files/a69938_4bd36c487fb44025be02aaea33800993.png"
																	style="width: 94px; height: 117px; object-fit: cover;"
																	>
															</div>
														</div>
													</div>

													<div
														style="top: 0px; left: 178px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu5nsp">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">&nbsp;</h2>

														<h5 class="font_5" style="font-size: 36px;">
															<span style="font-size: 36px;"><span
																style='font-family: arial black, arial-w01-black, arial-w02-black, arial-w10 black, sans-serif;'><span
																	style="font-weight: bold;">10</span></span></span>
														</h5>
													</div>
													<div
														style="top: 5px; left: 157px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu3lp8">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">
															<span style="font-weight: bold;"><span
																style="font-size: 24px;">TOP</span></span>
														</h2>

														<h2 class="font_2">&nbsp;</h2>
													</div>
												</div>
											</div>





<br style='clear:both;'/>
<br style='clear:both;'/>



<?php  // --------------------------- Furniture Month ----------------//?>
											<div
												style=" width: 224px; height: 554px; position: relative;"
												class="s17" id="ibwqi8r2">
												<div id="ibwqi8r2bg" class="s17bg"></div>
												<div id="ibwqi8r2inlineContent" class="s17inlineContent">
													<div
														style="top: 31px; bottom:; left: 4px; right:; width: 198px; height: 54px; position: absolute;"
														 title="bd-matt.jpg"
														class="s18" id="ibwqi8r2_0">
														<div class="s18_left s18_shd"></div>
														<div class="s18_right s18_shd"></div>
														<div style="width: 194px; height: 50px;"
															id="ibwqi8r2_0link" class="s18link">
															<div
																style="width: 194px; height: 50px; position: relative;"
																id="ibwqi8r2_0img" class="s18img">
																<div class="s18imgpreloader" id="ibwqi8r2_0imgpreloader"></div>
																<img id="ibwqi8r2_0imgimage" alt=""
																	src="./files/d3fbdd_9301681e44664e358bf41f10db697c18.jpg"
																	style="width: 194px; height: 50px; object-fit: cover;">
															</div>
														</div>
													</div>
													<div style="top: 5px; left: 6px; width: 176px; position: absolute;" class="s8" id="ibwqi8r4">
														<h5 class="font_5"
															style="line-height: 1.2em; font-size: 18px;">
															<span style="font-size: 18px;"><font color="#552c00">Furniture
																	- Month</font></span>
														</h5>
													</div>
													<div style="min-height: 850px; min-width: 219px; visibility: visible; top: 99px; left: 2px; width: 219px; height: 850px; position: absolute;"
														class="s19" id="icmm3ysl">


<div id="tableWrapper" style="overflow: auto">
	<table id="theTable" class="footable table outerBorder footable-loaded" style="width: 100%; margin: 0px;">
		<thead>
			<tr>
				<th id="thCol0" data-type="numeric">Wk</th>
				<th id="thCol1" data-type="alpha"><?php=$month_clean?></th>
				<th id="thCol2" data-type="alpha"></th>
			</tr>
		</thead>
		<tbody>
		<?php for($x=0;$x<count($furniture_month);$x++) {
				if (!empty($furniture_month[$x]['big_board_name'])){
					$strDisplayName = $furniture_month[$x]['big_board_name'];
				} else {
					$strDisplayName = $furniture_month[$x]['last_name'];
				}
		?>
    		<tr class="<?php=(($x%2==0)?'footableOdd':'footableEven');?>">
				<td><div style="width:32px;height:32px;  margin-bottom:2px;  display: block; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);    background-color: rgba(255, 255, 255, 1); border: 2px solid rgba(255, 255, 255, 1);  overflow: hidden;"><?php if($furniture_month[$x]['photo'] != '') echo "<img src='/images/users/".$furniture_month[$x]['photo']."' width='32' height='32'>"; else echo "<img src='images/default.jpg'/>";?></div></td>
    			<td style=' vertical-align: middle;'><?php=$strDisplayName?></td>
    			<td style=' vertical-align: middle;'>$<?php=number_format($furniture_month[$x]['total'])?></td>
    		</tr>
    	<?php  } ?>
		</tbody>
	</table>
</div>

														<div id="icmm3ysloverlay" class="s19overlay" ></div>
													</div>
													<div
														style="top: -7px; left: 156px; width: 94px; height: 117px; position: absolute; visibility: inherit;"
														title="" class="s5"
														id="icgu08gj">
														<div style="width: 94px; height: 117px;" id="icgu08gjlink"
															class="s5link">
															<div
																style="width: 94px; height: 117px; position: relative;"
																id="icgu08gjimg" class="s5img">
																<div class="s5imgpreloader" id="icgu08gjimgpreloader" ></div>
																<img id="icgu08gjimgimage" alt=""
																	src="./files/a69938_4bd36c487fb44025be02aaea33800993.png"
																	style="width: 94px; height: 117px; object-fit: cover;"
																	>
															</div>
														</div>
													</div>

													<div
														style="top: 0px; left: 178px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu5nsp">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">&nbsp;</h2>

														<h5 class="font_5" style="font-size: 36px;">
															<span style="font-size: 36px;"><span
																style='font-family: arial black, arial-w01-black, arial-w02-black, arial-w10 black, sans-serif;'><span
																	style="font-weight: bold;">10</span></span></span>
														</h5>
													</div>
													<div
														style="top: 5px; left: 157px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu3lp8">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">
															<span style="font-weight: bold;"><span
																style="font-size: 24px;">TOP</span></span>
														</h2>

														<h2 class="font_2">&nbsp;</h2>
													</div>
												</div>
											</div>







			</td>
			<td  style= vertical-align:top;'>



<?php  // --------------------------- All ----------------//?>

											<div
												style="width: 224px; height: 1100px; position: relative;"
												class="s17" id="ibwqi8r2">
												<div id="ibwqi8r2bg" class="s17bg"></div>
												<div id="ibwqi8r2inlineContent" class="s17inlineContent">
													<div
														style="top: 31px; bottom:; left: 4px; right:; width: 198px; height: 54px; position: absolute;"
														 title="bd-matt.jpg"
														class="s18" id="ibwqi8r2_0">
														<div class="s18_left s18_shd"></div>
														<div class="s18_right s18_shd"></div>
														<div style="width: 194px; height: 50px;"
															id="ibwqi8r2_0link" class="s18link">
															<div
																style="width: 194px; height: 50px; position: relative;"
																id="ibwqi8r2_0img" class="s18img">
																<div class="s18imgpreloader" id="ibwqi8r2_0imgpreloader"></div>
																<img id="ibwqi8r2_0imgimage" alt=""
																	src="./files/d3fbdd_9301681e44664e358bf41f10db697c18.jpg"
																	style="width: 194px; height: 50px; object-fit: cover;">
															</div>
														</div>
													</div>
													<div style="top: 5px; left: 6px; width: 176px; position: absolute;" class="s8" id="ibwqi8r4">
														<h5 class="font_5"
															style="line-height: 1.2em; font-size: 18px;">
															<span style="font-size: 18px;"><font color="#552c00">All
																	- Month</font></span>
														</h5>
													</div>
													<div style="min-height: 1050px; min-width: 219px; visibility: visible; top: 99px; left: 2px; width: 219px; height: 850px; position: absolute;"
														class="s19" id="icmm3ysl">


<div id="tableWrapper" style="overflow: auto">
	<table id="theTable" class="footable table outerBorder footable-loaded" style="width: 100%; margin: 0px;">
		<thead>
			<tr>
				<th id="thCol0" data-type="numeric">Month</th>
				<th id="thCol1" data-type="alpha"><?php=$month_clean?></th>
				<th id="thCol2" data-type="alpha"></th>
			</tr>
		</thead>
		<tbody>
		<?php for($x=0;$x<count($all);$x++) {
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
    	<?php  } ?>
		</tbody>
	</table>
</div>

														<div id="icmm3ysloverlay" class="s19overlay" ></div>
													</div>
													<div
														style="top: -7px; left: 156px; width: 94px; height: 117px; position: absolute; visibility: inherit;"
														title="" class="s5"
														id="icgu08gj">
														<div style="width: 94px; height: 117px;" id="icgu08gjlink"
															class="s5link">
															<div
																style="width: 94px; height: 117px; position: relative;"
																id="icgu08gjimg" class="s5img">
																<div class="s5imgpreloader" id="icgu08gjimgpreloader" ></div>
																<img id="icgu08gjimgimage" alt=""
																	src="./files/a69938_4bd36c487fb44025be02aaea33800993.png"
																	style="width: 94px; height: 117px; object-fit: cover;"
																	>
															</div>
														</div>
													</div>

													<div
														style="top: 0px; left: 178px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu5nsp">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">&nbsp;</h2>

														<h5 class="font_5" style="font-size: 36px;">
															<span style="font-size: 36px;"><span
																style='font-family: arial black, arial-w01-black, arial-w02-black, arial-w10 black, sans-serif;'><span
																	style="font-weight: bold;">25</span></span></span>
														</h5>
													</div>
													<div
														style="top: 5px; left: 157px; width: 83px; position: absolute; visibility: inherit;"
														class="s8" id="icgu3lp8">
														<h2 class="font_2"
															style="text-align: center; font-size: 24px;">
															<span style="font-weight: bold;"><span
																style="font-size: 24px;">TOP</span></span>
														</h2>

														<h2 class="font_2">&nbsp;</h2>
													</div>
												</div>
											</div>






			</td>

		</tr>
	</table>
	<br style='clear:both;'/>
	<br style='clear:both;'/>

			<div
				style=" width: 976px; height: 9px; "
				class="s10" id="idstchev">
				<div id="idstchevline" class="s10line"></div>
			</div>

	<br style='clear:both;'/>

	<!-- SLIDER -->
	<div style='height:75px;'>
		<div style='width: 980px; margin:0px auto; padding: 10px 0;'>
		<div class="footer-slider">
			<ul>
				<?php
				$arrSlides = getSlides(4);
				while ($objSlide = mysql_fetch_array($arrSlides)) { ?>
				<li><a href="<?php=$objSlide['link']?>"><img src="<?php=$objSlide['image_url']?>" alt="banner1" style="height:75px;widht:979px;"></a></li>
				<?php } ?>
			</ul>
		</div>
		</div>
	</div>
	<!-- // SLIDER -->

	<br style='clear:both;'/>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type='text/javascript' src='/js/unslider-min.js'></script>

<script>

$(function(){
	$('.banner-slider').unslider({
		animation: 'fade',
		autoplay: true,
		arrows: true
	});
	$('.footer-slider').unslider({
		animation: 'fade',
		autoplay: true,
		arrows: false,
		nav: false
	});
});
</script>

<?php include 'includes/footer.php';?>
