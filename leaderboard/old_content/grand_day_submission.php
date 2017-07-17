<?php
require ("../database.php");
require ("../secure.php");
require ("../form.inc.php");
require ("../announce.inc.php");
include ('../include/BigBoard.php');
if(!empty($_POST)){
    $msg = "Name: ".$_POST['Name']."\r\n";
    $msg .= "Email: ".$_POST['Email']."\r\n";
    $msg .= "Profit: ".$_POST['Profit']."\r\n";
    $msg .= "Date: ".$_POST['Date']."\r\n";
    
    $headers = 'From: noreply@boxdropbigboard.com' . "\r\n" .
        'Reply-To: noreply@boxdropbigboard.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail(BigBoard::$email,"Grand Day Submission Form",$msg,$headers);
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
<div class='content_container'>

	<br style='clear: both;' />
	<div style="float: left;">
		<br style='clear: both;' />
		<h2 class="font_2" style="font-size: 30px;">
			<span style="font-size: 30px;"><span
				style="text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.498), -1px 1px 0px rgba(0, 0, 0, 0.498), 1px 1px 0px rgba(0, 0, 0, 0.498), 1px -1px 0px rgba(0, 0, 0, 0.498);"><span
					style="font-weight: bold;"><span class="color_11">&nbsp;Grand Day
							Submision Form</span></span></span></span>
		</h2>
	</div>
	<br style='clear: both;' /> <br style='clear: both;' /> <br
		style='clear: both;' /> <br style='clear: both;' />
	<?php if(empty($_POST)){ ?>

	<?php BigBoard::printContentBlock(7,'above_form'); ?>

	<form method='post'>
	<div class="s15" >
			<input type="text" name="Name" class="s15nameField"	placeholder="Name:" id="idvs3qrfnameField"/>
			<input	type="text" name="Email" class="s15_required s15emailField" placeholder="Email:" id="idvs3qrfemailField"/>
			<input type="text" name="Profit" class="s15phoneField" placeholder="Grand Day Gross Profit:" id="idvs3qrfphoneField"/>
			<input type="text" name="Date" class="s15addressField" placeholder="Date:" id="idvs3qrfaddressField"/>
			
			<button id="idvs3qrfsubmit" class="s15submit">Send</button>
	</div> 
	</form>
	<?php } else {?>
	<div class='font_8' style='font-size: 16px; color: white;'><b>Submission has been made.</b></div>
	<br style='clear: both;' />
	<?php }?>
</div>
<br style='clear:both;'/>
<br style='clear:both;'/>

<?php include 'includes/footer.php';?>
