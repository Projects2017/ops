<?php
require('database.php');
require('secure.php');
require_once('include/cellprovider.class.php');
require_once('include/sms.php');
require_once('form.inc.php');
########
# INFO #
########

/*

Title:
SMS/Text Messenger (I Guess?)
Call it whatever you'd like...

Author:
Eric O'Callaghan (eric1207@gmail.com)

Date Created:
August 5, 2005 (Friday)

Last Updated:
February 21, 2007 (Wednesday)

Requirements:
PHP
SMTP

Installation:
1. You'll have to put this on your website
2. You can create a CSS file to style the page if you'd like (sms.css by default, or you can change the filename at the top of this script)
A good example is at http://sms.eric1207.com/sms.css
3. It's suggested to try sending a message with it
4. Do whatever

Notes:
Feel free to edit this script, but if you've found a great
way to improve upon it, go ahead and email me (eric1207@gmail.com)
with your changes and I'll update it on the website (http://sms.eric1207.com)
and give you plenty of credit.

Credit:
Talk to the great people over at Teleflip.Com also if you'd like to
praise them for their awesome service.

Enjoy..
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="/include/sms.css">
<title>RSS Open Order Notification</title>
</head>
<body>
<center>
<h3><u>RSS Open Order Text Notification</u></h3><br><br>
<?php
$claimID = $_GET['updateid'];
// Get the record and related info.
if(!isset($_POST['do'])) {
    $record = formdata('order',0,array('id' => $claimID));
    $fieldprop = forminfo('order');
    $record = $record[0];
    $dealer = db_user_getuserinfo($record['user_id']);
    $vendor = db_vendor_getinfo($record['vendor_id']);
}
############################
# BE CAREFUL EDITING BELOW #
############################

// Process the form if it was submitted
if (isset($_POST['do'])) {

    // Parse the data submitted from the form
    $number = trim($_POST['number']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $provider = $_POST['provider'];
	$email = trim($_POST['email']);
    if($sent == 'true') {
		 ?>
		<script type="text/javascript" language="JavaScript">
			// window.opener.document.getElementById('claim<?php=$claimID ?>').bgColor = '#FFFFFF';
			window.opener.document.getElementById('smsAdmin<?php=$claimID ?>').src = 'images/cellPhone_sent.gif';
			self.close();
		</script>
<?php
	}
	

	


	include_once('form.inc.php');
	$emails = array();
	if ($email) $emails[] = $email;
	if ($email2) $emails[] = $email2;
	if ($email3) $emails[] = $email3;
	
	formemail('order', $claimID, $emails);
	// need to pass the cell phone provider of the number in question to provide the correct email address
	
	if (sms_send($number,$subject,$message,'sms@retailservicesystems.com', $provider)) {
		echo "Text message sent.\n";
	} else {
		$smserror = sms_error();
	}
	$sql = "UPDATE `claim_order` SET `upsincesms` = '2' WHERE `id` = '".$claimID."'";
	mysql_query($sql);
	checkDBerror($sql);
}

// Show any errors encountered
if (isset($smserror)) {
    echo "<font color=\"red\"><b>" . $smserror . "</b></font><br><br>\n";
}

########
# FORM #
########
?>
<form method="POST" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>?updateid=<?php echo $claimID; ?>">
<strong>Dealer Number (Cell): </strong>&nbsp;<?php= $dealer['cell_phone'] ?><br />
<?php $cellPhone = str_replace("-","",$dealer['cell_phone']); ?>
<input type="hidden" name="number" maxlength="10" value="<?php= $cellPhone ?>">
<strong>Cell Provider: </strong>&nbsp;<?php
// grab the provider's name
$provider = new cellProvider($dealer['cell_provider']);
echo $provider->getName();
?><br />
<strong>Dealer E-Mail: </strong>&nbsp;<?php= $dealer['email'] ?>,&nbsp;<?php= $dealer['email2'] ?>,&nbsp;<?php= $dealer['email3'] ?><br />
<input type="hidden" name="email" value="<?php= $record['email'] ?>">
<?php $message = formsmsformat('order', $claimID); ?>
<strong>=Message=</strong>
<div></div>
<input type="hidden" name="subject" maxlength="10" value="RSS SMS Notification">
<input type="hidden" name="provider" value="<?php= $provider->getCode() ?>">
<textarea name="message" cols="40" rows="7">
<?php= htmlentities($message) ?>
</textarea>
<?php
//<input type="hidden" name="message" maxlength="10" value="<?php= $message ">
?>
<input type="hidden" name="sent" maxlength="10" value="true">
<br>
<input type="submit" name="do" value="Send">
</form>
</center>
</body>
</html>
