// JavaScript for BoL system
// bol.js

	var prepaid;
	var freight;
	var multiOrders = new Array();
	var multiOrderUser = "";
	var fromScript = false;
	var canceltypes = new Array('customer_refused','merchant_request');
	var canceldesc = new Array('Customer Refused Delivery',"Cancelled at Merchant's Request");
	
//this function includes all necessary js files for the application  
function include(file)  
{
	var script  = document.createElement('script');
	script.src  = file;
	script.type = 'text/javascript';
	script.defer = true;
	document.getElementsByTagName('head').item(0).appendChild(script);
}
include("../include/printing.js");
  
  // browser detect code
  var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();


function returnToQueue() {
  window.location = 'shipping.php';
}

function checkOrder(order, user)
{
	var add = true;
	for (i in multiOrders)
	{
		if(multiOrders[i]==order)
		{
			multiOrders.splice(i, 1);
			add = false;
		}
	}
	if(add)
	{
		multiOrders.push(order);
	}
	var orderlist = new Array();
	// check the multiOrders orders to see if any are missing picktix; if not, boling
	for(i=0; i<multiOrders.length; i++)
	{
		orderlist[i] = document.getElementById("picktix_"+multiOrders[i]).value;
	}
	orderlist.sort();
	var boling = true;
	for(i=0; i<orderlist.length; i++)
	{
		if(orderlist[i]==0)
		{
			boling = false;
		}
	}
	document.getElementById("checkedorders").value = multiOrders.join(";");
/*
	var thisOrderPicktix = 1;
	thisOrderPicktix = document.getElementById("picktix_"+order).value;
	if(multiOrders.length > 0)
	{
		multiOrderPickTix = (multiOrderPickTix == 0 || thisOrderPicktix == 0) ? 0 : 1;
	}
	else
	{
		multiOrderPickTix = 1;
	}
*/
	document.getElementById("multi_button").innerHTML = boling ? "BoL" : "PickTix";
	document.getElementById("multi_buttontype").value = boling ? "bol" : "picktix";
	if(multiOrders.length > 0)
	{
		document.getElementById("multiorder").style.visibility = "visible";
	}
	else
	{
		document.getElementById("multiorder").style.visibility = "hidden";
	}
}

function setVars()
{
	// run on page load of addbol.php
	// iterate through the items and calculate # of pieces & weight of shipment
	var totalrows = document.getElementById('totalrows').value;
	for (q=1; q<=totalrows; q++)
	{
  		var qlen = q.toString();
  		if (qlen.length<2) { var r = '0'+qlen; } else { var r = qlen; }
  		recalcrow(r, null);
  	}
	// are we in viewonly mode (i.e. pick ticket view)
	try 
	{
		var viewonly = document.getElementById('viewonly');
	}
	catch (err)
	{
		// not in viewonly, so show the prepaid freight amount
		if (isNaN(document.getElementById("prepaid").innerHTML.substring(1)))
		{
			prepaid = parseFloat(document.getElementById("prepaid").innerHTML.substring(1));
		}
		else
		{
			prepaid = "prepaid";
		}
	}
}

function changePackageCount()
{
	var getstring = "";
	for(i=0; typeof(document.addbol['getname'+i]) != undefined; i++)
	{
		getstring += document.addbol['getname'+i].value;
		getstring += '=';
		getstring += document.addbol['getvalue'+i].value;
		getstring += '&';
	}
	getstring = getstring + 'packages=' + document.addbol.packages.value;
	window.location('addbol.php?'+getstring);
}

function recalcrow(rownum, itemid)
{
	if(typeof itemid == 'undefined')
	{
		var resetItem = false;
	}
	else
	{
		var resetItem = true;
	}
	// recalculates the row amounts
	// run for all rows upon page load to update the total # of pieces and total weight
	var setid = 'set_' + rownum;
	var mattid = 'matt_' + rownum;
	var boxid = 'box_' + rownum;
	var lineweight = 'weight_' + rownum;
	var totalrows = document.getElementById('totalrows').value;
	var totalid = 'linetotalpcs' + rownum;
	var settot = 0;
	var matttot = 0;
	var boxtot = 0;
	// run down the item types, calc'ing the pieces shipped for each kind
	if (document.getElementById(setid).value!=0)
	{
    	if (isNaN(document.getElementById(setid).options[document.getElementById(setid).selectedIndex].value))
    	{
    		alert("Shipped amounts must be a number.");
    		return false;
    	}
    	settot = parseInt(document.getElementById(setid).options[document.getElementById(setid).selectedIndex].value) * document.getElementById('setamt_'+rownum).value;
	}
	else
	{
    	settot = 0;
    }
	if (document.getElementById(mattid).value!=0)
	{
    	if (isNaN(document.getElementById(mattid).options[document.getElementById(mattid).selectedIndex].value))
    	{
    		alert("Shipped amounts must be a number.");
			return false;
		}
		matttot = parseInt(document.getElementById(mattid).options[document.getElementById(mattid).selectedIndex].value);
	}
	else
	{
		matttot = 0;
	}
	if (!(document.getElementById(boxid) === null))
	{
		if (document.getElementById(boxid).value != 0)
		{
    		if (isNaN(document.getElementById(boxid).options[document.getElementById(boxid).selectedIndex].value))
    		{
				alert("Shipped amounts must be a number.");
				return false;
			}
    		boxtot = parseInt(document.getElementById(boxid).options[document.getElementById(boxid).selectedIndex].value);
		}
		else
		{
			boxtot = 0;
		}
	}
	// update this line's display & hidden fields w/ the current numbers
	document.getElementById(totalid).innerHTML = parseInt(settot + matttot + boxtot);
	document.getElementById('linetotalweight_'+rownum).value = (matttot + boxtot) * document.getElementById(lineweight).value;
	// update the form's display & hidden fields
	var formtot = 0;
	var formweighttot = 0;
	for (i=1; i<=totalrows; i++)
	{
		var ilen = i.toString();
		if (ilen.length<2) { var j = '0'+ilen; } else { var j = ilen; }
		formtot = formtot + parseInt(document.getElementById('linetotalpcs'+j).innerHTML);
		formweighttot = formweighttot + parseInt(document.getElementById('linetotalweight_'+j).value);
	}
	document.getElementById('totalpcs').innerHTML = formtot;
	document.getElementById('disp_weight').innerHTML = formweighttot;
	document.getElementById('weight').value = formweighttot;
	setid = "";
	mattid = "";
	boxid = "";
	totalid = "";
}

function recalcEdiItems(rownum, itemid)
{
	var itemDropDown = document.getElementById('itemid'+rownum+'_'+itemid);
	if(itemDropDown.options[itemDropDown.selectedIndex].value == '0')
	{
		// chosen the zero, so remove the container selector
		var containers = document.getElementById('containers'+rownum+'_'+itemid);
		containers.style.display = "none";
	}
	else
	{
		// chosen something other than zero, so replace the container selector
		var containers = document.getElementById('containers'+rownum+'_'+itemid);
		containers.style.display = "inline";
		boxtot = 0;
	}
}

function createCookie(name,value,secs) {
	if (secs) {
		var date = new Date();
		date.setTime(date.getTime()+(secs*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function printBoL()
{
	var printbtn = document.getElementById('printbtn');
	printbtn.className = "text_16";
	printbtn.style.verticalAlign = 'top';
	printbtn.style.fontWeight = 'bold';
	printbtn.style.fontSize = '24px';
	printbtn.innerHTML = 'SHIPPER COPY';
	printPage("Click to print the merchant's copy.");
	printbtn.innerHTML = 'MERCHANT COPY';
	printPage("Click to print the packing slip.");
	printbtn.innerHTML = 'PACKING SLIP';
	printPage("Completed printing.");
	returnToQueue();
}

function doPrint()
{
	printPage();
}

function doPrintPickTicket(po_id)
{
	// function which prints twp (2) pick tickets from the shipping queue
	// shows the outstanding items to complete the order
	var buttonzone = document.getElementById('buttonzone');
	buttonzone.className = "text_16";
	buttonzone.style.verticalAlign = 'top';
	buttonzone.style.fontWeight = 'bold';
	buttonzone.style.fontSize = '24px';
	buttonzone.innerHTML = 'PICK TICKET';
	printPage();
	printPage('Completed printing.');
	window.location = '/shipping/picktix_printed.php?id='+po_id;
	//window.location = 'addbol.php?id='+(po_id+1000)+'&viewonly';
}

function doPrintAndClose()
{
	printPage();
	self.close();
}

function printPickTicket(po_id)
{
	// as of 12/3/08, first we print the PO from a separate window
	if (isNaN(po_id))
	{
		var po_nums = po_id.split(";");
		for (var i=0; i<po_nums.length; i++)
		{
			window.open('/viewpo.php?autoprintclose=1&po='+(Number(po_nums[i])+1000),'po_print');
		}
		// should automatically print & self-close
	} else {
		window.open('/viewpo.php?autoprintclose=1&po='+(Number(po_id)+1000),'po_print');
		// should automatically print & self-close
	}
	// now do the regular pick ticket printing
	doPrintPickTicket(po_id);
}

/*
 *function pri http://www.pmddealer.com/d/home/pmdprint.exentLabel(lines)
 *{
 *	if (navigator.appName == 'Microsoft Internet Explorer') {
 *		try {
 *			var obNewAXComponent = new ActiveXObject("PMDPrint.PMDPrint");
 *			obNewAXComponent.printLabel(lines.toUpperCase());
 *		} catch(e) {
 *			alert("You must install the PMDPrint plugin before you can print labels.");
 *		}
 *	} else {
 *		alert("This feature is currently Internet Explorer only.\nPlease accept our apology for the inconvenience.");
 *	}
 *}
 */

function printPickLabel(po, labelData)
{
	printLabel(labelData);
	window.location = "ptlabel_printed.php?id="+po;
}

function printAdmin(bolid)
{
	var printbtn = document.getElementById('printbtn');
	printbtn.className = "text_16";
	printbtn.style.verticalAlign = 'top';
	printbtn.style.fontWeight = 'bold';
	printbtn.style.fontSize = '24px';
	printbtn.innerHTML = 'ACCOUNTING COPY';
	printPage("Completed printing.");
	window.location = "adminprinted.php?id=" + bolid;
}

function updateOOR(bolid)
{
	window.location = "updateoor.php?id=" + bolid;
}

function editTracking(bol_id,po,carrier,edi) {
	var trackingSpan = document.getElementById('edittracking');
	var origtracking = document.getElementById('origtracking').value;
	var newInputField = document.createElement('input');
	newInputField.setAttribute("type", "text");
	newInputField.setAttribute("size", "30");
	newInputField.setAttribute("name", "newtrackingnum");
	newInputField.setAttribute("id", "newtrackingnum");
	newInputField.setAttribute("value", origtracking);
	var bolID = document.createElement('input');
	bolID.setAttribute("type", "hidden");
	bolID.setAttribute("name", "bol_id");
	bolID.setAttribute("value", bol_id);
	var poID = document.createElement('input');
	poID.setAttribute("type", "hidden");
	poID.setAttribute("name", "po_id");
	poID.setAttribute("value", po);
	var carrierID = document.createElement('input');
	carrierID.setAttribute("type", "hidden");
	carrierID.setAttribute("name", "carrier_name");
	carrierID.setAttribute("value", carrier);
	var formElement = document.createElement('form');
	formElement.setAttribute("name", "settracking");
	formElement.setAttribute("method", "post");
	formElement.setAttribute("action", "settracking.php");
	formElement.appendChild(newInputField);
	formElement.appendChild(bolID);
	formElement.appendChild(poID);
	formElement.appendChild(carrierID);
	if(typeof(edi) != undefined && edi != null)
	{
		var doEdi = document.createElement('input');
		doEdi.setAttribute("type", "hidden");
		doEdi.setAttribute("name", "edi");
		doEdi.setAttribute("value", 1);
		formElement.appendChild(doEdi);
	}
	trackingSpan.innerHTML = 'Enter tracking number & press Enter/Return to submit';
	trackingSpan.removeAttribute('onclick');
	trackingSpan.attributes['title'].value = 'Press Enter/Return to submit changes';
	trackingSpan.appendChild(formElement);
	newInputField.focus();
	newInputField.select();
}

function editEdiOrderTracking(bol_id,po,carrier)
{
	editTracking(bol_id, po, carrier, true);
}

function editConsignee(po,userid,source,reset,newid)
{
	if(reset != null)
	{
		document.location = "setnewconsignee.php?po="+po+"&source="+source+"&resetid="+newid;
	}
	// first we define the form elements
	var formElement = document.createElement('div');
	formElement.setAttribute('id','editconsform');
	formElement.setAttribute('name','editconsform');
	var oldUserField = document.createElement('input');
	var poField = document.createElement('input');
	var sourceField = document.createElement('input');
	var newAddresseeField = document.createElement('input');
	var newAddress1Field = document.createElement('input');
	var newAddress2Field = document.createElement('input');
	var newCityField = document.createElement('input');
	var newStateField = document.createElement('input');
	var newPostalField = document.createElement('input');
	var newPhoneField = document.createElement('input');
	var newSubmitBtn = document.createElement('button');
	var cancelSubmitBtn = document.createElement('button');
	oldUserField.setAttribute('type','hidden');
	poField.setAttribute('type','hidden');
	sourceField.setAttribute('type','hidden');
	newAddresseeField.setAttribute('type','text');
	newAddress1Field.setAttribute('type','text');
	newAddress2Field.setAttribute('type','text');
	newCityField.setAttribute('type','text');
	newStateField.setAttribute('type','text');
	newPostalField.setAttribute('type','text');
	newPhoneField.setAttribute('type','text');
	newAddresseeField.setAttribute('size','40');
	newAddress1Field.setAttribute('size','30');
	newAddress2Field.setAttribute('size','30');
	newCityField.setAttribute('size','30');
	newStateField.setAttribute('size','4');
	newPostalField.setAttribute('size','12');
	newPhoneField.setAttribute('size','12');
	oldUserField.setAttribute('name','olduser');
	poField.setAttribute('name','po');
	sourceField.setAttribute('name','source');
	newAddresseeField.setAttribute('name','last_name');
	newAddress1Field.setAttribute('name','address');
	newAddress2Field.setAttribute('name','address2');
	newCityField.setAttribute('name','city');
	newStateField.setAttribute('name','state');
	newPostalField.setAttribute('name','zip');
	newPhoneField.setAttribute('name','phone');
	oldUserField.setAttribute('id','olduser');
	poField.setAttribute('id','po');
	sourceField.setAttribute('id','source');
	newAddresseeField.setAttribute('id','last_name');
	newAddress1Field.setAttribute('id','address');
	newAddress2Field.setAttribute('id','address2');
	newCityField.setAttribute('id','city');
	newStateField.setAttribute('id','state');
	newPostalField.setAttribute('id','zip');
	newPhoneField.setAttribute('id','phone');
	oldUserField.setAttribute('value',userid);
	poField.setAttribute('value',po);
	sourceField.setAttribute('value',source);
	newAddresseeField.setAttribute('value','');
	newAddress1Field.setAttribute('value','');
	newAddress2Field.setAttribute('value',"");
	newCityField.setAttribute('value','');
	newStateField.setAttribute('value','');
	newPostalField.setAttribute('value','');
	newPhoneField.setAttribute('value','');
	newSubmitBtn.innerHTML = "Submit";
	newSubmitBtn.setAttribute('type','button');
	cancelSubmitBtn.innerHTML = "Cancel";
	cancelSubmitBtn.setAttribute('type','button');
	cancelSubmitBtn.onclick = function() { window.location = window.location; };
	newSubmitBtn.onclick = function() { consignChange(); };
	// attach form fields to form element
	formElement.appendChild(oldUserField);
	formElement.appendChild(poField);
	formElement.appendChild(sourceField);
	formElement.innerHTML += "Addressee:";
	formElement.appendChild(newAddresseeField);
	formElement.innerHTML += "<br />Address:";
	formElement.appendChild(newAddress1Field);
	formElement.innerHTML += "<br />Address cont'd:";
	formElement.appendChild(newAddress2Field);
	formElement.innerHTML += "<br />City:";
	formElement.appendChild(newCityField);
	formElement.innerHTML += "&nbsp;ST:";
	formElement.appendChild(newStateField);
	formElement.innerHTML += "&nbsp;Zip:";
	formElement.appendChild(newPostalField);
	formElement.innerHTML += "<br />Phone #:";
	formElement.appendChild(newPhoneField);
	formElement.innerHTML += "<br />";
	formElement.appendChild(newSubmitBtn);
	formElement.appendChild(cancelSubmitBtn);
	var trackingSpan = document.getElementById('editconsignwrap');
	trackingSpan.innerHTML = 'Enter consignee address<br />';
	trackingSpan.removeAttribute('onclick');
	trackingSpan.setAttribute('title','Enter New Consignee Address');
	trackingSpan.appendChild(formElement);
}

function verifyConsignChange()
{
	var addbolform = document.forms[0];
	if(document.getElementById('last_name').value == '[Addressee]')
	{
		alert('Custom address is missing the addressee');
		return false;
	}
	if(document.getElementById('address').value == '[Address]')
	{
		alert('Custom address is missing the shipping address');
		return false;
	}
	if(document.getElementById('state').value == '[ST]' || document.getElementById('state').value.length > 2 || document.getElementById('state').value.length < 2)
	{
		alert('Custom address requires a two-character state code');
		return false;
	}
	if(document.getElementById('zip').value.length < 5 || document.getElementById('zip').value.length > 5)
	{
		alert('Custom address postal code must be five digits in length');
		return false;
	}
	return true;
}

function consignChange()
{
	if(!verifyConsignChange()) return;
	var formGoesHere = document.getElementById('customaddyformhere');
	var formElement = document.createElement('form');
	formElement.setAttribute('id','newconsform');
	formElement.setAttribute('name','newconsform');
	formElement.setAttribute('method','post');
	formElement.setAttribute('action','setnewconsignee.php');
	var oldUserField = document.createElement('input');
	var poField = document.createElement('input');
	var sourceField = document.createElement('input');
	var newAddresseeField = document.createElement('input');
	var newAddress1Field = document.createElement('input');
	var newAddress2Field = document.createElement('input');
	var newCityField = document.createElement('input');
	var newStateField = document.createElement('input');
	var newPostalField = document.createElement('input');
	var newPhoneField = document.createElement('input');
	oldUserField.setAttribute('type','hidden');
	poField.setAttribute('type','hidden');
	sourceField.setAttribute('type','hidden');
	newAddresseeField.setAttribute('type','hidden');
	newAddress1Field.setAttribute('type','hidden');
	newAddress2Field.setAttribute('type','hidden');
	newCityField.setAttribute('type','hidden');
	newStateField.setAttribute('type','hidden');
	newPostalField.setAttribute('type','hidden');
	newPhoneField.setAttribute('type','hidden');
	oldUserField.setAttribute('name','olduser');
	poField.setAttribute('name','po');
	sourceField.setAttribute('name','source');
	newAddresseeField.setAttribute('name','last_name');
	newAddress1Field.setAttribute('name','address');
	newAddress2Field.setAttribute('name','address2');
	newCityField.setAttribute('name','city');
	newStateField.setAttribute('name','state');
	newPostalField.setAttribute('name','zip');
	newPhoneField.setAttribute('name','phone');
	oldUserField.setAttribute('value',document.addbol.olduser.value);
	poField.setAttribute('value',document.addbol.po.value);
	sourceField.setAttribute('value',document.addbol.source.value);
	newAddresseeField.setAttribute('value',document.addbol.last_name.value);
	newAddress1Field.setAttribute('value',document.addbol.address.value);
	newAddress2Field.setAttribute('value',document.addbol.address2.value);
	newCityField.setAttribute('value',document.addbol.city.value);
	newStateField.setAttribute('value',document.addbol.state.value);
	newPostalField.setAttribute('value',document.addbol.zip.value);
	newPhoneField.setAttribute('value',document.addbol.phone.value);
	formElement.appendChild(oldUserField);
	formElement.appendChild(poField);
	formElement.appendChild(sourceField);
	formElement.appendChild(newAddresseeField);
	formElement.appendChild(newAddress1Field);
	formElement.appendChild(newAddress2Field);
	formElement.appendChild(newCityField);
	formElement.appendChild(newStateField);
	formElement.appendChild(newPostalField);
	formElement.appendChild(newPhoneField);
	formGoesHere.appendChild(formElement);
	document.getElementById('newconsform').submit();
}

function editCarrier(bol_id) {
	var carrierSpan = document.getElementById('editcarrier');
	var origCarrier = document.getElementById('origcarrier').value;
	var newInputField = document.createElement('input');
	newInputField.setAttribute("type", "text");
	newInputField.setAttribute("size", "50");
	newInputField.setAttribute("name", "newcarrier");
	newInputField.setAttribute("id", "newcarrier");
	newInputField.setAttribute("value", origCarrier);
	var bolID = document.createElement('input');
	bolID.setAttribute("type", "hidden");
	bolID.setAttribute("name", "bol_id");
	bolID.setAttribute("value", bol_id);
	var formElement = document.createElement('form');
	formElement.setAttribute("name", "setcarrier");
	formElement.setAttribute("method", "post");
	formElement.setAttribute("action", "setcarrier.php");
	formElement.appendChild(newInputField);
	formElement.appendChild(bolID);
	carrierSpan.innerHTML = 'Enter carrier name & press Enter/Return to submit';
	carrierSpan.removeAttribute('onclick');
	carrierSpan.attributes['title'].value = 'Press Enter/Return to submit changes';
	carrierSpan.appendChild(formElement);
	newInputField.focus();
	newInputField.select();
}

function editComment(bol_id) {
	var commentSpan = document.getElementById('editcomment');
	var origcomment = document.getElementById('origcomment').value;
	var newInputField = document.createElement('input');
	newInputField.setAttribute("type", "text");
	newInputField.setAttribute("size", "50");
	newInputField.setAttribute("name", "newcomment");
	newInputField.setAttribute("id", "newcomment");
	newInputField.setAttribute("value", origcomment);
	var bolID = document.createElement('input');
	bolID.setAttribute("type", "hidden");
	bolID.setAttribute("name", "bol_id");
	bolID.setAttribute("value", bol_id);
	var formElement = document.createElement('form');
	formElement.setAttribute("name", "setcomment");
	formElement.setAttribute("method", "post");
	formElement.setAttribute("action", "setcomment.php");
	formElement.appendChild(newInputField);
	formElement.appendChild(bolID);
	commentSpan.innerHTML = 'Enter comment & press Enter/Return to submit';
	commentSpan.removeAttribute('onclick');
	commentSpan.attributes['title'].value = 'Press Enter/Return to submit changes';
	commentSpan.appendChild(formElement);
	newInputField.focus();
	newInputField.select();
}

function editFreight(bol_id,userid,po,edi) {
	var freightSpan = document.getElementById('editfreight');
	var origfreight = document.getElementById('origfreight').value;
	if(origfreight=='') origfreight = '0.00';
	var newInputField = document.createElement('input');
	newInputField.setAttribute("type", "text");
	newInputField.setAttribute("size", "15");
	newInputField.setAttribute("name", "newfreight");
	newInputField.setAttribute("id", "newfreight");
	newInputField.setAttribute("value", origfreight);
	var bolID = document.createElement('input');
	bolID.setAttribute("type", "hidden");
	bolID.setAttribute("name", "bol_id");
	bolID.setAttribute("value", bol_id);
	var baseUserID = document.createElement('input');
	baseUserID.setAttribute("type", "hidden");
	baseUserID.setAttribute("name", "user_id");
	baseUserID.setAttribute("value", userid);
	var poID = document.createElement('input');
	poID.setAttribute("type", "hidden");
	poID.setAttribute("name", "po_id");
	poID.setAttribute("value", po);
	var formElement = document.createElement('form');
	formElement.setAttribute("name", "setfreight");
	formElement.setAttribute("id", "freightform");
	formElement.setAttribute("method", "post");
	formElement.setAttribute("action", "setfreight.php");
	formElement.setAttribute("onsubmit", "return freightVerify()");
	formElement.appendChild(newInputField);
	formElement.appendChild(bolID);
	formElement.appendChild(baseUserID);
	formElement.appendChild(poID);
	if(typeof(edi) != undefined && edi != null)
	{
		var origtracking = document.getElementById('origtracking').value;
		if(origtracking == '') formElement.setAttribute("onsubmit", "return EdiOrderPrintNoTrack()");
		var doEdi = document.createElement('input');
		doEdi.setAttribute("type", "hidden");
		doEdi.setAttribute("name", "edi");
		doEdi.setAttribute("value", 1);
		formElement.appendChild(doEdi);
	}
	freightSpan.innerHTML = 'Enter freight amount & press Enter/Return to submit';
	freightSpan.removeAttribute('onclick');
	freightSpan.attributes['title'].value = 'Press Enter/Return to submit changes';
	freightSpan.appendChild(formElement);
	newInputField.focus();
	newInputField.select();
}

function EdiOrderPrintNoTrack()
{
	var check = confirm("Do you want to print the Packing Slip with a blank tracking number?");
	if(check == true)
	{
		return true;
	}
	else
	{
		var noprint = document.createElement('input');
		noprint.setAttribute("type", "hidden");
		noprint.setAttribute("name", "nomakeedi");
		noprint.setAttribute("value", "1");
		var freightForm = document.getElementById("freightform");
		freightForm.appendChild(noprint);
		return true;
	}
}

function editEdiOrderFreight(bol, user, po)
{
	editFreight(bol, user, po, true);
}

function makeCancelReason(target, targname)
{
	var sibs = target.parentNode.getElementsByTagName('select');
	if(sibs.length > 1) return;
	var cancelOption = document.createElement('select');
	for(var i=0; i<canceltypes.length; i++)
	{
		var newcancelopt = document.createElement('option');
		newcancelopt.setAttribute('value', canceltypes[i]);
		newcancelopt.innerHTML = canceldesc[i];
		cancelOption.appendChild(newcancelopt);
	}
	cancelOption.setAttribute('name', 'rsn'+targname);
	cancelOption.setAttribute('onchange', 'updateReason("'+targname+'", canceltypes)')
	target.parentNode.appendChild(cancelOption);
	updateReason(targname, canceltypes);
}

function updateReason(targname, canceltypes)
{
	var hiddenval = document.getElementsByName("rsn"+targname.substr(3,3)+targname.substr(targname.length-6));
	// this is the one
	if(hiddenval.length > 0)
	{
		var selectval = document.getElementsByName('rsn'+targname);
		if(selectval.length > 0)
		{
			hiddenval[0].value = selectval[0].value;
		}
		else
		{
			hiddenval[0].value = canceltypes[0];
		}
	}
}

function detectCancel(self)
{
	var targetself = document.getElementsByName(self);
	var selectedamt = targetself[0].selectedIndex;
	if(targetself[0].options[selectedamt].value != 0)
	{
		// non-zero value selected, so show the cancel button
		document.getElementById('hideme').style.visibility = "visible";
		document.getElementById('hideme').style.display = "inline";
		makeCancelReason(targetself[0], self);
	}
	else
	{
		// zero value selected, check all others to make sure we're good
		var allselects = document.getElementsByTagName('select'); // should only be the cancel selects on the page
		for(var q = 0; q < allselects.length; q++)
		{
			if(allselects[q].value != 0) return false;
		}
		// not returned, all values are 0...hide the button then
		document.getElementById('hideme').style.visibility = "hidden";
	}
}

function updateService()
{
	var servtxt = document.getElementsByName('txtservice_level');
	var serv = document.getElementsByName('service_level');
	servtxt[0].value = serv[0].options[serv[0].selectedIndex].value;
}


function freightVerify() {
	var freightField = document.getElementById('newfreight');
	if(isNaN(freightField.value)) {
		alert("Freight amount must be a number.");
		freightField.focus();
		freightField.select();
		return false;
	} else {
		return true;
	}
}

function makeShipmentEdi(bol)
{
	window.location = 'makeshipmentedi.php?id=' + bol;
}

function setCarrier()
{
	var car = document.getElementById('carrier_name');
	var abbrev = document.getElementById('carrier_abbrev');
	abbrev.value = car.options[car.selectedIndex].value;
	var serve = document.getElementById('service_level');
	serve.value = document.getElementById(abbrev.value).value;
}