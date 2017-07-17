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
Submit Payment
<br><br>

<div id="paymentBox">

<table width="400">

<?php if (count($arrPaymentProfiles)>0){ ?>
<input type="hidden" id="customer_profile_id" value="<?php=$profileIdRequested?>"/>

<tr style="background:#f5f5f5;">
	<td><b>Payment Method</b></td>
	<td><b>Amount to Pay</b></td>
</tr>

<?php

		foreach ($arrPaymentProfiles as $paymentProfile){
?>

<tr>

<td class="paymentRow">
Credit Card <?php=$paymentProfile['ccNumber']?> (<a href="javascript:confRemove('<?php=$paymentProfile['profileID']?>');">remove</a>)
</td>


<td align="right" class="paymentRow">
<input type="text" class="paymentAmountBox" data-profile-id="<?php=$paymentProfile['profileID']?>" data-card-number="<?php=$paymentProfile['ccNumber']?>" style="text-align:right;"/>
</td>

</tr>
<?php } ?>


<tr style="background:#eee;">
	<td>
	<b>TOTAL</b>
	</td>
	<td align="right">
	<div id="totalAmount">$<?php=number_format($balance, 2, '.', '');?></div>
	</td>
</tr>	

<tr style="background:#eee;">
	<td>
	<b>TOTAL PAYMENT</b>
	</td>
	<td align="right">
	<div id="totalPayment">$0.00</div>
	</td>
</tr>	

<tr style="background:#eee;">
	<td>
	<b>BALANCE DUE</b>
	</td>
	<td align="right">
	<div id="totalDue">$<?php=number_format($balance, 2, '.', '');?></div>
	</td>
</tr>	

<?php } else {?>
<tr>
	<td colspan="2">
	<center>
No payment methods defined.
	</center>
	</td>
</tr>
<?php } ?>
<tr>

<td colspan="2">
<center>
<a href="javascript:addProfile();">Add Payment Method</a>
</center>
</td>

</tr>

</table>

</div>

<?php if (count($arrPaymentProfiles)>0){ ?>

<br><br>
<button id="btnMakePayment">Make Payment(s)</button>
<Br>
<script type='text/javascript' src='https://www.rapidscansecure.com/siteseal/siteseal.js?code=65,C934EFCA8C0DC9D7ABA80B659C434D15B3F6F9B1'></script>
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
