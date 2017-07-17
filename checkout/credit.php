<?php

require('../database.php');
require('../secure.php');
require('authnet/AuthorizeNet.php');

$balance = $_GET['balance'];

define("AUTHORIZENET_LOG_FILE", "phplog");

# get customer profile from user
$sql = "SELECT * FROM login_customerprofiles WHERE login_id = ".checkSession();
checkdberror($sql);
$que = mysql_query($sql);
$objCustomerProfile = mysql_fetch_assoc($que);

if ($objCustomerProfile){
	$profileIdRequested = $objCustomerProfile['customer_profile_id']; 
	$arrPaymentProfiles = array();

	define("AUTHORIZENET_API_LOGIN_ID", MERCHANT_LOGIN_ID);
    define("AUTHORIZENET_TRANSACTION_KEY", MERCHANT_TRANSACTION_KEY);
	define("AUTHORIZENET_SANDBOX", MERCHANT_SANDBOX);

	$refId = 'ref' . time();

	// Retrieve an existing customer profile along with all the associated payment profiles and shipping addresses
	$request = new AuthorizeNetCIM;
	$response = $request->getCustomerProfile($profileIdRequested);

	if (($response != null) && ($response->xml->messages->resultCode == "Ok") )
	{

		$paymentProfilesSelected = $response->xml->profile->paymentProfiles;

		foreach ($paymentProfilesSelected as $paymentProfile){
			$curPaymentProfile = array();
			$curPaymentProfile['profileID'] = $paymentProfile->customerPaymentProfileId;
			$curPaymentProfile['ccNumber'] = $paymentProfile->payment->creditCard->cardNumber;
			$curPaymentProfile['ccFirstName'] = $paymentProfile->billTo->firstName;
			$curPaymentProfile['ccLastName'] = $paymentProfile->billTo->lastName;
			array_push($arrPaymentProfiles, $curPaymentProfile);
		}

	}
	else
	{
		echo "ERROR: " . $response->xml->messages->message->text . "\n";
	}
}

?>

<div class="modal">

<style>

td{
	padding: 10px;
}

input{
	font-size: 18px;
	padding: 3px;
}

body{
	font-family: Arial;
}

.paymentRow{
	border-bottom: 1px solid #ccc;
}

</style>

<center>
Submit Credit
<br><br>

<div id="paymentBox">

<table width="400">

<input type="hidden" id="userid" value="<?php=$_POST['userid']?>"/>

<tr style="background:#eee;">
	<td>
	<b>CURRENT BALANCE</b>
	</td>
	<td align="right">
	<div id="totalDue">$<?php=number_format($balance, 2, '.', '');?></div>
	</td>
</tr>	

<tr style="background:#eee;">
	<td>
	<b>CREDIT AMOUNT</b>
	</td>
	<td align="right">
	<input type="text" class="form-control" id="credit_total"/>
	</td>
</tr>	

<tr style="background:#eee;">
	<td>
	<b>COMMENTS</b>
	</td>
	<td align="right">
	<input type="text" class="form-control" id="credit_comments"/>
	</td>
</tr>	

</table>

</div>

<?php if (count($arrPaymentProfiles)>0){ ?>

<br><br>
<button id="btnApplyCredit" data-user-id="<?php=$_POST['userid']?>" class="form-control btn" style="font-size:22px;">Apply Credit</button>

<br><BR>
<div id="info"></div>
<div id="paymentscreen"></div>
<?php } ?>

</center>

<script>

var links = document.getElementsByTagName( 'a' );

for( var i = 0, j =  links.length; i < j; i++ ) {
    links[i].setAttribute( 'tabindex', '-1' );
}


var infoText = "";





</script>

</div>
