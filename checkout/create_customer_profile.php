<?php
require("../database.php");
require("../secure.php");
require('authnet/AuthorizeNet.php');

define("AUTHORIZENET_LOG_FILE", "phplog");

define("AUTHORIZENET_API_LOGIN_ID", MERCHANT_LOGIN_ID);
define("AUTHORIZENET_TRANSACTION_KEY", MERCHANT_TRANSACTION_KEY);
define("AUTHORIZENET_SANDBOX", MERCHANT_SANDBOX);

$refId = 'ref' . time();

$paymentProfile = new AuthorizeNetPaymentProfile;
$paymentProfile->customerType = "individual";
$paymentProfile->payment->creditCard->cardNumber = $_POST['cc_number'];
$paymentProfile->payment->creditCard->expirationDate = $_POST['cc_exp_year']."-".$_POST['cc_exp_month'];

// Create the Bill To info
$paymentProfile->billTo->firstName = $_POST['first_name'];
$paymentProfile->billTo->lastName = $_POST['last_name'];
$paymentProfile->billTo->company = $_POST['company'];
$paymentProfile->billTo->address = $_POST['address'];
$paymentProfile->billTo->city = $_POST['city'];
$paymentProfile->billTo->state = $_POST['state'];
$paymentProfile->billTo->zip = $_POST['zip'];
$paymentProfile->billTo->country = $_POST['country'];

$paymentProfile->customerType = "individual";

# if NO CUSTOMER PROFILE, MAKE ONE
if (empty($_POST['customer_profile_id'])){

	$customerProfile = new AuthorizeNetCustomer;
	$customerProfile->description = "login_id=".checkSession();
	$customerProfile->merchantCustomerId = time().rand(1,150);
	$customerProfile->email = $_POST['email'];

	# add Payment Profile to delivery
	array_push($customerProfile->paymentProfiles,$paymentProfile);

	$request = new AuthorizeNetCIM;
	$response = $request->createCustomerProfile($customerProfile);

	if (($response != null) && ($response->xml->messages->resultCode == "Ok") )
	{
		echo "SUCCESS: PROFILE ID : " . $response->getCustomerProfileId() . "\n";

		$sql = "INSERT INTO login_customerprofiles SET login_id=".checkSession().",customer_profile_id='".$response->getCustomerProfileId()."'";
		$query = mysql_query($sql);

		$intCustomerProfileID = $response->getCustomerProfileId();

	}
	else
	{
		echo "ERROR: " . $response->xml->messages->message->text . "\n";
	}

} else {

	$intCustomerProfileID = $_POST['customer_profile_id'];

}

if (!empty($_POST['customer_profile_id'])){

	$request = new AuthorizeNetCIM;
	$response = $request->createCustomerPaymentProfile($intCustomerProfileID, $paymentProfile);

	$refId = 'ref' . time();

	if (($response != null) && ($response->xml->messages->resultCode == "Ok") )
	{
	#	echo "Create Customer Payment Profile SUCCESS: " . $response->getCustomerPaymentProfileId() . "\n";
	}
	else
	{
#		echo "Create Customer Payment Profile: ERROR Invalid response\n";
#		print_r($response);
		echo "ERROR: " . $response->xml->messages->message->text . "\n";
	#	Header("location: add_profile.php?e=1");
		die();
	}
}
