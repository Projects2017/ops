<?php include 'includes/header.php';

$LB_5kweek = mysql_query("SELECT * from users WHERE lb_5kweek = 'Y' ORDER BY last_name");
$LB_10kweek = mysql_query("SELECT * from users WHERE lb_10kweek = 'Y' ORDER BY last_name");
$LB_250kweek = mysql_query("SELECT * from users WHERE lb_250kweek = 'Y' ORDER BY last_name");
$LB_500kweek = mysql_query("SELECT * from users WHERE lb_500kweek = 'Y' ORDER BY last_name");
$LB_millyear = mysql_query("SELECT * from users WHERE lb_millyear = 'Y' ORDER BY last_name");
$LB_mrecord_mattress = mysql_query("SELECT * from users WHERE lb_mrecord_mattress = 'Y' ORDER BY last_name");


?>
			
			
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
    max-height: 162px !important;
    width: 100% !important;
}

</style>
			
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
<!--
					<li class="tabli">
						<a href="#tab8" data-toggle="tab">Monthly Record<br/><span>for Mattress Purchases</span></a>
					</li>
-->
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
										?>
									<div class="box" data-value="<?php=$LB_grandday_row['first_name']?> <?php=$LB_grandday_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_grandday_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_grandday_row['first_name']?> <?php=$LB_grandday_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_2kday_row['first_name']?> <?php=$LB_2kday_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_2kday_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_2kday_row['first_name']?> <?php=$LB_2kday_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_5kweek_row['first_name']?> <?php=$LB_5kweek_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_5kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_5kweek_row['first_name']?> <?php=$LB_5kweek_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_10kweek_row['first_name']?> <?php=$LB_10kweek_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_10kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_10kweek_row['first_name']?> <?php=$LB_10kweek_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_250kweek_row['first_name']?> <?php=$LB_250kweek_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_250kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_250kweek_row['first_name']?> <?php=$LB_250kweek_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_500kweek_row['first_name']?> <?php=$LB_500kweek_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_500kweek_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_500kweek_row['first_name']?> <?php=$LB_500kweek_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_millyear_row['first_name']?> <?php=$LB_millyear_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_millyear_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_millyear_row['first_name']?> <?php=$LB_millyear_row['last_name']?></div>
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
									?>
									<div class="box" data-value="<?php=$LB_mrecord_mattress_row['first_name']?> <?php=$LB_mrecord_mattress_row['last_name']?>">
										<div class="boximg"><img class="img-circle" alt="" src="<?php=profilePhoto($LB_mrecord_mattress_row[photo])?>" style="border:5px solid <?php=$borderColor?> !important;"/></div>
										<div class="boxname"><?php=$LB_mrecord_mattress_row['first_name']?> <?php=$LB_mrecord_mattress_row['last_name']?></div>
									</div>
									<?php } ?>
								</div>
								
								<div class="clear"></div>
							</div>
						</div>
					</div>


<?php include 'includes/footer.php';?>

<script>

var currMonth = '6';
var currYear = '<?php=date('Y')?>';



</script>
