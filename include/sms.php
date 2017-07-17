<?php
require_once('cellprovider.class.php'); // has cell provider email addressing info

function sms_send($number, $subject, $message, $from, $provider)
{
	// Teleflip Service no longer exists, just return success
	// return true;
	if($provider == 'oth')
	{
		// provider information hasn't been set, so we'll default to not sending a thing
		$gSmsError = "Provider has not been setup. Please choose the cell phone provider in the Dealer's information page in order to enable sending SMSs.";
		return false;
	}
	// Check that the phone number is numeric
    if (!is_numeric($number)) {
        $gSmsError = "Please enter a phone number containing only digits 0-9.";
		return false;
    // Make sure the phone number doesn't start with 911
    } elseif (eregi("^(911)(.*)$", $number)) {
        $gSmsError = "Please enter a valid phone number.";
		return false;
    // Check that the phone number is 10 digits
    } elseif (strlen($number) != "10") {
        $gSmsError = "Please enter a phone number that is 10 digits (the 3 digit area code and 7 digit number).";
		return false;
    // Check that the subject doesn't contain HTML characters
    } elseif (eregi("^(.*)(<|>)(.*)$", $subject)) {
        $gSmsError = "The subject of the message cannot contain HTML characters (such as &gt; or &lt;).";
		return false;
    // Check that the message doesn't contain HTML characters
    } elseif (eregi("^(.*)(<|>)(.*)$", $message)) {
        $gSmsError = "The message cannot contain HTML characters (such as &gt; or &lt;).";
		return false;
    // Check that the subject is between 3 and 20 characters
    } elseif ((strlen($subject) < "3") || (strlen($subject) > "20")) {
        $gSmsError = "The subject must be between 3 and 20 characters in length.";
		return false;
	} else {
        // Where are we sending it?
        // get the provider information
        $cellprovider = new cellProvider($provider);
        $to = $number .'@'. $cellprovider->getEmail();
		//echo "to: $to, subject: $subject, message: $message";
		//die();
        // Send the text message (via Teleflip's service)
        if (@sendmail($to, $subject, $message)) {
            // Give a success notice
			return true;
        // Give an error that message can't be sent
        } else {
            $gSmsError = "Sorry, there was an error while trying to send the message - please try again later.";
			return false;
        }
    }
}

function sms_error() {
	if(!isset($GLOBALS['gSmsError'])) {
		return false;
	} else {
		return $GLOBALS['gSmsError'];
	}
}

function sms_errorclear() {
	unset($GLOBALS['gSmsError']);
}
