<?php
require("../database.php");
require("../secure.php");
require('authnet/AuthorizeNet.php');

define("AUTHORIZENET_LOG_FILE", "phplog");

define("AUTHORIZENET_API_LOGIN_ID", MERCHANT_LOGIN_ID);
define("AUTHORIZENET_TRANSACTION_KEY", MERCHANT_TRANSACTION_KEY);
define("AUTHORIZENET_SANDBOX", MERCHANT_SANDBOX);
  
# get customer profile from user
$sql = "SELECT * FROM login_customerprofiles WHERE login_id = ".checkSession();
checkdberror($sql);
$que = mysql_query($sql);
$objCustomerProfile = mysql_fetch_assoc($que);

function deleteCustomerPaymentProfile($customerProfileId,$customerpaymentprofileid)
{

	$request = new AuthorizeNetCIM;
	$response = $request->deleteCustomerPaymentProfile($customerProfileId, $customerpaymentprofileid);

	if (($response != null) && ($response->xml->messages->resultCode == "Ok") )
	{
		# success
	}
	else
	{
		$errorMessages = $response->xml->messages->message->text;
	}
	return $response;
}

deleteCustomerPaymentProfile($objCustomerProfile['customer_profile_id'],$_REQUEST['payment_profile_id']);
