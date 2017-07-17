$(function(){

	console.log("Checkout Script Loaded.");

	$(document).on("click", '#btnPayment', function(event) { 
	  event.preventDefault();
	  loadPaymentScreen();
	});

	$(document).on("click", '#btnCreditAccount', function(event) { 
	  event.preventDefault();
	  loadCreditScreen($(this).attr("data-user-id"));
	});

	$(document).on("blur", '.paymentAmountBox', function(event) { 
		calcTotals();
	});
	
	$(document).on("click", '#btnApplyCredit', function(event) { 
		if ($("#credit_total").val() == ''){
		 alert("You must enter a credit amount.");
		} else {
			$.post("/checkout/apply_credit.php", {userid: $("#userid").val(), credit_comments: $("#credit_comments").val(), credit_total: $("#credit_total").val()}, function(html) {
				window.location.reload();
			});
		}
	});

	$(document).on("click", '#btnMakePayment', function(event) { 

//		if (totalDue > 0){
//			alert("Please enter a complete payment amount. \n($"+totalDue+" still due)");
//		} else {
			if (totalDue < 0){
				alert("Your balance shows a credit. Please only pay up to the total due.");
			} else {
				if (totalPayment == 0){
					alert("Please enter a payment amount.");
				} else {
					$(this).prop("disabled","disabled");
					processPayments();
				}
			}
//		}
	});

	$('body').on($.modal.AFTER_CLOSE, function(event, modal) {
		$(".modal").remove();
//		window.location.reload();
	});

	if ($("#grandTotal").html()){
		totalBalance = $("#grandTotal").html().replace(/(<([^>]+)>)/ig,"");
		totalDue = totalBalance;
	}
});

function loadPaymentScreen(){
  $.get("/checkout/payment.php", {balance: totalBalance}, function(html) {
	$(html).appendTo('body').modal({escapeClose: false,clickClose: false});
  });
}

function loadCreditScreen(userid){
  $.post("/checkout/credit.php", {balance: totalBalance, userid: userid}, function(html) {
	$(html).appendTo('body').modal({escapeClose: false,clickClose: false});
  });
}

function addProfile(){
//	$.modal.close();
	$.get("/checkout/add_profile.php", function(html) {
		$(html).appendTo('body').modal({escapeClose: false,clickClose: false});
	});
}

function calcTotals(){
	totalDue = totalBalance;
	totalPayment = 0;
	$('.paymentAmountBox').each(function(i, obj) {
		console.log($(this).val());
		if ($(this).val() != ""){
			totalDue -= $(this).val();
			totalPayment += parseFloat($(this).val());
		}
	});
	
//	console.log(totalPayment);
	
	$("#totalDue").html("$"+totalDue.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
	$("#totalPayment").html("$"+totalPayment.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
}

function addNewProfile(){

		$("#formMsg").hide();
		var missing = 0;

		$('.required').each(function(i, obj) {
			if ($(this).val() == ""){
				missing++;
				$("#formMsg").html("All fields marked with * are required.");
				$("#formMsg").show("fast");
				return false;
			}
		});
	
		if (missing > 0){
			return false;
		} else {
			$.post( "/checkout/create_customer_profile.php", { 
				first_name: $("#first_name").val(), 
				last_name: $("#last_name").val(), 
				company: $("#company").val(), 
				address: $("#address").val(), 
				city: $("#city").val(), 
				state: $("#state").val(), 
				email: $("#email").val(), 
				cc_number: $("#cc_number").val(), 
				cc_exp_month: $("#cc_exp_month").val(), 
				cc_exp_year: $("#cc_exp_year").val(), 
				country: $("#country").val(),
				customer_profile_id: $("#customer_profile_id").val()
				})
			
				.done(function( data ) {
				
				console.log(data);
				
					if (data.indexOf('ERROR:') > -1){
						$("#formMsg").html(data);
						$("#formMsg").show("fast");
					} else {
						console.log('OK');
						$.modal.close();
						$.get("/checkout/payment.php", {balance: totalBalance}, function(html) {
							$(html).appendTo('body').modal({escapeClose: false,clickClose: false});
						});
					}
			});
		}
}

function confRemove(i){
	if (confirm("Are you sure you want to remove this payment profile?")){
		$.post( "/checkout/delete_payment_profile.php", { payment_profile_id: i })
			.done(function( data ) {
			   $.modal.close();
			  $.get("/checkout/payment.php", {balance: totalBalance}, function(html) {
				$(html).appendTo('body').modal({escapeClose: false,clickClose: false});
			  });
		});
	}
}

var numPaymentAmountsProcessed;

function processPayments(){
	numPaymentAmounts = 0;
	$('.paymentAmountBox').each(function(i, obj) {
		if ($(this).val() != "" && $(this).val() != "0" && $(this).val() != "0.00"){
			numPaymentAmounts++;
		}
	});
	console.log('amtlen='+numPaymentAmounts);
	numPaymentAmountsProcessed = 0;
	infoText = "Processing Payment... Please Wait...<br><br>";
	$("#info").html(infoText);
	$('.paymentAmountBox').each(function(i, obj) {
		if ($(this).val() != "" && $(this).val() != "0" && $(this).val() != "0.00"){
			// process payment
			console.log('process customer_id '+$("#customer_profile_id").val()+' with payment_profile_id '+$(this).attr('data-profile-id')+' for amount of '+$(this).val());
			$.post( "/checkout/process_payment.php", { total_balance: totalBalance, po_id: orderFormID, customer_profile_id: $("#customer_profile_id").val(), payment_profile_id: $(this).attr('data-profile-id'), payment_amount: $(this).val() })
				.done(function( data ) {
				console.log(data);
				dispNumber = numPaymentAmountsProcessed+1;
				infoText += "Processing Card ("+dispNumber+" of "+numPaymentAmounts+")...";
				infoText += " "+data+".<bR>";
				numPaymentAmountsProcessed++;
				if (numPaymentAmountsProcessed == numPaymentAmounts){
					infoText += "<br><br>Done Processing... Refreshing...<bR>";
//					infoText += data;

				setTimeout(
				  function() 
				  {
					//do something special
						if (forwardToDetails && forwardToDetails==1){
							window.location.href = "/orders-details.php?po="+orderFormID;
						} else {
							window.location.reload();
						}
				  }, 3000);


				}
				$("#info").html(infoText);
			});
		}
	});
}