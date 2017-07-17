<?php
require('../database.php');
require('../secure.php');

# get customer profile from user
$sql = "SELECT * FROM login_customerprofiles WHERE login_id = ".checkSession();
checkdberror($sql);
$que = mysql_query($sql);
$objCustomerProfile = mysql_fetch_assoc($que);
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

<input type="hidden" id="customer_profile_id" name="customer_profile_id" value="<?php=$objCustomerProfile['customer_profile_id']?>"/>

<center>
<br><br>
<b>Add Payment Method</b>
<br>
<font style="font-size:11px;">* Required field</font>
	<br>
	
	<?php if ($_REQUEST['e']==1){
	?>
	<br><br>
	<font style="color:#aa0000;">
	An error occurred when adding the payment method.
	</font>
	<?php
	}
	?>
<table>
	<tr>
		<td valign="top">
		<Br>
		<table>
			<tr>
				<td>
				<label>First Name*</label>
				</td>
				<td>
				<input type="text" name="first_name" id="first_name" class="required"/>
				</td>
			</tr>
			<tr style="background:#f5f5f5;">
				<td>
				<label>Last Name*</label>
				</td>
				<td>
				<input type="text" name="last_name" id="last_name" class="required"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>Company</label>
				</td>
				<td>
				<input type="text" name="company" id="company"/>
				</td>
			</tr>
			<tr style="background:#f5f5f5;">
				<td>
				<label>Address*</label>
				</td>
				<td>
				<input type="text" name="address" id="address" class="required"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>City*</label>
				</td>
				<td>
				<input type="text" name="city" id="city" class="required"/>
				</td>
			</tr>
			<tr style="background:#f5f5f5;">
				<td>
				<label>State*</label>
				</td>
				<td>
				<input type="text" name="state" id="state" size="2" maxlength="2" style="width:50px;" class="required"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>ZIP*</label>
				</td>
				<td>
				<input type="text" name="zip" id="zip"maxlength="10" class="required"/>
				</td>
			</tr>
			<tr style="background:#f5f5f5;">
				<td>
				<label>Email</label>
				</td>
				<td>
				<input type="text" name="email" id="email"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>Card Number*</label>
				</td>
				<td>
				<input type="text" name="cc_number" id="cc_number" class="required"/>
				</td>
			</tr>
			<tr style="background:#f5f5f5;">
				<td>
				<label>Card Exp*</label>
				</td>
				<td>
				<select name="cc_exp_month" id="cc_exp_month" class="required">
					<option value="01">01</option>
					<option value="02">02</option>
					<option value="03">03</option>
					<option value="04">04</option>
					<option value="05">05</option>
					<option value="06">06</option>
					<option value="07">07</option>
					<option value="08">08</option>
					<option value="09">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
				</select>
				&nbsp;
				<select name="cc_exp_year" id="cc_exp_year" class="required">
					<option value="2016">2016</option>
					<option value="2017">2017</option>
					<option value="2018">2018</option>
					<option value="2019">2019</option>
					<option value="2020">2020</option>
					<option value="2021">2021</option>
					<option value="2022">2022</option>
					<option value="2023">2023</option>
					<option value="2024">2024</option>
					<option value="2025">2025</option>
				</select>
				</td>
			</tr>
			<tr>
				<td>
				<label>Country</label>
				</td>
				<td>
				<input type="text" name="country" id="country"/>
				</td>
			</tr>
			<tr>
				<td>
				&nbsp;
				</td>
				<td>
				<input type="button" onClick="parent.addNewProfile();" value="Add Payment Method"/> <input type="button" onClick="parent.loadPaymentScreen();" value="Cancel"/>
				<div id="formMsg" style="color:#aa0000;font-size:11px;font-weight:bold;margin-top:10px;padding:5px;background:#ffdde2;display:none;"></div>
				</td>
			</tr>
		</table>
		</td>
<!--
		<td valign="top">
		<b>SHIPPING INFO</b>
		<Br>
		<table>
			<tr>
				<td>
				<label>First Name</label>
				</td>
				<td>
				<input type="text" name="ship_first_name"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>Last Name</label>
				</td>
				<td>
				<input type="text" name="ship_last_name"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>Company</label>
				</td>
				<td>
				<input type="text" name="ship_company"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>Address</label>
				</td>
				<td>
				<input type="text" name="ship_address"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>City</label>
				</td>
				<td>
				<input type="text" name="ship_city"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>State</label>
				</td>
				<td>
				<input type="text" name="ship_state"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>ZIP</label>
				</td>
				<td>
				<input type="text" name="ship_zip"/>
				</td>
			</tr>
			<tr>
				<td>
				<label>Country</label>
				</td>
				<td>
				<input type="text" name="country"/>
				</td>
			</tr>
			<tr>
				<td>
				&nbsp;
				</td>
				<td>
				<input type="submit" value="Add Profile"/>
				</td>
			</tr>
		</table>
		</td>
-->
	</tr>
</table>
</center>

</div>