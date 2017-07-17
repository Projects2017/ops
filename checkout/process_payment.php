<?php
require('../database.php');
require('../secure.php');
require('authnet/AuthorizeNet.php');

define("AUTHORIZENET_LOG_FILE", "phplog");

define("AUTHORIZENET_API_LOGIN_ID", MERCHANT_LOGIN_ID);
define("AUTHORIZENET_TRANSACTION_KEY", MERCHANT_TRANSACTION_KEY);
define("AUTHORIZENET_SANDBOX", MERCHANT_SANDBOX);

require("../inc_content.php");
  

function chargeCustomerProfile($profileid, $paymentprofileid, $amount){
	global $userid;
    // Common setup for API credentials

	$request = new AuthorizeNetCIM;

	$transaction = new AuthorizeNetTransaction;
	$transaction->amount = $amount;
	$transaction->customerProfileId = $profileid;
	$transaction->customerPaymentProfileId = $paymentprofileid;

	$response = $request->createCustomerProfileTransaction("AuthCapture", $transaction);
	$transactionResponse = $response->getTransactionResponse();
	$transactionId = $transactionResponse->transaction_id;

	$strPaymentStatus = "";

	$po_real = $_POST['po_id'];
	$po_newid = $_POST['po_id'] - 1000;

    if ($response != null)
    {

			$tresponse = $response->getTransactionResponse();

      if($response->xml->messages->resultCode == "Ok")
      {
        
	        $strPaymentStatus = "approved";
			submitCreditFee(intval($userid),'c','PO# '.$po_real."\nTransaction ID: ".$transactionId,$amount,time());

#        }
#        else
#        {
#          echo "Transaction Failed \n";
#          if($tresponse->getErrors() != null)
#          {
#            echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
#            echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";            
#          }

#	        $strPaymentStatus = "declined";

      }
      else
      {
#        echo "Transaction Failed \n";
#        $tresponse = $response->getTransactionResponse();
 #       if($tresponse != null && $tresponse->getErrors() != null)
 #       {
#          echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
#          echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";                      
 #       }
  #      else
   #     {
#          echo " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
#          echo " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
    #    }

	        $strPaymentStatus = "declined";

      }

    }
    else
    {
      $strPaymentStatus = "declined";
#      echo  "No response returned \n";
    }

	# adjust PO # to match processing code (inc_content.php)

	$sql = "INSERT INTO po_payments SET login_id=".checkSession().", po_id=".$po_newid.",payment_amount='".$amount."',payment_status='".$strPaymentStatus."'";
	$query = mysql_query($sql);

	$sqlTotalPayments = mysql_query("SELECT SUM(payment_amount) as totalPayment from po_payments WHERE po_id = '".$po_newid."' AND payment_status='approved';");
	$totalPayments = mysql_fetch_row($sqlTotalPayments);
	$totalPayments = $totalPayments[0];
	$sqlTotal = mysql_query("SELECT total from order_forms WHERE ID = '".$po_newid."';");
	$total = mysql_fetch_row($sqlTotal);
	$total = $total[0];

	if ($total == $totalPayments){
		$sqlTotal = mysql_query("UPDATE order_forms set paid=1 WHERE ID = '".$po_newid."';");
		$sqlTotal = mysql_query("UPDATE orders set paid=1 WHERE po_id = '".$po_newid."';");
	}

	if ($strPaymentStatus == "declined") {
		echo '<font style="color:#aa0000;">'.strtoupper($strPaymentStatus).'</font>';
		print_r($response);
	} else {
		echo '<font style="color:#00aa00;">'.strtoupper($strPaymentStatus).'</font>';
	}
	die();

    return $response;
  }

  if(!defined('DONT_RUN_SAMPLES'))
    chargeCustomerProfile($_POST['customer_profile_id'],$_POST['payment_profile_id'],$_POST['payment_amount']);
