<?php
if(!empty($_POST)){
    $msg = "Name: ".$_POST['Name']."\r\n";
    $msg .= "Email: ".$_POST['Email']."\r\n";
    $msg .= "Profit: ".$_POST['Profit']."\r\n";
    $msg .= "Date: ".$_POST['Date']."\r\n";
    
    $headers = 'From: noreply@boxdropbigboard.com' . "\r\n" .
        'Reply-To: noreply@boxdropbigboard.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    sendmail(BigBoard::$email,"Grand Day Submission Form",$msg,$headers);
}
?>

<?php include 'includes/header.php';?>
<style>
.s15wrapper {min-width:180px;max-width:980px;max-height:1024px;position:absolute;width:100%;}
.s15 span {color:#FF8400;float:left;font:normal 14px 'Helvetica Neue', Helvetica, sans-serif;max-width:60%;}
.s15 span.s15_success {color:#FFCB05;}
.s15 span.s15_error {color:#8B0000;}
.s15 button,.s15 input,.s15 textarea {border-radius:5px;  box-shadow:0 1px 4px rgba(0, 0, 0, 0.6);;  padding:5px;}
.s15 input,.s15 textarea {font:normal normal normal 23px/1.3em Jura,sans-serif ;  background-color:rgba(108, 146, 198, 1);-webkit-appearance:none;-moz-appearance:none;border:1px solid rgba(0, 0, 0, 1);color:#FF8400;margin:0 0 5px;width:100%;}
.s15 input.s15_error,.s15 textarea.s15_error {font:normal normal normal 23px/1.3em Jura,sans-serif ;    border:1px solid #8B0000;color:#8B0000;}
.s15 input::-webkit-input-placeholder,.s15 textarea::-webkit-input-placeholder {color:#FFFFFF;}
.s15 input::-moz-placeholder,.s15 textarea::-moz-placeholder {color:#FFFFFF;}
.s15 input:-ms-input-placeholder,.s15 textarea:-ms-input-placeholder {color:#FFFFFF;}
.s15 input::placeholder,.s15 textarea::placeholder {color:#FFFFFF;}
.s15 button {background-color:rgba(255, 132, 0, 1);font:normal normal normal 23px/1.3em Jura,sans-serif ;  border:0;color:#303030;cursor:pointer;float:right;margin:0;max-width:35%;}
.s15 textarea {min-height:130px;resize:none;}
.s15[data-state~="mobile"] input {color:#FF8400;font-size:16px;height:45px;line-height:45px;margin:0 0 15px;padding:0 5px;}
.s15[data-state~="mobile"] input::-webkit-input-placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"] input::-moz-placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"] input:-ms-input-placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"] input::placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"] textarea {color:#FF8400;font-size:16px;height:150px;margin:0 0 10px;}
.s15[data-state~="mobile"]::-webkit-input-placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"]::-moz-placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"]:-ms-input-placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="mobile"]::placeholder {color:#FFFFFF;font-size:16px;}
.s15[data-state~="right"] {direction:rtl;text-align:right;}
.s15[data-state~="right"] span {float:right;}
.s15[data-state~="right"] button {float:left;}
.s15[data-state~="left"] {direction:ltr;text-align:left;}
.s15[data-state~="left"] span {float:left;}
.s15[data-state~="left"] button {float:right;}
.s15[data-state~="nameHidden"] .s15nameField,.s15[data-state~="emailHidden"] .s15emailField,.s15[data-state~="subjectHidden"] .s15subjectField,.s15[data-state~="phoneHidden"] .s15phoneField,.s15[data-state~="addressHidden"] .s15addressField,.s15[data-state~="messageHidden"] .s15messageField {display:none !important;}
</style>


				<div class="container">
					
					<div class="page-content maincontent resources">
						<div class="col-md-12"><div class="page-title"><h1>Grand Day Submission Form</h1></div></div>

						<form method='post'>
						
						<div class="col-md-7">
							<div class="content">

								<?php if(empty($_POST)){ ?>

								<p class="pr90">Submit your Grand Day Submissions below. Simply put in your Grand Day gross profit, and the date that you had your GRAND DAY.  The Grand Day slide will be updated at the end of the week (SUNDAY) and will run throughout the following Week.</p> 
								<h3 class="green">Congratulations on your GRAND DAY!</h3>
								<p class="lab">Date:</p>
								<div class="date-picker style2" data-date-format="mm/dd/yyyy"> </div>
								<input type="hidden" name="Date" id="idvs3qrfaddressField"/>
								<input type="text" name="Name" class="s15nameField"	placeholder="Name:" id="idvs3qrfnameField"/>
								<input	type="text" name="Email" class="s15_required s15emailField" placeholder="Email:" id="idvs3qrfemailField"/>
								<div class="gross">
									<p class="lab">Enter Gross Profit:</p>
									<p><div class="profitbox">
										<div class="cntn">
											<input placeholder="" type="Profit"/>
										</div>
									</div>
									</p>
									<a href="javascript:void(0)" class="btn greenbtn large">SUBMIT MY GRAND DAY </a>
								</div>
								
								</form>

								<?php } else {?>
								<div class='font_8' style='font-size: 16px; color: white;'><b>Submission has been made.</b></div>
								<br style='clear: both;' />
								<?php }?>
								
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
