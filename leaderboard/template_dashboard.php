<?php include 'includes/header.php';?>
<?php

$week_start  = date("Y-m-d",strtotime("-7 days", strtotime("last sunday")));
$week_end    = date("Y-m-d",strtotime("-8 days", strtotime("this sunday")));

$month_start    = date("Y-m-1");
$month_end      = date("Y-m-31");

$prev_month_start    = date("Y-m-1",strtotime("-1 month"));
$prev_month_end      = date("Y-m-31",strtotime("-1 month"));

if (empty($_REQUEST['start_date'])) $_REQUEST['start_date'] = date("m/1/Y");
if (empty($_REQUEST['end_date'])) $_REQUEST['end_date'] = date("m/t/Y");

$month_clean = date("M")." 1 - ".date('j',strtotime("last day of month"));
$week_clean  = date("M j",strtotime($week_start))." - ".date("M j",strtotime($week_end));

# get User Info for logged in user
$user = User::getUserFromLogin($_COOKIE['pmd_session_id']);

$manager = '';
if(!empty($_REQUEST['manager'])){
    $manager = $_REQUEST['manager'];
} else {
	$_REQUEST['manager'] = $user[manager]; 
    $manager = $_REQUEST['manager'];
}

$topMattressUserPrevMonth = BigBoard::getLeaders($prev_month_start, $prev_month_end, 'Bedding',1,'all','',true);
$topMattressUserPrevMonth = $topMattressUserPrevMonth[0];

$topFurnitureUserPrevMonth = BigBoard::getLeaders($prev_month_start, $prev_month_end, 'Furniture',1,'all','',true);
$topFurnitureUserPrevMonth = $topFurnitureUserPrevMonth[0];

# get last 10 users for "New Team Members"
#$qNewMembers = mysql_query("SELECT * from users WHERE ID IN (SELECT user_id FROM salestats) ORDER BY ID DESC LIMIT 10"); 
$qNewMembers = mysql_query("select distinct(user), users.`big_board_name`, users.last_name, users.`first_name` from order_forms LEFT JOIN users ON order_forms.user = users.ID ORDER BY order_forms.ID DESC LIMIT 10");

# get last 10 users for "Incentive Trip"
$qIncentiveMembers = mysql_query("SELECT * from users WHERE lb_incentive_ranking IS NOT NULL AND lb_incentive_ranking != '' ORDER BY ID DESC LIMIT 10");

$qNewsArticles = mysql_query("SELECT * from cms_news_articles WHERE title!='' AND deleted=0 ORDER BY publish_date LIMIT 5"); 

$strCountDown = "";

$queryCountdown = mysql_query("SELECT * from cms_countdown WHERE cms_countdown_id=1"); 
while( $qCountdownRow = mysql_fetch_assoc($queryCountdown)){
	$strCountDown = $qCountdownRow['cms_countdown_datetime'];
}

if (strtotime($strCountDown) < strtotime(date("Y-m-d h:i:s"))){
	$strCountDown = "";
}

$strThisMonth = date("Y-m");
$strThisYear = date("Y");

$strThisYearStart = date("Y-01-01");

$strPrevMonth = date("Y-m",strtotime("-1 month"));
$strPrevYear = date("Y",strtotime("-1 year"));

$LB_5kweek = mysql_query("SELECT * from users WHERE lb_5kweek = 'Y' ORDER BY last_name");
$LB_10kweek = mysql_query("SELECT * from users WHERE lb_10kweek = 'Y' ORDER BY last_name");
$LB_250kweek = mysql_query("SELECT * from users WHERE lb_250kweek = 'Y' ORDER BY last_name");
$LB_500kweek = mysql_query("SELECT * from users WHERE lb_500kweek = 'Y' ORDER BY last_name");
$LB_millyear = mysql_query("SELECT * from users WHERE lb_millyear = 'Y' ORDER BY last_name");
$LB_mrecord_mattress = mysql_query("SELECT * from users WHERE lb_mrecord_mattress = 'Y' ORDER BY last_name");


$intBeddingTotalNew = BigBoard::getLeaders($month_start, $month_end, 'Bedding',1,'',$user[ID]);
$intBeddingTotal = $intBeddingTotalNew[0][total];
$intPrevBeddingTotalNew = BigBoard::getLeaders($prev_month_start, $prev_month_end, 'Bedding',1,'',$user[ID]);
$intPrevBeddingTotal = $intPrevBeddingTotalNew[0][total];
$pctBeddingMonthlyChange = round((1 - $intPrevBeddingTotal / $intBeddingTotal) * 100);

$intFurnitureTotalNew = BigBoard::getLeaders($month_start, $month_end, 'Furniture',1,'',$user[ID]);
$intFurnitureTotal = $intFurnitureTotalNew[0][total];
$intPrevFurnitureTotalNew = BigBoard::getLeaders($prev_month_start, $prev_month_end, 'Furniture',1,'',$user[ID]);
$intPrevFurnitureTotal = $intPrevFurnitureTotalNew[0][total];
$pctFurnitureMonthlyChange = round((1 - $intPrevFurnitureTotal / $intFurnitureTotal) * 100);


$intTotalYTDRetail = BigBoard::getLeaders($strThisYearStart, date("Y-m-d"), 'All',1,'',$user[ID]);
$intTotalYTDRetail = $intTotalYTDRetail[0][total];

$intPrevTotalYTDRetail = BigBoard::getLeaders(date("Y-01-01",strtotime("-1 year")), date("Y-m-d",strtotime("-1 year")), 'All',1,'',$user[ID]);
$intPrevTotalYTDRetail = $intPrevTotalYTDRetail[0][total];

$pctTotalYTDRetailChange = round((1 - $intPrevTotalYTDRetail / $intTotalYTDRetail) * 100);
$myProfileImage = profilePhoto($user[photo]);

$arrAllTimeSales = BigBoard::allTimeSales();
#$arrManagers = mysql_query("SELECT * from managers ORDER BY `order`");
$managers = array();
$arrManagers = mysql_query("select sum(total) as total, u.manager as name from order_forms as o left join users as u on u.id = o.user where o.ordered >= '2017-02-1 00:00:00' and o.deleted = 0 and u.nonPMD <> 'Y' and total > 0 group by u.manager ORDER BY total DESC");
while($objManager = mysql_fetch_assoc($arrManagers)){
	$managers[] = $objManager;
}

$managersMonthly = array();
$sql = "select sum(total) as total, u.manager as name from order_forms as o left join users as u on u.id = o.user where o.ordered >= '".$month_start."' AND o.ordered <= '".$month_end."' and o.deleted = 0 and u.nonPMD <> 'Y' and total > 0 group by u.manager ORDER BY total DESC";
$arrManagersMonthly = mysql_query($sql);
while($objManagerMonthly = mysql_fetch_assoc($arrManagersMonthly)){
	$managersMonthly[] = $objManagerMonthly;
}
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

.bigsales{
	max-height: 1100px;
	overflow-y: scroll;
}

</style>

				<div class="topbar">
					<div class="container">
						<div class="page-content">
							 <div class="col-md-2 col">
								<div class="widgetbox">
									<div class="cntn">
										<div class="col-l">
											<center>
											<p><div class="profilePhotoCrop"><a href="#"><img class="profilePhoto" src="<?php=$myProfileImage?>" alt="" /></a></div></p>
											</center>
											<!-- <a href="#">edit profile</a> -->
										</div>
										
										<div class="col-r">
											<span class="membership gold">GOLD</span>
											<div class="line2"><?php=$user[first_name]?></div>
											<div class="line3"><?php=$user[city]?>, <?php=$user[state]?></div>
											<div class="line4"><a href="tel:205) 452-9843"><?php=$user[phone]?></a></div>
											<div class="line5"><a href="<?php=$user[email]?>"><?php=$user[email]?></a></div>
										</div>
										
										<div class="clear"></div>
									</div>
								</div>
							 </div>
							 
							 <div class="col-md-2 col">
								<div class="widgetbox amountbox">
									<div class="cntn">
										<div class="line1">$<?php=number_format((float)($intBeddingTotal), 2, '.', ',');?></div>
										<div class="line2">Mattress purchased this month</div>
									</div>
									<div class="bottombox">
									<?php
									if ($pctBeddingMonthlyChange > 0){ ?>
									<span class="inc"><?php=abs($pctBeddingMonthlyChange)?>% INCREASE</span>
									<?php } else { ?>
									<span class="dec"><?php=abs($pctBeddingMonthlyChange)?>% DECREASE</span>
									<?php } ?>
									</div>
								</div>
							 </div>
							 
							 <div class="col-md-2 col">
								<div class="widgetbox amountbox">
									<div class="cntn">
										<div class="line1">$<?php=number_format((float)($intFurnitureTotal), 2, '.', ',');?></div>
										<div class="line2">Furniture purchased this month</div>
									</div>
									<div class="bottombox">
									<?php
									if ($pctFurnitureMonthlyChange > 0){ ?>
									<span class="inc"><?php=abs($pctFurnitureMonthlyChange)?>% INCREASE</span>
									<?php } else { ?>
									<span class="dec"><?php=abs($pctFurnitureMonthlyChange)?>% DECREASE</span>
									<?php } ?>
									</div>
								</div>
							 </div>
							 
							 <div class="col-md-2 col">
								<div class="widgetbox amountbox">
									<div class="cntn">
										<div class="line1">$<?php=number_format((float)($intTotalYTDRetail), 2, '.', ',');?></div>
										<div class="line2">Year to date purchases</div>
									</div>
									<div class="bottombox">
									<?php
									if ($pctTotalYTDRetailChange > 0){ ?>
									<span class="inc"><?php=abs($pctTotalYTDRetailChange)?>% INCREASE</span>
									<?php } else { ?>
									<span class="dec"><?php=abs($pctTotalYTDRetailChange)?>% DECREASE</span>
									<?php } ?>
									</div>
								</div>
							 </div>

							 <div class="col-md-2 col">
								<div class="widgetbox amountbox">
									<center>
									<p><div class="profilePhotoCrop" style="margin-top:13px;"><a href="#"><img class="profilePhoto" src="/images/users/<?php=$topMattressUserPrevMonth['photo']?>" alt="" /></a></div></p>
									<div class="line3" style="padding:0px;margin:0px;line-height:8px;padding-top:5px;font-size:12px;"><?php=$topMattressUserPrevMonth['first_name']?></div>
									</center>
									<div class="bottombox" style="font-size:11px;text-transform:uppercase;padding:17px 2px 16px 2px;">
									<b><?php=date("M Y",strtotime('-1 months'))?></b> Mattress Leader
									</div>
								</div>
							 </div>

							 <div class="col-md-2 col">
								<div class="widgetbox amountbox">
									<center>
									<p><div class="profilePhotoCrop" style="margin-top:13px;"><a href="#"><img class="profilePhoto" src="/images/users/<?php=$topFurnitureUserPrevMonth['photo']?>" alt="" /></a></div></p>
									<div class="line3" style="padding:0px;margin:0px;line-height:8px;padding-top:5px;font-size:12px;"><?php=$topFurnitureUserPrevMonth['first_name']?></div>
									</center>
									<div class="bottombox">
									<div class="bottombox" style="font-size:11px;text-transform:uppercase;padding:17px 2px 16px 2px;">
									<b><?php=date("M Y",strtotime('-1 months'))?></b> Furniture Leader
									</div>
									</div>
								</div>
							 </div>
							 
							 <div class="col-md-3 col" style="font-size:13px;">
							</div>

							<div class="clear"></div>
						</div>
					</div>
				</div>

				<div class="container">
					
					<div class="page-content maincontent">
						 <div class="col-md-3 col">

							<div class="widgetbox newteam">
								<div class="title">New Locations</div>

								<table>
									<?php while($objNewMember = mysql_fetch_assoc($qNewMembers) ){
									
										if (!empty($objNewMember['big_board_name'])){
											$strDisplayName = $objNewMember['big_board_name'];
										} else {
											$strDisplayName = $objNewMember['last_name'];
										}

									?>
									<tr>
										<td class="photo" style="padding:2px;"><div class="smallProfilePhotoCrop"><img alt="" class="smallProfilePhoto" src="<?php=profilePhoto($objNewMember[photo])?>"/></div></td>
										<td class="name"><a href="#"><?php=$strDisplayName?></a></td>
									</tr>
									<?php } ?>
								</table>
							</div>

							<div class="widgetbox newteam">
								<div class="title"><a href="/leaderboard/incentive_trip">View Incentive Trip Qualifiers</a></div>
							</div>

<br>

						
						 </div>
						 
						 <div class="col-md-9 col" style="padding-left:20px !important;">
						 
							<div class="purchases">

								<div class="header">
									<div class="title">Leaderboard</div>
									<div class="months"> <div class="date-picker" id="topListDate" data-date-format="mm/dd/yyyy"> </div></div>
									<div class="clear"></div>
									
									
								</div>
								

<div class="row" style="padding:10px;">
								<div class="col-sm-3" style="width:150px;padding-top:7px;">
								 	Manager/Division:
								</div>
								<div class="col-sm-5">
								<select class="form-control" id="manager">
									<option value="all">All Division</option>
									<?php
										foreach($managers as $manager){
											if ($manager['name'] != "None"){	
												echo "<option value='".$manager['name']."'";
												if ($_REQUEST['manager']==$manager['name']) echo " selected";												
												echo ">".$manager['name']."</option>";
											}
										}
									?>
									<option disabled>──────────</option>
									<option value="divisions_ranked"<?php if (!empty($_REQUEST['manager']) && $_REQUEST['manager'] == "divisions_ranked") { ?> selected<?php } ?>>Divisions Ranked</option>
								</select>
								</div>
</div>
								<?php if (!empty($_REQUEST['manager']) && $_REQUEST['manager'] == "divisions_ranked") { ?><div style="display:none;"><?php } ?>
								<table class="left">
									<tr>
										<th colspan="3"><span class="titl"><span class="span1">Top 10</span> <span id="divisional_matt_header_span2" class="span2">- BoxDrop Mattress</span></span>
											<div class="actions">
												<label></label>
												<select class="filterbyMattress bs-select form-control input-small" id="to20Bedding_Filter">
<!--													<option value="month">day</option>-->
													<option selected value="month">month</option>
													<option value="year">year</option>
												</select>
											</div>
										</th>
									</tr>
									<tr>
										<td colspan="3">
											<div id="top20Bedding"></div>
										</td>
									</tr>
								</table>
								
								
								
								<table class="right">
									<tr>
										<th colspan="3"><span class="titl"><span class="span1">Top 10</span> <span class="span2">- BoxDrop Furniture</span></span>
											<div class="actions">
												<label></label>
												<select class="filterbyFurniture bs-select form-control input-small" id="to20All_Filter">
<!--													<option value="month">day</option>-->
													<option selected value="month">month</option>
													<option value="year">year</option>
												</select>
											</div>
										</th>
										</tr>
									<tr>
										<td colspan="3">
											<div id="top20All"></div>
										</td>
									</tr>
								</table>

								<?php if (!empty($_REQUEST['manager']) && $_REQUEST['manager'] == "divisions_ranked") { ?></div><?php } ?>
								
								<?php if (!empty($_REQUEST['manager']) && $_REQUEST['manager'] == "divisions_ranked") { ?>
								
																		<div class="row">
								<div class="col-sm-12" style="padding:7px;border-top:1px solid #ccc;color:#FFF;background:#555;">
								DIVISIONAL RANKINGS
								</div>
								</div>
								<div id="divisionalRankings">
									<?php
										$o = 1;
										foreach($managersMonthly as $manager){
										?>
																		<div class="row">
								<div class="col-sm-12" style="padding:7px;border-top:1px solid #ccc;">
								
<?php
											if ($manager['name'] != "None" && ($manager['name'] != "Clifton Mast")){	
												echo $o.". ".$manager['name']."<br>";
												$o++;
											}
										?>
								</div>
								</div>


										<?php
										}
									?>
								</div>

								
								<?php } ?>


							</div>
						 
							<br style='clear:both;'/>
							<br style='clear:both;'/>

							<div class="purchases"<?php if (!empty($_REQUEST['manager']) && $_REQUEST['manager'] == "divisions_ranked") { ?> style="display:none;"><?php } ?>>

								<div class="header">
									<div class="title">National Leaderboard</div>
									<div class="months"> <div class="date-picker" id="topListDateNational" data-date-format="mm/dd/yyyy"> </div></div>
									<div class="clear"></div>
									
									
								</div>
								
								<table class="left">
									<tr>
										<th colspan="3"><span class="titl"><span class="span1">Top 25</span> <span class="span2">- Mattress</span></span>

											<div class="actions">
												<label></label>
												<select class="filterbyMattress bs-select form-control input-small" id="to20Bedding_FilterNational">
													<option selected value="month">month</option>
													<option value="year">year</option>
												</select>
											</div>
										</th>
									</tr>
									<tr>
										<td colspan="3">
											<div id="top20BeddingNational"></div>
										</td>
									</tr>
								</table>
								
								
								
								<table class="right">
									<tr>
										<th colspan="3"><span class="titl"><span class="span1">Top 25</span> <span class="span2">- Mattress/Furniture</span></span>
											<div class="actions">
												<label></label>
												<select class="filterbyFurniture bs-select form-control input-small" id="to20All_FilterNational">
													<option selected value="month">month</option>
													<option value="year">year</option>
												</select>
											</div>
										</th>
										</tr>
									<tr>
										<td colspan="3">
											<div id="top20AllNational"></div>
										</td>
									</tr>
								</table>
							</div>
						 </div>
<!--
						 <div class="col-md-2 col">
						 <?php if (mysql_num_rows($qIncentiveMembers)): ?>
						 
							<div class="widgetbox newteam" style="min-height:0px !important;">
								<div class="title">Incentive Trip</div>

								<table>
									<?php while($objIncentiveMember = mysql_fetch_assoc($qIncentiveMembers) ){
									
										switch($objIncentiveMember[lb_incentive_ranking]){
											case "1": 
												$borderColor = "#cccccc";
												break;
											case "2": 
												$borderColor = "#CD7F32";
												break;
											case "3": 
												$borderColor = "#FFD700";
												break;
											case "4": 
												$borderColor = "#b9f2ff";
												break;
										}
									
									
										if (!empty($objIncentiveMember['big_board_name'])){
											$strDisplayName = $objIncentiveMember['big_board_name'];
										} else {
											$strDisplayName = $objIncentiveMember['last_name'];
										}

									?>
									<tr>
										<td class="photo"><div class="smallProfilePhotoCrop"><img alt="" class="smallProfilePhoto" src="<?php=profilePhoto($objIncentiveMember[photo])?>"/></div></td>
										<td class="name"><a href="#"><?php=$strDisplayName?></a></td>
									</tr>
									<?php } ?>
								</table>
							</div>
						 
						 
						 <?php if(!empty($strCountDown)) { ?>
							<div id="clockdiv" style="background:#333;color:#FFF;padding:7px;font-size:12px;">
							  <div>
								<span class="days"></span>
								<span class="smalltext">Days</span>
								<Br>
								<span class="hours"></span>
								<span class="smalltext">h</span>
								<span class="minutes"></span>
								<span class="smalltext">m</span>
								<span class="seconds"></span>
								<span class="smalltext">s</span>
							  </div>
							</div>
						<?php } ?>
                <?php endif; ?>

							<div class="widgetbox newteam">
								<div class="title">News </div>
									<div style="padding:10px 14px;">
									<?php
									
									if (mysql_num_rows($qNewsArticles)) {
									
										while($objNewsArticle = mysql_fetch_assoc($qNewsArticles) ){?>
										<div style="border-bottom:1px solid #ccc;">
										<b><a href="news_article.php?id=<?php=$objNewsArticle['cms_news_article_id']?>" style="padding:0px!important;"><?php=$objNewsArticle['title']?></a></b>
										<font style="font-size:13px;line-height:10px;"><i>
										<?php=$objNewsArticle['description']?>
										</i></font>
										</div>
										<br>
										<?php }
									} else {
										echo "<i style='font-size:11px;'>No news available</i>";
									}?>
									</div>
							</div>
							
							
						 </div>
-->
				</div>

								<div class="clear"></div>

<BR><BR>

<div style="border-top:2px solid #ccc;">
</div>
			
							</div>


						</div>

<style>

.profilePhotoCrop {
    width: 70px;
    height: 70px;
    position: relative;
    overflow: hidden;
    border-radius: 50% !important;
}

.smallProfilePhotoCrop {
    width: 40px;
    height: 40px;
    position: relative;
    overflow: hidden;
    border-radius: 50% !important;
}

img.img-circle {
    display: inline;
    margin: 0 auto 0;
    max-height: 55px !important;
    width: 100% !important;
}

#top20Bedding{
	min-height:565px;
}

#top20Furniture{
	min-height:565px;
}

.tabli{
	border-top:2px solid #999;
}

</style>

			<BR><BR>
<!--			
			<div class="container" style="min-height:500px;">
			
			<div class="tabbable tabbable-tabdrop">
				<ul class="nav nav-pills">
					<li class="tabli active">
						<a href="#tab2" data-toggle="tab">Bronze Club</a>
					</li>
					<li class="tabli">
						<a href="#tab3" data-toggle="tab">Silver Club</a>
					</li>
					<li class="tabli">
						<a href="#tab4" data-toggle="tab">Gold Club</a>
					</li>
					<li class="tabli">
						<a href="#tab5" data-toggle="tab">Platinum Club</a>
					</li>
					<li class="tabli">
						<a href="#tab6" data-toggle="tab">1/2 Million Dollar Club</a>
					</li>
					<li class="tabli">
						<a href="#tab7" data-toggle="tab">Million Dollar Club</a>
					</li>

					<li class="tabli">
						<a href="#tab8" data-toggle="tab">Monthly Record<br/><span>for Mattress Purchases</span></a>
					</li>
				</ul>
	
				<div class="container">
					
					<div class="page-content">
						<div class="tab-content">
							<div class="tab-pane" id="tab1">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div id="boxes" class="boxes">
								
									<?php
									$LB_grandday = mysql_query("SELECT * from users WHERE lb_grandday = 'Y' ORDER BY last_name");
										while ($LB_grandday_row = mysql_fetch_assoc($LB_grandday)){ 
											switch($LB_grandday_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_grandday_row['big_board_name'])){
											$strDisplayName = $LB_grandday_row['big_board_name'];
										} else {
											$strDisplayName = $LB_grandday_row['last_name'];
										}
										?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_grandday_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
								
								</div>
								
								<div class="clear"></div>
								
							</div>
							<div class="tab-pane active" id="tab2">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div class="boxes">

									<?php
									$LB_2kday = mysql_query("SELECT * from users WHERE lb_2kday = 'Y' ORDER BY last_name");
										while ($LB_2kday_row = mysql_fetch_assoc($LB_2kday)){ 
											switch($LB_2kday_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_2kday_row['big_board_name'])){
											$strDisplayName = $LB_2kday_row['big_board_name'];
										} else {
											$strDisplayName = $LB_2kday_row['last_name'];
										}
									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_2kday_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
									
									
								</div>
								
								<div class="clear"></div>
							</div>
							<div class="tab-pane" id="tab3">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div class="boxes">

									<?php while ($LB_5kweek_row = mysql_fetch_assoc($LB_5kweek)){
											switch($LB_5kweek_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}

										if (!empty($LB_5kweek_row['big_board_name'])){
											$strDisplayName = $LB_5kweek_row['big_board_name'];
										} else {
											$strDisplayName = $LB_5kweek_row['last_name'];
										}

									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_5kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
									
									
								</div>
								
								<div class="clear"></div>
							</div>
							<div class="tab-pane" id="tab4">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div class="boxes">

									<?php while ($LB_10kweek_row = mysql_fetch_assoc($LB_10kweek)){
											switch($LB_10kweek_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_10kweek_row['big_board_name'])){
											$strDisplayName = $LB_10kweek_row['big_board_name'];
										} else {
											$strDisplayName = $LB_10kweek_row['last_name'];
										}
									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_10kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>

								</div>
								
								<div class="clear"></div>
							</div>
							<div class="tab-pane" id="tab5">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div class="boxes">

									<?php while ($LB_250kweek_row = mysql_fetch_assoc($LB_250kweek)){
											switch($LB_250kweek_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_250kweek_row['big_board_name'])){
											$strDisplayName = $LB_250kweek_row['big_board_name'];
										} else {
											$strDisplayName = $LB_250kweek_row['last_name'];
										}
									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_250kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
									
								</div>
								
								<div class="clear"></div>
							</div>
							<div class="tab-pane" id="tab6">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div class="boxes">

									<?php while ($LB_500kweek_row = mysql_fetch_assoc($LB_500kweek)){
											switch($LB_500kweek_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_500kweek_row['big_board_name'])){
											$strDisplayName = $LB_500kweek_row['big_board_name'];
										} else {
											$strDisplayName = $LB_500kweek_row['last_name'];
										}
									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_500kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
									
								</div>
								
								<div class="clear"></div>
							</div>
							<div class="tab-pane" id="tab7">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div id="boxes" class="boxes">
									

									<?php while ($LB_millyear_row = mysql_fetch_assoc($LB_millyear)){
											switch($LB_millyear_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_millyear_row['big_board_name'])){
											$strDisplayName = $LB_millyear_row['big_board_name'];
										} else {
											$strDisplayName = $LB_millyear_row['last_name'];
										}
									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_millyear_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
									
									
								</div>
								
								<div class="clear"></div>
							</div>
							<div class="tab-pane" id="tab8">
								<div class="actions">
									<label>filter by:</label>
									<select class="filterby bs-select form-control input-small">
										<option value="az">A-Z</option>
										<option value="za">Z-A</option>
									</select>
								</div>
								<div class="clear"></div>
			
								<div id="boxes" class="boxes">

									<?php while ($LB_mrecord_mattress_row = mysql_fetch_assoc($LB_mrecord_mattress)){
											switch($LB_mrecord_mattress_row[level]){
												case "1": 
													$borderColor = "#cccccc";
													$membershipClass = "silver";
													break;
												case "2": 
													$borderColor = "#CD7F32";
													$membershipClass = "bronze";
													break;
												case "3": 
													$borderColor = "#FFD700";
													$membershipClass = "gold";
													break;
												case "4/5": 
													$borderColor = "#b9f2ff";
													$membershipClass = "diamond";
													break;
												default:
													$borderColor = "#FFF";
													break;
											}
										if (!empty($LB_mrecord_mattress_row['big_board_name'])){
											$strDisplayName = $LB_mrecord_mattress_row['big_board_name'];
										} else {
											$strDisplayName = $LB_mrecord_mattress_row['last_name'];
										}
									?>
									<div class="box" data-value="<?php=$strDisplayName?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_mrecord_mattress_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$strDisplayName?></div>
									</div>
									<?php } ?>
								</div>
								
							</div>
-->


				</div>

<?php include 'includes/footer.php';?>




<script>

var currMonth = '<?php=date('n')?>';
var currYear = '<?php=date('Y')?>';
<?php if ($_REQUEST['manager'] == "all") { ?>
var manager = '';
<?php } else { ?>
var manager = '<?php=$_REQUEST['manager']?>';
<?php } ?>

function getTopLists(){
	$("#top20Bedding").html("<center>Loading...</center>");
	$("#top20All").html("<center>Loading...</center>");
	
	console.log("currYear="+currYear+", currMonth="+currMonth);
	
	getBeddingTopList();
	getFurnitureTopList();
	
}


function updateDivisionalRankings(){
	$.post( "ajax/divisional_rankings.php", { y: currYear, m: currMonth, ttl: 6 })
	  .done(function( data ) {
		$("#divisionalRankings").html(data);
	  });
}

function getBeddingTopList(){
	$.post( "ajax/top_list_"+$("#to20Bedding_Filter").val()+".php", { y: currYear, m: currMonth, t: 'Bedding', mgr: manager, ttl: 10 })
	  .done(function( data ) {
		$("#top20Bedding").html(data);
	  });
}

function getFurnitureTopList(){
	$.post( "ajax/top_list_"+$("#to20All_Filter").val()+".php", { y: currYear, m: currMonth, t: 'Furniture', mgr: manager, ttl: 10 })
	  .done(function( data ) {
		$("#top20All").html(data);
	  });
}


function getTopListsNational(){
	$("#top20BeddingNational").html("<center>Loading...</center>");
	$("#top20AllNational").html("<center>Loading...</center>");
	
	console.log("currYear="+currYear+", currMonth="+currMonth);
	
	getBeddingTopListNational();
	getFurnitureTopListNational();
	
}

function getBeddingTopListNational(){
	$.post( "ajax/top_list_national_"+$("#to20Bedding_FilterNational").val()+".php", { y: currYear, m: currMonth, t: 'Bedding', mgr: manager, ttl: 25 })
	  .done(function( data ) {
		$("#top20BeddingNational").html(data);
	  });
}

function getFurnitureTopListNational(){
	$.post( "ajax/top_list_national_"+$("#to20All_FilterNational").val()+".php", { y: currYear, m: currMonth, t: 'Furniture', mgr: manager, ttl: 25 })
	  .done(function( data ) {
		$("#top20AllNational").html(data);
	  });
}

$(function(){

$("#manager").change(function(){
	window.location.href = "?manager="+$(this).val();
});



getTopLists();
getTopListsNational();

$(".filterbyMattress").change(function(){
	getBeddingTopList();
});

$(".filterbyFurniture").change(function(){
	getFurnitureTopList();
});

$("#to20Bedding_FilterNational").change(function(){
	getBeddingTopListNational();
});

$("#to20All_FilterNational").change(function(){
	getFurnitureTopListNational();
});


//$("#topListDate").datepicker("setDate", new Date(currYear+'-'+currMonth+'-01'));

$('#topListDate').datepicker().on('changeMonth', function(e){
	<?php if (!empty($_REQUEST['manager']) && $_REQUEST['manager'] == "divisions_ranked") { ?>
		currMonth = new Date(e.date).getMonth() + 1;
		currYear = String(e.date).split(" ")[3];
		updateDivisionalRankings();
	<?php } else { ?>
		currMonth = new Date(e.date).getMonth() + 1;
		currYear = String(e.date).split(" ")[3];
		getTopLists();
	<?php } ?>
});
$('#topListDateNational').datepicker().on('changeMonth', function(e){
	currMonth = new Date(e.date).getMonth() + 1;
	currYear = String(e.date).split(" ")[3];
	getTopListsNational();
});
  	
});



</script>

<script>

var currMonth = '<?php=date('m')?>';
var currYear = '<?php=date('Y')?>';



</script>
<script src="/leaderboard/js/cliftonhack.js"></script>
