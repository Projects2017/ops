// JavaScript for BoL system
// credit.js

function recalcrow(rownum, setamt) {
  setid = 'set_' + rownum;
  mattid = 'matt_' + rownum;
  boxid = 'box_' + rownum;
  totalid = 'linetotalpcs' + rownum;
  if (document.getElementById(setid).value!=0) {
    if (isNaN(document.getElementById(setid).options[document.getElementById(setid).selectedIndex].value)) {
      alert("Credited amounts must be a number.");
      return false;
    }
    var settot = parseInt(document.getElementById(setid).options[document.getElementById(setid).selectedIndex].value) * setamt;
  } else {
    var settot = 0;
  }
  if (document.getElementById(mattid).value!=0) {
    if (isNaN(document.getElementById(mattid).options[document.getElementById(mattid).selectedIndex].value)) {
      alert("Credited amounts must be a number.");
      return false;
    }
    var matttot = parseInt(document.getElementById(mattid).options[document.getElementById(mattid).selectedIndex].value);
  } else {
    var matttot = 0;
  }
  if (document.getElementById(boxid).value!=0) {
    if (isNaN(document.getElementById(boxid).options[document.getElementById(boxid).selectedIndex].value)) {
      alert("Credited amounts must be a number.");
      return false;
    }
    var boxtot = parseInt(document.getElementById(boxid).options[document.getElementById(boxid).selectedIndex].value);
  } else {
    var boxtot = 0;
  }
  document.getElementById(totalid).innerHTML = parseInt(settot + matttot + boxtot);
  var linetotals = document.getElementsByName('linetotalpcs');
  var formtot = 0;
  for (i=0; i<linetotals.length; i++) {
     formtot = formtot + parseInt(linetotals[i].innerHTML);
  }
  document.getElementById('totalpcs').innerHTML = formtot;
  setid = "";
  mattid = "";
  boxid = "";
  totalid = "";
}

function printBoL() {
  var printbtn = document.getElementById('printbtn');
  printbtn.innerHTML = '<td colspan="7" class="text_16" style="vertical-align: top; font-weight: bold; font-size: 24px" align="center">SHIPPER COPY</td>';
  window.print();
  alert("Click to print the merchant's copy.");
  printbtn.innerHTML = '<td colspan="7" class="text_16" style="vertical-align: top; font-weight: bold; font-size: 24px" align="center">MERCHANT COPY</td>';
  window.print();
  alert("Completed printing.")
}

function EdiCreditVerify()
{
	var check = confirm("This order should only be credited if contacted directly from the retailer. Do NOT credit without prior authorization.");
	return check;
}

function EdiSendCancelVerify()
{
	var currentStatus = document.do_addcredit.sendedifile.checked;
	if(currentStatus == false)
	{
		var check = confirm("Response files should be sent for all manual cancellations. Are you sure you want to not send a file?");
		if(check == false)
		document.do_addcredit.sendedifile.checked = true;
		return;
	}
	else
	{
		return true;
	}
}

function printCredit() {
  window.print();
}